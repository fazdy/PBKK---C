<?php
use CodeIgniter\Router\RouteCollection;
/** @var RouteCollection $routes */
$routes->setAutoRoute(false);
$routes->get('/', 'HomeController::index', ['as'=>'home']);
$routes->get('catalog', 'HomeController::catalog', ['as'=>'catalog']);
$routes->get('about', 'HomeController::about', ['as'=>'about']);
$routes->get('contact', 'HomeController::contact', ['as'=>'contact']);
$routes->get('register', 'AuthController::register', ['as'=>'register']);
$routes->post('register', 'AuthController::store');
$routes->get('login', 'AuthController::login', ['as'=>'login']);
$routes->post('login', 'AuthController::authenticate');
$routes->post('logout', 'AuthController::logout', ['as'=>'logout', 'filter'=>'auth']);
$routes->get('account', 'AccountController::index', ['as'=>'account', 'filter'=>'auth:customer']);
$routes->group('admin', ['filter'=>'auth:admin'], static function ($routes) {
    $routes->get('/', 'Admin\DashboardController::index', ['as'=>'admin']);
});
$routes->group('operator', ['filter'=>'auth:operator'], static function ($routes) {
    $routes->get('/', 'Operator\DashboardController::index', ['as'=>'operator']);
});

$routes->group('admin', ['filter' => 'auth:admin,operator', 'namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('catalog-dashboard', 'ProductController::dashboard');
    foreach (['products' => 'ProductController', 'categories' => 'CategoryController'] as $path => $controller) {
        $routes->get($path, $controller . '::index');
        $routes->get($path . '/create', $controller . '::create');
        $routes->post($path . '/store', $controller . '::store');
        $routes->get($path . '/edit/(:num)', $controller . '::edit/$1');
        $routes->post($path . '/update/(:num)', $controller . '::update/$1');
        $routes->post($path . '/delete/(:num)', $controller . '::delete/$1');
    }
    $routes->get('products/(:num)', 'ProductController::show/$1');
    $routes->post('products/(:num)/images/(:num)/primary', 'ProductController::primary/$1/$2');
    $routes->post('products/(:num)/images/(:num)/delete', 'ProductController::removeImage/$1/$2');
    $routes->get('inventory', 'ProductController::inventory');
    $routes->post('inventory/(:num)', 'ProductController::stock/$1');
});
