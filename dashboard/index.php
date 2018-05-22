<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\UploadedFile;

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

$container['upload_directory'] = '../uploads';

$app->get('/', function (Request $request, Response $response) {

     $this->logger->addInfo('Dashboard');

     $stmt = $this->db->prepare("SELECT * FROM announcements ORDER BY created_at DESC");
     $stmt->execute();
     $announcements = $stmt->fetchAll();

     $response = $this->view->render($response, 'dashboard.phtml', compact('announcements'));

    return $response;

})->setName('dashboard');

$app->post('/login', function (Request $request, Response $response) {

     $this->logger->addInfo('Login');

     $data = $request->getParsedBody();
     $login_data = [];
     $login_data['email'] = filter_var($data['email'], FILTER_SANITIZE_STRING);
     $login_data['password'] = filter_var($data['password'], FILTER_SANITIZE_STRING);

    return $login_data;
});

$app->post('/announcements', function (Request $request, Response $response) {
    
    $this->logger->addInfo('Create announcement');

    $data = $request->getParsedBody();
    $announcement_data = [];
    $announcement_data['title'] = filter_var($data['title'], FILTER_SANITIZE_STRING);
    
    $uploadedFiles = $request->getUploadedFiles();
    $uploadedFile = $uploadedFiles['file'];
    $directory = $this->get('upload_directory');
    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        $filename = moveUploadedFile($directory, $uploadedFile);
        $announcement_data['path'] = $filename;
    }

    $stmt = $this->db->prepare("INSERT INTO announcements (title, path) VALUES (:title, :path)");
    $stmt->bindParam(':title', $announcement_data['title']);
    $stmt->bindParam(':path', $announcement_data['path']);
    $stmt->execute();

    return $response->withRedirect('/dashboard', 301);
});

$app->patch('/announcements/{id}', function ($request, $response, $args) {
    
    $this->logger->addInfo('Update announcement');

    $id = (int)$args['id'];

    $stmt = $this->db->prepare('SELECT * FROM announcements WHERE id=:id LIMIT 1');
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $row = $stmt->fetch();

    if ($stmt->rowCount() != 1){
        return $response->withStatus(404);
    }

    $data = $request->getParsedBody();
    $announcement_data = [];
    $announcement_data['title'] = filter_var($data['title'], FILTER_SANITIZE_STRING);  

    $uploadedFiles = $request->getUploadedFiles();
    if(isset($uploadedFiles['file'])){
        $uploadedFile = $uploadedFiles['file'];
        $directory = $this->get('upload_directory');
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = moveUploadedFile($directory, $uploadedFile);
            $announcement_data['path'] = $filename;
        }
    }      

    $stmt = $this->db->prepare('UPDATE announcements SET title=:title, path=:path WHERE id=:id');
    $stmt->bindParam(':title', $announcement_data['title']);
    if(isset($announcement_data['path'])){
        $stmt->bindParam(':path', $announcement_data['path']);
    }else{
        $stmt->bindParam(':path', $row['path']);
    }
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    return $response->withRedirect('/dashboard', 301);

})->setName('announcements.update');

$app->delete('/announcements/{id}', function ($request, $response, $args) {
    
    $this->logger->addInfo('Delete announcement');

    $id = (int)$args['id'];

    $stmt = $this->db->prepare('DELETE FROM announcements WHERE id=:id');
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    return $response->withRedirect('/dashboard', 301);

})->setName('announcements.delete');


function moveUploadedFile($directory, UploadedFile $uploadedFile)
{
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}

$app->run();
