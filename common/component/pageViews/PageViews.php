<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2017/4/25
 * Time: 21:32
 */

namespace common\component\pageViews;

use yii\base\Component;
use linslin\yii2\curl\Curl;
use yii\helpers\Json;

class PageViews extends Component
{
    public $api_key;
    public $error;

    protected $task_start = 'http://service.liuliangbao.cn/api/v2/service.start';

    public function taskStart($id, $start = 0, $end = 0)
    {
        $curl = new Curl();

        $params['apiKey'] = $this->api_key;
        $params['taskId'] = $id;
        $end = strtotime(date('Y-m-d',time())) + 24 * 3600;
        if (!empty($start)) {
            $params['startTime'] = $start;
            $params['endTime'] = $end-1;
        } else {
            $params['startTime'] = time();
            $params['endTime'] = $end-1;//time() + 3600 * 24;
        }

        $response = $curl->setOption(
            CURLOPT_POSTFIELDS,
            http_build_query($params))
            ->post($this->task_start,false);
        return $this->parseResponse($response);
    }

    protected function parseResponse($response)
    {
        if (isset($response['status']) and isset($response['status']['code']) and $response['status']['code'] == 100) {
            return $response;
        } elseif (isset($response['status']) and isset($response['status']['message'])) {
            $this->error = Json::encode($response);//$response['status']['message'];
            return false;
        } else {
            $this->error = '未知错误';
            return false;
        }
    }
}