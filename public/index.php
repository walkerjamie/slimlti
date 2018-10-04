<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use IMSGlobal\LTI\ToolProvider;
use IMSGlobal\LTI\ToolProvider\DataConnector;

require '../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv('/var/www/html/slimlti');
$dotenv->load();
$bugsnag = Bugsnag\Client::make('d057a1ff6f219a43c6db75486b1eacaf');

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host'] = getenv('DB_HOST');
$config['db']['user'] = getenv('DB_USER');
$config['db']['pass'] = getenv('DB_PWD');
$config['db']['dbname'] = getenv('DB_NAME');

$app = new \Slim\App(["settings" => $config]);

$db = mysql_connect($config['db']['host'], $config['db']['user'], $config['db']['pass']);
mysql_select_db($db); 
$db_connector = DataConnector\DataConnector::getDataConnector('mysql', $db);


$container = $app->getContainer();

// Register component on container
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('../resources/views', [
        'cache' => false
    ]);
    
    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    return $view;
};

$app->get('/', function (Request $request, Response $response) {
    return $this->view->render($response, 'home.twig');
});

$app->get('/uri', function ($request, $response){
$uri = $request->getUri();
return $uri->getHost();
});

$app->get('/headers', function ($request, $response){
$headers = $request->getHeaders();
foreach ($headers as $name => $values) {
    echo $name . ": " . implode(", ", $values);
}	
return;
});

$app->get('/hello/{name}', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $response->getBody()->write("Hello, $name");

    return $response;
});

$app->post('/launch', function (Request $request, Response $response) {

//$db = mysql_connect($config['db']['host'], $config['db']['user'], $config['db']['pass']);
//$db = mysql_connect('', '', '');
//mysql_select_db($db);  
      //  $db_connector = DataConnector\DataConnector::getDataConnector('', $db); 

   	$consumer = new ToolProvider\ToolConsumer('testing.edu', $db_connector);
	$consumer->name = 'Testing';
	$consumer->secret = 'ThisIsASecret!';
	$consumer->enabled = TRUE;
	$consumer->save();
	$response->getBody()->write("Consumer Registration...");
    return $response;
});

$app->run();
