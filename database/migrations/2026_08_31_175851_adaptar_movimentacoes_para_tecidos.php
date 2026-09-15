<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimentacoes', function (Blueprint $table) {
            $table->unsignedBigInteger('id_variacao')
                ->nullable()
                ->change();

            $table->unsignedBigInteger('id_tecido')
                ->nullable()
                ->after('id_variacao');

            $table->decimal('quantidade', 10, 2)
                ->change();

            $table->foreign('id_tecido')
                ->references('id_tecido')
                ->on('tecidos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('movimentacoes', function (Blueprint $table) {
            $table->dropForeign(['id_tecido']);
            $table->dropColumn('id_tecido');

            $table->unsignedBigInteger('id_variacao')
                ->nullable(false)
                ->change();

            $table->unsignedInteger('quantidade')
                ->change();
        });
    }
};
