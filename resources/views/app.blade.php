<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="format-detection" content="telephone=no">
		<meta property="og:type" content="website">
		<meta property="og:title" content="XNova Game">
		<meta property="og:image" content="{{ asset('/assets/images/logo.jpg') }}">
		<meta property="og:image:width" content="300">
		<meta property="og:image:height" content="300">
		<meta property="og:site_name" content="Звездная Империя">
		<meta property="og:description" content="Вы являетесь межгалактическим императором, который распространяет своё влияние посредством различных стратегий на множество галактик">

		<link rel="image_src" href="{{ asset('/assets/images/pwa/icon_512.png') }}"/>
		<link rel="icon" type="image/x-icon" href="/favicon.ico"/>
		<link rel="icon" type="image/png" sizes="196x196" href="{{ asset('/assets/images/pwa/icon_192.png') }}"/>
		<link rel="apple-touch-icon" type="image/png" sizes="512x512" href="{{ asset('/assets/images/pwa/icon_512.png') }}"/>
		<link rel="manifest" href="{{ asset('/manifest.json') }}"/>

        @vite('resources/app/app.js')

		<x-inertia::head>
			<title>{{ config('app.name', 'Laravel') }}</title>
		</x-inertia::head>
    </head>
    <body>
        <x-inertia::app />
    </body>
</html>