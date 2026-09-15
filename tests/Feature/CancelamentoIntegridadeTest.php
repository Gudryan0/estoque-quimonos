<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CancelamentoIntegridadeTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = User::factory()->create();

        $this->actingAs($this->usuario);
    }

    public function test_ordem_planejada_pode_ser_cancelada(): void
    {
        $idVariacao = $this->criarProdutoVariacao();

        $idOrdem = $this->criarOrdem();

        $this->criarItem(
            $idOrdem,
            $idVariacao,
            2
        );

        $resposta = $this->post(
            route(
                'ordens.cancelar',
                $idOrdem
            ),
            [
                'motivo_cancelamento' =>
                    'Cliente desistiu do pedido.',

                'confirmar_cancelamento' =>
                    '1',
            ]
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
                'id_ordem' =>
                    $idOrdem,

                'status' =>
                    'cancelada',

                'motivo_cancelamento' =>
                    'Cliente desistiu do pedido.',

                'id_usuario_cancelamento' =>
                    $this->usuario->id,
            ]
        );

        $this->assertNotNull(
            DB::table('ordens_producao')
                ->where(
                    'id_ordem',
                    $idOrdem
                )
                ->value(
                    'cancelada_em'
                )
        );
    }

    public function test_cancelamento_de_ordem_em_producao_nao_devolve_material_consumido(): void
    {
        $idVariacao =
            $this->criarProdutoVariacao();

        $idMaterial =
            $this->criarMaterial(10);

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

        $this->post(
            route(
                'ordens.iniciar',
                $idOrdem
            )
        );

        $estoqueAposInicio =
            DB::table('materiais')
                ->where(
                    'id_material',
                    $idMaterial
                )
                ->value('quantidade');

        $this->assertEqualsWithDelta(
            5,
            (float) $estoqueAposInicio,
            0.0001
        );

        $this->post(
            route(
                'ordens.cancelar',
                $idOrdem
            ),
            [
                'motivo_cancelamento' =>
                    'Produção interrompida.',

                'confirmar_cancelamento' =>
                    '1',
            ]
        );

        $this->assertDatabaseHas(
            'ordens_producao',
            [
                'id_ordem' =>
                    $idOrdem,

                'status' =>
                    'cancelada',
            ]
        );

        $estoqueAposCancelamento =
            DB::table('materiais')
                ->where(
                    'id_material',
                    $idMaterial
                )
                ->value('quantidade');

        $this->assertEqualsWithDelta(
            5,
            (float) $estoqueAposCancelamento,
            0.0001
        );

        $this->assertEquals(
            1,
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
    }

    public function test_ordem_com_item_concluido_nao_pode_ser_cancelada(): void
    {
        $idVariacao =
            $this->criarProdutoVariacao();

        $idMaterial =
            $this->criarMaterial(20);

        $this->criarComposicao(
            $idVariacao,
            $idMaterial,
            1
        );

        $idOrdem =
            $this->criarOrdem();

        $idItemConcluido =
            $this->criarItem(
                $idOrdem,
                $idVariacao,
                2
            );

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
         * Apenas o primeiro item será concluído.
         *
         * Como ainda existe outro item pendente,
         * a ordem permanece em_producao.
         */
        for ($i = 0; $i < 4; $i++) {
            $this->post(
                route(
                    'ordens.itens.avancar',
                    [
                        $idOrdem,
                        $idItemConcluido,
                    ]
                )
            );
        }

        $this->assertDatabaseHas(
            'ordem_producao_itens',
            [
                'id_item' =>
                    $idItemConcluido,

                'etapa_atual' =>
                    'concluido',
            ]
        );

        $this->assertDatabaseHas(
            'ordens_producao',
            [
                'id_ordem' =>
                    $idOrdem,

                'status' =>
                    'em_producao',
            ]
        );

        $resposta = $this->post(
            route(
                'ordens.cancelar',
                $idOrdem
            ),
            [
                'motivo_cancelamento' =>
                    'Tentativa inválida.',

                'confirmar_cancelamento' =>
                    '1',
            ]
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
                'id_ordem' =>
                    $idOrdem,

                'status' =>
                    'em_producao',

                'motivo_cancelamento' =>
                    null,
            ]
        );

        $this->assertDatabaseHas(
            'ordem_producao_itens',
            [
                'id_item' =>
                    $idItemConcluido,

                'etapa_atual' =>
                    'concluido',
            ]
        );
    }

    public function test_ordem_planejada_sem_itens_pode_ser_excluida(): void
    {
        $idOrdem =
            $this->criarOrdem();

        $resposta = $this->delete(
            route(
                'ordens.destroy',
                $idOrdem
            )
        );

        $resposta->assertRedirect(
            route('ordens.index')
        );

        $this->assertDatabaseMissing(
            'ordens_producao',
            [
                'id_ordem' =>
                    $idOrdem,
            ]
        );
    }

    public function test_ordem_planejada_com_item_nao_pode_ser_excluida(): void
    {
        $idVariacao =
            $this->criarProdutoVariacao();

        $idOrdem =
            $this->criarOrdem();

        $this->criarItem(
            $idOrdem,
            $idVariacao,
            1
        );

        $resposta = $this->delete(
            route(
                'ordens.destroy',
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
                'id_ordem' =>
                    $idOrdem,

                'status' =>
                    'planejada',
            ]
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
        float $quantidade
    ): int {
        return DB::table(
            'materiais'
        )->insertGetId([
            'nome_material' =>
                'Tecido Teste',

            'categoria' =>
                'tecido',

            'tipo_material' =>
                null,

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
                true,

            'created_at' =>
                now(),

            'updated_at' =>
                now(),
        ]);
    }

    private function criarComposicao(
        int $idVariacao,
        int $idMaterial,
        float $quantidade
    ): void {
        DB::table(
            'produto_variacao_materiais'
        )->insert([
            'id_variacao' =>
                $idVariacao,

            'id_material' =>
                $idMaterial,

            'quantidade_por_unidade' =>
                $quantidade,

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

            'motivo_cancelamento' =>
                null,

            'cancelada_em' =>
                null,

            'id_usuario_cancelamento' =>
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
