@php
    $textContents = getContent('text_content.element')->sortBy('created_at');
@endphp

@if($textContents->count())
    <div class="text-content-section container">
        @foreach ($textContents as $textContent)
            <div class="content-item ">
                <h2 class="content-title">
                    {{ __($textContent->data_values->title) }}
                </h2>
                <div class="content-description">
                    {!! __($textContent->data_values->description) !!}
                </div>
            </div>
        @endforeach
    </div>
@endif

<style>

.text-content-section {
    border-left: 4px solid red !important;
    padding-left: 25px !important;
    margin-bottom: 40px !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

/* Title styling */
.content-title {
    font-size: 26px !important;
    font-weight: 800 !important;
    color: #2d2d2d !important;
    margin-bottom: 15px !important;
    line-height: 1.3 !important;
    max-width: 100% !important;
    word-break: break-word !important;
    white-space: normal !important;
}

/* Description styling */
.content-description {
    font-size: 16px !important;
    color: #555 !important;
    line-height: 1.8 !important;
    max-width: 100% !important;
    word-break: break-word !important;
    white-space: normal !important;
}

.content-item {
    margin-bottom: 40px !important;
}

/* Responsive styles for smaller screens */
@media (max-width: 768px) {
    .text-content-section {
        padding-left: 15px !important;
        border-left: 3px solid red !important;
    }

    .content-title {
        font-size: 22px !important;
    }

    .content-description {
        font-size: 14px !important;
    }
}

</style>
