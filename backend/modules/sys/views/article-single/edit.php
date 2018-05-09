<?php
use yii\widgets\ActiveForm;

$this->title = $model->isNewRecord ? '创建' : '编辑';
$this->params['breadcrumbs'][] = ['label' => '单页管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
        <div class="col-sm-9">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>基本信息</h5>
                </div>
                <div class="ibox-content">
                    <div class="col-sm-12">
                        <?= $form->field($model, 'title')->textInput() ?>
                        <?= $form->field($model, 'name')->textInput() ?>
                        <?= $form->field($model, 'sort')->textInput()?>
                        <?= $form->field($model, 'cover')->widget('backend\widgets\webuploader\Image', [
                            'boxId' => 'cover',
                            'options' => [
                                'multiple'   => false,
                            ]
                        ])?>
                        <?= $form->field($model, 'content')->widget(\crazydb\ueditor\UEditor::className()) ?>
                        <?= $form->field($model, 'description')->textarea()?>
                        <div class="hr-line-dashed"></div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12 text-center">
                            <button class="btn btn-primary" type="submit">保存内容</button>
                            <span class="btn btn-white" onclick="history.go(-1)">返回</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div>
                    <div class="ibox-content">
                        <?= $form->field($model, 'seo_key')->textInput()?>
                        <?= $form->field($model, 'seo_content')->textarea()?>
                        <?= $form->field($model, 'author')->textInput()?>
                        <?= $form->field($model, 'view')->textInput()?>
                        <?= $form->field($model, 'link')->textInput()?>
                        <?= $form->field($model, 'status')->radioList(['1' => '启用','0' => '禁用']) ?>
                    </div>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
