<div class="row g-4 g-xl-5 product-details-container">
    <div class="col-md-5" id="variantImages">
        @include($activeTemplate . 'partials.product_images')
    </div>

    <div class="col-md-7">
        <div class="product-details">
            <div class="product-header">
                <h1 class="product-title">{{ __($product->name) }}</h1>

                @if (gs('product_review'))
                    <div class="ratings-area">
                        <span class="ratings">
                            @php echo __(displayRating($product->reviews_avg_rating)) @endphp
                        </span>
                        @if ($product->reviews_count)
                            <span>({{ $product->reviews_count }})</span>
                        @else
                            <span> | @lang('No Review')</span>
                        @endif
                    </div>
                @endif

                <div class="d-flex flex-wrap align-items-center gap-2 product-detail-price">
                    <span class="product-price" id="productPrice">
                        @php echo $product->formattedPrice();  @endphp
                    </span>

                    <span id="stockBadge"></span>
                    <a href="https://wa.me/+8801821051012" target="_blank" class="whatsapp-button">
                        <i class="fab fa-whatsapp"></i>
                        <span class="whatsapp-text">Chat on WhatsApp</span>
                    </a>


                </div>
            </div>

            @if ($product->summary)
                <div class="product-summary">
                    <!-- {{ __($product->summary) }} -->
                      <!-- @php echo $product->summary @endphp -->
                       {!! $product->summary !!}
                      
                </div>
                <style>
                    .product-summary ul li {
                        list-style-type: disc !important;
                        display: list-item !important;
                    }
                    .product-summary ul {
                        margin-left: 20px !important;
                    }
                </style>
            @endif

            <span>
                <h2 class="product-details-label d-inline">@lang('Categories'): </h2>
                @forelse ($product->categories as $category)
                    <a href="{{ $category->shopLink() }}">{{ __($category->name) }}</a>
                    @if (!$loop->last)
                        /
                    @endif
                @empty
                    @lang('Uncategorized')
                @endforelse
            </span>

            <span>
                <h3 class="product-details-label d-inline">@lang('Brand'):</h3>
                @if ($product->brand)
                    <a href="{{ $product->brand->shopLink() }}">{{ __($product->brand->name) }}</a>
                @else
                    @lang('Non Brand')
                @endif
            </span>

            <span>
                <b class="product-details-label">@lang('SKU'):</b> <span
                    id="productSku">{{ $product->sku ?? __('Not available') }}</span>
            </span>

        </div>
        <style>
            .whatsapp-button {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                background-color: #0CC143; /* WhatsApp green */
                color: white;
                padding: 12px 20px;
                border-radius: 50px;
                font-size: 16px;
                font-weight: 600;
                text-decoration: none;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                transition: background-color 0.3s ease, transform 0.2s ease;
            }
            .whatsapp-text{
                color: white;
            }

            .whatsapp-button i {
                font-size: 20px;
                color: white;
            }

            .whatsapp-button:hover {
                text-decoration: none !important;
                border-bottom: none;
            }

            @media (max-width: 576px) {
                .whatsapp-button {
                    width: 100%;
                    justify-content: center;
                    font-size: 14px;
                }

                .whatsapp-button i {
                    font-size: 18px;
                }
            }


            /* Product Title */
            .product-title {
                font-size: 24px;
                font-weight: 700;
                color: #557DBF;
                margin-bottom: 10px;
            }

            /* Ratings */
            .ratings-area {
                margin-bottom: 15px;
                font-size: 14px;
                color: #DD4637;
            }

            /* Product Price */
            .product-price {
                font-size: 22px;
                font-weight: 700;
                color: #DD4637 !important;
            }

            /* Product Summary */
            .product-summary {
                margin-top: 20px;
                margin-bottom: 20px;
                font-size: 15px;
                color: #555;
            }

            /* Labels (Categories, Brand, SKU) */
            .product-details-label {
                color: #DD4637;
                margin-right: 5px;
                font-weight: 600;
                font-size: 15px;
            }

            /* Categories, Brand links */
            .product-details a {
                color: #557DBF;
                font-weight: 500;
                margin-right: 3px;
                text-decoration: none;
            }

            .product-details a:hover {
                text-decoration: underline;
            }

            /* Attribute section */
            .attribute-name {
                display: inline-block;
                margin: 4px 0 5px;
                color: #557DBF;
                font-weight: 600;
                color: black !important;
                font-size: 18px !important;
            }

            .attribute-value-wrapper {
                margin-bottom: 15px;
                margin-top: 15px;
            }
            
            

            /* Quantity & Add to Cart */
            .product-add-to-cart {
                margin-top: 25px;
                margin-bottom: 15px;
            }

            /* Wishlist & Compare */
            .product-wishlist .add-to-wishlist-btn,
            .product-wishlist .addToCompare {
                padding: 10px 15px;
                border: 1px solid #ccc;
                color: #557DBF;
                background: transparent;
                border-radius: 6px;
                transition: 0.3s ease;
                font-weight: 500;
            }

            .product-wishlist .add-to-wishlist-btn:hover,
            .product-wishlist .addToCompare:hover,
            .product-wishlist .add-to-wishlist-btn.active,
            .product-wishlist .addToCompare.active {
                background: #DD4637;
                color: #fff;
                border-color: #DD4637;
            }

            /* Buy Now and Add to Cart button */
            .addToCart,
            .buy-now-btn {
                background-color:rgb(26, 56, 104);
                color: #fff;
                border: none;
                padding: 10px 18px;
                font-size: 14px;
                font-weight: 600;
                border-radius: 5px;
                transition: all 0.3s ease;
            }

            .addToCart:hover,
            .buy-now-btn:hover {
                background-color: #DD4637;
            }

            /* View details button */
            .btn.outline {
                border: 1px solid #557DBF;
                color: #557DBF;
                padding: 8px 16px;
                font-size: 14px;
                border-radius: 5px;
                margin-top: 20px;
            }

            .btn.outline:hover {
                background-color: #557DBF;
                color: white;
            }

        </style>

        @if ($product->product_type == Status::PRODUCT_TYPE_VARIABLE && $product->attributes->count())
            <div class="product-attribute position-relative">
                <div class="ajax-preloader d-none"></div>
                @foreach ($product->attributes as $attribute)
                    @php
                        $attributeValues = $product->attributeValues->where('attribute_id', $attribute->id);
                        $attributeTypeClass =
                            $attribute->type == Status::ATTRIBUTE_TYPE_TEXT
                                ? 'product-size-area'
                                : 'product-color-area';
                    @endphp

                    <div class="attribute-value-wrapper attributeValueArea">
                        <span class="attribute-name fw-600">{{ __(@$attribute->name) }}:</span>

                        @foreach ($attributeValues as $attributeValue)
                            @php
                                $data = ['id' => $attributeValue->id, 'type' => $attribute->type];
                            @endphp
                            <button class="attribute-value attributeBtn" data-attribute='@json($data)'
                                data-media_id="{{ $attributeValue->pivot->media_id }}">
                                @if ($attribute->type == Status::ATTRIBUTE_TYPE_TEXT)
                                    <span class="text-attribute">{{ $attributeValue->value }}</span>
                                @elseif($attribute->type == Status::ATTRIBUTE_TYPE_COLOR)
                                    <span class="color-attribute colorAttribute"
                                        data-color="{{ $attributeValue->value }}"
                                        style="background:#{{ $attributeValue->value }}"></span>
                                @else
                                    <span class="color-attribute bg--img"
                                        data-media_id="{{ $attributeValue->pivot->media_id }}"
                                        data-background="{{ getImage(getFilePath('attribute') . '/' . $attributeValue->value) }}" />
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endif

        <div class="d-flex flex-column">
            <div class="product-add-to-cart">
                {{-- quantity input (unchanged) --}}
                <x-frontend.quantity-input :isDigital="$product->is_downloadable" data-update="no" />

                {{-- --- Add to Cart (keeps old behaviour) --- --}}
                <button type="button" class="btn btn--base btn--sm addToCart flex-shrink-0"
                    data-id="{{ $product->id }}" data-product_type="{{ $product->product_type }}"
                    @disabled(!$product->salePrice())>
                    @lang('Add to Cart')
                </button>

                {{-- --- Buy Now (new ➜ posts then redirects) --- --}}
                <button type="button" class="btn btn--base btn--sm addToCart buy-now-btn flex-shrink-0 ms-2"
                    data-id="{{ $product->id }}" data-product_type="{{ $product->product_type }}"
                    @disabled(!$product->salePrice())>
                    @lang('Buy Now')
                </button>
            </div>
            <div class="product-wishlist d-flex gap-2 mt-3 mb-3">

                @if (gs('product_wishlist'))
                    <button class="add-to-wishlist-btn @if (checkWishList($product->id)) active @endif addToWishlist"
                        data-id="{{ $product->id }}">
                        <span class="wish-icon"></span> @lang('Wishlist')
                    </button>
                @endif

                @if (gs('product_compare'))
                    <button class="add-to-wishlist-btn  @if (checkCompareList($product->id)) active @endif addToCompare"
                        data-id="{{ $product->id }}">
                        <i class="las la-exchange-alt compare-icon"></i> @lang('Compare')
                    </button>
                @endif
            </div>
            
        </div>

        @if ($quickView)
            <div>
                <a class="btn btn-sm btn--base mt-2" href="{{ $product->link() }}">@lang('View Details')</a>
            </div>
        @endif


        @if (!$quickView)
            <x-frontend.product-sharer :product="$product" />
        @endif
        <div class="card mt-4 border border-danger">
            <div class="card-body text-dark p-3">
                <p class="mb-0" style="font-size: 16px;">
                    প্রযুক্তি যেহেতু দ্রুত পরিবর্তনশীল, তাই যেকোনো সময় মূল্য, স্টক ও ডেলিভারি সম্পর্কিত তথ্য পরিবর্তিত হতে পারে। 
                    অর্ডার করার পূর্বে মূল্য, স্টক ও ডেলিভারি সম্পর্কে জানতে যোগাযোগ করুন 
                    <strong>01821051012</strong> নাম্বারে।
                </p>

            </div>
        </div>

    </div>
</div>
</div>

@if (!$quickView)
    @push('script')
    @endif
    <script src="{{ asset($activeTemplateTrue . 'js/product_details.js') }}?{{ time() }}"></script>

    <script>
        "use strict";

        $('.product-details-container').productDetails({
            productId: @json($product->id),
            totalAttributes: @json($product->attributes->count()),
            stockQuantity: @json($product->totalInStock()),
            trackInventory: @json($product->track_inventory == Status::YES),
            showStockQuantity: @json($product->show_stock && $product->track_inventory && $product->product_type == Status::PRODUCT_TYPE_SIMPLE),
            variantImageLoadUrl: "{{ route('product.variant.image', [':productId', ':attributeId']) }}",
            checkStockUrl: "{{ route('product.variant.stock', $product->slug) }}"
        });
    </script>

    @if (!$quickView)
    @endpush
@endif
