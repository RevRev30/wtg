<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WTG')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <header class="bg-white shadow-sm border-b fixed top-0 left-0 right-0 h-16 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center justify-between">
            <div class="flex items-center">
                <h1 class="text-xl font-semibold text-gray-900">WTG Staff Portal</h1>
            </div>
            <nav class="flex space-x-4">
                <a href="#" class="text-gray-700 hover:text-gray-900">Dashboard</a>
                <a href="#" class="text-gray-700 hover:text-gray-900">Seating</a>
                <a href="#" class="text-gray-700 hover:text-gray-900">Reports</a>
            </nav>
        </div>
    </header>

    <main class="pt-16">
        {{-- Page content --}}
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
