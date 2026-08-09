@extends('Template::layouts.app')
@section('app')
    <div class="body-overlay" id="body-overlay"></div>
    @include('Template::partials.preloader')
    @include('Template::partials.header')
    <main>
        @yield('content')
    </main>

    @if (!Route::is('cart.page'))
        <div class="site-sidebar cart-sidebar-area" id="cart-sidebar-area">
            <button class="sidebar-close-btn"><i class="las la-times"></i></button>
            <div class="top-content d-flex gap-2">
                <h5 class="cart-sidebar-area__title">@lang('My Cart')</h5> <a href="{{ route('cart.page') }}"
                    class="text-muted text-decoration-underline">@lang('Cart Page')</a>
            </div>
            <div class="cart-products cart--products"></div>
        </div>
    @endif

    @if (gs('product_wishlist'))
        <div class="site-sidebar cart-sidebar-area wishlist-sidebar" id="wish-sidebar-area">
            <button class="sidebar-close-btn"><i class="las la-times"></i></button>
            <div class="top-content d-flex gap-2">
                <h5 class="cart-sidebar-area__title">@lang('My Wishlist')</h5> <a href="{{ route('wishlist.page') }}"
                    class="text-muted text-decoration-underline">@lang('Wishlist Page')</a>
            </div>
            <div class="cart-products wish--products"></div>
        </div>
    @endif

    @auth
        <div class="site-sidebar sidebar-nav" id="authSidebarMenu">
            <button type="button" class="sidebar-close-btn"><i class="las la-times"></i></button>

            <ul class="text--white login-user-menu">
                @include('Template::user.partials.sidebar')
            </ul>
        </div>
    @endauth

    @include('Template::partials.footer')

    <!-- Floating Contact Widget -->
    <div class="floating-contact-widget">
        <style>
            .floating-contact-widget {
                position: fixed;
                bottom: 30px;
                left: 30px;
                z-index: 999;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }

            .contact-menu-btn {
                width: 65px;
                height: 65px;
                border-radius: 50%;
                background: #FF6B35;
                border: none;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 32px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
                transition: all 0.3s ease;
                color: #fff;
                position: relative;
                z-index: 10;
            }

            .contact-menu-btn:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            }

            .contact-menu-btn.active {
                background: #FF5A1F;
            }

            .contact-menu {
                position: absolute;
                bottom: 85px;
                left: 0;
                display: flex;
                flex-direction: column;
                gap: 15px;
                opacity: 0;
                visibility: hidden;
                transform: translateY(20px);
                transition: all 0.3s ease;
                pointer-events: none;
            }

            .contact-menu.show {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
                pointer-events: auto;
            }

            .contact-item {
                width: 65px;
                height: 65px;
                border-radius: 50%;
                border: none;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 32px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
                transition: all 0.3s ease;
                text-decoration: none;
                color: #fff;
            }

            .contact-item:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            }

            .contact-item.whatsapp {
                background: #25D366;
            }

            .contact-item.messenger {
                background: #0084FF;
            }

            .contact-item.phone {
                background: #28a745;
            }

            .contact-item.email {
                background: #ff6b6b;
            }

            /* Mobile Responsive */
            @media (max-width: 768px) {
                .floating-contact-widget {
                    bottom: 90px;
                    left: 20px;
                }

                .contact-menu-btn {
                    width: 55px;
                    height: 55px;
                    font-size: 26px;
                }

                .contact-item {
                    width: 55px;
                    height: 55px;
                    font-size: 26px;
                }

                .contact-menu {
                    bottom: 70px;
                    gap: 12px;
                }
            }

            @media (max-width: 480px) {
                .floating-contact-widget {
                    bottom: 100px;
                    left: 15px;
                }

                .contact-menu-btn {
                    width: 50px;
                    height: 50px;
                    font-size: 24px;
                }

                .contact-item {
                    width: 50px;
                    height: 50px;
                    font-size: 24px;
                }

                .contact-menu {
                    bottom: 65px;
                    gap: 10px;
                }
            }
        </style>

        <div class="contact-menu" id="contactMenu">
            <a href="https://wa.me/8801911997241?text=Hello" target="_blank" class="contact-item whatsapp">
                <i class="lab la-whatsapp"></i>
            </a>

            <a href="https://m.me/yourpage" target="_blank" class="contact-item messenger">
                <i class="lab la-facebook-messenger"></i>
            </a>

            <a href="tel:+8801911997241" class="contact-item phone">
                <i class="las la-phone"></i>
            </a>

            <a href="mailto:you@email.com" class="contact-item email">
                <i class="las la-envelope"></i>
            </a>
        </div>

        <button class="contact-menu-btn" id="contactMenuBtn">
            <i class="las la-comments"></i>
        </button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const contactMenuBtn = document.getElementById('contactMenuBtn');
            const contactMenu = document.getElementById('contactMenu');

            contactMenuBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                contactMenuBtn.classList.toggle('active');
                contactMenu.classList.toggle('show');
            });

            // Close menu when clicking outside
            document.addEventListener('click', function (e) {
                if (!e.target.closest('.floating-contact-widget')) {
                    contactMenuBtn.classList.remove('active');
                    contactMenu.classList.remove('show');
                }
            });
        });
    </script>


    @guest
        <!-- Modal -->
        <div class="modal custom--modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <h5 class="modal-title" id="loginModalTitle">@lang('Login to your account')</h5>
                        <button type="button" class="close modal-close-btn" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                        <div class="login-wrapper">
                            <form method="POST" action="{{ route('user.login') }}" class="sign-in-form">
                                @csrf
                                <div class="form-group">
                                    <label class="form--label" for="login-username">@lang('Username')</label>
                                    <input type="text" class="form--control" name="username" id="login-username"
                                        value="{{ old('email') }}">
                                </div>
                                <div class="form-group">
                                    <label class="form--label" for="login-pass">@lang('Password')</label>
                                    <input type="password" class="form--control" name="password" id="login-pass">
                                </div>

                                <div class="form-group">
                                    <div class="d-flex gap-1 flex-wrap justify-content-between">
                                        <div class="form-check form--check d-flex gap-1 align-items-center mb-0">
                                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                            <label class="form-check-label mb-0 lh-1" for="remember">
                                                @lang('Remember Me')
                                            </label>
                                        </div>

                                        <a href="{{ route('user.password.request') }}"
                                            class="t-link d-block text-end text--base heading-clr sm-text fw-md">
                                            @lang('Forgot Password?')
                                        </a>
                                    </div>
                                </div>

                                <x-captcha></x-captcha>


                                <button type="submit" class="btn btn--base w-100 h-45">@lang('Login')</button>

                                <p class="create-accounts mb-0 mt-2">
                                    <span class="text-dark">@lang('Don\'t have an account?') <a
                                            href="{{ route('user.register') }}"
                                            class="text--base">@lang('Create An Account')</a> </span>
                                </p>
                            </form>

                            @include('Template::partials.social_login')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endguest
@endsection