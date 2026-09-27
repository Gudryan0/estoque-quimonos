<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\OrdemProducao;
use App\Models\OrdemProducaoItem;
use App\Models\Produto;
use App\Models\ProdutoVariacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrdemProducaoItemController extends Controller
{
    private const QUANTIDADE_MAXIMA = 1000000;

    public function create(OrdemProducao $ordem)
    {
        if ($ordem->status !== 'planejada') {
            return redirect()
                ->route('ordens.show', $ordem)
                ->with(
                    'erro',
                    'Não é possível adicionar lotes nesta ordem.'
                );
        }

        $clientes = Cliente::orderBy('nome_cliente')->get();
        $catalogo = $this->catalogoVariacoes();

        return view(
            'ordens.itens.create',
            compact('ordem', 'clientes', 'catalogo')
        );
    }

    public function store(Request $request, OrdemProducao $ordem)
    {
        if ($ordem->status !== 'planejada') {
            return redirect()
                ->route('ordens.show', $ordem)
                ->with(
                    'erro',
                    'Não é possível adicionar lotes nesta ordem.'
                );
        }

        $this->normalizarDados($request);
        $dados = $this->validarDados($request);

        DB::transaction(function () use ($ordem, $dados) {
            $ordemBloqueada = OrdemProducao::query()
                ->lockForUpdate()
                ->findOrFail($ordem->id_ordem);

            if ($ordemBloqueada->status !== 'planejada') {
                throw ValidationException::withMessages([
                    'id_produto' =>
                        'A ordem não está mais planejada.',
                ]);
            }

            $composicao = $this->validarComposicao($dados);

            $cliente = !empty($dados['id_cliente'])
                ? Cliente::findOrFail($dados['id_cliente'])
                : null;

            $lote = OrdemProducaoItem::create([
                'id_ordem' => $ordemBloqueada->id_ordem,
                'id_produto' => $dados['id_produto'],
                'id_cliente' => $cliente?->id_cliente,
                'cliente_nome' => $cliente?->nome_cliente,
                'cor' => $dados['cor'],
                'quantidade' => $composicao['total'],
                'etapa_atual' => 'corte',
                'descricao_bordado' =>
                    $dados['descricao_bordado'] ?? null,
                'cor_linha' =>
                    $dados['cor_linha'] ?? null,
                'etiqueta' =>
                    $dados['etiqueta'] ?? null,
                'posicao_etiqueta' =>
                    $dados['posicao_etiqueta'] ?? null,
                'responsavel_costura' =>
                    $dados['responsavel_costura'] ?? null,
                'observacao' =>
                    $dados['observacao'] ?? null,
            ]);

            $lote->variacoes()->createMany(
                $composicao['linhas']
            );
        });

        return redirect()
            ->route('ordens.show', $ordem)
            ->with(
                'sucesso',
                'Lote adicionado à ordem de produção.'
            );
    }

    public function edit(
        OrdemProducao $ordem,
        OrdemProducaoItem $item
    ) {
        $this->garantirPertencimento($ordem, $item);

        if ($ordem->status !== 'planejada') {
            return redirect()
                ->route('ordens.show', $ordem)
                ->with(
                    'erro',
                    'Lotes só podem ser editados enquanto a ordem estiver planejada.'
                );
        }

        $item->load('variacoes');

        $clientes = Cliente::orderBy('nome_cliente')->get();
        $catalogo = $this->catalogoVariacoes();

        return view(
            'ordens.itens.edit',
            compact('ordem', 'item', 'clientes', 'catalogo')
        );
    }

    public function update(
        Request $request,
        OrdemProducao $ordem,
        OrdemProducaoItem $item
    ) {
        $this->garantirPertencimento($ordem, $item);

        if ($ordem->status !== 'planejada') {
            return redirect()
                ->route('ordens.show', $ordem)
                ->with(
                    'erro',
                    'Lotes só podem ser editados enquanto a ordem estiver planejada.'
                );
        }

        $this->normalizarDados($request);
        $dados = $this->validarDados($request);

        DB::transaction(function () use ($ordem, $item, $dados) {
            $ordemBloqueada = OrdemProducao::query()
                ->lockForUpdate()
                ->findOrFail($ordem->id_ordem);

            $itemBloqueado = OrdemProducaoItem::query()
                ->lockForUpdate()
                ->findOrFail($item->id_item);

            $this->garantirPertencimento(
                $ordemBloqueada,
                $itemBloqueado
            );

            if ($ordemBloqueada->status !== 'planejada') {
                throw ValidationException::withMessages([
                    'id_produto' =>
                        'A ordem não está mais planejada.',
                ]);
            }

            $composicao = $this->validarComposicao($dados);

            $cliente = !empty($dados['id_cliente'])
                ? Cliente::findOrFail($dados['id_cliente'])
                : null;

            $itemBloqueado->update([
                'id_produto' => $dados['id_produto'],
                'id_cliente' => $cliente?->id_cliente,
                'cliente_nome' => $cliente?->nome_cliente,
                'cor' => $dados['cor'],
                'quantidade' => $composicao['total'],
                'descricao_bordado' =>
                    $dados['descricao_bordado'] ?? null,
                'cor_linha' =>
                    $dados['cor_linha'] ?? null,
                'etiqueta' =>
                    $dados['etiqueta'] ?? null,
                'posicao_etiqueta' =>
                    $dados['posicao_etiqueta'] ?? null,
                'responsavel_costura' =>
                    $dados['responsavel_costura'] ?? null,
                'observacao' =>
                    $dados['observacao'] ?? null,
            ]);

            /*
             * A OP ainda está planejada, portanto
             * nenhuma movimentação de produção
             * foi realizada para este lote.
             *
             * Substituímos sua composição inteira
             * dentro da mesma transação.
             */
            $itemBloqueado->variacoes()->delete();

            $itemBloqueado->variacoes()->createMany(
                $composicao['linhas']
            );
        });

        return redirect()
            ->route('ordens.show', $ordem)
            ->with(
                'sucesso',
                'Lote atualizado com sucesso.'
            );
    }

    public function destroy(
        OrdemProducao $ordem,
        OrdemProducaoItem $item
    ) {
        $this->garantirPertencimento($ordem, $item);

        $resultado = DB::transaction(
            function () use ($ordem, $item) {
                $ordemBloqueada = OrdemProducao::query()
                    ->lockForUpdate()
                    ->findOrFail($ordem->id_ordem);

                $itemBloqueado = OrdemProducaoItem::query()
                    ->lockForUpdate()
                    ->findOrFail($item->id_item);

                $this->garantirPertencimento(
                    $ordemBloqueada,
                    $itemBloqueado
                );

                if ($ordemBloqueada->status !== 'planejada') {
                    return false;
                }

                $itemBloqueado->delete();

                return true;
            }
        );

        if (!$resultado) {
            return redirect()
                ->route('ordens.show', $ordem)
                ->with(
                    'erro',
                    'Lotes só podem ser removidos enquanto a ordem estiver planejada.'
                );
        }

        return redirect()
            ->route('ordens.show', $ordem)
            ->with(
                'sucesso',
                'Lote removido da ordem.'
            );
    }

    private function validarDados(Request $request): array
    {
        return $request->validate(
            [
                'id_produto' => [
                    'required',
                    'integer',
                    Rule::exists(
                        'produtos',
                        'id_produto'
                    )->where('ativo', true),
                ],

                'cor' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'id_cliente' => [
                    'nullable',
                    'integer',
                    'exists:clientes,id_cliente',
                ],

                'quantidades' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'quantidades.*' => [
                    'nullable',
                    'integer',
                    'min:0',
                    'max:' . self::QUANTIDADE_MAXIMA,
                ],

                'descricao_bordado' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],

                'cor_linha' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'etiqueta' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'posicao_etiqueta' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'responsavel_costura' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'observacao' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],
            ],
            [
                'id_produto.required' =>
                    'Selecione um produto.',

                'id_produto.exists' =>
                    'O produto selecionado não existe ou está inativo.',

                'cor.required' =>
                    'Selecione uma cor.',

                'id_cliente.exists' =>
                    'O cliente selecionado não existe.',

                'quantidades.required' =>
                    'Informe as quantidades dos tamanhos.',

                'quantidades.array' =>
                    'As quantidades informadas são inválidas.',

                'quantidades.*.integer' =>
                    'As quantidades devem ser números inteiros.',

                'quantidades.*.min' =>
                    'As quantidades não podem ser negativas.',

                'quantidades.*.max' =>
                    'Uma das quantidades informadas é muito alta.',

                'descricao_bordado.max' =>
                    'O bordado pode possuir no máximo 2000 caracteres.',

                'cor_linha.max' =>
                    'A cor da linha pode possuir no máximo 255 caracteres.',

                'etiqueta.max' =>
                    'A etiqueta pode possuir no máximo 255 caracteres.',

                'posicao_etiqueta.max' =>
                    'A posição da etiqueta pode possuir no máximo 255 caracteres.',

                'responsavel_costura.max' =>
                    'O responsável pela costura pode possuir no máximo 255 caracteres.',

                'observacao.max' =>
                    'A observação pode possuir no máximo 2000 caracteres.',
            ]
        );
    }

    private function validarComposicao(array $dados): array
    {
        /*
         * Revalidação dentro da transação.
         * Não confiamos somente nas opções
         * exibidas pelo formulário.
         */
        $produto = Produto::query()
            ->lockForUpdate()
            ->findOrFail($dados['id_produto']);

        if (!$produto->ativo) {
            throw ValidationException::withMessages([
                'id_produto' =>
                    'O produto selecionado está inativo.',
            ]);
        }

        $quantidades = $dados['quantidades'];

        $ids = [];

        foreach ($quantidades as $id => $quantidade) {
            if (
                !ctype_digit((string) $id)
                || (int) $id <= 0
            ) {
                throw ValidationException::withMessages([
                    'quantidades' =>
                        'Foi informado um identificador de variação inválido.',
                ]);
            }

            $ids[] = (int) $id;
        }

        $variacoes = ProdutoVariacao::query()
            ->whereIn('id_variacao', $ids)
            ->lockForUpdate()
            ->get()
            ->keyBy('id_variacao');

        $linhas = [];
        $total = 0;

        foreach ($quantidades as $id => $quantidade) {
            $variacao = $variacoes->get((int) $id);

            if (
                !$variacao
                || !$variacao->ativo
                || (int) $variacao->id_produto
                    !== (int) $dados['id_produto']
                || $variacao->cor !== $dados['cor']
            ) {
                throw ValidationException::withMessages([
                    'quantidades' =>
                        'Uma das variações não pertence ao produto e à cor selecionados ou está inativa.',
                ]);
            }

            $quantidade = (int) ($quantidade ?? 0);

            if ($quantidade === 0) {
                continue;
            }

            $total += $quantidade;

            if ($total > self::QUANTIDADE_MAXIMA) {
                throw ValidationException::withMessages([
                    'quantidades' =>
                        'O total do lote não pode ultrapassar '
                        . self::QUANTIDADE_MAXIMA
                        . ' unidades.',
                ]);
            }

            $linhas[] = [
                'id_variacao' => $variacao->id_variacao,
                'quantidade' => $quantidade,
            ];
        }

        if ($total === 0) {
            throw ValidationException::withMessages([
                'quantidades' =>
                    'Informe pelo menos um tamanho com quantidade maior que zero.',
            ]);
        }

        return [
            'linhas' => $linhas,
            'total' => $total,
        ];
    }

    private function catalogoVariacoes(): array
    {
        $variacoes = ProdutoVariacao::query()
            ->with('produto')
            ->where('ativo', true)
            ->whereHas(
                'produto',
                fn ($query) => $query->where('ativo', true)
            )
            ->orderBy('id_produto')
            ->orderBy('cor')
            ->orderBy('tamanho')
            ->get();

        return $variacoes
            ->groupBy('id_produto')
            ->map(function ($grupoProduto) {
                $produto = $grupoProduto->first()->produto;

                return [
                    'id' => $produto->id_produto,
                    'nome' => $produto->nome_produto,

                    'cores' => $grupoProduto
                        ->groupBy('cor')
                        ->map(function ($grupoCor, $cor) {
                            return [
                                'cor' => (string) $cor,

                                'variacoes' => $grupoCor
                                    ->map(function ($variacao) {
                                        return [
                                            'id' =>
                                                $variacao->id_variacao,

                                            'tamanho' =>
                                                $variacao->tamanho,
                                        ];
                                    })
                                    ->values()
                                    ->all(),
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->sortBy('nome')
            ->values()
            ->all();
    }

    private function normalizarDados(Request $request): void
    {
        $campos = [
            'cor',
            'id_cliente',
            'descricao_bordado',
            'cor_linha',
            'etiqueta',
            'posicao_etiqueta',
            'responsavel_costura',
            'observacao',
        ];

        $normalizados = [];

        foreach ($campos as $campo) {
            $valor = $request->input($campo);

            if (is_string($valor)) {
                $valor = trim($valor);

                if ($valor === '') {
                    $valor = null;
                }
            }

            $normalizados[$campo] = $valor;
        }

        $request->merge($normalizados);
    }

    private function garantirPertencimento(
        OrdemProducao $ordem,
        OrdemProducaoItem $item
    ): void {
        abort_unless(
            (int) $item->id_ordem
                === (int) $ordem->id_ordem,
            404
        );
    }
}
