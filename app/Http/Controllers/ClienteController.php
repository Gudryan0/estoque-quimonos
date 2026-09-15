<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\OrdemProducao;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
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

        $clientes = Cliente::query()
            ->select('clientes.*')
            ->selectSub(
                OrdemProducao::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn(
                        'ordens_producao.id_cliente',
                        'clientes.id_cliente'
                    ),
                'ordens_producao_count'
            )
            ->when(
                $busca !== '',
                function ($query) use ($busca) {
                    $query->where(
                        function ($query) use ($busca) {
                            $query
                                ->where(
                                    'nome_cliente',
                                    'like',
                                    "%{$busca}%"
                                )
                                ->orWhere(
                                    'telefone',
                                    'like',
                                    "%{$busca}%"
                                )
                                ->orWhere(
                                    'email',
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
                'nome_cliente'
            )
            ->get();

        return view(
            'clientes.index',
            compact(
                'clientes',
                'busca'
            )
        );
    }

    public function create()
    {
        return view(
            'clientes.create'
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

        Cliente::create(
            $dados
        );

        return redirect()
            ->route(
                'clientes.index'
            )
            ->with(
                'sucesso',
                'Cliente cadastrado com sucesso.'
            );
    }

    public function edit(
        Cliente $cliente
    ) {
        return view(
            'clientes.edit',
            compact('cliente')
        );
    }

    public function update(
        Request $request,
        Cliente $cliente
    ) {
        $this->normalizarDados(
            $request
        );

        $dados = $this->validarDados(
            $request,
            $cliente
        );

        $cliente->update(
            $dados
        );

        return redirect()
            ->route(
                'clientes.index'
            )
            ->with(
                'sucesso',
                'Cliente atualizado com sucesso.'
            );
    }

    public function destroy(
        Cliente $cliente
    ) {
        $possuiOrdemProducao =
            OrdemProducao::where(
                'id_cliente',
                $cliente->id_cliente
            )->exists();

        if ($possuiOrdemProducao) {
            return redirect()
                ->route(
                    'clientes.index'
                )
                ->with(
                    'erro',
                    'Não é possível excluir este cliente porque ele está vinculado a uma ou mais ordens de produção.'
                );
        }

        $cliente->delete();

        return redirect()
            ->route(
                'clientes.index'
            )
            ->with(
                'sucesso',
                'Cliente excluído com sucesso.'
            );
    }

    private function validarDados(
        Request $request,
        ?Cliente $cliente = null
    ): array {
        $regraEmail = Rule::unique(
            'clientes',
            'email'
        );

        if ($cliente !== null) {
            $regraEmail->ignore(
                $cliente->id_cliente,
                'id_cliente'
            );
        }

        return $request->validate(
            [
                'nome_cliente' => [
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

                'email' => [
                    'nullable',
                    'email:rfc',
                    'max:255',
                    $regraEmail,
                ],

                'endereco' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
            ],
            [
                'nome_cliente.required' =>
                    'Informe o nome do cliente.',

                'nome_cliente.string' =>
                    'O nome do cliente deve ser um texto.',

                'nome_cliente.min' =>
                    'O nome do cliente deve possuir pelo menos 2 caracteres.',

                'nome_cliente.max' =>
                    'O nome do cliente pode possuir no máximo 120 caracteres.',

                'nome_cliente.regex' =>
                    'O nome do cliente deve conter pelo menos uma letra.',

                'telefone.string' =>
                    'O telefone informado é inválido.',

                'telefone.max' =>
                    'O telefone informado é muito longo.',

                'telefone.regex' =>
                    'O telefone deve conter apenas números, espaços, parênteses, ponto, hífen ou sinal de mais.',

                'email.email' =>
                    'Informe um endereço de e-mail válido.',

                'email.max' =>
                    'O e-mail pode possuir no máximo 255 caracteres.',

                'email.unique' =>
                    'Este e-mail já está cadastrado para outro cliente.',

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
            'nome_cliente'
        );

        $telefone = $request->input(
            'telefone'
        );

        $email = $request->input(
            'email'
        );

        $endereco = $request->input(
            'endereco'
        );

        if (is_string($nome)) {
            $nome = preg_replace(
                '/\s+/',
                ' ',
                trim($nome)
            );
        }

        if (is_string($telefone)) {
            $telefone = trim(
                $telefone
            );

            if ($telefone === '') {
                $telefone = null;
            }
        }

        if (is_string($email)) {
            $email = strtolower(
                trim($email)
            );

            if ($email === '') {
                $email = null;
            }
        }

        if (is_string($endereco)) {
            $endereco = preg_replace(
                '/\s+/',
                ' ',
                trim($endereco)
            );

            if ($endereco === '') {
                $endereco = null;
            }
        }

        $request->merge([
            'nome_cliente' => $nome,
            'telefone' => $telefone,
            'email' => $email,
            'endereco' => $endereco,
        ]);
    }
}
