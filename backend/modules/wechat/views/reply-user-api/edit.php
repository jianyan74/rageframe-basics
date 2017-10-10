<?php
use yii\widgets\ActiveForm;
use jianyan\basics\common\models\wechat\ReplyUserApi;

$this->title = $model->isNewRecord ? '创建' : '编辑';
$this->params['breadcrumbs'][] = ['label' => '自动回复', 'url' => ['rule/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="wrapper wrapper-content animated fadeInRight">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <?= $this->render('/common/rule_edit_base',[
            'form' => $form,
            'rule' => $rule,
            'keyword' => $keyword,
            'ruleKeywords' => $ruleKeywords,
        ])?>
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>回复内容</h5>
                </div>
                <div class="ibox-content">
                    <div class="col-sm-12">
                        <?= $form->field($model, 'api_url')->dropDownList(ReplyUserApi::getList())->hint('1、添加此模块的规则后，只针对于单个规则定义有效，如果需要全部路由给接口处理，则修改该模块的优先级顺序<br>2、本地文件存放在公共文件夹内(/common/userapis)下<br>3、文件名格式为*Api.php，例如：TestApi.php') ?>
                        <?= $form->field($model, 'default')->textInput()->hint('当接口无回复时，则返回用户此处设置的文字信息，优先级高于“默认关键字”') ?>
                        <?= $form->field($model, 'cache_time')->textInput()->hint('接口返回数据将缓存在系统中的时限，默认为0不缓存') ?>
                        <?= $form->field($model, 'description')->textarea()->hint('仅作为后台备注接口的用途') ?>
                        <div class="hr-line-dashed"></div>
                    </div>
                    <div class="form-group">　
                        <div class="col-sm-4 col-sm-offset-2">
                            <button class="btn btn-primary" type="submit">保存内容</button>
                            <span class="btn btn-white" onclick="history.go(-1)">返回</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>

