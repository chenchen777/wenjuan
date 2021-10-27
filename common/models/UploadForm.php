<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2016/10/25
 * Time: 下午1:08
 */

namespace common\models;

use yii;
use \UCloud\Utils\Util;
use yii\base\Model;
use common\component\Params;
use yii\base\Exception;

class UploadForm extends Model
{
    public function generateKey($type,$ext,$tmpName,$localname)
    {
        $types = Yii::$app->params['UCloud_FILE_PATH'];

        if(!isset($types[$type])){
            throwException('未知类型');
        }
        $path = $types[$type];
        //$path .= date('Ym').'/';
        list($filename,$error) = Util::UCloud_FileHash($tmpName,$localname);//date("YmdHis").rand(100,999);
        return $path . $filename . '.' . $ext;
    }
}