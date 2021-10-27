<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

echo "<?php\n 
    use yii\helpers\Html; \n
    use yii\helpers\Url; \n
    ?>";
?>

<?php
 
    use yii\helpers\Html; 

    use yii\helpers\Url; 

    ?>

<div class="page-head" style="text-align: center">
  <div class="span12">
      <form class="form-search" method="get" action="<?php echo "<?php echo Url::to('index'); ?>" ?>">
      <div class="alert alert-block  fade in">
          <input type="text" placeholder="输入名称查找" name='keyword' class="m-wrap large"
             value="<?php echo "<?= isset(\$keyword) ? \$keyword : ''; ?>" ?>">
          <button type="submit" class="btn">查找<iclass="m-icon-swapright m-icon-white"></i></button>

<a href="<?php echo "<?php echo Url::to('/'.\$this->context->id.'/create'); ?>" ?>" class="btn blue">新  增</a>
      <?= "    <?php  if(Yii::\$app->controller->action->id != 'index') { \n ?> " ?>
            &nbsp;&nbsp;&nbsp;&nbsp;
          <a href="<?php echo "<?php echo Url::to('index'); ?>" ?>" class="btn default">返回</a>
      <?= "    <?php } ?>" ?></div>
      </div>

      
      </form>
      
 </div>



