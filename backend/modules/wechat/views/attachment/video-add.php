<?php
use yii\widgets\ActiveForm;

?>

<?php $form = ActiveForm::begin([]); ?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
        <h4 class="modal-title">添加视频</h4>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-content-ajax">
                    <div class="col-sm-12">
                        <?= $form->field($model, 'file_name')->textInput()->label('标题'); ?>
                        <?= $form->field($model, 'description')->textarea()->label('描述'); ?>
                        <?= $form->field($model, 'attachment')->widget('backend\widgets\webuploader\File', [
                            'boxId' => 'attachment',
                            'options' => [
                                'mimeTypes'  => 'video/*',
                                'multiple'   => false,
                            ],
                            'pluginOptions' => [
                                'uploadMaxSize' => 1024 * 1024 * 10,
                            ]
                        ])->label('视频')->hint('永久视频只支持rm/rmvb/wmv/avi/mpg/mpeg/mp4格式,大小不超过为20M.<br>注 :临时视频只支持mp4格式,大小不超过为10M') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-white" data-dismiss="modal">关闭</button>
        <button class="btn btn-primary" type="submit">保存</button>
    </div>
<?php ActiveForm::end(); ?>