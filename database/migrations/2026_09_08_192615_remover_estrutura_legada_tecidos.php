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
        | Movimentações
        |--------------------------------------------------------------------------
        |
        | id_material substituiu definitivamente id_tecido.
        |
        */

        Schema::table(
            'movimentacoes',
            function (Blueprint $table) {
                $table->dropForeign([
                    'id_tecido',
                ]);

                $table->dropColumn(
                    'id_tecido'
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Estruturas antigas
        |--------------------------------------------------------------------------
        */

        Schema::dropIfExists(
            'produto_variacao_tecidos'
        );

        Schema::dropIfExists(
            'tecidos'
        );
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Restaurar tabela de tecidos
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'tecidos',
            function (Blueprint $table) {
                $table->id(
                    'id_tecido'
                );

                $table->string(
                    'nome_tecido'
                );

                $table->string(
                    'tipo_tecido'
                );

                $table->string(
                    'cor'
                );

                $table->decimal(
                    'quantidade_metros',
                    10,
                    2
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
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Restaurar ficha antiga
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'produto_variacao_tecidos',
            function (Blueprint $table) {
                $table->id(
                    'id_composicao'
                );

                $table->unsignedBigInteger(
                    'id_variacao'
                );

                $table->unsignedBigInteger(
                    'id_tecido'
                );

                $table->decimal(
                    'metros_por_unidade',
                    10,
                    2
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
                    'id_tecido'
                )
                    ->references(
                        'id_tecido'
                    )
                    ->on('tecidos')
                    ->restrictOnDelete();

                $table->unique([
                    'id_variacao',
                    'id_tecido',
                ]);
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Restaurar id_tecido nas movimentações
        |--------------------------------------------------------------------------
        */

        Schema::table(
            'movimentacoes',
            function (Blueprint $table) {
                $table->unsignedBigInteger(
                    'id_tecido'
                )
                    ->nullable()
                    ->after(
                        'id_variacao'
                    );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Reconstruir tecidos a partir de materiais
        |--------------------------------------------------------------------------
        */

        $materiais = DB::table(
            'materiais'
        )
            ->where(
                'categoria',
                'tecido'
            )
            ->orderBy(
                'id_material'
            )
            ->get();

        foreach ($materiais as $material) {
            DB::table(
                'tecidos'
            )->insert([
                'id_tecido' =>
                    $material
                        ->id_material,

                'nome_tecido' =>
                    $material
                        ->nome_material,

                'tipo_tecido' =>
                    $material
                        ->tipo_material
                    ?? 'Não informado',

                'cor' =>
                    $material->cor
                    ?? 'Não informada',

                'quantidade_metros' =>
                    round(
                        (float)
                        $material
                            ->quantidade,
                        2
                    ),

                'id_fornecedor' =>
                    $material
                        ->id_fornecedor,

                'created_at' =>
                    $material
                        ->created_at,

                'updated_at' =>
                    $material
                        ->updated_at,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Reconstruir fichas antigas apenas para tecidos
        |--------------------------------------------------------------------------
        */

        $composicoes =
            DB::table(
                'produto_variacao_materiais'
            )
                ->join(
                    'materiais',
                    'produto_variacao_materiais.id_material',
                    '=',
                    'materiais.id_material'
                )
                ->where(
                    'materiais.categoria',
                    'tecido'
                )
                ->select(
                    'produto_variacao_materiais.*'
                )
                ->orderBy(
                    'produto_variacao_materiais.id_composicao'
                )
                ->get();

        foreach (
            $composicoes
            as $composicao
        ) {
            DB::table(
                'produto_variacao_tecidos'
            )->insert([
                'id_composicao' =>
                    $composicao
                        ->id_composicao,

                'id_variacao' =>
                    $composicao
                        ->id_variacao,

                'id_tecido' =>
                    $composicao
                        ->id_material,

                'metros_por_unidade' =>
                    round(
                        (float)
                        $composicao
                            ->quantidade_por_unidade,
                        2
                    ),

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
        | Reconstruir vínculos das movimentações
        |--------------------------------------------------------------------------
        */

        $idsTecidos = DB::table(
            'materiais'
        )
            ->where(
                'categoria',
                'tecido'
            )
            ->pluck(
                'id_material'
            );

        DB::table(
            'movimentacoes'
        )
            ->whereIn(
                'id_material',
                $idsTecidos
            )
            ->update([
                'id_tecido' =>
                    DB::raw(
                        'id_material'
                    ),
            ]);

        Schema::table(
            'movimentacoes',
            function (Blueprint $table) {
                $table->foreign(
                    'id_tecido'
                )
                    ->references(
                        'id_tecido'
                    )
                    ->on('tecidos')
                    ->restrictOnDelete();
            }
        );
    }
};
