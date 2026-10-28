<a href="{{ $category->shopLink() }}" class="d-block text-center">
    <img src="{{ getImage(null) }}" data-src="{{ $category->categoryImage() }}" class="w-100 lazyload owl-lazy" alt="{{ __($category->name) }}" style="height:75px;">
    <span class="title line-limitation-1">{{ __($category->name) }}</span>
</a>
