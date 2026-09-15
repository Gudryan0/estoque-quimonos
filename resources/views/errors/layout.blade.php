<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        @yield('title', 'Erro') - MY FIGHT co
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
            --error-bg: #f5f6f8;
            --error-surface: #ffffff;
            --error-border: #e1e4e8;

            --error-text: #1a1d21;
            --error-muted: #68707b;

            --error-gold: #c9a24a;
            --error-gold-dark: #9f7a2d;

            --error-radius: 1rem;
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
                linear-gradient(
                    135deg,
                    #f7f8fa,
                    #f3f4f6
                );

            color:
                var(--error-text);

            font-family:
                Inter,
                "Segoe UI",
                Roboto,
                Helvetica,
                Arial,
                sans-serif;
        }

        .error-page {
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            padding:
                2rem
                1rem;
        }

        .error-wrapper {
            width: 100%;
            max-width: 620px;
        }

        .error-brand {
            display: flex;
            align-items: center;
            justify-content: center;

            gap: 0.85rem;

            margin-bottom: 1.75rem;

            color:
                var(--error-text);

            text-decoration: none;
        }

        .error-brand:hover {
            color:
                var(--error-text);
        }

        .error-brand-logo {
            width: 58px;
            height: 58px;

            display: block;

            object-fit: contain;

            flex-shrink: 0;

            filter:
                drop-shadow(
                    0 2px 3px
                    rgba(0, 0, 0, 0.13)
                );
        }

        .error-brand-text {
            text-align: left;
        }

        .error-brand-text strong {
            display: block;

            font-size: 1rem;

            font-weight: 750;
        }

        .error-brand-text span {
            display: block;

            margin-top: 0.15rem;

            color:
                var(--error-muted);

            font-size: 0.75rem;
        }

        .error-card {
            position: relative;

            overflow: hidden;

            border:
                1px solid
                var(--error-border);

            border-radius:
                var(--error-radius);

            background:
                var(--error-surface);

            box-shadow:
                0 14px 40px
                rgba(11, 11, 13, 0.07);
        }

        .error-card::before {
            content: "";

            position: absolute;

            left: 0;
            right: 0;
            top: 0;

            height: 3px;

            background:
                linear-gradient(
                    90deg,
                    var(--error-gold-dark),
                    var(--error-gold)
                );
        }

        .error-card-body {
            padding:
                2.5rem
                2rem;

            text-align: center;
        }

        .error-code {
            margin-bottom: 0.9rem;

            color:
                #987523;

            font-size:
                clamp(
                    3.5rem,
                    12vw,
                    5.5rem
                );

            font-weight: 800;

            line-height: 1;

            letter-spacing: -0.05em;
        }

        .error-title {
            margin-bottom: 0.75rem;

            color:
                var(--error-text);

            font-size: 1.55rem;

            font-weight: 750;

            letter-spacing: -0.025em;
        }

        .error-description {
            max-width: 470px;

            margin:
                0 auto
                1.75rem;

            color:
                var(--error-muted);

            line-height: 1.65;
        }

        .error-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;

            gap: 0.65rem;
        }

        .error-actions .btn {
            min-width: 130px;

            border-radius: 0.65rem;

            font-weight: 600;
        }

        .btn-primary {
            --bs-btn-bg:
                var(--error-gold);

            --bs-btn-border-color:
                var(--error-gold);

            --bs-btn-color:
                #111315;

            --bs-btn-hover-bg:
                var(--error-gold-dark);

            --bs-btn-hover-border-color:
                var(--error-gold-dark);

            --bs-btn-hover-color:
                #ffffff;

            --bs-btn-active-bg:
                var(--error-gold-dark);

            --bs-btn-active-border-color:
                var(--error-gold-dark);
        }

        .error-footer {
            margin-top: 1.25rem;

            color:
                var(--error-muted);

            font-size: 0.75rem;

            text-align: center;
        }

        @media (
            max-width: 575.98px
        ) {
            .error-page {
                align-items: flex-start;

                padding-top: 2rem;
            }

            .error-brand-logo {
                width: 50px;
                height: 50px;
            }

            .error-card-body {
                padding:
                    2rem
                    1.25rem;
            }

            .error-title {
                font-size: 1.35rem;
            }

            .error-actions {
                flex-direction: column;
            }

            .error-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<div class="error-page">

    <div class="error-wrapper">

        <a
            href="{{
                auth()->check()
                    ? route('dashboard')
                    : route('login')
            }}"
            class="error-brand"
        >

            <img
                src="{{ asset('images/my-fight-icon.png') }}"
                alt=""
                class="error-brand-logo"
                aria-hidden="true"
            >

            <span class="error-brand-text">

                <strong>
                    MY FIGHT co
                </strong>

                <span>
                    Gestão de estoque e produção
                </span>

            </span>

        </a>

        <main class="error-card">

            <div class="error-card-body">

                <div
                    class="error-code"
                    aria-hidden="true"
                >
                    @yield('code')
                </div>

                <h1 class="error-title">
                    @yield('heading')
                </h1>

                <div class="error-description">
                    @yield('message')
                </div>

                <div class="error-actions">

                    @if(auth()->check())

                        <a
                            href="{{ route('dashboard') }}"
                            class="btn btn-primary"
                        >
                            Ir para o Dashboard
                        </a>

                    @else

                        <a
                            href="{{ route('login') }}"
                            class="btn btn-primary"
                        >
                            Ir para o Login
                        </a>

                    @endif

                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        onclick="history.back()"
                    >
                        Voltar
                    </button>

                </div>

            </div>

        </main>

        <div class="error-footer">
            Caso o problema continue, tente acessar
            novamente o sistema.
        </div>

    </div>

</div>

</body>
</html>
