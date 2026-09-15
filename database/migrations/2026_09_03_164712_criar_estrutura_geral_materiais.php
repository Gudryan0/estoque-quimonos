<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Materiais
        |--------------------------------------------------------------------------
        |
        | Substituirá futuramente a tabela "tecidos".
        |
        | Exemplos:
        | tecido, EVA, fita, linha, etiqueta etc.
        |
        */

        Schema::create(
            'materiais',
            function (Blueprint $table) {
                $table->id('id_material');

                $table->string('nome_material');

                $table->string(
                    'categoria',
                    50
                );

                $table->string(
                    'tipo_material'
                )->nullable();

                $table->string(
                    'cor'
                )->nullable();

                $table->string(
                    'unidade_medida',
                    20
                );

                $table->decimal(
                    'quantidade',
                    12,
                    3
                )->default(0);

                $table->decimal(
                    'estoque_minimo',
                    12,
                    3
                )->default(0);

                $table->unsignedBigInteger(
                    'id_fornecedor'
                )->nullable();

                $table->timestamps();

                $table->foreign(
                    'id_fornecedor'
                )
                    ->references(
                        'id_fornecedor'
                    )
                    ->on('fornecedores')
                    ->restrictOnDelete();

                $table->index(
                    'categoria'
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Ficha de consumo generalizada
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'produto_variacao_materiais',
            function (Blueprint $table) {
                $table->id(
                    'id_composicao'
                );

                $table->unsignedBigInteger(
                    'id_variacao'
                );

                $table->unsignedBigInteger(
                    'id_material'
                );

                $table->decimal(
                    'quantidade_por_unidade',
                    12,
                    3
                );

                $table->timestamps();

                $table->foreign(
                    'id_variacao'
                )
                    ->references(
                        'id_variacao'
                    )
                    ->on(
                        'produto_variacoes'
                    )
                    ->restrictOnDelete();

                $table->foreign(
                    'id_material'
                )
                    ->references(
                        'id_material'
                    )
                    ->on('materiais')
                    ->restrictOnDelete();

                $table->unique([
                    'id_variacao',
                    'id_material',
                ]);
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Movimentações
        |--------------------------------------------------------------------------
        |
        | Mantemos id_tecido temporariamente.
        | id_material será usado pela nova estrutura.
        |
        */

        Schema::table(
            'movimentacoes',
            function (Blueprint $table) {
                $table->unsignedBigInteger(
                    'id_material'
                )
                    ->nullable()
                    ->after('id_tecido');

                $table->foreign(
                    'id_material'
                )
                    ->references(
                        'id_material'
                    )
                    ->on('materiais')
                    ->restrictOnDelete();
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Migrar tecidos existentes para materiais
        |--------------------------------------------------------------------------
        |
        | Mantemos o mesmo ID:
        |
        | id_tecido 5 -> id_material 5
        |
        | Isso facilita a migração das movimentações e fichas.
        |
        */

        $tecidos = DB::table(
            'tecidos'
        )
            ->orderBy('id_tecido')
            ->get();

        foreach ($tecidos as $tecido) {
            DB::table(
                'materiais'
            )->insert([
                'id_material' =>
                    $tecido->id_tecido,

                'nome_material' =>
                    $tecido->nome_tecido,

                'categoria' =>
                    'tecido',

                'tipo_material' =>
                    $tecido->tipo_tecido,

                'cor' =>
                    $tecido->cor,

                'unidade_medida' =>
                    'm',

                'quantidade' =>
                    $tecido
                        ->quantidade_metros,

                'estoque_minimo' =>
                    0,

                'id_fornecedor' =>
                    $tecido
                        ->id_fornecedor,

                'created_at' =>
                    $tecido->created_at,

                'updated_at' =>
                    $tecido->updated_at,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Migrar fichas de consumo existentes
        |--------------------------------------------------------------------------
        */

        $composicoes = DB::table(
            'produto_variacao_tecidos'
        )
            ->orderBy('id_composicao')
            ->get();

        foreach ($composicoes as $composicao) {
            DB::table(
                'produto_variacao_materiais'
            )->insert([
                'id_composicao' =>
                    $composicao
                        ->id_composicao,

                'id_variacao' =>
                    $composicao
                        ->id_variacao,

                'id_material' =>
                    $composicao
                        ->id_tecido,

                'quantidade_por_unidade' =>
                    $composicao
                        ->metros_por_unidade,

                'created_at' =>
                    $composicao
                        ->created_at,

                'updated_at' =>
                    $composicao
                        ->updated_at,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Relacionar movimentações antigas
        |--------------------------------------------------------------------------
        */

        DB::table(
            'movimentacoes'
        )
            ->whereNotNull(
                'id_tecido'
            )
            ->update([
                'id_material' =>
                    DB::raw(
                        'id_tecido'
                    ),
            ]);
    }

    public function down(): void
    {
        Schema::table(
            'movimentacoes',
            function (Blueprint $table) {
                $table->dropForeign([
                    'id_material',
                ]);

                $table->dropColumn(
                    'id_material'
                );
            }
        );

        Schema::dropIfExists(
            'produto_variacao_materiais'
        );

        Schema::dropIfExists(
            'materiais'
        );
    }
};
