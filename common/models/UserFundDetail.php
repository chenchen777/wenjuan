<?php

namespace common\models;

use Yii;
use yii\widgets\Pjax;

/**
 * This is the model class for table "user_fund_detail".
 *
 * @property int $id
 * @property int $user_id
 * @property string $title 标题
 * @property string $fund_type 资金类型
 * @property string $relate_mode 关联model
 * @property int $relate_id 关联id
 * @property string $amount 消费金额
 * @property string $balance 剩余金额
 * @property int $version
 * @property int $deleted
 * @property int $create_at
 * @property int $update_at
 */
class UserFundDetail extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_fund_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'fund_type', 'relate_mode', 'relate_id', 'amount', 'balance'], 'required'],
            [['user_id', 'relate_id', 'version', 'deleted', 'create_at', 'update_at'], 'integer'],
            [['fund_type'], 'string'],
            [['amount', 'balance'], 'number'],
            [['title'], 'string', 'max' => 100],
            [['relate_mode'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'title' => '标题',
            'fund_type' => '资金类型',
            'relate_mode' => '关联model',
            'relate_id' => '关联id',
            'amount' => '消费金额',
            'balance' => '剩余金额',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

    public static function fundSave($id,$amount,$left_amount,$user_id='')
    {
        $fund = new UserFundDetail();
        if (!empty($user_id)){
            $fund->user_id = $user_id;
        }else{
            $fund->user_id = Yii::$app->user->id;
        }
        $fund->title = '会员购买';
        $fund->fund_type = 'user_level';
        $fund->relate_mode = 'user_level_log';
        $fund->relate_id = $id;
        $fund->amount = $amount;
        $fund->balance = $left_amount;
        if (! $fund->save()){
            return ['result'=>0,'msg'=>$fund->getError()];
        }
        return ['result'=>1];

    }

    public static function getFundDetail($page,$page_size)
    {
        if (empty($page)){
            $page = 1;
        }
        if (empty($page_size)){
            $page_size = 20;
        }
        $data = [];
        $fund = UserFundDetail::find()->where(['user_id'=>Yii::$app->user->id,'deleted'=>0]);
        $jquery = clone $fund;
        $total_count = $jquery->count();
        $total_page = ceil($total_count / $page_size);
        $offset = ($page - 1) * $page_size;
        $results = $fund->orderBy('id desc')->offset($offset)->limit($page_size)->all();
        $data['result'] = 1;
        $data['total_count'] = $total_count;
        $data['total_page'] = $total_page;
        $data['page'] = $page;
        $data['page_size'] = $page_size;

        if (empty($results)){
            return $data;
        }

        $i = 0;
        foreach ($results as $result){
            $amount = "+" . $result->amount;
            if ($result->fund_type == "user_level"){
               $amount =  "-" . $result->amount;
            }
            $data['data'][$i] = [
                'index' => $offset + 1 + $i,
                'time'=>date('Y-m-d H:i',$result->create_at),
                'fund'=>$amount,'source'=>$result->title,
                'left_amount'=>$result->balance];
            $i++;
        }
        return $data;

    }

    public function getUser()
    {
        return $this->hasOne(User::className(),['id'=>'user_id']);
    }

}
