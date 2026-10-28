<?php

use App\Filters\AuthFilter;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Explicit routes only (CI4's safer default). Each ported module adds its routes here.
$routes->setAutoRoute(false);
$routes->setTranslateURIDashes(true);

// Until Welcome is ported, the home page sends people to the Individual list.
$routes->addRedirect('/', 'individual');

// Accounts (CI3 User controller)
$routes->match(['GET', 'POST'], 'login', 'User::login');
$routes->get('logout', 'User::logout');
$routes->match(['GET', 'POST'], 'user/register', 'User::register'); // only when Config\Site::$allowRegistration
$routes->get('user/validate', 'User::validateAccount');
$routes->match(['GET', 'POST'], 'user/forgot', 'User::forgot');

// Public AJAX (CSRF is skipped for ajax/* in Config/Filters.php, as in CI3)
$routes->post('ajax/set_session_language', 'Ajax::setSessionLanguage');

/**
 * The standard routes of a data module:
 *   GET|POST {module}            list (POST = apply filters)
 *   GET|POST {module}/view       old CI3 list URL
 *   GET|POST {module}/add
 *   GET|POST {module}/edit/{id}
 *   POST     {module}/delete/{id}
 */
$crud = static function (RouteCollection $routes, string $module, string $controller): void {
    $routes->match(['GET', 'POST'], $module, "{$controller}::index");
    $routes->match(['GET', 'POST'], "{$module}/view", "{$controller}::index");
    $routes->match(['GET', 'POST'], "{$module}/add", "{$controller}::add");
    $routes->match(['GET', 'POST'], "{$module}/edit/(:num)", "{$controller}::edit/\$1");
    $routes->post("{$module}/delete/(:num)", "{$controller}::delete/\$1");
};

// Logged-in pages (CI3 Private_Controller)
$routes->group('', ['filter' => AuthFilter::class], static function (RouteCollection $routes) use ($crud): void {
    $crud($routes, 'consent', 'Consent');
    $crud($routes, 'contact', 'Contact');
    $crud($routes, 'individual', 'Individual');
    $crud($routes, 'phenotype', 'Phenotype');
    $crud($routes, 'referrer', 'Referrer');
    $crud($routes, 'result', 'Result');
    $crud($routes, 'sample', 'Sample');
    $crud($routes, 'sequencing', 'Sequencing');

    // select2 search boxes (called by dmdtrack.js)
    $routes->get('consent/GetConsentSearch', 'Consent::GetConsentSearch');
    $routes->get('contact/GetContactSearch', 'Contact::GetContactSearch');
    $routes->get('individual/GetIndividualSearch', 'Individual::GetIndividualSearch');
    $routes->get('individual/GetOneIndividualSearch/(:num)', 'Individual::GetOneIndividualSearch/$1');
    $routes->get('referrer/GetReferrerSearch', 'Referrer::GetReferrerSearch');
    $routes->get('sample/GetSampleSearch', 'Sample::GetSampleSearch');

    // Everything about one individual
    $routes->get('indivDetail', 'IndivDetail::index');
    $routes->get('indivDetail/view/(:num)', 'IndivDetail::view/$1');

    // The user's own account
    $routes->match(['GET', 'POST'], 'profile', 'Profile::index');
});
