@if ($products->isEmpty())
    <div class="text-muted small">@lang('No products found')</div>
@else
    @foreach ($products as $product)
        <div class="search-result-item d-flex align-items-center justify-content-between p-2 border mb-2">
            <div class="d-flex align-items-center">
                <img src="{{ $product->thumbnail ?? route('placeholder.image', ['size' => '80x80']) }}" alt="{{ $product->name }}" style="width:50px;height:50px;object-fit:cover;" class="me-2">
                <div>
                    <a href="{{ route('product.detail', $product->slug) }}" class="fw-semibold">{{ $product->name }}</a>
                    <div class="small text-muted">@lang('Price'): 
                        @if($product->sale_price && $product->sale_price > 0)
                            {{ showAmount($product->sale_price) }}
                        @else
                            {{ showAmount($product->price ?? 0) }}
                        @endif
                    </div>
                </div>
            </div>
            <div>
                @if($product->product_type == \App\Constants\Status::PRODUCT_TYPE_VARIABLE)
                    <a href="{{ route('product.detail', $product->slug) }}" class="btn btn-sm btn-outline-primary">@lang('Choose Options')</a>
                @else
                    <button class="btn btn-sm btn-primary search-add-to-cart" data-id="{{ $product->id }}">@lang('Add')</button>
                @endif
            </div>
        </div>
    @endforeach
@endif