<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_recharge".
 *
 * @property int $id
 * @property string $recharge_no 充值单号
 * @property int $user_id
 * @property int $user_level_log_id
 * @property string $user_card_owner 转出卡主
 * @property string $amount 充值金额
 * @property string $balance 充值完成后余额
 * @property string $balance_pre 充值完成前余额
 * @property string $remark 附加摘要
 * @property int $web_card_id 转入银行卡
 * @property string $web_bank_code 转入银行
 * @property string $web_bank_point 转入银行网点
 * @property string $web_card_no 转入卡号
 * @property string $web_card_owner 注入卡主
 * @property string $memo 备注
 * @property int $admin_id 操作管理员ID
 * @property int $admin_check_time 审核时间
 * @property int $status 1充值成功，-1审核失败，0待审核
 * @property int $create_at
 * @property int $update_at
 * @property int $deleted
 * @property int $version
 */
class UserRecharge extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_recharge';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['recharge_no', 'user_id','user_level_log_id', 'amount', 'web_bank_code', 'web_bank_point', 'web_card_no'], 'required'],
            [['user_id','user_level_log_id', 'web_card_id', 'admin_id', 'admin_check_time', 'status', 'create_at', 'update_at', 'deleted', 'version'], 'integer'],
            [['amount', 'balance', 'balance_pre'], 'number'],
            [['recharge_no', 'remark', 'web_bank_code'], 'string', 'max' => 20],
            [['user_card_owner'], 'string', 'max' => 16],
            [['web_bank_point'], 'string', 'max' => 50],
            [['web_card_no', 'web_card_owner'], 'string', 'max' => 30],
            [['memo'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'recharge_no' => '充值单号',
            'user_id' => 'User ID',
            'user_level_log_id' => 'User Level Log ID',
            'user_card_owner' => '转出卡主',
            'amount' => '充值金额',
            'balance' => '充值完成后余额',
            'balance_pre' => '充值完成前余额',
            'remark' => '附加摘要',
            'web_card_id' => '转入银行卡',
            'web_bank_code' => '转入银行',
            'web_bank_point' => '转入银行网点',
            'web_card_no' => '转入卡号',
            'web_card_owner' => '注入卡主',
            'memo' => '备注',
            'admin_id' => '操作管理员ID',
            'admin_check_time' => '审核时间',
            'status' => '1充值成功，-1审核失败，0待审核',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'deleted' => 'Deleted',
            'version' => 'Version',
        ];
    }

    public static function rechargeSave($remark,$bank_name,$bank_no,$amount,$log_id)
    {
        $user = User::findOne(['id'=>Yii::$app->user->id]);
        $recharge = new UserRecharge();
        $recharge->recharge_no = time() . rand(1000,9999);
        $recharge->user_id = $user->id;
        $recharge->user_level_log_id = $log_id;
        $recharge->user_card_owner = '';
        $recharge->amount = $amount;
        $recharge->balance = $user->balance;
        $recharge->balance_pre = $user->balance;
        $recharge->remark = $remark;
        $recharge->web_bank_code = "ccb";
        $recharge->web_bank_point = $bank_name;
        $recharge->web_card_no = $bank_no;
        $recharge->web_card_owner = '';
        $recharge->memo = '会员购买';
        $recharge->admin_id = 1;
        $recharge->status = 0;

        if (! $recharge->save()){
            return ['result'=>0,'msg'=>$recharge->getError()];
        }
        return ['result'=>1];
    }
}
