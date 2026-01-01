@props([
    'product' => $product,
    'wishlist' => null,
    'showRating' => true,
    'showTitle' => true,
    'showCartButton' => true,
])

@php
    $addedInCompareList = checkCompareList($product->id);
@endphp

<div class="product-card">
    @if (Route::is('wishlist.page'))
        <button class="active removeWishlist wishlist-product-remove-btn" data-page="1" data-id="{{ $wishlist->id }}"
            data-pid="{{ $product->id }}"><i class="las la-trash"></i></button>
    @endif
    <div class="product-thumb">
        <ul class="product-card-buttons">
            
        @if (gs('product_compare'))
            <li class="product-compare-btn">
                @if (!Route::is('compare.page'))
                    <button type="button" @class(['addToCompare', 'active' => checkCompareList($product->id)]) data-id="{{ $product->id }}">
                        <i class="las la-exchange-alt"></i>
                    </button>
                @endif
            </li>
        @endif
        @if (gs('product_wishlist'))
            <li class="product-wishlist-btn">
                @if (!Route::is('wishlist.add'))
                    <button type="button" @class(['addToWishlist', 'active' => checkWishList($product->id)]) data-id="{{ $product->id }}">
                        <i class="lar la-heart"></i>
                    </button>
                @endif
            </li>
        @endif
            <!-- @if ($product->product_type_id && gs('product_compare'))
                <li class="product-compare-btn">
                    <button tyepe="button" class="addToCompare {{ $addedInCompareList ? 'active' : '' }}"
                        data-id="{{ $product->id }}"><i class="las la-exchange-alt"></i></button>
                </li>
            @endif -->

            @if (!$showCartButton)
                @if ($product->productVariants->count())
                    <li class="product-quick-view-btn">
                        <button class="quickViewBtn" data-product="{{ $product->slug }}"><i
                                class="las la-cart-plus"></i></button>
                    </li>
                @else
                    <li class="product-quick-view-btn">
                        <input type="hidden" name="quantity" value="1">
                        <button tyepe="button" class="addToCart" data-id="{{ $product->id }}"
                            data-product_type="{{ $product->product_type }}"><i class="las la-cart-plus"></i></button>
                    </li>
                @endif
            @endif
        </ul>

        <a href="{{ $product->link() }}">
            <img src="{{ getImage(null) }}" class="lazyload" data-src="{{ $product->mainImage(false) }}"
               alt="{{ $product->name }}" />

        </a>
    </div>

    <div class="product-content">
        <div class="product-before-content">
            @if ($showTitle)
                <h6 class="title">
                    <a href="{{ $product->link() }}">{{ strLimit(__($product->name), 40) }}</a>
                </h6>
            @endif

            <div class="single_content__info">
                <div class="price">
                    @php
                        echo $product->formattedPrice();
                    @endphp
                </div>

                @if ($showRating && gs('product_review'))
                    <div class="ratings-area">
                        <span class="ratings">
                            @php echo displayRating($product->reviews_avg_rating) @endphp
                        </span>
                        <span class="rating-count">({{ $product->reviews_count ?? 0 }})</span>
                    </div>
                @endif
            </div>

            @if ($product->summary)
                <div class="single_content">
                    <p>{{ __($product->summary) }}</p>
                </div>
            @endif
        </div>
        {{-- @if ($showCartButton)
            @if ($product->productVariants->count())
                <button class="quickViewBtn add-to-cart-btn" data-product="{{ $product->slug }}"><i class="las la-shopping-bag"></i> @lang('Add to Cart')</button>
            @else
                <input type="hidden" name="quantity" value="1">
                <button tyepe="button" class="addToCart add-to-cart-btn" data-id="{{ $product->id }}" data-product_type="{{ $product->product_type }}"><i class="las la-shopping-bag"></i> @lang('Add to Cart')</button>
            @endif
        @endif
        @if ($showCartButton)
            @if ($product->productVariants->count())
                <button class="quickViewBtn add-to-cart-btn" data-product="{{ $product->slug }}"><i class="las la-shopping-bag"></i> @lang('Buy Now')</button>
            @else
                <input type="hidden" name="quantity" value="1">
                <button tyepe="button" class="addToCart add-to-cart-btn" data-id="{{ $product->id }}" data-product_type="{{ $product->product_type }}"><i class="las la-shopping-bag"></i> @lang('Buy Now')</button>
            @endif
        @endif --}}

        {{-- ========= Buttons (side‑by‑side) ========= --}}
        @if ($showCartButton)
            <div class="button-group"> {{-- grid wrapper --}}
                {{-- ── Add to Cart ──────────────────── --}}
                @if ($product->productVariants->count())
                    <button class="quickViewBtn add-to-cart-btn" data-product="{{ $product->slug }}">
                        <i class="las la-shopping-bag"></i>
                        @lang('Add to Cart')
                    </button>
                @else
                    <input type="hidden" name="quantity" value="1">
                    <button type="button" class="addToCart add-to-cart-btn" data-id="{{ $product->id }}"
                        data-product_type="{{ $product->product_type }}">
                        <i class="las la-shopping-bag"></i>
                        @lang('Add to Cart')
                    </button>
                @endif

                {{-- ── Buy Now ─────────────────────── --}}
                @if ($product->productVariants->count())
                    {{-- VARIABLE product --}}
                    <button class="quickViewBtn buy-now-btn" {{-- ◀ no .addToCart --}} data-product="{{ $product->slug }}">
                        <i class="las la-shopping-bag"></i> @lang('Buy Now')
                    </button>
                @else
                    {{-- SIMPLE product --}}
                    <input type="hidden" name="quantity" value="1">
                    <button type="button" class="addToCart buy-now-btn" {{-- ◀ both classes --}}
                        data-id="{{ $product->id }}" data-product_type="{{ $product->product_type }}">
                        <i class="las la-shopping-bag"></i> @lang('Buy Now')
                    </button>
                @endif

            </div>
        @endif



<script>
    document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.product-thumb').forEach(function (thumb) {
        thumb.addEventListener('click', function (e) {
            // যদি ক্লিক করা এলিমেন্ট বাটন না হয়
            if (!e.target.closest('button') && !e.target.closest('li')) {
                let link = thumb.querySelector('a');
                if (link) {
                    window.location.href = link.href;
                }
            }
        });
    });
});

</script>



</div>
</div>
