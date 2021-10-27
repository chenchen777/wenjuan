<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_pay_log".
 *
 * @property int $id
 * @property string $order_no 订单号
 * @property string $trader_no 支付(交易)单号
 * @property int $user_id 支付用户ID
 * @property string $pay_account 买家支付账号
 * @property int $pay_type 1 支付宝 2微信
 * @property string $relate_model
 * @property int $relate_id
 * @property int $pay_status 0 待支付 1支付完成 -1 支付失败
 * @property string $error_msg 订单失败信息
 * @property string $order_price 订单金额
 * @property string $pay_fee 实际支付金额
 * @property int $success_time 支付成功时间
 * @property int $version
 * @property int $deleted
 * @property int $create_at
 * @property int $update_at
 */
class UserPayLog extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_pay_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'pay_type', 'pay_status', 'success_time','relate_id', 'version', 'deleted', 'create_at', 'update_at'], 'integer'],
            [['order_no', 'trader_no'], 'string', 'max' => 64],
            [['pay_account','error_msg','relate_model'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_no' => 'Order No',
            'trader_no' => 'Trader No',
            'user_id' => 'User ID',
            'pay_account' => 'Pay Account',
            'pay_type' => 'Pay Type',
            'relate_model' => 'Relate Model',
            'relate_id' => 'Relate Id',
            'pay_status' => 'Pay Status',
            'error_msg' => 'Error Msg',
            'order_price' => 'Order Price',
            'pay_fee' => 'Pay Fee',
            'success_time' => 'Success Time',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

    /**
     * @param $amount
     * @param $order_no
     * @return bool
     * 订单预生成记录
     */
    public static function payLogSave($amount,$order_no,$id)
    {
        $pay = new UserPayLog();
        $pay->user_id = Yii::$app->user->id;
        $pay->order_no = $order_no;
        $pay->pay_type = 1;
        $pay->pay_status = 0;
        $pay->relate_model = 'user_level_log';
        $pay->relate_id = $id;
        $pay->order_price = $amount;

        try {
            if (!$pay->save()) {
                return false;
            }
        }catch (\Exception $e){
            return false;
        }
        return true;
    }


    /**
     * @param $order_no
     * @param $trader_no
     * @param $pay_account
     * @param $pay_fee
     * @param $time
     * @return
     * 订单完成
     */
    public static function hookSave($order_no,$trader_no,$pay_account,$pay_fee,$time)
    {
        $payHook = UserPayLog::findOne(['order_no'=>$order_no]);
        $payHook->trader_no = $trader_no;
        $payHook->pay_account = $pay_account;
        $payHook->pay_status = 1;
        $payHook->pay_fee =round($pay_fee,2);
        $payHook->success_time = $time;
        if (! $payHook->save()){
            return ['result'=>0,'msg'=>$payHook->getError()];
        }
        return ['result'=>1];

    }

    /**
     * @param $order_no
     * @param $trader_no
     * @param $pay_account
     * @param $pay_fee
     * @param $time
     * @param $error
     * @return array
     * 错误记录保存
     */
    public static function errSave($order_no,$trader_no,$pay_account,$pay_fee,$time,$error)
    {
        $payHook = UserPayLog::findOne(['order_no'=>$order_no]);
        $payHook->trader_no = $trader_no;
        $payHook->pay_account = $pay_account;
        $payHook->pay_status = -1;
        $payHook->error_msg = $error;
        $payHook->pay_fee =round($pay_fee,2);
        $payHook->success_time = $time;
        if (! $payHook->save()){
            return ['result'=>0,'msg'=>$payHook->getError()];
        }
        return ['result'=>1];
    }

}
