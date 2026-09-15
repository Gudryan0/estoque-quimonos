<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produto_variacoes', function (Blueprint $table) {
            $table->id('id_variacao');

            $table->unsignedBigInteger('id_produto');
            $table->string('tamanho');
            $table->string('cor');
            $table->unsignedInteger('quantidade')->default(0);
            $table->unsignedInteger('estoque_minimo')->default(0);

            $table->foreign('id_produto')
                ->references('id_produto')
                ->on('produtos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->unique(['id_produto', 'tamanho', 'cor']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produto_variacoes');
    }
};
