<?php
/**
 * Created by PhpStorm.
 * User: liuyaping
 * Date: 2019/4/25
 * Time: 5:10 PM
 */

namespace console\controllers;

use common\models\IpList;
use common\models\IpListJzt;
use common\models\User;
use common\models\UserLevelLog;
use common\models\UserLevelSuite;
use linslin\yii2\curl\Curl;
use yii\console\Controller;
use yii\helpers\Json;

/**
 * Class StatisDataController
 * @package console\controllers
 */
class StatisDataController extends Controller
{

    /**
     * @var int 每次的ipList入池数量
     */
    private $getIpNum = 200;

    public function actionVaaa(){
        $index = 0;
        do{
            $count = User::find()->Where(['BETWEEN','create_at',1556640000,1569772800])->andwhere(['is_ask_count'=>0])->count();
            if ($count==0){
                echo "更新完成\n";
                return;
            }
            $models = User::find()->Where(['BETWEEN','create_at',1556640000,1569772800])->andwhere(['is_ask_count'=>0])
                ->limit(10)->all();

            foreach ($models as $k => $y){
                //  查找京查号
                $user_agent = \Yii::$app->cha->createCommand("SELECT id,mphone FROM user WHERE mphone=>$y->mphone")->queryOne();
                var_dump();exit();
            }

        }while($count>0);
    }
    //  ip公共池
    public function actionIpListCe1(){
        //  获取ip
        $url = 'http://www.zdopen.com/PrivateProxy/GetIP/?api=201907081831514341&akey=def7bf6b9627f56e&fitter=2&order=2&type=3';
        $curl = new Curl();
        $collectString = $curl->get($url);
        $collectString = json_decode($collectString,true);
        $collectString = $collectString['data']['proxy_list'];

//        $collectString = explode(PHP_EOL,$collectString);
        //批量插入field
        $ipFields = ['ip','port','is_use','past_at','area','create_at','update_at'];
        $ipValues = [];
        foreach($collectString as $k => $y){
            $ip = $y['ip'] . ':' .$y['port'];
//            $re = self::check_proxy_ip_info($ip,1);
//            if($re == 0){
//                echo '不能代理'.$y['ip'] .'---'. date('Y-m-d H:i',time()) . "\n";
//                continue;
//            }else{
//                echo '代理'.$y['ip'] .'可以用' .'---'. date('Y-m-d H:i',time()) . "\n";
                $ipArray = [
                    $y['ip'],
                    $y['port'],
                    0,
                    time(),
                    '上海',
                    time(),
                    time()
                ];
                array_push($ipValues,$ipArray);
            }
//        }
        \Yii::$app->db->createCommand()->batchInsert(IpList::tableName(), $ipFields, $ipValues)->execute();
        echo '芝麻代理IP入库成功' .'---'. date('Y-m-d H:i',time()) . "\n";
//        }else{
//            echo '芝麻代理失败' .'------------'. date('Y-m-d H:i',time()) . "\n";
//        }
    }

    public static function check_proxy_ip_info($proxy_ip = false, $times=1){
        $header = array(

            // "GET / HTTP/1.1",

            // "HOST: www.baidu.com",

            "accept: application/json",

            "accept-encoding: gzip, deflate",

            "accept-language: en-US,en;q=0.8",

            "content-type: application/json",

            "user-agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36",

        );
        $url = 'http://www.jd.com/';

        $result['succeed_times'] = 0; //成功次数

        $result['defeat_times']  = 0; //失败次数

        $result['total_spen']    = 0; //总用时

        for ($i=0; $i < $times; $i++) {

            $s = microtime();

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $url); //设置传输的url

            curl_setopt($curl, CURLOPT_HTTPHEADER, $header); //发送http报头

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate'); // 解码压缩文件

            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //不验证证SSL书

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); //不验证SSL证书


            if (@$proxy_ip != false) { //使用代理ip

                curl_setopt($curl, CURLOPT_HTTPHEADER, array (

                    'Client_Ip: '.mt_rand(0, 255).'.'.mt_rand(0, 255).'.'.mt_rand(0, 255).'.'.mt_rand(0, 255),

                ));

                curl_setopt($curl, CURLOPT_HTTPHEADER, array (

                    'X-Forwarded-For: '.mt_rand(0, 255).'.'.mt_rand(0, 255).'.'.mt_rand(0, 255).'.'.mt_rand(0, 255),

                ));

                curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);

                curl_setopt($curl, CURLOPT_PROXY, $proxy_ip);

            }



            curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__).'/cookie.txt');

            curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__).'/cookie.txt');

            curl_setopt($curl, CURLOPT_TIMEOUT, 10); // 设置超时限制防止死循环

            // $response_header = curl_getinfo($curl); // 获取返回response报头

            $content = curl_exec($curl);

            if (strstr($content, '百度一下，你就知道')) {
                return 1;
                $result['list'][$i]['status'] = 1;

                $result['succeed_times'] += 1;

            } else {
                return 0;
                $result['list'][$i]['status'] = 0;

                $result['defeat_times']  += 1;

            }

            $e = microtime();
            $result['total_spen']          += abs($e-$s);

            $result['list'][$i]['spen']    =  abs($e-$s);

            $result['list'][$i]['content'] =  json_encode($content, true);

            // $result['list'][$i]['response_header'] =  $response_header;

        }

        $result['precent'] = (number_format($result['succeed_times']/$times, 4)*100).'%';

        $result['average_spen'] = number_format($result['total_spen']/$times, 4);

        return $result;
    }


    //  效验IP是否用
    public function actionInspectIp(){
        ini_set ('memory_limit', '512M');
        do{
            $count =0;
            $at = time() - 600;
            try{
                $temp = IpList::find()->where(['deleted'=>0])->andFilterWhere(['<','inspect_at',$at]);
                $count = $temp->count();

                if ($count==0){
                    echo "更新完成" .date('Y-m-d H:i',time()) . "\n";
                    return;
                }
                $procs = array();
                $model = IpList::find()->where(['deleted'=>0])->andFilterWhere(['<','inspect_at',$at]);;
                $querys = $model->limit(100)->all();

                \Yii::$app->db->close();
                $cmds = [];
                if (!$querys){
                    echo "查询完毕" . date('Y-m-d H:i',time()) . "\n";
                    return;
                }
                $i = 0;
                foreach ($querys as $query){
                    $cmds[$i][] = $query->id;
                    $i++;
                    if ($i==5){
                        $i=0;
                    }
                }
                foreach ($cmds as $cmd) {
                    $pid = pcntl_fork();
                    if ($pid == - 1) { // 进程创建失败
                        die('fork child process failure!');
                    } elseif ($pid) { // 父进程处理逻辑
                        $procs[] = $pid;
                    } else {
                        // 子进程处理逻辑
                        foreach ($cmd as $line) {
                            system('cd /data/wwwroot/jdcha && ' . 'php ' . './yii statis-data/inspect-ip-ce ' . $line);
                            echo $line . " done!\n";
                        }
                        exit(0);
                    }
                }
                foreach ($procs as $proc) {
                    pcntl_waitpid($proc, $status);
                }
                unset($pid);
                $pid = NULL;
                unset($procs);
                $procs = NULL;
            }catch (\Exception $e){
                echo $e->getMessage() . "11" . "\n";
            }
        }while($count>0);
    }

    public function actionInspectIpCe(){
        echo "更新时间" . date('Y-m-d H:i',time()) . "\n";
        $keyword_id = $_SERVER['argv'][2]?$_SERVER['argv'][2]:"";
        if (!$keyword_id){
            return;
        }
        echo 'IP:' . $keyword_id . "\n";
        $model = IpList::findOne(['deleted'=>0,'id'=>$keyword_id]);
        if (!$model){
            echo "IP: " .$keyword_id ."状态错误\n";
            return;
        }

            $ip = $model['ip'] . ':' .$model['port'];
            $re = self::check_proxy_ip_info($ip,1);
            if($re == 0){
                $model->deleted = 1;
            }else{
                $model->inspect_at = time();
            }

        if (!$model->save()){
            echo $model->getError() ."22" . "\n";
        };
    }

    public function actionIp(){
        $index = 0;
        do{
            $count = User::find()->select('mphone,last_login_ip')->where(['is_ask_count'=>0])->count();
            if ($count==0){
                echo "更新完成\n";
                return;
            }
            $models = User::find()->select('*')->where(['is_ask_count'=>0])->limit(100)->all();
            foreach ($models as  $k => $y){
                if(empty($y->mphone)){
                    continue;
                }
                if(empty($y->last_login_ip)){
                    continue;
                }

                $url = "http://ip.ws.126.net/ipquery?ip=";
                $url = $url . $y->last_login_ip;
                $curl = new Curl();
                $collectString = $curl->get($url);
                unset($curl);
                $res = mb_convert_encoding($collectString, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');

                echo $url . $res;
                if(strstr($res,'浙江')){
                    $y->province = '浙江';
                    $y->is_ask_count = 1;
                    $y->save();
                    echo '浙江省' . $y['mphone'] . '\n';
                    continue;
                }elseif(strstr($res,'上海')){
                    $y->province = '上海';
                    $y->is_ask_count = 1;
                    $y->save();
                    echo '上海' . $y['mphone'] . '\n';
                    continue;
                }elseif(strstr($res,'江苏')){
                    $y->province = '江苏';
                    $y->is_ask_count = 1;
                    $y->save();
                    echo '江苏' . $y['mphone'] . '\n';
                    continue;
                }elseif(strstr($res,'安徽')){
                    $y->province = '安徽';
                    $y->is_ask_count = 1;
                    $y->save();
                    echo '安徽' . $y['mphone'] . '\n';
                    continue;
                }else{
                    $y->is_ask_count = 1;
                    $y->province = substr($res, 8,9);
                    $y->save();
                }
            }
        }while($count>0);
    }


    public function actionDeleteIpDailiyun(){
        $nowTime = time();
        $deleteNum = IpList::deleteAll(['<', 'create_at', $nowTime]);
        $updateNum = IpList::updateAll(['is_use' => 0, 'is_app_use' => 0], ['deleted' => 0]);
        echo 'IpList删除' . $deleteNum . '条，更新'.$updateNum.'条 --- '. date('Y-m-d H:i:s',time()) . "\n";
        return;
    }

    //  ip公共池  -- 代理云
    public function actionIpListDailiyun(){
        //  获取ip
        $url = 'http://116528abc.v4.dailiyun.com/query.txt?key=NPAB5F4687&word=&count='.$this->getIpNum.'&rand=false&detail=true';
        $curl = new Curl();
        $collectString = $curl->get($url);

        $collectString = explode(PHP_EOL, $collectString);

        //批量插入field
        $ipFields = ['ip','port','is_use','is_app_pass','past_at','area','create_at','update_at','version'];
        $ipValues = [];
        $ip_list = [];
        foreach($collectString as $k => $y){
            if($y){
                $ips = explode(",",$y);
                $ips = explode(":",$ips[0]);
                $ip_list[] = $ips[0] . ':' .$ips[1];
            }
        }


        // 调用效验接口
        $ip_list = implode(",",$ip_list);
        $url = 'http://106.75.226.131:8181/ip/post-list-ip';
        $param['ip'] = $ip_list;
        $param['url'] = 'https://so.m.jd.com/ware/search._m2wq_list?keyword=%E6%B4%97%E8%A1%A3%E6%9C%BA&datatype=1&callback=jdSearchResultBkCbD&page=1&pagesize=10&ext_attr=no&brand_col=no&price_col=no&color_col=no&size_col=no&ext_attr_sort=no&merge_sku=yes&multi_suppliers=yes&area_ids=1,72,2819&filt_type=redisstore';
        $param['response_at'] = 2;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response,true);
        foreach($response['data'] as $k => $y){
            $ip = explode(':',$y['ip']);
            $port = $ip[1];
            $ip = $ip[0];
            $ipArray = [
                $ip,
                $port,
                0,
                $y['state'],
                time(),
                '',
                time() + 60,
                time(),
                4
            ];
            array_push($ipValues,$ipArray);
        }

        \Yii::$app->db->createCommand()->batchInsert(IpList::tableName(), $ipFields, $ipValues)->execute();
        echo '代理云IP入库成功，' . count($ipValues) . '条 --- ' . date('Y-m-d H:i',time()) . "\n";
        return;
    }

    //  ip公共池pc
    public function actionIpListPcHun(){
        //  易牛云-混播
        $url = 'http://ip.16yun.cn:817/myip/pl/1a2e4578-6dc7-4d85-a99f-427946fa93e3/?s=hwspcxjigu&u=liuyaping&count=200&format=json';
        $curl = new Curl();
        $collectString1 = $curl->get($url);
//        $collectString = explode("\r\n",$collectString1)
        $collectString = json_decode($collectString1,true);


        if(empty($collectString['status']) ){
            echo '代理错误' .$collectString['error_msg']. date('Y-m-d H:i',time()) . "\n";
            return;
        }
//        $collectString = $collectString['data']['proxy_list'];
        $collectString = $collectString['proxy'];

        //批量插入field
        $ipFields = ['ip','port','is_use','is_app_pass','past_at','area','create_at','update_at','version'];
        $ipValues = [];
        $ip_list = [];
        foreach($collectString as $k => $y){
            if($y){
                $ip_list[] = $y['ip'] . ':' .$y['port'];
            }
        }
        // 调用效验接口
        $ip_list = implode(",",$ip_list);
        $url = 'http://106.75.226.131:8181/ip/post-list-ip';
        $param['ip'] = $ip_list;
        $param['url'] = 'https://so.m.jd.com/ware/search._m2wq_list?keyword=%E6%B4%97%E8%A1%A3%E6%9C%BA&datatype=1&callback=jdSearchResultBkCbD&page=1&pagesize=10&ext_attr=no&brand_col=no&price_col=no&color_col=no&size_col=no&ext_attr_sort=no&merge_sku=yes&multi_suppliers=yes&area_ids=1,72,2819&filt_type=redisstore';
        $param['response_at'] = 5;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response,true);
        foreach($response['data'] as $k => $y){
            $ip = explode(':',$y['ip']);
            $port = $ip[1];
            $ip = $ip[0];
            $ipArray = [
                $ip,
                $port,
                0,
                $y['state'],
                time(),
                '',
                time(),
                time(),
                3
            ];
            array_push($ipValues,$ipArray);
        }

        \Yii::$app->db->createCommand()->batchInsert(IpListJzt::tableName(), $ipFields, $ipValues)->execute();
        echo '芝麻11代理IP入库成功' .'---'. date('Y-m-d H:i',time()) . "\n";
    }

    //  站大爷
    public function actionIpListPcz(){
        $url = 'http://www.zdopen.com/PrivateProxy/GetIP/?api=201912121929343863&akey=99f1f5124deb5c43&count=100&fitter=2&order=1&type=3';
//        $url = 'http://www.zdopen.com/ShortProxy/GetIP/?api=202001061641131697&akey=45d87ebe0d7b9b87&count=10&order=1&type=3';
        //  易牛云
//        $url = 'http://ip.16yun.cn:817/myip/pl/79094f43-ec6c-41ee-ae9b-60b0766706f2/?s=acbvxznucn&u=liuyaping&count=60&format=json';
        $curl = new Curl();
        $collectString1 = $curl->get($url);
//        $collectString = explode("\r\n",$collectString1)
        $collectString = json_decode($collectString1,true);


//        if(empty($collectString['status']) ){
//            echo '代理错误' .$collectString['error_msg']. date('Y-m-d H:i',time()) . "\n";
//            return;
//        }
        $collectString = $collectString['data']['proxy_list'];
//        $collectString = $collectString['proxy'];

        //批量插入field
        $ipFields = ['ip','port','is_use','is_app_pass','past_at','area','create_at','update_at','version'];
        $ipValues = [];
        $ip_list = [];
        foreach($collectString as $k => $y){
            if($y){
                $ip_list[] = $y['ip'] . ':' .$y['port'];
            }
        }
        // 调用效验接口
        $ip_list = implode(",",$ip_list);
        $url = 'http://106.75.226.131:8181/ip/post-list-ip';
        $param['ip'] = $ip_list;
        $param['url'] = 'https://so.m.jd.com/ware/search.action?keyword=洗衣机&searchFrom=search&sf=11&as=1';
        $param['response_at'] = 5;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($response,true);

        foreach($response['data'] as $k => $y){
            $ip = explode(':',$y['ip']);
            $port = $ip[1];
            $ip = $ip[0];
            $ipArray = [
                $ip,
                $port,
                0,
                $y['state'],
                time(),
                '',
                time(),
                time(),
                2
            ];
            array_push($ipValues,$ipArray);
        }

        \Yii::$app->db->createCommand()->batchInsert(IpListJzt::tableName(), $ipFields, $ipValues)->execute();
        echo '芝麻11代理IP入库成功' .'---'. date('Y-m-d H:i',time()) . "\n";
    }


    //  ip公共池测试
    public function actionIpListCe(){
        //  获取ip
//        $url = \Yii::$app->params['obtain_ip'];
        $url = 'http://httpdaili.f3322.net:800/user/webapi/getip.php?cdk=BC355D4F71DE3305C7035D78E5882B33&num=50&filter=1&lasthour=1';
        $curl = new Curl();
        $collectString = $curl->get($url);
        $collectString = explode(PHP_EOL,$collectString);
//        var_dump($collectString);exit();
        //批量插入field
        $ipFields = ['ip','port','is_use','is_app_pass','past_at','area','create_at','update_at'];
        $ipValues = [];
        foreach($collectString as $k => $y){
            if($y){
                $ips = explode(':',$y);
//                var_dump($ip);exit();
                $ip = $ips[0];
                $port = $ips[1];
//                $area = $ip[1];
//                $ip = $ip[0];
//                $ip = explode(':',$ip);
//                $port = $y['port'];
//                $ip = $ip[0];

                $ipArray = [
                    $ip,
                    $port,
                    0,
                    1,
                    time() + 3600*3,
                    '江苏',
                    time() + 3600*3,
                    time()
                ];
                array_push($ipValues,$ipArray);
            }
        }
        \Yii::$app->db->createCommand()->batchInsert(IpList::tableName(), $ipFields, $ipValues)->execute();
        echo '芝麻代理IP入库成功' .'---'. date('Y-m-d H:i',time()) . "\n";
//        }else{
//            echo '芝麻代理失败' .'------------'. date('Y-m-d H:i',time()) . "\n";
//        }
    }
    //  ip公共池
    public function actionIpList(){
        //  获取ip
//        $url = \Yii::$app->params['obtain_ip'];
        $url = 'http://httpdaili.f3322.net:800/user/webapi/getip.php?cdk=BC355D4F71DE3305C7035D78E5882B33&num=305&area=%E4%B8%8A%E6%B5%B7&show=1';
        $curl = new Curl();
        $collectString = $curl->get($url);
        $collectString = explode(PHP_EOL,$collectString);
        //批量插入field
        $ipFields = ['ip','port','is_use','past_at','area','create_at','update_at'];
        $ipValues = [];
        foreach($collectString as $k => $y){
            if($y){
                $ip = explode(' -> ',$y);
                $area = $ip[1];
                $ip = $ip[0];
                $ip = explode(':',$ip);
                $port = $ip[1];
                $ip = $ip[0];

                $ipArray = [
                    $ip,
                    $port,
                    0,
                    time() + (3600 *1),
                    $area,
                    time(),
                    time()
                ];
                array_push($ipValues,$ipArray);
            }
        }
        \Yii::$app->db->createCommand()->batchInsert(IpList::tableName(), $ipFields, $ipValues)->execute();
        echo '芝麻代理IP入库成功' .'---'. date('Y-m-d H:i',time()) . "\n";
//        }else{
//            echo '芝麻代理失败' .'------------'. date('Y-m-d H:i',time()) . "\n";
//        }
    }

    public function actionIpListPc2(){
        //  获取ip
//        $url = \Yii::$app->params['obtain_ip'];
        $url = 'http://webapi.http.zhimacangku.com/getip?num=400&type=2&pro=310000&city=0&yys=0&port=1&time=1&ts=0&ys=0&cs=1&lb=1&sb=0&pb=4&mr=1&regions=';
        $curl = new Curl();
        $collectString = $curl->get($url);
        $collectString = explode(PHP_EOL,$collectString);
        //批量插入field
        $ipFields = ['ip','port','is_use','past_at','area','create_at','update_at'];
        $ipValues = [];

        foreach($collectString as $k => $y){
//            var_dump($y);exit;
//            if($y){
//                $ip = explode(' -> ',$y);
//                $area = $ip[1];
//                $ip = $ip[0];
//                $ip = explode(':',$ip);
//                $port = $ip[1];
//                $ip = $ip[0];
//
//                $ipArray = [
//                    $ip,
//                    $port,
//                    0,
//                    time() + (3600 *1),
//                    $area,
//                    time(),
//                    time()
//                ];
//                array_push($ipValues,$ipArray);
//            }
        }
        \Yii::$app->db->createCommand()->batchInsert(IpList::tableName(), $ipFields, $ipValues)->execute();
        echo '芝麻代理IP入库成功' .'---'. date('Y-m-d H:i',time()) . "\n";
    }

    //  ip公共池pc
    public function actionIpListPc(){
//        $url = 'http://www.zdopen.com/PrivateProxy/GetIP/?api=201912121929343863&akey=99f1f5124deb5c43&count=250&fitter=2&order=1&type=3';
//        $url = 'http://www.zdopen.com/ShortProxy/GetIP/?api=202001061641131697&akey=45d87ebe0d7b9b87&count=10&order=1&type=3';
        //  易牛云
        $url = 'http://ip.16yun.cn:817/myip/pl/79094f43-ec6c-41ee-ae9b-60b0766706f2/?s=acbvxznucn&u=liuyaping&count=40&format=json';
        $curl = new Curl();
        $collectString1 = $curl->get($url);
//        $collectString = explode("\r\n",$collectString1)
        $collectString = json_decode($collectString1,true);


        if(empty($collectString['status']) ){
            echo '代理错误' .$collectString['error_msg']. date('Y-m-d H:i',time()) . "\n";
            return;
        }
//        $collectString = $collectString['data']['proxy_list'];
        $collectString = $collectString['proxy'];

        //批量插入field
        $ipFields = ['ip','port','is_use','is_app_pass','past_at','area','create_at','update_at','version'];
        $ipValues = [];
        $ip_list = [];
        foreach($collectString as $k => $y){
            if($y){
                $ip_list[] = $y['ip'] . ':' .$y['port'];
            }
        }
        // 调用效验接口
        $ip_list = implode(",",$ip_list);
        $url = 'http://106.75.226.131:8181/ip/post-list-ip';
        $param['ip'] = $ip_list;
        $param['url'] = 'https://so.m.jd.com/ware/search._m2wq_list?keyword=%E6%B4%97%E8%A1%A3%E6%9C%BA&datatype=1&callback=jdSearchResultBkCbD&page=1&pagesize=10&ext_attr=no&brand_col=no&price_col=no&color_col=no&size_col=no&ext_attr_sort=no&merge_sku=yes&multi_suppliers=yes&area_ids=1,72,2819&filt_type=redisstore';
        $param['response_at'] = 5;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response,true);
        foreach($response['data'] as $k => $y){
            $ip = explode(':',$y['ip']);
            $port = $ip[1];
            $ip = $ip[0];
            $ipArray = [
                $ip,
                $port,
                0,
                $y['state'],
                time(),
                '',
                time(),
                time(),
                1
            ];
            array_push($ipValues,$ipArray);
        }

        \Yii::$app->db->createCommand()->batchInsert(IpListJzt::tableName(), $ipFields, $ipValues)->execute();
        echo '芝麻11代理IP入库成功' .'---'. date('Y-m-d H:i',time()) . "\n";
    }

    //  清除ip池资源
    public function actionDeleteIp(){
        $eng_at = time() - (150);
        \Yii::$app->db->createCommand("DELETE FROM `ip_list_jzt` WHERE create_at<$eng_at")->execute();
//        \Yii::$app->db->createCommand("UPDATE `ip_list` SET deleted=1 WHERE past_at<$eng_at")->execute();
        \Yii::$app->db->createCommand("UPDATE `ip_list_jzt` SET is_use=0,is_app_use=0 WHERE  deleted=0")->execute();
        echo '删除IP成功' .'---'. date('Y-m-d H:i:s',time()) . "\n";
    }
    public function actionDeleteIpS(){
        $i=0;
        while(1){
            $i++;
            if($i>30) break;
            self::actionDeleteIp();
//            $pid=getmypid();
            //查询队列等任务
//            file_put_contents("/tmp/cron_test","{$pid}=={$i}\n",FILE_APPEND);
            sleep(2);
        }
    }
    //用户昨日注册、充值情况统计
    public function actionIndex()
    {
        $start_time = strtotime(date('Y-m-d',time() - 3600 * 24));
        $end_time = $start_time + 3600 *24;
        $users = User::find()->where(['deleted'=>0])->andWhere(['>','create_at',$start_time])->andWhere(['<','create_at',$end_time]);

        //今日注册
        $temp = clone $users;
        $regi_count = $temp->count();

        //邀请注册
        $temp = clone $users;
        $invite_count = $temp->andWhere(['>','up_user_id',0])->count();

        //自然注册
        $normal_count = $regi_count - $invite_count;

        $levels = UserLevelLog::find()->where(['status'=>1,'pay_type'=>2])->andWhere(['>','user_level_log.create_at',$start_time]);
        $levels->joinWith('user');

        //今日付费人数
        $temp = clone $levels;
        $fund_count = $temp->distinct('user_id')->count();

        //付款金额
        $temp = clone $levels;
        $all_fund = $temp->sum('pay_amount');

        //邀请用户付费人数
        $temp = clone $levels;
        $invite_fund_count = $temp->distinct('user_id')->andWhere(['>','user.up_user_id',0])->count();

        //邀请用户付费金额
        $temp = clone $levels;
        $invite_fund = $temp->andWhere(['>','user.up_user_id',0])->sum('pay_amount');

        //自然用户付费人数
        $normal_pay_count = $fund_count - $invite_fund_count;

        //自然用户付费金额
        $normal_fund = $all_fund - $invite_fund;

        \Yii::$app->db->close();

        $data[0]['register_num'] = $regi_count;
        $data[0]['pay_num'] = $fund_count;
        $data[0]['pay_amount'] = $all_fund ? $all_fund : 0;
        $data[0]['type'] = 1;
        $data[0]['statis_date'] = time() - 3600 * 24;
        $data[0]['create_at'] = time();
        $data[0]['update_at'] = time();

        $data[1]['register_num'] = $invite_count;
        $data[1]['pay_num'] = $invite_fund_count;
        $data[1]['pay_amount'] = $invite_fund ? $invite_fund : 0;
        $data[1]['type'] = 2;
        $data[1]['statis_date'] = time() - 3600 * 24;
        $data[1]['create_at'] = time();
        $data[1]['update_at'] = time();

        $data[2]['register_num'] = $normal_count;
        $data[2]['pay_num'] = $normal_pay_count;
        $data[2]['pay_amount'] = $normal_fund ? $normal_fund : 0;
        $data[2]['type'] = 3;
        $data[2]['statis_date'] = time() - 3600 * 24;
        $data[2]['create_at'] = time();
        $data[2]['update_at'] = time();


        $querys = UserLevelLog::find()->where(['status'=>1,'pay_type'=>2])->andWhere(['user_level_suite.deleted'=>0]);
        $querys->joinWith('levelSuit');
        $querys->joinWith('user');

        //当日购买会员总金额
        $temp = clone $querys;
        $total_amount = $temp->sum('pay_amount');

        $suits = UserLevelSuite::find()->where(['deleted'=>0])->select('id,name')->orderBy('id desc')->asArray()->all();

        //今日注册充值统计总数
        $temps = clone $querys;
        $temps = $temps->select(['user_level_suite_id as level,sum(pay_amount) as amount,count(distinct(user_level_log.user_id)) as num'])->groupBy('user_level_suite_id')->asArray()->all();
        $list = [];

        $i = 0;
        foreach ($suits as $suit){
//            $list[$i]['name'] = $suit['name'];
            $list[$i]['list'] = (object)[];
            foreach ($temps as $temp){
                if ($temp['level'] == $suit['id']){
                    $list[$i]['list'] = (object)$temp;
                    unset($list[$i]['list']->levelSuit);
                    unset($list[$i]['list']->user);

                    break;
                }
            }
            $i++;
        }
        $data[0]['level_data'] = Json::encode($list);

        //今日邀请注册充值统计总数
        $temps = clone $querys;
        $temps = $temps->andWhere(['>','up_user_id',0])->select(['user_level_suite_id as level,sum(pay_amount) as amount,count(distinct(user_level_log.user_id)) as num'])->groupBy('user_level_suite_id')->asArray()->all();
        $list = [];

        $i = 0;
        foreach ($suits as $suit){
//            $list[$i]['name'] = $suit['name'];
            $list[$i]['list'] = (object)[];
            foreach ($temps as $temp){
                if ($temp['level'] == $suit['id']){
                    $list[$i]['list'] = (object)$temp;
                    unset($list[$i]['list']->levelSuit);
                    unset($list[$i]['list']->user);
                    break;
                }
            }
            $i++;
        }
        $data[1]['level_data'] = Json::encode($list);

        //今日自然注册充值统计总数
        $temps = clone $querys;
        $temps = $temps->andWhere(['up_user_id'=>0])->select(['user_level_suite_id as level,sum(pay_amount) as amount,count(distinct(user_level_log.user_id)) as num'])->groupBy('user_level_suite_id')->asArray()->all();
        $list = [];

        $i = 0;
        foreach ($suits as $suit){
//            $list[$i]['name'] = $suit['name'];
            $list[$i]['list'] = (object)[];
            foreach ($temps as $temp){
                if ($temp['level'] == $suit['id']){
                    $list[$i]['list'] = (object)$temp;
                    unset($list[$i]['list']->levelSuit);
                    unset($list[$i]['list']->user);
                    break;
                }
            }
            $i++;
        }
        $data[2]['level_data'] = Json::encode($list);

//        var_dump($data);exit();

        \Yii::$app->db->close();

        //入库
        $columes = ['register_num','pay_num','pay_amount','type','statis_date','create_at','update_at','level_data'];

        try{
            $result = \Yii::$app->db->createCommand()->batchInsert('user_data', $columes, $data)->execute();
            if (!$result){
                echo date('Y-m-d H:i',time()) . "入库失败\n";
                return;
            }
            echo date('Y-m-d H:i',time()) . "入库成功\n";
        }catch (\Exception $e){
            echo date('Y-m-d H:i',time()) . "入库失败: " . $e->getMessage() . "\n";
        }

    }

}