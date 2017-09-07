<?php
use jianyan\basics\common\models\sys\Addons;
use jianyan\basics\common\models\sys\AddonsBinding;
use common\helpers\AddonsUrl;
use yii\helpers\Url;
?>

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