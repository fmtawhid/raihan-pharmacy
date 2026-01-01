<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Frontend;
use App\Models\Language;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Page;
use App\Models\User;
use App\Models\Subscriber;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use App\Models\MultiExpressDeal;
use App\Models\MultiExpressCategory;
class SiteController extends Controller
{
    public function index()
    {
        $pageTitle   = 'Home';
        $sections    = Page::where('tempname', activeTemplate())->where('slug', '/')->first();
        $seoContents = $sections->seo_content;
        $seoImage    = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        return view('Template::home', compact('pageTitle', 'sections', 'seoContents', 'seoImage'));
    }

    public function about()
    {
        $pageTitle = 'About Us';
        $sections = Page::where('tempname', activeTemplate())->where('slug', 'about-us')->first();
        $seoContents = $sections->seo_content;
        $seoImage = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        return view('Template::about', compact('pageTitle', 'sections', 'seoContents', 'seoImage'));
    }

    public function faq()
    {
        $pageTitle = 'FAQ';
        $sections = Page::where('tempname', activeTemplate())->where('slug', 'faq')->first();
        $seoContents = $sections->seo_content;
        $seoImage = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        return view('Template::faq', compact('pageTitle', 'sections', 'seoContents', 'seoImage'));
    }

    public function brands()
    {
        $pageTitle = 'Brands';
        $brands = Brand::orderBy('name')->get();
        $sections = Page::where('tempname', activeTemplate())->where('slug', 'brands')->first();
        $seoContents = $sections->seo_content;
        $seoImage = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        return view('Template::brands', compact('pageTitle', 'brands', 'sections', 'seoContents', 'seoImage'));
    }

    public function categories()
    {
        $pageTitle = 'Categories';
        $categories = Category::isParent()->with('allSubcategories')->orderBy('name')->get();

        $sections = Page::where('tempname', activeTemplate())->where('slug', 'categories')->first();
        $seoContents = $sections->seo_content;
        $seoImage = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        return view('Template::categories', compact('pageTitle', 'categories', 'sections', 'seoContents', 'seoImage'));
    }

    public function offers()
    {
        $pageTitle = 'All Offers';
        $offers = Offer::running()->get();

        $sections = Page::where('tempname', activeTemplate())->where('slug', 'offers')->first();
        $seoContents = $sections->seo_content;
        $seoImage = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;

        return view('Template::offers', compact('pageTitle', 'offers', 'sections', 'seoContents', 'seoImage'));
    }

    public function offerProducts($id)
    {
        try {
            $id = decrypt($id);
            $offer = Offer::running()->with('products')->findOrFail($id);
            $pageTitle =   $offer->name . ' - Products';
            return view('Template::offer_products', compact('pageTitle', 'offer'));
        } catch (\Throwable $th) {
            abort(404);
        }
    }

    public function trackOrder()
    {
        $pageTitle = 'Order Tracking';
        return view('Template::order_track', compact('pageTitle'));
    }

    public function contact()
    {
        $pageTitle = "Contact Us";
        $user = auth()->user();
        $sections = Page::where('tempname', activeTemplate())->where('slug', 'contact')->first();
        $seoContents = $sections->seo_content;
        $seoImage = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        return view('Template::contact', compact('pageTitle', 'user', 'sections', 'seoContents', 'seoImage'));
    }


    public function contactSubmit(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'subject' => 'required|string|max:255',
            'message' => 'required',
        ]);

        $request->session()->regenerateToken();

        if (!verifyCaptcha()) {
            $notify[] = ['error', 'Invalid captcha provided'];
            return back()->withNotify($notify);
        }

        $random = getNumber();

        $ticket = new SupportTicket();
        $ticket->user_id = auth()->id() ?? 0;
        $ticket->name = $request->name;
        $ticket->email = $request->email;
        $ticket->priority = Status::PRIORITY_MEDIUM;


        $ticket->ticket = $random;
        $ticket->subject = $request->subject;
        $ticket->last_reply = Carbon::now();
        $ticket->status = Status::TICKET_OPEN;
        $ticket->save();

        $adminNotification = new AdminNotification();
        $adminNotification->user_id = auth()->user() ? auth()->user()->id : 0;
        $adminNotification->title = 'A new contact message has been submitted';
        $adminNotification->click_url = urlPath('admin.ticket.view', $ticket->id);
        $adminNotification->save();

        $message = new SupportMessage();
        $message->support_ticket_id = $ticket->id;
        $message->message = $request->message;
        $message->save();

        $notify[] = ['success', 'Ticket created successfully!'];

        return to_route('ticket.view', [$ticket->ticket])->withNotify($notify);
    }

    public function policyPages($slug)
    {
        $policy = Frontend::where('slug', $slug)->where('data_keys', 'policy_pages.element')->firstOrFail();
        $pageTitle = $policy->data_values->title;
        $seoContents = $policy->seo_content;
        $seoImage = @$seoContents->image ? frontendImage('policy_pages', $seoContents->image, getFileSize('seo'), true) : null;
        return view('Template::policy', compact('policy', 'pageTitle', 'seoContents', 'seoImage'));
    }

    public function changeLanguage($lang = null)
    {
        $language = Language::where('code', $lang)->first();
        if (!$language) $lang = 'en';
        session()->put('lang', $lang);
        return back();
    }


    public function cookieAccept()
    {
        Cookie::queue('gdpr_cookie', gs('site_name'), 43200);
    }

    public function cookiePolicy()
    {
        $cookieContent = Frontend::where('data_keys', 'cookie.data')->first();
        abort_if($cookieContent->data_values->status != Status::ENABLE, 404);
        $pageTitle = 'Cookie Policy';
        $cookie = Frontend::where('data_keys', 'cookie.data')->first();
        return view('Template::cookie', compact('pageTitle', 'cookie'));
    }

    public function placeholderImage($size = null)
    {
        $imgWidth = explode('x', $size)[0];
        $imgHeight = explode('x', $size)[1];
        $text = $imgWidth . '×' . $imgHeight;
        $fontFile = realpath('assets/font/solaimanLipi_bold.ttf');
        $fontSize = round(($imgWidth - 50) / 8);
        if ($fontSize <= 9) {
            $fontSize = 9;
        }
        if ($imgHeight < 100 && $fontSize > 30) {
            $fontSize = 30;
        }

        $image     = imagecreatetruecolor($imgWidth, $imgHeight);
        $colorFill = imagecolorallocate($image, 100, 100, 100);
        $bgFill    = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $bgFill);
        $textBox = imagettfbbox($fontSize, 0, $fontFile, $text);
        $textWidth  = abs($textBox[4] - $textBox[0]);
        $textHeight = abs($textBox[5] - $textBox[1]);
        $textX      = ($imgWidth - $textWidth) / 2;
        $textY      = ($imgHeight + $textHeight) / 2;
        header('Content-Type: image/jpeg');
        imagettftext($image, $fontSize, 0, $textX, $textY, $colorFill, $fontFile, $text);
        imagejpeg($image);
        imagedestroy($image);
    }

    public function maintenance()
    {
        $pageTitle = 'Maintenance Mode';
        if (gs('maintenance_mode') == Status::DISABLE) {
            return to_route('home');
        }
        $maintenance = Frontend::where('data_keys', 'maintenance.data')->first();
        return view('Template::maintenance', compact('pageTitle', 'maintenance'));
    }

    public function addSubscriber(Request $request)
    {

        if(!(gs('subscriber_module'))) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->all()
            ]);
        }

        $subscribe = Subscriber::where('email', $request->email)->first();
        if (!$subscribe) {
            $subscribe = new Subscriber();
            $subscribe->email = $request->email;
            $subscribe->save();
            return response()->json([
                'status' => true,
                'message' => trans('Subscribed successfully')
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => trans('Already subscribed')
            ]);
        }
    }

    public function getOrderTrackData(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'order_number' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $orderData = Order::isValidOrder()->where('order_number', $request->order_number)->first();

        if (!$orderData) {
            $notify = 'No order found with this order number';
            return response()->json(['error' => $notify]);
        }

        $paymentStatus = $orderData->payment_status;
        $status = $orderData->status;
        return response()->json(['success' => true, 'payment_status' => $paymentStatus, 'status' => $status]);
    }
  
  

    /* -------- All Deals (Cart/List) -------- */
    public function allDeals()
    {
        $pageTitle = 'All Express Deals';

        // SEO
        $seoContents = (object) [
            'social_title' => 'All Express Deals – Best Offers & Discounts',
            'title' => 'All Express Deals',
            'image' => null
        ];

        $categories = MultiExpressCategory::where('status','active')->get();
        $deals = MultiExpressDeal::with('category')
            ->whereIn('status', ['active', 'upcoming'])
            ->latest()
            ->paginate(20);


        return view('Template::all_deals', compact('deals','categories','pageTitle','seoContents'));
    }

    // Deals by Category (ID)
    public function dealsByCategory($id)
    {
        $category = MultiExpressCategory::findOrFail($id);

        $pageTitle = 'Deals in Category: ' . $category->name;

        $seoContents = (object) [
            'social_title' => 'Deals in Category – ' . $category->name,
            'title' => 'Deals in Category',
            'image' => null
        ];

        $categories = MultiExpressCategory::where('status','active')->get();

        $deals = MultiExpressDeal::with('category')
                ->where('category_id', $category->id)
                ->whereIn('status', ['active', 'upcoming'])
                ->latest()
                ->paginate(20);


        return view('Template::all_deals', compact('deals','categories','pageTitle','seoContents','category'));
    }




    /* -------- Single Deal -------- */
    // public function showDeal($id)
    // {
    //     try {
    //         $deal = MultiExpressDeal::where('status', 'active')
    //             ->with([
    //                 'category',
    //                 'pricingTiers',
    //                 'deliveryOptions',
    //                 'orders' => fn($q) => $q->latest()->limit(5)
    //             ])
    //             ->findOrFail($id);

    //         $pageTitle = 'Deal Details: ' . $deal->title;

    //         // SEO content add
    //         $seoContents = (object) [
    //             'social_title' => $deal->title . ' - Best Price, Fast Delivery',
    //             'image'        => $deal->image ?? null
    //         ];

    //         return view('Template::show_deal', compact('deal', 'pageTitle', 'seoContents'));

    //     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    //         abort(404, 'Deal not found or is not available');
    //     }
    // }

    public function showDeal($id)
    {
        try {
            $deal = MultiExpressDeal::where('status', 'active')
                ->with([
                    'category',
                    'pricingTiers',
                    'deliveryOptions',
                    'orders' => fn($q) => $q->latest()->limit(5)
                ])
                ->findOrFail($id);

            $pageTitle = 'Deal Details: ' . $deal->title;

            // SEO content add
            $seoContents = (object) [
                'social_title' => $deal->title . ' - Best Price, Fast Delivery',
                'image'        => $deal->image ?? null
            ];

            // Related deals: same category, exclude current, limit 6
            $relatedDeals = MultiExpressDeal::where('category_id', $deal->category_id)
                ->where('id', '!=', $deal->id)
                ->where('status', 'active')
                ->latest()
                ->take(6)
                ->get();

            return view('Template::show_deal', compact('deal', 'pageTitle', 'seoContents', 'relatedDeals'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Deal not found or is not available');
        }
    }

    // public function joinDeal(Request $request, MultiExpressDeal $deal)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:150',
    //         'phone' => 'required|string|max:50',
    //         'address' => 'required|string|max:255',
    //         'quantity' => 'required|integer|min:1|max:' . ($deal->max_capacity ?? 100),
    //         'pricing' => 'required',
    //         'delivery_option_id' => 'nullable|exists:multi_express_delivery_options,id',
    //     ]);

    //     $quantity = $request->quantity;


    //     // Determine price per item
    //     if($request->pricing){
    //         $pricePerItem = floatval($request->pricing); // selected radio price
    //     } else {
    //         $pricePerItem = $deal->deal_price; // fallback default deal price
    //     }


    //     // Calculate total price
    //     $totalPrice = $pricePerItem * $quantity;

    //     // Delivery charge (fixed, not per item)
    //     $deliveryCharge = 0;
    //     if($request->delivery_option_id){
    //         $deliveryOption = $deal->deliveryOptions()->find($request->delivery_option_id);
    //         if($deliveryOption){
    //             $deliveryCharge = $deliveryOption->charge_per_item; // fixed charge
    //             $totalPrice += $deliveryCharge;
    //         }
    //     }

    //     // Save order
    //     $order = $deal->orders()->create([
    //         'name' => $request->name,
    //         'contact_no' => $request->phone, // phone column
    //         'address' => $request->address,
    //         'quantity' => $quantity,
    //         'delivery_option_id' => $request->delivery_option_id,
    //         'price_per_item' => $pricePerItem, // save numeric value
    //         'total_price' => $totalPrice,
    //     ]);

    //     return redirect()->back()->with('success', __('You have successfully joined the deal!'));
    // }


    public function joinDeal(Request $request, MultiExpressDeal $deal)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'required|string|max:50',
            'email' => 'required|email',
            'password' => 'nullable|min:6',
            'address' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1|max:' . ($deal->max_capacity ?? 100),
            'pricing' => 'required',
            'delivery_option_id' => 'nullable|exists:multi_express_delivery_options,id',
        ]);

        // Step 1: Find existing user or create new
        if (auth()->check()) {
            $user = auth()->user();
        } else {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                $user = User::create([
            
                    'email' => $request->email,
                    'password' => bcrypt($request->password ?? '123456'),
                    'mobile' => $request->phone,
                ]);
            }

            auth()->login($user);
        }

        $quantity = $request->quantity;

        // Price per item
        $pricePerItem = $request->pricing ? floatval($request->pricing) : $deal->deal_price;

        // Total price
        $totalPrice = $pricePerItem * $quantity;

        // Delivery charge
        $deliveryCharge = 0;
        if ($request->delivery_option_id) {
            $deliveryOption = $deal->deliveryOptions()->find($request->delivery_option_id);
            if ($deliveryOption) {
                $deliveryCharge = $deliveryOption->charge_per_item * $quantity;
                $totalPrice += $deliveryCharge;
            }
        }

        // Create order
        $order = $deal->orders()->create([
            'user_id' => $user->id,
            'name' => $request->name,
            'contact_no' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'quantity' => $quantity,
            'delivery_option_id' => $request->delivery_option_id,
            'price_per_item' => $pricePerItem,
            'total_price' => $totalPrice,
        ]);

        return redirect()->back()->with('success', __('You have successfully joined the deal!'));
    }



}
