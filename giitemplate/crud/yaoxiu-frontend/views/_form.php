<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;




/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

/* @var $model \yii\db\ActiveRecord */
$model = new $generator->modelClass();
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
/* @var $form yii\widgets\ActiveForm */
?>




<?php echo "<?php include('_top.php'); ?>\n" ?>



<div class="admin-content">
    <div class="admin-content-body">
      <div class="">
        <div class=""><strong class="">个人资料</strong> / <small>Personal information</small></div>
      </div>

      <hr>

      <div class="am-g">

        <div class="col-md-12 am-u-md-8 am-u-md-pull-4">
          <!-- BEGIN FORM-->
          <?= "<?php " ?>$form = ActiveForm::begin([
           'id' => '<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-form',  
           'options' => ['class' => 'form-horizontal'],  
            'fieldConfig' => [  
                'template' => "<div class=\"\">{label}\n<div class=\"col-md-4\">{input}\n{error}</div></div>",  
                'labelOptions' => ['class' => 'col-md-3 am-form-label'],  
            ],  
          ]); ?>
          
          <?php foreach ($generator->getColumnNames() as $attribute) {
            if (in_array($attribute, $safeAttributes)) {
                echo "    <?= " . $generator->generateActiveField($attribute) . " ?>\n\n";
            }
         } ?>
         
         

            <div class="am-form-group">
              <div class="col-md-9 col-md-3">
              <button type="submit" class="btn btn-circle blue">保  存</button>
              <button type="button" onclick="javascript::history.go(-1);" class="btn btn-circle default">返回</button>
              </div>
            </div>
          <?= "<?php " ?>ActiveForm::end(); ?>
        </div>
      </div>
    </div>


  </div>

