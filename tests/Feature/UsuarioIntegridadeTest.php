<?php

namespace Tests\Feature;

use App\Http\Middleware\AdministradorMiddleware;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuarioIntegridadeTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_nao_pode_excluir_a_propria_conta(): void
    {
        $administrador = User::factory()
            ->administrador()
            ->create();

        $resposta = $this
            ->actingAs($administrador)
            ->delete(
                route(
                    'usuarios.destroy',
                    $administrador
                )
            );

        $resposta
            ->assertRedirect(
                route('usuarios.index')
            )
            ->assertSessionHas(
                'erro',
                'Você não pode excluir sua própria conta.'
            );

        $this->assertDatabaseHas(
            'users',
            [
                'id' =>
                    $administrador->id,

                'nivel_acesso' =>
                    'administrador',
            ]
        );
    }

    public function test_ultimo_administrador_nao_pode_ser_excluido(): void
    {
        $administrador = User::factory()
            ->administrador()
            ->create();

        $funcionario = User::factory()
            ->create();

        /*
         * O middleware normalmente impediria um funcionário
         * de acessar a administração de usuários.
         *
         * Aqui removemos SOMENTE esse middleware porque
         * queremos testar diretamente a regra de negócio
         * existente no Controller:
         *
         * "o último administrador não pode ser excluído".
         */
        $resposta = $this
            ->withoutMiddleware(
                AdministradorMiddleware::class
            )
            ->actingAs($funcionario)
            ->delete(
                route(
                    'usuarios.destroy',
                    $administrador
                )
            );

        $resposta
            ->assertRedirect(
                route('usuarios.index')
            )
            ->assertSessionHas(
                'erro',
                'Não é possível excluir o último administrador do sistema.'
            );

        $this->assertDatabaseHas(
            'users',
            [
                'id' =>
                    $administrador->id,

                'nivel_acesso' =>
                    'administrador',
            ]
        );
    }

    public function test_ultimo_administrador_nao_pode_ser_rebaixado(): void
    {
        $administrador = User::factory()
            ->administrador()
            ->create();

        /*
         * O Controller usa return back().
         *
         * Portanto informamos qual seria a página anterior
         * real do usuário: a edição daquele administrador.
         */
        $resposta = $this
            ->actingAs($administrador)
            ->from(
                route(
                    'usuarios.edit',
                    $administrador
                )
            )
            ->put(
                route(
                    'usuarios.update',
                    $administrador
                ),
                [
                    'name' =>
                        $administrador->name,

                    'email' =>
                        $administrador->email,

                    'password' =>
                        '',

                    'password_confirmation' =>
                        '',

                    'nivel_acesso' =>
                        'funcionario',
                ]
            );

        $resposta
            ->assertRedirect(
                route(
                    'usuarios.edit',
                    $administrador
                )
            )
            ->assertSessionHas(
                'erro',
                'Não é possível rebaixar o último administrador do sistema.'
            );

        $this->assertDatabaseHas(
            'users',
            [
                'id' =>
                    $administrador->id,

                'nivel_acesso' =>
                    'administrador',
            ]
        );
    }

    public function test_administrador_pode_ser_rebaixado_quando_existe_outro_administrador(): void
    {
        $administrador1 = User::factory()
            ->administrador()
            ->create();

        User::factory()
            ->administrador()
            ->create();

        $resposta = $this
            ->actingAs($administrador1)
            ->put(
                route(
                    'usuarios.update',
                    $administrador1
                ),
                [
                    'name' =>
                        $administrador1->name,

                    'email' =>
                        $administrador1->email,

                    'password' =>
                        '',

                    'password_confirmation' =>
                        '',

                    'nivel_acesso' =>
                        'funcionario',
                ]
            );

        $resposta->assertRedirect(
            route('usuarios.index')
        );

        $this->assertDatabaseHas(
            'users',
            [
                'id' =>
                    $administrador1->id,

                'nivel_acesso' =>
                    'funcionario',
            ]
        );
    }
}
