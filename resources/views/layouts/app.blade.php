<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Proyecto Sam')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div class="toast-stack">
        @php
            $success = session('success') ?? session('status');
            $error = session('error');
        @endphp

        @if ($success)
            <div class="toast toast-success" role="status" aria-live="polite">
                <div class="toast-icon">✓</div>
                <div class="toast-body">
                    <div class="toast-title">Todo listo</div>
                    <div class="toast-message">{{ $success }}</div>
                </div>
                <button type="button" class="toast-close" data-toast-close aria-label="Cerrar">
                    ✕
                </button>
            </div>
        @elseif ($error)
            <div class="toast toast-error" role="alert" aria-live="assertive">
                <div class="toast-icon">!</div>
                <div class="toast-body">
                    <div class="toast-title">Ocurrió un problema</div>
                    <div class="toast-message">{{ $error }}</div>
                </div>
                <button type="button" class="toast-close" data-toast-close aria-label="Cerrar">
                    ✕
                </button>
            </div>
        @elseif ($errors->any())
            <div class="toast toast-error" role="alert" aria-live="assertive">
                <div class="toast-icon">!</div>
                <div class="toast-body">
                    <div class="toast-title">Revisa el formulario</div>
                    <div class="toast-message">
                        {{ $errors->first() }}
                    </div>
                </div>
                <button type="button" class="toast-close" data-toast-close aria-label="Cerrar">
                    ✕
                </button>
            </div>
        @endif
    </div>

    <div class="app-shell">
        <div class="card">
            @yield('content')
        </div>
    </div>
</body>
</html>

