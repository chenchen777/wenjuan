<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2016/10/12
 * Time: 下午3:39
 */

namespace api\models;

use yii\db\ActiveRecord;

class Base extends ActiveRecord
{
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

