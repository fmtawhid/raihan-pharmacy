@extends($activeTemplate . 'layouts.master')

@section('content')
<div class="py-60">
    <div class="container">
        
        {{-- BREADCRUMB --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item"><a href="/deals">Deals</a></li>
                <li class="breadcrumb-item active">{{ $deal->category->name ?? 'Deal' }}</li>
            </ol>
        </nav>

        <div class="row g-4">

            <!-- ================= LEFT COLUMN (Product Info) ================= -->
            <div class="col-lg-8 card custom-card p-4">

                {{-- MAIN FEATURE IMAGE --}}
                <div class="deal-images mb-4">
                    <div class="position-relative bg-light rounded overflow-hidden" style="height: 450px;">
                        <img src="{{ $deal->feature_image ? asset($deal->feature_image) : asset('assets/images/default.png') }}" 
                            class="img-fluid w-100 h-100"
                            style="object-fit: cover;"
                            alt="{{ $deal->title }}"
                            id="mainImage">
                    </div>
                </div>

                {{-- GALLERY IMAGES --}}
                @if($deal->gallery ?? false)
                    <div class="gallery-thumbs d-flex gap-2 mb-4 flex-wrap">
                        @foreach($deal->gallery as $img)
                            <img src="{{ getImage($img) }}" 
                                 class="img-thumbnail cursor-pointer"
                                 width="80"
                                 height="80"
                                 style="object-fit: cover; cursor: pointer; transition: 0.3s;"
                                 onclick="document.getElementById('mainImage').src='{{ getImage($img) }}'"
                                 alt="Gallery">
                        @endforeach
                    </div>
                @endif

                {{-- DEAL TITLE & RATING --}}
                <div class="mb-3">
                    <h2 class="mb-2">{{ __($deal->title) }}</h2>
                    <div class="d-flex gap-3 align-items-center mb-3">
                        <div>
                            <span class="badge bg-success">{{ ucfirst($deal->stock_status) }}</span>
                        </div>
                    </div>
                </div>

                {{-- SHORT DESCRIPTION --}}
                @if($deal->short_description)
                    <p class="text-muted fs-6 mb-3">{{ $deal->short_description }}</p>
                @endif

                {{-- PRICING SECTION --}}
                <div class="lowest-price-box mb-4">
                    <div class="price-title">Lowest Price</div>

                    <div class="price-wrapper">
                        <span class="current-price">৳{{ number_format($deal->deal_price, 2) }}</span>
                        <span class="old-price">৳{{ number_format($deal->regular_price, 2) }}</span>
                    </div>

                    <div class="save-badge">
                        <i class="fa fa-tag"></i> You Save ৳{{ round($deal->regular_price - $deal->deal_price) }}
                    </div>

                    <div class="lowest-badge">
                        <i class="fa fa-check-circle"></i> Lowest price achieved! Everyone gets the best deal!
                    </div>
                </div>

                {{-- Quantity Based Pricing --}}
                @if($deal->pricingTiers && $deal->pricingTiers->count())
                <div class="mb-4">
                    <h5 class="mb-3 fw-bold">
                        <i class="fa fa-tags" style="color:#ff5722;"></i> Quantity Based Pricing
                    </h5>

                    <div class="row g-3">
                        @foreach($deal->pricingTiers as $tier)
                            <div class="col-md-4 col-sm-6">
                                <div class="card text-center shadow-sm h-100 rounded-3" 
                                    style="border: 2px solid #4CAF50; border-radius:12px;">
                                    
                                    <div class="card-body py-4">
                                        <h6 style="color:#e53935; margin-bottom:8px;">
                                            {{ $tier->min_quantity }} - {{ $tier->max_quantity ?? '∞' }} units
                                        </h6>

                                        <h4 style="color:#2e7d32; margin-bottom:4px;">
                                            ৳{{ number_format($tier->price_per_item, 2) }}
                                        </h4>

                                        <span style="color:#777; font-size:12px;">Best Price</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- ================= Estimated Delivery Timeline ================= --}}
                <div class="delivery-timeline-wrapper mb-4">
                    {{-- Title --}}
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fa fa-calendar-alt" style="font-size:28px; color:#003399;"></i>
                        <h3 class="m-0 fw-bold" style="font-size:26px;">
                            <span style="color:#003399;">Estimated</span>
                            <span style="color:#b92100;">Delivery</span>
                            <span style="color:#0c5d08;">Timeline</span>
                        </h3>
                    </div>

                    {{-- Outer Box --}}
                    <div class="delivery-main-box">
                        {{-- Inner Box --}}
                        <div class="delivery-inner-box">
                            <div class="d-flex justify-content-between mb-2">
                                {{-- Delivery Start --}}
                                <div class="text-start">
                                    <span class="badge rounded-pill"
                                        style="background:#0c5d08; color:white; padding:6px 10px;font-size: 30px;">
                                        <i class="fa fa-play-circle"></i>
                                    </span>
                                    <div class="mt-2 fw-semibold" style="color:#0c5d08;">
                                        Delivery Starts
                                    </div>
                                    <small style="color:#003399;">
                                        {{ \Carbon\Carbon::parse($deal->delivery_start_date)->format('M d, Y') }}
                                    </small>
                                </div>

                                {{-- Delivery End --}}
                                <div class="text-end">
                                    <span class="badge rounded-pill"
                                        style="background:#db4437; color:white; padding:6px 10px; font-size: 30px;">
                                        <i class="fa fa-stop-circle"></i>
                                    </span>
                                    <div class="mt-2 fw-semibold" style="color:#b92100;">
                                        Maximum
                                    </div>
                                    <small style="color:#003399;">
                                        {{ \Carbon\Carbon::parse($deal->delivery_end_date)->format('M d, Y') }}
                                    </small>
                                </div>
                            </div>

                            {{-- Info Bar --}}
                            <div class="delivery-info-bar">
                                <i class="fa fa-truck"></i> 
                                If delivery starting today, completing within 
                                <span style="font-weight:700; color:#0c5d08;">
                                    {{ \Carbon\Carbon::parse($deal->delivery_start_date)->diffInDays($deal->delivery_end_date) }}
                                    days
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                


                {{-- DELIVERY NOTE --}}
                @if($deal->delivery_note)
                    <div class="mb-4">
                        <h5 class="mb-2 border-bottom pb-1"><i class="fas fa-info-circle mr-1 sm:mr-2"></i> @lang('About this Deal')</h5>
                        <div class="text-muted">
                            {!! $deal->delivery_note !!}
                        </div>
                    </div>
                @endif  

                {{-- FULL DESCRIPTION --}}
                @if($deal->description)
                    <div class="mb-4">
                        <h5 class="mb-3 border-bottom pb-1"><i class="fas fa-info-circle mr-1 sm:mr-2"></i>@lang('Description')</h5>
                        <div class="text-muted">
                            {!! $deal->description !!}
                        </div>
                    </div>
                @endif
                                {{-- KEY DETAILS --}}
                <div class="mb-4">
                    <h5 class="mb-3 border-bottom pb-1"> <i class="fas fa-star mr-1 sm:mr-2"></i> @lang('Deal Features and Attributions')</h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="p-2 bg-light rounded">
                                <small class="text-muted">@lang('Category')</small>
                                <p class="mb-0 fw-bold">{{ $deal->category->name ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-2 bg-light rounded">
                                <small class="text-muted">@lang('Minimum Required')</small>
                                <p class="mb-0 fw-bold">{{ $deal->min_required ?? 'N/A' }} units</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-2 bg-light rounded">
                                <small class="text-muted">@lang('Max Capacity')</small>
                                <p class="mb-0 fw-bold">{{ $deal->max_capacity ?? 'Unlimited' }}</p>
                            </div>
                        </div>
                        @if($deal->purchase_limit_per_user)
                            <div class="col-md-3">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted">@lang('Per User Limit')</small>
                                    <p class="mb-0 fw-bold">{{ $deal->purchase_limit_per_user }} units</p>
                                </div>
                            </div>
                        @endif

                        <div class="row g-3">
                            <!-- Group Buying -->
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded text-center">
                                    <i class="fa fa-users fa-2x mb-2 text-primary"></i>
                                    <small class="d-block text-muted">Group Buying</small>
                                    <p class="mb-0">More people = better price</p>
                                </div>
                            </div>

                            <!-- Secure Payment -->
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded text-center">
                                    <i class="fa fa-lock fa-2x mb-2 text-success"></i>
                                    <small class="d-block text-muted">Secure Payment</small>
                                    <p class="mb-0">Protected transaction</p>
                                </div>
                            </div>

                            <!-- Fast Shipping -->
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded text-center">
                                    <i class="fa fa-truck fa-2x mb-2 text-warning"></i>
                                    <small class="d-block text-muted">Fast Shipping</small>
                                    <p class="mb-0">Quick delivery</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- ================= RIGHT SIDEBAR ================= -->
            <div class="col-lg-4">

                <div class="card custom-card p-3 mb-4 sticky-card">
                <!-- Countdown Title -->
                <h5 class="fw-bold mb-2">
                    <span class="section-title-icon">⏱</span> Deals Ends In
                </h5>

                <!-- Countdown Boxes -->
                <div class="row text-center mb-3" id="countdown">
                    <div class="col-3">
                        <div class="count-box" id="days">00</div>
                        <small>Days</small>
                    </div>
                    <div class="col-3">
                        <div class="count-box" id="hours">00</div>
                        <small>Hours</small>
                    </div>
                    <div class="col-3">
                        <div class="count-box" id="minutes">00</div>
                        <small>Mins</small>
                    </div>
                    <div class="col-3">
                        <div class="count-box" id="seconds">00</div>
                        <small>Secs</small>
                    </div>
                </div>

                <!-- Join Button -->
                <button class="btn join-btn w-100 mb-2" data-bs-toggle="modal" data-bs-target="#joinDealModal">
                    <i class="fas fa-user-plus me-1"></i> Join Now
                </button>
            </div>

            <style>
            /* Sticky bottom on small screens */
            @media (max-width: 768px) {
                .sticky-card {
                    position: fixed;
                    bottom: 35px;
                    left: 0;
                    width: 100%;
                    z-index: 1050; /* ensure it’s above other elements */
                    padding: 0.5rem 1rem;
                    border-radius: 0;
                    box-shadow: 0 -2px 8px rgba(0,0,0,0.15);
                    background-color: #fff;
                }

                .sticky-card .count-box {
                    font-size: 0.8rem;
                    padding: 0.3rem 0.4rem;
                }

                .sticky-card h5 {
                    font-size: 1rem;
                }

                .sticky-card small {
                    font-size: 0.7rem;
                }

                .sticky-card .join-btn {
                    font-size: 0.85rem;
                    padding: 0.35rem 0.5rem;
                }

                #countdown .col-3 {
                    padding: 0 2px;
                }
            }
            </style>

                {{-- JOIN DEAL MODAL --}}
                <div class="modal fade" id="joinDealModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <form action="{{ route('multi_express.deal.join', $deal->id) }}" method="POST">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header bg-light border-bottom">
                                    <h5 class="modal-title fw-bold">{{ $deal->title }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="row g-3">

                                        {{-- NAME & PHONE --}}
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">@lang('Name') <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control" 
                                                    value="{{ auth()->check() ? auth()->user()->firstname . ' ' . auth()->user()->lastname : '' }}" 
                                                    {{ auth()->check() ? '' : 'required' }}>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">@lang('Phone') <span class="text-danger">*</span></label>
                                                <input type="tel" name="phone" class="form-control" 
                                                    value="{{ auth()->check() ? auth()->user()->mobile ?? '' : '' }}" 
                                                    {{ auth()->check() ? '' : 'required' }}>
                                            </div>
                                        </div>

                                        {{-- ADDRESS --}}
                                        <div class="col-12">
                                            <label class="form-label">@lang('Address') <span class="text-danger">*</span></label>
                                            <textarea name="address" class="form-control" rows="2" required></textarea>
                                        </div>

                                        @if(auth()->check())
                                            {{-- Logged in user: hide email input, auto submit --}}
                                            <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                                        @else
                                            {{-- Guest: show email input --}}
                                            <div class="col-md-6">
                                                <label class="form-label">@lang('Email') <span class="text-danger">*</span></label>
                                                <input type="email" name="email" class="form-control" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">@lang('Password') <span class="text-danger">*</span></label>
                                                <input type="password" name="password" class="form-control" required>
                                                <small class="text-muted">Create a password to manage your orders later</small>
                                            </div>
                                        @endif

                                        {{-- PRICING TIERS --}}
                                        @if($deal->pricingTiers->count())
                                            <div class="col-12">
                                                <label class="form-label fw-bold mb-3">@lang('Select Quantity Tier') <span class="text-danger">*</span></label>
                                                <div id="pricingOptions" class="pricing-options-wrapper">
                                                    <div class="row g-3">
                                                    
                                                        @foreach($deal->pricingTiers as $i => $tier)
                                                        
                                                            <div class="col-md-4">
                                                                <label class="pricing-option-label w-100">
                                                                    <input class="form-check-input pricing-radio" type="radio" name="pricing"
                                                                        value="{{ $tier->price_per_item }}"
                                                                        data-min="{{ $tier->min_quantity }}"
                                                                        data-max="{{ $tier->max_quantity }}"
                                                                        id="tier{{ $tier->id }}"
                                                                        {{ $i == 0 ? 'checked' : '' }}>
                                                                    <div class="pricing-option-box">
                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <span class="fw-bold">{{ $tier->min_quantity }}-{{ $tier->max_quantity }} units</span>
                                                                            <span class="badge bg-success fs-6">৳{{ number_format($tier->price_per_item, 2) }}</span>
                                                                        </div>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- DELIVERY OPTIONS --}}
                                        @if($deal->deliveryOptions?->count())
                                        <div class="col-12">
                                            <label class="form-label fw-bold mb-3">@lang('Delivery Option') <span class="text-danger">*</span></label>
                                            <div id="deliveryOptions" class="delivery-options-wrapper">
                                                <div class="row g-3">
                                                    @foreach($deal->deliveryOptions as $option)
                                                        <div class="col-md-4">
                                                            <label class="delivery-option-label w-100">
                                                                <input class="form-check-input delivery-radio" type="radio" name="delivery_option_id" 
                                                                    value="{{ $option->id }}" data-charge="{{ $option->charge_per_item }}" 
                                                                    id="deliv{{ $option->id }}" {{ $loop->first ? 'checked' : '' }}>
                                                                <div class="delivery-option-box">
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <div>
                                                                            <strong class="d-block">{{ $option->label }}</strong>
                                                                            <small class="text-muted">Delivery Charge</small>
                                                                        </div>
                                                                        <span class="badge bg-primary fs-6">৳{{ number_format($option->charge_per_item, 2) }}</span>
                                                                    </div>
                                                                </div>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        {{-- QUANTITY INPUT --}}
                                        <!-- <div class="col-12">
                                            <label class="form-label">@lang('Quantity') <span class="text-danger">*</span></label>
                                            <button class="btn btn-outline-secondary" type="button" id="decreaseBtn">−</button>
                                            <input type="number" 
                                                name="quantity" 
                                                id="quantityInput" 
                                                class="form-control form-control-lg"
                                                min="1" 
                                                max="{{ $deal->purchase_limit_per_user ?? 100 }}"
                                                value="1" 
                                                required>
                                            <small class="text-muted" id="quantityLimitText">
                                                @if($deal->purchase_limit_per_user)
                                                    Maximum {{ $deal->purchase_limit_per_user }} units per user
                                                @else
                                                    Enter the quantity you want to purchase
                                                @endif
                                            </small>
                                        </div> -->
                                        <div class="col-12">
                                            <label class="form-label">@lang('Quantity') <span class="text-danger">*</span></label>
                                            <div class="input-group" style="max-width: 150px;">
                                                <button class="btn btn-outline-secondary" type="button" id="decreaseBtn">−</button>
                                                <input type="number" 
                                                    name="quantity" 
                                                    id="quantityInput" 
                                                    class="form-control form-control-lg text-center"
                                                    min="1" 
                                                    max="{{ $deal->purchase_limit_per_user ?? 100 }}"
                                                    value="1" 
                                                    required>
                                                <button class="btn btn-outline-secondary" type="button" id="increaseBtn">+</button>
                                            </div>

                                            <small class="text-muted" id="quantityLimitText">
                                                @if($deal->purchase_limit_per_user)
                                                    Maximum {{ $deal->purchase_limit_per_user }} units per user
                                                @else
                                                    Enter the quantity you want to purchase
                                                @endif
                                            </small>
                                        </div>



                                        {{-- PRICE BREAKDOWN --}}
                                        <div class="col-12">
                                            <div class="p-3 bg-light rounded">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Price per item:</span>
                                                    <strong>৳<span id="pricePerItem">{{ number_format($deal->deal_price, 2) }}</span></strong>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Quantity:</span>
                                                    <strong><span id="quantityDisplay">1</span></strong>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Delivery charge:</span>
                                                    <strong>৳<span id="deliveryCharge">0.00</span></strong>
                                                </div>
                                                <hr>
                                                <div class="d-flex justify-content-between fs-5">
                                                    <strong>Total:</strong>
                                                    <strong class="text-success">৳<span id="totalPrice">{{ number_format($deal->deal_price, 2) }}</span></strong>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="modal-footer border-top">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Cancel')</button>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-check-circle me-2"></i>@lang('Confirm & Join')
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Deal Progress Card -->
                <div class="card custom-card p-4 mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-users me-2"></i> Deal Progress
                    </h5>

                    @php
                    
                        $joined  = $deal->orders()->sum('quantity') ?? 0;
                        $min     = $deal->min_required ?? 0;
                        $max     = $deal->max_capacity ?? 1;

                        $minProgress = ($min > 0) ? min(100, round(($joined / $min) * 100)) : 0;
                        $capProgress = min(100, round(($joined / $max) * 100));

                        $remaining = max(0, $max - $joined);  // negative fix
                    
                    
                    @endphp

                    <!-- Minimum Required -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="progress-label">
                                <i class="fas fa-user-check me-1"></i> Minimum Required
                            </span>
                            <span class="progress-label">{{ $joined }}/{{ $min }}</span>
                        </div>

                        <div class="progress mt-1">
                            <div class="progress-bar bg-danger" style="width: {{ $minProgress }}%"></div>
                        </div>

                        @if($joined >= $min)
                            <div class="text-success small mt-1">✔ Minimum requirement met!</div>
                        @else
                            <div class="text-danger small mt-1">Need {{ $min - $joined }} more to meet minimum requirement.</div>
                        @endif
                    </div>

                    <!-- Maximum Capacity -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span class="progress-label">
                                <i class="fas fa-users me-1"></i> Capacity
                            </span>
                            <span class="progress-label">{{ $joined }}/{{ $max }}</span>
                        </div>

                        <div class="progress mt-1">
                            <div class="progress-bar bg-success" style="width: {{ $capProgress }}%"></div>
                        </div>
                    </div>

                    <div class="small text-secondary mt-1">
                        {{ $capProgress }}% of capacity filled • {{ $remaining }} more can join
                    </div>
                </div>

                {{-- DELIVERY OPTIONS CARD --}}
                @if($deal->deliveryOptions && $deal->deliveryOptions->count())
                    <div class="card custom-card mb-4 p4">
                        <div class="card-body">
                            <h6 class="card-title mb-3">🚚 @lang('Delivery Options')</h6>
                            <div class="d-flex flex-column gap-2">
                                @foreach($deal->deliveryOptions as $delivery)
                                    <div class="p-3 rounded border-2" 
                                         style="border-color: #6a11cb; background: linear-gradient(135deg, rgba(106,17,203,0.1), rgba(37,117,252,0.1));">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong>{{ $delivery->label }}</strong>
                                            <span class="badge bg-primary">৳{{ number_format($delivery->charge_per_item, 2) }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- RECENT PARTICIPANTS CARD --}}
                <div class="card custom-card mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">👥 @lang('Recent Participants')</h6>
                        <ul class="list-unstyled mb-0 small">
                            @forelse($deal->orders->take(5) as $order)
                                <li class="mb-2 pb-2 border-bottom">
                                    <strong>{{ Str::mask($order->name, '*', 1, -1) }}</strong>
                                    <br>
                                    <span class="text-muted text-xs">{{ $order->created_at->format('M d, Y') }}</span>
                                </li>
                            @empty
                                <p class="text-muted text-center py-3">@lang('No participants yet')</p>
                            @endforelse
                        </ul>
                    </div>
                </div>

                {{-- SHARE CARD --}}
                <div class="card custom-card">
                    <div class="card-body">
                        <h6 class="card-title mb-3">📤 @lang('Share This Deal')</h6>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" 
                               target="_blank" class="btn btn-sm btn-primary flex-grow-1">
                                <i class="fab fa-facebook-f"></i> Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}" 
                               target="_blank" class="btn btn-sm btn-info flex-grow-1">
                                <i class="fab fa-twitter"></i> Twitter
                            </a>
                            <button class="btn btn-sm btn-secondary flex-grow-1" 
                                onclick="navigator.clipboard.writeText(window.location.href); alert('Link copied!')">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>



    </div>
</div>

{{-- ========== RELATED PRODUCTS ========== --}}
@if(isset($relatedDeals) && $relatedDeals->count())
    <div class="my-5">
        <div class="container">
            <h4 class="mb-4">@lang('Related Products')</h4>

            <div class="row g-4">
                @foreach($relatedDeals as $relatedDeal)
                    @php
                        $totalOrders = $relatedDeal->orders()->sum('quantity');
                        $progressPercent = $relatedDeal->max_capacity ? min(100, ($totalOrders / $relatedDeal->max_capacity) * 100) : 0;
                        $discountPercent = $relatedDeal->regular_price ? round(100 - ($relatedDeal->deal_price / $relatedDeal->regular_price * 100)) : 0;
                    @endphp

                    <div class="col-sm-4 col-md-3 col-lg-3">
                        <div class="deal-card position-relative border rounded shadow-sm overflow-hidden p-2">
                            {{-- Discount Badge --}}
                            @if($discountPercent)
                                <div class="discount-badge">-{{ $discountPercent }}%</div>
                            @endif

                            {{-- Product Thumb --}}
                            <div class="text-center product-thumb">
                                <a href="{{ route('multi_express.deal.show', $relatedDeal->id) }}">
                                    <img class="lazyload" 
                                        data-src="{{ $relatedDeal->feature_image ? getImage($relatedDeal->feature_image) : asset('assets/images/default.png') }}" 
                                        alt="{{ $relatedDeal->title }}" style="height:140px; object-fit:cover;">
                                </a>
                            </div>

                            {{-- Product Content --}}
                            <div class="product-content mt-2">
                                <h6 class="title mb-1" style="font-size:14px;">
                                    <a href="{{ route('multi_express.deal.show', $relatedDeal->id) }}">
                                        {{ \Illuminate\Support\Str::limit($relatedDeal->title, 45) }}
                                    </a>
                                </h6>

                                {{-- Price --}}
                                <div class="price mb-1 text-success fw-bold">
                                    ৳{{ number_format($relatedDeal->deal_price, 2) }}
                                    @if($relatedDeal->regular_price && $relatedDeal->regular_price > $relatedDeal->deal_price)
                                        <del class="text-muted">৳{{ number_format($relatedDeal->regular_price, 2) }}</del>
                                    @endif
                                </div>

                                {{-- Progress Bar --}}
                                @if($relatedDeal->min_required)
                                    <div class="progress mb-1" style="height:8px; border-radius:4px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progressPercent }}%"></div>
                                    </div>
                                    <small>{{ $totalOrders }} / {{ $relatedDeal->min_required }} Sold</small>
                                @endif

                                {{-- View Deal Button --}}
                                <div class="mt-2">
                                    <a href="{{ route('multi_express.deal.show', $relatedDeal->id) }}" class="btn btn-sm btn-outline-primary w-100">
                                        @lang('View')
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif


@push('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.getElementById('quantityInput');
    const quantityDisplay = document.getElementById('quantityDisplay');
    const pricingRadios = document.querySelectorAll('.pricing-radio');
    const deliveryRadios = document.querySelectorAll('.delivery-radio');
    const pricePerItemSpan = document.getElementById('pricePerItem');
    const deliveryChargeSpan = document.getElementById('deliveryCharge');
    const totalPriceSpan = document.getElementById('totalPrice');
    const countdownEl = document.getElementById('countdown');

    const maxLimit = parseInt(quantityInput.max) || 100;
    const minLimit = parseInt(quantityInput.min) || 1;

    function updatePrice() {
        const quantity = parseInt(quantityInput.value) || 1;
        quantityDisplay.textContent = quantity;

        let pricePerItem = {{ $deal->deal_price }};
        const selectedPricing = document.querySelector('.pricing-radio:checked');
        if(selectedPricing){
            pricePerItem = parseFloat(selectedPricing.value);
        }

        let deliveryCharge = 0;
        const selectedDelivery = document.querySelector('.delivery-radio:checked');
        if(selectedDelivery){
            deliveryCharge = parseFloat(selectedDelivery.dataset.charge) || 0;
        }

        const totalDeliveryCharge = deliveryCharge * quantity;
        const totalPrice = (pricePerItem * quantity) + totalDeliveryCharge;

        pricePerItemSpan.textContent = pricePerItem.toFixed(2);
        deliveryChargeSpan.textContent = totalDeliveryCharge.toFixed(2);
        totalPriceSpan.textContent = totalPrice.toFixed(2);
    }

    // ===== INCREMENT / DECREMENT BUTTONS =====
    document.getElementById('increaseBtn').addEventListener('click', function() {
        let value = parseInt(quantityInput.value) || 1;
        if (value < maxLimit) quantityInput.value = value + 1;
        updatePrice();
    });

    document.getElementById('decreaseBtn').addEventListener('click', function() {
        let value = parseInt(quantityInput.value) || 1;
        if (value > minLimit) quantityInput.value = value - 1;
        updatePrice();
    });

    // Manual input validation
    quantityInput.addEventListener('input', function() {
        let value = parseInt(quantityInput.value) || minLimit;
        if(value < minLimit) quantityInput.value = minLimit;
        else if(value > maxLimit) quantityInput.value = maxLimit;
        updatePrice();
    });

    // Pricing & Delivery change events
    pricingRadios.forEach(radio => radio.addEventListener('change', updatePrice));
    deliveryRadios.forEach(radio => radio.addEventListener('change', updatePrice));

    // Initialize price on load
    updatePrice();

    // ===== COUNTDOWN =====
    @php
        $endTime = $deal->deal_end_time ?? $deal->delivery_end_date;
    @endphp
    const endTime = new Date("{{ $endTime }}").getTime();

    function updateCountdown() {
        const now = new Date().getTime();
        const distance = endTime - now;

        if (distance < 0) {
            countdownEl.innerHTML = "<div class='col-12'><span class='text-danger fw-bold'>Deal Ended</span></div>";

            // Hide join button
    const joinBtn = countdownEl.closest('.card').querySelector('.join-btn');
    if (joinBtn) {
        joinBtn.style.display = 'none';
    }
            return;
        }
        

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById('days').textContent = days.toString().padStart(2, '0');
        document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
        document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
        document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
});
</script>

@endpush

@push('style')
<style>
    .sticky-top {
        top: 20px;
    }
    .gallery-thumbs img:hover {
        opacity: 0.8;
        transform: scale(1.05);
    }
    .form-check {
        transition: all 0.3s ease;
    }
    .form-check:hover {
        background-color: #f8f9fa;
    }
    .breadcrumb {
        background-color: #f8f9fa;
    }

    .sticky-top {
        top: 20px;
    }
    
    .gallery-thumbs img:hover {
        opacity: 0.8;
        transform: scale(1.05);
    }
    
    .breadcrumb {
        background-color: #f8f9fa;
    }

    /* ===== PRICING OPTIONS STYLING ===== */
    .pricing-options-wrapper {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .pricing-option-label {
        cursor: pointer;
        display: block;
        margin: 0;
    }

    .pricing-option-label input[type="radio"] {
        display: none;
    }

    .pricing-option-box {
        padding: 16px 20px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        background-color: #f9f9f9;
        transition: all 0.3s ease;
    }

    .pricing-option-label input[type="radio"]:checked + .pricing-option-box {
        border-color: #28a745;
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.05), rgba(40, 167, 69, 0.1));
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.15);
        transform: scale(1.01);
    }

    .pricing-option-label:hover .pricing-option-box {
        border-color: #28a745;
        background-color: #f0f8f5;
    }

    /* ===== DELIVERY OPTIONS STYLING ===== */
    .delivery-options-wrapper {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .delivery-option-label {
        cursor: pointer;
        display: block;
        margin: 0;
    }

    .delivery-option-label input[type="radio"] {
        display: none;
    }

    .delivery-option-box {
        padding: 16px 20px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        background-color: #f9f9f9;
        transition: all 0.3s ease;
    }

    .delivery-option-label input[type="radio"]:checked + .delivery-option-box {
        border-color: #0d6efd;
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.05), rgba(13, 110, 253, 0.1));
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
        transform: scale(1.01);
    }

    .delivery-option-label:hover .delivery-option-box {
        border-color: #0d6efd;
        background-color: #f0f4ff;
    }

    /* Countdown Boxes */
    .count-box {
        width: 100%;
        padding: 15px 0;
        border-radius: 12px;
        background: linear-gradient(135deg, #1f3c88, #c31432);
        color: white;
        font-size: 20px;
        font-weight: 600;
        text-align: center;
    }

    /* Join Button Gradient */
    .join-btn {
        background: linear-gradient(90deg, #c31432, #1f3c88);
        color: white !important;
        padding: 12px;
        border-radius: 10px;
        font-size: 18px;
        font-weight: 600;
    }

    /* Card Shadow */
    .custom-card {
        border-radius: 12px;
        box-shadow: 0px 4px 12px rgba(0,0,0,0.12);
    }

    /* Icons */
    .section-title-icon {
        color: red;
        font-size: 20px;
    }

    /* Progress Labels */
    .progress-label {
        font-size: 14px;
        font-weight: 600;
    }

    /* Progress bars */
    .progress {
        height: 9px;
        border-radius: 10px;
    }

    /* Lowest Price Box */
    .lowest-price-box {
        border: 2px solid #e85a4f;
        border-radius: 10px;
        background: #fff8f5;
        padding: 20px;
    }

    .price-title {
        font-size: 13px;
        color: #666;
        font-weight: 500;
        margin-bottom: 5px;
    }

    .price-wrapper {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .current-price {
        font-size: 38px;
        color: #e32626;
        font-weight: 700;
    }

    .old-price {
        font-size: 20px;
        color: #999;
        text-decoration: line-through;
    }

    .save-badge {
        display: inline-block;
        margin-top: 10px;
        background: #ffe7d5;
        padding: 6px 14px;
        border-radius: 10px;
        font-size: 14px;
        color: #d34200;
        font-weight: 600;
    }

    .lowest-badge {
        margin-top: 15px;
        background: #fff0e9;
        border: 1px solid #f7b69a;
        padding: 12px;
        border-radius: 10px;
        color: #a7401b;
        font-size: 15px;
        font-weight: 600;
    }

    .lowest-badge i {
        color: #28a745;
        margin-right: 6px;
    }

    /* Delivery Timeline */
    .delivery-timeline-wrapper {
        border: 2px solid #003399;
        background: #f0fff8;
        border-radius: 12px;
        padding: 20px 25px;
    }

    .delivery-inner-box {
        border: 2px solid #db4437;
        background: #fffcfa;
        border-radius: 12px;
        padding: 20px;
    }

    .delivery-info-bar {
        margin-top: 15px;
        background: #f0f4ff;
        border-radius: 8px;
        padding: 12px 15px;
        color: #0c5d08;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .delivery-info-bar i {
        color: #0c5d08;
        font-size: 18px;
    }

    /* Related Products */
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

    .price {
        font-size: 14px;
        font-weight: 500;
    }
</style>
@endpush
{{-- Include SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- SUCCESS MESSAGE POPUP --}}
@if(session('success'))

    {{-- 🟢 OPTION 1: SweetAlert Popup --}}
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '{{ session("success") }}',
            confirmButtonColor: '#4caf50'
        });
    </script>

    {{-- 🟢 OPTION 2: Bootstrap Toast Popup --}}
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
        <div class="toast align-items-center text-bg-success border-0 show" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    {{ session('success') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let toastEl = document.querySelector('.toast');
            if (toastEl) {
                let toast = new bootstrap.Toast(toastEl);
                toast.show();
            }
        });
    </script>

@endif

@endsection