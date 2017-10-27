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
                    <li class="active"><a href="<?= Url::to(['/wechat/setting/history-stat']); ?>"> 参数</a></li>
                    <li><a href="<?= Url::to(['/wechat/setting/upload-auth-file']); ?>"> 上传JS接口文件</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active">
                        <div class="panel-body">
                            <?php $form = ActiveForm::begin(); ?>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label class="control-label"><?= $list['is_msg_history']['title'] ?></label>
                                    <div>
                                        <label><input name="setting[is_msg_history][status]" value="1" type="radio" <?= $list['is_msg_history']['status'] == 1 ? 'checked' : '' ?> > 开启</label>
                                        <label><input name="setting[is_msg_history][status]" value="-1" type="radio" <?= $list['is_msg_history']['status'] == 1 ? '' : 'checked' ?>> 关闭</label>
                                    </div>
                                    <div class="hint-block">开启此项后，系统将记录用户与系统的往来消息记录。</div>
                                    <div class="help-block"></div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label" for="rule-name"><?= $list['msg_history_date']['title'] ?></label>
                                    <input class="form-control" name="setting[msg_history_date][value]" value="<?= $list['msg_history_date']['value'] ?>" type="text" />
                                    <div class="hint-block">设置保留历史消息记录的天数，为0则为保留全部，需要开启定时任务。</div>
                                    <div class="help-block"></div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label"><?= $list['is_utilization_stat']['title'] ?></label>
                                    <div>
                                        <label><input name="setting[is_utilization_stat][status]" value="1" type="radio" <?= $list['is_utilization_stat']['status'] == 1 ? 'checked' : '' ?> > 开启</label>
                                        <label><input name="setting[is_utilization_stat][status]" value="-1" type="radio" <?= $list['is_utilization_stat']['status'] == 1 ? '' : 'checked' ?> > 关闭</label>
                                    </div>
                                    <div class="hint-block">开启此项后，系统将记录系统中的规则的使用情况，并生成走势图。</div>
                                    <div class="help-block"></div>
                                </div>
                                <div class="hr-line-dashed"></div>
                                <div class="form-group">
                                    <div class="col-sm-4 col-sm-offset-2">
                                        <button class="btn btn-primary" type="submit">保存内容</button>
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