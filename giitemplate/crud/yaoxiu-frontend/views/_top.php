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
    
    <div class="am-g">
        <div class="am-u-sm-12 am-u-md-6">
          <div class="am-btn-toolbar">
            <div class="am-btn-group am-btn-group-xs">
              <a href="<?php echo "<?php echo Url::to('/user/'.\$this->context->id.'/create'); ?>" ?>" class="am-btn am-btn-default"><span class="am-icon-plus"></span> 新增</a>
             
             <?= "    <?php  if(Yii::\$app->controller->action->id != 'index') { \n ?> " ?>
            &nbsp;&nbsp;&nbsp;&nbsp;
          <a href="<?php echo "<?php echo Url::to('index'); ?>" ?>"  class="am-btn am-btn-default">返回</a>
      <?= "    <?php } ?>" ?>
      
            </div>
          </div>
        </div>
        
        <div class="am-u-sm-12 am-u-md-3">
        <form class="form-search" method="get" action="<?php echo "<?php echo Url::to('index'); ?>" ?>">
          <div class="am-input-group am-input-group-sm">
            <input  placeholder="输入名称查找" name='keyword' class="am-form-field" type="text"  value="<?php echo "<?= isset(\$keyword) ? \$keyword : ''; ?>" ?>">
          <span class="am-input-group-btn">
            <button class="am-btn am-btn-default" type="button">搜索</button>
          </span>
          </div>
          </form>
        </div>
      </div>
      
