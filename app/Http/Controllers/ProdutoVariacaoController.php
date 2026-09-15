<?php

namespace App\Http\Controllers;

use App\Models\OrdemProducaoItem;
use App\Models\Produto;
use App\Models\ProdutoVariacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProdutoVariacaoController extends Controller
{
    public function index(
        Request $request,
        Produto $produto
    ) {
        $status =
            $request->query('status') === 'inativos'
                ? 'inativos'
                : 'ativos';

        $busca = trim(
            (string) $request->query(
                'q',
                ''
            )
        );

        $situacao = trim(
            (string) $request->query(
                'situacao',
                ''
            )
        );

        if (
            $status !== 'ativos'
            ||
            !in_array(
                $situacao,
                [
                    'normal',
                    'baixo',
                    'sem_estoque',
                ],
                true
            )
        ) {
            $situacao = '';
        }

        /*
        |--------------------------------------------------------------------------
        | Contadores gerais
        |--------------------------------------------------------------------------
        */

        $quantidadeAtivas = $produto
            ->variacoes()
            ->where(
                'ativo',
                true
            )
            ->count();

        $quantidadeInativas = $produto
            ->variacoes()
            ->where(
                'ativo',
                false
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Variações
        |--------------------------------------------------------------------------
        */

        $variacoes = ProdutoVariacao::query()
            ->where(
                'id_produto',
                $produto->id_produto
            )
            ->withCount([
                'movimentacoes',

                'composicoesMateriais as composicoes_count',
            ])
            ->where(
                'ativo',
                $status === 'ativos'
            )
            ->when(
                $busca !== '',
                function ($query) use ($busca) {
                    $query->where(
                        function ($query) use ($busca) {
                            $query
                                ->where(
                                    'tamanho',
                                    'like',
                                    "%{$busca}%"
                                )
                                ->orWhere(
                                    'cor',
                                    'like',
                                    "%{$busca}%"
                                );
                        }
                    );
                }
            )
            ->when(
                $situacao === 'sem_estoque',
                function ($query) {
                    $query->where(
                        'quantidade',
                        '<=',
                        0
                    );
                }
            )
            ->when(
                $situacao === 'baixo',
                function ($query) {
                    $query
                        ->where(
                            'quantidade',
                            '>',
                            0
                        )
                        ->whereColumn(
                            'quantidade',
                            '<=',
                            'estoque_minimo'
                        );
                }
            )
            ->when(
                $situacao === 'normal',
                function ($query) {
                    $query
                        ->where(
                            'quantidade',
                            '>',
                            0
                        )
                        ->whereColumn(
                            'quantidade',
                            '>',
                            'estoque_minimo'
                        );
                }
            )
            ->orderBy('tamanho')
            ->orderBy('cor')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Uso em produção
        |--------------------------------------------------------------------------
        */

        $idsVariacoes =
            $variacoes
                ->pluck('id_variacao');

        $variacoesUtilizadasEmProducao =
            collect();

        $variacoesComOrdemAtiva =
            collect();

        if ($idsVariacoes->isNotEmpty()) {
            $variacoesUtilizadasEmProducao =
                OrdemProducaoItem::query()
                    ->whereIn(
                        'id_variacao',
                        $idsVariacoes
                    )
                    ->distinct()
                    ->pluck(
                        'id_variacao'
                    )
                    ->mapWithKeys(
                        function ($idVariacao) {
                            return [
                                (int) $idVariacao
                                    => true,
                            ];
                        }
                    );

            $variacoesComOrdemAtiva =
                DB::table(
                    'ordem_producao_itens as item'
                )
                    ->join(
                        'ordens_producao as ordem',
                        'ordem.id_ordem',
                        '=',
                        'item.id_ordem'
                    )
                    ->whereIn(
                        'item.id_variacao',
                        $idsVariacoes
                    )
                    ->whereIn(
                        'ordem.status',
                        [
                            'planejada',
                            'em_producao',
                        ]
                    )
                    ->distinct()
                    ->pluck(
                        'item.id_variacao'
                    )
                    ->mapWithKeys(
                        function ($idVariacao) {
                            return [
                                (int) $idVariacao
                                    => true,
                            ];
                        }
                    );
        }

        $variacoes->each(
            function ($variacao) use (
                $variacoesUtilizadasEmProducao,
                $variacoesComOrdemAtiva
            ) {
                $variacao->setAttribute(
                    'utilizada_em_producao',
                    $variacoesUtilizadasEmProducao
                        ->has(
                            (int)
                            $variacao->id_variacao
                        )
                );

                $variacao->setAttribute(
                    'possui_ordem_ativa',
                    $variacoesComOrdemAtiva
                        ->has(
                            (int)
                            $variacao->id_variacao
                        )
                );

                $variacao->setAttribute(
                    'motivo_bloqueio_exclusao',
                    $this
                        ->motivoBloqueioExclusao(
                            $variacao
                        )
                );
            }
        );

        $produtoPossuiOrdemAtiva =
            $this->produtoPossuiOrdemAtiva(
                $produto
            );

        return view(
            'produtos.variacoes.index',
            compact(
                'produto',
                'variacoes',
                'status',
                'busca',
                'situacao',
                'quantidadeAtivas',
                'quantidadeInativas',
                'produtoPossuiOrdemAtiva'
            )
        );
    }

    public function create(
        Produto $produto
    ) {
        if (!$produto->ativo) {
            return redirect()
                ->route(
                    'produtos.variacoes.index',
                    [
                        'produto' => $produto,
                        'status' => 'inativos',
                    ]
                )
                ->with(
                    'erro',
                    'Não é possível cadastrar uma variação em um produto inativo.'
                );
        }

        return view(
            'produtos.variacoes.create',
            compact('produto')
        );
    }

    public function store(
        Request $request,
        Produto $produto
    ) {
        if (!$produto->ativo) {
            return redirect()
                ->route(
                    'produtos.variacoes.index',
                    [
                        'produto' => $produto,
                        'status' => 'inativos',
                    ]
                )
                ->with(
                    'erro',
                    'Não é possível cadastrar uma variação em um produto inativo.'
                );
        }

        $this->normalizarDados(
            $request
        );

        $dados = $this->validarDados(
            $request,
            $produto
        );

        $dados['id_produto'] =
            $produto->id_produto;

        ProdutoVariacao::create(
            $dados
        );

        return redirect()
            ->route(
                'produtos.variacoes.index',
                $produto
            )
            ->with(
                'sucesso',
                'Variação cadastrada com sucesso.'
            );
    }

    public function edit(
        Produto $produto,
        ProdutoVariacao $variacao
    ) {
        $this->garantirPertencimento(
            $produto,
            $variacao
        );

        return view(
            'produtos.variacoes.edit',
            compact(
                'produto',
                'variacao'
            )
        );
    }

    public function update(
        Request $request,
        Produto $produto,
        ProdutoVariacao $variacao
    ) {
        $this->garantirPertencimento(
            $produto,
            $variacao
        );

        $this->normalizarDados(
            $request
        );

        $dados = $this->validarDados(
            $request,
            $produto,
            $variacao
        );

        $variacao->update(
            $dados
        );

        return redirect()
            ->route(
                'produtos.variacoes.index',
                [
                    'produto' => $produto,

                    'status' =>
                        $variacao->ativo
                            ? 'ativos'
                            : 'inativos',
                ]
            )
            ->with(
                'sucesso',
                'Variação atualizada com sucesso.'
            );
    }

    public function inativar(
        Produto $produto,
        ProdutoVariacao $variacao
    ) {
        $this->garantirPertencimento(
            $produto,
            $variacao
        );

        if (!$variacao->ativo) {
            return redirect()
                ->route(
                    'produtos.variacoes.index',
                    [
                        'produto' => $produto,
                        'status' => 'inativos',
                    ]
                )
                ->with(
                    'erro',
                    'Esta variação já está inativa.'
                );
        }

        if (
            $this->variacaoPossuiOrdemAtiva(
                $variacao
            )
        ) {
            return redirect()
                ->route(
                    'produtos.variacoes.index',
                    $produto
                )
                ->with(
                    'erro',
                    'Não é possível inativar esta variação porque ela está vinculada a uma ordem de produção planejada ou em produção.'
                );
        }

        $variacao->ativo = false;
        $variacao->save();

        return redirect()
            ->route(
                'produtos.variacoes.index',
                $produto
            )
            ->with(
                'sucesso',
                'Variação inativada com sucesso.'
            );
    }

    public function reativar(
        Produto $produto,
        ProdutoVariacao $variacao
    ) {
        $this->garantirPertencimento(
            $produto,
            $variacao
        );

        if (!$produto->ativo) {
            return redirect()
                ->route(
                    'produtos.variacoes.index',
                    [
                        'produto' => $produto,
                        'status' => 'inativos',
                    ]
                )
                ->with(
                    'erro',
                    'O produto está inativo. Reative primeiro o produto para depois reativar esta variação.'
                );
        }

        if ($variacao->ativo) {
            return redirect()
                ->route(
                    'produtos.variacoes.index',
                    $produto
                )
                ->with(
                    'erro',
                    'Esta variação já está ativa.'
                );
        }

        $variacao->ativo = true;
        $variacao->save();

        return redirect()
            ->route(
                'produtos.variacoes.index',
                [
                    'produto' => $produto,
                    'status' => 'inativos',
                ]
            )
            ->with(
                'sucesso',
                'Variação reativada com sucesso.'
            );
    }

    public function inativarTodas(
        Produto $produto
    ) {
        if (!$produto->ativo) {
            return redirect()
                ->route(
                    'produtos.variacoes.index',
                    [
                        'produto' => $produto,
                        'status' => 'inativos',
                    ]
                )
                ->with(
                    'erro',
                    'O produto está inativo.'
                );
        }

        $quantidadeAtivas = $produto
            ->variacoes()
            ->where(
                'ativo',
                true
            )
            ->count();

        if ($quantidadeAtivas === 0) {
            return redirect()
                ->route(
                    'produtos.variacoes.index',
                    $produto
                )
                ->with(
                    'erro',
                    'Este produto não possui variações ativas para inativar.'
                );
        }

        if (
            $this->produtoPossuiOrdemAtiva(
                $produto
            )
        ) {
            return redirect()
                ->route(
                    'produtos.variacoes.index',
                    $produto
                )
                ->with(
                    'erro',
                    'Não foi possível inativar todas as variações porque pelo menos uma delas está vinculada a uma ordem de produção planejada ou em produção.'
                );
        }

        $produto
            ->variacoes()
            ->where(
                'ativo',
                true
            )
            ->update([
                'ativo' => false,
            ]);

        return redirect()
            ->route(
                'produtos.variacoes.index',
                [
                    'produto' => $produto,
                    'status' => 'inativos',
                ]
            )
            ->with(
                'sucesso',
                'Todas as variações do produto foram inativadas com sucesso.'
            );
    }

    public function reativarTodas(
        Produto $produto
    ) {
        if (!$produto->ativo) {
            return redirect()
                ->route(
                    'produtos.variacoes.index',
                    [
                        'produto' => $produto,
                        'status' => 'inativos',
                    ]
                )
                ->with(
                    'erro',
                    'Reative primeiro o produto para depois reativar suas variações.'
                );
        }

        $quantidadeInativas = $produto
            ->variacoes()
            ->where(
                'ativo',
                false
            )
            ->count();

        if ($quantidadeInativas === 0) {
            return redirect()
                ->route(
                    'produtos.variacoes.index',
                    $produto
                )
                ->with(
                    'erro',
                    'Este produto não possui variações inativas para reativar.'
                );
        }

        $produto
            ->variacoes()
            ->where(
                'ativo',
                false
            )
            ->update([
                'ativo' => true,
            ]);

        return redirect()
            ->route(
                'produtos.variacoes.index',
                $produto
            )
            ->with(
                'sucesso',
                'Todas as variações do produto foram reativadas com sucesso.'
            );
    }

    public function destroy(
        Produto $produto,
        ProdutoVariacao $variacao
    ) {
        $this->garantirPertencimento(
            $produto,
            $variacao
        );

        if (
            (float) $variacao->quantidade > 0
        ) {
            return redirect()
                ->route(
                    'produtos.variacoes.index',
                    [
                        'produto' => $produto,

                        'status' =>
                            $variacao->ativo
                                ? 'ativos'
                                : 'inativos',
                    ]
                )
                ->with(
                    'erro',
                    'Não é possível excluir definitivamente esta variação porque ela possui estoque disponível. O saldo precisa ser regularizado antes da exclusão.'
                );
        }

        if (
            $variacao
                ->movimentacoes()
                ->exists()
        ) {
            return redirect()
                ->route(
                    'produtos.variacoes.index',
                    [
                        'produto' => $produto,

                        'status' =>
                            $variacao->ativo
                                ? 'ativos'
                                : 'inativos',
                    ]
                )
                ->with(
                    'erro',
                    $this->mensagemHistorico(
                        $variacao,
                        'movimentações registradas'
                    )
                );
        }

        if (
            $variacao
                ->composicoesMateriais()
                ->exists()
        ) {
            return redirect()
                ->route(
                    'produtos.variacoes.index',
                    [
                        'produto' => $produto,

                        'status' =>
                            $variacao->ativo
                                ? 'ativos'
                                : 'inativos',
                    ]
                )
                ->with(
                    'erro',
                    $this->mensagemHistorico(
                        $variacao,
                        'uma ficha de consumo de materiais'
                    )
                );
        }

        $utilizadaEmProducao =
            OrdemProducaoItem::where(
                'id_variacao',
                $variacao->id_variacao
            )->exists();

        if ($utilizadaEmProducao) {
            return redirect()
                ->route(
                    'produtos.variacoes.index',
                    [
                        'produto' => $produto,

                        'status' =>
                            $variacao->ativo
                                ? 'ativos'
                                : 'inativos',
                    ]
                )
                ->with(
                    'erro',
                    $this->mensagemHistorico(
                        $variacao,
                        'histórico em ordens de produção'
                    )
                );
        }

        $statusDestino =
            $variacao->ativo
                ? 'ativos'
                : 'inativos';

        $variacao->delete();

        return redirect()
            ->route(
                'produtos.variacoes.index',
                [
                    'produto' => $produto,
                    'status' => $statusDestino,
                ]
            )
            ->with(
                'sucesso',
                'Variação excluída definitivamente.'
            );
    }

    private function variacaoPossuiOrdemAtiva(
        ProdutoVariacao $variacao
    ): bool {
        return DB::table(
            'ordem_producao_itens as item'
        )
            ->join(
                'ordens_producao as ordem',
                'ordem.id_ordem',
                '=',
                'item.id_ordem'
            )
            ->where(
                'item.id_variacao',
                $variacao->id_variacao
            )
            ->whereIn(
                'ordem.status',
                [
                    'planejada',
                    'em_producao',
                ]
            )
            ->exists();
    }

    private function produtoPossuiOrdemAtiva(
        Produto $produto
    ): bool {
        return DB::table(
            'ordem_producao_itens as item'
        )
            ->join(
                'ordens_producao as ordem',
                'ordem.id_ordem',
                '=',
                'item.id_ordem'
            )
            ->join(
                'produto_variacoes as variacao',
                'variacao.id_variacao',
                '=',
                'item.id_variacao'
            )
            ->where(
                'variacao.id_produto',
                $produto->id_produto
            )
            ->whereIn(
                'ordem.status',
                [
                    'planejada',
                    'em_producao',
                ]
            )
            ->exists();
    }

    private function validarDados(
        Request $request,
        Produto $produto,
        ?ProdutoVariacao $variacao = null
    ): array {
        $regraUnica = Rule::unique(
            'produto_variacoes',
            'cor'
        )
            ->where(
                function ($query) use (
                    $produto,
                    $request
                ) {
                    return $query
                        ->where(
                            'id_produto',
                            $produto->id_produto
                        )
                        ->where(
                            'tamanho',
                            $request->input(
                                'tamanho'
                            )
                        );
                }
            );

        if ($variacao !== null) {
            $regraUnica->ignore(
                $variacao->id_variacao,
                'id_variacao'
            );
        }

        return $request->validate(
            [
                'tamanho' => [
                    'required',
                    'string',
                    'min:1',
                    'max:30',
                    'regex:/[A-Za-zÀ-ÖØ-öø-ÿ]/u',
                ],

                'cor' => [
                    'required',
                    'string',
                    'min:2',
                    'max:60',
                    'regex:/[A-Za-zÀ-ÖØ-öø-ÿ]/u',
                    $regraUnica,
                ],

                'estoque_minimo' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:1000000',
                ],
            ],
            [
                'tamanho.required' =>
                    'Informe o tamanho da variação.',

                'tamanho.string' =>
                    'O tamanho informado é inválido.',

                'tamanho.max' =>
                    'O tamanho pode possuir no máximo 30 caracteres.',

                'tamanho.regex' =>
                    'O tamanho deve conter pelo menos uma letra.',

                'cor.required' =>
                    'Informe a cor da variação.',

                'cor.string' =>
                    'A cor deve ser um texto.',

                'cor.min' =>
                    'A cor deve possuir pelo menos 2 caracteres.',

                'cor.max' =>
                    'A cor pode possuir no máximo 60 caracteres.',

                'cor.regex' =>
                    'A cor deve conter pelo menos uma letra.',

                'cor.unique' =>
                    'Já existe uma variação deste produto com o mesmo tamanho e cor.',

                'estoque_minimo.required' =>
                    'Informe o estoque mínimo.',

                'estoque_minimo.integer' =>
                    'O estoque mínimo deve ser um número inteiro.',

                'estoque_minimo.min' =>
                    'O estoque mínimo não pode ser negativo.',

                'estoque_minimo.max' =>
                    'O estoque mínimo informado é muito alto.',
            ]
        );
    }

    private function normalizarDados(
        Request $request
    ): void {
        $tamanho = $request->input(
            'tamanho'
        );

        $cor = $request->input(
            'cor'
        );

        if (is_string($tamanho)) {
            $tamanho = preg_replace(
                '/\s+/',
                ' ',
                trim($tamanho)
            );
        }

        if (is_string($cor)) {
            $cor = preg_replace(
                '/\s+/',
                ' ',
                trim($cor)
            );
        }

        $request->merge([
            'tamanho' => $tamanho,
            'cor' => $cor,
        ]);
    }

    private function garantirPertencimento(
        Produto $produto,
        ProdutoVariacao $variacao
    ): void {
        abort_unless(
            $variacao->id_produto
                === $produto->id_produto,
            404
        );
    }

    private function motivoBloqueioExclusao(
        ProdutoVariacao $variacao
    ): ?string {
        if (
            (float) $variacao->quantidade > 0
        ) {
            return
                'Esta variação possui estoque disponível.';
        }

        if (
            (int)
            $variacao->movimentacoes_count > 0
        ) {
            return
                'Esta variação possui movimentações registradas e deve ser mantida para preservar o histórico.';
        }

        if (
            (int)
            $variacao->composicoes_count > 0
        ) {
            return
                'Esta variação possui uma ficha de consumo de materiais.';
        }

        if (
            (bool)
            $variacao->utilizada_em_producao
        ) {
            return
                'Esta variação já foi utilizada em uma ordem de produção e deve ser mantida para preservar o histórico.';
        }

        return null;
    }

    private function mensagemHistorico(
        ProdutoVariacao $variacao,
        string $motivo
    ): string {
        if ($variacao->ativo) {
            return
                'Não é possível excluir esta variação porque ela possui '
                . $motivo
                . '. Caso não seja mais utilizada, inative-a.';
        }

        return
            'Não é possível excluir definitivamente esta variação porque ela possui '
            . $motivo
            . ' e deve ser mantida para preservar o histórico do sistema.';
    }
}
