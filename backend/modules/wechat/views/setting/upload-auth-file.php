<?php
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = '参数';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="tabs-container">
                <ul class="nav nav-tabs">
                    <li><a href="<?= Url::to(['/wechat/setting/history-stat']); ?>"> 参数</a></li>
                    <li class="active"><a href="<?= Url::to(['/wechat/setting/upload-auth-file']); ?>"> 上传JS接口文件</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active">
                        <div class="panel-body">
                            <?php $form = ActiveForm::begin([
                                'options' => [
                                    'enctype' => 'multipart/form-data'
                                ]
                            ]); ?>
                            <div class="col-sm-12">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <div class="input-group m-b">
                                            <input id="js-file" type="file" name="jsFile" style="display:none">
                                            <input type="text" class="form-control" id="fileName" name="fileName" readonly>
                                            <span class="input-group-btn">
                                            <a class="btn btn-white" onclick="$('#js-file').click();">选择文件</a>
                                        </span>
                                        </div>
                                        <div class="hint-block">设置JS接口安全域名，需要上传的文件。 </div>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="hr-line-dashed"></div>
                                    <div class="form-group">
                                        <div class="col-sm-4 col-sm-offset-2">
                                            <button class="btn btn-primary" type="submit">保存内容</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('input[id=js-file]').change(function() {
        $('#fileName').val($(this).val());
    });
</script>