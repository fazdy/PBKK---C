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
