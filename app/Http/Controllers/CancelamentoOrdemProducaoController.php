<?php

namespace App\Http\Controllers;

use App\Models\OrdemProducao;
use App\Models\OrdemProducaoHistorico;
use App\Models\OrdemProducaoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CancelamentoOrdemProducaoController extends Controller
{
    public function show(
        OrdemProducao $ordem
    ) {
        if (
            $ordem->status
            === 'cancelada'
        ) {
            return redirect()
                ->route(
                    'ordens.show',
                    $ordem
                )
                ->with(
                    'erro',
                    'Esta ordem já está cancelada.'
                );
        }

        /*
         * A conclusão da OP é terminal.
         * Depois que todos os itens forem concluídos,
         * a ordem não pode mais ser reaberta ou cancelada.
         */
        if (
            $ordem->status
            === 'concluida'
        ) {
            return redirect()
                ->route(
                    'ordens.show',
                    $ordem
                )
                ->with(
                    'erro',
                    'Esta ordem já foi concluída definitivamente e não pode ser cancelada.'
                );
        }

        if (
            !in_array(
                $ordem->status,
                [
                    'planejada',
                    'em_producao',
                ],
                true
            )
        ) {
            return redirect()
                ->route(
                    'ordens.show',
                    $ordem
                )
                ->with(
                    'erro',
                    'Esta ordem não pode ser cancelada.'
                );
        }

        /*
         * Enquanto a OP ainda está em produção,
         * itens concluídos podem ser revertidos para
         * Finalização. O cancelamento somente fica
         * disponível depois dessa correção.
         */
        if (
            $ordem->status
            === 'em_producao'
            &&
            $ordem
                ->itens()
                ->where(
                    'etapa_atual',
                    'concluido'
                )
                ->exists()
        ) {
            return redirect()
                ->route(
                    'ordens.show',
                    $ordem
                )
                ->with(
                    'erro',
                    'Esta ordem possui item concluído. Reverta primeiro os itens concluídos para Finalização antes de cancelar a ordem.'
                );
        }

        $ordem->loadCount('itens');

        return view(
            'ordens.cancelar',
            compact('ordem')
        );
    }

    public function cancelar(
        Request $request,
        OrdemProducao $ordem
    ) {
        $motivo =
            $request->input(
                'motivo_cancelamento'
            );

        if (is_string($motivo)) {
            $motivo = trim($motivo);
        }

        $request->merge([
            'motivo_cancelamento' =>
                $motivo,
        ]);

        $dados = $request->validate(
            [
                'motivo_cancelamento' => [
                    'required',
                    'string',
                    'min:3',
                    'max:2000',
                ],

                'confirmar_cancelamento' => [
                    'accepted',
                ],
            ],
            [
                'motivo_cancelamento.required' =>
                    'Informe o motivo do cancelamento.',

                'motivo_cancelamento.string' =>
                    'O motivo do cancelamento deve ser um texto.',

                'motivo_cancelamento.min' =>
                    'O motivo do cancelamento deve possuir pelo menos 3 caracteres.',

                'motivo_cancelamento.max' =>
                    'O motivo do cancelamento pode possuir no máximo 2000 caracteres.',

                'confirmar_cancelamento.accepted' =>
                    'Confirme que deseja cancelar esta ordem de produção.',
            ]
        );

        $resultado = DB::transaction(
            function () use (
                $ordem,
                $dados
            ) {
                $ordemBloqueada =
                    OrdemProducao::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $ordem->id_ordem
                        );

                if (
                    !in_array(
                        $ordemBloqueada->status,
                        [
                            'planejada',
                            'em_producao',
                        ],
                        true
                    )
                ) {
                    return [
                        'erro' =>
                            'O estado atual desta ordem não permite cancelamento.',
                    ];
                }

                $itens =
                    OrdemProducaoItem::query()
                        ->where(
                            'id_ordem',
                            $ordemBloqueada
                                ->id_ordem
                        )
                        ->lockForUpdate()
                        ->get();

                /*
                 * Uma OP ainda em produção pode conter
                 * itens já concluídos enquanto outros
                 * permanecem pendentes.
                 *
                 * Como esses itens já adicionaram produtos
                 * ao estoque, primeiro é obrigatório usar
                 * o fluxo normal de reversão.
                 */
                if (
                    $ordemBloqueada->status
                    === 'em_producao'
                ) {
                    $possuiItemConcluido =
                        $itens->contains(
                            function ($item) {
                                return
                                    $item->etapa_atual
                                    === 'concluido';
                            }
                        );

                    if ($possuiItemConcluido) {
                        return [
                            'erro' =>
                                'Esta ordem possui item concluído. Reverta primeiro os itens concluídos para Finalização antes de cancelar a ordem.',
                        ];
                    }
                }

                $statusAnterior =
                    $ordemBloqueada->status;

                $ordemBloqueada->status =
                    'cancelada';

                $ordemBloqueada
                    ->motivo_cancelamento =
                    $dados[
                        'motivo_cancelamento'
                    ];

                $ordemBloqueada
                    ->cancelada_em =
                    now();

                $ordemBloqueada
                    ->id_usuario_cancelamento =
                    auth()->id();

                $ordemBloqueada->save();

                /*
                 * A etapa atual dos itens é preservada.
                 *
                 * "cancelado" é utilizado apenas como
                 * destino do registro histórico.
                 */
                foreach ($itens as $item) {
                    if (
                        $statusAnterior
                        === 'planejada'
                    ) {
                        $etapaOrigem = null;

                        $observacao =
                            'Ordem cancelada antes do início da produção. Motivo: '
                            . $dados[
                                'motivo_cancelamento'
                            ];
                    } else {
                        $etapaOrigem =
                            $item->etapa_atual;

                        $observacao =
                            'Ordem cancelada durante a produção. '
                            . 'Os materiais já consumidos não foram devolvidos automaticamente ao estoque. '
                            . 'Motivo: '
                            . $dados[
                                'motivo_cancelamento'
                            ];
                    }

                    OrdemProducaoHistorico::create([
                        'id_ordem' =>
                            $ordemBloqueada
                                ->id_ordem,

                        'id_item' =>
                            $item->id_item,

                        'id_usuario' =>
                            auth()->id(),

                        'acao' =>
                            'cancelamento',

                        'etapa_origem' =>
                            $etapaOrigem,

                        'etapa_destino' =>
                            'cancelado',

                        'observacao' =>
                            $observacao,
                    ]);
                }

                return [
                    'erro' => null,

                    'status_anterior' =>
                        $statusAnterior,
                ];
            }
        );

        if ($resultado['erro']) {
            return redirect()
                ->route(
                    'ordens.show',
                    $ordem
                )
                ->with(
                    'erro',
                    $resultado['erro']
                );
        }

        if (
            $resultado[
                'status_anterior'
            ]
            === 'em_producao'
        ) {
            return redirect()
                ->route(
                    'ordens.show',
                    $ordem
                )
                ->with(
                    'sucesso',
                    'Ordem cancelada. Os materiais já consumidos permaneceram baixados do estoque.'
                );
        }

        return redirect()
            ->route(
                'ordens.show',
                $ordem
            )
            ->with(
                'sucesso',
                'Ordem de produção cancelada com sucesso.'
            );
    }
}
