<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FluxoProducaoTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = User::factory()->create();

        $this->actingAs($this->usuario);
    }

    public function test_inicio_da_producao_consume_materiais_de_forma_agregada(): void
    {
        $idVariacao = $this->criarProdutoVariacao();

        $idMaterial = $this->criarMaterial(
            quantidade: 20
        );

        $this->criarComposicao(
            $idVariacao,
            $idMaterial,
            2.5
        );

        $idOrdem = $this->criarOrdem();

        $idItem1 = $this->criarItem(
            $idOrdem,
            $idVariacao,
            2
        );

        $idItem2 = $this->criarItem(
            $idOrdem,
            $idVariacao,
            3
        );

        /*
         * Necessidade:
         *
         * item 1:
         * 2 × 2,5 = 5
         *
         * item 2:
         * 3 × 2,5 = 7,5
         *
         * total:
         * 12,5
         */
        $resposta = $this->post(
            route(
                'ordens.iniciar',
                $idOrdem
            )
        );

        $resposta->assertRedirect(
            route(
                'ordens.show',
                $idOrdem
            )
        );

        $this->assertDatabaseHas(
            'ordens_producao',
            [
                'id_ordem' => $idOrdem,
                'status' => 'em_producao',
            ]
        );

        $estoqueMaterial =
            DB::table('materiais')
                ->where(
                    'id_material',
                    $idMaterial
                )
                ->value('quantidade');

        $this->assertEqualsWithDelta(
            7.5,
            (float) $estoqueMaterial,
            0.0001
        );

        $this->assertEquals(
            2,
            DB::table('movimentacoes')
                ->where(
                    'id_ordem',
                    $idOrdem
                )
                ->where(
                    'origem',
                    'producao_consumo'
                )
                ->count()
        );

        $totalConsumido =
            DB::table('movimentacoes')
                ->where(
                    'id_ordem',
                    $idOrdem
                )
                ->where(
                    'origem',
                    'producao_consumo'
                )
                ->sum('quantidade');

        $this->assertEqualsWithDelta(
            12.5,
            (float) $totalConsumido,
            0.0001
        );

        $this->assertNotNull(
            DB::table(
                'ordem_producao_itens'
            )
                ->where(
                    'id_item',
                    $idItem1
                )
                ->value(
                    'materiais_consumidos_at'
                )
        );

        $this->assertNotNull(
            DB::table(
                'ordem_producao_itens'
            )
                ->where(
                    'id_item',
                    $idItem2
                )
                ->value(
                    'materiais_consumidos_at'
                )
        );

        $this->assertEquals(
            2,
            DB::table(
                'ordem_producao_historicos'
            )
                ->where(
                    'id_ordem',
                    $idOrdem
                )
                ->where(
                    'acao',
                    'inicio'
                )
                ->count()
        );
    }

    public function test_falta_de_estoque_impede_inicio_sem_consumo_parcial(): void
    {
        $idVariacao =
            $this->criarProdutoVariacao();

        $idMaterial =
            $this->criarMaterial(
                quantidade: 10
            );

        $this->criarComposicao(
            $idVariacao,
            $idMaterial,
            2
        );

        $idOrdem =
            $this->criarOrdem();

        $idItem1 = $this->criarItem(
            $idOrdem,
            $idVariacao,
            3
        );

        $idItem2 = $this->criarItem(
            $idOrdem,
            $idVariacao,
            3
        );

        /*
         * Necessário:
         * 6 + 6 = 12
         *
         * Disponível:
         * 10
         */
        $resposta = $this->post(
            route(
                'ordens.iniciar',
                $idOrdem
            )
        );

        $resposta
            ->assertRedirect(
                route(
                    'ordens.show',
                    $idOrdem
                )
            )
            ->assertSessionHas('erro');

        $this->assertDatabaseHas(
            'ordens_producao',
            [
                'id_ordem' => $idOrdem,
                'status' => 'planejada',
            ]
        );

        $estoqueMaterial =
            DB::table('materiais')
                ->where(
                    'id_material',
                    $idMaterial
                )
                ->value('quantidade');

        $this->assertEqualsWithDelta(
            10,
            (float) $estoqueMaterial,
            0.0001
        );

        $this->assertEquals(
            0,
            DB::table('movimentacoes')
                ->where(
                    'id_ordem',
                    $idOrdem
                )
                ->where(
                    'origem',
                    'producao_consumo'
                )
                ->count()
        );

        $this->assertNull(
            DB::table(
                'ordem_producao_itens'
            )
                ->where(
                    'id_item',
                    $idItem1
                )
                ->value(
                    'materiais_consumidos_at'
                )
        );

        $this->assertNull(
            DB::table(
                'ordem_producao_itens'
            )
                ->where(
                    'id_item',
                    $idItem2
                )
                ->value(
                    'materiais_consumidos_at'
                )
        );
    }

    public function test_material_inativo_impede_inicio_da_producao(): void
    {
        $idVariacao =
            $this->criarProdutoVariacao();

        $idMaterial =
            $this->criarMaterial(
                quantidade: 100,
                ativo: false
            );

        $this->criarComposicao(
            $idVariacao,
            $idMaterial,
            1
        );

        $idOrdem =
            $this->criarOrdem();

        $this->criarItem(
            $idOrdem,
            $idVariacao,
            5
        );

        $resposta = $this->post(
            route(
                'ordens.iniciar',
                $idOrdem
            )
        );

        $resposta
            ->assertRedirect(
                route(
                    'ordens.show',
                    $idOrdem
                )
            )
            ->assertSessionHas('erro');

        $this->assertDatabaseHas(
            'ordens_producao',
            [
                'id_ordem' => $idOrdem,
                'status' => 'planejada',
            ]
        );

        $estoqueMaterial =
            DB::table('materiais')
                ->where(
                    'id_material',
                    $idMaterial
                )
                ->value('quantidade');

        $this->assertEqualsWithDelta(
            100,
            (float) $estoqueMaterial,
            0.0001
        );

        $this->assertEquals(
            0,
            DB::table('movimentacoes')
                ->where(
                    'id_ordem',
                    $idOrdem
                )
                ->count()
        );
    }

    public function test_conclusao_adiciona_produto_ao_estoque_e_encerra_ordem(): void
    {
        $idVariacao =
            $this->criarProdutoVariacao(
                quantidade: 0
            );

        $idMaterial =
            $this->criarMaterial(
                quantidade: 10
            );

        $this->criarComposicao(
            $idVariacao,
            $idMaterial,
            1
        );

        $idOrdem =
            $this->criarOrdem();

        $idItem =
            $this->criarItem(
                $idOrdem,
                $idVariacao,
                2
            );

        $this->post(
            route(
                'ordens.iniciar',
                $idOrdem
            )
        );

        /*
         * corte
         * → bordado
         */
        $this->post(
            route(
                'ordens.itens.avancar',
                [
                    $idOrdem,
                    $idItem,
                ]
            )
        );

        /*
         * bordado
         * → costura
         */
        $this->post(
            route(
                'ordens.itens.avancar',
                [
                    $idOrdem,
                    $idItem,
                ]
            )
        );

        /*
         * costura
         * → finalizacao
         */
        $this->post(
            route(
                'ordens.itens.avancar',
                [
                    $idOrdem,
                    $idItem,
                ]
            )
        );

        /*
         * finalizacao
         * → concluido
         */
        $resposta = $this->post(
            route(
                'ordens.itens.avancar',
                [
                    $idOrdem,
                    $idItem,
                ]
            )
        );

        $resposta->assertRedirect(
            route(
                'ordens.show',
                $idOrdem
            )
        );

        $this->assertDatabaseHas(
            'ordens_producao',
            [
                'id_ordem' => $idOrdem,
                'status' => 'concluida',
            ]
        );

        $this->assertDatabaseHas(
            'ordem_producao_itens',
            [
                'id_item' => $idItem,
                'etapa_atual' => 'concluido',
            ]
        );

        $this->assertDatabaseHas(
            'produto_variacoes',
            [
                'id_variacao' => $idVariacao,
                'quantidade' => 2,
            ]
        );

        $this->assertDatabaseHas(
            'movimentacoes',
            [
                'id_variacao' =>
                    $idVariacao,

                'id_ordem' =>
                    $idOrdem,

                'id_item_producao' =>
                    $idItem,

                'tipo_movimentacao' =>
                    'entrada',

                'origem' =>
                    'producao_conclusao',

                'quantidade' =>
                    2,
            ]
        );

        $this->assertNotNull(
            DB::table(
                'ordem_producao_itens'
            )
                ->where(
                    'id_item',
                    $idItem
                )
                ->value(
                    'produto_estoque_adicionado_at'
                )
        );
    }

    public function test_ordem_concluida_nao_pode_ser_reaberta_por_reversao(): void
    {
        $idVariacao =
            $this->criarProdutoVariacao(
                quantidade: 0
            );

        $idMaterial =
            $this->criarMaterial(
                quantidade: 10
            );

        $this->criarComposicao(
            $idVariacao,
            $idMaterial,
            1
        );

        $idOrdem =
            $this->criarOrdem();

        $idItem =
            $this->criarItem(
                $idOrdem,
                $idVariacao,
                2
            );

        $this->post(
            route(
                'ordens.iniciar',
                $idOrdem
            )
        );

        for ($i = 0; $i < 4; $i++) {
            $this->post(
                route(
                    'ordens.itens.avancar',
                    [
                        $idOrdem,
                        $idItem,
                    ]
                )
            );
        }

        $this->assertDatabaseHas(
            'ordens_producao',
            [
                'id_ordem' => $idOrdem,
                'status' => 'concluida',
            ]
        );

        $this->assertDatabaseHas(
            'produto_variacoes',
            [
                'id_variacao' =>
                    $idVariacao,

                'quantidade' =>
                    2,
            ]
        );

        $resposta = $this->post(
            route(
                'ordens.itens.reverter',
                [
                    $idOrdem,
                    $idItem,
                ]
            )
        );

        $resposta
            ->assertRedirect(
                route(
                    'ordens.show',
                    $idOrdem
                )
            )
            ->assertSessionHas(
                'erro',
                'Esta ordem já foi concluída definitivamente e não permite mais reversão de etapas.'
            );

        /*
         * Nada deve ter mudado.
         */
        $this->assertDatabaseHas(
            'ordens_producao',
            [
                'id_ordem' => $idOrdem,
                'status' => 'concluida',
            ]
        );

        $this->assertDatabaseHas(
            'ordem_producao_itens',
            [
                'id_item' => $idItem,
                'etapa_atual' => 'concluido',
            ]
        );

        $this->assertDatabaseHas(
            'produto_variacoes',
            [
                'id_variacao' =>
                    $idVariacao,

                'quantidade' =>
                    2,
            ]
        );

        $this->assertEquals(
            0,
            DB::table('movimentacoes')
                ->where(
                    'id_ordem',
                    $idOrdem
                )
                ->where(
                    'origem',
                    'producao_reversao'
                )
                ->count()
        );
    }

    private function criarProdutoVariacao(
        int $quantidade = 0
    ): int {
        $idProduto =
            DB::table('produtos')
                ->insertGetId([
                    'nome_produto' =>
                        'Kimono Teste',

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

        return DB::table(
            'produto_variacoes'
        )->insertGetId([
            'id_produto' =>
                $idProduto,

            'tamanho' =>
                'A2',

            'cor' =>
                'Branco',

            'quantidade' =>
                $quantidade,

            'estoque_minimo' =>
                0,

            'ativo' =>
                true,

            'created_at' =>
                now(),

            'updated_at' =>
                now(),
        ]);
    }

    private function criarMaterial(
        float $quantidade,
        bool $ativo = true
    ): int {
        return DB::table(
            'materiais'
        )->insertGetId([
            'nome_material' =>
                'Tecido Teste',

            'categoria' =>
                'tecido',

            'tipo_material' =>
                'Trançado',

            'cor' =>
                'Branco',

            'unidade_medida' =>
                'm',

            'quantidade' =>
                $quantidade,

            'estoque_minimo' =>
                0,

            'id_fornecedor' =>
                null,

            'ativo' =>
                $ativo,

            'created_at' =>
                now(),

            'updated_at' =>
                now(),
        ]);
    }

    private function criarComposicao(
        int $idVariacao,
        int $idMaterial,
        float $quantidadePorUnidade
    ): void {
        DB::table(
            'produto_variacao_materiais'
        )->insert([
            'id_variacao' =>
                $idVariacao,

            'id_material' =>
                $idMaterial,

            'quantidade_por_unidade' =>
                $quantidadePorUnidade,

            'created_at' =>
                now(),

            'updated_at' =>
                now(),
        ]);
    }

    private function criarOrdem(): int
    {
        return DB::table(
            'ordens_producao'
        )->insertGetId([
            'codigo' =>
                'OP-TESTE-'
                . uniqid(),

            'id_cliente' =>
                null,

            'cliente_nome' =>
                null,

            'id_usuario_criador' =>
                $this->usuario->id,

            'status' =>
                'planejada',

            'data_prevista' =>
                now()
                    ->addDays(5)
                    ->format('Y-m-d'),

            'observacao' =>
                null,

            'created_at' =>
                now(),

            'updated_at' =>
                now(),
        ]);
    }

    private function criarItem(
        int $idOrdem,
        int $idVariacao,
        int $quantidade
    ): int {
        return DB::table(
            'ordem_producao_itens'
        )->insertGetId([
            'id_ordem' =>
                $idOrdem,

            'id_variacao' =>
                $idVariacao,

            'quantidade' =>
                $quantidade,

            'etapa_atual' =>
                'corte',

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

            'materiais_consumidos_at' =>
                null,

            'produto_estoque_adicionado_at' =>
                null,

            'created_at' =>
                now(),

            'updated_at' =>
                now(),
        ]);
    }
}
