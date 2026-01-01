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
        "@graph": [
          {
            "@type": "Organization",
            "name": "Multitech Bangladesh",
            "url": "https://multitech.com.bd/",
            "logo": "https://multitech.com.bd/assets/images/logo_icon/favicon.png",
            "sameAs": [
              "https://www.facebook.com/multitechbd",
              "https://www.linkedin.com/company/multitechbd"
            ]
          },
          {
            "@type": "WebSite",
            "name": "Multitech Bangladesh",
            "url": "https://multitech.com.bd/",
            "inLanguage": "en",
            "potentialAction": {
              "@type": "SearchAction",
              "target": "https://multitech.com.bd/products?search={search_term_string}",
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
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-0T77HDHBWJ');
    </script>

    <!-- Google Tag Manager (GTM) - First Snippet -->
    <script>
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(
        j,f);
        })(window,document,'script','dataLayer','GTM-PB67XXJD');
    </script>
    <!-- End Google Tag Manager -->

</head>

@php echo loadExtension('google-analytics') @endphp

<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PB67XXJD"
        height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->


    @unless(in_array(Route::currentRouteName(), ['product.detail']))
        <!-- SEO Content -->
        <div class="seo-text">
            <h1>{{ @$seoContents->social_title ?? $seo->social_title ?? ' Multitech Title'}}</h1>
            <p>{{ @$seoContents->social_description ?? $seo->social_description ?? ' Multitech Description'}}</p>
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
    @php
        $cookie = App\Models\Frontend::where('data_keys', 'cookie.data')->first();
    @endphp

    @if ($cookie->data_values->status == Status::ENABLE && !\Cookie::get('gdpr_cookie'))
        <div class="cookies-card text-center hide">
            <div class="cookies-card__icon bg--base">
                <i class="las la-cookie-bite"></i>
            </div>
            <p class="mt-4 cookies-card__content">{{ $cookie->data_values->short_desc }} <a href="{{ route('cookie.policy') }}" target="_blank" class="text--base">@lang('Learn more')</a></p>
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
