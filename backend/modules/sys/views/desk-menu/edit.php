<?php
use yii\widgets\ActiveForm;
use yii\helpers\Url;

?>

<?php $form = ActiveForm::begin([
    'id' => $model->formName(),
    'enableAjaxValidation' => true,
    'validationUrl' => Url::toRoute(['edit','id' => $model['id']]),
]); ?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
    <h4 class="modal-title">上级目录:<?= $parent_title?></h4>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-content-ajax">
                <div class="col-sm-12">
                    <?= $form->field($model, 'title')->textInput() ?>
                    <?= $form->field($model, 'url_type')->radioList(['1'=>'系统路由','2'=>'直接链接']) ?>
                    <?= $form->field($model, 'url')->textInput()->hint("系统路由例如：index/index<br/>直接链接例如：www.baidu.com") ?>
                    <?= $form->field($model, 'menu_css')->textInput()?>
                    <?= $form->field($model, 'sort')->textInput() ?>
                    <?= $form->field($model, 'target')->radioList(['1'=>'是','-1'=>'否']) ?>
                    <?= $form->field($model, 'status')->radioList(['1' => '启用','0' => '禁用']) ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-white" data-dismiss="modal">关闭</button>
    <button class="btn btn-primary" type="submit">保存内容</button>
</div>
<?php ActiveForm::end(); ?>

