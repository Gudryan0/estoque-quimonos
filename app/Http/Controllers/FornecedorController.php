<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use Illuminate\Http\Request;

class FornecedorController extends Controller
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

        $fornecedores = Fornecedor::query()
            ->withCount('materiais')
            ->when(
                $busca !== '',
                function ($query) use ($busca) {
                    $query->where(
                        function ($query) use ($busca) {
                            $query
                                ->where(
                                    'nome_fornecedor',
                                    'like',
                                    "%{$busca}%"
                                )
                                ->orWhere(
                                    'telefone',
                                    'like',
                                    "%{$busca}%"
                                )
                                ->orWhere(
                                    'endereco',
                                    'like',
                                    "%{$busca}%"
                                );
                        }
                    );
                }
            )
            ->orderBy(
                'nome_fornecedor'
            )
            ->get();

        return view(
            'fornecedores.index',
            compact(
                'fornecedores',
                'busca'
            )
        );
    }

    public function create()
    {
        return view(
            'fornecedores.create'
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

        Fornecedor::create(
            $dados
        );

        return redirect()
            ->route(
                'fornecedores.index'
            )
            ->with(
                'sucesso',
                'Fornecedor cadastrado com sucesso.'
            );
    }

    public function edit(
        Fornecedor $fornecedor
    ) {
        return view(
            'fornecedores.edit',
            compact('fornecedor')
        );
    }

    public function update(
        Request $request,
        Fornecedor $fornecedor
    ) {
        $this->normalizarDados(
            $request
        );

        $dados = $this->validarDados(
            $request
        );

        $fornecedor->update(
            $dados
        );

        return redirect()
            ->route(
                'fornecedores.index'
            )
            ->with(
                'sucesso',
                'Fornecedor atualizado com sucesso.'
            );
    }

    public function destroy(
        Fornecedor $fornecedor
    ) {
        if (
            $fornecedor
                ->materiais()
                ->exists()
        ) {
            return redirect()
                ->route(
                    'fornecedores.index'
                )
                ->with(
                    'erro',
                    'Não é possível excluir este fornecedor porque ele está vinculado a um ou mais materiais.'
                );
        }

        $fornecedor->delete();

        return redirect()
            ->route(
                'fornecedores.index'
            )
            ->with(
                'sucesso',
                'Fornecedor excluído com sucesso.'
            );
    }

    private function validarDados(
        Request $request
    ): array {
        return $request->validate(
            [
                'nome_fornecedor' => [
                    'required',
                    'string',
                    'min:2',
                    'max:120',
                    'regex:/[A-Za-zÀ-ÖØ-öø-ÿ]/u',
                ],

                'telefone' => [
                    'nullable',
                    'string',
                    'max:30',
                    'regex:/^[0-9\s()+\-.]+$/',

                    function (
                        string $attribute,
                        mixed $value,
                        \Closure $fail
                    ) {
                        if ($value === null) {
                            return;
                        }

                        $digitos = preg_replace(
                            '/\D/',
                            '',
                            $value
                        );

                        $quantidadeDigitos =
                            strlen($digitos);

                        if (
                            $quantidadeDigitos < 8
                            ||
                            $quantidadeDigitos > 15
                        ) {
                            $fail(
                                'O telefone deve possuir entre 8 e 15 dígitos.'
                            );
                        }
                    },
                ],

                'endereco' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
            ],
            [
                'nome_fornecedor.required' =>
                    'Informe o nome do fornecedor.',

                'nome_fornecedor.string' =>
                    'O nome do fornecedor deve ser um texto.',

                'nome_fornecedor.min' =>
                    'O nome do fornecedor deve possuir pelo menos 2 caracteres.',

                'nome_fornecedor.max' =>
                    'O nome do fornecedor pode possuir no máximo 120 caracteres.',

                'nome_fornecedor.regex' =>
                    'O nome do fornecedor deve conter pelo menos uma letra.',

                'telefone.string' =>
                    'O telefone informado é inválido.',

                'telefone.max' =>
                    'O telefone informado é muito longo.',

                'telefone.regex' =>
                    'O telefone deve conter apenas números, espaços, parênteses, ponto, hífen ou sinal de mais.',

                'endereco.string' =>
                    'O endereço deve ser um texto.',

                'endereco.max' =>
                    'O endereço pode possuir no máximo 255 caracteres.',
            ]
        );
    }

    private function normalizarDados(
        Request $request
    ): void {
        $nome = $request->input(
            'nome_fornecedor'
        );

        $telefone = $request->input(
            'telefone'
        );

        $endereco = $request->input(
            'endereco'
        );

        if (is_string($nome)) {
            $nome = trim($nome);
        }

        if (is_string($telefone)) {
            $telefone = trim(
                $telefone
            );

            if ($telefone === '') {
                $telefone = null;
            }
        }

        if (is_string($endereco)) {
            $endereco = trim(
                $endereco
            );

            if ($endereco === '') {
                $endereco = null;
            }
        }

        $request->merge([
            'nome_fornecedor' => $nome,
            'telefone' => $telefone,
            'endereco' => $endereco,
        ]);
    }
}
