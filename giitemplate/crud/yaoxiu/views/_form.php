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

<div class="portlet box green">
         <div class="portlet-title">
          <div class="caption">
           <i class="fa fa-gift"></i>管理
          </div>

         </div>
         <div class="portlet-body form">
          <!-- BEGIN FORM-->
          <?= "<?php " ?>$form = ActiveForm::begin([
           'id' => '<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-form',  
           'options' => ['class' => 'form-horizontal'],  
            'fieldConfig' => [  
                'template' => "<div class=\"form-group\">{label}\n<div class=\"col-md-4\">{input}\n{error}</div></div>",  
                'labelOptions' => ['class' => 'col-md-3 control-label'],  
            ],  
          ]); ?>
        
           <div class="form-body">

          <?php foreach ($generator->getColumnNames() as $attribute) {
            if (in_array($attribute, $safeAttributes)) {
                echo "    <?= " . $generator->generateActiveField($attribute) . " ?>\n\n";
            }
         } ?>
                          
   
           <div class="form-actions">
            <div class="row">
             <div class="col-md-offset-3 col-md-9">
              <button type="submit" class="btn btn-circle blue">保  存</button>
              <button type="button" onclick="history.go(-1);" class="btn btn-circle default">返回</button>
             </div>
            </div>
           </div>
        <?= "<?php " ?>ActiveForm::end(); ?>
          <!-- END FORM-->
         </div>
        </div>
        
