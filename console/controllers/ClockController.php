<?php
/**
 * Created by PhpStorm.
 * User: liuyaping
 * Date: 2019/4/30
 * Time: 10:11 AM
 */

namespace console\controllers;


use common\models\RandomList;
use common\models\UserLinshi;
use common\models\UserLinshii;
use linslin\yii2\curl\Curl;
use yii\console\Controller;
use yii\helpers\Json;

class ClockController extends Controller
{

    /**
     * 获取不重复的 uuid - 随机生成
     * @return string
     */
    public static function Uuid()
    {
        /* 生成时间戳 */
        list($msec, $sec) = explode(' ', microtime());
        $millisecond = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        $code = substr($millisecond * mt_rand(1, 9), 0, 13); // 获取13位数字
        /* 生成uuid */
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr($chars, mt_rand(1, 9), 12);
        return '86' . $code . '-' . $uuid;
    }
    public function actionUuid(){
        $i = 0;
        $time =  time();
        echo "执行时间" . date('Y-m-d H:i',time()) . "\n";
        while($i<5000){
            $uuid =  self::Uuid();
            \Yii::$app->db->createCommand("insert into random_list(random, create_at, update_at) values ('$uuid',$time,$time)")->execute();
            $i++;
        }
        echo "生成5000完成------\n";
        $i = 0;
        while($i<3000){
            $uuid =  self::Uuid();
            \Yii::$app->db->createCommand("insert into random_list2(random, create_at, update_at) values ('$uuid',$time,$time)")->execute();
            $i++;
        }
        echo "生成3000完成++++++\n";
    }
    public function actionIndex()
    {
        do{
            $count = UserLinshi::find()->where(['is_update'=>0])->orderBy('id desc')->count();
            if ($count==0){
                echo "更新完成\n";
                return;
            }
            $procs = array();
            $models = UserLinshi::find()->where(['is_update'=>0])->orderBy('id desc')->limit('100')->all();


            \Yii::$app->db->close();
            $cmds = [];
            if (!$models){
                echo "查询完毕" . date('Y-m-d H:i',time()) . "\n";
                return;
            }
            $i = 0;
            foreach ($models as $query){
                $cmds[$i][] = $query->id;
                $i++;
                if ($i==10){
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
                        system('cd /data/wwwroot/jdcha && ' . 'php ' . './yii clock/monitor-new ' . $line);
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
        }while($count>0);
    }

    public function actionMonitorNew()
    {
        echo "更新时间" . date('Y-m-d H:i',time()) . "\n";
        $keyword_id = $_SERVER['argv'][2]?$_SERVER['argv'][2]:"";
//        $keyword_id = 1184;
        if (!$keyword_id){
            return;
        }
        $y = UserLinshi::find()->where(['id'=>$keyword_id])->one();
//        foreach($models as $k =>$y){
            $is_you = \Yii::$app->jdrqds->createCommand("select * from user where mphone= $y->mphone")->queryOne();

            if(empty($is_you)){
                $y->is_update = 1;
                $y->save();
                echo "账号没有" .$y->mphone. "\n";
            }else{
                if($is_you['recharge_count'] >0){
                    $y->is_update = 2;
                    $y->save();
                    echo "账号——————————————" .$y->mphone. "\n";
                }else{
                    $y->is_update = 1;
                    $y->save();
                    echo "账号没有充值" .$y->mphone. "\n";
                }
            }
//        }

    }


    public function actionIndexa()
    {
        do{
            $count = UserLinshii::find()->where(['is_update'=>0])->orderBy('id desc')->count();
            if ($count==0){
                echo "更新完成\n";
                return;
            }
            $procs = array();
            $models = UserLinshii::find()->where(['is_update'=>0])->orderBy('id desc')->limit('100')->all();


            \Yii::$app->db->close();
            $cmds = [];
            if (!$models){
                echo "查询完毕" . date('Y-m-d H:i',time()) . "\n";
                return;
            }
            $i = 0;
            foreach ($models as $query){
                $cmds[$i][] = $query->id;
                $i++;
                if ($i==10){
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
                        system('cd /data/wwwroot/jdcha && ' . 'php ' . './yii clock/monitor-newa ' . $line);
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
        }while($count>0);
    }

    public function actionMonitorNewa()
    {
        echo "更新时间" . date('Y-m-d H:i',time()) . "\n";
        $keyword_id = $_SERVER['argv'][2]?$_SERVER['argv'][2]:"";
//        $keyword_id = 1184;
        if (!$keyword_id){
            return;
        }
        $y = UserLinshii::find()->where(['id'=>$keyword_id])->one();
//        foreach($models as $k =>$y){
        $is_you = \Yii::$app->jdrqds->createCommand("select * from user where mphone= $y->mphone")->queryOne();

        if(empty($is_you)){
            $y->is_update = 1;
            $y->save();
            echo "账号没有" .$y->mphone. "\n";
        }else{
            if($is_you['recharge_count'] >0){
                $y->is_update = 2;
                $y->save();
                echo "账号——————————————" .$y->mphone. "\n";
            }else{
                $y->is_update = 1;
                $y->save();
                echo "账号没有充值" .$y->mphone. "\n";
            }
        }
//        }

    }

    /**
     * 匹配标准uuid,获取全球购商品中能用的uuid
     */
    public function actionUuidCheck()
    {

        //取出redis中存的标准排名
        $redis = \Yii::$app->redis;
        $key = "standard_rank" . date('Y-m-d',time());
        if ($val = $redis->get($key)){
        }else {
            $val = self::getStandardRank();

        }
        $parama = [
            'sku'         => "19731117142",
            'keyword'     => "牛奶",
            'page_start'  => "1",
            'page_end'    => "100",
            'price_start' => "",
            'price_end'   => "",
            'sort'        => "1",
        ];
        $index = 0;
        do{
            $rans = RandomList::find()->where(['global_can_use'=>0])->andWhere(['>','id',$index])->limit(100)->all();
            if (count($rans)==0){
                echo date('Y-m-d H:i') . "uuid检索完毕\n";
                return;
            }
            foreach ($rans as $ran){
                $parama["uuid_1"] = $ran->random;

                $url = \Yii::$app->params['global_url'] ."uuid_1";
                $curl = new Curl();
                $curl->setOption(CURLOPT_TIMEOUT,90);
                try{
                    $postString = $curl->setRequestBody(Json::encode($parama))
                        ->setHeader('content-type', 'application/json')->post($url);
                    $data = Json::decode($postString,false);
                    $code = $data->return_code;
                    $result = $data->result;
                    if ($code !=200){
                        continue;
                    }
                    $rank = $result->rank;
                    $change = abs($rank - $val);
                    echo "查询排名:" . $rank ."\n";
                    if ($change < 2){   //浮动与标准排名相差一个单位，则记录此uuid可用
                        $ran->global_can_use = 1;
                        $ran->save();
                        echo 'random :' . $ran->id . "可用\n";
                    }else {
                    }
                }catch (\Exception $e){
                    echo "错误:" . $e->getMessage() . "\n";
                }
                $index = $ran->id;

            }
            \Yii::$app->db->close();
        }while(true);

    }

    //根据标准uuid 获取排名
    public function getStandardRank()
    {
        $uuids = ['99001280539735-70bbe9fb93f1','99001280513798-70bbe9f2667f'];
        $index = 0;
        do {
            $parama = [
                'sku'         => "19731117142",
                'keyword'     => "牛奶",
                'page_start'  => "1",
                'page_end'    => "100",
                'price_start' => "",
                'price_end'   => "",
                'sort'        => "1",
                $parama["uuid_1"] = $uuids[$index],
            ];
            $url = \Yii::$app->params['global_url'] ."uuid_1";
            $curl = new Curl();
            $curl->setOption(CURLOPT_TIMEOUT,90);
            $postString = $curl->setRequestBody(Json::encode($parama))
                ->setHeader('content-type', 'application/json')->post($url);
            echo $postString . "\n";
            $data = Json::decode($postString,false);
            $code = $data->return_code;
            $result = $data->result;
            if ($code !=200){
                if ($index < (count($uuids) - 1)){
                    $index += 1;
                    continue;
                }else {
                    return false;
                }

            }
            $rank = $result->rank;
            echo "标准排名:" .  $rank . "\n";
            $redis = \Yii::$app->redis;
            $key = "standard_rank" . date('Y-m-d',time());
            $redis->set($key,$rank);   //将标准排名放在redis中进行匹配
            return $rank;

        }while($index < count($uuids));

    }

    public function actionTest()
    {

        $redis = \Yii::$app->redis;
        $redis->set('aaaa', 1111);
        echo $redis->get('aaaa');
    }



}