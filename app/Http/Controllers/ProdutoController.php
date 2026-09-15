<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdutoController extends Controller
{
    public function index(
        Request $request
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

        $categoria = trim(
            (string) $request->query(
                'categoria',
                ''
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Categorias disponíveis no status atual
        |--------------------------------------------------------------------------
        */

        $categorias = Produto::query()
            ->where(
                'ativo',
                $status === 'ativos'
            )
            ->whereNotNull('categoria')
            ->where('categoria', '!=', '')
            ->distinct()
            ->orderBy('categoria')
            ->pluck('categoria');

        /*
        |--------------------------------------------------------------------------
        | Produtos
        |--------------------------------------------------------------------------
        */

        $produtos = Produto::query()
            ->withCount([
                'variacoes',

                'variacoes as variacoes_ativas_count' =>
                    function ($query) {
                        $query->where(
                            'ativo',
                            true
                        );
                    },
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
                                    'nome_produto',
                                    'like',
                                    "%{$busca}%"
                                )
                                ->orWhere(
                                    'categoria',
                                    'like',
                                    "%{$busca}%"
                                )
                                ->orWhere(
                                    'descricao',
                                    'like',
                                    "%{$busca}%"
                                );
                        }
                    );
                }
            )
            ->when(
                $categoria !== '',
                function ($query) use (
                    $categoria
                ) {
                    $query->where(
                        'categoria',
                        $categoria
                    );
                }
            )
            ->orderBy(
                'nome_produto'
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Produtos envolvidos em OP ativa
        |--------------------------------------------------------------------------
        |
        | Usado também pela interface para evitar que o usuário tente
        | inativar algo que o backend já sabe que não pode ser inativado.
        |
        */

        $produtosComOrdemAtiva =
            DB::table(
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
                ->whereIn(
                    'ordem.status',
                    [
                        'planejada',
                        'em_producao',
                    ]
                )
                ->distinct()
                ->pluck(
                    'variacao.id_produto'
                )
                ->mapWithKeys(
                    function ($idProduto) {
                        return [
                            (int) $idProduto => true,
                        ];
                    }
                );

        $produtos->each(
            function ($produto) use (
                $produtosComOrdemAtiva
            ) {
                $produto->setAttribute(
                    'possui_ordem_producao_ativa',
                    $produtosComOrdemAtiva->has(
                        (int) $produto->id_produto
                    )
                );
            }
        );

        return view(
            'produtos.index',
            compact(
                'produtos',
                'status',
                'busca',
                'categoria',
                'categorias'
            )
        );
    }

    public function create()
    {
        $categorias = $this
            ->categoriasExistentes();

        return view(
            'produtos.create',
            compact('categorias')
        );
    }

    public function store(
        Request $request
    ) {
        $this->normalizarDados(
            $request
        );

        $dados = $this->validarDados(
            $request
        );

        Produto::create(
            $dados
        );

        return redirect()
            ->route(
                'produtos.index'
            )
            ->with(
                'sucesso',
                'Produto cadastrado com sucesso.'
            );
    }

    public function edit(
        Produto $produto
    ) {
        $categorias = $this
            ->categoriasExistentes();

        return view(
            'produtos.edit',
            compact(
                'produto',
                'categorias'
            )
        );
    }

    public function update(
        Request $request,
        Produto $produto
    ) {
        $this->normalizarDados(
            $request
        );

        $dados = $this->validarDados(
            $request
        );

        $produto->update(
            $dados
        );

        return redirect()
            ->route(
                'produtos.index',
                [
                    'status' =>
                        $produto->ativo
                            ? 'ativos'
                            : 'inativos',
                ]
            )
            ->with(
                'sucesso',
                'Produto atualizado com sucesso.'
            );
    }

    public function inativar(
        Produto $produto
    ) {
        if (!$produto->ativo) {
            return redirect()
                ->route(
                    'produtos.index',
                    [
                        'status' =>
                            'inativos',
                    ]
                )
                ->with(
                    'erro',
                    'Este produto já está inativo.'
                );
        }

        if (
            $this->possuiOrdemProducaoAtiva(
                $produto
            )
        ) {
            return redirect()
                ->route(
                    'produtos.index'
                )
                ->with(
                    'erro',
                    'Não é possível inativar este produto porque uma ou mais de suas variações estão vinculadas a uma ordem de produção planejada ou em produção.'
                );
        }

        DB::transaction(
            function () use ($produto) {
                $produto
                    ->variacoes()
                    ->update([
                        'ativo' => false,
                    ]);

                $produto->ativo = false;
                $produto->save();
            }
        );

        return redirect()
            ->route(
                'produtos.index'
            )
            ->with(
                'sucesso',
                'Produto inativado com sucesso. Todas as suas variações foram inativadas.'
            );
    }

    public function reativar(
        Produto $produto
    ) {
        if ($produto->ativo) {
            return redirect()
                ->route(
                    'produtos.index'
                )
                ->with(
                    'erro',
                    'Este produto já está ativo.'
                );
        }

        $produto->ativo = true;
        $produto->save();

        return redirect()
            ->route(
                'produtos.index',
                [
                    'status' => 'inativos',
                ]
            )
            ->with(
                'sucesso',
                'Produto reativado com sucesso. As variações permanecem inativas e podem ser reativadas individualmente ou todas de uma vez.'
            );
    }

    public function destroy(
        Produto $produto
    ) {
        if (
            $produto
                ->variacoes()
                ->exists()
        ) {
            return redirect()
                ->route(
                    'produtos.index',
                    [
                        'status' =>
                            $produto->ativo
                                ? 'ativos'
                                : 'inativos',
                    ]
                )
                ->with(
                    'erro',
                    'Não é possível excluir este produto porque ele possui variações cadastradas. Caso não seja mais utilizado, inative-o.'
                );
        }

        $produto->delete();

        return redirect()
            ->route(
                'produtos.index',
                [
                    'status' =>
                        $produto->ativo
                            ? 'ativos'
                            : 'inativos',
                ]
            )
            ->with(
                'sucesso',
                'Produto excluído definitivamente.'
            );
    }

    private function possuiOrdemProducaoAtiva(
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
        Request $request
    ): array {
        return $request->validate(
            [
                'nome_produto' => [
                    'required',
                    'string',
                    'min:2',
                    'max:120',
                    'regex:/[A-Za-zÀ-ÖØ-öø-ÿ]/u',
                ],

                'categoria' => [
                    'required',
                    'string',
                    'min:2',
                    'max:80',
                    'regex:/[A-Za-zÀ-ÖØ-öø-ÿ]/u',
                ],

                'descricao' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],
            ],
            [
                'nome_produto.required' =>
                    'Informe o nome do produto.',

                'nome_produto.string' =>
                    'O nome do produto deve ser um texto.',

                'nome_produto.min' =>
                    'O nome do produto deve possuir pelo menos 2 caracteres.',

                'nome_produto.max' =>
                    'O nome do produto pode possuir no máximo 120 caracteres.',

                'nome_produto.regex' =>
                    'O nome do produto deve conter pelo menos uma letra.',

                'categoria.required' =>
                    'Informe a categoria do produto.',

                'categoria.string' =>
                    'A categoria deve ser um texto.',

                'categoria.min' =>
                    'A categoria deve possuir pelo menos 2 caracteres.',

                'categoria.max' =>
                    'A categoria pode possuir no máximo 80 caracteres.',

                'categoria.regex' =>
                    'A categoria deve conter pelo menos uma letra.',

                'descricao.string' =>
                    'A descrição deve ser um texto.',

                'descricao.max' =>
                    'A descrição pode possuir no máximo 2000 caracteres.',
            ]
        );
    }

    private function normalizarDados(
        Request $request
    ): void {
        $nome = $request->input(
            'nome_produto'
        );

        $categoria = $request->input(
            'categoria'
        );

        $descricao = $request->input(
            'descricao'
        );

        if (is_string($nome)) {
            $nome = preg_replace(
                '/\s+/',
                ' ',
                trim($nome)
            );
        }

        if (is_string($categoria)) {
            $categoria = preg_replace(
                '/\s+/',
                ' ',
                trim($categoria)
            );
        }

        if (is_string($descricao)) {
            $descricao = trim(
                $descricao
            );

            if ($descricao === '') {
                $descricao = null;
            }
        }

        $request->merge([
            'nome_produto' => $nome,
            'categoria' => $categoria,
            'descricao' => $descricao,
        ]);
    }

    private function categoriasExistentes()
    {
        return Produto::query()
            ->whereNotNull('categoria')
            ->where('categoria', '!=', '')
            ->distinct()
            ->orderBy('categoria')
            ->pluck('categoria');
    }
}
