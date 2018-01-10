<?php
use yii\helpers\Url;
use yii\widgets\LinkPager;
use yii\widgets\ActiveForm;
use jianyan\basics\backend\widgets\menu\AddonLeftWidget;
use jianyan\basics\common\models\wechat\RuleKeyword;
use common\enums\StatusEnum;

$this->title = '规则管理';
$this->params['breadcrumbs'][] = ['label' =>  '扩展模块','url' => ['/sys/addons/index']];
$this->params['breadcrumbs'][] = ['label' =>  $addonModel['title'],'url' => ['/sys/addons/binding','addon' => $addonModel['name']]];
$this->params['breadcrumbs'][] = ['label' =>  $this->title];
?>

<div class="col-sm-2" style="width: 15%; height: 100%;background:#fff;">
    <?= AddonLeftWidget::widget(); ?>
</div>
<div class="col-sm-10" style="width: 85%;padding-left: 0;padding-right: 0;">
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="tabs-container">
            <div class="tab-content">
                <div class="tab-pane active">
                    <div class="panel-body">
                        <div class="ibox float-e-margins">
                            <div class="row">
                                <div class="col-sm-3">
                                    <form action="" method="get" class="form-horizontal" role="form" id="form">
                                        <div class="input-group m-b">
                                            <input type="hidden" name="addon" value="<?= Yii::$app->params['addon']['info']['name']; ?>">
                                            <input type="text" class="form-control" name="keyword" value="" placeholder="<?= $keyword ? $keyword : '请输入规则名称'?>"/>
                                            <span class="input-group-btn">
                                                 <button class="btn btn-white"><i class="fa fa-search"></i> 搜索</button>
                                            </span>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-sm-9">
                                    <div class="ibox-tools">
                                        <div class="input-group m-b">
                                            <div class="input-group-btn">
                                                <a href="<?php echo Url::to(['edit','addon' => Yii::$app->params['addon']['info']['name']])?>" class="btn btn-primary btn-xs" type="button"><i class="fa fa-plus"></i> 添加回复</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="ibox-content">
                                <?php foreach($models as $model){ ?>
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <span class="collapsed"><?= $model->name ?></span>
                                            <span class="pull-right" id="<?= $model->id ?>">
                                                    <span class="label label-info">优先级：<?= $model->displayorder; ?></span>
                                                <?php if(RuleKeyword::verifyTake($model->ruleKeyword)){ ?>
                                                    <?php if($model->status == StatusEnum::ENABLED){ ?>
                                                        <span class="label label-info">直接接管</span>
                                                    <?php } ?>
                                                <?php } ?>
                                                <?php if($model->status == StatusEnum::DELETE){ ?>
                                                    <span class="label label-danger" onclick="statusRule(this)">已禁用</span>
                                                <?php }else{ ?>
                                                    <span class="label label-info" onclick="statusRule(this)">已启用</span>
                                                <?php } ?>
                                                </span>
                                        </div>
                                        <div id="collapseOne" class="panel-collapse collapse in" aria-expanded="true" style="">
                                            <div class="panel-body">
                                                <div class="col-lg-9 tooltip-demo">
                                                    <?php if($model->ruleKeyword){ ?>
                                                        <?php foreach($model->ruleKeyword as $rule){
                                                            if($rule->type != RuleKeyword::TYPE_TAKE){ ?>
                                                                <span class="simple_tag" data-toggle="tooltip" data-placement="bottom" title="<?= RuleKeyword::$typeExplain[$rule->type]; ?>"><?= $rule->content?></span>
                                                            <?php }
                                                        }
                                                    } ?>
                                                </div>
                                                <div class="col-lg-3">
                                                    <div class="btn-group pull-right">
                                                        <a class="btn btn-white btn-sm" href="<?= Url::to(['edit','id'=>$model->id,'addon' => Yii::$app->params['addon']['info']['name']])?>"><i class="fa fa-edit"></i> 编辑</a>
                                                        <a class="btn btn-white btn-sm" href="<?= Url::to(['delete','id'=>$model->id,'addon' => Yii::$app->params['addon']['info']['name']])?>" onclick="rfDelete(this);return false;"><i class="fa fa-times"></i> 删除</a>
                                                        <!--                                                            <a class="btn btn-white btn-sm" href="#"><i class="fa fa-bar-chart-o"></i> 使用率走势</a>-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <?= LinkPager::widget([
                                            'pagination' => $pages,
                                            'maxButtonCount' => 5,
                                            'firstPageLabel' => "首页",
                                            'lastPageLabel' => "尾页",
                                            'nextPageLabel' => "下一页",
                                            'prevPageLabel' => "上一页",
                                        ]);?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // status => 1:启用;-1禁用;
    function statusRule(obj){

        var id = $(obj).parent().attr('id');
        var self = $(obj);
        var status = self.hasClass("label-danger") ? 1 : -1;

        $.ajax({
            type:"get",
            url:"<?= Url::to(['ajax-update'])?>",
            dataType: "json",
            data: {id:id,status:status},
            success: function(data){
                if(data.code == 200) {
                    if(self.hasClass("label-danger")){
                        self.removeClass("label-danger").addClass("label-info");
                        self.text('已启用');
                    } else {
                        self.removeClass("label-info").addClass("label-danger");
                        self.text('已禁用');
                    }
                }else{
                    rfWarning(data.message);
                }
            }
        });
    }
</script>