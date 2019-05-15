<?php

// Initialize Slim App
$app = new \Slim\App(["settings" => $config]);

// Create container
$container = $app->getContainer();
$settings = $container->get('settings');

/**
 * Activating routes in a subfolder
 * Note:
 * - This $container['environment'] doesn't work for NGINX, 
 *      so you still have to set path >> root /var/www/yourdomain.com/public;
 * - Better to comment or delete this $container['environment'] to prevent useless memory PHP-FPM in NGINX
 */
$container['environment'] = function () {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $_SERVER['SCRIPT_NAME'] = dirname(dirname($scriptName)).DIRECTORY_SEPARATOR.basename($scriptName);
    return new Slim\Http\Environment($_SERVER);
};

// Register component Http-cache
$container['httpCache'] = function () {
    return new \Slim\HttpCache\CacheProvider();
};

// Register component Monolog
$container['logger'] = function($container) {
    $logger = new \Monolog\Logger($container['settings']['app']['name']);
    $file_handler = new \Monolog\Handler\StreamHandler(dirname(__DIR__).DIRECTORY_SEPARATOR."logs".DIRECTORY_SEPARATOR."app.log",$container['settings']['app']['log']['level']);
    $formatter = new \Monolog\Formatter\LineFormatter(null, null, false, true);
    $file_handler->setFormatter($formatter);
    $logger->pushHandler($file_handler);
    return $logger;
};

// Override the default Not Found Handler
$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        $data = [
            'status' => 'error',
            'code' => '404',
            'message' => $response->withStatus(404)->getReasonPhrase()
        ];
        return $response->withJson($data,404,JSON_PRETTY_PRINT);
    };
};

// Override the default Not Allowed Handler
$container['notAllowedHandler'] = function ($container) {
    return function ($request, $response, $methods) use ($container) {
        $data = [
            'status' => 'error',
            'code' => '405',
            'message' => $response->withStatus(405)->getReasonPhrase().', method must be one of: ' . implode(', ', $methods)
        ];
        return $response->withJson($data,405,JSON_PRETTY_PRINT);
    };
};

// Override the slim error handler
$container['errorHandler'] = function ($container) {
    return function ($request, $response, $exception) use ($container) {
        $container->logger->addInfo('{ 
"code": '.json_encode($exception->getCode()).', 
"message": '.json_encode($exception->getMessage()).'}',['file'=>$exception->getFile(),'line'=>$exception->getLine()]);
        $response->getBody()->rewind();
        if($container['settings']['displayErrorDetails']){
            $data = [
                'status' => 'error',
                'code' => '500',
                'error_code' => $exception->getCode(),
                'message' => trim($exception->getMessage()),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => explode("\n", $exception->getTraceAsString())
            ];
        } else {
            $data = [
                'status' => 'error',
                'code' => '500',
                'message' => 'Something went wrong!',
            ];
        }
        return $response->withJson($data,500,JSON_PRETTY_PRINT);
    };
};

// Override PHP 7 error handler
$container['phpErrorHandler'] = function ($container) {
    return $container['errorHandler'];
};

// Get base url
$container['base_url'] = function () {
    if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || 
        isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
        return 'https://'.$_SERVER['HTTP_HOST'];
    }
    return 'http://'.$_SERVER['HTTP_HOST'];
};

// Get current url
$container['current_url'] = function ($container) {
    return $container['base_url'].$_SERVER['REQUEST_URI'];
};

// Get client ip
$container['client_ip'] = function(){
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
	$remote  = $_SERVER['REMOTE_ADDR'];
	if(filter_var($client, FILTER_VALIDATE_IP)) {
        $ip = $client;
	} elseif(filter_var($forward, FILTER_VALIDATE_IP)) {
	    $ip = $forward;
	} else {
        $ip = $remote;
	}
	return $ip;
};

/**
 * Is match char (alternative to preg_match)
 * 
 * @param string $match     this is the text to match
 * @param string $string    this is the source text
 * @param string $method    this is the method to match string. There is first|last|any, default is any. 
 * @return bool
 */
$container['isMatchChar'] = function ($container) {
    return function ($match,$string,$method='any') {
        switch(strtolower($method)){
            case 'first':
                if (substr($string, 0, abs(strlen($match))) == $match) return true;
            case 'last':
                if (substr($string, (-1 * abs(strlen($match)))) == $match) return true;
            default:
                if(strpos($string,$match) !== false) return true;
        }
        return false;
    };
};

/**
 * fileSearch with using opendir (very fast)
 * 
 * @param string $dir               this is the full path of directory
 * @param string $ext               this is the extension of file. Default is php extension. Example Regex: $pattern = "/\\.{$ext}$/"; or $pattern = "/\.router.php$/";
 * @param bool $extIsRegex          if set to true then the $ext parameter will be executed as regex way. Default is false.
 * @param string|array $excludedir  this is to stop recursive into spesific sub directories. Default is empty means will recursive all sub directories.
 * 
 * @return array
 */
$container['fileSearch'] = function ($container) {
    return function ($dir, $ext='php',$extIsRegex=false,$excludedir='')  use ($container) {
        $files = [];
        $fh = opendir($dir);
        if($excludedir !== ''){
            if(is_string($excludedir)){
                if (!$container['isMatchChar']($excludedir,$dir)){
                    while (($file = readdir($fh)) !== false) {
                        if($file == '.' || $file == '..') continue;
                        $filepath = $dir . DIRECTORY_SEPARATOR . $file;
                        if (is_dir($filepath))
                            $files = array_merge($files, $container['fileSearch']($filepath, $ext, $extIsRegex, $excludedir));
                        else {
                            if($extIsRegex){
                                if(preg_match($ext, $file)) array_push($files, $filepath);
                            } else {
                                if($container['isMatchChar']($ext,$file,'last')) array_push($files, $filepath);
                            }
                        }
                    }
                }
            } else {
                foreach($excludedir as $dirs){
                    if (!$container['isMatchChar']($dirs,$dir)){
                        while (($file = readdir($fh)) !== false) {
                            if($file == '.' || $file == '..') continue;
                            $filepath = $dirs . DIRECTORY_SEPARATOR . $file;
                            if (is_dir($filepath))
                                $files = array_merge($files, $container['fileSearch']($filepath, $ext, $extIsRegex, $excludedir));
                            else {
                                if($extIsRegex){
                                    if(preg_match($ext, $file)) array_push($files, $filepath);
                                } else {
                                    if($container['isMatchChar']($ext,$file,'last')) array_push($files, $filepath);
                                }
                            }
                        }   
                    }
                }
            }
        } else {
            while (($file = readdir($fh)) !== false) {
                if($file == '.' || $file == '..') continue;
                $filepath = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($filepath))
                    $files = array_merge($files, $container['fileSearch']($filepath, $ext, $extIsRegex, $excludedir));
                else {
                    if($extIsRegex){
                        if(preg_match($ext, $file)) array_push($files, $filepath);
                    } else {
                        if($container['isMatchChar']($ext,$file,'last')) array_push($files, $filepath);
                    }
                }
            }
        }
        closedir($fh);
        return $files;
    };
};

/**
 * etag generator
 * 
 * @param string $type          this is the type of update in etag. The value could be minute|hour|day|content.
 * @param string|int $value     this is the value. For example etag type minute then you can fill it with number.
 * @return string
 */
$container['etag'] = function ($container) {
    return function ($type='minute', $value=1) {
        $type = strtolower($type);
        $interval = true;
        switch($type){
            case 'minute':
                $fix = date('Y-m-d H:');
                $rate = date('i');
                $max = 60;
                if($value>60) $value = 60;
                break;
            case 'hour':
                $fix = date('Y-m-d ');
                $rate = date('H');
                $max = 24;
                if($value>24) $value = 24;
                break;
            case 'day':
                $fix = date('Y-m-');
                $rate = date('d');
                $max = date('t',strtotime(date('Y-m')));
                break;
            default:
                $interval = false;
                if (is_array($value)){
                    return strtolower(md5(json_encode($value)));
                }
                return strtolower(md5($value));
        }
        if($interval){
            $n=0;
            for ($i = 0; $i <= $max; $i+=$value) {
                if($i<=$rate) $n++;
            }
            return strtolower(md5($fix.$n.trim($_SERVER['REQUEST_URI'],'/')));
        }
        return '';
    };
};

// Add middleware JWT Auth
$app->add(new Tuupola\Middleware\JwtAuthentication([
    "header" => $settings['jwt']['header'],
    "regexp" => "/(.*)/",
    "secure" => $settings['jwt']['secure'],
    "relaxed" => $settings['jwt']['relaxed'],
    "secret" => $settings['jwt']['secret'],
    "algorithm" => $settings['jwt']['algorithm'],
    "path" => $settings['jwt']['path'],
    "ignore" => $settings['jwt']['ignore'],
    "logger" => null,
    "error" => function ($response, $arguments) {
        $data = [
            'status' => 'error',
            'code' => '401',
            'message' => $arguments["message"]
        ];
        return $response->withJson($data, 401, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
]));

// Add middleware http-cache for all routes
$app->add(new \Slim\HttpCache\Cache('public',$settings['app']['http']['max-age']));

// Add middleware for cors
$app->add(new \Tuupola\Middleware\CorsMiddleware($settings['cors']));

// Load all modules router files before run
$modrouters = $container['fileSearch'](dirname(__DIR__).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR,'router.php');
foreach ($modrouters as $modrouter) {
    require $modrouter;
}

// Release unecessary memory
unset($modrouters);

$app->run();
