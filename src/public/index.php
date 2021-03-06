<?php

// User types from PSR-7
use DI\Container;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;
require_once '../../vendor/jasig/phpcas/source/CAS.php';

// Require the Composer autoload file to load the dependencies
require __DIR__ . '/../../vendor/autoload.php';

// Define constants as part of the application
// These can possibly be replaced by a config file
define('TEMPLATE_DIR', '../templates/');
define('CACHE_DIR', '../../cache/');

// Create the DI container
$container = new Container();

// Set as the active container
AppFactory::setContainer($container);

// Create app
$app = AppFactory::create();

// Setup the middleware
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true); // Must go last

// Register twig-view component on container
// This sets up the templating engine so that we can use it later
$container->set('view', function () {
   $prod = getenv("VOLRPI_PROD") != FALSE;
   return new \Slim\Views\Twig(TEMPLATE_DIR, [
      'cache' => $prod ? CACHE_DIR : false
   ]);
});

// CAS Connection information
phpCAS::client(CAS_VERSION_2_0, 'cas-auth.rpi.edu/cas', 443, '');
// Currently does not check CA certificate, need to fix later
// phpCAS::setCasServerCACert($cas_server_ca_cert_path);
phpCAS::setNoCasServerValidation();

// Return username if logged in, empty string otherwise
function getUsername(){
  $username = '';
  if(phpCAS::isAuthenticated()){
    $username = phpCAS::getUser();
  }
  return strtolower($username);
}

// The handler for the main index page
$app->get('/', function (Request $request, Response $response, array $args) {
   return $this->get('view')->render($response, 'index.html', ['username' => getUsername()]);
})->setName('index');

// The handler for the organizations page
$app->get('/organizations', function (Request $request, Response $response, array $args) {
   return $this->get('view')->render($response, 'organizations.html', ['username' => getUsername()]);
})->setName('organizations');

// The handler for the leaderboard page
$app->get('/leaderboard', function (Request $request, Response $response, array $args) {
   return $this->get('view')->render($response, 'leaderboard.html', ['username' => getUsername()]);
})->setName('leaderboard');

// Login functionality
$app->get('/login', function (Request $request, Response $response, array $args) {
   phpCAS::forceAuthentication();
   return $response->withHeader('Location', '/')->withStatus(301);
})->setName('login');

// Logout functionality
$app->get('/logout', function (Request $request, Response $response, array $args) {
   phpCAS::logout();
})->setName('logout');

// The handler for the user page
$app->get('/user/{user}', function (Request $request, Response $response, array $args) {
   return $this->get('view')->render($response, 'user.html', ['username' => getUsername(), 'user' => $args['user']]);
})->setName('user');


// Run the application
$app->run();

?>
