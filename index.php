<?php

// Following this doc:
// http://docs.slimframework.com/

require_once __DIR__.'/vendor/slim/slim/Slim/Slim.php';
require_once __DIR__.'/ReadBean/rb.php';

require_once __DIR__.'/app/controllers/User.php';
require_once __DIR__.'/app/controllers/Content.php';
require_once __DIR__.'/app/controllers/FeaturedContent.php';

require_once __DIR__.'/app/middlewares/authentication.php';
require_once __DIR__.'/app/middlewares/response_wrapper.php';
require_once __DIR__.'/constants.php';

// ******************************** CONSTANTS ***********************************



// ****************************** DB Connection *********************************

R::setup('mysql:host=162.243.134.41;dbname=mm', 'mmbot', 'mmbotpassword');
R::freeze(true);

// ***************** SLIM App Instantiation and configuration *******************

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

// Add middlewares

$app->add(new ResponseWrapper());
$app->add(new Authentication());    //Authentication middleware. This should be outermost middleware

$app->config('debug', true);
$app->config('cookies.domain', 'marathimultiplex.com');

// ***************************** Configurations *********************************

// Only invoked if mode is "production"
$app->configureMode('production', function () use ($app) {
    $app->config(array(
        'log.enable' => true,
        'debug' => false
    ));
});

// Only invoked if mode is "development"
$app->configureMode('development', function () use ($app) {
    $app->config(array(
        'log.enable' => true,
        'debug' => true
    ));
});

// ******************************* API routes **********************************

$app->get('/user/:id', 'User::getUserById');
$app->post('/user', 'User::addUser');

$app->get('/content/:id', 'Content::getContentById');
$app->get('/featured/content/:list_name', 'FeaturedContent::getFeaturedContent');

// ********************************* RUN ****************************************

$app->run();

?>
