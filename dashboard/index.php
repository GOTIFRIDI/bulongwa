<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host']   = 'localhost';
$config['db']['user']   = 'user';
$config['db']['pass']   = 'password';
$config['db']['dbname'] = 'exampleapp';

$app = new \Slim\App(['settings' => $config]);

$container = $app->getContainer();

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("Hello");

    return $response;
});

$app->run();
