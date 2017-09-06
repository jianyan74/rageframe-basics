<?php
use yii\helpers\Url;
use common\helpers\AddonsUrl;
use jianyan\basics\common\models\sys\Addons;
use jianyan\basics\common\models\sys\AddonsBinding;

$this->title = $model['title'];
$this->params['breadcrumbs'][] = ['label' =>  '扩展模块','url' => ['index']];
$this->params['breadcrumbs'][] = ['label' =>  $model['addon']['title'],'url' => ['binding','addon' => $model['addon']['name']]];
$this->params['breadcrumbs'][] = ['label' =>  $this->title];
?>

<div class="col-sm-2" style="width: 13%; height: 93%;background:#fff;">
    <div class="ibox-content">
        <div class="file-manager">
            <?php if(Yii::$app->params['addon']['info']['setting'] == Addons::SETTING_TRUE || !empty($list[AddonsBinding::ENTRY_COVER])){ ?>
                <h4> 核心功能设置</h4>
                <ul class="folder-list" style="padding: 10px">
                    <?php foreach (Yii::$app->params['addon']['binding'][AddonsBinding::ENTRY_COVER] as $vo){ ?>
                        <li>
                            <a href="<?php echo Url::to(['cover','id' => $vo['id'],'addon'=> Yii::$app->params['addon']['info']['name']])?>" title="<?php echo $vo['title'] ?>">
                                <i class="<?php echo $vo['icon'] ? $vo['icon'] : 'fa fa-external-link-square';?>"></i>
                                <?php echo $vo['title'] ?>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if(Yii::$app->params['addon']['info']['setting'] == Addons::SETTING_TRUE){ ?>
                        <li>
                            <a href="<?php echo AddonsUrl::toRoot(['setting/display','addon'=> Yii::$app->params['addon']['info']['name']])?>" title="参数设置">
                                <i class="fa fa-cog"></i>
                                参数设置
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            <?php } ?>
            <h4> 业务功能菜单</h4>
            <ul class="folder-list" style="padding: 10px">
                <?php foreach (Yii::$app->params['addon']['binding'][AddonsBinding::ENTRY_MENU] as $vo){ ?>
                    <li>
                        <a href="<?php echo AddonsUrl::to([$vo['route'],'addon'=> Yii::$app->params['addon']['info']['name']])?>" title="<?php echo $vo['title'] ?>">
                            <i class="<?php echo $vo['icon'] ? $vo['icon'] : 'fa fa-puzzle-piece';?>"></i>
                            <?php echo $vo['title'] ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
<div class="col-sm-10" style="width: 87%;">
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs-container">
                    <div class="tab-content">
                        <div class="tab-pane active">
                            <div class="panel-body">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label" for="menu-title">微信入口直接链接</label>
                                        <input class="form-control" type="text" value="<?= AddonsUrl::toWechat([$model['route'],'addon'=>$model['addon']['name']]) ?>" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="menu-title">二维码</label><br>
                                            <div class="row" style="padding-left: 15px">
                                                <img src="<?= Url::to(['qr','shortUrl'=> AddonsUrl::toWechat([$model['route'],'addon'=>$model['addon']['name']])])?>" style="border:1px solid #CCC;border-radius:4px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label" for="menu-title">前台入口直接链接</label>
                                        <input class="form-control" type="text" value="<?= AddonsUrl::toFront([$model['route'],'addon'=>$model['addon']['name']]); ?>" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label" for="menu-title">二维码</label><br>
                                            <div class="row" style="padding-left: 15px">
                                                <img src="<?= Url::to(['qr','shortUrl'=> AddonsUrl::toFront([$model['route'],'addon'=>$model['addon']['name']])])?>" style="border:1px solid #CCC;border-radius:4px;">
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
    </div>
</div>


