<?php
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

?>

<?php $form = ActiveForm::begin([]); ?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
    <h4 class="modal-title">标签</h4>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div>
                <div class="ibox-content">
                    <div class="col-md-12">
                        <?= Html::checkboxList('tag_id', $fansTags, ArrayHelper::map($tags,'id', 'name')); ?>
                        <div class="hr-line-dashed"></div>
                    </div>

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
