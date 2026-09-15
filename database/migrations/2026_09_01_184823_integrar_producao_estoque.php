<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'produto_variacao_tecidos',
            function (Blueprint $table) {
                $table->id('id_composicao');

                $table->unsignedBigInteger('id_variacao');

                $table->unsignedBigInteger('id_tecido');

                $table->decimal(
                    'metros_por_unidade',
                    10,
                    2
                );

                $table->timestamps();

                $table->foreign('id_variacao')
                    ->references('id_variacao')
                    ->on('produto_variacoes')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->foreign('id_tecido')
                    ->references('id_tecido')
                    ->on('tecidos')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->unique([
                    'id_variacao',
                    'id_tecido',
                ]);
            }
        );

        Schema::table(
            'ordem_producao_itens',
            function (Blueprint $table) {
                $table->timestamp(
                    'materiais_consumidos_at'
                )
                    ->nullable()
                    ->after('etapa_atual');

                $table->timestamp(
                    'produto_estoque_adicionado_at'
                )
                    ->nullable()
                    ->after('materiais_consumidos_at');
            }
        );

        Schema::table(
            'movimentacoes',
            function (Blueprint $table) {
                $table->unsignedBigInteger(
                    'id_ordem'
                )
                    ->nullable()
                    ->after('id_usuario');

                $table->unsignedBigInteger(
                    'id_item_producao'
                )
                    ->nullable()
                    ->after('id_ordem');

                $table->string(
                    'origem',
                    50
                )
                    ->default('manual')
                    ->after('tipo_movimentacao');

                $table->foreign('id_ordem')
                    ->references('id_ordem')
                    ->on('ordens_producao')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->foreign('id_item_producao')
                    ->references('id_item')
                    ->on('ordem_producao_itens')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->index('origem');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'movimentacoes',
            function (Blueprint $table) {
                $table->dropForeign([
                    'id_item_producao',
                ]);

                $table->dropForeign([
                    'id_ordem',
                ]);

                $table->dropIndex([
                    'origem',
                ]);

                $table->dropColumn([
                    'id_ordem',
                    'id_item_producao',
                    'origem',
                ]);
            }
        );

        Schema::table(
            'ordem_producao_itens',
            function (Blueprint $table) {
                $table->dropColumn([
                    'materiais_consumidos_at',
                    'produto_estoque_adicionado_at',
                ]);
            }
        );

        Schema::dropIfExists(
            'produto_variacao_tecidos'
        );
    }
};
