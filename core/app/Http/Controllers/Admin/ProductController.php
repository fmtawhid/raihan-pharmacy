<?php

namespace App\Http\Controllers\Admin;

use App\Support\Barcode;
use App\Support\Sku;
use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\ProductManager;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Purchaser;
use App\Models\Category;
use App\Models\DigitalFile;
use App\Models\Media;
use App\Models\Product;
use App\Services\ProductValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{

    // Inject an instance of ProductManager into the class
    private $productManager;

    public function __construct(ProductManager $productManager)
    {
        $this->productManager = $productManager;
    }

    /**
     * Show the form to add a new digital product.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return $this->productForm("Add New Product");
    }

    /**
     * Show the form to edit an existing digital product.
     *
     * @param int $id Product ID
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $product = Product::with(['attributes', 'attributeValues.media', 'batches.purchaser',])->findOrFail($id);
        return $this->productForm("Edit Product", $product);
    }

    /**
     * Common method to set up data for rendering the product form.
     *
     * @param string $pageTitle Page title for the form
     * @param Product|null $product Product (for editing)
     * @return \Illuminate\View\View
     */
    public function productForm($pageTitle, $product = null)
    {



        $brands                 = Brand::orderBy('name')->get();
        $categories             = Category::with('allSubcategories')->isParent()->get();
        $attributes             = Attribute::with('attributeValues')->get();
        $purchasers    = Purchaser::orderBy('name')->get();
        $productAttributes      = [];
        $attributeValues        = [];

        if ($product && $product->attributes->count()) {
            $productAttributes  = $product->attributes->pluck('id');
            $attributeValues    = $product->attributeValues->groupBy('attribute_id');
            $attributeValues    = $attributeValues->map->pluck('pivot.attribute_value_id')->all();
        }
        return view('admin.product.form.setting', compact('pageTitle', 'categories', 'brands', 'product', 'attributes', 'attributeValues', 'productAttributes', 'purchasers'));
    }

    /**
     * Adjust the stock of a product based on the form data.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $productId Product ID
     * @return void
     */
    public function store(Request $request, $id = 0)
    {
        $productManager    = $this->productManager;
        $isUpdate          = $id ? true : false;

        if ($isUpdate) {
            $product = Product::find($id);
            if (!$product) {
                return errorResponse('Product not found');
            }
        } else {
            $product = new Product();
        }

        $validationService = new ProductValidationService();
        $validator         = $validationService->productValidationRule($request, $product);
        if ($validator->fails()) {
            $data = [
                'isUpdate' => $isUpdate,
                'redirectTo' => $this->getRedirectUrl($product, $isUpdate)
            ];
            return errorResponse($validator->errors(), $data);
        }

        // Assign 'specify' value from the request to the product (added here)
        if ($request->has('specify')) {
            $product->specify = $request->specify; // Save the 'specify' field
        }

        // Continue with the existing logic for gallery images
        if ($request->gallery_images) {
            $requestGalleryImages = trim($request->gallery_images, ',');
            $galleryImages = explode(",", $requestGalleryImages);
            $existingImages = Media::whereIn('id', $galleryImages)->pluck('id')->toArray();
            if (count($galleryImages) !== count($existingImages)) {
                return errorResponse('Invalid images selected');
            }
        } else {
            $galleryImages = [];
        }

        // Continue with validation for attribute values, stock, etc.
        $validationService->validateAttributeValues($request);
        $needAttributeAdjustment = $this->isAttributeAdjustmentNeeded($request, $product);

        // Create unique slug
        $slug = createUniqueSlug($request->slug ?? $request->name, Product::class, $id);
        $request->merge(['slug' => $slug]);

        // Handle digital file upload
        $digitalFileName = null;
        if ($request->is_downloadable && $request->hasFile('file') && $request->delivery_type == Status::DOWNLOAD_INSTANT && $request->product_type == Status::PRODUCT_TYPE_SIMPLE) {
            $digitalFileName = $productManager->uploadDigitalProductFile($request->file, $product->digitalFile->name ?? null);
        }

        // Check if stock is trackable
        $isTrackable = $this->checkStockTrackable($request->track_inventory, $request->in_stock, $product, $isUpdate);
        $changeQty = $isTrackable ? $this->getStockChangeQuantity($request->in_stock, $product, $isUpdate) : 0;

        // Set product values and save product
        $productManager->setProductEntities($request, $product);
        $product->save();

        // Handle batch and purchaser information if provided
        $hasBatchInfo =
            $request->filled('batch_no')       ||
            $request->filled('quantity')       ||
            $request->filled('purchase_price');

        if ($hasBatchInfo) {
            $purchaserId = $this->handlePurchaserInfo($request);

            // Create stock entry and track changes
            $this->productManager->receiveStock(
                $product,
                null,
                [
                    'batch_no'       => $request->batch_no,
                    'purchaser_id'   => $purchaserId,
                    'purchase_price' => $request->purchase_price,
                    'quantity'       => $request->quantity ?? 0,
                    'purchased_at'   => $request->purchased_at,
                ]
            );

            $isTrackable = false;
        }

        // Handle SKU and barcode
        if ($isUpdate) {
            if ($product->wasChanged('sku')) {
                Storage::disk('public')->delete($product->getOriginal('barcode_path'));
                $product->barcode_path = null;
            }
        }

        if (!$product->barcode_path) {
            $product->barcode_path = Barcode::file(
                $product->sku,
                'barcodes/products'
            );
            $product->saveQuietly();
        }

        // Handle stock log after product save
        if ($isTrackable) {
            $string = Str::plural('product', abs($changeQty));
            $description = $changeQty > 0 ?  $changeQty . " $string added" : abs($changeQty) . " $string subtracted";
            $remark = $changeQty > 0 ? '+' : '-';
            $productManager->createStockLog($product, $changeQty, $description, null, $remark);
        }

        // Handle attribute adjustments
        if ($needAttributeAdjustment) {
            $productAttributes = $product->product_type == Status::PRODUCT_TYPE_VARIABLE ? $request->product_attributes : [];
            $attributeValues = $product->product_type == Status::PRODUCT_TYPE_VARIABLE ? $request->attribute_values : [];
            $productManager->adjustProductAttributes($productAttributes, $product, $isUpdate);
            $attributeValues = array_merge(...$attributeValues);
            $productManager->adjustProductAttributeValues($attributeValues, $product, $isUpdate);
            $productManager->adjustProductVariants($product->id);
        }

        // Remove the old digital file if necessary
        if ($product->digitalFile && ($request->delivery_type == Status::DOWNLOAD_AFTER_SALE || $request->product_type == Status::PRODUCT_TYPE_VARIABLE)) {
            $productManager->removeDigitalProductFile($product->digitalFile->name);
            $product->digitalFile->delete();
        }

        // Save the new digital file if necessary
        if ($digitalFileName) {
            $digitalFile = $product->digitalFile ?? new DigitalFile();
            $digitalFile->name = $digitalFileName;
            $product->digitalFile()->save($digitalFile);
        }

        // Handle gallery images, categories, variants, etc.
        $productManager->adjustGalleryImages($galleryImages, $product, $isUpdate);
        $productManager->adjustCategories($request->categories, $product, $isUpdate);
        $this->saveProductVariants($request, $product);

        $message = $isUpdate ? 'Product updated successfully' : 'Product added successfully';

        return response()->json(['status' => 'success', 'message' => $message, 'isUpdate' => $isUpdate, 'redirectTo' => $this->getRedirectUrl($product, $isUpdate)]);
    }

//     public function store(Request $request, $id = 0)
//     {
//         $productManager    = $this->productManager;
//         $isUpdate          = $id ? true : false;

//         if ($isUpdate) {
//             $product = Product::find($id);
//             if (!$product) {
//                 return errorResponse('Product not found');
//             }
//         } else {
//             $product = new Product();
//         }

//         $validationService = new ProductValidationService();
//         $validator         = $validationService->productValidationRule($request, $product);
//         if ($validator->fails()) {
//             $data = [
//                 'isUpdate' => $isUpdate,
//                 'redirectTo' => $this->getRedirectUrl($product, $isUpdate)
//             ];
//             return errorResponse($validator->errors(), $data);
//         }

//         // Assign 'specify' value from the request to the product
//         if ($request->has('specify')) {
//             $product->specify = $request->specify; // Save the 'specify' field
//         }

//         if ($request->gallery_images) {
//             $requestGalleryImages = trim($request->gallery_images, ',');
//             $galleryImages = explode(",", $requestGalleryImages);
//             $existingImages = Media::whereIn('id', $galleryImages)->pluck('id')->toArray();
//             // Check if all the requested IDs exist in the database
//             if (count($galleryImages) !== count($existingImages)) {
//                 return errorResponse('Invalid images selected');
//             }
//         } else {
//             $galleryImages = [];
//         }


//         $validationService->validateAttributeValues($request);
//         $needAttributeAdjustment = $this->isAttributeAdjustmentNeeded($request, $product);

//         $slug = createUniqueSlug($request->slug ?? $request->name, Product::class, $id);


//         $request->merge(['slug' => $slug]);

//         $digitalFileName = null;


//         if ($request->is_downloadable && $request->hasFile('file') && $request->delivery_type == Status::DOWNLOAD_INSTANT && $request->product_type == Status::PRODUCT_TYPE_SIMPLE) {
//             $digitalFileName = $productManager->uploadDigitalProductFile($request->file, $product->digitalFile->name ?? null);
//         }

//         // product stock log trackable or not
//         $isTrackable = $this->checkStockTrackable($request->track_inventory, $request->in_stock, $product, $isUpdate);
//         $changeQty = $isTrackable ? $this->getStockChangeQuantity($request->in_stock, $product, $isUpdate) : 0;

//         // Assign the values of products table's columns
//         $productManager->setProductEntities($request, $product);


//         $product->save();

//         $hasBatchInfo =
//             $request->filled('batch_no')       ||
//             $request->filled('quantity')       ||
//             $request->filled('purchase_price');

//         if ($hasBatchInfo) {
//             // 1. resolve / create purchaser
//             if ($request->purchaser_id === 'self' || !$request->filled('purchaser_id')) {
//                 $purchaserId = 1; // ← ID of 'SELF / MANUFACTURER'
//             } elseif ($request->purchaser_id === 'new') { 
//                 // “Other (type below)” → create or fetch by name
//                 $purchaserId = Purchaser::firstOrCreate(
//                     ['name' => trim($request->new_purchaser)]
//                 )->id;
//             } else {
//                 // one of the existing suppliers
//                 $purchaserId = $request->purchaser_id;
//             }


//             // 2. hand off to ProductManager → creates batch, bumps stock, logs
//             $this->productManager->receiveStock(
//                 $product,
//                 null,               // variant (simple product here)
//                 [
//                     'batch_no'       => $request->batch_no,
//                     'purchaser_id'   => $purchaserId,
//                     'purchase_price' => $request->purchase_price,
//                     'quantity'       => $request->quantity ?? 0,
//                     'purchased_at'   => $request->purchased_at,
//                 ]
//             );

//             // 3. because receiveStock already logged the change,
//             //    SKIP the “createStockLog” block that comes later
//             $isTrackable = false;
//         }
//         /* ------------------------------------------------------------------
//    ①  Is this a fresh product, or did the SKU change?
//    ②  Delete any stale PNG.
//    ③  Create a new PNG and update the DB column.
// -------------------------------------------------------------------*/
//         if ($isUpdate) {
//             // did the admin change the SKU?
//             if ($product->wasChanged('sku')) {
//                 Storage::disk('public')->delete($product->getOriginal('barcode_path'));
//                 $product->barcode_path = null;
//             }
//         }

//         if (!$product->barcode_path) {
//             $product->barcode_path = Barcode::file(
//                 $product->sku,
//                 'barcodes/products'     // directory inside storage/app/public
//             );
//             $product->saveQuietly();    // second save – no events, no timestamps touch
//         }

//         /* ---------------------------------------------------------------
//    continue with all the existing logic (variants, gallery, etc.)
// ----------------------------------------------------------------*/


//         // create stock log after product save
//         if ($isTrackable) {
//             $string = Str::plural('product', abs($changeQty));
//             $description = $changeQty > 0 ?  $changeQty . " $string added" : abs($changeQty) . " $string subtracted";
//             $remark = $changeQty > 0 ? '+' : '-';
//             $productManager->createStockLog($product, $changeQty, $description, null, $remark);
//         }


//         if ($needAttributeAdjustment) {
//             $productAttributes = $product->product_type == Status::PRODUCT_TYPE_VARIABLE ? $request->product_attributes : [];
//             $attributeValues = $product->product_type == Status::PRODUCT_TYPE_VARIABLE ? $request->attribute_values : [];
//             $productManager->adjustProductAttributes($productAttributes, $product, $isUpdate);
//             $attributeValues = array_merge(...$attributeValues);
//             $productManager->adjustProductAttributeValues($attributeValues, $product, $isUpdate);
//             $productManager->adjustProductVariants($product->id);
//         }


//         // Remove the old digital file if the delivery type changed from instant download to after sale and has old file
//         // Also if the product variant from no variant to no variant
//         if ($product->digitalFile && ($request->delivery_type == Status::DOWNLOAD_AFTER_SALE || $request->product_type == Status::PRODUCT_TYPE_VARIABLE)) {
//             $productManager->removeDigitalProductFile($product->digitalFile->name);
//             $product->digitalFile->delete();
//         }

//         // Save the digital file to database
//         if ($digitalFileName) {
//             $digitalFile = $product->digitalFile ?? new DigitalFile();
//             $digitalFile->name = $digitalFileName;
//             $product->digitalFile()->save($digitalFile);
//         }

//         $productManager->adjustGalleryImages($galleryImages, $product, $isUpdate);

//         $productManager->adjustCategories($request->categories, $product, $isUpdate);

//         // Store/Update product variants

//         $this->saveProductVariants($request, $product);

//         $message = $isUpdate ? 'Product updated successfully' : 'Product added successfully';

//         return response()->json(['status' => 'success', 'message' => $message, 'isUpdate' => $isUpdate, 'redirectTo' => $this->getRedirectUrl($product, $isUpdate)]);
//     }


    public function generateSku()
    {
        return response()->json(['sku' => Sku::base()]);
    }

    /**
     * Determine if attribute adjustment is needed for a product.
     *
     * This method checks if the product's attributes need to be adjusted based on
     * changes in the product type or if the product is a variable type.
     *
     * @param \Illuminate\Http\Request $request The incoming request containing product data
     * @param \App\Models\Product $product The product being checked
     * @return bool Returns true if attribute adjustment is needed, false otherwise
     */
    private function isAttributeAdjustmentNeeded($request, $product)
    {
        // When storing new product and product type is simple
        if (!$product->id && $request->product_type == Status::PRODUCT_TYPE_SIMPLE) {
            return false;
        }

        // When storing new product and product type is variable
        if (!$product->id && $request->product_type == Status::PRODUCT_TYPE_VARIABLE) {
            return true;
        }

        if ($product->id && $product->product_type != $request->product_type) {
            return true;
        }

        $oldAttributes = $product->attributeValues->pluck('id')->toArray();
        $newAttributes = array_merge(...array_values($request->attribute_values ?? []));

        if (array_diff($oldAttributes, $newAttributes) || array_diff($newAttributes, $oldAttributes)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the redirect URL after creating or updating a product.
     *
     * @param \App\Models\Product $product
     * @param bool $isUpdate Indicates whether the product is being updated
     * @return string
     */
    private function getRedirectUrl($product, $isUpdate)
    {
        return $product->editUrl();
    }

    /**
    /**
     * Soft delete a product.
     *
     * @param int $id Product ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        $product = Product::where('id', $id)->withTrashed()->first();
        $message = $this->productManager->deleteProduct($product);
        $notify[] = ['success', $message];
        return back()->withNotify($notify);
    }

    /**
     * Download the digital product file.
     *
     * @param int $id Digital File ID
     * @return \Illuminate\Http\Response
     */
    public function digitalDownload($id)
    {
        return $this->productManager->downloadDigitalProductFile($id);
    }

    /**
     * Generate product variants for a variable product.
     *
     * This method generates all possible combinations of attribute values for a variable product
     * and saves them as product variants.
     *
     * @param int $id Product ID
     * @return \Illuminate\Http\Response
     */
    public function generateVariants($id)
    {

        $product = Product::with([
            'attributeValues',
            'productVariants' => function ($variants) {
                $variants->withTrashed();
            }
        ])->find($id);

        if (!$product) {
            return errorResponse('Product not found');
        }

        if ($product->product_type != Status::PRODUCT_TYPE_VARIABLE) {
            return errorResponse('This product is not a variable product');
        }

        if ($product->attributeValues->count() == 0) {
            return errorResponse('This product has no attribute value yet');
        }

        $generatedVariants = $this->productManager->generateVariants($product->attributeValues);
        $this->productManager->saveProductVariants($generatedVariants, $product);

        return successResponse('Product variants generated successfully');
    }

    /**
     * Save generated product variants for a variable product.
     *
     * This method takes an array of generated variants and saves them to the database
     * as product variants associated with the given product.
     *
     * @param array $generatedVariants An array of generated product variants
     * @param \App\Models\Product $product The product to which the variants belong
     * @return void
     */
    private function saveProductVariants(Request $request, $product)
    {

        if (!$request->variants) {
            return; // No variants to save
        }

        $variants       = $product->productVariants;
        $minimumPrice   = null;


        foreach ($request->variants as $requestVariant) {
            $requestVariant            = (object) $requestVariant;
            $variant                   = $variants->where('id', $requestVariant->id)->first();

            $isUpdate = true;
            $isTrackable = $this->checkStockTrackable(@$requestVariant->track_inventory, $requestVariant->in_stock, $variant, $isUpdate);
            $changeQty = $isTrackable ? $this->getStockChangeQuantity($requestVariant->in_stock, $variant, $isUpdate) : 0;

            $variant->regular_price    = $requestVariant->regular_price ?? null;
            $variant->sale_price       = $requestVariant->sale_price ?? null;
            $variant->sale_starts_from = $requestVariant->sale_starts_from;
            $variant->sale_ends_at     = $requestVariant->sale_ends_at;
            // $variant->sku              = $requestVariant->sku;

            $variant->sku = $requestVariant->sku
                ?: ($variant->sku                                   // keep existing on update
                    ?: $this->makeVariantSku(
                        $product->sku,
                        $requestVariant->attribute_values
                    ));

            $variant->main_image_id    = $requestVariant->main_image;
            $variant->manage_stock     = @$requestVariant->manage_stock ? Status::YES : Status::NO;
            $variant->track_inventory  = @$requestVariant->track_inventory ? Status::YES : Status::NO;
            $variant->show_stock       = @$requestVariant->show_stock ? Status::YES : Status::NO;
            $variant->in_stock         = @$requestVariant->in_stock ?? 0;
            $variant->alert_quantity   = @$requestVariant->alert_quantity ?? 0;
            $variant->is_published     = @$requestVariant->is_published ? Status::YES : Status::NO;
            $variant->save();

            if ($variant->is_published) {
                if ($minimumPrice == null && $variant->regular_price) {
                    $minimumPrice = $variant->regular_price;
                } elseif ($requestVariant->regular_price && $minimumPrice > $variant->regular_price) {
                    $minimumPrice = $variant->regular_price;
                }
            }


            if ($requestVariant->gallery_images) {
                $requestGalleryImages = trim($requestVariant->gallery_images, ',');
                $galleryImages = explode(",", $requestGalleryImages);
            } else {
                $galleryImages = [];
            }

            $productManager = $this->productManager;


            $productManager->adjustVariantGalleryImages($galleryImages, $variant);


            $digitalFileName    = null;

            if (@$requestVariant->file) {
                $digitalFileName = $this->productManager->uploadDigitalProductFile($requestVariant->file, $variant->digitalFile->name ?? null);
            }

            if ($digitalFileName) {
                $digitalFile = $variant->digitalFile ?? new DigitalFile();
                $digitalFile->name = $digitalFileName;
                $variant->digitalFile()->save($digitalFile);
            }

            if ($isTrackable) {
                $string = Str::plural('product', abs($changeQty));
                $description = $changeQty > 0 ?  $changeQty . " $string added" : abs($changeQty) . " $string subtracted";
                $remark = $changeQty > 0 ? '+' : '-';
                $productManager->createStockLog($product, $changeQty, $description, $variant, $remark);
            }
        }

        $product->regular_price = $minimumPrice;
        $product->save();
    }

    /**
     * check product should be trackable or not
     * @param \Illuminate\Http\Request $request The incoming request containing product data
     * @param $product the instance of product for which stock log is being created.
     * @param $isUpdate the flag is create or update the product
     */
    private function checkStockTrackable($requestTrackInventory, $requestQuantity, $product, $isUpdate)
    {
        return (!$isUpdate && $requestTrackInventory) || ($isUpdate && $requestTrackInventory && $product->in_stock != $requestQuantity);
    }

    private function makeVariantSku(string $parentSku, $attributeJson): string
    {
        $suffix = Sku::suffix(json_encode($attributeJson));     // RED‑M
        $candidate = "{$parentSku}-{$suffix}";
        $i = 1;
        $sku = $candidate;
        while (\App\Models\ProductVariant::where('sku', $sku)->exists()) {
            $sku = "{$candidate}-{$i}";
            $i++;
        }
        return $sku;
    }

    /**
     * get how many product added or subtracted
     * @param \Illuminate\Http\Request $request The incoming request containing product data
     * @param $product the instance of product for which stock log is being created.
     * @param $isUpdate the flag is create or update the product
     */
    private function getStockChangeQuantity($requestQuantity, $product, $isUpdate)
    {
        return !$isUpdate ? $requestQuantity : $requestQuantity - $product->in_stock;
    }

    /**
     * Switch the publish status of a product.
     *
     * This method toggles the `is_published` status of the specified product.
     * If the product is currently published, it will be unpublished and vice versa.
     *
     * @param int $id Product ID
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the new publish status
     */
    public function switchPublishStatus($id)
    {
        $product         = Product::find($id);
        $product->is_published = !$product->is_published;
        $product->save();
        return successResponse($product->is_published ? 'Published' : 'Unpublished');
    }

    /**
     * Assign media to attribute values for a specific product.
     *
     * @param \Illuminate\Http\Request $request The incoming request containing attribute values and media IDs
     * @param int $id Product ID
     * @return \Illuminate\Http\RedirectResponse A redirect response back to the product edit page with a notification
     */
    public function assignMediaToAttributes(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $galleryImages = $product->galleryImages;

        if ($galleryImages->isEmpty()) {
            $notify[] = ['error', 'Invalid request'];
            return back()->withNotify($notify);
        }

        $mediaIdsArray = $galleryImages->pluck('id')->toArray();
        $mediaAttribute = $product->attributes->whereIn('type', [Status::ATTRIBUTE_TYPE_COLOR])->first();
        $mediaAttributeValues = $product->attributeValues->where('attribute_id', $mediaAttribute->id)->pluck('id')->toArray();

        $request->validate([
            'attribute_values' => 'required|array',
            'attribute_values.*.media_id' => 'nullable|in:' . implode(',', $mediaIdsArray),
            'attribute_values.*.attribute_value_id' => 'nullable|in:' . implode(',', $mediaAttributeValues),
        ]);

        $attributes = collect($request->attribute_values);

        $filteredAttributes = $attributes->filter(function ($item) {
            return isset($item['attribute_value_id']);
        });

        foreach ($filteredAttributes as $attribute) {
            $product->attributeValues()->updateExistingPivot(
                $attribute['attribute_value_id'],
                ['media_id' => $attribute['media_id']]
            );
        }

        // Provide a success response
        $notify[] = ['success', 'Media assigned successfully to attribute values'];
        return redirect()->to(route('admin.products.edit', $product->id) . '#media-content')->withNotify($notify);
    }
}
