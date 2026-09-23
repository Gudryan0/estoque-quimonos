<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        @yield('title', 'MY FIGHT co')
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

    @vite('resources/css/app.css')
</head>

<body>

@php
    $menuPrincipal = [
        [
            'nome' => 'Dashboard',
            'rota' => 'dashboard',
            'ativo' => 'dashboard',
        ],
        [
            'nome' => 'Fornecedores',
            'rota' => 'fornecedores.index',
            'ativo' => 'fornecedores.*',
        ],
        [
            'nome' => 'Materiais',
            'rota' => 'materiais.index',
            'ativo' => 'materiais.*',
        ],
        [
            'nome' => 'Clientes',
            'rota' => 'clientes.index',
            'ativo' => 'clientes.*',
        ],
        [
            'nome' => 'Produtos',
            'rota' => 'produtos.index',
            'ativo' => 'produtos.*',
        ],
        [
            'nome' => 'Movimentações',
            'rota' => 'movimentacoes.index',
            'ativo' => 'movimentacoes.*',
        ],
        [
            'nome' => 'Produção',
            'rota' => 'ordens.index',
            'ativo' => 'ordens.*',
        ],
    ];

    $menuAdministracao = [
        [
            'nome' => 'Relatórios',
            'rota' => 'relatorios.index',
            'ativo' => 'relatorios.*',
        ],
        [
            'nome' => 'Usuários',
            'rota' => 'usuarios.index',
            'ativo' => 'usuarios.*',
        ],
    ];
@endphp

<a
    href="#conteudo-principal"
    class="skip-link"
>
    Pular para o conteúdo
</a>

<div class="app-layout">

    {{-- Sidebar desktop --}}
    <aside
        class="app-sidebar d-none d-lg-flex"
        aria-label="Navegação principal"
    >
        <div class="sidebar-header">
            <a
                    href="{{ route('dashboard') }}"
                    class="sidebar-brand"
                    aria-label="MY FIGHT co"
                >
                    <span
                        class="brand-symbol"
                        aria-hidden="true"
                    ></span>

                    <span>
                        <strong>
                            MY FIGHT co
                        </strong>

                        <small>
                            Estoque e produção
                        </small>
                    </span>
                </a>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section-title">
                Principal
            </div>

            @foreach($menuPrincipal as $item)
                <a
                    href="{{ route($item['rota']) }}"
                    class="
                        sidebar-link

                        {{
                            request()->routeIs(
                                $item['ativo']
                            )
                                ? 'active'
                                : ''
                        }}
                    "
                    @if(
                        request()->routeIs(
                            $item['ativo']
                        )
                    )
                        aria-current="page"
                    @endif
                >
                    {{ $item['nome'] }}
                </a>
            @endforeach

            @if(
                auth()->user()->nivel_acesso
                === 'administrador'
            )
                <div
                    class="
                        sidebar-section-title
                        mt-4
                    "
                >
                    Administração
                </div>

                @foreach(
                    $menuAdministracao
                    as $item
                )
                    <a
                        href="{{
                            route(
                                $item['rota']
                            )
                        }}"
                        class="
                            sidebar-link

                            {{
                                request()->routeIs(
                                    $item['ativo']
                                )
                                    ? 'active'
                                    : ''
                            }}
                        "
                        @if(
                            request()->routeIs(
                                $item['ativo']
                            )
                        )
                            aria-current="page"
                        @endif
                    >
                        {{ $item['nome'] }}
                    </a>
                @endforeach
            @endif
        </nav>

        <div class="sidebar-user">
            <div class="sidebar-user-info">
                <div
                    class="sidebar-user-avatar"
                    aria-hidden="true"
                >
                    {{
                        strtoupper(
                            mb_substr(
                                auth()->user()->name,
                                0,
                                1
                            )
                        )
                    }}
                </div>

                <div class="sidebar-user-text">
                    <strong>
                        {{ auth()->user()->name }}
                    </strong>

                    <span>
                        {{
                            auth()->user()->nivel_acesso
                            === 'administrador'
                                ? 'Administrador'
                                : 'Funcionário'
                        }}
                    </span>
                </div>
            </div>

            <form
                action="{{ route('logout') }}"
                method="POST"
            >
                @csrf

                <button
                    type="submit"
                    class="
                        btn
                        btn-outline-light
                        btn-sm
                        w-100
                    "
                >
                    Sair do sistema
                </button>
            </form>
        </div>
    </aside>

    {{-- Barra mobile --}}
    <header class="mobile-header d-lg-none">

        <button
            class="btn mobile-menu-button"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#menuMobile"
            aria-controls="menuMobile"
            aria-label="Abrir menu de navegação"
        >
            <span aria-hidden="true">
                ☰
            </span>
        </button>

        <a
                href="{{ route('dashboard') }}"
                class="mobile-brand"
                aria-label="MY FIGHT co"
            >
                <span
                    class="brand-symbol brand-symbol-sm"
                    aria-hidden="true"
                ></span>

                <span class="mobile-brand-name">
                    MY FIGHT co
                </span>
            </a>

        <div
            class="mobile-user-avatar"
            aria-hidden="true"
        >
            {{
                strtoupper(
                    mb_substr(
                        auth()->user()->name,
                        0,
                        1
                    )
                )
            }}
        </div>

    </header>

    {{-- Menu mobile --}}
    <div
        class="
            offcanvas
            offcanvas-start
            mobile-sidebar
        "
        tabindex="-1"
        id="menuMobile"
        aria-labelledby="menuMobileTitulo"
    >
        <div class="offcanvas-header">

            <div>
                <strong id="menuMobileTitulo">
                    MY FIGHT co
                </strong>

                <div class="small text-secondary">
                    Gestão de estoque e produção
                </div>
            </div>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="offcanvas"
                aria-label="Fechar menu"
            ></button>

        </div>

        <div class="offcanvas-body">

            <nav
                class="mobile-nav"
                aria-label="Navegação mobile"
            >
                <div class="sidebar-section-title">
                    Principal
                </div>

                @foreach($menuPrincipal as $item)
                    <a
                        href="{{
                            route(
                                $item['rota']
                            )
                        }}"
                        class="
                            mobile-nav-link

                            {{
                                request()->routeIs(
                                    $item['ativo']
                                )
                                    ? 'active'
                                    : ''
                            }}
                        "
                        @if(
                            request()->routeIs(
                                $item['ativo']
                            )
                        )
                            aria-current="page"
                        @endif
                    >
                        {{ $item['nome'] }}
                    </a>
                @endforeach

                @if(
                    auth()->user()->nivel_acesso
                    === 'administrador'
                )
                    <div
                        class="
                            sidebar-section-title
                            mt-4
                        "
                    >
                        Administração
                    </div>

                    @foreach(
                        $menuAdministracao
                        as $item
                    )
                        <a
                            href="{{
                                route(
                                    $item['rota']
                                )
                            }}"
                            class="
                                mobile-nav-link

                                {{
                                    request()->routeIs(
                                        $item['ativo']
                                    )
                                        ? 'active'
                                        : ''
                                }}
                            "
                            @if(
                                request()->routeIs(
                                    $item['ativo']
                                )
                            )
                                aria-current="page"
                            @endif
                        >
                            {{ $item['nome'] }}
                        </a>
                    @endforeach
                @endif
            </nav>

            <div class="mobile-user mt-4">
                <strong>
                    {{ auth()->user()->name }}
                </strong>

                <span>
                    {{
                        auth()->user()->nivel_acesso
                        === 'administrador'
                            ? 'Administrador'
                            : 'Funcionário'
                    }}
                </span>

                <form
                    action="{{ route('logout') }}"
                    method="POST"
                    class="mt-3"
                >
                    @csrf

                    <button
                        type="submit"
                        class="
                            btn
                            btn-outline-light
                            btn-sm
                            w-100
                        "
                    >
                        Sair do sistema
                    </button>
                </form>
            </div>

        </div>
    </div>

    {{-- Conteúdo --}}
    <div class="app-main">

        <main
            class="app-content"
            id="conteudo-principal"
            tabindex="-1"
        >

            @if(session('sucesso'))
                <div
                    class="
                        alert
                        alert-success
                        alert-dismissible
                        fade
                        show
                        app-alert
                    "
                    role="status"
                    aria-live="polite"
                >
                    <strong>
                        Sucesso.
                    </strong>

                    {{ session('sucesso') }}

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Fechar mensagem"
                    ></button>
                </div>
            @endif

            @if(session('erro'))
                <div
                    class="
                        alert
                        alert-danger
                        alert-dismissible
                        fade
                        show
                        app-alert
                    "
                    role="alert"
                    aria-live="assertive"
                >
                    <strong>
                        Atenção.
                    </strong>

                    {{ session('erro') }}

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Fechar mensagem"
                    ></button>
                </div>
            @endif

            @yield('content')

        </main>

    </div>

</div>

{{-- Modal global de confirmação --}}
<div
    class="modal fade"
    id="appConfirmModal"
    tabindex="-1"
    aria-labelledby="appConfirmModalTitle"
    aria-describedby="appConfirmModalMessage"
    aria-hidden="true"
>
    <div
        class="
            modal-dialog
            modal-dialog-centered
        "
    >
        <div class="modal-content">

            <div class="modal-header">
                <h2
                    class="modal-title fs-5"
                    id="appConfirmModalTitle"
                >
                    Confirmar ação
                </h2>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Fechar"
                ></button>
            </div>

            <div class="modal-body">
                <p
                    class="mb-0"
                    id="appConfirmModalMessage"
                >
                    Deseja continuar?
                </p>
            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                >
                    Voltar
                </button>

                <button
                    type="button"
                    class="btn btn-danger"
                    id="appConfirmModalButton"
                >
                    Confirmar
                </button>

            </div>

        </div>
    </div>
</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        /*
        |--------------------------------------------------------------------------
        | Tooltips globais
        |--------------------------------------------------------------------------
        */

        const tooltipTargets =
            document.querySelectorAll(
                '[title][tabindex="0"], [data-bs-toggle="tooltip"]'
            );

        tooltipTargets.forEach(
            function (elemento) {
                new bootstrap.Tooltip(
                    elemento,
                    {
                        container: 'body',
                        trigger: 'hover focus'
                    }
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Tabelas responsivas
        |--------------------------------------------------------------------------
        |
        | Em telas menores, algumas tabelas precisam de rolagem horizontal.
        | O aviso só é exibido quando realmente existe conteúdo escondido.
        |
        */

        const tabelasResponsivas =
            Array.from(
                document.querySelectorAll(
                    '.table-responsive'
                )
            );

        function obterAvisoTabela(
            container
        ) {
            const anterior =
                container.previousElementSibling;

            if (
                anterior
                &&
                anterior.classList.contains(
                    'table-scroll-hint'
                )
            ) {
                return anterior;
            }

            const aviso =
                document.createElement(
                    'div'
                );

            aviso.className =
                'table-scroll-hint';

            aviso.hidden = true;

            aviso.innerHTML =
                '<span aria-hidden="true">↔</span>'
                + '<span>Deslize a tabela para ver mais</span>';

            container.parentNode.insertBefore(
                aviso,
                container
            );

            return aviso;
        }

        function atualizarTabelaResponsiva(
            container
        ) {
            const aviso =
                obterAvisoTabela(
                    container
                );

            const telaCompacta =
                window.matchMedia(
                    '(max-width: 991.98px)'
                ).matches;

            const possuiOverflow =
                container.scrollWidth
                >
                container.clientWidth + 4;

            aviso.hidden =
                !(
                    telaCompacta
                    &&
                    possuiOverflow
                );
        }

        function atualizarTabelasResponsivas() {
            tabelasResponsivas.forEach(
                function (container) {
                    atualizarTabelaResponsiva(
                        container
                    );
                }
            );
        }

        atualizarTabelasResponsivas();

        window.addEventListener(
            'load',
            atualizarTabelasResponsivas
        );

        let temporizadorResize = null;

        window.addEventListener(
            'resize',
            function () {
                window.clearTimeout(
                    temporizadorResize
                );

                temporizadorResize =
                    window.setTimeout(
                        atualizarTabelasResponsivas,
                        100
                    );
            }
        );

        if (
            'ResizeObserver'
            in window
        ) {
            const observadorTabelas =
                new ResizeObserver(
                    function (entradas) {
                        entradas.forEach(
                            function (entrada) {
                                atualizarTabelaResponsiva(
                                    entrada.target
                                );
                            }
                        );
                    }
                );

            tabelasResponsivas.forEach(
                function (container) {
                    observadorTabelas.observe(
                        container
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Modal global de confirmação
        |--------------------------------------------------------------------------
        |
        | Qualquer formulário pode utilizar:
        |
        | data-confirm="Mensagem"
        |
        | E, opcionalmente:
        |
        | data-confirm-title="Título"
        | data-confirm-button="Texto do botão"
        | data-confirm-variant="danger"
        |
        */

        const modalElement =
            document.getElementById(
                'appConfirmModal'
            );

        const modalTitle =
            document.getElementById(
                'appConfirmModalTitle'
            );

        const modalMessage =
            document.getElementById(
                'appConfirmModalMessage'
            );

        const modalButton =
            document.getElementById(
                'appConfirmModalButton'
            );

        if (
            !modalElement
            ||
            !modalTitle
            ||
            !modalMessage
            ||
            !modalButton
        ) {
            return;
        }

        const modal =
            new bootstrap.Modal(
                modalElement
            );

        let pendingForm = null;
        let pendingSubmitter = null;
        let triggerElement = null;

        function aplicarVariante(
            variante
        ) {
            const variantes = [
                'primary',
                'success',
                'warning',
                'danger'
            ];

            variantes.forEach(
                function (item) {
                    modalButton.classList.remove(
                        'btn-' + item
                    );
                }
            );

            if (
                !variantes.includes(
                    variante
                )
            ) {
                variante = 'danger';
            }

            modalButton.classList.add(
                'btn-' + variante
            );
        }

        document.addEventListener(
            'submit',
            function (event) {
                const form =
                    event.target;

                if (
                    !(form instanceof HTMLFormElement)
                ) {
                    return;
                }

                const mensagem =
                    form.dataset.confirm;

                if (!mensagem) {
                    return;
                }

                if (
                    form.dataset.confirmed
                    === 'true'
                ) {
                    delete form.dataset.confirmed;
                    return;
                }

                event.preventDefault();

                pendingForm =
                    form;

                pendingSubmitter =
                    event.submitter
                    ?? null;

                triggerElement =
                    pendingSubmitter;

                modalTitle.textContent =
                    form.dataset.confirmTitle
                    || 'Confirmar ação';

                modalMessage.textContent =
                    mensagem;

                modalButton.textContent =
                    form.dataset.confirmButton
                    || 'Confirmar';

                aplicarVariante(
                    form.dataset.confirmVariant
                    || 'danger'
                );

                modal.show();
            },
            true
        );

        modalButton.addEventListener(
            'click',
            function () {
                if (!pendingForm) {
                    return;
                }

                const form =
                    pendingForm;

                const submitter =
                    pendingSubmitter;

                pendingForm = null;
                pendingSubmitter = null;

                form.dataset.confirmed =
                    'true';

                modal.hide();

                window.setTimeout(
                    function () {
                        if (
                            submitter
                            &&
                            submitter.form
                                === form
                        ) {
                            form.requestSubmit(
                                submitter
                            );
                        } else {
                            form.requestSubmit();
                        }
                    },
                    150
                );
            }
        );

        modalElement.addEventListener(
            'hidden.bs.modal',
            function () {
                pendingForm = null;
                pendingSubmitter = null;

                if (
                    triggerElement
                    &&
                    document.contains(
                        triggerElement
                    )
                ) {
                    triggerElement.focus();
                }

                triggerElement = null;
            }
        );
    }
);
</script>

</body>
</html>
