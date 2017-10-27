<?php
use yii\widgets\ActiveForm;

?>

<?php $form = ActiveForm::begin([]); ?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
        <h4 class="modal-title">添加语音</h4>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-content">
                    <div class="col-sm-12">
                        <?= $form->field($model, 'attachment')->widget('backend\widgets\webuploader\File', [
                            'boxId' => 'attachment',
                            'options' => [
                                'mimeTypes'  => 'audio/*',
                                'multiple'   => false,
                            ],
                            'pluginOptions' => [
                                'uploadMaxSize' => 1024 * 1024 * 2,
                            ]
                        ])->label('语音')->hint('临时语音只支持amr/mp3格式,大小不超过为2M<br>永久语音只支持mp3/wma/wav/amr格式,大小不超过为5M,长度不超过60秒') ?>
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