@php
    $content = getContent('order_confirmation.content', true);
@endphp

@extends('Template::layouts.checkout')

@section('blade')
    <div class="address-wrapper">
        <div class="confirmation-card">
            <div class="confirmation-card-icon">
                <img src="{{ asset($activeTemplateTrue . 'images/order-completed.gif') }}" class="w-100 lazyload"
                    alt="image">

            </div>

            <h3 class="confirmation-card-title mb-2">{{ __(@$content->data_values->title) }}</h3>
            <p class="confirmation-card-desc mb-4">{{ __(@$content->data_values->description) }}</p>
            <h5>Your Order ID: <code>{{ $order->order_number }}</code></h5>
            @if (auth()->check())
                <a href="{{ route('user.order', $order->order_number) }}"
                    class="btn btn-outline--light h-45">@lang('View Order Details')</a>
            @else
                <p>Please check your email for the link to check your Order Status Anytime.</p>
            @endif

            {{-- <a href="{{ route('user.order', $order->order_number) }}" class="btn btn--base mt-2">
                View Order Summary
            </a> --}}

            @guest
                <a href="{{ route('guest.order.view', $order->order_number) }}" class="btn btn--base mt-2">
                    View Order Summary
                </a>
            @endguest
            {{-- <p class="confirmation-card-desc mb-4">{{ __(@$content->data_values->description) }}</p> --}}
            {{-- <a href="{{ route('user.order', $order->order_number) }}"
                class="btn btn-outline--light h-45">@lang('View Order Details')</a> --}}
        </div>
    </div>
@endsection
