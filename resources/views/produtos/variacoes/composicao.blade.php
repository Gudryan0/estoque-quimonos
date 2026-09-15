@extends('layouts.app')

@section('title', 'Ficha de Consumo')

@section('content')

@php
    $formatarQuantidade =
        function ($valor, $unidade) {
            $numero = (float) $valor;

            $unidadeDiscreta =
                in_array(
                    $unidade,
                    [
                        'un',
                        'rolo',
                    ],
                    true
                );

            $ehInteiro =
                abs(
                    $numero
                    - round($numero)
                ) < 0.0005;

            $decimais =
                $unidadeDiscreta
                && $ehInteiro
                    ? 0
                    : 3;

            $quantidade = number_format(
                $numero,
                $decimais,
                ',',
                '.'
            );

            $sufixo = match ($unidade) {
                'un' => 'un.',

                'rolo' =>
                    abs(
                        $numero - 1
                    ) < 0.0005
                        ? 'rolo'
                        : 'rolos',

                default => $unidade,
            };

            return
                $quantidade
                . ' '
                . $sufixo;
        };

    $rotulosCategoria = [
        'tecido' => 'Tecido',
        'eva' => 'EVA',
        'fita' => 'Fita',
        'linha' => 'Linha',
        'etiqueta' => 'Etiqueta',
        'outro' => 'Outro',
    ];

    $fichaEditavel =
        $produto->ativo
        && $variacao->ativo;
@endphp

<div class="mb-3">
    <a
        href="{{
            route(
                'produtos.variacoes.index',
                [
                    'produto' => $produto,

                    'status' =>
                        $variacao->ativo
                            ? 'ativos'
                            : 'inativos',
                ]
            )
        }}"
        class="btn btn-outline-secondary btn-sm"
    >
        ← Voltar para variações
    </a>
</div>

<div
    class="
        d-flex
        flex-column
        flex-md-row
        justify-content-between
        align-items-md-start
        gap-3
        mb-4
    "
>
    <div>
        <h1 class="mb-1">
            Ficha de consumo
        </h1>

        <p class="text-muted mb-0">
            <strong class="text-body">
                {{ $produto->nome_produto }}
            </strong>

            ·

            {{ $variacao->tamanho }}

            /

            {{ $variacao->cor }}
        </p>
    </div>

    <span
        class="
            badge

            {{
                $fichaEditavel
                    ? 'bg-primary-subtle text-primary-emphasis border border-primary-subtle'
                    : 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle'
            }}
        "
    >
        {{
            $fichaEditavel
                ? 'Ficha editável'
                : 'Somente consulta'
        }}
    </span>
</div>

<div
    class="
        alert
        bg-primary-subtle
        text-primary-emphasis
        border
        border-primary-subtle
        mb-4
    "
>
    Informe quanto de cada material é necessário
    para produzir <strong>uma unidade</strong>
    desta variação.

    Materiais medidos em
    <strong>metro</strong> ou
    <strong>quilograma</strong>
    podem utilizar até três casas decimais.

    Materiais medidos em
    <strong>unidade</strong> ou
    <strong>rolo</strong>
    utilizam quantidades inteiras.
</div>

@if(!$fichaEditavel)

    <div
        class="
            alert
            bg-warning-subtle
            text-warning-emphasis
            border
            border-warning-subtle
            mb-4
        "
    >
        <strong>
            Ficha em modo de consulta.
        </strong>

        O produto ou esta variação está inativo.
        A composição existente continuará visível,
        mas não poderá ser alterada até que ambos
        estejam ativos novamente.
    </div>

@endif

@if($errors->any())

    <div
        class="
            alert
            alert-danger
            mb-4
        "
    >
        <strong>
            Verifique os dados informados.
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

@if($fichaEditavel)

    <div class="card mb-5">
        <div class="card-body p-4">

            <div class="mb-4">
                <h2 class="h5 mb-1">
                    Adicionar ou atualizar material
                </h2>

                <p class="text-muted small mb-0">
                    Selecionar um material que já está
                    na ficha atualizará sua quantidade.
                </p>
            </div>

            <form
                action="{{
                    route(
                        'produtos.variacoes.composicao.store',
                        [
                            $produto,
                            $variacao,
                        ]
                    )
                }}"
                method="POST"
            >
                @csrf

                <div class="row g-3 align-items-end">

                    <div class="col-lg-7">
                        <label
                            for="id_material"
                            class="form-label"
                        >
                            Material

                            <span class="text-danger">
                                *
                            </span>
                        </label>

                        <select
                            name="id_material"
                            id="id_material"
                            class="
                                form-select

                                @error('id_material')
                                    is-invalid
                                @enderror
                            "
                            required
                        >
                            <option
                                value=""
                                data-unidade=""
                                data-consumo=""
                            >
                                Selecione um material
                            </option>

                            @foreach(
                                $materiais
                                as $material
                            )

                                @php
                                    $composicaoAtual =
                                        $consumosPorMaterial
                                            ->get(
                                                $material
                                                    ->id_material
                                            );

                                    $categoriaMaterial =
                                        $rotulosCategoria[
                                            $material
                                                ->categoria
                                        ]
                                        ??
                                        ucfirst(
                                            $material
                                                ->categoria
                                        );
                                @endphp

                                <option
                                    value="{{
                                        $material
                                            ->id_material
                                    }}"
                                    data-unidade="{{
                                        $material
                                            ->unidade_medida
                                    }}"
                                    data-consumo="{{
                                        $composicaoAtual
                                            ? (float)
                                                $composicaoAtual
                                                    ->quantidade_por_unidade
                                            : ''
                                    }}"
                                    @selected(
                                        (string)
                                        old('id_material')
                                        ===
                                        (string)
                                        $material
                                            ->id_material
                                    )
                                >
                                    {{ $categoriaMaterial }}

                                    ·

                                    {{
                                        $material
                                            ->nome_material
                                    }}

                                    @if($material->cor)
                                        ·
                                        {{ $material->cor }}
                                    @endif

                                    ·

                                    {{
                                        $formatarQuantidade(
                                            $material
                                                ->quantidade,
                                            $material
                                                ->unidade_medida
                                        )
                                    }}

                                    em estoque
                                </option>

                            @endforeach
                        </select>

                        @error('id_material')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="form-text">
                            Apenas materiais ativos
                            podem ser adicionados.
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <label
                            for="quantidade_por_unidade"
                            class="form-label"
                        >
                            Consumo por unidade

                            <span class="text-danger">
                                *
                            </span>
                        </label>

                        <div class="input-group">
                            <input
                                type="number"
                                name="quantidade_por_unidade"
                                id="quantidade_por_unidade"
                                class="
                                    form-control

                                    @error(
                                        'quantidade_por_unidade'
                                    )
                                        is-invalid
                                    @enderror
                                "
                                value="{{
                                    old(
                                        'quantidade_por_unidade'
                                    )
                                }}"
                                min="0.001"
                                step="0.001"
                                inputmode="decimal"
                                required
                            >

                            <span
                                class="input-group-text"
                                id="unidadeQuantidade"
                            >
                                —
                            </span>

                            @error(
                                'quantidade_por_unidade'
                            )
                                <div
                                    class="invalid-feedback"
                                >
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div
                            class="form-text"
                            id="ajudaQuantidade"
                        >
                            Selecione um material.
                        </div>
                    </div>

                    <div class="col-lg-2">
                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            Salvar
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>

@endif

<div
    class="
        d-flex
        justify-content-between
        align-items-end
        gap-3
        mb-3
    "
>
    <div>
        <h2 class="h4 mb-1">
            Materiais necessários
        </h2>

        <p class="text-muted small mb-0">
            Componentes consumidos na fabricação
            de uma unidade desta variação.
        </p>
    </div>

    @if($composicoes->isNotEmpty())
        <span
            class="
                badge
                bg-secondary-subtle
                text-secondary-emphasis
                border
                border-secondary-subtle
            "
        >
            {{ $composicoes->count() }}

            {{
                $composicoes->count() === 1
                    ? 'material'
                    : 'materiais'
            }}
        </span>
    @endif
</div>

@if($composicoes->isEmpty())

    <div
        class="
            alert
            bg-warning-subtle
            text-warning-emphasis
            border
            border-warning-subtle
        "
    >
        <strong>
            Ficha de consumo não definida.
        </strong>

        Esta variação ainda não possui materiais
        cadastrados.

        Enquanto a ficha não for definida,
        uma ordem contendo esta variação
        não poderá ser iniciada.
    </div>

@else

    <div class="card mb-4">
        <div class="table-responsive">
            <table
                class="
                    table
                    table-hover
                    align-middle
                    mb-0
                "
            >
                <thead>
                    <tr>
                        <th>
                            Material
                        </th>

                        <th>
                            Consumo por unidade
                        </th>

                        <th>
                            Estoque atual
                        </th>

                        <th>
                            Situação
                        </th>

                        <th class="text-end">
                            Ações
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach(
                        $composicoes
                        as $composicao
                    )

                        @php
                            $material =
                                $composicao->material;

                            $categoriaMaterial =
                                $rotulosCategoria[
                                    $material->categoria
                                ]
                                ??
                                ucfirst(
                                    $material->categoria
                                );

                            if (!$material->ativo) {
                                $situacaoTexto =
                                    'Material inativo';

                                $situacaoClasse =
                                    'bg-secondary-subtle '
                                    . 'text-secondary-emphasis '
                                    . 'border '
                                    . 'border-secondary-subtle';

                            } elseif (
                                (float)
                                $material->quantidade
                                <= 0
                            ) {
                                $situacaoTexto =
                                    'Sem estoque';

                                $situacaoClasse =
                                    'bg-danger-subtle '
                                    . 'text-danger-emphasis '
                                    . 'border '
                                    . 'border-danger-subtle';

                            } elseif (
                                (float)
                                $material->estoque_minimo
                                > 0
                                &&
                                (float)
                                $material->quantidade
                                <=
                                (float)
                                $material->estoque_minimo
                            ) {
                                $situacaoTexto =
                                    'Estoque baixo';

                                $situacaoClasse =
                                    'bg-warning-subtle '
                                    . 'text-warning-emphasis '
                                    . 'border '
                                    . 'border-warning-subtle';

                            } else {
                                $situacaoTexto =
                                    'Disponível';

                                $situacaoClasse =
                                    'bg-success-subtle '
                                    . 'text-success-emphasis '
                                    . 'border '
                                    . 'border-success-subtle';
                            }
                        @endphp

                        <tr>
                            <td>
                                <strong>
                                    {{
                                        $material
                                            ->nome_material
                                    }}
                                </strong>

                                <br>

                                <small class="text-muted">
                                    {{ $categoriaMaterial }}

                                    @if($material->cor)
                                        ·
                                        {{ $material->cor }}
                                    @endif
                                </small>
                            </td>

                            <td class="text-nowrap">
                                <strong>
                                    {{
                                        $formatarQuantidade(
                                            $composicao
                                                ->quantidade_por_unidade,
                                            $material
                                                ->unidade_medida
                                        )
                                    }}
                                </strong>
                            </td>

                            <td class="text-nowrap">
                                {{
                                    $formatarQuantidade(
                                        $material
                                            ->quantidade,
                                        $material
                                            ->unidade_medida
                                    )
                                }}
                            </td>

                            <td>
                                <span
                                    class="
                                        badge
                                        {{ $situacaoClasse }}
                                    "
                                >
                                    {{ $situacaoTexto }}
                                </span>
                            </td>

                            <td
                                class="
                                    text-end
                                    text-nowrap
                                "
                            >
                                @if($fichaEditavel)

                                    <form
                                        action="{{
                                            route(
                                                'produtos.variacoes.composicao.destroy',
                                                [
                                                    $produto,
                                                    $variacao,
                                                    $composicao,
                                                ]
                                            )
                                        }}"
                                        method="POST"
                                        class="d-inline"
                                        data-confirm="Deseja remover este material da ficha de consumo? A variação deixará de consumir este material nas próximas ordens iniciadas."
                                        data-confirm-title="Remover material da ficha"
                                        data-confirm-button="Remover"
                                        data-confirm-variant="danger"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="
                                                btn
                                                btn-sm
                                                btn-outline-danger
                                            "
                                        >
                                            Remover
                                        </button>
                                    </form>

                                @else

                                    <span
                                        class="d-inline-block"
                                        tabindex="0"
                                        title="Remover indisponível — A ficha está em modo de consulta porque o produto ou a variação está inativo."
                                    >
                                        <button
                                            type="button"
                                            class="
                                                btn
                                                btn-sm
                                                btn-outline-danger
                                            "
                                            disabled
                                        >
                                            Remover
                                        </button>
                                    </span>

                                @endif
                            </td>
                        </tr>

                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endif

<script>
(function () {
    const material =
        document.getElementById(
            'id_material'
        );

    const quantidade =
        document.getElementById(
            'quantidade_por_unidade'
        );

    const unidade =
        document.getElementById(
            'unidadeQuantidade'
        );

    const ajuda =
        document.getElementById(
            'ajudaQuantidade'
        );

    if (
        !material
        ||
        !quantidade
        ||
        !unidade
    ) {
        return;
    }

    function formatarUnidade(valor) {
        if (valor === 'un') {
            return 'un.';
        }

        if (valor === 'rolo') {
            return 'rolo';
        }

        return valor || '—';
    }

    function atualizarUnidade(
        preencherConsumo = false
    ) {
        const opcao =
            material.options[
                material.selectedIndex
            ];

        if (!opcao) {
            return;
        }

        const unidadeSelecionada =
            opcao.dataset.unidade || '';

        const consumoAtual =
            opcao.dataset.consumo || '';

        const unidadeDiscreta = [
            'un',
            'rolo'
        ].includes(
            unidadeSelecionada
        );

        unidade.textContent =
            formatarUnidade(
                unidadeSelecionada
            );

        if (unidadeDiscreta) {
            quantidade.step = '1';
            quantidade.min = '1';

            quantidade.inputMode =
                'numeric';

            if (ajuda) {
                ajuda.textContent =
                    'Utilize somente números inteiros.';
            }
        } else {
            quantidade.step = '0.001';
            quantidade.min = '0.001';

            quantidade.inputMode =
                'decimal';

            if (ajuda) {
                ajuda.textContent =
                    unidadeSelecionada
                        ? 'Pode possuir até 3 casas decimais.'
                        : 'Selecione um material.';
            }
        }

        if (preencherConsumo) {
            quantidade.value =
                consumoAtual;
        }
    }

    material.addEventListener(
        'change',
        function () {
            atualizarUnidade(true);
        }
    );

    atualizarUnidade(false);
})();
</script>

@endsection
