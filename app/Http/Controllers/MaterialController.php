<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MaterialController extends Controller
{
    public function index(
        Request $request
    ) {
        $status =
            $request->query('status') === 'inativos'
                ? 'inativos'
                : 'ativos';

        $busca = $request->query(
            'q',
            ''
        );

        $busca = is_string($busca)
            ? trim($busca)
            : '';

        $categorias = $this->categorias();

        $categoria = $request->query(
            'categoria',
            ''
        );

        $categoria = is_string($categoria)
            ? $categoria
            : '';

        if (
            !array_key_exists(
                $categoria,
                $categorias
            )
        ) {
            $categoria = '';
        }

        $situacao = $request->query(
            'situacao',
            ''
        );

        $situacao = is_string($situacao)
            ? $situacao
            : '';

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

        $materiais = Material::query()
            ->with('fornecedor')
            ->withCount([
                'movimentacoes',
                'composicoes',
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
                                    'nome_material',
                                    'like',
                                    "%{$busca}%"
                                )
                                ->orWhere(
                                    'tipo_material',
                                    'like',
                                    "%{$busca}%"
                                )
                                ->orWhere(
                                    'cor',
                                    'like',
                                    "%{$busca}%"
                                )
                                ->orWhereHas(
                                    'fornecedor',
                                    function ($query) use (
                                        $busca
                                    ) {
                                        $query->where(
                                            'nome_fornecedor',
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
                $categoria !== '',
                function ($query) use ($categoria) {
                    $query->where(
                        'categoria',
                        $categoria
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
                        ->where(
                            'estoque_minimo',
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
                        ->where(function ($query) {
                            $query
                                ->where(
                                    'estoque_minimo',
                                    '<=',
                                    0
                                )
                                ->orWhereColumn(
                                    'quantidade',
                                    '>',
                                    'estoque_minimo'
                                );
                        });
                }
            )
            ->orderBy('nome_material')
            ->orderBy('cor')
            ->get();

        return view(
            'materiais.index',
            compact(
                'materiais',
                'status',
                'busca',
                'categoria',
                'situacao',
                'categorias'
            )
        );
    }

    public function create()
    {
        $fornecedores =
            Fornecedor::orderBy(
                'nome_fornecedor'
            )->get();

        $categorias =
            $this->categorias();

        $unidades =
            $this->unidades();

        return view(
            'materiais.create',
            compact(
                'fornecedores',
                'categorias',
                'unidades'
            )
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

        Material::create([
            'nome_material' =>
                $dados['nome_material'],

            'categoria' =>
                $dados['categoria'],

            'tipo_material' =>
                $dados['tipo_material']
                ?? null,

            'cor' =>
                $dados['cor']
                ?? null,

            'unidade_medida' =>
                $dados['unidade_medida'],

            'quantidade' => 0,

            'estoque_minimo' =>
                $this->normalizarEstoqueMinimo(
                    $dados['estoque_minimo'],
                    $dados['unidade_medida']
                ),

            'id_fornecedor' =>
                $dados['id_fornecedor']
                ?? null,
        ]);

        return redirect()
            ->route(
                'materiais.index'
            )
            ->with(
                'sucesso',
                'Material cadastrado com sucesso.'
            );
    }

    public function edit(
        Material $material
    ) {
        $fornecedores =
            Fornecedor::orderBy(
                'nome_fornecedor'
            )->get();

        $categorias =
            $this->categorias();

        $unidades =
            $this->unidades();

        $unidadeBloqueada =
            $this->unidadeBloqueada(
                $material
            );

        return view(
            'materiais.edit',
            compact(
                'material',
                'fornecedores',
                'categorias',
                'unidades',
                'unidadeBloqueada'
            )
        );
    }

    public function update(
        Request $request,
        Material $material
    ) {
        $this->normalizarDados(
            $request
        );

        $dados = $this->validarDados(
            $request
        );

        $unidadeFoiAlterada =
            $material->unidade_medida
            !== $dados['unidade_medida'];

        if ($unidadeFoiAlterada) {
            if (
                $this->unidadeBloqueada(
                    $material
                )
            ) {
                return back()
                    ->withInput()
                    ->with(
                        'erro',
                        'Não é possível alterar a unidade de medida deste material porque ele já possui estoque, movimentações ou ficha de consumo vinculada.'
                    );
            }
        }

        $material->update([
            'nome_material' =>
                $dados['nome_material'],

            'categoria' =>
                $dados['categoria'],

            'tipo_material' =>
                $dados['tipo_material']
                ?? null,

            'cor' =>
                $dados['cor']
                ?? null,

            'unidade_medida' =>
                $dados['unidade_medida'],

            'estoque_minimo' =>
                $this->normalizarEstoqueMinimo(
                    $dados['estoque_minimo'],
                    $dados['unidade_medida']
                ),

            'id_fornecedor' =>
                $dados['id_fornecedor']
                ?? null,
        ]);

        return redirect()
            ->route(
                'materiais.index',
                [
                    'status' =>
                        $material->ativo
                            ? 'ativos'
                            : 'inativos',
                ]
            )
            ->with(
                'sucesso',
                'Material atualizado com sucesso.'
            );
    }

    public function inativar(
        Material $material
    ) {
        if (!$material->ativo) {
            return redirect()
                ->route(
                    'materiais.index',
                    [
                        'status' => 'inativos',
                    ]
                )
                ->with(
                    'erro',
                    'Este material já está inativo.'
                );
        }

        $material->ativo = false;
        $material->save();

        return redirect()
            ->route(
                'materiais.index'
            )
            ->with(
                'sucesso',
                'Material inativado com sucesso.'
            );
    }

    public function reativar(
        Material $material
    ) {
        if ($material->ativo) {
            return redirect()
                ->route(
                    'materiais.index'
                )
                ->with(
                    'erro',
                    'Este material já está ativo.'
                );
        }

        $material->ativo = true;
        $material->save();

        return redirect()
            ->route(
                'materiais.index',
                [
                    'status' => 'inativos',
                ]
            )
            ->with(
                'sucesso',
                'Material reativado com sucesso.'
            );
    }

    public function destroy(
        Material $material
    ) {
        if (
            (float) $material->quantidade > 0
        ) {
            return redirect()
                ->route(
                    'materiais.index',
                    [
                        'status' =>
                            $material->ativo
                                ? 'ativos'
                                : 'inativos',
                    ]
                )
                ->with(
                    'erro',
                    'Não é possível excluir este material porque ele possui estoque disponível. Caso não seja mais utilizado, inative-o.'
                );
        }

        if (
            $material
                ->movimentacoes()
                ->exists()
        ) {
            return redirect()
                ->route(
                    'materiais.index',
                    [
                        'status' =>
                            $material->ativo
                                ? 'ativos'
                                : 'inativos',
                    ]
                )
                ->with(
                    'erro',
                    'Não é possível excluir este material porque ele possui movimentações registradas. Caso não seja mais utilizado, inative-o.'
                );
        }

        if (
            $material
                ->composicoes()
                ->exists()
        ) {
            return redirect()
                ->route(
                    'materiais.index',
                    [
                        'status' =>
                            $material->ativo
                                ? 'ativos'
                                : 'inativos',
                    ]
                )
                ->with(
                    'erro',
                    'Não é possível excluir este material porque ele é utilizado em uma ficha de consumo. Caso não seja mais utilizado, inative-o.'
                );
        }

        $material->delete();

        return redirect()
            ->route(
                'materiais.index',
                [
                    'status' =>
                        $material->ativo
                            ? 'ativos'
                            : 'inativos',
                ]
            )
            ->with(
                'sucesso',
                'Material excluído definitivamente.'
            );
    }

    private function validarDados(
        Request $request
    ): array {
        return $request->validate(
            [
                'nome_material' => [
                    'required',
                    'string',
                    'min:2',
                    'max:120',
                    'regex:/[A-Za-zÀ-ÖØ-öø-ÿ]/u',
                ],

                'categoria' => [
                    'required',
                    Rule::in(
                        array_keys(
                            $this->categorias()
                        )
                    ),
                ],

                'tipo_material' => [
                    'nullable',
                    'string',
                    'max:80',
                    'regex:/[A-Za-zÀ-ÖØ-öø-ÿ]/u',
                ],

                'cor' => [
                    'nullable',
                    'string',
                    'max:60',
                    'regex:/[A-Za-zÀ-ÖØ-öø-ÿ]/u',
                ],

                'unidade_medida' => [
                    'required',
                    Rule::in(
                        array_keys(
                            $this->unidades()
                        )
                    ),
                ],

                'estoque_minimo' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:999999999.999',

                    function (
                        string $attribute,
                        mixed $value,
                        \Closure $fail
                    ) use ($request) {
                        if (!is_numeric($value)) {
                            return;
                        }

                        $valor =
                            (string) $value;

                        if (
                            str_contains(
                                strtolower($valor),
                                'e'
                            )
                        ) {
                            $fail(
                                'O estoque mínimo deve ser informado em formato decimal comum.'
                            );

                            return;
                        }

                        $unidade =
                            $request->input(
                                'unidade_medida'
                            );

                        $numero =
                            (float) $value;

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
                            if (
                                abs(
                                    $numero
                                    - round($numero)
                                ) > 0.0000001
                            ) {
                                $fail(
                                    'O estoque mínimo deve ser um número inteiro para materiais medidos em unidade ou rolo.'
                                );
                            }

                            return;
                        }

                        $partes = explode(
                            '.',
                            $valor
                        );

                        if (
                            count($partes) > 1
                            &&
                            strlen($partes[1]) > 3
                        ) {
                            $fail(
                                'O estoque mínimo pode possuir no máximo 3 casas decimais.'
                            );
                        }
                    },
                ],

                'id_fornecedor' => [
                    'nullable',
                    'integer',
                    'exists:fornecedores,id_fornecedor',
                ],
            ],
            [
                'nome_material.required' =>
                    'Informe o nome do material.',

                'nome_material.string' =>
                    'O nome do material deve ser um texto.',

                'nome_material.min' =>
                    'O nome do material deve possuir pelo menos 2 caracteres.',

                'nome_material.max' =>
                    'O nome do material pode possuir no máximo 120 caracteres.',

                'nome_material.regex' =>
                    'O nome do material deve conter pelo menos uma letra.',

                'categoria.required' =>
                    'Informe a categoria do material.',

                'categoria.in' =>
                    'A categoria selecionada é inválida.',

                'tipo_material.string' =>
                    'O tipo do material deve ser um texto.',

                'tipo_material.max' =>
                    'O tipo do material pode possuir no máximo 80 caracteres.',

                'tipo_material.regex' =>
                    'O tipo do material deve conter pelo menos uma letra.',

                'cor.string' =>
                    'A cor deve ser um texto.',

                'cor.max' =>
                    'A cor pode possuir no máximo 60 caracteres.',

                'cor.regex' =>
                    'A cor deve conter pelo menos uma letra.',

                'unidade_medida.required' =>
                    'Informe a unidade de medida.',

                'unidade_medida.in' =>
                    'A unidade de medida selecionada é inválida.',

                'estoque_minimo.required' =>
                    'Informe o estoque mínimo.',

                'estoque_minimo.numeric' =>
                    'O estoque mínimo deve ser um número.',

                'estoque_minimo.min' =>
                    'O estoque mínimo não pode ser negativo.',

                'estoque_minimo.max' =>
                    'O estoque mínimo informado é muito alto.',

                'id_fornecedor.integer' =>
                    'O fornecedor selecionado é inválido.',

                'id_fornecedor.exists' =>
                    'O fornecedor selecionado não existe.',
            ]
        );
    }

    private function normalizarDados(
        Request $request
    ): void {
        $nome = $request->input(
            'nome_material'
        );

        $categoria = $request->input(
            'categoria'
        );

        $tipo = $request->input(
            'tipo_material'
        );

        $cor = $request->input(
            'cor'
        );

        $unidade = $request->input(
            'unidade_medida'
        );

        if (is_string($nome)) {
            $nome = preg_replace(
                '/\s+/',
                ' ',
                trim($nome)
            );
        }

        if (is_string($categoria)) {
            $categoria = strtolower(
                trim($categoria)
            );
        }

        if (is_string($tipo)) {
            $tipo = preg_replace(
                '/\s+/',
                ' ',
                trim($tipo)
            );

            if ($tipo === '') {
                $tipo = null;
            }
        }

        if (is_string($cor)) {
            $cor = preg_replace(
                '/\s+/',
                ' ',
                trim($cor)
            );

            if ($cor === '') {
                $cor = null;
            }
        }

        if (is_string($unidade)) {
            $unidade = strtolower(
                trim($unidade)
            );
        }

        $request->merge([
            'nome_material' => $nome,
            'categoria' => $categoria,
            'tipo_material' => $tipo,
            'cor' => $cor,
            'unidade_medida' => $unidade,
        ]);
    }

    private function categorias(): array
    {
        return [
            'tecido' => 'Tecido',
            'eva' => 'EVA',
            'fita' => 'Fita',
            'linha' => 'Linha',
            'etiqueta' => 'Etiqueta',
            'outro' => 'Outro',
        ];
    }

    private function unidades(): array
    {
        return [
            'm' => 'Metro (m)',
            'un' => 'Unidade (un.)',
            'rolo' => 'Rolo',
            'kg' => 'Quilograma (kg)',
        ];
    }

    private function unidadeBloqueada(
        Material $material
    ): bool {
        return
            (float) $material->quantidade > 0
            ||
            $material
                ->movimentacoes()
                ->exists()
            ||
            $material
                ->composicoes()
                ->exists();
    }

    private function normalizarEstoqueMinimo(
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
