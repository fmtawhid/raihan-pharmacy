@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="py-60">
        <div class="container">
            <div class="row g-4 g-xl-5">
                <div class="col-xl-12">
                    @include($activeTemplate . 'partials.quick_view')
                    @php
                        $description = preg_replace('/<\/?br>/', '', $product->description, 1);
                    @endphp
                    <div class="container mt-5">
                        <div class="row g-4"> {{-- Start row for two columns --}}
                            <!-- LEFT COLUMN: Product Details -->
                            <div class="col-md-12 col-xl-9">
                                @if ($product->specification || $description || $product->video_link || gs('product_review') || $product->specify)
                                    <div class="products-details-wrapper pt-60">
                                        <div class="products-description pt-0">

                                            {{-- Tabs --}}
                                            <ul class="nav nav-tabs d-flex flex-wrap justify-content-between text-center" id="productTabs">
                                                @if ($product->specification)
                                                    <li class="nav-item flex-fill">
                                                        <a href="#specification" class="nav-link" data-bs-toggle="tab">@lang('Specification')</a>
                                                    </li>
                                                @endif  
                                                 
                                                @if ($description)
                                                    <li class="nav-item flex-fill">
                                                        <a href="#description" class="nav-link" data-bs-toggle="tab">@lang('Description')</a>
                                                    </li>
                                                @endif
                                                @if ($product->video_link)
                                                    <li class="nav-item flex-fill">
                                                        <a href="#video" class="nav-link" data-bs-toggle="tab">@lang('Video')</a>
                                                    </li>
                                                @endif
                                                @if (gs('product_review'))
                                                    <li class="nav-item flex-fill">
                                                        <a href="#reviews" class="nav-link" data-bs-toggle="tab">
                                                            @lang('Reviews')({{ __($product->reviews_count) }})
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>

                                            {{-- Tab Content --}}
                                            <div class="tab-content product-details-tab-content">
                                                @if ($product->specification && $product->productType)
                                                    <div class="tab-pane fade" id="specification">
                                                        <div class="specification-wrapper">
                                                            <div class="specification-table d-flex flex-column">
                                                                @foreach ($product->productType->specifications as $specGroup)
                                                                    @if (collect($product->specification)->whereIn('key', $specGroup['attributes'])->whereNotNull('value')->count())
                                                                        <div>
                                                                            <h6 class="mb-2">{{ __($specGroup['group_name']) }}</h6>
                                                                            <ul>
                                                                                @foreach ($specGroup['attributes'] ?? [] as $attribute)
                                                                                    @php $spec = collect($product->specification)->firstWhere('key', $attribute); @endphp
                                                                                    @if (@$spec->value)
                                                                                        <li>
                                                                                            <span>{{ __($attribute) }}</span>
                                                                                            <span>{{ @$spec->value }}</span>
                                                                                        </li>
                                                                                    @endif
                                                                                @endforeach
                                                                            </ul>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if ($description || $product->extra_descriptions)
                                                    <div class="tab-pane fade" id="description">
                                                        @if ($description)
                                                            <div class="description-item">
                                                                @php echo $product->description @endphp
                                                            </div>
                                                        @endif
                                                        @if ($product->extra_descriptions)
                                                            <div class="description-item mt-5">
                                                                @foreach ($product->extra_descriptions as $desc)
                                                                    <h4>{{ __(@$desc['key']) }}</h4>
                                                                    <p>@php echo @$desc['value']; @endphp</p>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif

                                                @if ($product->specify)
                                                    <div class="tab-pane fade" id="specify">
                                                        <div class="specification-wrapper">
                                                            <h6 class="mb-2">@lang('Specify')</h6>
                                                            <div class="specification-item">
                                                                {!! $product->specify !!}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if ($product->video_link)
                                                    <div class="tab-pane fade" id="video">
                                                        <iframe class="product-details-video" src="{{ $product->video_link }}" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                                                    </div>
                                                @endif

                                                @if (gs('product_review'))
                                                    <div class="tab-pane fade" id="reviews">
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <h5 class="mb-0">Reviews</h5>
                                                            <a href="{{ route('user.review.index') }}" class="btn btn--base btn--sm addToCart flex-shrink-0">
                                                                <i class="fas fa-pen"></i> Write Review
                                                            </a>
                                                        </div>

                                                        <div class="review-area"></div>
                                                    </div>
                                                @endif

                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- RIGHT COLUMN: Other + Latest Products -->
                            <div class="col-md-12 col-xl-3">
                                @if ($otherProducts->count() > 0)
                                    <div class="mb-4 mt-4">
                                        <h5 class="product-details-title fw-500 mb-3">Related Product</h5>
                                        <div class="row gy-3">
                                            @foreach ($otherProducts as $relatedProduct)
                                                <div class="col-sm-6 col-md-6 col-lg-12">
                                                    <div class="best-sell-item">
                                                        <div class="best-sell-inner d-flex flex-wrap">
                                                            <div class="thumb">
                                                                <a href="{{ $relatedProduct->link() }}">
                                                                    <img src="{{ $relatedProduct->mainImage(false) }}" class="lazyload" alt="products">
                                                                </a>
                                                            </div>
                                                            <div class="content">
                                                                <h6 class="title">
                                                                    <a href="{{ $relatedProduct->link() }}">{{ __($relatedProduct->name) }}</a>
                                                                </h6>
                                                                @if (gs('product_review'))
                                                                    <div class="ratings-area">
                                                                        <span class="ratings">
                                                                            @php echo __(displayRating($relatedProduct->reviews_avg_rating)) @endphp
                                                                        </span>
                                                                        @if ($relatedProduct->reviews_count)
                                                                            <span>({{ $relatedProduct->reviews_count }})</span>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                                <div class="price fw-500">
                                                                    @php echo $relatedProduct->formattedPrice(); @endphp
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if ($latestProducts->count() > 0)
                                    <div class="">
                                        <h5 class="product-details-title fw-500 mb-3">@lang('Latest Products')</h5>
                                        <div class="row gy-3">
                                            @foreach ($latestProducts as $relatedProduct)
                                                <div class="col-sm-6 col-md-6 col-lg-12">
                                                    <div class="best-sell-item">
                                                        <div class="best-sell-inner d-flex flex-wrap">
                                                            <div class="thumb">
                                                                <a href="{{ $relatedProduct->link() }}">
                                                                    <img src="{{ $relatedProduct->mainImage(false) }}" class="lazyload" alt="products">
                                                                </a>
                                                            </div>
                                                            <div class="content">
                                                                <h6 class="title">
                                                                    <a href="{{ $relatedProduct->link() }}">{{ __($relatedProduct->name) }}</a>
                                                                </h6>
                                                                @if (gs('product_review'))
                                                                    <div class="ratings-area">
                                                                        <span class="ratings">
                                                                            @php echo __(displayRating($relatedProduct->reviews_avg_rating)) @endphp
                                                                        </span>
                                                                        @if ($relatedProduct->reviews_count)
                                                                            <span>({{ $relatedProduct->reviews_count }})</span>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                                <div class="price fw-500">
                                                                    @php echo $relatedProduct->formattedPrice(); @endphp
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Extra Styles for tab --}}
                    <style>
                        .nav-tabs {
                            border-bottom: none;
                            margin-bottom: 20px;
                            gap: 10px;
                        }
                        .nav-tabs .nav-item {
                            text-align: center;
                        }
                        .nav-tabs .nav-item .nav-link {
                            border-radius: 0.25rem;
                            padding: 12px 15px;
                            font-size: 14px;
                            font-weight: 600;
                            border: 1px solid #ddd;
                            transition: all 0.3s ease;
                            width: 100%;
                        }
                        .nav-tabs .nav-item .nav-link:hover {
                            background-color: #f1f1f1;
                            border-color: #bbb;
                        }
                        .nav-tabs .nav-item .nav-link.active {
                            background-color: #FF5C00;
                            color: white;
                            border-color: #FF5C00;
                            font-weight: 700;
                        }
                        @media (max-width: 576px) {
                            .nav-tabs {
                                flex-direction: column;
                            }
                            .nav-tabs .nav-item {
                                width: 100%;
                            }
                        }
                        .product-details-tab-content {
                            border: 1px solid #ddd;
                            padding: 20px;
                            background-color: #fff;
                        }
                    </style>

                </div>


            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        'use strict';
        (function($) {
            @if (gs('product_review'))
                loadReviews(`{{ route('product.reviews', $product->id) }}`);
            @endif

            const firstTabLink = document.querySelector('#productTabs a');

            if (firstTabLink) {
                const firstTab = new bootstrap.Tab(firstTabLink);
                firstTab.show();
            }
        })(jQuery)
    </script>
@endpush

@push('style-lib')
    <link href="{{ asset($activeTemplateTrue . 'css/owl.carousel.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/xzoom/xzoom.css') }}">
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/xzoom/magnific-popup.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset($activeTemplateTrue . 'js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue . 'js/owl-carousel.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue . 'js/xzoom/xzoom.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue . 'js/xzoom/magnific-popup.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue . 'js/xzoom/setup.js') }}"></script>
@endpush
