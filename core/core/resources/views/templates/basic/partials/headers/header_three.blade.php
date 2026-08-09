@php
$headerThreeKeys = array_keys((array) $headerThree->group);

$firstLetters = array_map(function ($key) {
return $key[0];
}, $headerThreeKeys);

$layoutClass = 'primary-menu-' . implode('', $firstLetters);

$headerColor = $headerThree?->background_color ?? gs('base_color');

$featuredCategories = \App\Models\Category::where('feature_in_banner', 1)
->with('subcategories.allSubcategories')
->orderBy('position', 'ASC')
->get();
$mainMenuLimit = 12;

$allCategories = \App\Models\Category::orderBy('name','ASC')->get();
@endphp

@if (@$headerThree->status == 'on')
<div class="header-bottom @if (gs('homepage_layout') == 'full_width_banner') without-category @endif"
    style="background-color: #{{ $headerColor }}">
    <div class="container">
        <div class="row g-0">
            <div class="header-bottom-wrapper {{ $layoutClass }} d-flex align-items-center">
                <!-- Search Bar - Left Side -->
                <div class="header-bottom-search d-none d-lg-flex">
                    <form action="{{ route('product.all') }}" method="GET" class="header-bottom-search-form">
                        <div class="header-bottom-search-inner">
                            <select name="category" id="headerCategorySelect" class="header-bottom-category-select">
                                <option value="">@lang('All Categories')</option>
                                @foreach ($allCategories as $cat)
                                <option value="{{ $cat->shopLink() }}" data-id="{{ $cat->id }}" @selected(request()->category ==
                                    $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="search" class="header-bottom-search-input"
                                value="{{ request()->search }}" placeholder="@lang('I am shopping for')...">
                            <button type="submit" class="header-bottom-search-btn"><i
                                    class="las la-search"></i></button>
                        </div>
                    </form>
                </div>

                <!-- <nav class="navbar navbar-expand-lg navbar-light py-0 flex-grow-1"> -->
                <nav class="navbar navbar-expand-lg navbar-light py-0">
                    <div class="container-fluid px-0">
                        <div class="d-lg-none mobile-header-controls">
                            <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse"
                                data-bs-target="#navbarMegaMenu" aria-controls="navbarMegaMenu" aria-expanded="false"
                                aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>

                            <form action="{{ route('product.all') }}" method="GET" class="mobile-search-form-inline">
                                <div class="mobile-search-inner-inline">
                                    <input type="text" name="search" class="mobile-search-input-inline" value="{{ request()->search }}" placeholder="@lang('Search...')">
                                    <button type="submit" class="mobile-search-btn-inline"><i class="las la-search"></i></button>
                                </div>
                            </form>
                        </div>

                        <div class="collapse navbar-collapse" id="navbarMegaMenu">
                            <ul class="main-menu navbar-nav flex-lg-row flex-column">
                                @foreach ($featuredCategories as $index => $category)
                           

                                @if ($index < $mainMenuLimit) {{-- Display first 7 categories directly --}} <li
                                    class="menu-item nav-item">
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
                                    <li class="total-product-item">
                                        <a href="{{ route('product.all') }}" class="total-product-badge">
                                            <i class="fas fa-box me-1"></i>
                                            Items: {{ \App\Models\Product::count() }}
                                        </a>
                                    </li>

                                    @if ($featuredCategories->count() > $mainMenuLimit)
                                    {{-- "View More" menu item --}}
                                    <li class="menu-item nav-item">
                                        <a class="nav-link" href="{{ route('product.all') }}">
                                            View More
                                            <i class="fas fa-chevron-down ms-1 d-inline-block"></i>
                                        </a>
                                        <ul class="sub-menu">
                                            @foreach ($featuredCategories as $index => $category)
                                            @if ($index >= $mainMenuLimit)
                                            {{-- Display remaining categories in "View More" dropdown --}}
                                            <li class="menu-item">
                                                <a href="{{ $category->shopLink() }}">
                                                    {{ $category->name }}
                                                    @if ($category->subcategories->count())
                                                    <i class="fas fa-chevron-right ms-1 d-inline-block"></i>
                                                    @endif
                                                </a>
                                                @if ($category->subcategories->count())
                                                {{-- This submenu will open to the left --}}
                                                <ul class="sub-menu"
                                                    style="left: -100% !important; right: auto !important; margin-left: -1px !important;">
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
                                                        <ul class="sub-menu"
                                                            style="left: -100% !important; right: auto !important; margin-left: -1px !important;">
                                                            @foreach ($subcategory->allSubcategories as $child)
                                                            <li class="menu-item">
                                                                <a
                                                                    href="{{ $child->shopLink() }}">{{ $child->name }}</a>
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
document.addEventListener('DOMContentLoaded', function() {
    // Desktop Category dropdown linkup
    const categorySelect = document.getElementById('headerCategorySelect');
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            if (this.value) {
                window.location.href = this.value;
            } else {
                window.location.href = '{{ route("product.all") }}';
            }
        });
    }

    if (window.innerWidth <= 768) {
        document.querySelectorAll('.main-menu .menu-item > a').forEach(function(link) {
            let parentLi = link.parentElement;
            if (parentLi.querySelector('.sub-menu')) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
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
/* Search Bar in Header Bottom */
.header-bottom-search {
    margin-right: auto;
    flex-shrink: 0;
    padding: 6px 0;
}

.header-bottom-search-form {
    display: flex;
}

.header-bottom-search-inner {
    display: flex;
    align-items: center;
    background: #fff;
    border-radius: 6px;
    overflow: hidden;
    height: 40px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.header-bottom-category-select {
    border: none;
    outline: none;
    padding: 0 12px;
    height: 100%;
    font-size: 13px;
    color: #333;
    background: #f5f5f5;
    border-right: 1px solid #ddd;
    cursor: pointer;
    min-width: 140px;
}

.header-bottom-search-input {
    border: none;
    outline: none;
    padding: 0 12px;
    height: 100%;
    font-size: 14px;
    min-width: 220px;
    color: #333;
}

.header-bottom-search-input::placeholder {
    color: #999;
}

.header-bottom-search-btn {
    border: none;
    outline: none;
    background: hsl(var(--base));
    color: #fff;
    padding: 0 16px;
    height: 100%;
    font-size: 18px;
    cursor: pointer;
    transition: background 0.3s;
}

.header-bottom-search-btn:hover {
    background: hsl(var(--base) / 0.85);
}

.main-menu {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    width: 100%;
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

.total-product-badge {
    margin-left: auto;
    color: #fff;
    font-size: 16px;
    padding: 8px 18px;
    background-color: #dd4637;
    font-weight: 900;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    text-decoration: none;
    border-radius: 4px;
    transition: background 0.3s;
    cursor: pointer;
}

.total-product-badge:hover {
    background-color: #c73a2f;
    color: #fff;
    text-decoration: none;
}

.total-product-item {
    margin-left: auto !important;
    position: relative;
    display: flex;
    align-items: center;
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
        width: 100%;
    }

    .main-menu .menu-item>a {
        color: #fff;
        padding: 10px 16px;
    }

    .total-product-item {
        flex-grow: 1;
        margin-left: 0 !important;
        width: 100%;
    }

    .total-product-badge {
        margin-left: auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 12px 16px;
        background-color: #dd4637;
        border-radius: 4px;
        text-decoration: none;
        color: #fff;
    }

    .total-product-badge:hover {
        background-color: #c73a2f;
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

    .navbar-toggler {
        flex-shrink: 0;
        padding: 8px;
    }

}

/* Mobile Header Controls */
.mobile-header-controls {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
}

.mobile-search-form-inline {
    flex: 1;
    display: flex;
}

.mobile-search-inner-inline {
    display: flex;
    align-items: center;
    background: #fff;
    border-radius: 6px;
    overflow: hidden;
    height: 36px;
    width: 100%;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.mobile-search-input-inline {
    border: none;
    outline: none;
    padding: 0 10px;
    height: 100%;
    font-size: 13px;
    flex: 1;
    color: #333;
    min-width: 0;
}

.mobile-search-input-inline::placeholder {
    color: #999;
}

.mobile-search-btn-inline {
    border: none;
    outline: none;
    background: hsl(var(--base));
    color: #fff;
    padding: 0 12px;
    height: 100%;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s;
    flex-shrink: 0;
}

.mobile-search-btn-inline:hover {
    background: hsl(var(--base) / 0.85);
}

@media (max-width: 480px) {
    .mobile-header-controls {
        gap: 170px;
    }

    .mobile-search-inner-inline {
        height: 34px;
    }

    .mobile-search-input-inline {
        font-size: 12px;
        padding: 0 8px;
    }

    .mobile-search-btn-inline {
        font-size: 14px;
        padding: 0 10px;
    }
}
</style>
@endpush