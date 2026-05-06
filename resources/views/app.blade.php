<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @vite('resources/app/app.js')
        <x-inertia::head />
    </head>
    <body class="{{ $page['props']['bodyClass'] ?? '' }}">
        <x-inertia::app />
    </body>
</html>