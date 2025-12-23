<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\HttpServer\Router\Router;

use App\Module\Account\Presentation\Controller\UserController;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');

Router::get('/favicon.ico', function () {
    return '';
});

// Account Module Routes
Router::addGroup('/api/v1/accounts', function () {
    Router::post('/users', [UserController::class, 'store']);
    Router::get('/{userId}', [UserController::class, 'show']);
    // Router::get('/{id}/balance', 'UserController@balance');
});

// // Transaction Module Routes
// Router::addGroup('/api/v1/transactions', function () {
//     Router::post('/transfer', 'App\Module\Transaction\Presentation\Controller\TransactionController@transfer');
//     Router::get('/{id}', 'App\Module\Transaction\Presentation\Controller\TransactionController@show');
//     Router::get('/account/{accountId}', 'App\Module\Transaction\Presentation\Controller\TransactionController@listByAccount');
// });