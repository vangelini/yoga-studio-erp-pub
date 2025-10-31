<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Shanti Sadhana') }}</title>
        @vite(['resources/css/app.css', 'resources/js/alpine.js'])
        <style>
            ::-webkit-scrollbar {
                width: 8px;
            }
            ::-webkit-scrollbar-track {
                background: #f1f1f1;
            }
            ::-webkit-scrollbar-thumb {
                background: #888;
                border-radius: 4px;
            }
            ::-webkit-scrollbar-thumb:hover {
                background: #555;
            }
            [x-cloak] {
                display: none !important;
            }
        </style>
        @stack('head')
    </head>
    <body class="bg-stone-50 text-stone-800 min-h-screen flex flex-col">
        @yield('body')
        @stack('scripts')
    </body>
</html>
