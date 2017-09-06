<!DOCTYPE html>
<html itemscope itemtype="http://schema.org/Article">
<head>
@php
$title= substr(strip_tags($story->name),0,50);
$body = substr(strip_tags($story->body),0,200).'...';
@endphp
    <title>{{ isset($title) ? $title." | ":""}} Vox of Writers</title>
    <meta name="description" content="{{ $body }}" />

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $title }}" />
    <meta property="og:type" content="article" />
    <meta property="og:image" content="{{env('APP_URL')}}/story/{{ $story->_id }}/img"/>
    <meta property="og:url"   content="{{env('APP_FRONT_URL')}}/story/{{ $story->_id }}"/>
    <meta property="og:description" content="{{ $body }}"/>
    <meta property="og:site_name" content="Vox of Writers"/>
    <meta property="article:published_time" content="{{ $story->created_at }}" />
    <meta property="article:modified_time" content="{{ $story->updated_at }}" />

    <meta property="fb:app_id" content="{{env('FACEBOOK_CLIENT_ID')}}"/>


    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $title }}"/>
    <meta itemprop="description" content="{{ $body }}"/>
    <meta itemprop="image" content="{{env('APP_URL')}}/story/{{ $story->_id }}/img"/>
    <meta itemprop="author" content="@author_handle"/>
    <meta itemprop="datePublished" content="{{ $story->created_at }}"/>
    <meta itemprop="headline" content="{{ $body }}"/>
    <meta itemprop="publisher" content="Vox of Writers"/>
    <meta itemprop="dateModified" content="{{ $story->updated_at }}"/>

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary_large_image"/>
    <meta name="twitter:site" content="@publisher_handle"/>
    <meta name="twitter:title" content="{{ $title }}"/>
    <meta name="twitter:description" content="{{ $body }}"/>
    <meta name="twitter:creator" content="@author_handle"/>
    <!-- Twitter summary card with large image must be at least 280x150px -->
    <meta name="twitter:image" content="{{env('APP_URL')}}/story/{{ $story->_id }}/img"/>

</head>
<body>
</body>
</html>
