<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations_sobem_corretamente_no_ambiente_de_testes(): void
    {
        $tabelas = [
            'users',
            'fornecedores',
            'clientes',
            'produtos',
            'produto_variacoes',
            'materiais',
            'produto_variacao_materiais',
            'movimentacoes',
            'ordens_producao',
            'ordem_producao_itens',
            'ordem_producao_item_variacoes',
            'ordem_producao_historicos',
        ];

        foreach ($tabelas as $tabela) {
            $this->assertTrue(
                Schema::hasTable($tabela),
                "A tabela {$tabela} não foi criada."
            );
        }
    }

    public function test_estrutura_de_lotes_possui_as_colunas_esperadas(): void
    {
        $this->assertTrue(
            Schema::hasColumns(
                'ordem_producao_itens',
                [
                    'id_item',
                    'id_ordem',
                    'id_produto',
                    'id_cliente',
                    'cliente_nome',
                    'cor',
                    'quantidade',
                    'etapa_atual',
                    'materiais_consumidos_at',
                    'produto_estoque_adicionado_at',
                ]
            )
        );

        $this->assertTrue(
            Schema::hasColumns(
                'ordem_producao_item_variacoes',
                [
                    'id_item_variacao',
                    'id_item',
                    'id_variacao',
                    'quantidade',
                ]
            )
        );

        /*
         * Cliente pertence ao lote, não mais
         * diretamente à ordem de produção.
         */
        $this->assertFalse(
            Schema::hasColumn(
                'ordens_producao',
                'id_cliente'
            )
        );

        $this->assertFalse(
            Schema::hasColumn(
                'ordens_producao',
                'cliente_nome'
            )
        );

        /*
         * A variação deixou de pertencer
         * diretamente ao item/lote.
         */
        $this->assertFalse(
            Schema::hasColumn(
                'ordem_producao_itens',
                'id_variacao'
            )
        );
    }

    public function test_factory_cria_usuario_funcionario_valido(): void
    {
        $usuario =
            User::factory()
                ->create();

        $this->assertDatabaseHas(
            'users',
            [
                'id' =>
                    $usuario->id,

                'nivel_acesso' =>
                    'funcionario',
            ]
        );
    }

    public function test_factory_pode_criar_administrador(): void
    {
        $usuario =
            User::factory()
                ->administrador()
                ->create();

        $this->assertSame(
            'administrador',
            $usuario->nivel_acesso
        );

        $this->assertDatabaseHas(
            'users',
            [
                'id' =>
                    $usuario->id,

                'nivel_acesso' =>
                    'administrador',
            ]
        );
    }
}
