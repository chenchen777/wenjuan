<?php
return [
    //  APP排名使用京东博士接口
    "JDBS" =>0,
    /**********全局*********/
    'webname' => '京东魔盒',  //网站名称
    'seo_title' => '京东查排名',  //网站标题
    'seo_keywords' => "京东排名查询 京东查排名 京东排名查询软件 京东排名查询工具 京东权重查询 京东关键词排名查询",
    'seo_description' => "京东查排名平台是京东排名查询软件 京东排名查询工具,提供京东排名查询,京东权重查询,京东关键词权重查询,便于商家优化京东排名",
    //'domain' => 'http://cha.chaojids.com',    //网站域名 可识别分站
    'domain' => 'http://jdmohe.com',    //网站域名 可识别分站
    'logo' => '/res/main.png',
    'icp' => '浙ICP备16037446号-1',
    'service_qq' => '2778320',
    'title'=>"京东查排名_京东排名查询_京东魔盒 京东排名权重查询平台",

    'notifyPhone' => '15068888852',
    
    /************会员**************/

    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'user.passwordResetTokenExpire' => 3600,
    
    /****系统***********/
    'fileRoot'  => Yii::getAlias('@backend/web/upload/'),   //上传文件根目录
//    'fileDomain' => 'http://img.jueue.net/', //文件域名 注意/结尾
//    'fileDomain_api' => 'http://img.52wangcai.com',
    
    'invite_code_need' => true,  //是否需要邀请码
    'invite_code_required' => true,   //邀请码是否必填
    
    'mphone_owner_api' => [
        'url' => 'http://api.k780.com:88/',
        'appkey' =>24927,
        'secret' => '5700291563e11101165369809a04838c',
        'sign'  => '2d181ad52cbdb2b37fb085b0ba56f2a0',
    ],


    // jdcha-api项目地址
    'go-jdcha-api' => '106.75.218.90:8081/',


    //网站状态
    'web_status' => [
        'canCreateTask' => 1, //是否允许新建任务
    ],
    //系统时间节点
    'times' => [
        'task_allow_st' => '04:00:00', //允许新建任务开始时间
        'task_allow_end' => '21:00:00', //允许新建任务结束时间
    ],

    //转账银行卡信息
    'banks' => [
        'bank_name' => '中国建设银行xx支行',
        'bankNo'    => '6227000782130759893',
    ],

    //分享url
    'share_url'=> 'http://jdmohe.com/home/share?share_code=',

    //京侦探分享链接
    'zt_share' => 'http://jdzhentan.cn/home/share?share_code=',

    //讲师分享url
    'lecture_url' => 'https://www.jdmohe.com?lecture_code=',

    //排序方式
    'result_sort'=>[
        '0'=> '',
        '1'=>'综合',
        '2'=>'销量',
        '3'=>'评论数',
        '4'=>'新品',
        '5'=>'价格',
    ],
    //  新查询app接口 golang
    'go_app_ranking' => "http://106.75.226.131:8989/cj-jd/app/post-ranking",
    //  京喜查排名
    'go_app_ranking_jx' => "http://106.75.226.131:8989/cj-jd/app/post-wq-ranking",
    //  京喜指定店铺查排名
    'go_app_ranking_jx_shop' => "http://106.75.226.131:8989/cj-jd/app/post-wq-ranking-shop",
    // 根据店铺查询排名
    'go_appshop_ranking' => "http://106.75.226.131:8989/cj-jd/app/post-ranking-shop",

    // 查权重
    'go_appweight_ranking' => "http://106.75.226.131:8989/cj-jd/app/post-weight-search",


    //  新查询app接口 golang
//    'go_app_ranking' => "http://106.75.226.131:8282/rankingso/post-ranking",
//    //  京喜查排名
//    'go_app_ranking_jx' => "http://106.75.226.131:8282/wq-ranking/post-ranking",
//    //  京喜指定店铺查排名
//    'go_app_ranking_jx_shop' => "http://106.75.226.131:8282/wq-ranking/post-ranking-shop",
//    // 根据店铺查询排名
//    'go_appshop_ranking' => "http://106.75.226.131:8484/ranking-shop/post-ranking-shop",
    //全球商品排名查询链接
    'global_url' => "http://106.75.218.236:5001/",
    //  获取商品信息
    'good_particulars' => 'http://106.75.214.236:5001/goods_detail',
    //发起任务sku
    'good_sku' => 'http://106.75.218.210:5001/comment_sku',
    'good_comment' => 'http://106.75.218.210:5001/comment',
    //sku 信息采集
//    'sku_collect'=> 'http://106.75.218.64:5000/comment',
//    'sku_collect_fold'=> 'http://106.75.209.210:5000/fold_comment',
    'sku_collect_fold'=> 'http://106.75.214.236:5001/fold_comment',
    'sku_collect'=> 'http://cj.chaojids.com/collect?sku=',
    //销量 信息采集sales_collect
//    'sales_collect'=> 'http://cj.chaojids.com/sales/',
// 京东更新了  同一台点电脑会检测是不是所属的cook值
//    'sales_collect'=> 'http://61.147.103.145:8081/sales/',
//  服务调整
    'sales_collect'=> 'http://10.23.63.180:8082/sales/',
//  采集排名PC
    'rank_pc' => 'http://106.75.226.131:8989/',
    //sku 魔盒采集(查排名、查权重)
//    'sku_collectForMohe'=> 'http://192.168.1.185:5000/comment_ratio',
    'sku_collectForMohe'=> 'http://cj.chaojids.com/collectForMohe?sku=',
    //sku占比采集接口
    'sku_comment' => 'http://106.75.226.131:8989/cj-jd/app/goods-comment',

    //排名、权重采集
//    'rank_url' => 'http://106.75.215.25:8080/',
//    'rank_url' => 'http://106.75.226.142:8080/',
    'rank_url' => 'http://127.0.0.1:8081/',
    //  APP查询
    'app_rank_url' => 'http://106.75.226.131:8585/',
    //  评价监控
    'comm_rank_monitor' => 'http://106.75.214.236:5001/comm_rank_monitor',
    //  2019年1月9日10:59:15 服务器调整 lisk
    'comment_rank_url' => [
        'comment_rank_url_1' => 'http://106.75.214.236:5000/',
        'comment_rank_url_2' => 'http://106.75.213.226:5000/',
        'comment_rank_url_3' => 'http://106.75.209.210:5000/',
    ],


    //  新的
    'rank_url_py_1' => 'http://106.75.209.210:5000/',
    'rank_url_py_2' => 'http://106.75.214.236:5000/',
    'rank_url_py_3' => 'http://120.132.20.63:5000/',
    'rank_url_pu_4' => 'http://106.75.213.226:5000/',
//    'rank_url_3' => 'http://106.75.213.226:5000/',
//    'rank_url_4' => 'http://120.132.103.27:5000/',

    //  老的
    'rank_url_1' => 'http://10.23.63.180:8080/',
    'rank_url_2' => 'http://10.23.32.64:8080/',
    'rank_url_3' => 'http://10.23.70.31:8080/',
    'rank_url_4' => 'http://10.23.232.114:8080/',
    'rank_url_5' => 'http://10.23.58.50:8080/',

    //获取店铺信息
    'shop_collect' => 'http://cj.chaojids.com/mohe/getShopInfoForWechat?key=',


    //权重分浮动
    'weight_count' => 5,

    //渠道直接推荐用户提成
    'dis_percent' => 0.4,

    //渠道间接用户提成
    'ind_percent' => 0.25,

    //普通上线用户提成
    'up_percent' => 0.15,

    //客服qq
    'qq' => '3517779573',
    //客服微信
    'wechat' => 'XBKJ1205',
    //微信二维码
    'wechat_head' => 'http://jdmohe.cn-sh2.ufileos.com/plug/wechatMohe.jpg',

    //京东sku地址
    'jd_url' => 'http://item.jd.com/',

    //支付宝
    'app_id' => '93b353f3-61e4-4a3d-8010-119690407bac',
    'app_secret' => '5be340e5-8324-4897-9dbc-f967e17c0178',
    'master_secret' => 'c2a760f6-8777-4ae8-95b5-c4be1ac636bd',
    'alipay' => [
        //应用ID,您的APPID。
//         'app_id' => "2017021705720570",
        'app_id' => "2018050502640104",
        //商户私钥
//         'merchant_private_key' => "MIIEowIBAAKCAQEAoRi/+kZxd8bGBazDOw9HsPXQiRgX+J6JRpPCt6HQ/i/Draiq5c2F/krnuC9MSMShDaC2eVWjxvQw2Xuuh6Gt6IkGTOSDLdWHfDd8IZDXcZFgu1MNewi+W4EG3ts6HeDZzQ7bc8RB29fv5SJCPhs0vf8601Y81N9uDOuvDSm4TTF/xNAxAk/dn2C2kcVOOwGCd+uWWE2uA1zy+sqF+Zs5vLn9v/pGmGNIHdZ/bb0/l54yjLCL2Hjt399nwBjyk6wti+Cega3zN9WcmydidYWVjqspybuKRCPFT2p36SzFfYitN8YQK5hk5iGYlL8T3YfRrtL8LoKM9O2wNP6p1BfK4QIDAQABAoIBAAySvPEQrGx3xB7PRBGI+MRz+wmoKr7JyNcMU0c3xvL/0VrtbiEvcETPndQ/RMntJtDEeVlw+K5fgqyGvbFySft7LlW0BBUAtGlaf0KzZk1D1nPoYIX4wbqskFe2bovEb9tUTIZ1i9pXuS5+BQOJ5gzqLbIu6eKHabRydKAnYG4NzkJIHziRW+Gt1wtua2TnYxVr1zLWiU6NQuzPzt88stfPzZyI/oqtJMwwo9MCfruevrUCf9eywPllX1Rxj+AHf3hZG/YqUbdGTK0kg9TlYpykozzkPeC0bw6qSNSz7lW1WWyP+0lwdbONF9NuyvKs5Jau4/gPq3LfUSZdsaQSbwECgYEAzY8zxoYhcs9UPytCNRQgC8C1nr1WWUfTDvxMPHN08xZ5kML94DPqnTaKs5dw2ZgGx3uq2w3MyDvb4NpK4HbIs2cSMJ94GYSAtUVE8YI4H58CZisYCccw/bAt3BQNmEpkJ0xWyW2WdoRAb5jaOV8KzS0kcwiz98qmUtjzvC/SPtECgYEAyKB/D+mCU6NeddXY/airHHy1HEHXpI5duT+rmOY2/QCB6yT+pfiU6+Z5MJpfuuzzCpWkM+jsE2pXEn5CXk7ExazcOiteJUtt7jhYm58cCXqVGdPRDtRrDyMMYwcVHWcCVUbNueI0n6eob5OcdrknUhqBzzfxV1PhkGzd7xrVbxECgYEAiIvNqtxCr89FeUi1DCk4OFZkzvOavmfVraiuw6E2WJvfWOgOWLZj7NbkP/QjRIeWSUC++BAsrf4FS1H2VzDQlUusa5wi3WCFVuSSrZMA0RCBOXj62NP2mS5E6GJxvW39JZWyOM+RGV00qGzy2RVnSW/pPHjVg46b+YCgN6vUj2ECgYA84fetLH+QQhN4Ttz6nSTQEEgluxVlqo5mmRvJ2pL1VCIY6bEdTMJklBxS04YZg659l8ustRJvEeY6hnY9iEnOcxah0GdRigJiOFcrYq1vcvOsUssuZOfYQBqSMqQFuCzNFB96OVse2BIVSgZBQQ7dq42ZBLEIfzwESAVReiMrAQKBgFcNhHAw5Fc0mWDkmL82nB80eHs0V6YdmGABKdzo2mveIwRmFzbHAswhgq8dMJ/sBJe9iB6kWW0DozO8tiMtGZNwcaVXXcg6ZQMV7fAX66WDNvzOd7AfgcaaTGHtr9tX8nctXFNqaHjfsl7LtCTawG2YMeLFqYlk1auvY1xj3xux",
        'merchant_private_key' => "MIIEpQIBAAKCAQEA7oxwqnksSRVHcpix3IDWwGjFrstmimTqfwHkUObyfZPWBB0zIkYoAzLMYTMYMBvELBTVBcRuO7c1Tr4HOeQJFUGKlYvrCA7phdrLVHObN4cwolYe6xyeAj8Xo5LAFDo1HOWfV7cm5m2H23fvVxH3f4BqoTUqF04iQKFYEa4FTzuhPxHEwTfhQ5EeNRCClvHxPHCbI6Ohfdsmt2F8ju8KwJMfr0t+a/fI0wY+AoI5+KGiquyUFOWVuYwxz0lv5Za0RtpS/TuhmSFL/k7w4ameljInlceENXxOSTBoqPUn4NK2xwvdkNo7l4co5BP6E5CRKEXw5+xCaoYNfWUwHH5+/QIDAQABAoIBAQCXhpPmZVPxFFgu/bk05I4E6EhxkHgQ/qtJg80gaqKri+WM9XNOmRu/dFwIfekmXezNR2pM4IpY2jY9T03NwwDWBKzf82GW28oQIXu4qDp3GiOVanj3RVVE/gio+YWTE6o7Mcx2bjuCcR60FMnuF9aLoemZfui+pv3w6cvEZzKuMWtwEyqp4gd70FYWnc/O3yxYILMuVc08g1cpx4fXNJcBAJJojufk6jFe+LfE2NcnbtXsdtQdIHXrAtV0uQGf+nKCcv59DOLWtEWyorg9NvBSxi4WxVmSu6uL2qwCTf6pHK5Z7No4n0CbB1jf2A6QxNEFj8yTF2SbcuPEs5foXagBAoGBAPquMhqzBuNSPzGRzJS0j02ou1Dky+rDFDRiEmAGfn54km9ofwD3tsy8O4qV5/jO1IkXyNn+kB9Y4ioYizV2mZbQrAQXQqd02yqYrG1Wrnrg1ifAVFkNSdcGMBZFAxm9AEutpWbBHtMkSmL/0cFUmnWM//BrFDdKuCadFfOfM2j9AoGBAPOcVsIcuYQdXUCg9CeQH23aQwRbA4zsRwzRkzbJfK4FLybxnPg5mt7+lJeuGpob3DUJbxrKEdK4/ehBY2DxMvUm5DsiJMmJwNeqE6wysmSeaYqhMC96ylVq36f+D0J7/G95Vpjrjk/YEm8TvvmqMraJRMVqafS1xtWtq/yA5k4BAoGALBo9gmkAyzmLGKN4BKmHlLgczGyUW6cahOTEKtS5qHJ6ANO3lGoAZSCwsP8SorlwtvXSRlf87ybqE1HiG/PTra7ktIuLFH8AmAvoOgxIAiJQSwoRTOGDnirehux5USvpQ2c3dE6Bp8/4rlYnhwXlbszxslCEa/3fTBvS4tudZdkCgYEArc9i2zYxasCksyLjcILU2cT7QdVKEDbYbp3swg86BNMwFEIY88Xt6KKNFDelRUURdUnY2q1H0CgrCJX2uMj+yYjJGCPBSYYecAEp7hdxznZz6PQedBHQ4ObS0oJjEtVxg3t4PLz8GZ2ZcWUOQgWcDz6bMZs+I9m6FuTFq++v4gECgYEA2fcyRsf4D41PGRFAWit+Pqd0v/DfHH+z34sqaKbnJ1byQOQGi6GsLF6y4QbyHGRBIzKtJ/B4JI4VFdXArNCY2UwULjUAF1r6xHE3vdl5dkhJyqhZY0Xqpi2O+y4bhOGRCwoWXnqmHn2aDF8EugpRGg+f3OzBsuBwBWe2Rs2SEZA=",
        //异步通知地址
        'notify_url' => "http://外网可访问网关地址/alipay.trade.page.pay-PHP-UTF-8/notify_url.php",
        //同步跳转
        'return_url' => "http://外网可访问网关地址/alipay.trade.page.pay-PHP-UTF-8/return_url.php",
        //编码格式
        'charset' => "UTF-8",
        //签名方式
        'sign_type'=>"RSA2",
        //支付宝网关
        'gatewayUrl' => "https://openapi.alipay.com/gateway.do",
        //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
//         'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAgtktyezkflMmvzmCqa8JcLh/+0oH7ysre1rzMtoi0sNrp91VX+uqd9WUmwSvnRly1iauS3GCDoFQfLow9YJLjSfOWvLlwhDuMqZGGwVcWrhwAWmJuupm2U289B8IkS4Ef9P1PgeIPchLcbPqUblBLQ0zw9WmyKYnv0HbB0iB0Snq7ZzY4iEYt8Wc+qyxuHdGAaO/vrif7MUtHtrngnxQW8hdjxbPSHVnIv1QBPXWPNftGXWUSC7/L31HSWBp84/87zWweuDTlo0tExdAHlDC89V3BCWbmDZeNmJ/Wsr3MqeFPNCBPN6X77VYIlpK0JArj/aXvVrDkCKziQAZOAo3pQIDAQAB",
        'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqtyMpujSMXtu4EB4MncdrufJzJ16+zQp5oWKKhMWhaheEK/p7TJA6WAFUA1eSkWRCNVeKk/oCuROxJ3diqIZNYs7ZQHHRo0QlTw5v3mn0mTbrpRJ2hUmBpFKEEmZWgQtXKpdZjiK0fNzW6tAWLKIcnaR1U/PhTQm5qzfusbRrFVQkGYZPsRgPAveSDgYTpiLE3QDSu0K7uKSHzsoXp21YaX3W+REPD9V3ygdO+BMeBw3RS7BWXXTq4Yrmk4Ifjn9eSOzXiCqRskpsOQIaE3K8dRcIhXr79mIs+lPJDNPoZi40ZQWrhp3xeyf5fYws+AkfU8D20LZXngghxWaz1ZfYQIDAQAB",
    ],
    //官方运营微信二维码
    'wechat_code' => 'http://jdmohe.cn-sh2.ufileos.com/wx_new.jpg',
//     'wechat_code' => 'http://www.jdmohe.com/images/weix.jpg',


    //华科代理配置
    'ip'       => 'http-cla.abuyun.com',
    'port'     => '9030',
    'account'  => 'H2W2UORQ8L221A5C',
    'password' => '8B99793906F7CF24',
    'obtain_ip' => 'http://httpdaili.f3322.net:800/user/webapi/getip.php?cdk=FF3B456FCEB2603BB1EC8535EF5E96D6&num=50&area=%E4%B8%8A%E6%B5%B7&show=1',
    //第三方
    'sms_sign' => '',
    //创蓝短信配置
    "CL_SMS" => [
        'api_account' => 'xuliubing8',
        'api_password' => 'MMc1002003',
        'api_send_url' => 'http://222.73.117.158/msg/HttpBatchSendSM',
        'api_balance_url' => 'http://222.73.117.158/msg/QueryBalance',
    ],
    //阿里大鱼短信配置
    "Dayu_SMS_APP_KEY" => '23535824',
    "Dayu_SMS_APP_SECRET" => "ebd50d80851d63f34cedba681994e1c5",
    "Dayu_SMS_PRODUCT_NAME" => "买家秀",
    "Dayu_SMS_PRODUCT_REGISTER_TMPLATEID" => [
        'SMS_35660104',
        'SMS_35875045',
        'SMS_35655046',
    ],
    "Dayu_DEFAULT_CODE_TMPLATEID" => 'SMS_26210084',
    //验证码有效时间
    'sms_expire' => 600,
    'sms_send_ticker' => 60,

    'img_host'=>'http://img.chaojids.com',
    //流量token
    'pageviews_token'=>'0d10d7eb1f21625b572343b5dda823e8',
    'app_pageviews_token'=>'ab5033e498d8027f2c7c0bdc',
    //proxy_token
    'proxy_token'=>'5d6ee07b5482c07dc2298db4708ca240',
    
    
    //ucloud
    'UCloud_API' => [
        'public_key' => 'FG1MN42kq9Mogb6gP+ePH+oxrk4WLyHEW6IjBkUtgXfVd00z+uPLnQ==',
        'private_key' => 'cf2f9a038e57555645ab8df6cfb6fbe0b2db3f6f',
        'timeout' => 30,
        'suffix' => '.cn-sh2.ufileos.com',
    ],
    'UCloud_FILE_HOST' => 'jdmohe.ufile.ucloud.com.cn',
    'FILE_HOST' => 'jdmohe.ufile.ucloud.com.cn',
    'FILE_BUCKET' => 'jdmohe',
    'UCloud_FILE_PATH' => [
        'COMMENT' => 'comment/', //评论
        'LOGO'  => 'logo/', //渠道和代理上传的logo
        'COMMENT' => 'plug/',
        'AVATOR' => 'avator/',
    ],

    'comment_client_id'=>108,
    'quick_car_url'=>"http://cj.chaojids.com/flash/",

    'version' => [
        'version_number' => 1,
        'is_force'   => '0',
        'url' => 'http://jdmohe.ufile.ucloud.com.cn/app-release.apk',
        'update_content' => '1.查排名'.PHP_EOL.'2.销量'.PHP_EOL.'3.查权重'.PHP_EOL
    ],

    'ios_version' => [
        'version_number' => '1.0.0',
        'is_force'   => '1',
        'url' => 'http://jdmohe.ufile.ucloud.com.cn/app-release.apk',
        'update_content' => '1.查排名'.PHP_EOL.'2.销量'.PHP_EOL.'3.查权重'.PHP_EOL
    ],

    'enter_type' => 0,
];
