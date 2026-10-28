<!doctype html>
<html lang="{{ config('app.locale') }}" itemscope itemtype="http://schema.org/WebPage">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title> {{ @$seoContents->social_title ?? $seo->title ?? ''}} - {{ gs()->siteName(__()) }} </title>

    @include('partials.seo')
    <link type="image/x-icon" href="{{ siteFavicon() }}" rel="shortcut icon">

    <link href="{{ asset('assets/global/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/global/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/global/css/line-awesome.min.css') }}" rel="stylesheet" />

    @stack('style-lib')

    <link href="{{ asset($activeTemplateTrue . 'css/main.css') }}" rel="stylesheet">
    <link href="{{ asset($activeTemplateTrue . 'css/custom.css') }}" rel="stylesheet">
    <link href="{{ asset($activeTemplateTrue . 'css/color.php?color=' . gs('base_color')) }}" rel="stylesheet">
    @stack('head')

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@graph": [{
                "@type": "Organization",
                "name": "Rayhan Pharmacy Bangladesh",
                "url": "hhttps://rayhanpharmacy.com/",
                "logo": "hhttps://rayhanpharmacy.com/assets/images/logo_icon/favicon.png",
                "sameAs": [
                    "https://www.facebook.com/multitechbd",
                    "https://www.linkedin.com/company/multitechbd"
                ]
            },
            {
                "@type": "WebSite",
                "name": "Rayhan Pharmacy Bangladesh",
                "url": "hhttps://rayhanpharmacy.com/",
                "inLanguage": "en",
                "potentialAction": {
                    "@type": "SearchAction",
                    "target": "hhttps://rayhanpharmacy.com/products?search={search_term_string}",
                    "query-input": "required name=search_term_string"
                }
            }
        ]
    }
    </script>

    @stack('style')



    <!-- Google Site Verification -->
    <meta name="google-site-verification" content="Txy8zPdjp60qANGSXoiJcm_2RYQnezTeNoyLdfdlfuU" />

    <!-- Google Analytics (GA) -->
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G0T77HDHBWJ"></script>
    <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', 'G-0T77HDHBWJ');
    </script>

    <!-- Google Tag Manager (GTM) - First Snippet -->
    <script>
    (function(w, d, s, l, i) {
        w[l] = w[l] || [];
        w[l].push({
            'gtm.start': new Date().getTime(),
            event: 'gtm.js'
        });
        var f = d.getElementsByTagName(s)[0],
            j = d.createElement(s),
            dl = l != 'dataLayer' ? '&l=' + l : '';
        j.async = true;
        j.src =
            'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
        f.parentNode.insertBefore(
            j, f);
    })(window, document, 'script', 'dataLayer', 'GTM-PB67XXJD');
    </script>
    <!-- End Google Tag Manager -->

</head>

@php echo loadExtension('google-analytics') @endphp

<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PB67XXJD" height="0" width="0"
            style="display:none;visibility:hidden"></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->


    @unless(in_array(Route::currentRouteName(), ['product.detail']))
    <!-- SEO Content -->
    <div class="seo-text">
        <h1>{{ @$seoContents->social_title ?? $seo->social_title ?? ' Rayhan Pharmacy Bangladesh'}}</h1>
        <p>{{ @$seoContents->social_description ?? $seo->social_description ?? 'Rayhan Pharmacy Bangladesh'}}</p>
    </div>
    <style>
    .seo-text {
        position: absolute;
        left: -9999px;
        top: auto;
        width: 1px;
        height: 1px;
        overflow: hidden;
    }
    </style>
    @endunless
    @yield('app')

    <a class="scrollToTop" href="javascript:void(0)"><i class="las la-angle-up"></i></a>

    {{-- Popup Banner --}}
    @php
    $popupBanner = App\Models\Frontend::where('data_keys', 'popup_banner.data')->first();
    @endphp

    @if ($popupBanner && @$popupBanner->data_values->status == Status::ENABLE)
    <div class="popup-banner-overlay" id="popupBannerOverlay">
        <div class="popup-banner-card">
            <button type="button" class="popup-banner-close" id="popupBannerClose">&times;</button>
            @if(@$popupBanner->data_values->image)
            <div class="popup-banner-image">
                <img src="{{ getImage(getFilePath('popupBanner') . '/' . $popupBanner->data_values->image, getFileSize('popupBanner')) }}"
                    alt="{{ @$popupBanner->data_values->title }}">
            </div>
            @endif
            <!-- @if(@$popupBanner->data_values->title)
            <h3 class="popup-banner-title">{{ $popupBanner->data_values->title }}</h3>
            @endif
            @if(@$popupBanner->data_values->description)
            <p class="popup-banner-desc">{{ $popupBanner->data_values->description }}</p>
            @endif
            @if(@$popupBanner->data_values->btn_text && @$popupBanner->data_values->btn_url)
            <a href="{{ $popupBanner->data_values->btn_url }}"
                class="btn btn--base popup-banner-btn">{{ $popupBanner->data_values->btn_text }}</a>
            @endif -->
        </div>
    </div>

    <style>
    .popup-banner-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 99999;
        padding: 15px;
    }

    .popup-banner-card {
        background: #fff;
        border-radius: 12px;
        max-width: 500px;
        width: 100%;
        position: relative;
        text-align: center;
        /* padding: 30px 25px; */
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        animation: popupFadeIn 0.35s ease;
        max-height: 90vh;
        overflow-y: auto;
    }

    @keyframes popupFadeIn {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(20px);
        }

        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .popup-banner-close {
        position: absolute;
        top: 8px;
        right: 12px;
        background: none;
        border: none;
        font-size: 28px;
        line-height: 1;
        cursor: pointer;
        color: red;
        background-color: #fff;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background 0.2s;
    }

    .popup-banner-close:hover {
        background: rgba(0, 0, 0, 0.1);
    }

    .popup-banner-image {
        /* margin-bottom: 15px; */
    }

    .popup-banner-image img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
    }

    .popup-banner-title {
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 10px;
        color: #222;
    }

    .popup-banner-desc {
        font-size: 15px;
        color: #555;
        margin-bottom: 18px;
    }

    .popup-banner-btn {
        display: inline-block;
        padding: 10px 30px;
        font-size: 15px;
        border-radius: 6px;
    }
    </style>
    @endif
    {{-- End Popup Banner --}}

    @php
    $cookie = App\Models\Frontend::where('data_keys', 'cookie.data')->first();
    @endphp

    @if ($cookie->data_values->status == Status::ENABLE && !\Cookie::get('gdpr_cookie'))
    <div class="cookies-card text-center hide">
        <div class="cookies-card__icon bg--base">
            <i class="las la-cookie-bite"></i>
        </div>
        <p class="mt-4 cookies-card__content">{{ $cookie->data_values->short_desc }} <a
                href="{{ route('cookie.policy') }}" target="_blank" class="text--base">@lang('Learn more')</a></p>
        <div class="cookies-card__btn mt-4">
            <a class="btn btn--base w-100 policy h-45" href="javascript:void(0)">@lang('Allow')</a>
        </div>
    </div>
    @endif

    @stack('modal')

    <script src="{{ asset('assets/global/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue . 'js/jquery.validate.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue . 'js/lazyload.js') }}"></script>
    @stack('script-lib')
    <script src="{{ asset($activeTemplateTrue . 'js/main.js') }}"></script>
    <script>
    'use strict';
    $('.policy').on('click', function() {
        $.get("{{ route('cookie.accept') }}", function(response) {
            $('.cookies-card').addClass('d-none');
        });
    });

    // Popup Banner dismiss
    if (sessionStorage.getItem('popup_banner_dismissed')) {
        $('#popupBannerOverlay').remove();
    }
    $(document).on('click', '#popupBannerClose, #popupBannerOverlay', function(e) {
        if (e.target === this) {
            $('#popupBannerOverlay').fadeOut(200, function() {
                $(this).remove();
            });
            sessionStorage.setItem('popup_banner_dismissed', '1');
        }
    });
    </script>
    <x-frontend.visermart-script />

    @php echo loadExtension('tawk-chat') @endphp
    @include('partials.notify')
    @if (gs('pn'))
    @include('partials.push_script')
    @endif
    @stack('script')
</body>

</html>