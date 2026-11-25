<!DOCTYPE html>
<html lang="es" data-theme="cobalt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión - Ferretería Jack Sparkys</title>
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-bg font-sans antialiased text-slate-800 flex items-center justify-center px-4 py-5 min-h-dvh">
    <div class="w-full max-w-md">
        {{ $slot }}
    </div>
    @livewireScripts
</body>
</html>
