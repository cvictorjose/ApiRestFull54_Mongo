<!DOCTYPE html>
<html itemscope itemtype="http://schema.org/Person">
<head>
    <title>{{ $user->name ." ". $user->surname}} | Vox of Writers</title>
    <meta name="description" content="{{ $user->bio }}" />
    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $user->name }} {{ $user->surname }}"/>
    <meta property="og:type" content="person" />
    <meta property="og:url"   content="{{env('APP_FRONT_URL')}}/user/{{ $user->_id }}"/>
    <meta property="og:image" content="{{env('APP_URL')}}/user/{{ $user->_id }}/img"/>
    <meta property="og:description" content="{{ $user->bio }}"/>
    <meta property="og:site_name" content="Vox of Writers" />
    <meta property="fb:app_id" content="{{env('FACEBOOK_CLIENT_ID')}}"/>

    <meta name="twitter:card" content="summary"/>
    <meta name="twitter:site" content="@publisher_handle"/>
    <meta name="twitter:title" content="{{ $user->name }} {{ $user->surname }}"/>
    <meta name="twitter:description" content="{{ $user->bio }}"/>
    <meta name="twitter:creator" content="@author_handle"/>
    <!-- Twitter summary card with large image must be at least 200x200px -->
    <meta name="twitter:image" content="{{env('APP_FRONT_URL')}}/user/{{ $user->_id }}/img"/>

    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $user->name ." ". $user->surname }}"/>
    <meta itemprop="description" content="{{ $user->bio }}"/>
    <meta itemprop="image" content="{{env('APP_URL')}}/user/{{ $user->_id }}/img"/>

</head>
<body>
</body>
</html>