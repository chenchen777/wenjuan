<?php
/**
 * 
 * User: xc
 * Date: 2018/04/08
 * Time: 10:39
 */

namespace console\controllers;

use common\models\ApiCityLists;
use linslin\yii2\curl\Curl;
use yii\console\Controller;
use yii\base\Exception;
use Yii;
use yii\db\Query;
use common\models\LoginIp;
use common\component\PublicHelper;
use common\component\UCloud;
use yii\validators\ValidationAsset;


class FiveMinController extends Controller
{

    public function getTasks()
    {
        return [
            ['name' => 'getCity'],
//             ['name' => 'changeIp'],
            ['name' => 'scanIp'],
        ];
    }
    public function actionIndex()
    {
        foreach ($this->getTasks() as $task) {
            $method = $task['name'];
            if (method_exists($this, $method)) {
                $this->$method();
            }
        }
    }
    // 获取京东城市
    public function actionCityList(){
        Yii::$app->db->createCommand()->truncateTable('api_city_lists')->execute();
        echo "清理完成\n";
        //  一级
        $cat_list = ApiCityLists::getCatFirst();
        //  二级
        foreach($cat_list as $k => $y){
            ApiCityLists::getCatSecond($k,3);
        }
        echo "二级更新完成" . date('Y-m-d H:i',time()) . "\n";
        //  三级
        $cat_list = ApiCityLists::find()->where(['district_level'=>3])->asArray()->all();
        foreach($cat_list as $k => $y){
            ApiCityLists::getCatSecond($y['id'],4);
        }
        echo "三级更新完成" . date('Y-m-d H:i',time()) . "\n";
    }
    /**
     * 获取ip对应的城市
     */
    public function getCity()
    {
        $ip = LoginIp::find()->where(['deleted' => 0, 'province' => ''])->andwhere(['>', 'admin_id', 0])->all();
        if (empty($ip)) {
            Yii::$app->db->close();
            echo "没有需要采集的ip\n";
            return;
        }
        foreach ($ip as $key => $value) {
            $res = PublicHelper::getIpCity($value->ip);
            if (!empty($res)) {
                $ip[$key]->city = $res['city'];
                $ip[$key]->province = $res['province'];
            } else {
                $ip[$key]->city = '未知';
                $ip[$key]->province = '未知';
            }
            $value->save();
        }
        echo "ip城市采集成功\n";
        Yii::$app->db->close();
    }
    
    /**
     * 切换弹性ip
     */
    public function changeIp()
    {
        //从数据库获取需要替换的ip资源id及需要替换的资源id 1.解绑 2.释放 3.修改出口权重 4.申请弹性ip 5.绑定弹性ip
        echo "开始切换弹性ip\n";
        $oldId = '123';
        $newId = '456';
        $hostId = '789';
        $private_key = "123";
        $public_key = "456";
        $ucloud = new UCloud($private_key, $public_key);
        $res = $ucloud->unBind($oldId, $hostId, $type='uhost');
        if ($res->RetCode !=0 ) {   //解绑失败
            echo "解绑".$res->Message."\n";
        }
        $res = $ucloud->release($oldId);
        if ($res->RetCode !=0 ) {   //释放失败
            echo "释放".$res->Message."\n";
        }
        $res= $ucloud->modifyEIPWeight($newId, $hostId, '100');
        if ($res->RetCode !=0 ) {   //修改权重失败
            echo "修改出口权重".$res->Message."\n";
        }
        $apply = $ucloud->apply();
        if ($apply->RetCode !=0 ) {   //申请弹性ip失败
            echo "申请弹性ip".$res->Message."\n";
        }
        $res = $ucloud->bind($apply->EIPSet->EIPId, $hostId, $type='uhost');
        if ($res->RetCode !=0 ) {   //绑定失败
            echo "绑定".$res->Message."\n";
        }
        //保存到数据库
        
        echo "弹性ip成功";
        
        
    }
    
    /**
     * 把请求多的ip加入防火墙黑名单
     */
    public function scanIp()
    {
        echo "查询redis里访问ip\n";
        $redis = Yii::$app->redis;
        $data = $redis->zrangebyscore(date('Y-m-d'), 3000, 100000);
        foreach ($data as $value) {
            echo $value ." 加入防火墙黑名单\n";
            system("iptables -A INPUT -s $value -p tcp --dport 80 -j DROP");
            $redis->zadd(date('Y-m-d'), 2500, $value);
        }
//         var_dump($data);exit;
        
        
        
    }
    


    
}