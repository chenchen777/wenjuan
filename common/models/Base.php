<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2017/3/18
 * Time: 14:31
 */

namespace common\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use Yii;

class Base extends ActiveRecord
{
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute'=>'create_at',
                'updatedAtAttribute'=>'update_at',
            ]
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if($insert) {
                $this->version = 0;
                $this->deleted = 0;
            }
            return true;
        } else {
            return false;
        }
    }

    public function getError()
    {
        $errors = array_values($this->errors);
        $first = reset($errors);empty($errors)?[]:$errors[0];
        return empty($first)?'':reset($first);
    }

    public function optimisticLock()
    {
        return 'version';
    }

    public static function lockBySql($sql,$lock=0)
    {
        if($lock and Yii::$app->db->getTransaction()) {
            return self::findBySql($sql . ' for update')->one();
        }
        return  self::findBySql($sql)->one();
    }

    public static function lockAllBySql($sql,$lock=0)
    {
        if($lock and Yii::$app->db->getTransaction()) {
            return self::findBySql($sql . ' for update')->all();
        }
        return  self::findBySql($sql)->all();
    }
    
    /**
     * 取得根域名
     * @param string $domain 域名
     * @return string 返回根域名
     */
    public static function getRootDomain($domain) {
        $re_domain = '';
        $domain_postfix_cn_array = array("com", "net", "org", "gov", "edu", "com.cn", "cn","cc",'mobi');
        $array_domain = explode(".", str_replace('http://','', $domain));
        $array_num = count($array_domain) - 1;
        //解决 xx.com/a/造成根为  com/a这种
        $roots = explode('/', $array_domain[$array_num]);
        if ($roots[0] == 'cn') {  //org.cn gov.cn cn等
            if (in_array($array_domain[$array_num - 1], $domain_postfix_cn_array)) {
                $re_domain = $array_domain[$array_num - 2] . "." . $array_domain[$array_num - 1] . "." . $roots[0];
            } else {
                $re_domain = $array_domain[$array_num - 1] . "." . $roots[0];
            }
        } else {
            $re_domain = $array_domain[$array_num - 1] . "." . $roots[0];
        }
        return $re_domain;
    }
}