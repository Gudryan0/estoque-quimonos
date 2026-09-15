<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tecidos', function (Blueprint $table) {
            $table->id('id_tecido');
            $table->string('nome_tecido');
            $table->string('tipo_tecido');
            $table->string('cor');
            $table->decimal('quantidade_metros', 10, 2);
            $table->unsignedBigInteger('id_fornecedor');

            $table->foreign('id_fornecedor')
                ->references('id_fornecedor')
                ->on('fornecedores')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tecidos');
    }
};
