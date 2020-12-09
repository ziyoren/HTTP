<?php
declare(strict_types=1);

/**
 * ZiyoREN Controller
 *
 * Copyright 2020, Jianshan Liao
 * Released under the MIT license
 */

namespace ziyoren\Http;


function request(){
    return Controller::$_ziyo_http_server_request;
}

function response(){
    return Controller::$_ziyo_http_server_response;
}


class Controller
{
    public static $_ziyo_http_server_request;
    public static $_ziyo_http_server_response;

    public function __construct($request, $response){
        self::$_ziyo_http_server_request  = $request;
        self::$_ziyo_http_server_response = $response;
    }

    final public function request()
    {
        return self::$_ziyo_http_server_request;
    }

    final public function response()
    {
        return self::$_ziyo_http_server_response;
    }

}
