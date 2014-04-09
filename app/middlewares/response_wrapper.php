<?php 

require_once __DIR__.'/../../vendor/slim/slim/Slim/Middleware.php';

class ResponseWrapper extends Slim\Middleware
{
    public function __construct()
    {
    }

    public function call()
    {
        $this->next->call();
    
        $app = \Slim\Slim::getInstance();
        $app->response()->header('Content-Type', 'application/json');
        $status = $app->response()->status();
        
        if($status == 200) {
            $response['stat'] = "ok";
            $response['data'] = json_decode($app->response()->body());
        } else {
            $response['stat'] = "fail";
            $response = array_merge($response, json_decode($app->response()->body(), true));
        }
        
        $app->response()->body(json_encode($response));
        //error_log("Response.body: " . $app->response()->body());
    }
}