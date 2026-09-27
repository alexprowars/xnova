<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="color-scheme" content="light dark">
		<link rel="icon" href="/favicon.ico">
		@vite(['resources/app/app.js', 'resources/css/admin.css'])
		<x-inertia::head />
	</head>
	<body>
		<x-inertia::app />
	</body>
</html>
