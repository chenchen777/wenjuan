<?php
/**
 * 配置参数类
 * 读取渠道 代理 总站配置参数
 * 渠道和代理读取redis 总站读取配置文件
 * reids存储 'domain_'.$domain = ['type' =>2,'id'=>1] 即站点类型,相应id值
 * @author tang
 *
 */

namespace api\models;

use yii;
use yii\base\Model;
use yii\base\Exception;

class Config extends Model
{
    CONST OWNER_DOMAIN = 1;  //总站
    CONST DISTRIBUTOR_DOMAIN = 2; //渠道站
    CONST AGENT_DOMAIN = 3; //代理站

    /***
     * 根据域名获取站点类型及id
     * @param string $domain
     * @return number[]|mixed
     */
    public static function getDomainData($domain)
    {
        print_r($domain);exit();
        if ($domain == Yii::$app->params['domain']) {
            return ['type' => 1, 'id' => 0, 'distributor_id' => 0, 'webname' => Yii::$app->params['webname']];
        }
//        $data = Yii::$app->redis->hgetall('domain_' . trim($domain));
//        if ($data) {
//            $agent = Agent::findOne(['domain' => trim($domain) ,'deleted' => 0]);
//            if(! $agent) {
//                return [];
//            }
//
//            return [$data[0] => $data[1], $data[2] => $data[3], $data[4] => $data[5], $data[6] => $data[7]];
//        } else {
//            //优先渠道
//            if (self::setDistributorParamsByDomain($domain)) {
//                $data = Yii::$app->redis->hgetall('domain_' . trim($domain));
//                if ($data) {
//                    return [$data[0] => $data[1], $data[2] => $data[3], $data[4] => $data[5], $data[6] => $data[7]];
//                }
//            } else {  //当前站不是渠道站 尝试获取代理站
//                if (self::setAgentParamsByDomain($domain)) {
//                    $data = Yii::$app->redis->hgetall('domain_' . trim($domain));
//                    if ($data) {
//                        return [$data[0] => $data[1], $data[2] => $data[3], $data[4] => $data[5], $data[6] => $data[7]];
//                    }
//                }
//            }
//        }
        return [];
    }

    /****
     * 获取配置参数
     * @param int $domainType 网站类型 1总站 2渠道 3代理
     * @param int $id //对应的渠道或者代理id,domaintype=1时为0
     * @param string $key //对应配置文件的key
     * @return string
     * @throws Exception
     */
    public static function getConfig($domainType, $id, $key)
    {
        if (empty ($key))
            return '';
        //总站，读取配置文件
        $domainType = 1;
        if ($domainType == 1) {
            return self::getFileParams($key);
        } elseif ($domainType == 2) {
            return self::getDistributorParams($id, $key);
        } elseif ($domainType == 3) {
            return self::getAgentParams($id, $key);
        } else {
            throw new Exception('系统出错');
        }
    }

    /***
     * 总站获取配置文件信息
     * 等同读取方式为 Yii::$app->params
     * @param string $key
     * @return string
     */
    protected static function getFileParams($key)
    {
        $value = empty(Yii::$app->params[$key])?'':Yii::$app->params[$key];
        if (!$value)
            return '';

        return $value;
    }

    /***
     * 读取渠道配置参数
     * @param integer $distributorid
     * @param string $key
     * @return string
     */
    protected static function getDistributorParams($distributorid, $key)
    {
        if (empty($distributorid) || empty($key)) {
            return '';
        }
        if (!Yii::$app->redis->get('distributor_params_' . $distributorid)) {
            self::setDistributorParams($distributorid);
        }
        $json = Yii::$app->redis->get('distributor_params_' . $distributorid);
        $json_arr = json_decode($json);
        return $json_arr->$key;
    }

    /***
     * 读取代理配置参数
     * @param integer $agentid
     * @param string $key
     * @return string
     */
    protected static function getAgentParams($agentid, $key)
    {
        if (empty($agentid) || empty($key)) {
            return '';
        }
        if (!Yii::$app->redis->get('agent_params_' . $agentid)) {
            self::setAgentParams($agentid);
        }
        $json = Yii::$app->redis->get('agent_params_' . $agentid);
        $json_arr = json_decode($json);
        return $json_arr->$key;
    }


    /*
     * 根据渠道id设置渠道参数
     */
    public static function setDistributorParams($distributorid)
    {
        $distributor = Distributor::findOne(['id' => $distributorid, 'deleted' => 0]);
        if ($distributor) {
            $params = json_encode($distributor->attributes);
            Yii::$app->redis->hmset('domain_' . $distributor->domain, 'type', '2', 'id', $distributor->id, 'distributorid', $distributor->id, 'webname', $distributor->webname);
            Yii::$app->redis->set('distributor_params_' . $distributor->id, json_encode($distributor->attributes));
            return true;
        }
        return false;
    }

    /**
     * 根据域名设置渠道参数
     * @param string $domain
     * @return bool
     */
    protected static function setDistributorParamsByDomain($domain)
    {
        $distributor = Distributor::findOne(['domain' => trim($domain), 'deleted' => 0]);
        if ($distributor) {
            Yii::$app->redis->hmset('domain_' . $distributor->domain, 'type', '2', 'id', $distributor->id, 'distributorid', $distributor->id, 'webname', $distributor->webname);
            Yii::$app->redis->set('distributor_params_' . $distributor->id, json_encode($distributor->attributes));
            return true;
        }
        return false;
    }


    /**
     * 根据渠道id设置渠道参数
     */
    public static function setAgentParams($agentid)
    {
        $agent = Agent::findOne(['id' => $agentid, 'deleted' => 0]);
        if ($agent) {
            Yii::$app->redis->hmset('domain_' . $agent->domain, 'type', '3', 'id', $agent->id, 'distributorid', $agent->distributor_id, 'webname', $agent->webname);
            Yii::$app->redis->set('agent_params_' . $agent->id, json_encode($agent->attributes));
            return true;
        }
        return false;
    }

    /**
     * 根据域名设置代理参数
     * @param string $domain
     * @return bool
     */
    protected static function setAgentParamsByDomain($domain)
    {
        $agent = Agent::findOne(['domain' => trim($domain), 'deleted' => 0]);
        if ($agent) {
            Yii::$app->redis->hmset('domain_' . $agent->domain, 'type', '3', 'id', $agent->id, 'distributorid', $agent->distributor_id, 'webname', $agent->webname);
            Yii::$app->redis->set('agent_params_' . $agent->id, json_encode($agent->attributes));
            return true;
        }
        return false;
    }

    /***
     * 释放渠道配置
     * @param integer $distributorid
     */
    public static function unsetDistributorParams($distributorid)
    {
        $distributor = Distributor::findOne(['id' => $distributorid, 'deleted' => 0]);
        if ($distributor) {
            Yii::$app->redis->del('domain_' . $distributor->domain);
            Yii::$app->redis->del('distributor_params_' . $distributor->id);
        }
    }

    /***
     * 释放代理配置
     * @param integer $agentid
     */
    public static function unsetAgentParams($agentid)
    {
        $agent = Agent::findOne(['id' => $agentid]);
        Yii::$app->redis->del('domain_' . $agent->domain);
        Yii::$app->redis->del('agent_params_' . $agentid);
    }


    /**
     * 获取logo
     * @param unknown $domainType
     * @param unknown $id
     * @return string|mixed|string|unknown
     */
    public static function getLogo($domainType, $id)
    {
        $logo = self::getConfig($domainType, $id, 'logo');
        if ($domainType == self::OWNER_DOMAIN) {
            return $logo;
        } else {
            return OssHelper::getImgUrl($logo, 4, 80, 317);
        }
    }

    public static function getServiceQq($domainType, $id)
    {
        return self::getConfig($domainType, $id, 'service_qq');
    }
}