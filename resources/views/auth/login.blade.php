<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        Login - MY FIGHT co
    </title>

    <link
        rel="icon"
        type="image/png"
        href="{{ asset('images/my-fight-icon.png') }}"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        :root {
            --login-bg: #f5f6f8;
            --login-surface: #ffffff;
            --login-border: #e1e4e9;

            --login-text: #17191d;
            --login-muted: #6b717c;

            --login-gold: #d7aa35;
            --login-gold-light: #f4c542;
            --login-gold-dark: #b8871a;

            --login-radius: 1rem;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;

            background:
                var(--login-bg);

            color:
                var(--login-text);

            font-family:
                Inter,
                "Segoe UI",
                Roboto,
                Helvetica,
                Arial,
                sans-serif;
        }

        .login-page {
            min-height: 100vh;

            display: grid;

            grid-template-columns:
                minmax(320px, 0.9fr)
                minmax(460px, 1.1fr);
        }

        .login-brand-area {
            position: relative;

            min-height: 100vh;

            display: flex;
            flex-direction: column;
            justify-content: space-between;

            padding: 3rem;

            overflow: hidden;

            background:
                linear-gradient(
                    145deg,
                    #0b0b0d 0%,
                    #11151b 58%,
                    #0a1627 145%
                );

            color:
                white;
        }

        .login-brand-area::before,
        .login-brand-area::after {
            content: "";

            position: absolute;

            border-radius: 50%;

            border:
                1px solid
                rgba(215, 170, 53, 0.1);
        }

        .login-brand-area::before {
            width: 440px;
            height: 440px;

            right: -210px;
            top: -170px;
        }

        .login-brand-area::after {
            width: 320px;
            height: 320px;

            left: -180px;
            bottom: -140px;
        }

        .login-brand,
        .login-brand-content,
        .login-brand-footer {
            position: relative;

            z-index: 1;
        }

        .login-brand {
            display: inline-flex;
            align-items: center;

            width: fit-content;

            gap: 1rem;

            color: white;

            text-decoration: none;
        }

        .login-logo {
            width: 82px;
            height: 82px;

            display: block;

            object-fit: contain;

            flex-shrink: 0;

            filter:
                drop-shadow(
                    0 5px 12px
                    rgba(0, 0, 0, 0.3)
                );
        }

        .login-brand-text strong {
            display: block;

            color:
                #ffffff;

            font-size: 1.15rem;

            font-weight: 750;

            letter-spacing: 0.015em;
        }

        .login-brand-text span {
            display: block;

            margin-top: 0.25rem;

            color:
                #a6acb7;

            font-size: 0.8rem;
        }

        .login-brand-content {
            max-width: 520px;

            margin:
                auto 0;
        }

        .login-brand-content::before {
            content: "";

            width: 54px;
            height: 3px;

            display: block;

            margin-bottom: 1.4rem;

            border-radius: 999px;

            background:
                var(--login-gold);
        }

        .login-brand-content h1 {
            max-width: 520px;

            margin-bottom: 1.25rem;

            color: white;

            font-size:
                clamp(
                    2rem,
                    4vw,
                    3.4rem
                );

            font-weight: 750;

            line-height: 1.08;

            letter-spacing: -0.035em;
        }

        .login-brand-content p {
            max-width: 500px;

            margin: 0;

            color:
                #b8bdc7;

            font-size: 1.05rem;

            line-height: 1.7;
        }

        .login-brand-footer {
            color:
                #838a96;

            font-size: 0.78rem;
        }

        .login-form-area {
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            padding:
                3rem
                2rem;
        }

        .login-form-wrapper {
            width: 100%;
            max-width: 430px;
        }

        .login-mobile-brand {
            display: none;

            margin-bottom: 2rem;
        }

        .login-mobile-logo {
            width: 66px;
            height: 66px;

            object-fit: contain;
        }

        .login-title {
            margin-bottom: 0.4rem;

            color:
                var(--login-text);

            font-size: 1.8rem;

            font-weight: 750;

            letter-spacing: -0.025em;
        }

        .login-subtitle {
            margin-bottom: 2rem;

            color:
                var(--login-muted);

            line-height: 1.55;
        }

        .login-card {
            overflow: hidden;

            border:
                1px solid
                var(--login-border);

            border-radius:
                var(--login-radius);

            background:
                var(--login-surface);

            box-shadow:
                0 12px 35px
                rgba(11, 11, 13, 0.07);
        }

        .login-card::before {
            content: "";

            height: 3px;

            display: block;

            background:
                linear-gradient(
                    90deg,
                    var(--login-gold-dark),
                    var(--login-gold-light)
                );
        }

        .login-card-body {
            padding: 2rem;
        }

        .form-label {
            margin-bottom: 0.45rem;

            color: #34383f;

            font-size: 0.88rem;

            font-weight: 600;
        }

        .form-control {
            min-height: 46px;

            border-color: #d5d9df;

            border-radius: 0.65rem;

            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color:
                var(--login-gold-dark);

            box-shadow:
                0 0 0 0.2rem
                rgba(184, 135, 26, 0.17);
        }

        .btn-login {
            min-height: 46px;

            border-color:
                var(--login-gold);

            border-radius: 0.65rem;

            background:
                var(--login-gold);

            color:
                #111317;

            font-weight: 700;
        }

        .btn-login:hover,
        .btn-login:focus,
        .btn-login:active {
            border-color:
                var(--login-gold-dark)
                !important;

            background:
                var(--login-gold-dark)
                !important;

            color:
                #0b0b0d
                !important;
        }

        .login-alert {
            border-radius: 0.7rem;

            font-size: 0.88rem;
        }

        .login-alert ul {
            padding-left: 1.1rem;
        }

        .login-security-note {
            margin-top: 1.25rem;

            color:
                var(--login-muted);

            font-size: 0.78rem;

            text-align: center;
        }

        @media (
            max-width: 991.98px
        ) {
            .login-page {
                display: block;
            }

            .login-brand-area {
                display: none;
            }

            .login-form-area {
                min-height: 100vh;

                padding:
                    2rem
                    1rem;
            }

            .login-mobile-brand {
                display: flex;
                align-items: center;

                gap: 0.9rem;
            }

            .login-mobile-brand-text strong {
                display: block;

                color:
                    var(--login-text);

                font-size: 1rem;
            }

            .login-mobile-brand-text span {
                display: block;

                margin-top: 0.15rem;

                color:
                    var(--login-muted);

                font-size: 0.78rem;
            }
        }

        @media (
            max-width: 575.98px
        ) {
            .login-form-area {
                align-items: flex-start;

                padding:
                    1.5rem
                    1rem;
            }

            .login-form-wrapper {
                margin-top: 0.5rem;
            }

            .login-card-body {
                padding: 1.4rem;
            }

            .login-title {
                font-size: 1.55rem;
            }

            .login-mobile-logo {
                width: 56px;
                height: 56px;
            }
        }

        @media (
            prefers-reduced-motion: reduce
        ) {
            *,
            *::before,
            *::after {
                transition-duration:
                    0.01ms
                    !important;

                animation-duration:
                    0.01ms
                    !important;
            }
        }
    </style>
</head>

<body>

<div class="login-page">

    <section
        class="login-brand-area"
        aria-label="Apresentação do sistema"
    >

        <a
            href="{{ route('login') }}"
            class="login-brand"
            aria-label="MY FIGHT co"
        >

            <img
                src="{{ asset('images/my-fight-icon.png') }}"
                alt=""
                class="login-logo"
                aria-hidden="true"
            >

            <span class="login-brand-text">

                <strong>
                    MY FIGHT co
                </strong>

                <span>
                    Gestão de estoque e produção
                </span>

            </span>

        </a>

        <div class="login-brand-content">

            <h1>
                Estoque e produção
                em um só lugar.
            </h1>

            <p>
                Acompanhe materiais, produtos,
                movimentações e ordens de produção
                de forma centralizada.
            </p>

        </div>

        <div class="login-brand-footer">
            MY FIGHT co · Gestão de estoque e produção
        </div>

    </section>

    <main class="login-form-area">

        <div class="login-form-wrapper">

            <div class="login-mobile-brand">

                <img
                    src="{{ asset('images/my-fight-icon.png') }}"
                    alt=""
                    class="login-mobile-logo"
                    aria-hidden="true"
                >

                <div class="login-mobile-brand-text">

                    <strong>
                        MY FIGHT co
                    </strong>

                    <span>
                        Gestão de estoque e produção
                    </span>

                </div>

            </div>

            <div class="mb-4">

                <h1 class="login-title">
                    Bem-vindo
                </h1>

                <p class="login-subtitle">
                    Entre com suas credenciais
                    para acessar o sistema.
                </p>

            </div>

            <div class="login-card">

                <div class="login-card-body">

                    @if(session('sucesso'))

                        <div
                            class="
                                alert
                                alert-success
                                login-alert
                            "
                            role="status"
                        >
                            {{ session('sucesso') }}
                        </div>

                    @endif

                    @if($errors->any())

                        <div
                            class="
                                alert
                                alert-danger
                                login-alert
                            "
                            role="alert"
                        >

                            <strong>
                                Não foi possível entrar.
                            </strong>

                            <ul class="mb-0 mt-2">

                                @foreach(
                                    $errors->all()
                                    as $erro
                                )

                                    <li>
                                        {{ $erro }}
                                    </li>

                                @endforeach

                            </ul>

                        </div>

                    @endif

                    <form
                        action="{{
                            route(
                                'login.autenticar'
                            )
                        }}"
                        method="POST"
                    >

                        @csrf

                        <div class="mb-3">

                            <label
                                for="email"
                                class="form-label"
                            >
                                E-mail
                            </label>

                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="
                                    form-control

                                    @error('email')
                                        is-invalid
                                    @enderror
                                "
                                value="{{
                                    old('email')
                                }}"
                                placeholder="seu@email.com"
                                autocomplete="email"
                                required
                                autofocus
                            >

                        </div>

                        <div class="mb-4">

                            <label
                                for="password"
                                class="form-label"
                            >
                                Senha
                            </label>

                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="
                                    form-control

                                    @error('password')
                                        is-invalid
                                    @enderror
                                "
                                placeholder="Digite sua senha"
                                autocomplete="current-password"
                                required
                            >

                        </div>

                        <button
                            type="submit"
                            class="
                                btn
                                btn-login
                                w-100
                            "
                        >
                            Entrar
                        </button>

                    </form>

                </div>

            </div>

            <div class="login-security-note">
                Acesso restrito a usuários autorizados.
            </div>

        </div>

    </main>

</div>

</body>
</html>
