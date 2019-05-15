# Slim-JWT-Skeleton

[![Version](https://img.shields.io/packagist/v/aalfiann/slim-jwt-skeleton.svg)](https://packagist.org/packages/aalfiann/slim-jwt-skeleton)
[![Downloads](https://img.shields.io/packagist/dt/aalfiann/slim-jwt-skeleton.svg)](https://packagist.org/packages/aalfiann/slim-jwt-skeleton)
[![License](https://img.shields.io/packagist/l/aalfiann/slim-jwt-skeleton.svg)](https://github.com/aalfiann/slim-jwt-skeleton/blob/HEAD/LICENSE.md)

This is a skeleton to built rest api with slim framework 3 and JWT Auth. 

## Dependencies
- Logger >> monolog/monolog
- HTTP Cache >> slim/http-cache
- Firebase JWT >> firebase/php-jwt
- Slim JWT Auth >> tuupola/slim-jwt-auth
- Cors Middleware >> tuupola/cors-middleware
- ETag Middleware >> aalfiann/slim-etag-middleware

## Installation

Install this package via [Composer](https://getcomposer.org/).
```
composer create-project aalfiann/slim-jwt-skeleton [my-app-name]
```

## Getting Started

### How to generate Token
Send request to https://yourdomain.com/api/generate

```
Method: 
    GET / POST
Header: 
    Content-Type: application/json
Body:
    {
        "userid":"",
        "scope":["get","post","delete","put"]   
    }

Output Response:
{
    "token":"This is jwt token",
    "expire" 1557908861
}
```

### How to test
Send request to https://yourdomain.com/api/
```
Method:
    GET / POST
Header:
    Content-Type: application/json
    X-Token: thisisyourjwttoken generated
```

### How to create new application
- Go to modules directory
- Create new folder `my-app`
- To create routes, you should follow this pattern >> `*.router.php`
- Done

### Example
This is the `my-app.router.php` file
```php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Route for /my-app
$app->group('/my-app', function($app) {

    // Show index page
    // Try to open browser to http://yourdomain.com/my-app/
    $app->get('/', function (Request $request, Response $response) {
        $data = [
            'welcome' => 'Hello World, this is my-app index page.',
            'message' => 'This is my first app rest api with slim-jwt-skeleton.'
        ];
        return $response->withJson($data,200,JSON_PRETTY_PRINT);
    })->setName("/my-app/");

});
```

**Note:**  
- Documentation about `Slim` is available on [slimframework.com](http://slimframework.com).
- This is a forked version from the original [slimphp/Slim-Skeleton](https://github.com/slimphp/Slim-Skeleton).