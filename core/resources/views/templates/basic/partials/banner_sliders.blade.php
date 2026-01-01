@php
    $sliders = getContent('banner.element');
    $sliders1 = getContent('banner1.content');
    $sliders2 = getContent('banner2.content');
@endphp

<div class="container">
    <div class="row gy-3">
        <!-- Slider Section -->
        <div class="col-lg-8 col-md-12">
            @if ($sliders->isNotEmpty())
                <div class="slider-wrapper overflow-hidden rounded--5 w-100 h-100">
                    <div class="banner-slider owl-theme owl-carousel">
                        @foreach ($sliders as $slider)
                            <div class="slide-item">
                                <a href="{{ @$slider->data_values->link }}" class="d-block w-100">
                                    <img src="{{ frontendImage('banner', @$slider->data_values->slider, '990x480') }}" alt="slider-image" class="img-fluid w-100 rounded-3">
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Banner Section 1 & 2 stacked vertically on all screens -->
        <div class="col-lg-4 col-md-12 d-flex flex-column gap-3">
            @if ($sliders1->isNotEmpty())
                <div class="banner-item overflow-hidden rounded--5 w-100">
                    <a href="{{ @$sliders1->first()->data_values->link }}" class="d-block w-100">
                        <img src="{{ frontendImage('banner1', @$sliders1->first()->data_values->banner_image, '400x240') }}" alt="{{ $sliders1->first()->data_values->banner_heading }}" class="img-fluid w-100 rounded-3">
                    </a>
                </div>
            @endif

            @if ($sliders2->isNotEmpty())
                <div class="banner-item overflow-hidden rounded--5 w-100">
                    <a href="{{ @$sliders2->first()->data_values->link }}" class="d-block w-100">
                        <img src="{{ frontendImage('banner2', @$sliders2->first()->data_values->banner_image, '400x240') }}" alt="{{ $sliders2->first()->data_values->banner_heading }}" class="img-fluid w-100 rounded-3">
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('script')
    <script>
        (function($) {
            "use strict";
            $(".banner-slider").owlCarousel({
                items: 1,
                loop: true,
                autoplay: true,
                nav: false,
                dots: false,
                animateOut: 'fadeOut'
            });
        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .banner-item img, .slide-item img {
            object-fit: cover;
            height: 100%;
        }

        @media (max-width: 991px) {
            .slider-wrapper {
                height: auto !important;
            }
        }

        @media (min-width: 992px) {
            .slider-wrapper {
                height: 500px !important;
            }

            .banner-item {
                height: 240px !important;
            }
        }
    </style>
@endpush
