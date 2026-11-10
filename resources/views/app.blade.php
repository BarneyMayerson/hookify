<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- Inline script to detect system dark mode preference and apply it immediately --}}
  <script>
    (function() {
      const appearance = '{{ $appearance ?? 'system' }}';

      if (appearance === 'system') {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (prefersDark) {
          document.documentElement.classList.add('dark');
        }
      }
    })();
  </script>

  {{-- Inline style to set the HTML background color based on our theme in app.css --}}
  <style>
    html {
      background-color: hsl(0 0% 100%);
    }

    html.dark {
      background-color: hsl(240 25% 4%);
    }
  </style>

  <link rel="icon" href="/favicon.ico" sizes="any">
  <link rel="icon" href="/favicon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">

  @fonts

  @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
  <link rel="canonical" href="{{ url()->current() }}">

  <x-inertia::head>
    <title>{{ config('app.name', 'Hookify') }} — ВАШ_СЛОГАН</title>
    <meta name="description" data-inertia="description" content="Setting up pre-commit hooks">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0a0a0f" media="(prefers-color-scheme: dark)">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name', 'Hookify') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" data-inertia="og:title" content="{{ config('app.name', 'Hookify') }} — ВАШ_СЛОГАН">
    <meta property="og:description" data-inertia="og:description" content="ОПИСАНИЕ">
    <meta property="og:image" content="{{ asset('og-image.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
  </x-inertia::head>
</head>

<body class="font-sans antialiased">
  <x-inertia::app />
</body>

</html>
