<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutenticacaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_e_redirecionado_para_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(
            route('login')
        );
    }

    public function test_visitante_nao_pode_acessar_dashboard(): void
    {
        $response = $this->get(
            route('dashboard')
        );

        $response->assertRedirect(
            route('login')
        );
    }

    public function test_usuario_ativo_pode_fazer_login(): void
    {
        $usuario = User::factory()
            ->create();

        $response = $this->post(
            route('login.autenticar'),
            [
                'email' =>
                    $usuario->email,

                'password' =>
                    'password',
            ]
        );

        $response->assertRedirect(
            route('dashboard')
        );

        $this->assertAuthenticatedAs(
            $usuario
        );
    }

    public function test_usuario_inativo_nao_pode_fazer_login(): void
    {
        $usuario = User::factory()
            ->create([
                'ativo' =>
                    false,
            ]);

        $response = $this
            ->from(
                route('login')
            )
            ->post(
                route('login.autenticar'),
                [
                    'email' =>
                        $usuario->email,

                    'password' =>
                        'password',
                ]
            );

        $response
            ->assertRedirect(
                route('login')
            )
            ->assertSessionHasErrors([
                'email' =>
                    'E-mail ou senha inválidos.',
            ]);

        $this->assertGuest();
    }

    public function test_usuario_inativado_com_sessao_aberta_e_desconectado(): void
    {
        $usuario = User::factory()
            ->create();

        $this->actingAs(
            $usuario
        );

        /*
         * Simula a situação em que o usuário
         * já estava conectado quando um
         * administrador inativou sua conta.
         */
        $usuario->update([
            'ativo' =>
                false,
        ]);

        $response = $this->get(
            route('dashboard')
        );

        $response
            ->assertRedirect(
                route('login')
            )
            ->assertSessionHasErrors([
                'email' =>
                    'Sua conta está inativa. Procure um administrador.',
            ]);

        $this->assertGuest();
    }
}
