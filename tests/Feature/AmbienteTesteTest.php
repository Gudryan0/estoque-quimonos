<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AmbienteTesteTest extends TestCase
{
    public function test_ambiente_de_testes_usa_sqlite_em_memoria(): void
    {
        $this->assertSame(
            'testing',
            app()->environment()
        );

        $this->assertSame(
            'sqlite',
            config('database.default')
        );

        $this->assertSame(
            'sqlite',
            DB::connection()->getDriverName()
        );

        $this->assertSame(
            ':memory:',
            DB::connection()->getDatabaseName()
        );
    }
}
