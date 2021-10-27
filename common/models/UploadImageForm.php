<?php
/**
 * Created by PhpStorm.
 * User: sfng
 * Date: 2016/10/25
 * Time: 上午11:31
 */

namespace common\models;

use yii\web\UploadedFile;

class UploadImageForm extends UploadForm
{
    /**
     * @var UploadedFile
     */
    public $file;

    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, jpeg,jpe'],
        ];
    }


}