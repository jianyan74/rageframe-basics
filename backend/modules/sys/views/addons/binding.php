<?php
use jianyan\basics\backend\widgets\menu\AddonLeftWidget;

$this->title = Yii::$app->params['addon']['info']['title'];
$this->params['breadcrumbs'][] = ['label' => '扩展模块','url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Yii::$app->params['addon']['info']['title'],'url' => ['binding','addon' => Yii::$app->params['addon']['info']['name']]];
$this->params['breadcrumbs'][] = ['label' => $this->title];
?>

<div class="col-sm-2" style="width: 13%; height: 100%;background:#fff;">
    <?= AddonLeftWidget::widget(); ?>
</div>