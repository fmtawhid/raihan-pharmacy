<?php

use App\Constants\Status;
use App\Models\Extension;
use App\Models\Frontend;
use App\Models\GeneralSetting;
use Carbon\Carbon;
use App\Lib\Captcha;
use App\Lib\CartManager;
use App\Lib\ClientInfo;
use App\Lib\CurlRequest;
use App\Lib\FileManager;
use App\Lib\ProductManager;
use App\Lib\ProductPriceManager;
use App\Lib\WishlistManager;
use App\Models\ProductCollection;
use App\Models\Language;
use App\Models\Offer;
use App\Models\PromotionalBanner;
use App\Notify\Notify;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Laramin\Utility\VugiChugi;

if (!function_exists('getBangladeshLocationData')) {
    function getBangladeshLocationData()
    {
        return [
            'divisions' => json_decode(file_get_contents(resource_path('data/bd-divisions.json')), true)['divisions'],
            'districts' => json_decode(file_get_contents(resource_path('data/bd-districts.json')), true)['districts'],
            'upazilas'  => json_decode(file_get_contents(resource_path('data/bd-upazilas.json')),  true)['upazilas'],
            'dhaka'     => json_decode(file_get_contents(resource_path('data/dhaka-city.json')),   true)['dhaka'],
        ];
    }
}


// app/Helpers/LocationHelper.php  (same file where getBangladeshLocationData lives)
// if (! function_exists('getBangladeshLocationName')) {
//     /**
//      * Return the name for a Bangladeshi location ID.
//      * Divisions have priority over districts, districts over upazilas,
//      * so duplicate IDs don’t produce the wrong label.
//      */
//     function getBangladeshLocationName(int $id): string
//     {
//         static $loc = null;          // cached for the request
//         if ($loc === null) {
//             $loc = getBangladeshLocationData();
//         }

//         // 1) division?
//         $name = collect($loc['divisions'])->firstWhere('id', $id)['name'] ?? null;
//         if ($name) return $name;

//         // 2) district?
//         $name = collect($loc['districts'])->firstWhere('id', $id)['name'] ?? null;
//         if ($name) return $name;

//         // 3) upazila (thana)?
//         return collect($loc['upazilas'])->firstWhere('id', $id)['name'] ?? '-';
//     }
// }
