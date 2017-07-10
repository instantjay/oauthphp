<?php

namespace instantjay\oauthphp;

use Particle\Filter\Filter;

class WechatUser {
    public $isSubscribed;
    public $openId;
    public $nickname;
    public $gender;
    public $cityName;
    public $countryName;
    public $provinceName;
    public $language;
    public $profilePhotoUrl;
    public $subscriptionTime;

    public function __construct($r) {
        $f = new Filter();

        $f->value('subscribe')->int();
        $f->value('openid');
        $f->value('nickname');
        $f->value('sex')->int();
        $f->value('language');
        $f->value('city');
        $f->value('province');
        $f->value('country');
        $f->value('headimgurl');
        $f->value('subscribe_time')->int();
        
        $r = $f->filter($r);

        //
        $this->isSubscribed = $r['subscribe'];
        $this->openId = $r['openid'];
        $this->nickname = $r['nickname'];
        $this->gender = $r['sex'];
        $this->cityName = $r['city'];
        $this->provinceName = $r['province'];
        $this->countryName = $r['country'];
        $this->language = $r['language'];
        $this->profilePhotoUrl = $r['headimgurl'];
        $this->subscriptionTime = $r['subscribe_time'];
    }
}