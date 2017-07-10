<?php

namespace instantjay\oauthphp\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

abstract class Provider {
    protected $appId;
    protected $appSecret;
    protected $accessToken;
    protected $callbackUri;

    /**
     * @param $uri string
     * @param $params array
     * @return Response
     */
    public function request($uri, $params) {
        $client = new Client();
        $response = $client->request('GET', $uri, $params);
        return $response;
    }

    public abstract function requestTempCode($callbackUri, $state = null);
    public abstract function requestAccessToken($code, $callbackUri);
}