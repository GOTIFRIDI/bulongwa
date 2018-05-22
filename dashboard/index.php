<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host']   = $_ENV['DB_HOST'];
$config['db']['user']   = $_ENV['DB_USERNAME'];
$config['db']['pass']   = $_ENV['DB_PASSWORD'];
$config['db']['dbname'] = $_ENV['DB_DATABASE'];

$app = new \Slim\App(['settings' => $config]);

$container = $app->getContainer();

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler('../logs/app.log');
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$container['view'] = new \Slim\Views\PhpRenderer('templates/');

$app->get('/', function (Request $request, Response $response) {

     $this->logger->addInfo('Dashboard');

     $stmt = $this->db->prepare("SELECT * FROM announcements ORDER BY created_at DESC");
     $stmt->execute();
     $announcements = $stmt->fetchAll();

     $response = $this->view->render($response, 'dashboard.phtml', compact('announcements'));

    return $response;
});

$app->post('/login', function (Request $request, Response $response) {

     $this->logger->addInfo('Login');

     $data = $request->getParsedBody();
     $login_data = [];
     $login_data['email'] = filter_var($data['email'], FILTER_SANITIZE_STRING);
     $login_data['password'] = filter_var($data['password'], FILTER_SANITIZE_STRING);

    return $login_data;
});

$app->post('/announcements', function (Request $request, Response $response) {
    
    $data = $request->getParsedBody();
    $announcement_data = [];
    $announcement_data['title'] = filter_var($data['title'], FILTER_SANITIZE_STRING);
    $files = $request->getUploadedFiles();
    $files->moveTo('../files');

});

$app->run();
