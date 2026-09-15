<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CancelamentoOrdemProducaoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ComposicaoProdutoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FluxoProducaoController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MovimentacaoController;
use App\Http\Controllers\OrdemProducaoController;
use App\Http\Controllers\OrdemProducaoItemController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\ProdutoVariacaoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\UsuarioController;
use App\Http\Middleware\AdministradorMiddleware;
use Illuminate\Support\Facades\Route;

Route::get(
    '/login',
    [AuthController::class, 'showLogin']
)->name('login');

Route::post(
    '/login',
    [AuthController::class, 'login']
)->name('login.autenticar');

Route::middleware('auth')->group(function () {

    Route::get(
        '/',
        [DashboardController::class, 'index']
    )->name('dashboard');

    Route::post(
        '/logout',
        [AuthController::class, 'logout']
    )->name('logout');

    Route::resource(
        'fornecedores',
        FornecedorController::class
    )
        ->except(['show'])
        ->parameters([
            'fornecedores' => 'fornecedor',
        ]);

    Route::post(
        '/materiais/{material}/inativar',
        [
            MaterialController::class,
            'inativar',
        ]
    )->name(
        'materiais.inativar'
    );

    Route::post(
        '/materiais/{material}/reativar',
        [
            MaterialController::class,
            'reativar',
        ]
    )->name(
        'materiais.reativar'
    );

    Route::resource(
        'materiais',
        MaterialController::class
    )
        ->except(['show'])
        ->parameters([
            'materiais' => 'material',
        ]);

    Route::resource(
        'clientes',
        ClienteController::class
    )
        ->except(['show'])
        ->parameters([
            'clientes' => 'cliente',
        ]);

    Route::post(
        '/produtos/{produto}/inativar',
        [
            ProdutoController::class,
            'inativar',
        ]
    )->name(
        'produtos.inativar'
    );

    Route::post(
        '/produtos/{produto}/reativar',
        [
            ProdutoController::class,
            'reativar',
        ]
    )->name(
        'produtos.reativar'
    );

    Route::resource(
        'produtos',
        ProdutoController::class
    )
        ->except(['show'])
        ->parameters([
            'produtos' => 'produto',
        ]);

    Route::get(
        '/produtos/{produto}/variacoes',
        [
            ProdutoVariacaoController::class,
            'index',
        ]
    )->name(
        'produtos.variacoes.index'
    );

    Route::get(
        '/produtos/{produto}/variacoes/create',
        [
            ProdutoVariacaoController::class,
            'create',
        ]
    )->name(
        'produtos.variacoes.create'
    );

    Route::post(
        '/produtos/{produto}/variacoes',
        [
            ProdutoVariacaoController::class,
            'store',
        ]
    )->name(
        'produtos.variacoes.store'
    );

    Route::post(
        '/produtos/{produto}/variacoes/inativar-todas',
        [
            ProdutoVariacaoController::class,
            'inativarTodas',
        ]
    )->name(
        'produtos.variacoes.inativarTodas'
    );

    Route::post(
        '/produtos/{produto}/variacoes/reativar-todas',
        [
            ProdutoVariacaoController::class,
            'reativarTodas',
        ]
    )->name(
        'produtos.variacoes.reativarTodas'
    );

    Route::get(
        '/produtos/{produto}/variacoes/{variacao}/edit',
        [
            ProdutoVariacaoController::class,
            'edit',
        ]
    )->name(
        'produtos.variacoes.edit'
    );

    Route::put(
        '/produtos/{produto}/variacoes/{variacao}',
        [
            ProdutoVariacaoController::class,
            'update',
        ]
    )->name(
        'produtos.variacoes.update'
    );

    Route::post(
        '/produtos/{produto}/variacoes/{variacao}/inativar',
        [
            ProdutoVariacaoController::class,
            'inativar',
        ]
    )->name(
        'produtos.variacoes.inativar'
    );

    Route::post(
        '/produtos/{produto}/variacoes/{variacao}/reativar',
        [
            ProdutoVariacaoController::class,
            'reativar',
        ]
    )->name(
        'produtos.variacoes.reativar'
    );

    Route::delete(
        '/produtos/{produto}/variacoes/{variacao}',
        [
            ProdutoVariacaoController::class,
            'destroy',
        ]
    )->name(
        'produtos.variacoes.destroy'
    );

    Route::get(
        '/produtos/{produto}/variacoes/{variacao}/composicao',
        [
            ComposicaoProdutoController::class,
            'index',
        ]
    )->name(
        'produtos.variacoes.composicao.index'
    );

    Route::post(
        '/produtos/{produto}/variacoes/{variacao}/composicao',
        [
            ComposicaoProdutoController::class,
            'store',
        ]
    )->name(
        'produtos.variacoes.composicao.store'
    );

    Route::delete(
        '/produtos/{produto}/variacoes/{variacao}/composicao/{composicao}',
        [
            ComposicaoProdutoController::class,
            'destroy',
        ]
    )->name(
        'produtos.variacoes.composicao.destroy'
    );

    Route::resource(
        'movimentacoes',
        MovimentacaoController::class
    )->only([
        'index',
        'create',
        'store',
    ]);

    Route::get(
        '/ordens-producao',
        [
            OrdemProducaoController::class,
            'index',
        ]
    )->name('ordens.index');

    Route::get(
        '/ordens-producao/create',
        [
            OrdemProducaoController::class,
            'create',
        ]
    )->name('ordens.create');

    Route::post(
        '/ordens-producao',
        [
            OrdemProducaoController::class,
            'store',
        ]
    )->name('ordens.store');

    Route::get(
        '/ordens-producao/{ordem}',
        [
            OrdemProducaoController::class,
            'show',
        ]
    )->name('ordens.show');

    Route::get(
        '/ordens-producao/{ordem}/edit',
        [
            OrdemProducaoController::class,
            'edit',
        ]
    )->name('ordens.edit');

    Route::put(
        '/ordens-producao/{ordem}',
        [
            OrdemProducaoController::class,
            'update',
        ]
    )->name('ordens.update');

    Route::delete(
        '/ordens-producao/{ordem}',
        [
            OrdemProducaoController::class,
            'destroy',
        ]
    )->name('ordens.destroy');

    Route::get(
        '/ordens-producao/{ordem}/cancelar',
        [
            CancelamentoOrdemProducaoController::class,
            'show',
        ]
    )->name('ordens.cancelar.form');

    Route::post(
        '/ordens-producao/{ordem}/cancelar',
        [
            CancelamentoOrdemProducaoController::class,
            'cancelar',
        ]
    )->name('ordens.cancelar');

    Route::post(
        '/ordens-producao/{ordem}/iniciar',
        [
            FluxoProducaoController::class,
            'iniciar',
        ]
    )->name('ordens.iniciar');

    Route::get(
        '/ordens-producao/{ordem}/itens/create',
        [
            OrdemProducaoItemController::class,
            'create',
        ]
    )->name('ordens.itens.create');

    Route::post(
        '/ordens-producao/{ordem}/itens',
        [
            OrdemProducaoItemController::class,
            'store',
        ]
    )->name('ordens.itens.store');

    Route::get(
        '/ordens-producao/{ordem}/itens/{item}/edit',
        [
            OrdemProducaoItemController::class,
            'edit',
        ]
    )->name('ordens.itens.edit');

    Route::put(
        '/ordens-producao/{ordem}/itens/{item}',
        [
            OrdemProducaoItemController::class,
            'update',
        ]
    )->name('ordens.itens.update');

    Route::delete(
        '/ordens-producao/{ordem}/itens/{item}',
        [
            OrdemProducaoItemController::class,
            'destroy',
        ]
    )->name('ordens.itens.destroy');

    Route::post(
        '/ordens-producao/{ordem}/itens/{item}/avancar',
        [
            FluxoProducaoController::class,
            'avancar',
        ]
    )->name('ordens.itens.avancar');

    Route::post(
        '/ordens-producao/{ordem}/itens/{item}/reverter',
        [
            FluxoProducaoController::class,
            'reverter',
        ]
    )->name('ordens.itens.reverter');

    Route::middleware(
        AdministradorMiddleware::class
    )->group(function () {

        Route::resource(
            'usuarios',
            UsuarioController::class
        )
            ->except(['show'])
            ->parameters([
                'usuarios' => 'usuario',
            ]);

        Route::get(
            '/relatorios',
            [
                RelatorioController::class,
                'index',
            ]
        )->name(
            'relatorios.index'
        );
    });
});
