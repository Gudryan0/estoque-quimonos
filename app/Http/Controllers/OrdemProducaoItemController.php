<?php

namespace App\Http\Controllers;

use App\Models\OrdemProducao;
use App\Models\OrdemProducaoItem;
use App\Models\ProdutoVariacao;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrdemProducaoItemController extends Controller
{
    public function create(
        OrdemProducao $ordem
    ) {
        if (
            $ordem->status
            !== 'planejada'
        ) {
            return redirect()
                ->route(
                    'ordens.show',
                    $ordem
                )
                ->with(
                    'erro',
                    'Não é possível adicionar itens nesta ordem.'
                );
        }

        $variacoes =
            $this->variacoesDisponiveis();

        return view(
            'ordens.itens.create',
            compact(
                'ordem',
                'variacoes'
            )
        );
    }

    public function store(
        Request $request,
        OrdemProducao $ordem
    ) {
        if (
            $ordem->status
            !== 'planejada'
        ) {
            return redirect()
                ->route(
                    'ordens.show',
                    $ordem
                )
                ->with(
                    'erro',
                    'Não é possível adicionar itens nesta ordem.'
                );
        }

        $dados =
            $this->validarDados(
                $request
            );

        $variacao =
            ProdutoVariacao::with(
                'produto'
            )
                ->find(
                    $dados[
                        'id_variacao'
                    ]
                );

        if (
            !$variacao
            ||
            !$variacao->ativo
            ||
            !$variacao->produto
            ||
            !$variacao->produto->ativo
        ) {
            return back()
                ->withInput()
                ->with(
                    'erro',
                    'Não é possível adicionar esta variação porque o produto ou a variação está inativo.'
                );
        }

        OrdemProducaoItem::create([
            ...$dados,

            'id_ordem' =>
                $ordem->id_ordem,

            'etapa_atual' =>
                'corte',
        ]);

        return redirect()
            ->route(
                'ordens.show',
                $ordem
            )
            ->with(
                'sucesso',
                'Item adicionado à ordem de produção.'
            );
    }

    public function edit(
        OrdemProducao $ordem,
        OrdemProducaoItem $item
    ) {
        $this->garantirPertencimento(
            $ordem,
            $item
        );

        if (
            $ordem->status
            !== 'planejada'
        ) {
            return redirect()
                ->route(
                    'ordens.show',
                    $ordem
                )
                ->with(
                    'erro',
                    'Itens só podem ser editados enquanto a ordem estiver planejada.'
                );
        }

        $variacoes =
            $this->variacoesDisponiveis();

        return view(
            'ordens.itens.edit',
            compact(
                'ordem',
                'item',
                'variacoes'
            )
        );
    }

    public function update(
        Request $request,
        OrdemProducao $ordem,
        OrdemProducaoItem $item
    ) {
        $this->garantirPertencimento(
            $ordem,
            $item
        );

        if (
            $ordem->status
            !== 'planejada'
        ) {
            return redirect()
                ->route(
                    'ordens.show',
                    $ordem
                )
                ->with(
                    'erro',
                    'Itens só podem ser editados enquanto a ordem estiver planejada.'
                );
        }

        $dados =
            $this->validarDados(
                $request
            );

        $variacao =
            ProdutoVariacao::with(
                'produto'
            )
                ->find(
                    $dados[
                        'id_variacao'
                    ]
                );

        if (
            !$variacao
            ||
            !$variacao->ativo
            ||
            !$variacao->produto
            ||
            !$variacao->produto->ativo
        ) {
            return back()
                ->withInput()
                ->with(
                    'erro',
                    'Não é possível utilizar esta variação porque o produto ou a variação está inativo.'
                );
        }

        $item->update(
            $dados
        );

        return redirect()
            ->route(
                'ordens.show',
                $ordem
            )
            ->with(
                'sucesso',
                'Item atualizado com sucesso.'
            );
    }

    public function destroy(
        OrdemProducao $ordem,
        OrdemProducaoItem $item
    ) {
        $this->garantirPertencimento(
            $ordem,
            $item
        );

        if (
            $ordem->status
            !== 'planejada'
        ) {
            return redirect()
                ->route(
                    'ordens.show',
                    $ordem
                )
                ->with(
                    'erro',
                    'Itens só podem ser removidos enquanto a ordem estiver planejada.'
                );
        }

        $item->delete();

        return redirect()
            ->route(
                'ordens.show',
                $ordem
            )
            ->with(
                'sucesso',
                'Item removido da ordem.'
            );
    }

    private function validarDados(
        Request $request
    ): array {
        return $request->validate(
            [
                'id_variacao' => [
                    'required',
                    'integer',

                    Rule::exists(
                        'produto_variacoes',
                        'id_variacao'
                    )->where(
                        function ($query) {
                            $query->where(
                                'ativo',
                                true
                            );
                        }
                    ),
                ],

                'quantidade' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:1000000',
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
                'id_variacao.required' =>
                    'Selecione uma variação.',

                'id_variacao.integer' =>
                    'A variação selecionada é inválida.',

                'id_variacao.exists' =>
                    'A variação selecionada não existe ou está inativa.',

                'quantidade.required' =>
                    'Informe a quantidade.',

                'quantidade.integer' =>
                    'A quantidade deve ser um número inteiro.',

                'quantidade.min' =>
                    'A quantidade deve ser de pelo menos 1 unidade.',

                'quantidade.max' =>
                    'A quantidade informada é muito alta.',

                'descricao_bordado.string' =>
                    'A descrição do bordado deve ser um texto.',

                'descricao_bordado.max' =>
                    'A descrição do bordado pode possuir no máximo 2000 caracteres.',

                'cor_linha.string' =>
                    'A cor da linha deve ser um texto.',

                'cor_linha.max' =>
                    'A cor da linha pode possuir no máximo 255 caracteres.',

                'etiqueta.string' =>
                    'A etiqueta deve ser um texto.',

                'etiqueta.max' =>
                    'A etiqueta pode possuir no máximo 255 caracteres.',

                'posicao_etiqueta.string' =>
                    'A posição da etiqueta deve ser um texto.',

                'posicao_etiqueta.max' =>
                    'A posição da etiqueta pode possuir no máximo 255 caracteres.',

                'responsavel_costura.string' =>
                    'O responsável pela costura deve ser um texto.',

                'responsavel_costura.max' =>
                    'O responsável pela costura pode possuir no máximo 255 caracteres.',

                'observacao.string' =>
                    'A observação deve ser um texto.',

                'observacao.max' =>
                    'A observação pode possuir no máximo 2000 caracteres.',
            ]
        );
    }

    private function variacoesDisponiveis()
    {
        return ProdutoVariacao::with(
            'produto'
        )
            ->where(
                'ativo',
                true
            )
            ->whereHas(
                'produto',
                function ($query) {
                    $query->where(
                        'ativo',
                        true
                    );
                }
            )
            ->get()
            ->sortBy(
                function ($variacao) {
                    return
                        $variacao
                            ->produto
                            ->nome_produto
                        . $variacao->tamanho
                        . $variacao->cor;
                }
            )
            ->values();
    }

    private function garantirPertencimento(
        OrdemProducao $ordem,
        OrdemProducaoItem $item
    ): void {
        abort_unless(
            $item->id_ordem
                ===
            $ordem->id_ordem,
            404
        );
    }
}
