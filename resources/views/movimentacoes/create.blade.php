@extends('layouts.app')

@section('title', 'Nova Movimentação')

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

            $quantidade =
                number_format(
                    $numero,
                    $decimais,
                    ',',
                    '.'
                );

            $sufixo = match ($unidade) {
                'un' =>
                    'un.',

                'rolo' =>
                    abs(
                        $numero - 1
                    ) < 0.0005
                        ? 'rolo'
                        : 'rolos',

                default =>
                    $unidade,
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
@endphp

<div class="mb-4">
    <h1 class="mb-1">
        Nova movimentação
    </h1>

    <p class="text-muted mb-0">
        Registre manualmente uma entrada
        ou saída de estoque.
    </p>
</div>

<div class="row">
    <div class="col-xl-9 col-xxl-8">
        <div class="card">
            <div class="card-body p-4">

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
                    Esta tela registra apenas
                    <strong>movimentações manuais</strong>.

                    Consumo de materiais, conclusão e reversão
                    de produção são registrados automaticamente
                    pelo sistema.
                </div>

                <form
                    action="{{
                        route(
                            'movimentacoes.store'
                        )
                    }}"
                    method="POST"
                >
                    @csrf

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label
                                for="tipo_item"
                                class="form-label"
                            >
                                Tipo de item
                                <span class="text-danger">
                                    *
                                </span>
                            </label>

                            <select
                                name="tipo_item"
                                id="tipo_item"
                                class="
                                    form-select

                                    @error('tipo_item')
                                        is-invalid
                                    @enderror
                                "
                                required
                            >
                                <option value="">
                                    Selecione
                                </option>

                                <option
                                    value="produto"
                                    @selected(
                                        old('tipo_item')
                                        === 'produto'
                                    )
                                >
                                    Produto acabado
                                </option>

                                <option
                                    value="material"
                                    @selected(
                                        old('tipo_item')
                                        === 'material'
                                    )
                                >
                                    Material
                                </option>
                            </select>

                            @error('tipo_item')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label
                                for="tipo_movimentacao"
                                class="form-label"
                            >
                                Movimentação
                                <span class="text-danger">
                                    *
                                </span>
                            </label>

                            <select
                                name="tipo_movimentacao"
                                id="tipo_movimentacao"
                                class="
                                    form-select

                                    @error(
                                        'tipo_movimentacao'
                                    )
                                        is-invalid
                                    @enderror
                                "
                                required
                            >
                                <option value="">
                                    Selecione
                                </option>

                                <option
                                    value="entrada"
                                    @selected(
                                        old(
                                            'tipo_movimentacao'
                                        )
                                        === 'entrada'
                                    )
                                >
                                    Entrada
                                </option>

                                <option
                                    value="saida"
                                    @selected(
                                        old(
                                            'tipo_movimentacao'
                                        )
                                        === 'saida'
                                    )
                                >
                                    Saída
                                </option>
                            </select>

                            @error('tipo_movimentacao')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                    </div>

                    {{-- Produto --}}
                    <div
                        id="campo-produto"
                        class="mt-4 d-none"
                    >
                        <label
                            for="id_variacao"
                            class="form-label"
                        >
                            Produto
                            <span class="text-danger">
                                *
                            </span>
                        </label>

                        <select
                            name="id_variacao"
                            id="id_variacao"
                            class="
                                form-select

                                @error('id_variacao')
                                    is-invalid
                                @enderror
                            "
                        >
                            <option
                                value=""
                                data-estoque=""
                                data-unidade="un"
                            >
                                Selecione uma variação
                            </option>

                            @foreach(
                                $variacoes
                                as $variacao
                            )
                                <option
                                    value="{{
                                        $variacao
                                            ->id_variacao
                                    }}"
                                    data-estoque="{{
                                        (float)
                                        $variacao
                                            ->quantidade
                                    }}"
                                    data-unidade="un"
                                    @selected(
                                        old('id_variacao')
                                        ==
                                        $variacao
                                            ->id_variacao
                                    )
                                >
                                    {{
                                        $variacao
                                            ->produto
                                            ->nome_produto
                                    }}

                                    ·

                                    {{ $variacao->tamanho }}

                                    /

                                    {{ $variacao->cor }}

                                    · Estoque:
                                    {{
                                        number_format(
                                            $variacao
                                                ->quantidade,
                                            0,
                                            ',',
                                            '.'
                                        )
                                    }}
                                    un.
                                </option>
                            @endforeach
                        </select>

                        @error('id_variacao')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Material --}}
                    <div
                        id="campo-material"
                        class="mt-4 d-none"
                    >
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
                        >
                            <option
                                value=""
                                data-estoque=""
                                data-unidade=""
                            >
                                Selecione um material
                            </option>

                            @foreach(
                                $materiais
                                as $material
                            )
                                @php
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
                                    data-estoque="{{
                                        (float)
                                        $material
                                            ->quantidade
                                    }}"
                                    data-unidade="{{
                                        $material
                                            ->unidade_medida
                                    }}"
                                    @selected(
                                        old('id_material')
                                        ==
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

                                    · Estoque:
                                    {{
                                        $formatarQuantidade(
                                            $material
                                                ->quantidade,

                                            $material
                                                ->unidade_medida
                                        )
                                    }}
                                </option>
                            @endforeach
                        </select>

                        @error('id_material')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Estoque selecionado --}}
                    <div
                        id="informacao-estoque"
                        class="
                            bg-body-secondary
                            rounded
                            p-3
                            mt-3
                            d-none
                        "
                    >
                        <div
                            class="
                                small
                                text-muted
                                mb-1
                            "
                        >
                            Estoque disponível
                        </div>

                        <strong
                            id="estoque-disponivel"
                        >
                            —
                        </strong>
                    </div>

                    {{-- Quantidade --}}
                    <div class="mt-4">
                        <label
                            for="quantidade"
                            class="form-label"
                        >
                            Quantidade
                            <span class="text-danger">
                                *
                            </span>
                        </label>

                        <div class="input-group">
                            <input
                                type="number"
                                name="quantidade"
                                id="quantidade"
                                class="
                                    form-control

                                    @error('quantidade')
                                        is-invalid
                                    @enderror
                                "
                                min="0.001"
                                step="0.001"
                                value="{{
                                    old('quantidade')
                                }}"
                                required
                            >

                            <span
                                class="input-group-text"
                                id="unidade-quantidade"
                            >
                                —
                            </span>

                            @error('quantidade')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div
                            class="form-text"
                            id="ajuda-quantidade"
                        >
                            Selecione primeiro o item.
                        </div>
                    </div>

                    {{-- Observação --}}
                    <div class="mt-4 mb-4">
                        <label
                            for="observacao"
                            class="form-label"
                        >
                            Observação
                        </label>

                        <textarea
                            name="observacao"
                            id="observacao"
                            class="
                                form-control

                                @error('observacao')
                                    is-invalid
                                @enderror
                            "
                            rows="4"
                            maxlength="2000"
                            placeholder="Motivo ou informação adicional sobre esta movimentação"
                        >{{ old('observacao') }}</textarea>

                        @error('observacao')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="form-text">
                            Campo opcional.
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Registrar movimentação
                        </button>

                        <a
                            href="{{
                                route(
                                    'movimentacoes.index'
                                )
                            }}"
                            class="
                                btn
                                btn-outline-secondary
                            "
                        >
                            Cancelar
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        const tipoItem =
            document.getElementById(
                'tipo_item'
            );

        const tipoMovimentacao =
            document.getElementById(
                'tipo_movimentacao'
            );

        const campoProduto =
            document.getElementById(
                'campo-produto'
            );

        const campoMaterial =
            document.getElementById(
                'campo-material'
            );

        const produto =
            document.getElementById(
                'id_variacao'
            );

        const material =
            document.getElementById(
                'id_material'
            );

        const quantidade =
            document.getElementById(
                'quantidade'
            );

        const unidadeQuantidade =
            document.getElementById(
                'unidade-quantidade'
            );

        const ajudaQuantidade =
            document.getElementById(
                'ajuda-quantidade'
            );

        const informacaoEstoque =
            document.getElementById(
                'informacao-estoque'
            );

        const estoqueDisponivel =
            document.getElementById(
                'estoque-disponivel'
            );

        function formatarNumero(
            valor,
            unidade
        ) {
            const numero =
                Number(valor);

            if (
                Number.isNaN(numero)
            ) {
                return '—';
            }

            const discreta =
                unidade === 'un'
                ||
                unidade === 'rolo';

            const ehInteiro =
                Math.abs(
                    numero
                    -
                    Math.round(numero)
                ) < 0.0005;

            const decimais =
                discreta
                && ehInteiro
                    ? 0
                    : 3;

            return numero.toLocaleString(
                'pt-BR',
                {
                    minimumFractionDigits:
                        decimais,

                    maximumFractionDigits:
                        decimais
                }
            );
        }

        function sufixoUnidade(
            unidade,
            valor = null
        ) {
            if (unidade === 'un') {
                return 'un.';
            }

            if (unidade === 'rolo') {
                if (
                    valor !== null
                    &&
                    Math.abs(
                        Number(valor) - 1
                    ) < 0.0005
                ) {
                    return 'rolo';
                }

                return 'rolos';
            }

            return unidade || '—';
        }

        function obterOpcaoAtual() {
            if (
                tipoItem.value
                === 'produto'
            ) {
                return produto.options[
                    produto.selectedIndex
                ];
            }

            if (
                tipoItem.value
                === 'material'
            ) {
                return material.options[
                    material.selectedIndex
                ];
            }

            return null;
        }

        function atualizarQuantidade() {
            const opcao =
                obterOpcaoAtual();

            if (!opcao) {
                unidadeQuantidade.textContent =
                    '—';

                ajudaQuantidade.textContent =
                    'Selecione primeiro o item.';

                informacaoEstoque.classList.add(
                    'd-none'
                );

                quantidade.removeAttribute(
                    'max'
                );

                return;
            }

            const unidade =
                opcao.dataset.unidade
                || '';

            const estoque =
                opcao.dataset.estoque
                || '';

            const discreta =
                tipoItem.value === 'produto'
                ||
                unidade === 'un'
                ||
                unidade === 'rolo';

            if (discreta) {
                quantidade.step = '1';
                quantidade.min = '1';
                quantidade.inputMode =
                    'numeric';
            } else {
                quantidade.step =
                    '0.001';

                quantidade.min =
                    '0.001';

                quantidade.inputMode =
                    'decimal';
            }

            unidadeQuantidade.textContent =
                sufixoUnidade(
                    unidade
                );

            if (
                tipoItem.value === 'produto'
            ) {
                ajudaQuantidade.textContent =
                    'Produtos acabados utilizam somente quantidades inteiras.';
            } else if (discreta) {
                ajudaQuantidade.textContent =
                    'Esta unidade utiliza somente quantidades inteiras.';
            } else {
                ajudaQuantidade.textContent =
                    'Esta unidade permite até 3 casas decimais.';
            }

            if (estoque !== '') {
                estoqueDisponivel.textContent =
                    formatarNumero(
                        estoque,
                        unidade
                    )
                    + ' '
                    + sufixoUnidade(
                        unidade,
                        estoque
                    );

                informacaoEstoque.classList.remove(
                    'd-none'
                );
            } else {
                informacaoEstoque.classList.add(
                    'd-none'
                );
            }

            if (
                tipoMovimentacao.value
                    === 'saida'
                &&
                estoque !== ''
            ) {
                quantidade.max =
                    estoque;
            } else {
                quantidade.removeAttribute(
                    'max'
                );
            }
        }

        function atualizarCampos() {
            if (
                tipoItem.value
                === 'produto'
            ) {
                campoProduto.classList.remove(
                    'd-none'
                );

                campoMaterial.classList.add(
                    'd-none'
                );

                produto.required = true;
                material.required = false;

                unidadeQuantidade.textContent =
                    'un.';

            } else if (
                tipoItem.value
                === 'material'
            ) {
                campoProduto.classList.add(
                    'd-none'
                );

                campoMaterial.classList.remove(
                    'd-none'
                );

                produto.required = false;
                material.required = true;

            } else {
                campoProduto.classList.add(
                    'd-none'
                );

                campoMaterial.classList.add(
                    'd-none'
                );

                produto.required = false;
                material.required = false;
            }

            atualizarQuantidade();
        }

        tipoItem.addEventListener(
            'change',
            atualizarCampos
        );

        tipoMovimentacao.addEventListener(
            'change',
            atualizarQuantidade
        );

        produto.addEventListener(
            'change',
            atualizarQuantidade
        );

        material.addEventListener(
            'change',
            atualizarQuantidade
        );

        atualizarCampos();
    }
);
</script>

@endsection
