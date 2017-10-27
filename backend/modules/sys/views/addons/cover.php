<?php
use yii\helpers\Url;
use common\helpers\AddonsUrl;
use jianyan\basics\backend\widgets\menu\AddonLeftWidget;

$this->title = $model['name'];
$this->params['breadcrumbs'][] = ['label' =>  '扩展模块','url' => ['index']];
$this->params['breadcrumbs'][] = ['label' =>  $model['title'],'url' => ['binding','addon' => $model['name']]];
$this->params['breadcrumbs'][] = ['label' =>  $this->title];
?>

<div class="col-sm-2" style="width: 15%; height: 100%;background:#fff;">
    <?= AddonLeftWidget::widget(); ?>
</div>
<div class="col-sm-10" style="width: 85%;padding-left: 0;padding-right: 0;">
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs">
                        <?php foreach ($binding['cover'] as $value){ ?>
                            <li class="<?php if($value['id'] == $id ){ echo 'active' ;}?>"><a href="<?= Url::to(['cover','id' => $value['id'],'addon' => $model['name']])?>"> <?= $value['title'] ?></a></li>
                        <?php } ?>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active">
                            <div class="panel-body">
                                <?php foreach ($binding['cover'] as $value){ ?>
                                    <?php if($value['id'] == $id ){?>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="control-label" for="menu-title">微信入口直接链接</label>
                                                <input class="form-control" type="text" value="<?= AddonsUrl::toWechat([$value['route'],'addon' => $model['name']]) ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label" for="menu-title">二维码</label><br>
                                                    <div class="row" style="padding-left: 15px">
                                                        <img src="<?= Url::to(['qr','shortUrl'=> AddonsUrl::toWechat([$value['route'],'addon' => $model['name']])])?>" style="border:1px solid #CCC;border-radius:4px;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="control-label" for="menu-title">前台入口直接链接</label>
                                                <input class="form-control" type="text" value="<?= AddonsUrl::toFront([$value['route'],'addon' => $model['name']]); ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label" for="menu-title">二维码</label><br>
                                                    <div class="row" style="padding-left: 15px">
                                                        <img src="<?= Url::to(['qr','shortUrl'=> AddonsUrl::toFront([$value['route'],'addon' => $model['name']])])?>" style="border:1px solid #CCC;border-radius:4px;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }
                                } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
