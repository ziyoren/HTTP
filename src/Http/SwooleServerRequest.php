<?php


namespace ziyoren\Http;

use function ziyoren\dump;

class SwooleServerRequest extends ServerRequest
{

    protected static $_request;

    private static $instance;


    public static function getInstance( $request)
    {
        self::$_request = $request;

        if (is_null(self::$instance)) {

            self::initFromSwooleRequest($request);

        }

        return self::$instance;
    }


    protected static function initFromSwooleRequest($request)
    {
        $get = isset($request->get) ? $request->get : [];
        $post = isset($request->post) ? $request->post : [];
        $cookie = isset($request->cookie) ? $request->cookie : [];
        $files = isset($request->files) ? $request->files : [];

        $server = self::initServer($request);

        $headers = [];
        foreach ($request->header as $name => $value) {
            $headers[str_replace('-', '_', $name)] = $value;
        }

        self::$instance = new self(
            $server['REQUEST_METHOD'],
            static::createUriFromGlobal($server),
            [],
            null,
            $server
        );

        self::$instance->getBody()->write($request->rawContent());

        self::$instance->withParsedBody($post)
            ->withQueryParams($get)
            ->withCookieParams($cookie)
            ->withUploadedFiles($files)
            ->withHeaders($headers);

        unset($headers);
        unset($get);
        unset($post);
        unset($cookie);
        unset($files);
    }


    protected static function initServer($request)
    {

        $host = '::1';

        foreach (['host', 'server_addr'] as $name) {
            if (!empty($request->header[$name])) {
                $host = parse_url($request->header[$name], PHP_URL_HOST) ?: $request->header[$name];
            }
        }

        return [
            'REQUEST_METHOD' => $request->server['request_method'],
            'REQUEST_URI' => $request->server['request_uri'],
            'PATH_INFO' => $request->server['path_info'],
            'REQUEST_TIME' => $request->server['request_time'],
            'GATEWAY_INTERFACE' => 'swoole/' . SWOOLE_VERSION,
            // Server
            'SERVER_PROTOCOL' => isset($request->header['server_protocol']) ? $request->header['server_protocol'] : $request->server['server_protocol'],
            'REQUEST_SCHEMA' => isset($request->header['request_scheme']) ? $request->header['request_scheme'] : explode('/', $request->server['server_protocol'])[0],
            'SERVER_NAME' => isset($request->header['server_name']) ? $request->header['server_name'] : $host,
            'SERVER_ADDR' => $host,
            'SERVER_PORT' => isset($request->header['server_port']) ? $request->header['server_port'] : $request->server['server_port'],
            'REMOTE_ADDR' => $request->server['remote_addr'],
            'REMOTE_PORT' => isset($request->header['remote_port']) ? $request->header['remote_port'] : $request->server['remote_port'],
            'QUERY_STRING' => isset($request->server['query_string']) ? $request->server['query_string'] : '',
            // Headers
            'HTTP_HOST' => $host,
            'HTTP_USER_AGENT' => isset($request->header['user-agent']) ? $request->header['user-agent'] : '',
            'HTTP_ACCEPT' => isset($request->header['accept']) ? $request->header['accept'] : '*/*',
            'HTTP_ACCEPT_LANGUAGE' => isset($request->header['accept-language']) ? $request->header['accept-language'] : '',
            'HTTP_ACCEPT_ENCODING' => isset($request->header['accept-encoding']) ? $request->header['accept-encoding'] : '',
            'HTTP_CONNECTION' => isset($request->header['connection']) ? $request->header['connection'] : '',
            'HTTP_CACHE_CONTROL' => isset($request->header['cache-control']) ? $request->header['cache-control'] : '',
        ];
    }



    public function __get($name)
    {
        try{
            return self::$_request->{$name};
        }catch (\Throwable $e){
            dump($e->getMessage());
            return null;
        }
    }


    public function __call($method, $args)
    {
        try{
            return call_user_func_array([self::$_request, $method], $args);
        }catch (\Throwable $e){
            dump($e->getMessage());
            return null;
        }
    }


}