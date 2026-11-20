<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
    <meta name="theme-color" content="#064a40">
    <link rel="stylesheet" href="{{ asset('css/login-12-mayo.css') }}">
</head>
<body class="co-login-page">
    <main class="co-login-card">
        <section class="co-login-hero" aria-label="Cooperativa de Transportes 12 de Mayo">
            <div class="co-mountain co-mountain--back" aria-hidden="true"></div>
            <div class="co-mountain co-mountain--mid" aria-hidden="true"></div>
            <div class="co-mountain co-mountain--front" aria-hidden="true"></div>

            <div class="co-login-hero__content">
                <img class="co-login-hero__logo"
                     src="{{ asset('images/logo-12-mayo.png') }}"
                     alt="Escudo de la Cooperativa de Transportes 12 de Mayo R.L.">
                <h2 class="co-login-hero__title">COOPERATIVA DE TRANSPORTES<br>12 DE MAYO R.L.</h2>
                <p class="co-login-hero__subtitle">Transporte Seguro, Gente Unida</p>
            </div>

            <div class="co-login-hero__photo" aria-hidden="true">
                <img src="{{ asset('images/login-flota.jpg') }}" alt="">
            </div>
            <div class="co-login-hero__place">POTOSÍ · BOLIVIA</div>
        </section>

        <section class="co-login-form-side" aria-labelledby="login-title">
            <div class="co-login-user-icon" aria-hidden="true">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-5 0-9 2.6-9 6v1a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1v-1c0-3.4-4-6-9-6Z"/>
                </svg>
            </div>
            <h1 id="login-title">Sistema de Gestión Cooperativa</h1>
            <p class="co-login-desc">Ingrese sus credenciales para continuar</p>

            @if (session('status'))
                <div class="co-login-alert" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->has('email') || $errors->has('password'))
                <div class="co-login-error" role="alert">Revise sus credenciales e intente nuevamente.</div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="co-login-field">
                    <label class="co-login-label" for="email">Usuario (correo electrónico)</label>
                    <div class="co-login-input-wrap">
                        <svg class="co-login-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>
                        </svg>
                        <input id="email" name="email" type="email" required autofocus
                               autocomplete="username" value="{{ old('email') }}"
                               class="co-login-input" placeholder="correo@ejemplo.com">
                    </div>
                    @error('email')
                        <span class="co-login-field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="co-login-field">
                    <label class="co-login-label" for="password">Contraseña</label>
                    <div class="co-login-input-wrap">
                        <svg class="co-login-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                        </svg>
                        <input id="password" name="password" type="password" required
                               autocomplete="current-password" class="co-login-input co-login-input--password"
                               placeholder="Escriba su contraseña">
                        <button type="button" id="co-show-password" class="co-login-toggle"
                                aria-label="Mostrar contraseña" aria-pressed="false">Ver</button>
                    </div>
                    @error('password')
                        <span class="co-login-field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="co-login-options">
                    <label class="co-login-remember">
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        Recordarme
                    </label>
                    
                </div>
                <button class="co-login-submit" type="submit" data-test="login-button">Ingresar</button>
            </form>
            <p class="co-login-bottom">Acceso exclusivo para personal autorizado</p>
        </section>
    </main>
    <script>
        (() => {
            const boton = document.getElementById('co-show-password');
            const input = document.getElementById('password');
            if (!boton || !input) return;
            boton.addEventListener('click', () => {
                const visible = input.type === 'password';
                input.type = visible ? 'text' : 'password';
                boton.textContent = visible ? 'Ocultar' : 'Ver';
                boton.setAttribute('aria-pressed', visible ? 'true' : 'false');
                boton.setAttribute('aria-label', visible ? 'Ocultar contraseña' : 'Mostrar contraseña');
            });
        })();
    </script>
    @fluxScripts
</body>
</html>
