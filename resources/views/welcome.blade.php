<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SLEM COOP</title>
    <link rel="icon" href="{{ asset('alt-logo.png') }}" type="image/png" />

    <!-- This is the critical fix: inject React Fast Refresh preamble manually -->
    @viteReactRefresh

    <!-- Your normal Vite entries -->
    @vite([
        'resources/css/app.css',
        'resources/js/src/main.tsx'
    ])

    <!-- Optional debug styles -->
    <style>body { background: #f0f7ff; }</style>
</head>
<body>
    <div id="root"></div>

    <!-- Optional: temporary debug text to confirm Blade loads -->
    <!-- <h1 style="color: green; text-align: center;">Blade is serving – check console for React</h1> -->
</body>
</html>
