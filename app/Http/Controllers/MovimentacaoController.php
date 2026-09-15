<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Movimentacao;
use App\Models\ProdutoVariacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MovimentacaoController extends Controller
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

        $tipoItem =
            in_array(
                $request->query('tipo_item'),
                [
                    'produto',
                    'material',
                ],
                true
            )
                ? $request->query('tipo_item')
                : '';

        $tipoMovimentacao =
            in_array(
                $request->query(
                    'tipo_movimentacao'
                ),
                [
                    'entrada',
                    'saida',
                ],
                true
            )
                ? $request->query(
                    'tipo_movimentacao'
                )
                : '';

        $origensPermitidas = [
            'manual',
            'producao_consumo',
            'producao_conclusao',
            'producao_reversao',
        ];

        $origem =
            in_array(
                $request->query('origem'),
                $origensPermitidas,
                true
            )
                ? $request->query('origem')
                : '';

        $dataInicio =
            $this->normalizarDataFiltro(
                $request->query(
                    'data_inicio'
                )
            );

        $dataFim =
            $this->normalizarDataFiltro(
                $request->query(
                    'data_fim'
                )
            );

        $movimentacoes = Movimentacao::query()
            ->with([
                'usuario',
                'variacao.produto',
                'material',
            ])
            ->when(
                $busca !== '',
                function ($query) use ($busca) {
                    $query->where(
                        function ($query) use ($busca) {
                            $query
                                ->where(
                                    'observacao',
                                    'like',
                                    "%{$busca}%"
                                )
                                ->orWhereHas(
                                    'usuario',
                                    function ($query) use (
                                        $busca
                                    ) {
                                        $query->where(
                                            'name',
                                            'like',
                                            "%{$busca}%"
                                        );
                                    }
                                )
                                ->orWhereHas(
                                    'variacao',
                                    function ($query) use (
                                        $busca
                                    ) {
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
                                            )
                                            ->orWhereHas(
                                                'produto',
                                                function (
                                                    $query
                                                ) use (
                                                    $busca
                                                ) {
                                                    $query
                                                        ->where(
                                                            'nome_produto',
                                                            'like',
                                                            "%{$busca}%"
                                                        );
                                                }
                                            );
                                    }
                                )
                                ->orWhereHas(
                                    'material',
                                    function ($query) use (
                                        $busca
                                    ) {
                                        $query
                                            ->where(
                                                'nome_material',
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
                    );
                }
            )
            ->when(
                $tipoItem === 'produto',
                function ($query) {
                    $query->whereNotNull(
                        'id_variacao'
                    );
                }
            )
            ->when(
                $tipoItem === 'material',
                function ($query) {
                    $query->whereNotNull(
                        'id_material'
                    );
                }
            )
            ->when(
                $tipoMovimentacao !== '',
                function ($query) use (
                    $tipoMovimentacao
                ) {
                    $query->where(
                        'tipo_movimentacao',
                        $tipoMovimentacao
                    );
                }
            )
            ->when(
                $origem !== '',
                function ($query) use (
                    $origem
                ) {
                    $query->where(
                        'origem',
                        $origem
                    );
                }
            )
            ->when(
                $dataInicio !== '',
                function ($query) use (
                    $dataInicio
                ) {
                    $query->whereDate(
                        'data_movimentacao',
                        '>=',
                        $dataInicio
                    );
                }
            )
            ->when(
                $dataFim !== '',
                function ($query) use (
                    $dataFim
                ) {
                    $query->whereDate(
                        'data_movimentacao',
                        '<=',
                        $dataFim
                    );
                }
            )
            ->orderByDesc(
                'data_movimentacao'
            )
            ->orderByDesc(
                'id_movimentacao'
            )
            ->paginate(25)
            ->withQueryString();

        return view(
            'movimentacoes.index',
            compact(
                'movimentacoes',
                'busca',
                'tipoItem',
                'tipoMovimentacao',
                'origem',
                'dataInicio',
                'dataFim'
            )
        );
    }

    public function create()
    {
        /*
        |--------------------------------------------------------------------------
        | Produtos disponíveis
        |--------------------------------------------------------------------------
        |
        | Somente variações ativas de produtos ativos podem receber
        | movimentações manuais.
        |
        */

        $variacoes = ProdutoVariacao::with(
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
            ->orderBy('id_produto')
            ->orderBy('tamanho')
            ->orderBy('cor')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Materiais disponíveis
        |--------------------------------------------------------------------------
        */

        $materiais = Material::query()
            ->where(
                'ativo',
                true
            )
            ->orderBy('categoria')
            ->orderBy('nome_material')
            ->orderBy('cor')
            ->get();

        return view(
            'movimentacoes.create',
            compact(
                'variacoes',
                'materiais'
            )
        );
    }

    public function store(
        Request $request
    ) {
        $this->normalizarObservacao(
            $request
        );

        $dados = $request->validate(
            [
                'tipo_item' => [
                    'required',
                    'in:produto,material',
                ],

                'tipo_movimentacao' => [
                    'required',
                    'in:entrada,saida',
                ],

                'observacao' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],
            ],
            [
                'tipo_item.required' =>
                    'Selecione o tipo do item.',

                'tipo_item.in' =>
                    'O tipo do item informado é inválido.',

                'tipo_movimentacao.required' =>
                    'Selecione o tipo da movimentação.',

                'tipo_movimentacao.in' =>
                    'O tipo da movimentação informado é inválido.',

                'observacao.string' =>
                    'A observação deve ser um texto.',

                'observacao.max' =>
                    'A observação pode possuir no máximo 2000 caracteres.',
            ]
        );

        if (
            $dados['tipo_item']
            === 'produto'
        ) {
            $dadosProduto =
                $request->validate(
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
                        ],
                    ],
                    [
                        'id_variacao.required' =>
                            'Selecione uma variação do produto.',

                        'id_variacao.integer' =>
                            'A variação selecionada é inválida.',

                        'id_variacao.exists' =>
                            'A variação selecionada não existe ou está inativa.',

                        'quantidade.required' =>
                            'Informe a quantidade.',

                        'quantidade.integer' =>
                            'A quantidade de produto deve ser um número inteiro.',

                        'quantidade.min' =>
                            'A quantidade deve ser de pelo menos 1 unidade.',
                    ]
                );

            return $this->movimentarProduto(
                $dadosProduto[
                    'id_variacao'
                ],
                $dados[
                    'tipo_movimentacao'
                ],
                (int) $dadosProduto[
                    'quantidade'
                ],
                $dados[
                    'observacao'
                ] ?? null
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Material
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
            return redirect()
                ->route(
                    'movimentacoes.create'
                )
                ->with(
                    'erro',
                    'O material selecionado não está mais disponível.'
                )
                ->withInput();
        }

        $dadosQuantidade =
            $request->validate(
                [
                    'quantidade' => [
                        'required',
                        'numeric',
                        'min:0.001',

                        function (
                            string $attribute,
                            mixed $value,
                            \Closure $fail
                        ) use ($material) {
                            if (
                                !is_numeric($value)
                            ) {
                                return;
                            }

                            $numero =
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
                                if ($numero < 1) {
                                    $fail(
                                        'Para materiais medidos em unidade ou rolo, a quantidade deve ser de pelo menos 1.'
                                    );

                                    return;
                                }

                                if (
                                    abs(
                                        $numero
                                        - round($numero)
                                    ) > 0.0000001
                                ) {
                                    $fail(
                                        'Para materiais medidos em unidade ou rolo, a quantidade deve ser um número inteiro.'
                                    );
                                }

                                return;
                            }

                            if (
                                abs(
                                    $numero
                                    - round(
                                        $numero,
                                        3
                                    )
                                ) > 0.0000001
                            ) {
                                $fail(
                                    'A quantidade pode possuir no máximo 3 casas decimais.'
                                );
                            }
                        },
                    ],
                ],
                [
                    'quantidade.required' =>
                        'Informe a quantidade.',

                    'quantidade.numeric' =>
                        'A quantidade deve ser um número.',

                    'quantidade.min' =>
                        'A quantidade deve ser de pelo menos 0,001.',
                ]
            );

        $quantidade =
            $this->normalizarQuantidadeMaterial(
                $dadosQuantidade[
                    'quantidade'
                ],
                $material->unidade_medida
            );

        return $this->movimentarMaterial(
            $material->id_material,
            $dados[
                'tipo_movimentacao'
            ],
            $quantidade,
            $dados[
                'observacao'
            ] ?? null
        );
    }

    private function movimentarProduto(
        int $idVariacao,
        string $tipo,
        int $quantidade,
        ?string $observacao
    ) {
        $resultado = DB::transaction(
            function () use (
                $idVariacao,
                $tipo,
                $quantidade,
                $observacao
            ) {
                $variacao =
                    ProdutoVariacao::with(
                        'produto'
                    )
                        ->lockForUpdate()
                        ->findOrFail(
                            $idVariacao
                        );

                /*
                |--------------------------------------------------------------------------
                | Proteção contra item inativo
                |--------------------------------------------------------------------------
                */

                if (
                    !$variacao->ativo
                    ||
                    !$variacao->produto
                    ||
                    !$variacao->produto->ativo
                ) {
                    return [
                        'erro' =>
                            'Não é possível movimentar este produto porque ele ou sua variação está inativo.',
                    ];
                }

                if (
                    $tipo === 'saida'
                    &&
                    $variacao->quantidade
                        < $quantidade
                ) {
                    return [
                        'erro' =>
                            'Estoque insuficiente para realizar esta saída. Disponível: '
                            . number_format(
                                $variacao->quantidade,
                                0,
                                ',',
                                '.'
                            )
                            . ' un.',
                    ];
                }

                if ($tipo === 'entrada') {
                    $variacao->quantidade +=
                        $quantidade;
                } else {
                    $variacao->quantidade -=
                        $quantidade;
                }

                $variacao->save();

                Movimentacao::create([
                    'id_variacao' =>
                        $variacao->id_variacao,

                    'id_material' =>
                        null,

                    'id_usuario' =>
                        auth()->id(),

                    'id_ordem' =>
                        null,

                    'id_item_producao' =>
                        null,

                    'tipo_movimentacao' =>
                        $tipo,

                    'origem' =>
                        'manual',

                    'quantidade' =>
                        $quantidade,

                    'observacao' =>
                        $observacao,

                    'data_movimentacao' =>
                        now(),
                ]);

                return [
                    'erro' => null,
                ];
            }
        );

        if ($resultado['erro']) {
            return redirect()
                ->route(
                    'movimentacoes.create'
                )
                ->with(
                    'erro',
                    $resultado['erro']
                )
                ->withInput();
        }

        return redirect()
            ->route(
                'movimentacoes.index'
            )
            ->with(
                'sucesso',
                'Movimentação de produto registrada com sucesso.'
            );
    }

    private function movimentarMaterial(
        int $idMaterial,
        string $tipo,
        float|int $quantidade,
        ?string $observacao
    ) {
        $resultado = DB::transaction(
            function () use (
                $idMaterial,
                $tipo,
                $quantidade,
                $observacao
            ) {
                $material =
                    Material::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $idMaterial
                        );

                if (!$material->ativo) {
                    return [
                        'erro' =>
                            'Não é possível movimentar este material porque ele está inativo.',
                    ];
                }

                $estoqueAtual =
                    (float)
                    $material->quantidade;

                if (
                    $tipo === 'saida'
                    &&
                    $estoqueAtual
                        < $quantidade
                ) {
                    return [
                        'erro' =>
                            'Estoque insuficiente de '
                            . $material->nome_material
                            . '. Disponível: '
                            . $this
                                ->formatarQuantidadeMaterial(
                                    $estoqueAtual,
                                    $material
                                        ->unidade_medida
                                )
                            . '.',
                    ];
                }

                if ($tipo === 'entrada') {
                    $novoEstoque =
                        $estoqueAtual
                        + $quantidade;
                } else {
                    $novoEstoque =
                        $estoqueAtual
                        - $quantidade;
                }

                $material->quantidade =
                    $this
                        ->normalizarQuantidadeMaterial(
                            $novoEstoque,
                            $material
                                ->unidade_medida
                        );

                $material->save();

                Movimentacao::create([
                    'id_variacao' =>
                        null,

                    'id_material' =>
                        $material
                            ->id_material,

                    'id_usuario' =>
                        auth()->id(),

                    'id_ordem' =>
                        null,

                    'id_item_producao' =>
                        null,

                    'tipo_movimentacao' =>
                        $tipo,

                    'origem' =>
                        'manual',

                    'quantidade' =>
                        $quantidade,

                    'observacao' =>
                        $observacao,

                    'data_movimentacao' =>
                        now(),
                ]);

                return [
                    'erro' => null,
                ];
            }
        );

        if ($resultado['erro']) {
            return redirect()
                ->route(
                    'movimentacoes.create'
                )
                ->with(
                    'erro',
                    $resultado['erro']
                )
                ->withInput();
        }

        return redirect()
            ->route(
                'movimentacoes.index'
            )
            ->with(
                'sucesso',
                'Movimentação de material registrada com sucesso.'
            );
    }

    private function normalizarQuantidadeMaterial(
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

    private function formatarQuantidadeMaterial(
        mixed $valor,
        string $unidade
    ): string {
        $numero =
            (float) $valor;

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
    }

    private function normalizarObservacao(
        Request $request
    ): void {
        $observacao =
            $request->input(
                'observacao'
            );

        if (is_string($observacao)) {
            $observacao =
                trim(
                    $observacao
                );

            if ($observacao === '') {
                $observacao = null;
            }
        }

        $request->merge([
            'observacao' =>
                $observacao,
        ]);
    }

    private function normalizarDataFiltro(
        mixed $valor
    ): string {
        if (!is_string($valor)) {
            return '';
        }

        $valor = trim(
            $valor
        );

        if ($valor === '') {
            return '';
        }

        $data =
            \DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $valor
            );

        if (
            !$data
            ||
            $data->format('Y-m-d')
                !== $valor
        ) {
            return '';
        }

        return $valor;
    }
}
