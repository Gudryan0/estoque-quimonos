<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'ordens_producao',
            function (Blueprint $table) {
                $table->text(
                    'motivo_cancelamento'
                )->nullable();

                $table->timestamp(
                    'cancelada_em'
                )->nullable();

                $table->unsignedBigInteger(
                    'id_usuario_cancelamento'
                )->nullable();

                $table->foreign(
                    'id_usuario_cancelamento'
                )
                    ->references('id')
                    ->on('users')
                    ->restrictOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'ordens_producao',
            function (Blueprint $table) {
                $table->dropForeign([
                    'id_usuario_cancelamento',
                ]);

                $table->dropColumn([
                    'motivo_cancelamento',
                    'cancelada_em',
                    'id_usuario_cancelamento',
                ]);
            }
        );
    }
};
