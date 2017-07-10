<?php

namespace instantjay\oauthphp\Providers\Wechat;

class InternationalWechat extends Wechat {
    public function __construct($appId, $appSecret) {
        parent::__construct($appId, $appSecret, 'https://api.wechat.com');
    }
}