<?php

namespace instantjay\oauthphp\Providers\Wechat;

class ChineseWechat extends Wechat {
    public function __construct($appId, $appSecret) {
        parent::__construct($appId, $appSecret, 'https://api.weixin.qq.com');
    }
}