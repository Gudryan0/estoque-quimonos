<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Produto;
use App\Models\ProdutoVariacao;
use App\Models\ProdutoVariacaoMaterial;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ComposicaoProdutoController extends Controller
{
    public function index(
        Produto $produto,
        ProdutoVariacao $variacao
    ) {
        $this->garantirPertencimento(
            $produto,
            $variacao
        );

        /*
        |--------------------------------------------------------------------------
        | Composição atual
        |--------------------------------------------------------------------------
        |
        | Materiais já presentes na ficha continuam sendo exibidos mesmo se
        | forem posteriormente inativados. Isso preserva o histórico da
        | composição.
        |
        */

        $composicoes = $variacao
            ->composicoesMateriais()
            ->with('material')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Materiais disponíveis para inclusão
        |--------------------------------------------------------------------------
        |
        | Somente materiais ativos podem ser adicionados ou atualizados
        | através do formulário.
        |
        */

        $materiais = Material::with(
            'fornecedor'
        )
            ->where(
                'ativo',
                true
            )
            ->orderBy('categoria')
            ->orderBy('nome_material')
            ->orderBy('cor')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Consumos existentes por material
        |--------------------------------------------------------------------------
        |
        | Usado pela interface para preencher a quantidade atual quando o
        | usuário seleciona um material que já pertence à ficha.
        |
        */

        $consumosPorMaterial =
            $composicoes->keyBy(
                'id_material'
            );

        return view(
            'produtos.variacoes.composicao',
            compact(
                'produto',
                'variacao',
                'composicoes',
                'materiais',
                'consumosPorMaterial'
            )
        );
    }

    public function store(
        Request $request,
        Produto $produto,
        ProdutoVariacao $variacao
    ) {
        $this->garantirPertencimento(
            $produto,
            $variacao
        );

        if (
            !$produto->ativo
            ||
            !$variacao->ativo
        ) {
            return redirect()
                ->route(
                    'produtos.variacoes.composicao.index',
                    [
                        $produto,
                        $variacao,
                    ]
                )
                ->with(
                    'erro',
                    'Não é possível alterar a ficha de consumo de um produto ou variação inativa.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Validação do material
        |--------------------------------------------------------------------------
        */

        $dadosMaterial =
            $request->validate(
                [
                    'id_material' => [
                        'required',
                        'integer',

                        Rule::exists(
                            'materiais',
                            'id_material'
                        )->where(
                            function ($query) {
                                $query->where(
                                    'ativo',
                                    true
                                );
                            }
                        ),
                    ],
                ],
                [
                    'id_material.required' =>
                        'Selecione um material.',

                    'id_material.integer' =>
                        'O material selecionado é inválido.',

                    'id_material.exists' =>
                        'O material selecionado não existe ou está inativo.',
                ]
            );

        $material = Material::query()
            ->where(
                'ativo',
                true
            )
            ->find(
                $dadosMaterial[
                    'id_material'
                ]
            );

        if ($material === null) {
            return back()
                ->withInput()
                ->with(
                    'erro',
                    'O material selecionado não está mais disponível.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Validação da quantidade conforme unidade
        |--------------------------------------------------------------------------
        */

        $dadosQuantidade =
            $request->validate(
                [
                    'quantidade_por_unidade' => [
                        'required',
                        'numeric',
                        'min:0.001',
                        'max:999999999.999',

                        function (
                            string $attribute,
                            mixed $value,
                            \Closure $fail
                        ) use ($material) {
                            if (!is_numeric($value)) {
                                return;
                            }

                            $quantidade =
                                (float) $value;

                            $unidadeDiscreta =
                                in_array(
                                    $material
                                        ->unidade_medida,
                                    [
                                        'un',
                                        'rolo',
                                    ],
                                    true
                                );

                            if ($unidadeDiscreta) {
                                if ($quantidade < 1) {
                                    $fail(
                                        'Para materiais medidos em unidade ou rolo, a quantidade por unidade deve ser de pelo menos 1.'
                                    );

                                    return;
                                }

                                if (
                                    abs(
                                        $quantidade
                                        - round(
                                            $quantidade
                                        )
                                    ) > 0.0000001
                                ) {
                                    $fail(
                                        'Para materiais medidos em unidade ou rolo, a quantidade por unidade deve ser um número inteiro.'
                                    );
                                }
                            }
                        },
                    ],
                ],
                [
                    'quantidade_por_unidade.required' =>
                        'Informe a quantidade utilizada por unidade.',

                    'quantidade_por_unidade.numeric' =>
                        'A quantidade por unidade deve ser um número.',

                    'quantidade_por_unidade.min' =>
                        'A quantidade por unidade deve ser de pelo menos 0,001.',

                    'quantidade_por_unidade.max' =>
                        'A quantidade por unidade informada é muito alta.',
                ]
            );

        $quantidade =
            $this->normalizarQuantidade(
                $dadosQuantidade[
                    'quantidade_por_unidade'
                ],
                $material->unidade_medida
            );

        ProdutoVariacaoMaterial::updateOrCreate(
            [
                'id_variacao' =>
                    $variacao->id_variacao,

                'id_material' =>
                    $material->id_material,
            ],
            [
                'quantidade_por_unidade' =>
                    $quantidade,
            ]
        );

        return redirect()
            ->route(
                'produtos.variacoes.composicao.index',
                [
                    $produto,
                    $variacao,
                ]
            )
            ->with(
                'sucesso',
                'Ficha de consumo atualizada com sucesso.'
            );
    }

    public function destroy(
        Produto $produto,
        ProdutoVariacao $variacao,
        ProdutoVariacaoMaterial $composicao
    ) {
        $this->garantirPertencimento(
            $produto,
            $variacao
        );

        abort_unless(
            $composicao->id_variacao
                ===
                $variacao->id_variacao,
            404
        );

        if (
            !$produto->ativo
            ||
            !$variacao->ativo
        ) {
            return redirect()
                ->route(
                    'produtos.variacoes.composicao.index',
                    [
                        $produto,
                        $variacao,
                    ]
                )
                ->with(
                    'erro',
                    'Não é possível alterar a ficha de consumo de um produto ou variação inativa.'
                );
        }

        $composicao->delete();

        return redirect()
            ->route(
                'produtos.variacoes.composicao.index',
                [
                    $produto,
                    $variacao,
                ]
            )
            ->with(
                'sucesso',
                'Material removido da ficha de consumo.'
            );
    }

    private function garantirPertencimento(
        Produto $produto,
        ProdutoVariacao $variacao
    ): void {
        abort_unless(
            $variacao->id_produto
                ===
                $produto->id_produto,
            404
        );
    }

    private function normalizarQuantidade(
        mixed $valor,
        string $unidade
    ): float|int {
        $numero =
            (float) $valor;

        if (
            in_array(
                $unidade,
                [
                    'un',
                    'rolo',
                ],
                true
            )
        ) {
            return (int) round(
                $numero
            );
        }

        return round(
            $numero,
            3
        );
    }
}
