@php
    $featuredCategories = \App\Models\Category::where('feature_in_banner', 1)->with('products.brand')->get();

    if (gs('homepage_layout') == 'sidebar_menu') {
        $categoriesToShow = [
            768 => ['items' => 5],
            992 => ['items' => 5],
            1199 => ['items' => 5],
        ];
        if (!$fixedBanner) {
            $categoriesToShow[1399] = ['items' => 6];
        }
    } else {
        $categoriesToShow = [
            768 => ['items' => 6],
            992 => ['items' => 7],
            1199 => ['items' => 8],
        ];
    }
@endphp

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

    /* Owl Navigation Container */
    .featured-category-slider .owl-nav {
        display: block !important;
    }

    /* Navigation arrows */
    .featured-category-slider .owl-prev,
    .featured-category-slider .owl-next {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.5) !important;
        color: white !important;
        display: flex !important;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        z-index: 1000;
        font-size: 18px !important;
    }

    .featured-category-slider .owl-prev:hover,
    .featured-category-slider .owl-next:hover {
        background: rgba(0, 0, 0, 0.7) !important;
    }

    .featured-category-slider .owl-prev {
        left: -50px;
    }

    .featured-category-slider .owl-next {
        right: -50px;
    }

    /* Hide arrows on mobile */
    @media (max-width: 1199px) {
        .featured-category-slider .owl-nav {
            display: none !important;
        }
    }
    
    /* Center slider content */
    .featured-category-slider .owl-stage {
        display: flex;
        align-items: center;
    }
</style>

@if (!blank($featuredCategories))
    <div class="overflow-hidden position-relative" style="padding: 0 50px;">
        <div class="featured-category-slider owl-theme owl-carousel">
            @foreach ($featuredCategories as $category)
                <div class="single-category p-2">
                    <x-dynamic-component :component="frontendComponent('category-card')" :category="$category" />
                </div>
            @endforeach
        </div>
    </div>

    @push('script')
        <script>
            (function($) {
                "use strict";
                const categoriesToShow = @json($categoriesToShow);

                const viewItems = {
                    0: {
                        items: 2,
                        margin: 12,
                        nav: false
                    },
                    425: {
                        items: 3,
                        margin: 12,
                        nav: false
                    },
                    575: {
                        items: 4,
                        margin: 12,
                        nav: false
                    },
                    ...categoriesToShow
                };

                // Enable navigation only on large screens
                viewItems[1199] = {
                    ...viewItems[1199],
                    nav: true
                };

                $(".featured-category-slider").owlCarousel({
                    margin: 16,
                    responsiveClass: true,
                    items: 3,
                    dots: false,
                    autoplay: true,
                    autoplayTimeout: 4000,
                    loop: true,
                    lazyLoad: true,
                    responsive: viewItems,
                    nav: true,
                    
                    onInitialized: function(event) {
                        // Adjust arrow positions after initialization
                        $('.featured-category-slider .owl-prev').css('left', '-50px');
                        $('.featured-category-slider .owl-next').css('right', '-50px');
                    }
                });
            })(jQuery);
        </script>
    @endpush
@endif