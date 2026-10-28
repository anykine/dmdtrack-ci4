<?php

use App\Filters\AuthFilter;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Explicit routes only (CI4's safer default). Each ported module adds its routes here.
$routes->setAutoRoute(false);
$routes->setTranslateURIDashes(true);

// Until Welcome/Individual are ported, the home page sends people to Phenotype.
$routes->addRedirect('/', 'phenotype');

// Auth (from CI3 User controller)
$routes->match(['GET', 'POST'], 'login', 'User::login');
$routes->get('logout', 'User::logout');

// Public AJAX (CSRF is skipped for ajax/* in Config/Filters.php, as in CI3)
$routes->post('ajax/set_session_language', 'Ajax::setSessionLanguage');

// Logged-in pages (CI3 Private_Controller)
$routes->group('', ['filter' => AuthFilter::class], static function (RouteCollection $routes): void {
    // Phenotype
    $routes->match(['GET', 'POST'], 'phenotype', 'Phenotype::index');
    $routes->match(['GET', 'POST'], 'phenotype/view', 'Phenotype::index'); // old CI3 URL
    $routes->match(['GET', 'POST'], 'phenotype/add', 'Phenotype::add');
    $routes->match(['GET', 'POST'], 'phenotype/edit/(:num)', 'Phenotype::edit/$1');
    $routes->post('phenotype/delete/(:num)', 'Phenotype::delete/$1');

    // Individual: only the select2 lookups used by the Phenotype form are ported so far
    $routes->get('individual/GetIndividualSearch', 'Individual::GetIndividualSearch');
    $routes->get('individual/GetOneIndividualSearch/(:num)', 'Individual::GetOneIndividualSearch/$1');
});
