@php
$headerThreeKeys = array_keys((array) $headerThree->group);

$firstLetters = array_map(function ($key) {
return $key[0];
}, $headerThreeKeys);

$layoutClass = 'primary-menu-' . implode('', $firstLetters);

$headerColor = $headerThree?->background_color ?? gs('base_color');

$featuredCategories = \App\Models\Category::where('feature_in_banner', 1)
    ->with('subcategories.allSubcategories')
    ->orderBy('position', 'ASC')  // <-- position অনুযায়ী সাজানো
    ->get();
$mainMenuLimit = 12; // Define the limit for main menu items
@endphp

@if (@$headerThree->status == 'on')
<div class="header-bottom @if (gs('homepage_layout') == 'full_width_banner') without-category @endif" style="background-color: #{{ $headerColor }}">
    <div class="container">
        <div class="row g-0">
            <div class="header-bottom-wrapper {{ $layoutClass }}">
                <nav class="navbar navbar-expand-lg navbar-light py-0 w-100">
                    <div class="container-fluid px-0">
                        <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMegaMenu" aria-controls="navbarMegaMenu" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>

                        <li class="d-lg-none " style="list-style: none;">
                            <a href="{{ route('multi_express.deal.index') }}" 
                            class="ecommerce deal-btn"
                            style="background: linear-gradient(135deg, #423fce, #db4437); color: #fff; border: none; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block; transition: all 0.3s ease;">
                                <span class="ecommerce__icon">
                                    <span class="ecommerce__is deal-count d-none"></span>
                                </span>
                                <span class="" style="color:#fff;">@lang('Deals')</span>
                            </a>
                        </li>

                        <div class="collapse navbar-collapse" id="navbarMegaMenu">
                            <ul class="main-menu navbar-nav flex-lg-row flex-column">
                                @foreach ($featuredCategories as $index => $category)
                                @if ($index < $mainMenuLimit) {{-- Display first 7 categories directly --}}
                                    <li class="menu-item nav-item">
                                    <a class="nav-link" href="{{ $category->shopLink() }}">
                                        {{ $category->name }}
                                        @if ($category->subcategories->count())
                                        <i class="fas fa-chevron-down ms-1 d-inline-block"></i>
                                        @endif
                                    </a>

                                    @if ($category->subcategories->count())
                                    <ul class="sub-menu">
                                        @foreach ($category->subcategories as $subcategory)
                                        <li class="menu-item">
                                            <a href="{{ $subcategory->shopLink() }}">
                                                {{ $subcategory->name }}
                                                @if ($subcategory->allSubcategories->count())
                                                <i class="fas fa-chevron-right ms-1 d-inline-block"></i>
                                                @endif
                                            </a>

                                            @if ($subcategory->allSubcategories->count())
                                            <ul class="sub-menu">
                                                @foreach ($subcategory->allSubcategories as $child)
                                                <li class="menu-item">
                                                    <a href="{{ $child->shopLink() }}">{{ $child->name }}</a>
                                                </li>
                                                @endforeach
                                            </ul>
                                            @endif
                                        </li>
                                        @endforeach
                                    </ul>
                                    @endif
                                    </li>
                                    @endif
                                    @endforeach

                                   
                                    @if ($featuredCategories->count() > $mainMenuLimit) {{-- "View More" menu item --}}
                                    <li class="menu-item nav-item">
                                        <a class="nav-link" href="javascript:void(0)">
                                            View More
                                            <i class="fas fa-chevron-down ms-1 d-inline-block"></i>
                                        </a>
                                        <ul class="sub-menu">
                                            @foreach ($featuredCategories as $index => $category)
                                            @if ($index >= $mainMenuLimit) {{-- Display remaining categories in "View More" dropdown --}}
                                            <li class="menu-item">
                                                <a href="{{ $category->shopLink() }}">
                                                    {{ $category->name }}
                                                    @if ($category->subcategories->count())
                                                    <i class="fas fa-chevron-right ms-1 d-inline-block"></i>
                                                    @endif
                                                </a>
                                                @if ($category->subcategories->count())
                                                {{-- This submenu will open to the left --}}
                                                <ul class="sub-menu" style="left: -100% !important; right: auto !important; margin-left: -1px !important;">
                                                    @foreach ($category->subcategories as $subcategory)
                                                    <li class="menu-item">
                                                        <a href="{{ $subcategory->shopLink() }}">
                                                            {{ $subcategory->name }}
                                                            @if ($subcategory->allSubcategories->count())
                                                            <i class="fas fa-chevron-right ms-1 d-inline-block"></i>
                                                            @endif
                                                        </a>
                                                        @if ($subcategory->allSubcategories->count())
                                                        {{-- This nested submenu will also open to the left --}}
                                                        <ul class="sub-menu" style="left: -100% !important; right: auto !important; margin-left: -1px !important;">
                                                            @foreach ($subcategory->allSubcategories as $child)
                                                            <li class="menu-item">
                                                                <a href="{{ $child->shopLink() }}">{{ $child->name }}</a>
                                                            </li>
                                                            @endforeach
                                                        </ul>
                                                        @endif
                                                    </li>
                                                    @endforeach
                                                </ul>
                                                @endif
                                            </li>
                                            @endif
                                            @endforeach
                                        </ul>
                                    </li>
                                    @endif
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</div>

@endif
@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.innerWidth <= 768) {
        document.querySelectorAll('.main-menu .menu-item > a').forEach(function(link) {
            let parentLi = link.parentElement;
            if (parentLi.querySelector('.sub-menu')) {
                link.addEventListener('click', function(e) {
                    // লিঙ্কে যাও়া বন্ধ করো
                    e.preventDefault();
                    // াবমেনু টগল রো
                    let submenu = parentLi.querySelector('.sub-menu');
                    if (submenu.style.display === 'block') {
                        submenu.style.display = 'none';
                    } else {
                        submenu.style.display = 'block';
                    }
                });
            }
        });
    }
});
</script>
@endpush

@push('style')
<style>

    .main-menu {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-wrap: wrap;
    }

    .main-menu .menu-item {
        position: relative;
    }

    .main-menu .menu-item>a {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 18px;
        color: rgb(255, 255, 255);
        text-decoration: none;
        font-weight: 600;
        transition: background 0.3s;
        background-color: #557DBF;
        /* Main menu background - blue */
    }


    .menu-item .nav-item:hover>a {
        background: #dd4637;
    }

    /* Sub Menu */
    .sub-menu {
        position: absolute;
        top: 100%;
        left: 0;
        min-width: 200px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        display: none;
        z-index: 1000;
    }

    .menu-item:hover>.sub-menu {
        display: block;
        color: #dd4637;
        /* Red highlight on hover */
    }

    .sub-menu .menu-item {
        position: relative;
    }

    .sub-menu a {
        display: flex;
        justify-content: space-between;
        padding: 10px 15px;
        color: #333;
        text-decoration: none;
        transition: background 0.3s;
    }

    .sub-menu a:hover {
        background: #f5f5f5;
        color: #dd4637;
        /* Red highlight on hover */
    }

    .sub-menu .sub-menu {
        top: 0;
        left: 100%;
        margin-left: 1px;
    }




    /* Responsive */
    @media (max-width: 991px) {
        .main-menu {
            flex-direction: column;
            background-color: #557DBF;
            /* Mobile background - blue */
        }

        .main-menu .menu-item>a {
            color: #fff;
            padding: 10px 16px;
        }

        .sub-menu {
            position: static;
            box-shadow: none;
            background: #f2f2f2;
            display: none;
        }

        .menu-item:hover>.sub-menu {
            display: block;
        }

        .sub-menu a {
            color: #333;
        }

        .sub-menu a:hover {
            background: #ddd;
            color: #dd4637;
        }

        .sub-menu .sub-menu {
            margin-left: 15px;
            position: static;
        }

    }
</style>
@endpush