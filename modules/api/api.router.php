<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;

// Route for /api
$app->group('/api', function($app) {
    // Show index api page
    // Try to open browser to http://yourdomain.com/api/
    $app->map(['GET','POST'],'/', function (Request $request, Response $response) {
        $token = $request->getAttribute("token");
        if(!empty($token['scope'])){
            if(count(array_intersect($token["scope"], ['get','post'])) > 0) {
                $data = [
                    'welcome' => 'Hello World, this is my-app index page.',
                    'message' => 'This is my first app rest api with slim-jwt-skeleton.'
                ];
                return $response->withJson($data,200,JSON_PRETTY_PRINT);
            }
        }
        $data = [
            'status' => 'error',
            'code' => '403',
            'message' => $response->withStatus(403)->getReasonPhrase()
        ];
        return $response->withJson($data,403,JSON_PRETTY_PRINT);
    })->setName("/api/");

    // Generate JWT Token
    $app->map(["POST","GET"],'/generate', function (Request $request, Response $response) {
        // Get request data JSON 
        $json = $request->getBody();
        $params = json_decode($json, true);
        
        // Create token
        $now = time();
        $expire = time() + $this->settings['jwt']['expire'];
        $payload = [
            "iss" => $_SERVER['SERVER_NAME'],
            "iat" => $now,
            "exp" => $expire,
            "userid" => $params['userid'],
            "scope" => $params['scope']
        ];
        $secret = $this->settings['jwt']['secret'];
        $token = JWT::encode($payload, $secret, "HS512");
        
        // Return token
        $data = [
            'token' => $token,
            'expire' => $expire
        ];
        return $response->withJson($data,200,JSON_PRETTY_PRINT);
    })->setName("/api/generate");
    
});