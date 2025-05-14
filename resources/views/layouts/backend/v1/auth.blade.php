<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $title ?? 'Login' }} | {{ config('app.name') }}</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="{{ $title ?? 'Login' }} | {{ config('app.name') }}" />
    <meta name="author" content="{{ config('app.name') }}" />
    <meta name="description" content="{{ $description ?? 'Login' }}" />
    <meta name="keywords" content="{{ $keywords ?? 'Login' }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css"
        integrity="sha256-tZHrRjVqNSRyWg2wbppGnT833E/Ys0DHWGwT04GiqQg=" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI=" crossorigin="anonymous" />
    <link rel="stylesheet" href="{{ get_template_url('css/adminlte.css') }}" />
</head>

    @yield('content')

</html>
