<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy(
            'name'
        )->get();

        return view(
            'usuarios.index',
            compact('usuarios')
        );
    }

    public function create()
    {
        return view(
            'usuarios.create'
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

        User::create($dados);

        return redirect()
            ->route('usuarios.index')
            ->with(
                'sucesso',
                'Usuário cadastrado com sucesso.'
            );
    }

    public function edit(
        User $usuario
    ) {
        return view(
            'usuarios.edit',
            compact('usuario')
        );
    }

    public function update(
        Request $request,
        User $usuario
    ) {
        $this->normalizarDados(
            $request
        );

        $dados = $this->validarDados(
            $request,
            $usuario
        );

        if (
            $usuario->nivel_acesso
                === 'administrador'
            &&
            $dados['nivel_acesso']
                === 'funcionario'
            &&
            $this->quantidadeAdministradores()
                <= 1
        ) {
            return back()
                ->withInput()
                ->with(
                    'erro',
                    'Não é possível rebaixar o último administrador do sistema.'
                );
        }

        if (
            empty(
                $dados['password']
            )
        ) {
            unset(
                $dados['password']
            );
        }

        $usuario->update(
            $dados
        );

        return redirect()
            ->route('usuarios.index')
            ->with(
                'sucesso',
                'Usuário atualizado com sucesso.'
            );
    }

    public function destroy(
        User $usuario
    ) {
        if (
            $usuario->id
            === auth()->id()
        ) {
            return redirect()
                ->route('usuarios.index')
                ->with(
                    'erro',
                    'Você não pode excluir sua própria conta.'
                );
        }

        if (
            $usuario->nivel_acesso
                === 'administrador'
            &&
            $this->quantidadeAdministradores()
                <= 1
        ) {
            return redirect()
                ->route('usuarios.index')
                ->with(
                    'erro',
                    'Não é possível excluir o último administrador do sistema.'
                );
        }

        if (
            $usuario
                ->movimentacoes()
                ->exists()
        ) {
            return redirect()
                ->route('usuarios.index')
                ->with(
                    'erro',
                    'Não é possível excluir este usuário porque ele possui movimentações de estoque registradas.'
                );
        }

        if (
            $usuario
                ->ordensCriadas()
                ->exists()
        ) {
            return redirect()
                ->route('usuarios.index')
                ->with(
                    'erro',
                    'Não é possível excluir este usuário porque ele é responsável pela criação de uma ou mais ordens de produção.'
                );
        }

        if (
            $usuario
                ->historicosProducao()
                ->exists()
        ) {
            return redirect()
                ->route('usuarios.index')
                ->with(
                    'erro',
                    'Não é possível excluir este usuário porque ele possui ações registradas no histórico de produção.'
                );
        }

        if (
            $usuario
                ->ordensCanceladas()
                ->exists()
        ) {
            return redirect()
                ->route('usuarios.index')
                ->with(
                    'erro',
                    'Não é possível excluir este usuário porque ele é responsável pelo cancelamento de uma ou mais ordens de produção.'
                );
        }

        $usuario->delete();

        return redirect()
            ->route('usuarios.index')
            ->with(
                'sucesso',
                'Usuário excluído com sucesso.'
            );
    }

    private function validarDados(
        Request $request,
        ?User $usuario = null
    ): array {
        $regraEmail = Rule::unique(
            'users',
            'email'
        );

        if ($usuario !== null) {
            $regraEmail->ignore(
                $usuario->id
            );
        }

        return $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'min:2',
                    'max:120',
                    'regex:/[A-Za-zÀ-ÖØ-öø-ÿ]/u',
                ],

                'email' => [
                    'required',
                    'email:rfc',
                    'max:255',
                    $regraEmail,
                ],

                'password' => [
                    $usuario === null
                        ? 'required'
                        : 'nullable',
                    'string',
                    'min:8',
                    'max:255',
                    'confirmed',
                ],

                'nivel_acesso' => [
                    'required',
                    Rule::in([
                        'administrador',
                        'funcionario',
                    ]),
                ],
            ],
            [
                'name.required' =>
                    'Informe o nome do usuário.',

                'name.string' =>
                    'O nome do usuário deve ser um texto.',

                'name.min' =>
                    'O nome do usuário deve possuir pelo menos 2 caracteres.',

                'name.max' =>
                    'O nome do usuário pode possuir no máximo 120 caracteres.',

                'name.regex' =>
                    'O nome do usuário deve conter pelo menos uma letra.',

                'email.required' =>
                    'Informe o e-mail do usuário.',

                'email.email' =>
                    'Informe um endereço de e-mail válido.',

                'email.max' =>
                    'O e-mail pode possuir no máximo 255 caracteres.',

                'email.unique' =>
                    'Este e-mail já está sendo utilizado por outro usuário.',

                'password.required' =>
                    'Informe uma senha.',

                'password.string' =>
                    'A senha informada é inválida.',

                'password.min' =>
                    'A senha deve possuir pelo menos 8 caracteres.',

                'password.max' =>
                    'A senha pode possuir no máximo 255 caracteres.',

                'password.confirmed' =>
                    'A confirmação da senha não corresponde à senha informada.',

                'nivel_acesso.required' =>
                    'Selecione o nível de acesso.',

                'nivel_acesso.in' =>
                    'O nível de acesso selecionado é inválido.',
            ]
        );
    }

    private function normalizarDados(
        Request $request
    ): void {
        $nome = $request->input(
            'name'
        );

        $email = $request->input(
            'email'
        );

        $nivelAcesso =
            $request->input(
                'nivel_acesso'
            );

        if (is_string($nome)) {
            $nome = preg_replace(
                '/\s+/',
                ' ',
                trim($nome)
            );
        }

        if (is_string($email)) {
            $email = strtolower(
                trim($email)
            );
        }

        if (
            is_string(
                $nivelAcesso
            )
        ) {
            $nivelAcesso =
                strtolower(
                    trim(
                        $nivelAcesso
                    )
                );
        }

        $request->merge([
            'name' => $nome,
            'email' => $email,
            'nivel_acesso' =>
                $nivelAcesso,
        ]);
    }

    private function quantidadeAdministradores(): int
    {
        return User::where(
            'nivel_acesso',
            'administrador'
        )->count();
    }
}
