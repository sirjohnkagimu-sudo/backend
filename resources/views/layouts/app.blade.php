<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Edumall') }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('storage/logo/favicon.ico') }}" type="image/x-icon"/>

    <!-- Google Fonts (Modern SaaS) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap (keep for existing pages) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Tailwind CDN (for auth + modern UI) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Custom Theme -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            indigo: '#4f46e5',
                            emerald: '#10b981',
                        }
                    },
                }
            }
        }
    </script>

    <!-- Alpine.js (for dropdowns / interactions if needed) -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

</head>

<body class="font-sans antialiased bg-gray-100 text-gray-800">

    <div class="min-h-screen">

        {{-- Hide navigation on auth pages --}}
        @unless (
            request()->routeIs('login') ||
            request()->routeIs('register') ||
            request()->routeIs('password.*')
        )
            @include('layouts.navigation')
        @endunless

        {{-- Page Header --}}
        @isset($header)
            @if($header)
                <header class="bg-white shadow-sm border-b border-gray-200">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                        <h1 class="text-xl font-semibold text-gray-800">
                            {{ $header }}
                        </h1>
                    </div>
                </header>
            @endif
        @endisset

        {{-- Main Content --}}
        <main>
            @yield('content')
        </main>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Laravel global JS -->
    <script>
        window.Laravel = {!! json_encode(['user_id' => auth()->id()]) !!};
    </script>

</body>
</html>
