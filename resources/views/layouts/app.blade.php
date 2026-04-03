<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · Gestion Réseau MikroTik</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('styles.css') }}">
    
</head>
<body>
    @include('layouts.navigation')

    @yield('content')

    <script src="{{ asset('script.js') }}"></script>
    @stack('scripts')
</body>
</html>