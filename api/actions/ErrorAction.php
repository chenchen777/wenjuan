<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2017/3/18
 * Time: 14:49
 */

namespace api\actions;

class ErrorAction extends \yii\web\ErrorAction
{
    public function run()
    {
        $ren = [
            'result' => 404,
//            'data' => [
//                'code' => $this->getExceptionCode(),
//                'name' => $this->getExceptionName(),
//                'message' => $this->getExceptionMessage(),
//            ]
            'msg' =>$this->getExceptionMessage(),
//                'token失效'
            'code' => $this->getExceptionCode(),
//                'name' => $this->getExceptionName(),
//                'message' => $this->getExceptionMessage(),

        ];
        if($ren['code'] == 401){
            $ren['msg'] = 'token失效';
            unset($ren['code']);
        }else{
            $ren['result'] = 500;
            unset($ren['code']);
        }
        return $ren;
    }
}