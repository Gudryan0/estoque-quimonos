
@php
    $edicao = $item !== null;

    $acao = $edicao
        ? route('ordens.itens.update', [$ordem, $item])
        : route('ordens.itens.store', $ordem);

    $produtoSelecionado = old(
        'id_produto',
        $item?->id_produto
    );

    $corSelecionada = old(
        'cor',
        $item?->cor
    );

    $quantidadesAtuais = $edicao
        ? $item->variacoes
            ->pluck('quantidade', 'id_variacao')
            ->all()
        : [];

    $quantidadesIniciais = old(
        'quantidades',
        $quantidadesAtuais
    );
@endphp

<div class="card">
    <div class="card-body p-4">

        @if($errors->any())
            <div class="alert alert-danger mb-4">
                <strong>
                    Verifique os dados informados.
                </strong>

                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ $acao }}" method="POST">
            @csrf

            @if($edicao)
                @method('PUT')
            @endif

            <h2 class="h5 mb-3">
                Produto e quantidades
            </h2>

            <div class="row g-3 mb-4">
                <div class="col-md-7">
                    <label for="id_produto" class="form-label">
                        Produto
                        <span class="text-danger">*</span>
                    </label>

                    <select
                        name="id_produto"
                        id="id_produto"
                        class="form-select @error('id_produto') is-invalid @enderror"
                        required
                    >
                        <option value="">
                            Selecione um produto
                        </option>

                        @foreach($catalogo as $produto)
                            <option
                                value="{{ $produto['id'] }}"
                                @selected(
                                    (string) $produtoSelecionado
                                    === (string) $produto['id']
                                )
                            >
                                {{ $produto['nome'] }}
                            </option>
                        @endforeach
                    </select>

                    @error('id_produto')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="col-md-5">
                    <label for="cor" class="form-label">
                        Cor do produto
                        <span class="text-danger">*</span>
                    </label>

                    <select
                        name="cor"
                        id="cor"
                        class="form-select @error('cor') is-invalid @enderror"
                        required
                    >
                        <option value="">
                            Selecione primeiro o produto
                        </option>
                    </select>

                    @error('cor')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="border rounded-3 p-3 mb-2">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                    <div>
                        <h3 class="h6 mb-1">
                            Quantidades por tamanho
                        </h3>

                        <p class="small text-muted mb-0">
                            Informe as quantidades dos tamanhos
                            que serão produzidos neste lote.
                        </p>
                    </div>

                    <div class="text-sm-end">
                        <span class="small text-muted d-block">
                            Total do lote
                        </span>

                        <strong id="total-lote" class="fs-5">
                            0 unidades
                        </strong>
                    </div>
                </div>

                <div
                    id="grade-quantidades"
                    aria-live="polite"
                >
                    <p class="text-muted small mb-0">
                        Selecione um produto e uma cor para
                        visualizar os tamanhos disponíveis.
                    </p>
                </div>
            </div>

            <div class="form-text mb-4">
                Os tamanhos com quantidade zero não serão
                incluídos no lote. Pelo menos um tamanho
                deverá possuir quantidade maior que zero.
            </div>

            <hr class="my-4">

            <h2 class="h5 mb-3">
                Especificações do lote
            </h2>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="id_cliente" class="form-label">
                        Cliente
                    </label>

                    <select
                        name="id_cliente"
                        id="id_cliente"
                        class="form-select @error('id_cliente') is-invalid @enderror"
                    >
                        <option value="">
                            Sem cliente definido
                        </option>

                        @foreach($clientes as $cliente)
                            <option
                                value="{{ $cliente->id_cliente }}"
                                @selected(
                                    old('id_cliente', $item?->id_cliente)
                                    == $cliente->id_cliente
                                )
                            >
                                {{ $cliente->nome_cliente }}
                            </option>
                        @endforeach
                    </select>

                    @error('id_cliente')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label
                        for="responsavel_costura"
                        class="form-label"
                    >
                        Responsável pela costura
                    </label>

                    <input
                        type="text"
                        name="responsavel_costura"
                        id="responsavel_costura"
                        class="form-control @error('responsavel_costura') is-invalid @enderror"
                        value="{{ old('responsavel_costura', $item?->responsavel_costura) }}"
                        maxlength="255"
                        placeholder="Ex.: Rambo"
                    >

                    @error('responsavel_costura')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="form-text">
                        Informe o nome do costureiro ou
                        os nomes dos responsáveis.
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label
                    for="descricao_bordado"
                    class="form-label"
                >
                    Bordado
                </label>

                <textarea
                    name="descricao_bordado"
                    id="descricao_bordado"
                    class="form-control @error('descricao_bordado') is-invalid @enderror"
                    rows="3"
                    maxlength="2000"
                    placeholder="Ex.: Logo da academia no peito esquerdo"
                >{{ old('descricao_bordado', $item?->descricao_bordado) }}</textarea>

                @error('descricao_bordado')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="cor_linha" class="form-label">
                        Cor da linha
                    </label>

                    <input
                        type="text"
                        name="cor_linha"
                        id="cor_linha"
                        class="form-control @error('cor_linha') is-invalid @enderror"
                        value="{{ old('cor_linha', $item?->cor_linha) }}"
                        maxlength="255"
                        placeholder="Ex.: Branco"
                    >

                    @error('cor_linha')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="etiqueta" class="form-label">
                        Etiqueta / Estampa
                    </label>

                    <input
                        type="text"
                        name="etiqueta"
                        id="etiqueta"
                        class="form-control @error('etiqueta') is-invalid @enderror"
                        value="{{ old('etiqueta', $item?->etiqueta) }}"
                        maxlength="255"
                        placeholder="Ex.: Logo personalizado"
                    >

                    @error('etiqueta')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label
                        for="posicao_etiqueta"
                        class="form-label"
                    >
                        Posição da etiqueta / estampa
                    </label>

                    <input
                        type="text"
                        name="posicao_etiqueta"
                        id="posicao_etiqueta"
                        class="form-control @error('posicao_etiqueta') is-invalid @enderror"
                        value="{{ old('posicao_etiqueta', $item?->posicao_etiqueta) }}"
                        maxlength="255"
                        placeholder="Ex.: Manga direita"
                    >

                    @error('posicao_etiqueta')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="observacao" class="form-label">
                    Observações do lote
                </label>

                <textarea
                    name="observacao"
                    id="observacao"
                    class="form-control @error('observacao') is-invalid @enderror"
                    rows="3"
                    maxlength="2000"
                    placeholder="Outras instruções de produção"
                >{{ old('observacao', $item?->observacao) }}</textarea>

                @error('observacao')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
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
                Todos os tamanhos deste lote avançarão juntos
                pelas etapas de produção.

                A edição ficará indisponível depois que
                a OP for iniciada.
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    {{ $edicao ? 'Salvar alterações' : 'Adicionar lote' }}
                </button>

                <a
                    href="{{ route('ordens.show', $ordem) }}"
                    class="btn btn-outline-secondary"
                >
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const catalogo = {{ \Illuminate\Support\Js::from($catalogo) }};

    const produtoInicial = {{ \Illuminate\Support\Js::from((string) ($produtoSelecionado ?? '')) }};
    const corInicial = {{ \Illuminate\Support\Js::from((string) ($corSelecionada ?? '')) }};
    const quantidadesIniciais = {{ \Illuminate\Support\Js::from($quantidadesIniciais) }};

    const produtoSelect = document.getElementById('id_produto');
    const corSelect = document.getElementById('cor');
    const grade = document.getElementById('grade-quantidades');
    const totalElement = document.getElementById('total-lote');

    function produtoAtual() {
        return catalogo.find(function (produto) {
            return String(produto.id) === produtoSelect.value;
        });
    }

    function corAtual() {
        const produto = produtoAtual();

        if (!produto) {
            return null;
        }

        return produto.cores.find(function (grupo) {
            return grupo.cor === corSelect.value;
        });
    }

    function atualizarTotal() {
        let total = 0;

        grade.querySelectorAll('input[type="number"]')
            .forEach(function (input) {
                const quantidade = Number(input.value);

                if (
                    Number.isInteger(quantidade)
                    && quantidade > 0
                ) {
                    total += quantidade;
                }
            });

        totalElement.textContent =
            total.toLocaleString('pt-BR')
            + (total === 1 ? ' unidade' : ' unidades');
    }

    function preencherGrade(usarQuantidadesIniciais) {
        grade.replaceChildren();

        const grupo = corAtual();

        if (!grupo) {
            const mensagem = document.createElement('p');
            mensagem.className = 'text-muted small mb-0';
            mensagem.textContent =
                'Selecione um produto e uma cor para visualizar os tamanhos disponíveis.';

            grade.appendChild(mensagem);
            atualizarTotal();
            return;
        }

        const linha = document.createElement('div');
        linha.className = 'row g-3';

        grupo.variacoes.forEach(function (variacao) {
            const coluna = document.createElement('div');
            coluna.className = 'col-6 col-md-4 col-xl-3';

            const label = document.createElement('label');
            label.className = 'form-label';
            label.htmlFor = 'quantidade_' + variacao.id;
            label.textContent = variacao.tamanho;

            const input = document.createElement('input');
            input.type = 'number';
            input.name = 'quantidades[' + variacao.id + ']';
            input.id = 'quantidade_' + variacao.id;
            input.className = 'form-control';
            input.min = '0';
            input.max = '1000000';
            input.step = '1';
            input.inputMode = 'numeric';

            const valorInicial = usarQuantidadesIniciais
                ? quantidadesIniciais[String(variacao.id)]
                : 0;

            input.value = valorInicial ?? 0;
            input.addEventListener('input', atualizarTotal);

            coluna.appendChild(label);
            coluna.appendChild(input);
            linha.appendChild(coluna);
        });

        grade.appendChild(linha);
        atualizarTotal();
    }

    function preencherCores(corDesejada) {
        corSelect.replaceChildren();

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Selecione uma cor';

        corSelect.appendChild(placeholder);

        const produto = produtoAtual();
        corSelect.disabled = !produto;

        if (produto) {
            produto.cores.forEach(function (grupo) {
                const option = document.createElement('option');
                option.value = grupo.cor;
                option.textContent = grupo.cor;
                corSelect.appendChild(option);
            });
        }

        corSelect.value = corDesejada || '';

        preencherGrade(
            produtoSelect.value === produtoInicial
            && corSelect.value === corInicial
        );
    }

    produtoSelect.addEventListener('change', function () {
        preencherCores('');
    });

    corSelect.addEventListener('change', function () {
        preencherGrade(false);
    });

    preencherCores(corInicial);
});
</script>
