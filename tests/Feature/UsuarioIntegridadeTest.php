<?php

namespace Tests\Feature;

use App\Http\Middleware\AdministradorMiddleware;
use App\Models\Movimentacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

                'ativo' =>
                    true,
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
         * "o último administrador ativo não pode ser excluído".
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
                'Não é possível excluir o último administrador ativo do sistema.'
            );

        $this->assertDatabaseHas(
            'users',
            [
                'id' =>
                    $administrador->id,

                'nivel_acesso' =>
                    'administrador',

                'ativo' =>
                    true,
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
                'Não é possível rebaixar o último administrador ativo do sistema.'
            );

        $this->assertDatabaseHas(
            'users',
            [
                'id' =>
                    $administrador->id,

                'nivel_acesso' =>
                    'administrador',

                'ativo' =>
                    true,
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

                'ativo' =>
                    true,
            ]
        );
    }

    public function test_usuario_nao_pode_inativar_a_propria_conta(): void
    {
        $administrador = User::factory()
            ->administrador()
            ->create();

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
                        'administrador',

                    'ativo' =>
                        '0',
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
                'Você não pode inativar sua própria conta.'
            );

        $this->assertDatabaseHas(
            'users',
            [
                'id' =>
                    $administrador->id,

                'nivel_acesso' =>
                    'administrador',

                'ativo' =>
                    true,
            ]
        );
    }

    public function test_ultimo_administrador_ativo_nao_pode_ser_inativado(): void
    {
        $administrador = User::factory()
            ->administrador()
            ->create();

        $funcionario = User::factory()
            ->create();

        /*
         * Assim como no teste de exclusão,
         * removemos apenas o middleware
         * administrativo para alcançar
         * diretamente a regra do Controller.
         */
        $resposta = $this
            ->withoutMiddleware(
                AdministradorMiddleware::class
            )
            ->actingAs($funcionario)
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
                        'administrador',

                    'ativo' =>
                        '0',
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
                'Não é possível inativar o último administrador ativo do sistema.'
            );

        $this->assertDatabaseHas(
            'users',
            [
                'id' =>
                    $administrador->id,

                'nivel_acesso' =>
                    'administrador',

                'ativo' =>
                    true,
            ]
        );
    }

    public function test_funcionario_pode_ser_inativado_e_reativado(): void
    {
        $administrador = User::factory()
            ->administrador()
            ->create();

        $funcionario = User::factory()
            ->create();

        $respostaInativacao = $this
            ->actingAs($administrador)
            ->put(
                route(
                    'usuarios.update',
                    $funcionario
                ),
                [
                    'name' =>
                        $funcionario->name,

                    'email' =>
                        $funcionario->email,

                    'password' =>
                        '',

                    'password_confirmation' =>
                        '',

                    'nivel_acesso' =>
                        'funcionario',

                    'ativo' =>
                        '0',
                ]
            );

        $respostaInativacao->assertRedirect(
            route('usuarios.index')
        );

        $this->assertDatabaseHas(
            'users',
            [
                'id' =>
                    $funcionario->id,

                'nivel_acesso' =>
                    'funcionario',

                'ativo' =>
                    false,
            ]
        );

        $respostaReativacao = $this
            ->actingAs($administrador)
            ->put(
                route(
                    'usuarios.update',
                    $funcionario
                ),
                [
                    'name' =>
                        $funcionario->name,

                    'email' =>
                        $funcionario->email,

                    'password' =>
                        '',

                    'password_confirmation' =>
                        '',

                    'nivel_acesso' =>
                        'funcionario',

                    'ativo' =>
                        '1',
                ]
            );

        $respostaReativacao->assertRedirect(
            route('usuarios.index')
        );

        $this->assertDatabaseHas(
            'users',
            [
                'id' =>
                    $funcionario->id,

                'nivel_acesso' =>
                    'funcionario',

                'ativo' =>
                    true,
            ]
        );
    }

    public function test_funcionario_nao_pode_acessar_administracao_de_usuarios(): void
    {
        $funcionario = User::factory()
            ->create();

        $resposta = $this
            ->actingAs($funcionario)
            ->get(
                route('usuarios.index')
            );

        $resposta->assertRedirect(
            route('dashboard')
        );
    }

    public function test_funcionario_nao_pode_acessar_relatorios(): void
    {
        $funcionario = User::factory()
            ->create();

        $resposta = $this
            ->actingAs($funcionario)
            ->get(
                route('relatorios.index')
            );

        $resposta->assertRedirect(
            route('dashboard')
        );
    }

    public function test_usuario_com_movimentacao_nao_pode_ser_excluido(): void
    {
        $administrador = User::factory()
            ->administrador()
            ->create();

        $usuario = User::factory()
            ->create();

        $idMaterial = DB::table(
            'materiais'
        )->insertGetId([
            'nome_material' =>
                'Material de Teste',

            'categoria' =>
                'outro',

            'tipo_material' =>
                null,

            'cor' =>
                null,

            'unidade_medida' =>
                'un',

            'quantidade' =>
                1,

            'estoque_minimo' =>
                0,

            'id_fornecedor' =>
                null,

            'ativo' =>
                true,

            'created_at' =>
                now(),

            'updated_at' =>
                now(),
        ]);

        Movimentacao::create([
            'id_material' =>
                $idMaterial,

            'id_usuario' =>
                $usuario->id,

            'tipo_movimentacao' =>
                'entrada',

            'origem' =>
                'manual',

            'quantidade' =>
                1,

            'observacao' =>
                'Movimentação criada para teste.',

            'data_movimentacao' =>
                now(),
        ]);

        $resposta = $this
            ->actingAs($administrador)
            ->delete(
                route(
                    'usuarios.destroy',
                    $usuario
                )
            );

        $resposta
            ->assertRedirect(
                route('usuarios.index')
            )
            ->assertSessionHas(
                'erro',
                'Não é possível excluir este usuário porque ele possui movimentações de estoque registradas.'
            );

        $this->assertDatabaseHas(
            'users',
            [
                'id' =>
                    $usuario->id,
            ]
        );
    }

    public function test_usuario_que_criou_ordem_nao_pode_ser_excluido(): void
    {
        $administrador = User::factory()
            ->administrador()
            ->create();

        $usuario = User::factory()
            ->create();

        DB::table(
            'ordens_producao'
        )->insert([
            'codigo' =>
                'OP-TESTE-CRIADOR',

            'id_usuario_criador' =>
                $usuario->id,

            'status' =>
                'planejada',

            'data_prevista' =>
                null,

            'observacao' =>
                null,

            'created_at' =>
                now(),

            'updated_at' =>
                now(),
        ]);

        $resposta = $this
            ->actingAs($administrador)
            ->delete(
                route(
                    'usuarios.destroy',
                    $usuario
                )
            );

        $resposta
            ->assertRedirect(
                route('usuarios.index')
            )
            ->assertSessionHas(
                'erro',
                'Não é possível excluir este usuário porque ele é responsável pela criação de uma ou mais ordens de produção.'
            );

        $this->assertDatabaseHas(
            'users',
            [
                'id' =>
                    $usuario->id,
            ]
        );
    }

    public function test_usuario_com_historico_de_producao_nao_pode_ser_excluido(): void
    {
        $administrador = User::factory()
            ->administrador()
            ->create();

        $usuario = User::factory()
            ->create();

        $idProduto = DB::table(
            'produtos'
        )->insertGetId([
            'nome_produto' =>
                'Kimono Histórico',

            'categoria' =>
                'Kimono',

            'descricao' =>
                null,

            'ativo' =>
                true,

            'created_at' =>
                now(),

            'updated_at' =>
                now(),
        ]);

        $idOrdem = DB::table(
            'ordens_producao'
        )->insertGetId([
            'codigo' =>
                'OP-TESTE-HISTORICO',

            'id_usuario_criador' =>
                $administrador->id,

            'status' =>
                'em_producao',

            'data_prevista' =>
                null,

            'observacao' =>
                null,

            'created_at' =>
                now(),

            'updated_at' =>
                now(),
        ]);

        $idItem = DB::table(
            'ordem_producao_itens'
        )->insertGetId([
            'id_ordem' =>
                $idOrdem,

            'id_produto' =>
                $idProduto,

            'id_cliente' =>
                null,

            'cliente_nome' =>
                null,

            'cor' =>
                'Branco',

            'quantidade' =>
                1,

            'etapa_atual' =>
                'bordado',

            'descricao_bordado' =>
                null,

            'cor_linha' =>
                null,

            'etiqueta' =>
                null,

            'posicao_etiqueta' =>
                null,

            'responsavel_costura' =>
                null,

            'observacao' =>
                null,

            'created_at' =>
                now(),

            'updated_at' =>
                now(),
        ]);

        DB::table(
            'ordem_producao_historicos'
        )->insert([
            'id_ordem' =>
                $idOrdem,

            'id_item' =>
                $idItem,

            'id_usuario' =>
                $usuario->id,

            'acao' =>
                'avanco',

            'etapa_origem' =>
                'corte',

            'etapa_destino' =>
                'bordado',

            'observacao' =>
                'Histórico criado para teste.',

            'created_at' =>
                now(),

            'updated_at' =>
                now(),
        ]);

        $resposta = $this
            ->actingAs($administrador)
            ->delete(
                route(
                    'usuarios.destroy',
                    $usuario
                )
            );

        $resposta
            ->assertRedirect(
                route('usuarios.index')
            )
            ->assertSessionHas(
                'erro',
                'Não é possível excluir este usuário porque ele possui ações registradas no histórico de produção.'
            );

        $this->assertDatabaseHas(
            'users',
            [
                'id' =>
                    $usuario->id,
            ]
        );
    }

    public function test_usuario_que_cancelou_ordem_nao_pode_ser_excluido(): void
    {
        $administrador = User::factory()
            ->administrador()
            ->create();

        $usuario = User::factory()
            ->create();

        DB::table(
            'ordens_producao'
        )->insert([
            'codigo' =>
                'OP-TESTE-CANCELAMENTO',

            'id_usuario_criador' =>
                $administrador->id,

            'status' =>
                'cancelada',

            'data_prevista' =>
                null,

            'observacao' =>
                null,

            'motivo_cancelamento' =>
                'Cancelamento criado para teste.',

            'cancelada_em' =>
                now(),

            'id_usuario_cancelamento' =>
                $usuario->id,

            'created_at' =>
                now(),

            'updated_at' =>
                now(),
        ]);

        $resposta = $this
            ->actingAs($administrador)
            ->delete(
                route(
                    'usuarios.destroy',
                    $usuario
                )
            );

        $resposta
            ->assertRedirect(
                route('usuarios.index')
            )
            ->assertSessionHas(
                'erro',
                'Não é possível excluir este usuário porque ele é responsável pelo cancelamento de uma ou mais ordens de produção.'
            );

        $this->assertDatabaseHas(
            'users',
            [
                'id' =>
                    $usuario->id,
            ]
        );
    }
}
