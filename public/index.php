<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../src/config/db.php';

require '../src/auth/auth.php';

$app = new \Slim\App;

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});


// Customer Routes
require '../src/routes/customers.php';
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

$app->run();