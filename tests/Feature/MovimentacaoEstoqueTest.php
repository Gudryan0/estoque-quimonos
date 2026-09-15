<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MovimentacaoEstoqueTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = User::factory()->create();
    }

    public function test_saida_de_produto_nao_pode_deixar_estoque_negativo(): void
    {
        $idProduto = DB::table('produtos')->insertGetId([
            'nome_produto' => 'Kimono Teste',
            'categoria' => 'Kimono',
            'descricao' => null,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $idVariacao = DB::table(
            'produto_variacoes'
        )->insertGetId([
            'id_produto' => $idProduto,
            'tamanho' => 'A2',
            'cor' => 'Branco',
            'quantidade' => 5,
            'estoque_minimo' => 0,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resposta = $this
            ->actingAs($this->usuario)
            ->post(
                route('movimentacoes.store'),
                [
                    'tipo_item' => 'produto',
                    'tipo_movimentacao' => 'saida',
                    'id_variacao' => $idVariacao,
                    'quantidade' => 6,
                ]
            );

        $resposta
            ->assertRedirect(
                route('movimentacoes.create')
            )
            ->assertSessionHas(
                'erro',
                'Estoque insuficiente para realizar esta saída. Disponível: 5 un.'
            );

        $this->assertDatabaseHas(
            'produto_variacoes',
            [
                'id_variacao' => $idVariacao,
                'quantidade' => 5,
            ]
        );

        $this->assertDatabaseMissing(
            'movimentacoes',
            [
                'id_variacao' => $idVariacao,
                'tipo_movimentacao' => 'saida',
            ]
        );
    }

    public function test_saida_de_material_nao_pode_deixar_estoque_negativo(): void
    {
        $idMaterial = DB::table(
            'materiais'
        )->insertGetId([
            'nome_material' => 'Tecido Branco',
            'categoria' => 'tecido',
            'tipo_material' => 'Trançado',
            'cor' => 'Branco',
            'unidade_medida' => 'm',
            'quantidade' => 3.500,
            'estoque_minimo' => 0,
            'id_fornecedor' => null,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resposta = $this
            ->actingAs($this->usuario)
            ->post(
                route('movimentacoes.store'),
                [
                    'tipo_item' => 'material',
                    'tipo_movimentacao' => 'saida',
                    'id_material' => $idMaterial,
                    'quantidade' => 4,
                ]
            );

        $resposta
            ->assertRedirect(
                route('movimentacoes.create')
            )
            ->assertSessionHas('erro');

        $quantidade = DB::table('materiais')
            ->where(
                'id_material',
                $idMaterial
            )
            ->value('quantidade');

        $this->assertEquals(
            3.500,
            (float) $quantidade
        );

        $this->assertDatabaseMissing(
            'movimentacoes',
            [
                'id_material' => $idMaterial,
                'tipo_movimentacao' => 'saida',
            ]
        );
    }

    public function test_entrada_manual_de_produto_aumenta_estoque_e_registra_movimentacao(): void
    {
        $idProduto = DB::table('produtos')->insertGetId([
            'nome_produto' => 'Kimono Teste',
            'categoria' => 'Kimono',
            'descricao' => null,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $idVariacao = DB::table(
            'produto_variacoes'
        )->insertGetId([
            'id_produto' => $idProduto,
            'tamanho' => 'A3',
            'cor' => 'Azul',
            'quantidade' => 2,
            'estoque_minimo' => 0,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resposta = $this
            ->actingAs($this->usuario)
            ->post(
                route('movimentacoes.store'),
                [
                    'tipo_item' => 'produto',
                    'tipo_movimentacao' => 'entrada',
                    'id_variacao' => $idVariacao,
                    'quantidade' => 4,
                    'observacao' => 'Entrada de teste',
                ]
            );

        $resposta->assertRedirect(
            route('movimentacoes.index')
        );

        $this->assertDatabaseHas(
            'produto_variacoes',
            [
                'id_variacao' => $idVariacao,
                'quantidade' => 6,
            ]
        );

        $this->assertDatabaseHas(
            'movimentacoes',
            [
                'id_variacao' => $idVariacao,
                'id_usuario' => $this->usuario->id,
                'tipo_movimentacao' => 'entrada',
                'origem' => 'manual',
                'quantidade' => 4,
            ]
        );
    }

    public function test_movimentacao_de_material_aceita_quantidade_decimal(): void
    {
        $idMaterial = DB::table(
            'materiais'
        )->insertGetId([
            'nome_material' => 'Tecido Preto',
            'categoria' => 'tecido',
            'tipo_material' => null,
            'cor' => 'Preto',
            'unidade_medida' => 'm',
            'quantidade' => 10.000,
            'estoque_minimo' => 0,
            'id_fornecedor' => null,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resposta = $this
            ->actingAs($this->usuario)
            ->post(
                route('movimentacoes.store'),
                [
                    'tipo_item' => 'material',
                    'tipo_movimentacao' => 'saida',
                    'id_material' => $idMaterial,
                    'quantidade' => 2.750,
                ]
            );

        $resposta->assertRedirect(
            route('movimentacoes.index')
        );

        $quantidade = DB::table('materiais')
            ->where(
                'id_material',
                $idMaterial
            )
            ->value('quantidade');

        $this->assertEquals(
            7.250,
            (float) $quantidade
        );

        $this->assertDatabaseHas(
            'movimentacoes',
            [
                'id_material' => $idMaterial,
                'id_usuario' => $this->usuario->id,
                'tipo_movimentacao' => 'saida',
                'origem' => 'manual',
                'quantidade' => 2.750,
            ]
        );
    }

    public function test_material_em_unidade_nao_aceita_quantidade_fracionaria(): void
    {
        $idMaterial = DB::table(
            'materiais'
        )->insertGetId([
            'nome_material' => 'Etiqueta',
            'categoria' => 'etiqueta',
            'tipo_material' => null,
            'cor' => null,
            'unidade_medida' => 'un',
            'quantidade' => 10.000,
            'estoque_minimo' => 0,
            'id_fornecedor' => null,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resposta = $this
            ->actingAs($this->usuario)
            ->post(
                route('movimentacoes.store'),
                [
                    'tipo_item' => 'material',
                    'tipo_movimentacao' => 'saida',
                    'id_material' => $idMaterial,
                    'quantidade' => 1.5,
                ]
            );

        $resposta->assertSessionHasErrors(
            'quantidade'
        );

        $quantidade = DB::table('materiais')
            ->where(
                'id_material',
                $idMaterial
            )
            ->value('quantidade');

        $this->assertEquals(
            10.000,
            (float) $quantidade
        );

        $this->assertDatabaseMissing(
            'movimentacoes',
            [
                'id_material' => $idMaterial,
            ]
        );
    }

    public function test_material_em_rolo_nao_aceita_quantidade_fracionaria(): void
    {
        $idMaterial = DB::table(
            'materiais'
        )->insertGetId([
            'nome_material' => 'Linha Branca',
            'categoria' => 'linha',
            'tipo_material' => null,
            'cor' => 'Branca',
            'unidade_medida' => 'rolo',
            'quantidade' => 8.000,
            'estoque_minimo' => 0,
            'id_fornecedor' => null,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resposta = $this
            ->actingAs($this->usuario)
            ->post(
                route('movimentacoes.store'),
                [
                    'tipo_item' => 'material',
                    'tipo_movimentacao' => 'saida',
                    'id_material' => $idMaterial,
                    'quantidade' => 1.5,
                ]
            );

        $resposta->assertSessionHasErrors(
            'quantidade'
        );

        $quantidade = DB::table('materiais')
            ->where(
                'id_material',
                $idMaterial
            )
            ->value('quantidade');

        $this->assertEquals(
            8.000,
            (float) $quantidade
        );

        $this->assertDatabaseMissing(
            'movimentacoes',
            [
                'id_material' => $idMaterial,
            ]
        );
    }

    public function test_material_decimal_nao_aceita_mais_de_tres_casas_decimais(): void
    {
        $idMaterial = DB::table(
            'materiais'
        )->insertGetId([
            'nome_material' => 'Tecido Azul',
            'categoria' => 'tecido',
            'tipo_material' => null,
            'cor' => 'Azul',
            'unidade_medida' => 'm',
            'quantidade' => 10.000,
            'estoque_minimo' => 0,
            'id_fornecedor' => null,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resposta = $this
            ->actingAs($this->usuario)
            ->post(
                route('movimentacoes.store'),
                [
                    'tipo_item' => 'material',
                    'tipo_movimentacao' => 'saida',
                    'id_material' => $idMaterial,
                    'quantidade' => 1.2345,
                ]
            );

        $resposta->assertSessionHasErrors(
            'quantidade'
        );

        $quantidade = DB::table('materiais')
            ->where(
                'id_material',
                $idMaterial
            )
            ->value('quantidade');

        $this->assertEquals(
            10.000,
            (float) $quantidade
        );

        $this->assertDatabaseMissing(
            'movimentacoes',
            [
                'id_material' => $idMaterial,
            ]
        );
    }

    public function test_variacao_inativa_nao_pode_receber_movimentacao_manual(): void
    {
        $idProduto = DB::table('produtos')->insertGetId([
            'nome_produto' => 'Kimono Arquivado',
            'categoria' => 'Kimono',
            'descricao' => null,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $idVariacao = DB::table(
            'produto_variacoes'
        )->insertGetId([
            'id_produto' => $idProduto,
            'tamanho' => 'A1',
            'cor' => 'Preto',
            'quantidade' => 5,
            'estoque_minimo' => 0,
            'ativo' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resposta = $this
            ->actingAs($this->usuario)
            ->post(
                route('movimentacoes.store'),
                [
                    'tipo_item' => 'produto',
                    'tipo_movimentacao' => 'entrada',
                    'id_variacao' => $idVariacao,
                    'quantidade' => 2,
                ]
            );

        $resposta->assertSessionHasErrors(
            'id_variacao'
        );

        $this->assertDatabaseHas(
            'produto_variacoes',
            [
                'id_variacao' => $idVariacao,
                'quantidade' => 5,
            ]
        );

        $this->assertDatabaseMissing(
            'movimentacoes',
            [
                'id_variacao' => $idVariacao,
            ]
        );
    }

    public function test_material_inativo_nao_pode_receber_movimentacao_manual(): void
    {
        $idMaterial = DB::table(
            'materiais'
        )->insertGetId([
            'nome_material' => 'Linha Antiga',
            'categoria' => 'linha',
            'tipo_material' => null,
            'cor' => 'Branca',
            'unidade_medida' => 'rolo',
            'quantidade' => 3.000,
            'estoque_minimo' => 0,
            'id_fornecedor' => null,
            'ativo' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resposta = $this
            ->actingAs($this->usuario)
            ->post(
                route('movimentacoes.store'),
                [
                    'tipo_item' => 'material',
                    'tipo_movimentacao' => 'entrada',
                    'id_material' => $idMaterial,
                    'quantidade' => 1,
                ]
            );

        $resposta->assertSessionHasErrors(
            'id_material'
        );

        $quantidade = DB::table('materiais')
            ->where(
                'id_material',
                $idMaterial
            )
            ->value('quantidade');

        $this->assertEquals(
            3.000,
            (float) $quantidade
        );

        $this->assertDatabaseMissing(
            'movimentacoes',
            [
                'id_material' => $idMaterial,
            ]
        );
    }
}
