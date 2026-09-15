<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'produtos',
            function (Blueprint $table) {
                $table
                    ->boolean('ativo')
                    ->default(true)
                    ->after('descricao');
            }
        );

        Schema::table(
            'produto_variacoes',
            function (Blueprint $table) {
                $table
                    ->boolean('ativo')
                    ->default(true)
                    ->after('estoque_minimo');
            }
        );

        Schema::table(
            'materiais',
            function (Blueprint $table) {
                $table
                    ->boolean('ativo')
                    ->default(true)
                    ->after('id_fornecedor');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'produtos',
            function (Blueprint $table) {
                $table->dropColumn(
                    'ativo'
                );
            }
        );

        Schema::table(
            'produto_variacoes',
            function (Blueprint $table) {
                $table->dropColumn(
                    'ativo'
                );
            }
        );

        Schema::table(
            'materiais',
            function (Blueprint $table) {
                $table->dropColumn(
                    'ativo'
                );
            }
        );
    }
};
