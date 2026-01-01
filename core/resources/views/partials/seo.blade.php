@if ($seo)
    @php
        if(!isset($seoImage)){
            $seoImage = @$seoContents->image;
        }
       //dd($seoContents, $seo);
    @endphp

    <meta name="title" content="{{ @$seoContents->social_title ?? $seo->title ?? 'multitect.com.bd'}} - {{ gs()->siteName(__()) }}">
    <meta name="description" content="{{ @$seoContents->description ?? $seo->description }}">
    <meta name="keywords" content="{{ implode(',', @$seoContents->keywords ?? $seo->keywords) }}">
    <link rel="icon" href="{{ siteFavicon() }}" type="image/x-icon">

    {{-- <!-- Apple Stuff --> --}}
    <link rel="apple-touch-icon" href="{{ siteFavicon() }}" rel="shortcut icon">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="{{ @$seoContents->social_title ?? $seo->title ?? 'multitect.com.bd' }} - {{ gs()->siteName(__()) }}">
    {{-- <!-- Google / Search Engine Tags --> --}}
    <meta itemprop="name" content="{{ gs()->siteName($pageTitle) }}">
    <meta itemprop="description" content="{{ @$seoContents->description ?? $seo->description }}">
    <meta itemprop="image" content="{{ $seoImage ?? getImage(getFilePath('seo') . '/' . $seo->image) }}">
    {{-- <!-- Facebook Meta Tags --> --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ @$seoContents->social_title ?? $seo->title ?? 'multitect.com.bd' }} - {{ gs()->siteName(__()) }}">
    <meta property="og:description" content="{{ @$seoContents->social_description ?? $seo->social_description }}">
    <meta property="og:image" content="{{ $seoImage ?? getImage(getFilePath('seo') . '/' . $seo->image) }}">
    <meta property="og:image:type" content="image/{{ pathinfo(getImage(getFilePath('seo')) . '/' . $seo->image)['extension'] }}">
    <meta property="og:logo" content="{{ siteFavicon() }}">
    @php $socialImageSize =  @$seoContents->image_size ?  explode('x', @$seoContents->image_size) :explode('x', getFileSize('seo')) @endphp
    <meta property="og:image:width" content="{{ $socialImageSize[0] }}">
    <meta property="og:image:height" content="{{ $socialImageSize[1] }}">
    
    <meta property="og:url" content="{{ url()->current() }}">
    {{-- <!-- Twitter Meta Tags --> --}}
    <meta name="twitter:card" content="summary_large_image">
@endif
