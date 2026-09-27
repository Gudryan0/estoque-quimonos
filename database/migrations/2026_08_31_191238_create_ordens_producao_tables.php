
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Ordem de produção:
         * pode reunir lotes de diferentes clientes.
         */
        Schema::create(
            'ordens_producao',
            function (Blueprint $table) {
                $table->id('id_ordem');

                $table->string('codigo')->unique();

                $table->unsignedBigInteger(
                    'id_usuario_criador'
                );

                $table->enum('status', [
                    'planejada',
                    'em_producao',
                    'concluida',
                    'cancelada',
                ])->default('planejada');

                $table->date('data_prevista')
                    ->nullable();

                $table->text('observacao')
                    ->nullable();

                $table->timestamps();

                $table->foreign('id_usuario_criador')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            }
        );

        /*
         * Cada item representa um lote produtivo.
         *
         * Produto, cor, cliente, costureiro,
         * especificações e etapa são comuns às
         * variações que compõem o lote.
         */
        Schema::create(
            'ordem_producao_itens',
            function (Blueprint $table) {
                $table->id('id_item');

                $table->unsignedBigInteger(
                    'id_ordem'
                );

                $table->unsignedBigInteger(
                    'id_produto'
                );

                $table->unsignedBigInteger(
                    'id_cliente'
                )->nullable();

                $table->string(
                    'cliente_nome'
                )->nullable();

                $table->string('cor');

                /*
                 * Total calculado a partir das
                 * quantidades das variações.
                 *
                 * Não será informado separadamente
                 * pelo usuário.
                 */
                $table->unsignedInteger(
                    'quantidade'
                );

                $table->enum('etapa_atual', [
                    'corte',
                    'bordado',
                    'costura',
                    'finalizacao',
                    'concluido',
                ])->default('corte');

                $table->text(
                    'descricao_bordado'
                )->nullable();

                $table->string(
                    'cor_linha'
                )->nullable();

                $table->string(
                    'etiqueta'
                )->nullable();

                $table->string(
                    'posicao_etiqueta'
                )->nullable();

                $table->string(
                    'responsavel_costura'
                )->nullable();

                $table->text(
                    'observacao'
                )->nullable();

                $table->timestamps();

                $table->foreign('id_ordem')
                    ->references('id_ordem')
                    ->on('ordens_producao')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreign('id_produto')
                    ->references('id_produto')
                    ->on('produtos')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->foreign('id_cliente')
                    ->references('id_cliente')
                    ->on('clientes')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();

                $table->index('etapa_atual');

                $table->index([
                    'id_produto',
                    'cor',
                ]);
            }
        );

        /*
         * Quantidades por variação.
         *
         * Um lote pode possuir vários tamanhos,
         * mas cada variação aparece somente uma
         * vez dentro dele.
         */
        Schema::create(
            'ordem_producao_item_variacoes',
            function (Blueprint $table) {
                $table->id('id_item_variacao');

                $table->unsignedBigInteger(
                    'id_item'
                );

                $table->unsignedBigInteger(
                    'id_variacao'
                );

                $table->unsignedInteger(
                    'quantidade'
                );

                $table->timestamps();

                $table->foreign('id_item')
                    ->references('id_item')
                    ->on('ordem_producao_itens')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreign('id_variacao')
                    ->references('id_variacao')
                    ->on('produto_variacoes')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->unique([
                    'id_item',
                    'id_variacao',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'ordem_producao_item_variacoes'
        );

        Schema::dropIfExists(
            'ordem_producao_itens'
        );

        Schema::dropIfExists(
            'ordens_producao'
        );
    }
};
