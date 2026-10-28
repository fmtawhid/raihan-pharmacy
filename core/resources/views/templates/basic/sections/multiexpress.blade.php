

@php
    $content = getContent('multiexpress.content', true);
    $heading = @$content->data_values->heading;
    $subheading = @$content->data_values->subheading;
    $buttonText = @$content->data_values->button_text;
    $buttonLink = @$content->data_values->button_link;

    $dealIds = @$content->data_values->selected_deals ?? [];
    if (is_string($dealIds)) $dealIds = explode(',', $dealIds);

    $deals = \App\Models\MultiExpressDeal::whereIn('id', $dealIds)
                ->where('status', 'active')
                ->get();
@endphp

<div class="my-60">
    <div class="container">

        {{-- Section Heading --}}
        @if($heading)
            <h2 class="section__title text-center mb-3">{{ $heading }}</h2>
        @endif
        @if($subheading)
            <p class="section__subtitle text-center mb-5">{{ $subheading }}</p>
        @endif

        {{-- Deals Grid --}}
        <div class="row g-4">
            @forelse($deals as $deal)
                @php
                    $totalOrders = $deal->orders()->sum('quantity');
                    $progressPercent = $deal->max_capacity ? min(100, ($totalOrders / $deal->max_capacity) * 100) : 0;
                    $discountPercent = $deal->regular_price ? round(100 - ($deal->deal_price / $deal->regular_price * 100)) : 0;
                @endphp

                <div class="col-sm-4 col-md-3 col-lg-3">
                    <div class="deal-card position-relative border rounded shadow-sm overflow-hidden p-2">

                        {{-- Discount Badge --}}
                        @if($discountPercent)
                            <div class="discount-badge">-{{ $discountPercent }}%</div>
                        @endif

                        {{-- Product Thumb --}}
                        <div class="text-center product-thumb">
                            <a href="{{ route('multi_express.deal.show', $deal->id) }}">
                                <img class="lazyload" 
                                     data-src="{{ $deal->feature_image ?? 'assets/images/default.png' }}" 
                                     alt="{{ $deal->title }}">
                            </a>
                        </div>

                        {{-- Product Content --}}
                        <div class="product-content mt-2">
                            <h6 class="title mb-1">
                                <a href="{{ route('multi_express.deal.show', $deal->id) }}">
                                    {{ strLimit(__($deal->title), 40) }}
                                </a>
                            </h6>

                            {{-- Price --}}
                            <div class="price mb-1">
                                ৳{{ number_format($deal->deal_price, 2) }}
                                @if($deal->regular_price && $deal->regular_price > $deal->deal_price)
                                    <del class="text-muted">৳{{ number_format($deal->regular_price, 2) }}</del>
                                @endif
                            </div>

                           
                            {{-- Progress Bar --}}
                            @if($deal->min_required)
                                <div class="progress mb-1" style="height:8px; border-radius:4px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progressPercent }}%"></div>
                                </div>
                                <small>{{ $totalOrders }} / {{ $deal->min_required }} Sold</small>
                            @endif

                            {{-- View Deal Button --}}
                            <div class="mt-2">
                                <a href="{{ route('multi_express.deal.show', $deal->id) }}" class="btn btn--base btn-sm w-100">
                                    @lang('View Deal')
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            @empty
                <div class="col-12 text-center">
                    <p>@lang('No deals found.')</p>
                </div>
            @endforelse
        </div>

        {{-- Section Button --}}
        @if($buttonText && $buttonLink)
            <div class="text-center mt-5">
                <a href="{{ $buttonLink }}" class="btn btn--base btn-lg">{{ $buttonText }}</a>
            </div>
        @endif
    </div>
</div>

@push('style')
<style>

.price {
    font-size: 14px;
    font-weight: 500;
    color: #b41e20ff;
}
.discount-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #ff4d4f;
    color: #fff;
    padding: 3px 8px;
    font-size: 12px;
    border-radius: 5px;
    font-weight: bold;
    z-index: 10;
}

.deal-card:hover {
    transform: translateY(-3px);
    transition: all 0.3s ease;
}

.progress {
    background-color: #e9ecef;
}
</style>
@endpush

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Clickable thumb redirect
    document.querySelectorAll('.product-thumb').forEach(function (thumb) {
        thumb.addEventListener('click', function (e) {
            if (!e.target.closest('button') && !e.target.closest('li')) {
                let link = thumb.querySelector('a');
                if (link) window.location.href = link.href;
            }
        });
    });
});
</script>
@endpush

