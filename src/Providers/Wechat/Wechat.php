<?php

namespace instantjay\oauthphp\Providers\Wechat;

use GuzzleHttp\Client;
use function GuzzleHttp\Psr7\build_query;

abstract class Wechat {
    protected $appId;
    protected $appSecret;
    protected $baseUri;

    /**
     * Wechat constructor.
     * @param $appId string
     */
    public function __construct($appId, $appSecret, $baseUri) {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->baseUri = $baseUri;
    }

    /**
     * Forward the user to Wechat's site where he will be presented with the QR code for sign-in.
     *
     * @param $callbackUrl
     * @param $state
     */
    public function authenticate($callbackUrl, $state = null) {
        $url = $this->baseUri.'https://open.weixin.qq.com/connect/qrconnect';

        $params = [
            'appid' => $this->appId,
            'redirect_uri' => urlencode($callbackUrl),
            'response_type' => 'code',
            'scope' => 'snsapi_login',
            'state' => $state
        ];

        $urlParams = build_query($params);

        $completeUrl = $url.'?'.$urlParams;

        header('Location: '.$completeUrl);
        die;
    }

    /**
     * @param $params
     * @return string|null
     */
    public function parseAuthenticationResponse($params) {
        if(empty($params['code']))
            return null;

        return $params['code'];
    }

    /**
     * http://admin.wechat.com/wiki/index.php?title=Access_token
     *
     * @param $code
     * @return \Psr\Http\Message\StreamInterface
     * @throws \Exception
     */
    public function requestAccessToken($code) {
        $guzzle = new Client();
        $url = $this->baseUri.'/sns/oauth2/access_token';

        $params = [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'code' => $code,
            'grant_type' => 'authorization_code'
        ];

        $response = $guzzle->request('GET', $url, $params);

        if($response->getStatusCode() != 200)
            throw new \Exception('Wechat API call failed.');

        if($response->getHeader('content-type') != 'application/json')
            throw new \Exception('Received response was not JSON.');

        return $response->getBody();
    }

    /**
     * @param $refreshToken
     * @return \Psr\Http\Message\StreamInterface
     */
    public function refreshAccessToken($refreshToken) {
        $uri = $this->baseUri.'/sns/oauth2/refresh_token';

        $params = [
            'appid' => $this->appId,
            'grand_type' => 'refresh_token',
            'refresh_token' => $refreshToken
        ];

        $client = new Client();
        $response = $client->request('GET', $uri, $params);

        return $response->getBody();
    }

    public function requestUser($accessToken, $openId) {
        $uri = $this->baseUri.'/sns/userinfo';

        $params = [
            'access_token' => $accessToken,
            'openid' => $openId
        ];

        $client = new Client();

        $response = $client->request('GET', $uri, $params);

        $user = new WechatUser($response->getBody());

        return $user;
    }
}