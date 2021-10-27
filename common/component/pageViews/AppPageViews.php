<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2017/4/25
 * Time: 21:11
 */

namespace common\component\pageViews;

use common\Helper\Helper;
use yii\base\Component;
use linslin\yii2\curl\Curl;
use yii\base\Exception;
use yii\helpers\Json;

class AppPageViews extends PageViews
{
    protected $create = 'http://www.zhangliuliang.net/Api/jd';
    protected $task_start = 'http://www.zhangliuliang.net/Api/start';
    protected $task_show =  "http://www.zhangliuliang.net/Api/getjd";

    public function taskCreate($params)
    {
        $curl = new Curl();
        $params['key'] = $this->api_key;
        $response = $curl->setOption(
            CURLOPT_POSTFIELDS,
            http_build_query($params))
            ->post($this->create);

        return $this->parseResponse($response);
    }

    public function generatorParams($name,$url,$num,$keyword)
    {
        $match = preg_match("/item\.jd\.com\/\d+\.html/i",$url,$matches);
        if(empty($match)){
            throw new Exception('app流量地址错误');
        }
        $jd_url = $matches[0];
        //$url = preg
        $params = [];
        $params['task_name'] = $name;
        $params['task_keys'] = $keyword;
        $params['total_num'] = $num;
        $params['task_url'] = $jd_url;
        $params['task_c'] = 30;
        $params['task_p'] = 30;
        $params['pcpv'] = rand(1,3);
        return $params;
    }

    public function taskStart($id, $start = 0, $end = 0)
    {
        $curl = new Curl();

        $params['key'] = $this->api_key;
        $params['tid'] = $id;
        //$end = strtotime(date('Y-m-d',time())) + 24 * 3600;
        if (!empty($start)) {
            $params['startTime'] = $start;
            //$params['endTime'] = $end;
        } else {
            $params['starttime'] = time();
            //$params['endTime'] = $end;//time() + 3600 * 24;
        }

        $response = $curl->setOption(
            CURLOPT_POSTFIELDS,
            http_build_query($params))
            ->post($this->task_start);

        return $this->parseResponse($response);
    }

    public function taskShow($id)
    {
        $curl = new Curl();
        $params['key'] = $this->api_key;
        $params['tid'] = $id;
        $response = $curl->setOption(
            CURLOPT_POSTFIELDS,
            http_build_query($params))
            ->post($this->task_show);

        return $this->parseResponse($response);
    }

    protected function getMessage($code)
    {
        $errors = [
            403=>'用户不存在',
            101=>'京东链接不对',
            110=>'账户余额不足',
            500=>'系统未知异常',
        ];
        if(isset($errors[$code])){
            return $errors[$code];
        }else{
            return '';
        }
    }

    protected function parseResponse($response)
    {
        $response = Helper::remove_utf8_bom($response);
        $response = Json::decode($response);
        if (isset($response['codes'])  and $response['codes'] == 200) {
            return $response;
        } elseif (isset($response['error'])) {
            $this->error = $this->getMessage($response['error']);
            if(empty($this->error)){
                $this->error = Json::encode($response);
            }
            return false;
        } else {
            $this->error = '未知错误';
            return false;
        }
    }
}