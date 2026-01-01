@extends($activeTemplate . 'layouts.master')

@section('content')
<div class="py-5">
    <div class="container">
        <div class="row">

            {{-- Sidebar: Categories --}}
            <aside class="col-lg-3 col-md-3 col-sm-4 mb-4 order-lg-1 order-2">
                <div class="card p-3 shadow-sm">
                    <h5 class="mb-3">@lang('Categories')</h5>
                    <ul class="list-unstyled mb-0">
                        {{-- All Deals --}}
                        <li class="mb-2">
                            <a href="{{ route('multi_express.deal.index') }}"
                            class="d-flex align-items-center gap-2 {{ !isset($category) ? 'fw-bold text-primary' : 'text-dark' }}">
                                <i class="las la-th-large"></i>
                                @lang('All Deals')
                            </a>
                        </li>

                        {{-- Loop through categories --}}
                        @foreach($categories as $cat)
                            <li class="mb-2">
                                <a href="{{ route('multi_express.deal.by_category', $cat->id) }}"
                                class="d-flex align-items-center gap-2 {{ isset($category) && $category->id == $cat->id ? 'fw-bold text-primary' : 'text-dark' }}">
                                    <i class="las la-box"></i>
                                    {{ __($cat->name) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>


            {{-- Main Deals Grid --}}
            <div class="col-lg-9 col-md-9 col-sm-8 order-lg-2 order-1">
                <h3 class="mb-4">
                    @if(isset($category))
                        @lang('Deals in Category:') {{ $category->name }}
                    @else
                        @lang('All Express Deals')
                    @endif
                </h3>

                <div class="row g-4">
                    @forelse($deals as $deal)
                        <div class="col-12 col-sm-6 col-lg-4">
                            <div class="deal-card border rounded shadow-sm overflow-hidden h-100 d-flex flex-column">
                                @if($deal->feature_image)
                                    <img src="{{ getImage($deal->feature_image) }}" class="img-fluid w-100" style="height:200px; object-fit:cover;">
                                @endif
                                <div class="deal-card-body p-3 flex-grow-1 d-flex flex-column">
                                    <h5 class="deal-title mb-2">
                                        <a href="{{ route('multi_express.deal.show', $deal->id) }}" class="text-decoration-none">
                                            {{ __($deal->title) }}
                                        </a>
                                    </h5>
                                    <p class="mb-1"><strong>@lang('Category'):</strong> {{ $deal->category->name ?? '-' }}</p>
                                    <p class="mb-1">
                                        <strong>@lang('Price'):</strong> ৳{{ $deal->deal_price }}
                                        @if($deal->regular_price)
                                            <span class="text-muted"><del>৳{{ $deal->regular_price }}</del></span>
                                            <span class="badge bg-danger">
                                                {{ round(100-($deal->deal_price/$deal->regular_price*100)) }}% OFF
                                            </span>
                                        @endif
                                    </p>
                                    <p class="mb-2">
                                        <strong>@lang('Status'):</strong>
                                        @if($deal->stock_status == 'ready')
                                            <span class="badge bg-success">@lang('Ready')</span>
                                        @elseif($deal->stock_status == 'upcoming')
                                            <span class="badge bg-warning">@lang('Upcoming')</span>
                                        @else
                                            <span class="badge bg-danger">@lang('Sold Out')</span>
                                        @endif
                                    </p>

                                    {{-- Progress --}}
                                    @php
                                        $joined = $deal->orders?->sum('quantity') ?? 0;
                                        $max = $deal->min_required ?? 100;
                                        $progress = min(100, round(($joined/$max)*100));
                                    @endphp
                                    <div class="progress mb-2" style="height:6px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small>{{ $joined }}/{{ $max }} @lang('joined')</small>

                                    <a href="{{ route('multi_express.deal.show', $deal->id) }}" class="btn btn-primary btn-sm mt-auto w-100 join-deal-btn">
                                        @lang('View Deal')
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center">
                            <p>@lang('No deals found')</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">
                    {{ $deals->links() }}
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    /* Optional hover effect for cards */
    .deal-card:hover {
        transform: translateY(-4px);
        transition: 0.3s ease;
    }

</style>
@endsection
