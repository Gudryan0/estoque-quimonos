<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'ordem_producao_historicos',
            function (Blueprint $table) {
                $table->id('id_historico');

                $table->unsignedBigInteger('id_ordem');

                $table->unsignedBigInteger('id_item');

                $table->unsignedBigInteger('id_usuario');

                $table->string('acao', 50);

                $table->string('etapa_origem')
                    ->nullable();

                $table->string('etapa_destino');

                $table->text('observacao')
                    ->nullable();

                $table->timestamps();

                $table->foreign('id_ordem')
                    ->references('id_ordem')
                    ->on('ordens_producao')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreign('id_item')
                    ->references('id_item')
                    ->on('ordem_producao_itens')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreign('id_usuario')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->index([
                    'id_ordem',
                    'created_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'ordem_producao_historicos'
        );
    }
};
