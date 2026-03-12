<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

require '../src/config/db.php';
require '../src/auth/auth.php';

$app = AppFactory::create();

$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($req, $handler) {
    $response = $handler->handle($req);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

require '../src/routes/users.php';
require '../src/routes/ofab.php';
require '../src/routes/carga_bizcocho.php';
require '../src/routes/carga_horno_alta.php';
require '../src/routes/carga_formacion.php';
require '../src/routes/stocks.php';
require '../src/routes/revierte_formacion.php';
require '../src/routes/revierte_bizcocho.php';
require '../src/routes/revierte_horno_alta.php';
require '../src/routes/carga_revisacion.php';
require '../src/routes/revierte_revisacion.php';
require '../src/routes/informe_revisacion.php';
require '../src/routes/informe_formacion.php';
require '../src/routes/informe_bizcocho.php';
require '../src/routes/informe_horno_alta.php';
require '../src/routes/precios.php';

$app->run();
