<?php 

require_once __DIR__.'/../../vendor/slim/slim/Slim/Middleware.php';

class Authentication extends Slim\Middleware
{
    protected $valid_app_ids;

    public function __construct()
    {
        define("WEB_APP_ID", "45310f69-56e3-4b87-8426-e5c0c87b929d");
        $this->valid_app_ids = array(WEB_APP_ID);
    }

    public function call()
    {
        $app = \Slim\Slim::getInstance();
        $app->response()->header('Content-Type', 'application/json');
        
        $headers = $app->request()->headers();
        
        if(!isset($headers['APP_ID']) || !in_array($headers['APP_ID'], $this->valid_app_ids)) {
            $res = $this->app->response();
            $res->status(401);
            $res->body('Go get a ticket!');
        } else {
            $this->next->call();
        }
    }
}

?>