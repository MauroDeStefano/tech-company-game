<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Laravel') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-light">
    <div id="app">
        <!-- Header -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="#">
                    <strong>Laravel + Vue.js</strong>
                </a>
                <span class="navbar-text">
                    Stack completo pronto all'uso
                </span>
            </div>
        </nav>
        
        <!-- Hero Section -->
        <div class="bg-primary text-white py-5">
            <div class="container text-center">
                <h1 class="display-4 fw-bold mb-3">
                    🚀 Progetto Ready!
                </h1>
                <p class="lead mb-4">
                    Laravel + Vue.js + Bootstrap + Tailwind CSS + Vite
                </p>
                <div class="row justify-content-center">
                    <div class="col-auto">
                        <span class="badge bg-light text-dark fs-6 me-2">PHP {{ PHP_VERSION }}</span>
                        <span class="badge bg-light text-dark fs-6 me-2">Laravel {{ app()->version() }}</span>
                        <span class="badge bg-light text-dark fs-6">Vue.js 3</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="py-5">
            <example-component></example-component>
        </div>
        
        <!-- Footer -->
        <footer class="bg-dark text-white py-4 mt-5">
            <div class="container text-center">
                <p class="mb-0">
                    Template creato con ❤️ per lo sviluppo rapido
                </p>
            </div>
        </footer>
    </div>
</body>
</html>
