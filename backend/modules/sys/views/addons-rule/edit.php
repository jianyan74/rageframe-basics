<?php
use yii\widgets\ActiveForm;
use jianyan\basics\backend\widgets\menu\AddonLeftWidget;

$this->title = $title;
$this->params['breadcrumbs'][] = ['label' =>  '扩展模块','url' => ['index']];
$this->params['breadcrumbs'][] = ['label' =>  $addonModel['title'],'url' => ['binding','addon' => $addonModel['name']]];
$this->params['breadcrumbs'][] = ['label' =>  $this->title];
?>

<div class="col-sm-2" style="width: 15%; height: 100%;background:#fff;">
    <?= AddonLeftWidget::widget(); ?>
</div>
<div class="col-sm-10" style="width: 85%;padding-left: 0;padding-right: 0;">
    <div class="wrapper wrapper-content animated fadeInRight">
        <?php $form = ActiveForm::begin(); ?>
        <div class="row">
            <?= $this->render('@basics/backend/modules/wechat/views/common/rule_edit_base',[
                'form'          => $form,
                'rule'          => $rule,
                'keyword'       => $keyword,
                'ruleKeywords'  => $ruleKeywords,
            ])?>
            <div class="col-sm-12">
                <?= $form->field($model, 'addon')->hiddenInput()->label(false) ?>
                <div class="form-group">　
                    <div class="col-sm-4 col-sm-offset-2">
                        <button class="btn btn-primary" type="submit">保存内容</button>
                        <span class="btn btn-white" onclick="history.go(-1)">返回</span>
                    </div>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>





