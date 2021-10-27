<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

use yii\helpers\Html;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();

echo "<?php\n";
?>

use yii\helpers\Html;
use <?= $generator->indexWidgetType === 'grid' ? "yii\\grid\\GridView" : "yii\\widgets\\ListView" ?>;
<?= $generator->enablePjax ? 'use yii\widgets\Pjax;' : '' ?>

/* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : '' ?>
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>;
$this->params['breadcrumbs'][] = $this->title;
?>



<div class="portlet box grey-cascade">
      <div class="portlet-title">
       <div class="caption">
        <i class="fa fa-cogs"></i><?= $this->title ?>
       </div>
       
      </div>
      
      <div class="portlet-body">
      <?php echo "<?php include('_top.php'); ?>\n" ?>

            <div class="table-responsive">
      
      
      <?php if(!empty($generator->searchModelClass)): ?>
<?= "    <?php " . ($generator->indexWidgetType === 'grid' ? "// " : "") ?>echo $this->render('_search', ['model' => $searchModel]); ?>
<?php endif; ?>

   
<?= $generator->enablePjax ? '<?php Pjax::begin(); ?>' : '' ?>
<?php if ($generator->indexWidgetType === 'grid'): ?>
    <?= "<?= " ?>GridView::widget([
        'dataProvider' => $dataProvider,
        <?= !empty($generator->searchModelClass) ? "'filterModel' => \$searchModel,\n        'columns' => [\n" : "'columns' => [\n"; ?>
            ['class' => 'yii\grid\SerialColumn'],

<?php
$count = 0;
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        if (++$count < 6) {
            echo "            '" . $name . "',\n";
        } else {
            echo "            // '" . $name . "',\n";
        }
    }
} else {
    foreach ($tableSchema->columns as $column) {
        $format = $generator->generateColumnFormat($column);
        if (++$count < 6) {
            echo "            '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
        } else {
            echo "            // '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
        }
    }
}
?>

             ['class' => 'yii\grid\ActionColumn',
                'header' => '操作',
                'template' => '{view} {update} {delete}',
            ],
        ],
    ]); ?>
<?php else: ?>
     <table class="table table-bordered table-striped">
        <thead>
         <th> </th>
         <th>任务编号</th>
         <th>商家</th>
         <th >任务</th>
         <th>担保金</th>
         <th>数量</th>
         <th>单价</th>
         <th>费用合计</th>
         <th>进度</th>
         <th>状态</th>
         <th>发布时间</th>
         <th> </th>
        </thead>
        <tbody>
    <?= "<?= " ?>ListView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}\n<div style=\'float:right\'>{summary}{pager}</div>',
        'itemView' => '_item',
        'itemOptions' => ['class' => 'item'],
    ]) ?>   
      
       </tbody>
       </table>
<?php endif; ?>
      
      </div>
           </div>
     </div>
<?= $generator->enablePjax ? '<?php Pjax::end(); ?>' : '' ?>


