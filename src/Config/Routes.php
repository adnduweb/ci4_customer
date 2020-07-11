 <?php

    $routes->group(CI_SITE_AREA, ['namespace' => 'Adnduweb\Ci4_customer\Controllers\Admin', 'filter' => 'apiauth'], function ($routes) {
    });



    $locale = '/';
    if (service('Settings')->setting_activer_multilangue == true) {
        $locale = '/{locale}';
    }

    //Espace client
    $routes->get($locale . '/logout', 'FrontAuthenticationController::logout', ['namespace' => '\Adnduweb\Ci4_customer\Controllers\Front', 'as' => 'logout-customer']);
    $routes->get($locale . '/signin', 'FrontAuthenticationController::SignIn', ['namespace' => '\Adnduweb\Ci4_customer\Controllers\Front', 'as' => 'signin']);
    $routes->post($locale . '/signin', 'FrontAuthenticationController::postProcessSignIn', ['namespace' => '\Adnduweb\Ci4_customer\Controllers\Front']);
    $routes->get($locale . '/signup', 'FrontAuthenticationController::SignUp', ['namespace' => '\Adnduweb\Ci4_customer\Controllers\Front']);
    $routes->post($locale . '/signup', 'FrontAuthenticationController::postProcessSignUp', ['namespace' => '\Adnduweb\Ci4_customer\Controllers\Front']);
    $routes->get($locale . '/activate-account-customer', 'FrontAuthenticationController::ActivateAccount', ['namespace' => '\Adnduweb\Ci4_customer\Controllers\Front']);
    $routes->get($locale . '/my-account', 'FrontAccountController::index', ['namespace' => '\Adnduweb\Ci4_customer\Controllers\Front', 'filter' => 'loginCustomer']);
  