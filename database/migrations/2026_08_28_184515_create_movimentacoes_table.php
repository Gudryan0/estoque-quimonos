<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimentacoes', function (Blueprint $table) {
            $table->id('id_movimentacao');

            $table->unsignedBigInteger('id_variacao');
            $table->unsignedBigInteger('id_usuario');

            $table->enum('tipo_movimentacao', ['entrada', 'saida']);
            $table->unsignedInteger('quantidade');
            $table->text('observacao')->nullable();
            $table->timestamp('data_movimentacao')->useCurrent();

            $table->foreign('id_variacao')
                ->references('id_variacao')
                ->on('produto_variacoes')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('id_usuario')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentacoes');
    }
};
