@php
$content = getContent('how_to_order.content', true);
$elements = getContent('how_to_order.element');
@endphp

@if (@$content->data_values->heading || $elements->count())
<section class="orderProcessStep pt-8 pb-100 my-5 position-relative overflow-hidden z-1 trending-products-area">
    <div class="container">
        <div class="py-5">
            <div class="row align-items-center">
                <div class="col-xl-5">
                    <div class="section-title text-center text-xl-start">
                        @if (@$content->data_values->heading)
                        <h3 class="mb-0">{{ __(@$content->data_values->heading) }}</h3>
                        @endif
                    </div>
                </div>
            </div>
            <div class="howToOrder">
                <div class="row">
                    <div class="col-md-6 d-flex align-items-center">
                        @if ($elements->count())
                        <ul class="buyignGuide pt-5" style="list-style-type: disc; list-style-position:inside;">
                            @foreach ($elements as $element)
                            <li>{!! __(@$element->data_values->step_text) !!}</li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                    <div class="col-md-6">
                        @if (@$content->data_values->video_url)
                        @php
                            $videoUrl = @$content->data_values->video_url;
                            // Convert regular YouTube URL to embed URL
                            if (str_contains($videoUrl, 'youtube.com/watch')) {
                                preg_match('/[?&]v=([^&]+)/', $videoUrl, $matches);
                                if (!empty($matches[1])) {
                                    $videoUrl = 'https://www.youtube.com/embed/' . $matches[1];
                                }
                            } elseif (str_contains($videoUrl, 'youtu.be/')) {
                                preg_match('/youtu\.be\/([^?&]+)/', $videoUrl, $matches);
                                if (!empty($matches[1])) {
                                    $videoUrl = 'https://www.youtube.com/embed/' . $matches[1];
                                }
                            }
                        @endphp
                        <iframe width="560" height="315" src="{{ $videoUrl }}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('style')
<style>
    .orderProcessStep {
        background-color: #d6ecec;
    }

    .buyignGuide {
        list-style-type: disc;
        list-style-position: inside;
        padding: 0;
        margin: 0;
    }

    .buyignGuide li {
        margin-bottom: 15px;
        font-size: 17px;
        font-weight: 500;
        color: #222;
        line-height: 1.6;
    }

    .howToOrder iframe {
        width: 100%;
        height: 315px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 991px) {
        .buyignGuide li {
            font-size: 15px;
        }

        .howToOrder iframe {
            height: 250px;
        }
    }
</style>
@endpush
@endif