<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2017/4/6
 * Time: 21:03
 */

namespace common\component\pageViews;

use linslin\yii2\curl\Curl;
use yii\base\Component;

class PcPageViews extends PageViews
{
    protected $create = 'http://service.liuliangbao.cn/api/v2/task.pc.create';
    protected $task_show = 'http://service.liuliangbao.cn/api/v2/task.pc.view';

    public function taskCreate($params)
    {
        $curl = new Curl();
        $params['apiKey'] = $this->api_key;
        $response = $curl->setOption(
            CURLOPT_POSTFIELDS,
            http_build_query($params))
            ->post($this->create,false);

        return $this->parseResponse($response);
    }

    public function taskShow($id)
    {
        $curl = new Curl();
        $params['apiKey'] = $this->api_key;
        $params['taskId'] = $id;
        $response = $curl->setOption(
            CURLOPT_POSTFIELDS,
            http_build_query($params))
            ->post($this->task_show,false);

        return $this->parseResponse($response);
    }
}