<?php

/** 
 * Configuration Slim
 *
 * @var bool $config['displayErrorDetails']                 to display error details on slim
 * @var bool $config['addContentLengthHeader']              should be set to false. This will allows the web server to set the Content-Length header which makes Slim behave more predictably
 * @var string $config['httpVersion']                       The protocol version used by the Response object. Default is '1.1'. 
 * @var int $config['responseChunkSize']                    Size of each chunk read from the Response body when sending to the browser. Default is 4096
 * @var string $config['outputBuffering']                   If false, then no output buffering is enabled. If 'append' or 'prepend', then any echo or print statements are captured and are either appended or prepended to the Response returned from the route callable. Default is 'append'
 * @var bool $config['determineRouteBeforeAppMiddleware']   When true, the route is calculated before any middleware is executed. This means that you can inspect route parameters in middleware if you need to. Default is false.
 * @var string $config['routerCacheFile']                   This will make performance faster because php will not always recompile router in each request.
 */
$config['displayErrorDetails']                  = true;
$config['addContentLengthHeader']               = false;
$config['httpVersion']                          = '1.1';
$config['responseChunkSize']                    = 4096;
$config['outputBuffering']                      = 'append';
$config['determineRouteBeforeAppMiddleware']    = false;
//$config['routerCacheFile']                    = 'temp/routes.cache.php'; //Just uncomment if you are in production.

/**
 * Configuration App
 * 
 * @var string $config['app']['name']           is the name of your application.
 * @var string $config['app']['language']       is language that we use. Default is en means english.
 * @var string $config['app']['timezone']       is your default php timezone.
 * @var object $config['app']['log']['level']   is the level of logger.
 * @var int $config['app']['http']['max-age']   is the lifetime of http cache.
 */
$config['app']['name']                  = 'slim-jwt-skeleton';
$config['app']['language']              = 'en';
$config['app']['timezone']              = 'Asia/Jakarta';
$config['app']['log']['level']          = \Monolog\Logger::DEBUG;
$config['app']['http']['max-age']       = 604800;

/**
 * Configuration JWT
 * 
 * @var string $config['jwt']['header']         is the headers name of JWT Token.
 * @var bool $config['jwt']['secure']           if this set to true then your website must have SSL.
 * @var string $config['jwt']['secret']         is the JWT secret key. (don't submit your secret key to git)
 * @var array $config['jwt']['relaxed']         is to allow using JWT in non SSL even the secure is true.
 * @var array $config['jwt']['algorithm']       is the JWT algorithm standard.
 * @var string|array $config['jwt']['path']     if you specified the path, then it will require to use JWT Auth. Assume this like folder path.
 * @var string|array $config['jwt']['ignore']   is you specified the ignore path, then there is no JWT Auth. Assume this like folder path.
 * @var int $config['jwt']['expire']            is the JWT Token expired time.
 */
$config['jwt']['header']    = 'X-Token';
$config['jwt']['secure']    = true;
$config['jwt']['secret']    = 'devjavelinee';
$config['jwt']['relaxed']   = ["localhost", "dev.yourdomain.com"];
$config['jwt']['algorithm'] = ["HS256", "HS512", "HS384"];
$config['jwt']['path']      = '/api/';
$config['jwt']['ignore']    = '/api/generate';
$config['jwt']['expire']    = 3600;

/**
 * Configuration Cors
 * 
 * @var array $config['cors']['origin']         is to allow cors from others domain. Default is * means all domain will be allowed.
 * @var string $config['cors']['origin.server'] is the domain name of your server.
 * @var array $config['cors']['methods']        is the request method.
 * @var array $config['cors']['headers.allow']  is to allow custom request headers.
 * @var array $config['cors']['headers.expose'] is to display the headers.
 * @var bool $config['cors']['credentials']     is to tell browsers that credentials is included in request.
 * @var int $config['cors']['cache']            specify cache time if your server having condition cache.
 */
$config['cors']['origin']           = ["*"];
$config['cors']['origin.server']    = "https://yourdomain.com";
$config['cors']['methods']          = ["GET", "POST", "PUT", "PATCH", "DELETE"];
$config['cors']['headers.allow']    = ["X-Requested-With", "Content-Type", "X-Token", "Authorization", "If-Match", "If-Unmodified-Since", "ETag"];
$config['cors']['headers.expose']   = ["ETag"];
$config['cors']['credentials']      = false;
$config['cors']['cache']            = 0;
