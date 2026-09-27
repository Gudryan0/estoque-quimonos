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
         * Lote 1:
         * 2 × 2,5 = 5
         *
         * Lote 2:
         * 3 × 2,5 = 7,5
         *
         * Total:
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

        for ($i = 0; $i < 4; $i++) {
            $resposta = $this->post(
                route(
                    'ordens.itens.avancar',
                    [
                        $idOrdem,
                        $idItem,
                    ]
                )
            );
        }

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
                'quantidade' => 2,
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

    public function test_lote_com_varios_tamanhos_consume_e_adiciona_cada_variacao_ao_estoque(): void
    {
        $idVariacaoA1 =
            $this->criarProdutoVariacao(
                quantidade: 0,
                tamanho: 'A1',
                cor: 'Azul'
            );

        $idProduto = (int)
            DB::table('produto_variacoes')
                ->where(
                    'id_variacao',
                    $idVariacaoA1
                )
                ->value('id_produto');

        $idVariacaoA2 =
            $this->criarVariacaoNoProduto(
                $idProduto,
                tamanho: 'A2',
                cor: 'Azul',
                quantidade: 0
            );

        $idMaterial =
            $this->criarMaterial(
                quantidade: 20
            );

        /*
         * A1:
         * 3 peças × 1 = 3
         *
         * A2:
         * 2 peças × 1,5 = 3
         *
         * Total:
         * 6
         */
        $this->criarComposicao(
            $idVariacaoA1,
            $idMaterial,
            1
        );

        $this->criarComposicao(
            $idVariacaoA2,
            $idMaterial,
            1.5
        );

        $idOrdem =
            $this->criarOrdem();

        $idLote =
            $this->criarLote(
                $idOrdem,
                [
                    $idVariacaoA1 => 3,
                    $idVariacaoA2 => 2,
                ]
            );

        $this->post(
            route(
                'ordens.iniciar',
                $idOrdem
            )
        );

        $estoqueMaterial =
            DB::table('materiais')
                ->where(
                    'id_material',
                    $idMaterial
                )
                ->value('quantidade');

        $this->assertEqualsWithDelta(
            14,
            (float) $estoqueMaterial,
            0.0001
        );

        $this->assertDatabaseHas(
            'ordem_producao_itens',
            [
                'id_item' =>
                    $idLote,

                'quantidade' =>
                    5,
            ]
        );

        $this->assertDatabaseHas(
            'ordem_producao_item_variacoes',
            [
                'id_item' =>
                    $idLote,

                'id_variacao' =>
                    $idVariacaoA1,

                'quantidade' =>
                    3,
            ]
        );

        $this->assertDatabaseHas(
            'ordem_producao_item_variacoes',
            [
                'id_item' =>
                    $idLote,

                'id_variacao' =>
                    $idVariacaoA2,

                'quantidade' =>
                    2,
            ]
        );

        for ($i = 0; $i < 4; $i++) {
            $this->post(
                route(
                    'ordens.itens.avancar',
                    [
                        $idOrdem,
                        $idLote,
                    ]
                )
            );
        }

        $this->assertDatabaseHas(
            'ordens_producao',
            [
                'id_ordem' =>
                    $idOrdem,

                'status' =>
                    'concluida',
            ]
        );

        $this->assertDatabaseHas(
            'produto_variacoes',
            [
                'id_variacao' =>
                    $idVariacaoA1,

                'quantidade' =>
                    3,
            ]
        );

        $this->assertDatabaseHas(
            'produto_variacoes',
            [
                'id_variacao' =>
                    $idVariacaoA2,

                'quantidade' =>
                    2,
            ]
        );

        $this->assertEquals(
            2,
            DB::table('movimentacoes')
                ->where(
                    'id_item_producao',
                    $idLote
                )
                ->where(
                    'origem',
                    'producao_conclusao'
                )
                ->count()
        );
    }

    public function test_reversao_de_lote_com_varios_tamanhos_remove_todas_as_variacoes(): void
    {
        $idVariacaoA1 =
            $this->criarProdutoVariacao(
                quantidade: 0,
                tamanho: 'A1',
                cor: 'Azul'
            );

        $idProduto = (int)
            DB::table('produto_variacoes')
                ->where(
                    'id_variacao',
                    $idVariacaoA1
                )
                ->value('id_produto');

        $idVariacaoA2 =
            $this->criarVariacaoNoProduto(
                $idProduto,
                tamanho: 'A2',
                cor: 'Azul',
                quantidade: 0
            );

        $idMaterial =
            $this->criarMaterial(
                quantidade: 50
            );

        $this->criarComposicao(
            $idVariacaoA1,
            $idMaterial,
            1
        );

        $this->criarComposicao(
            $idVariacaoA2,
            $idMaterial,
            1
        );

        $idOrdem =
            $this->criarOrdem();

        $idLoteConcluido =
            $this->criarLote(
                $idOrdem,
                [
                    $idVariacaoA1 => 2,
                    $idVariacaoA2 => 1,
                ]
            );

        /*
         * Mantém outro lote pendente para que
         * a OP continue em produção e permita
         * reverter a conclusão do primeiro.
         */
        $this->criarLote(
            $idOrdem,
            [
                $idVariacaoA1 => 1,
            ]
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
                        $idLoteConcluido,
                    ]
                )
            );
        }

        $this->assertDatabaseHas(
            'ordens_producao',
            [
                'id_ordem' =>
                    $idOrdem,

                'status' =>
                    'em_producao',
            ]
        );

        $this->assertDatabaseHas(
            'produto_variacoes',
            [
                'id_variacao' =>
                    $idVariacaoA1,

                'quantidade' =>
                    2,
            ]
        );

        $this->assertDatabaseHas(
            'produto_variacoes',
            [
                'id_variacao' =>
                    $idVariacaoA2,

                'quantidade' =>
                    1,
            ]
        );

        $resposta = $this->post(
            route(
                'ordens.itens.reverter',
                [
                    $idOrdem,
                    $idLoteConcluido,
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
            ->assertSessionHas('sucesso');

        $this->assertDatabaseHas(
            'ordem_producao_itens',
            [
                'id_item' =>
                    $idLoteConcluido,

                'etapa_atual' =>
                    'finalizacao',

                'produto_estoque_adicionado_at' =>
                    null,
            ]
        );

        $this->assertDatabaseHas(
            'produto_variacoes',
            [
                'id_variacao' =>
                    $idVariacaoA1,

                'quantidade' =>
                    0,
            ]
        );

        $this->assertDatabaseHas(
            'produto_variacoes',
            [
                'id_variacao' =>
                    $idVariacaoA2,

                'quantidade' =>
                    0,
            ]
        );

        $this->assertEquals(
            2,
            DB::table('movimentacoes')
                ->where(
                    'id_item_producao',
                    $idLoteConcluido
                )
                ->where(
                    'origem',
                    'producao_reversao'
                )
                ->count()
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
                'id_ordem' =>
                    $idOrdem,

                'status' =>
                    'concluida',
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

        $this->assertDatabaseHas(
            'ordens_producao',
            [
                'id_ordem' =>
                    $idOrdem,

                'status' =>
                    'concluida',
            ]
        );

        $this->assertDatabaseHas(
            'ordem_producao_itens',
            [
                'id_item' =>
                    $idItem,

                'etapa_atual' =>
                    'concluido',
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
        int $quantidade = 0,
        string $tamanho = 'A2',
        string $cor = 'Branco'
    ): int {
        $idProduto =
            DB::table('produtos')
                ->insertGetId([
                    'nome_produto' =>
                        'Kimono Teste ' . uniqid(),

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

        return $this->criarVariacaoNoProduto(
            $idProduto,
            $tamanho,
            $cor,
            $quantidade
        );
    }

    private function criarVariacaoNoProduto(
        int $idProduto,
        string $tamanho,
        string $cor,
        int $quantidade = 0
    ): int {
        return DB::table(
            'produto_variacoes'
        )->insertGetId([
            'id_produto' =>
                $idProduto,

            'tamanho' =>
                $tamanho,

            'cor' =>
                $cor,

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
        return $this->criarLote(
            $idOrdem,
            [
                $idVariacao =>
                    $quantidade,
            ]
        );
    }

    private function criarLote(
        int $idOrdem,
        array $quantidadesPorVariacao
    ): int {
        if ($quantidadesPorVariacao === []) {
            throw new \RuntimeException(
                'O lote precisa possuir pelo menos uma variação.'
            );
        }

        $idsVariacoes = array_map(
            'intval',
            array_keys(
                $quantidadesPorVariacao
            )
        );

        $variacoes =
            DB::table('produto_variacoes')
                ->whereIn(
                    'id_variacao',
                    $idsVariacoes
                )
                ->get()
                ->keyBy('id_variacao');

        if (
            $variacoes->count()
            !== count($idsVariacoes)
        ) {
            throw new \RuntimeException(
                'Uma das variações do lote não foi encontrada.'
            );
        }

        $primeiraVariacao =
            $variacoes->first();

        foreach ($variacoes as $variacao) {
            if (
                (int) $variacao->id_produto
                    !==
                (int) $primeiraVariacao->id_produto
                ||
                $variacao->cor
                    !==
                $primeiraVariacao->cor
            ) {
                throw new \RuntimeException(
                    'As variações do lote precisam pertencer ao mesmo produto e cor.'
                );
            }
        }

        $total = array_sum(
            array_map(
                'intval',
                $quantidadesPorVariacao
            )
        );

        $idItem =
            DB::table(
                'ordem_producao_itens'
            )->insertGetId([
                'id_ordem' =>
                    $idOrdem,

                'id_produto' =>
                    $primeiraVariacao->id_produto,

                'id_cliente' =>
                    null,

                'cliente_nome' =>
                    null,

                'cor' =>
                    $primeiraVariacao->cor,

                'quantidade' =>
                    $total,

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

        foreach (
            $quantidadesPorVariacao
            as $idVariacao => $quantidade
        ) {
            DB::table(
                'ordem_producao_item_variacoes'
            )->insert([
                'id_item' =>
                    $idItem,

                'id_variacao' =>
                    (int) $idVariacao,

                'quantidade' =>
                    (int) $quantidade,

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ]);
        }

        return $idItem;
    }
}
