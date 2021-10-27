<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "investigation".
 *
 * @property int $id 主键id
 * @property int $is_use 您会使用一键下单工具吗? 1会，2不会
 * @property int $order_frequency 您使用一键下单的频率是多久一次呢？1业务量很多，每天都会用，2业务量不多，平均一周2～3，3偶尔使用，4从来不用
 * @property int $purchase_order_num 您每天会采购多少订单量？1,50单以内,2,51～200单,3,200～1000单,4,1000单以上
 * @property int $artificial_num 您会耗费多少人工去处理货源采购？1,1人2,2～5人,3,5～10人,10人以上
 * @property int $is_auto_pay 您希望能自动支付吗 1,需要,2,不需要,3,无所谓
 * @property int $is_goods_config 您希望有备用采购商品配置吗?1,需要,2不需要,3,无所谓,4其他
 * @property string|null $solve_problem 您希望软件能为您解决什么问题（多选）?1,自动下单,2,自动同步发货3,采购盈利财务账单,4,能处理售后,5,预锁定采购库存,
 * @property string|null $other_problem 其他问题
 * @property string $error 您在采购过程中经常出现的错误是什么？1,sku采购错误,2,地址错误,3,数量错误,4其他
 * @property string $is_continue 发现采购价亏损后您会继续采购吗？1接受亏损，继续下单给用户发货2,和买家协商退款,3和买家协商补款,4其他
 * @property string $platform 您会在什么平台做无货源电视（多选）1,抖音,2,拼多多,3淘宝,4,其他
 * @property string $support 您希望一键下单为您提供更多什么支持？
 * @property string $personal_info 如您愿意进一步参与平台调研和反馈，希望您留下联系电话和姓名进一步沟通，非常感谢
 * @property int $create_at
 * @property int $update_at
 * @property int $version
 * @property int $deleted
 */
class Investigation extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'investigation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_use', 'order_frequency', 'purchase_order_num', 'artificial_num', 'is_auto_pay', 'is_goods_config', 'error', 'is_continue', 'platform', 'support', 'personal_info'], 'required'],
            [['is_use', 'order_frequency', 'purchase_order_num', 'artificial_num', 'is_auto_pay', 'is_goods_config'], 'integer'],
            [['solve_problem', 'other_problem', 'error', 'is_continue', 'platform', 'support', 'personal_info'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'is_use' => 'Is Use',
            'order_frequency' => 'Order Frequency',
            'purchase_order_num' => 'Purchase Order Num',
            'artificial_num' => 'Artificial Num',
            'is_auto_pay' => 'Is Auto Pay',
            'is_goods_config' => 'Is Goods Config',
            'solve_problem' => 'Solve Problem',
            'other_problem' => 'Other Problem',
            'error' => 'Error',
            'is_continue' => 'Is Continue',
            'platform' => 'Platform',
            'support' => 'Support',
            'personal_info' => 'Personal Info',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'version' => 'Version',
            'deleted' => 'Deleted',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if($insert) {
                $this->version = 0;
                $this->deleted = 0;
                $this->create_at = time();
                $this->update_at = time();
            } else {
                $this->version = $this->version + 1;
                $this->update_at = time();
            }
            return true;
        } else {
            return false;
        }
    }
}
