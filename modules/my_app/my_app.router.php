<?php
//Define interface class for router
use \Psr\Http\Message\ServerRequestInterface as Request;    //PSR7 ServerRequestInterface   >> Each router file must contains this
use \Psr\Http\Message\ResponseInterface as Response;        //PSR7 ResponseInterface        >> Each router file must contains this

//Define your modules class
use \modules\my_app\MyApp;   //Your main modules class

// Route for /my_app
$app->group('/my_app', function($app) {

    // Get module information
    $app->map(['GET','POST'],'/module-info', function (Request $request, Response $response) {
        $ma = new MyApp();
        $response = $this->httpCache->withEtag($response, $this['etag']('day',5));
        return $response->withJson(json_decode($ma->viewInfo(),true),200,JSON_PRETTY_PRINT);
    });

    // Show index page
    // Try to open browser to http://yourdomain.com/my_app/
    $app->get('/', function (Request $request, Response $response) {
        $data = [
            'welcome' => 'Hello World, this is my_app index page.',
            'message' => 'This is my first app rest api with slim-jwt-skeleton.'
        ];
        return $response->withJson($data,200,JSON_PRETTY_PRINT);
    })->setName("/my_app/");

});
