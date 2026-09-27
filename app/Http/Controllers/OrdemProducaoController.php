<?php

namespace App\Http\Controllers;

use App\Models\OrdemProducao;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrdemProducaoController extends Controller
{
    public function index(
        Request $request
    ) {
        $busca = trim(
            (string) $request->query(
                'q',
                ''
            )
        );

        $statusPermitidos = [
            'planejada',
            'em_producao',
            'concluida',
            'cancelada',
        ];

        $status =
            in_array(
                $request->query('status'),
                $statusPermitidos,
                true
            )
                ? $request->query('status')
                : '';

        $prazosPermitidos = [
            'atrasadas',
            'hoje',
            'futuras',
            'sem_previsao',
        ];

        $prazo =
            in_array(
                $request->query('prazo'),
                $prazosPermitidos,
                true
            )
                ? $request->query('prazo')
                : '';

        $hoje =
            now(
                config(
                    'app.business_timezone',
                    'America/Sao_Paulo'
                )
            )->toDateString();

        $ordens = OrdemProducao::query()
            ->with([
                'criador',
                'itens.cliente',
            ])
            ->withCount('itens')
            ->when(
                $busca !== '',
                function ($query) use ($busca) {
                    $query->where(
                        function ($query) use ($busca) {
                            $query
                                ->where(
                                    'codigo',
                                    'like',
                                    "%{$busca}%"
                                )
                                ->orWhereHas(
                                    'itens',
                                    function ($query) use (
                                        $busca
                                    ) {
                                        $query->where(
                                            'cliente_nome',
                                            'like',
                                            "%{$busca}%"
                                        );
                                    }
                                )
                                ->orWhereHas(
                                    'criador',
                                    function ($query) use (
                                        $busca
                                    ) {
                                        $query->where(
                                            'name',
                                            'like',
                                            "%{$busca}%"
                                        );
                                    }
                                );
                        }
                    );
                }
            )
            ->when(
                $status !== '',
                function ($query) use ($status) {
                    $query->where(
                        'status',
                        $status
                    );
                }
            )
            ->when(
                $prazo === 'atrasadas',
                function ($query) use ($hoje) {
                    $query
                        ->whereIn(
                            'status',
                            [
                                'planejada',
                                'em_producao',
                            ]
                        )
                        ->whereNotNull(
                            'data_prevista'
                        )
                        ->whereDate(
                            'data_prevista',
                            '<',
                            $hoje
                        );
                }
            )
            ->when(
                $prazo === 'hoje',
                function ($query) use ($hoje) {
                    $query
                        ->whereIn(
                            'status',
                            [
                                'planejada',
                                'em_producao',
                            ]
                        )
                        ->whereDate(
                            'data_prevista',
                            $hoje
                        );
                }
            )
            ->when(
                $prazo === 'futuras',
                function ($query) use ($hoje) {
                    $query
                        ->whereIn(
                            'status',
                            [
                                'planejada',
                                'em_producao',
                            ]
                        )
                        ->whereDate(
                            'data_prevista',
                            '>',
                            $hoje
                        );
                }
            )
            ->when(
                $prazo === 'sem_previsao',
                function ($query) {
                    $query->whereNull(
                        'data_prevista'
                    );
                }
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id_ordem')
            ->paginate(25)
            ->withQueryString();

        return view(
            'ordens.index',
            compact(
                'ordens',
                'busca',
                'status',
                'prazo'
            )
        );
    }

    public function create()
    {
        /*
         * A OP não possui cliente próprio.
         *
         * Os clientes serão informados
         * nos lotes adicionados posteriormente.
         */
        return view(
            'ordens.create'
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

        do {
            $codigo =
                'OP-'
                . now(
                    config(
                        'app.business_timezone',
                        'America/Sao_Paulo'
                    )
                )->format('Ymd')
                . '-'
                . Str::upper(
                    Str::random(6)
                );

        } while (
            OrdemProducao::where(
                'codigo',
                $codigo
            )->exists()
        );

        $ordem = OrdemProducao::create([
            'codigo' =>
                $codigo,

            'id_usuario_criador' =>
                auth()->id(),

            'status' =>
                'planejada',

            'data_prevista' =>
                $dados['data_prevista']
                ?? null,

            'observacao' =>
                $dados['observacao']
                ?? null,
        ]);

        return redirect()
            ->route(
                'ordens.show',
                $ordem
            )
            ->with(
                'sucesso',
                'Ordem de produção criada com sucesso. Adicione os lotes que serão produzidos.'
            );
    }

    public function show(
        OrdemProducao $ordem
    ) {
        /*
         * Cada item representa um lote.
         *
         * As variações e respectivas quantidades
         * pertencem ao lote, não diretamente à OP.
         */
        $ordem->load([
            'criador',
            'cancelador',

            'itens.produto',
            'itens.cliente',

            'itens.variacoes.variacao',

            'historicos.usuario',
            'historicos.item.produto',
            'historicos.item.variacoes.variacao',
        ]);

        return view(
            'ordens.show',
            compact('ordem')
        );
    }

    public function edit(
        OrdemProducao $ordem
    ) {
        /*
         * A interface bloqueia a edição,
         * mas esta proteção também impede
         * o acesso direto pela URL.
         */
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
                    'Somente ordens planejadas podem ser editadas.'
                );
        }

        return view(
            'ordens.edit',
            compact('ordem')
        );
    }

    public function update(
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
                    'Somente ordens planejadas podem ser alteradas.'
                );
        }

        $this->normalizarDados(
            $request
        );

        $dados = $this->validarDados(
            $request,
            $ordem
        );

        $ordem->update([
            'data_prevista' =>
                $dados['data_prevista']
                ?? null,

            'observacao' =>
                $dados['observacao']
                ?? null,
        ]);

        return redirect()
            ->route(
                'ordens.show',
                $ordem
            )
            ->with(
                'sucesso',
                'Ordem de produção atualizada com sucesso.'
            );
    }

    public function destroy(
        OrdemProducao $ordem
    ) {
        if (
            $ordem->status
            !== 'planejada'
        ) {
            return redirect()
                ->route(
                    'ordens.index'
                )
                ->with(
                    'erro',
                    'Somente ordens planejadas podem ser excluídas.'
                );
        }

        if (
            $ordem
                ->itens()
                ->exists()
        ) {
            return redirect()
                ->route(
                    'ordens.show',
                    $ordem
                )
                ->with(
                    'erro',
                    'Remova os lotes antes de excluir a ordem.'
                );
        }

        /*
         * Proteção adicional de integridade.
         */
        if (
            $ordem
                ->historicos()
                ->exists()
        ) {
            return redirect()
                ->route(
                    'ordens.show',
                    $ordem
                )
                ->with(
                    'erro',
                    'Não é possível excluir esta ordem porque ela possui histórico de produção registrado.'
                );
        }

        $ordem->delete();

        return redirect()
            ->route(
                'ordens.index'
            )
            ->with(
                'sucesso',
                'Ordem de produção excluída com sucesso.'
            );
    }

    private function validarDados(
        Request $request,
        ?OrdemProducao $ordem = null
    ): array {
        $dados = $request->validate(
            [
                'data_prevista' => [
                    'nullable',
                    'date',
                ],

                'observacao' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],
            ],
            [
                'data_prevista.date' =>
                    'Informe uma data prevista válida.',

                'observacao.string' =>
                    'A observação deve ser um texto.',

                'observacao.max' =>
                    'A observação pode possuir no máximo 2000 caracteres.',
            ]
        );

        $this->validarDataPrevista(
            $dados['data_prevista']
            ?? null,
            $ordem
        );

        return $dados;
    }

    private function validarDataPrevista(
        ?string $dataPrevista,
        ?OrdemProducao $ordem = null
    ): void {
        if ($dataPrevista === null) {
            return;
        }

        $novaData = Carbon::parse(
            $dataPrevista
        )->startOfDay();

        $hoje =
            now(
                config(
                    'app.business_timezone',
                    'America/Sao_Paulo'
                )
            )->toDateString();

        /*
         * Criação:
         * uma nova OP não pode nascer
         * com previsão no passado.
         */
        if ($ordem === null) {
            if (
                $novaData->format('Y-m-d')
                    < $hoje
            ) {
                throw ValidationException::withMessages([
                    'data_prevista' =>
                        'A data prevista não pode ser anterior à data atual.',
                ]);
            }

            return;
        }

        /*
         * Edição:
         * uma ordem antiga pode já possuir
         * uma previsão vencida.
         *
         * Permitimos salvar sem alterar
         * essa data, mas uma nova data
         * escolhida deve ser hoje ou futura.
         */

        $dataAtual =
            $ordem->data_prevista
                ? Carbon::parse(
                    $ordem->data_prevista
                )->format('Y-m-d')
                : null;

        $novaDataFormatada =
            $novaData->format(
                'Y-m-d'
            );

        $dataFoiAlterada =
            $novaDataFormatada
            !== $dataAtual;

        if (
            $dataFoiAlterada
            &&
            $novaData->format('Y-m-d')
                < $hoje
        ) {
            throw ValidationException::withMessages([
                'data_prevista' =>
                    'Ao alterar a data prevista, escolha a data atual ou uma data futura.',
            ]);
        }
    }

    private function normalizarDados(
        Request $request
    ): void {
        $dataPrevista =
            $request->input(
                'data_prevista'
            );

        $observacao =
            $request->input(
                'observacao'
            );

        if (is_string($dataPrevista)) {
            $dataPrevista =
                trim($dataPrevista);

            if ($dataPrevista === '') {
                $dataPrevista = null;
            }
        }

        if (is_string($observacao)) {
            $observacao =
                trim($observacao);

            if ($observacao === '') {
                $observacao = null;
            }
        }

        $request->merge([
            'data_prevista' =>
                $dataPrevista,

            'observacao' =>
                $observacao,
        ]);
    }
}
