<?php

namespace Simple\Profiler;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Simple\Profiler\Container;
use DebugBar\StandardDebugBar;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Middleware for profiling purpose
 * 
 */
class Profiler
{
    private $note ='To enable response data you have to change $response on specific endpoint/Action: $response->withJson((string|object|array)) to $response->write((string))';
    
    private $container;

    private static $httpstack;

    /**
     * @param \Simple\Profiler\Container $container
     */
    public function __construct(Container $container) {
        $this->container = $container;        
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, $next)
    {
        if ($this->container->getEloquentManager() !== null) {
            // enable query logs eloquent
            $this->container->getEloquentManager()::connection()->enableQueryLog();
        }

        $next($request, $response);

        $debugbar = new StandardDebugBar();
        $collections = $debugbar->collect();

        // data collections
        $meta = $collections['__meta'];
        $data_request = $collections['request'];
        $php = $collections['php'];
        $time = $collections['time'];
        $memory = $collections['memory'];

        // collect data params & body from slim request
        $dataParams = $request->getQueryParams() ?? '';
        $dataBody = $request->getParsedBody() ?? '';

        $darkMode = $this->container->getDarkMode();

        $eloquentLog = $doctrineLog = null;
        if ($this->container->getEloquentManager() !== null) {
            $eloquentLog = $this->container->getEloquentManager()::getQueryLog();
        }
        if ($this->container->getDoctrineStack() !== null) {
            $doctrineLog = $this->container->getDoctrineStack()->queries;
        }

        // route footprints
        $route = $request->getAttribute('route') ?? '';

        // get response contents
        $response->getBody()->rewind();
        $contents = $response->getBody()->getContents();

        // get action target
        $actions = 'Please enable determineRouteBeforeAppMiddleware';
        if ($route) {
            $actions = $route->getCallable();
            if (is_array($route->getCallable())) {
                $actions = sprintf("%s:%s", get_class($route->getCallable()[0] ?? null), $route->getCallable()[1] ?? null);
            }
        }

        // generate context
        $context = [
            'response' => empty($contents) ? $this->note : $contents,
            'uri' => $meta['uri'],
            'method' => $meta['method'],
            'action' => $actions,
            'dataParams' => json_encode($dataParams, JSON_PRETTY_PRINT),
            'dataBody' => json_encode($dataBody, JSON_PRETTY_PRINT),
            'execTime' => sprintf("%s (%s)", $time['duration_str'], $time['duration']),
            'memUsage' => sprintf("%s (%s)", $memory['peak_usage_str'], $memory['peak_usage']),
            'darkMode' => $darkMode,
            'guzzleHttp' => self::$httpstack,
            'eloquentLog' => $eloquentLog,
            'doctrineLog' => $doctrineLog
        ];

        // reset body.
        $response->getBody()->rewind();

        $responseFormat = $this->container->getResponseFormat();
        switch ($responseFormat) {
            case Container::HTTP_FORMAT:
                $loader = new FilesystemLoader(__DIR__.'/template');
                $twig = new Environment($loader);

                $response->getBody()->write($twig->render('index.twig', $context));
                break;

            case Container::JSON_FORMAT:
                $response->getBody()->write(
                    json_encode($context, JSON_PRETTY_PRINT)
                );
                break;

            default : // nothing todo here
                break;
        }

        return $response->withHeader('Content-Type', $responseFormat);
    }

    public static function guzzleStack()
    {
        if (self::$httpstack === null) {
            self::$httpstack = [];
        }

        $starttime = microtime(true);
        return function(callable $handler) use ($starttime) {
            return function(RequestInterface $request, array $options) use ($handler, $starttime) {
                self::$httpstack[] = [
                    'request' => $request->getUri()->__toString()
                ];
                return $handler($request, $options)->then(
                    function(ResponseInterface $response) use ($starttime)
                    {
                        $endtime = microtime(true);
                        $response->getBody()->rewind();
                        self::$httpstack[count(self::$httpstack) - 1]['response'] = $response->getBody()->getContents();
                        self::$httpstack[count(self::$httpstack) - 1]['time'] = $endtime - $starttime;
                        return $response;
                });
            };
        };
    }
}