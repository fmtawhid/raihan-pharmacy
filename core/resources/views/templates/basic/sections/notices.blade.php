@php
    $notices = getContent('notices.element', orderById: true);
@endphp

@if($notices->count())
    <style>
        .notice-box {
            position: relative;
            background: #fff7f6;
            border: 1px solid #ffddd8;
            border-left: 4px solid #DD4637;
            padding: 10px 15px;
            overflow: hidden;
            height: 50px; /* ফিকসড হাইট */
            display: flex;
            align-items: center;
            width: 75%;
            margin: 0 auto;
            border-radius: 10px;
        }

        .single-notice {
            position: absolute;
            white-space: nowrap;
            right: -100%; /* ডান পাশ েকে শুরু */
            display: flex;
            align-items: center;
            transition: transform 0.5s linear;
        }

        .single-notice a {
            color: #DD4637;
            text-decoration: none;
            font-weight: 600;
        }

        .single-notice a:hover {
            color: rgb(146, 47, 38);
            text-decoration: underline;
        }

        /* Mobile Responsive */
        @media (max-width: 767px) {
            .notice-box {
                width: 100%;
                padding: 8px 10px;
                height: 45px;
            }

            .single-notice a {
                font-size: 14px;
            }
        }

        @media (max-width: 380px) {
            .notice-box {
                padding: 6px 8px;
                height: 40px;
            }

            .single-notice a {
                font-size: 13px;
            }
        }
    </style>

    <div class="notice-box">
        @foreach ($notices as $notice)
            <div class="single-notice">
                <a href="{{ __($notice->data_values->link) }}" target="_blank">
                    <i class="fas fa-bullhorn me-1"></i> {{ __($notice->data_values->title) }}
                </a>
            </div>
        @endforeach
    </div>

    @push('script')
        <script>
            (function($){
                "use strict";
                let $notices = $('.single-notice');
                let total = $notices.length;
                let index = 0;

                function showNotice() {
                    $notices.css('right', '-100%'); // সব নোটিশ ডন বাইরে
                    let $current = $notices.eq(index);
                    let width = $current.outerWidth(true);

                    $current.css('right', '-' + width + 'px'); // ডান পাশ েকে শুরু
                    $current.animate({ right: '100%' }, 15000, 'linear', function() {
                        index++;
                        if(index >= total) index = 0;
                        showNotice();
                    });
                }

                showNotice(); // শুু
            })(jQuery);
        </script>
    @endpush
@endif
