<?php

namespace instantjay\oauthphp\Providers\Wechat;

use GuzzleHttp\Client;
use instantjay\oauthphp\Exceptions\RejectedAuthException;

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
     * http://open.wechat.com/cgi-bin/newreadtemplate?t=overseas_open/docs/web/login/login#auth-process
     *
     * @param $callbackUrl
     * @param $scope string snsapi_base, snsapi_login, snsapi_userinfo etc
     * @param $state
     */
    public function authenticate($callbackUrl, $scope = 'snsapi_base', $state = null) {
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize';

        $params = [
            'appid' => $this->appId,
            'redirect_uri' => urlencode($callbackUrl),
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state
        ];

        $urlParams = http_build_query($params);

        $completeUrl = $url.'?'.$urlParams;

        header('Location: '.$completeUrl);
        die;
    }

    /**
     * After user gets sent back from Wechat, use this method to parse the params that get passed along back.
     * Returns the temporary code that can be used to fetch an access token, or throws an exception if something fails (eg. the user rejects the signin).
     *
     * @param $params
     * @return string|null
     * @throws RejectedAuthException
     */
    public function parseAuthenticationResponse($params) {
        if(empty($params['code']))
            throw new RejectedAuthException();

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

    /**
     * @param $accessToken
     * @param $openId
     * @return WechatUser
     */
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