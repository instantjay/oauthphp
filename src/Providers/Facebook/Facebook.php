<?php

namespace instantjay\oauthphp\Providers\Facebook;

class Facebook extends Provider {
    const API_URI = 'https://www.facebook.com/v2.9/dialog/oauth';

    public function __construct($appId, $appSecret) {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    public function requestTempCode($callbackUri, $state = null) {
        $params = [
            'client_id' => $this->appId,
            'redirect_uri' => $callbackUri,
            'state' => $state,
            'response_type' => 'code' // 'code' is default value. https://developers.facebook.com/docs/facebook-login/manually-build-a-login-flow
        ];

        $urlParams = http_build_query($params);
        $uri = $callbackUri.'?'.$urlParams;

        header("Location: $uri");
        die;
    }

    public function requestAccessToken($code, $callbackUri) {
        $params = [
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret,
            'redirect_uri' => $callbackUri,
            'code' => $code
        ];

        $response = $this->request(self::API_URI, $params);

        if(!$response->getStatusCode() != 200)
            throw new \Exception();

        $r = $response->getBody();
        return $r['access_token'];
    }
}