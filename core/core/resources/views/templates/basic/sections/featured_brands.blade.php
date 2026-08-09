@php
$topBrands = \App\Models\Brand::featured()->get();
$content = getContent('featured_brands.content', true);
@endphp

<section class="my-60">
    <div class="container">
        @if (!blank($topBrands))
        <div class="section-header">
            <h5 class="title">{{ __(@$content->data_values->title) }}</h5>
        </div>

        <div class="brand-slider owl-theme owl-carousel">
            @foreach ($topBrands as $brand)
            <div class="small-card-item text-center">
                <x-dynamic-component :component="frontendComponent('brand-card')" :brand="$brand" />
            </div>
            @endforeach
        </div>
        @endif
    </div>
</section>

<style>
    .brand-slider .small-card-item {
        height: 150px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .brand-slider .small-card-item img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .brand-slider .small-card-item .brand-card {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    /* Hide arrows on mobile devices */
    @media (max-width: 767px) {

        .owl-prev,
        .owl-next {
            display: none !important;
        }
    }

    /* Position arrows on larger screens */
    @media (min-width: 768px) {
        .owl-prev {
            position: absolute;
            left: -50px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
        }

        .owl-next {
            position: absolute;
            right: -50px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
        }

        .owl-prev,
        .owl-next {
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            padding: 12px;
            font-size: 20px;
        }

        .owl-prev:hover,
        .owl-next:hover {
            background-color: rgba(0, 0, 0, 0.7);
        }
    }
</style>

@push('script')
<script>
    (function($) {
        "use strict";

        // $(".brand-slider").owlCarousel({
        //     margin: 16,
        //     responsiveClass: true,
        //     items: 7,
        //     nav: true,
        //     dots: false,
        //     autoplay: true,
        //     autoplayTimeout: 5000,
        //     loop: true,
        //     lazyLoad: false,
        //     responsive: {
        //         0: {
        //             items: 1,
        //             nav: false // Disable arrows on small screens
        //         },
        //         425: {
        //             items: 2,
        //             nav: false // Disable arrows on medium-small screens
        //         },
        //         768: {
        //             items: 3,
        //             nav: true // Enable arrows on tablets and larger
        //         },
        //         992: {
        //             items: 5
        //         },
        //         1200: {
        //             items: 7
        //         }
        //     },
        //     onInitialized: function() {
        //         // Hide arrows on mobile after initialization
        //         if ($(window).width() < 768) {
        //             $(".owl-prev, .owl-next").hide();
        //         }
        //     },
        //     onResize: function() {
        //         // Toggle arrows visibility based on screen size
        //         if ($(window).width() < 768) {
        //             $(".owl-prev, .owl-next").hide();
        //         } else {
        //             $(".owl-prev, .owl-next").show();
        //         }
        //     }
        // });
        $(".brand-slider").owlCarousel({
            margin: 16,
            responsiveClass: true,
            items: 7,
            nav: true,
            dots: false,
            autoplay: true,
            autoplayTimeout: 5000,
            loop: true,
            lazyLoad: true,
            responsive: {
                0: {
                    items: 1,
                    nav: false
                },
                425: {
                    items: 2,
                    nav: false
                },
                768: {
                    items: 3,
                    nav: true
                },
                992: {
                    items: 5
                },
                1200: {
                    items: 7
                }
            },
            onTranslated: function(event) {
                // লুপের ডুপ্লিকেট ইমেজ গুলো লোড করাও
                $(".owl-item.active img[data-src]").each(function() {
                    $(this).attr("src", $(this).data("src")).removeAttr("data-src");
                });
            }
        });

    })(jQuery);
</script>
@endpush