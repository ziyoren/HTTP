<?php


namespace ziyoren\Http;


use Swoole\Http\Response as SwooleHttpResponse;
use function ziyoren\dump;

class SwooleServerResponse extends Response
{

    protected static $response;

    private static $instance;


    public function __construct($content = '', $statusCode = 200, array $headers = [])
    {
        parent::__construct($content, $statusCode, $headers);

        $swHeaders = self::$response->header;

        if (is_array($swHeaders)) {
            $this->withHeaders(self::$response->header);
        }
    }

    public static function getInstance( $response, $fd )
    {
        $response->detach();

        self::$response = SwooleHttpResponse::create($fd);

        if (is_null(self::$instance)) {

            self::initFromSwooleResponse($response);

        }

        return self::$instance;
    }


    public static function initFromSwooleResponse($response)
    {
        self::$instance = new self();
    }


    public function end($html = '')
    {
        $this->setSwooleHeaders();

        self::$response->status($this->getStatusCode());

        $content = $this->getBody()->getContents();

        if ( empty($content) ) {
            $content = $html;
        }

        self::$response->end($content);
    }


    private function setSwooleHeaders()
    {
        $headers = $this->getHeaders();

        foreach ($headers as $key => $value){
            static::$response->header($key, implode(',', $value));
        }
    }


    public function __get($name)
    {
        try{
            return self::$response->{$name};
        }catch (\Throwable $e){
            dump($e->getMessage());
            return null;
        }
    }


    public function __call($method, $args)
    {
        try{
            dump([$method, $args], 'Response Call: ');
            return call_user_func_array([self::$response, $method], $args);
        }catch (\Throwable $e){
            dump($e->getMessage());
            return null;
        }
    }

}