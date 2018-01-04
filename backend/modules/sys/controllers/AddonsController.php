<?php
namespace jianyan\basics\backend\modules\sys\controllers;

use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use Overtrue\Pinyin\Pinyin;
use jianyan\basics\common\models\sys\Addons;
use jianyan\basics\common\models\sys\AddonsBinding;
use common\enums\StatusEnum;
use common\helpers\AddonsHelp;
use common\helpers\StringHelper;
use common\helpers\FileHelper;
use backend\controllers\MController;

/**
 * 插件控制器
 *
 * Class AddonsController
 * @package jianyan\basics\backend\modules\sys\controllers
 */
class AddonsController extends MController
{
    /**
     * @return array
     */
    public function actions()
    {
        return [
            'upload' => Yii::$app->params['ueditorConfig']
        ];
    }

    /**
     * 已安装模块列表
     *
     * @return mixed|string
     */
    public function actionUninstall()
    {
        $request  = Yii::$app->request;

        if($request->isPost)
        {
            $addonName = $request->get('name');
            // 删除数据库
            if($model = Addons::find()->where(['name' => $addonName])->one())
            {
                $model->delete();
            }

            // 验证模块信息
            $class = AddonsHelp::getAddonsClass($addonName);

            if(!class_exists($class))
            {
                return $this->message('卸载成功',$this->redirect(['uninstall']));
            }

            // 卸载
            $addons = new $class;
            if(StringHelper::strExists($addons->uninstall,'.php'))
            {
                if($addons->uninstall && file_exists(AddonsHelp::getAddons($addonName) . $addons->uninstall))
                {
                    include_once AddonsHelp::getAddons($addonName) . $addons->uninstall;
                }
            }

            return $this->message('卸载成功',$this->redirect(['uninstall']));
        }

        return $this->render('uninstall',[
            'list' => Addons::find()->orderBy('append asc')->all(),
        ]);
    }

    /**
     * 安装插件
     */
    public function actionInstall()
    {
        $request  = Yii::$app->request;

        $model = new Addons();
        if($request->isPost)
        {
            // 开启事物
            $transaction = Yii::$app->db->beginTransaction();
            try
            {
                $addonName = $request->get('name');
                $class = AddonsHelp::getAddonsClass($addonName);

                if(!class_exists($class))
                {
                    throw new \Exception('实例化失败,插件不存在或检查插件名称');
                }

                $addons = new $class;
                // 安装
                if(StringHelper::strExists($addons->install,'.php'))
                {
                    if($addons->install && file_exists(AddonsHelp::getAddons($addonName) . $addons->install))
                    {
                        include_once AddonsHelp::getAddons($addonName).$addons->install;
                    }
                }

                // 添加入口
                isset($addons->bindings) && AddonsBinding::add($addons->bindings,$addonName);
                $model->attributes = $addons->info;
                $model->type = $addons->type ? $addons->type : 'other';
                $model->setting = $addons->setting ? StatusEnum::ENABLED : StatusEnum::DISABLED;
                $model->hook = $addons->hook ? StatusEnum::ENABLED : StatusEnum::DISABLED;
                $model->is_rule = $addons->is_rule ? StatusEnum::ENABLED : StatusEnum::DISABLED;
                $model->wxapp_support = $addons->wxappSupport ? StatusEnum::ENABLED : StatusEnum::DISABLED;
                $model->wechat_message = isset($addons->wechatMessage) ? serialize($addons->wechatMessage) : '' ;

                // 首先字母转大写拼音
                if($chinese = StringHelper::strToChineseCharacters($model->title))
                {
                    $title_initial = mb_substr($chinese[0][0],0,1,'utf-8');
                    $pinyin = new Pinyin();
                    $model->title_initial = ucwords($pinyin->abbr($title_initial));
                }

                if($model->save())
                {
                    $transaction->commit();
                    return $this->message('安装成功',$this->redirect(['uninstall']));
                }
                else
                {
                    $error = $this->analysisError($model->getFirstErrors());
                    throw new \Exception($error);
                }
            }
            catch (\Exception $e)
            {
                $transaction->rollBack();
                return $this->message($e->getMessage(),$this->redirect(['install']),'error');
            }
        }

        return $this->render('install',[
            'list' => $model->getList()
        ]);
    }


    /**
     * 更新数据库文件
     *
     * @return mixed
     */
    public function actionUpgrade()
    {
        $request  = Yii::$app->request;
        $addonName = $request->get('name');
        $class = AddonsHelp::getAddonsClass($addonName);

        if(!class_exists($class))
        {
            return $this->message('实例化失败,插件不存在或检查插件名称',$this->redirect(['uninstall']),'error');
        }

        $addons = new $class;
        // 更新
        if(StringHelper::strExists($addons->upgrade,'.php'))
        {
            if($addons->upgrade && file_exists(AddonsHelp::getAddons($addonName) . $addons->upgrade))
            {
                include_once AddonsHelp::getAddons($addonName).$addons->upgrade;
            }
        }

        return $this->message('更新数据成功',$this->redirect(['uninstall']));
    }

    /**
     * 只更新配置文件
     *
     * @return mixed
     */
    public function actionUpdateConfig()
    {
        $request  = Yii::$app->request;
        // 开启事物
        $transaction = Yii::$app->db->beginTransaction();
        try
        {
            $addonName = $request->get('name');
            $class = AddonsHelp::getAddonsClass($addonName);

            // 判断类是否存在
            if(!class_exists($class))
            {
                throw new \Exception('实例化失败,插件不存在或检查插件名称');
            }

            $addons = new $class;

            if(!($model = Addons::getAddon($addonName)))
            {
                throw new \Exception('插件不存在');
            }

            // 删除原先的数据
            AddonsBinding::deleted($addonName);
            // 添加入口
            isset($addons->bindings) && AddonsBinding::add($addons->bindings,$addonName);
            $model->attributes = $addons->info;
            $model->type = $addons->type ? $addons->type : 'other';
            $model->setting = $addons->setting ? StatusEnum::ENABLED : StatusEnum::DISABLED;
            $model->hook = $addons->hook ? StatusEnum::ENABLED : StatusEnum::DISABLED;
            $model->wxapp_support = $addons->wxappSupport ? StatusEnum::ENABLED : StatusEnum::DISABLED;
            $model->wechat_message = isset($addons->wechatMessage) ? serialize($addons->wechatMessage) : '' ;

            // 首先字母转大写拼音
            if($chinese = StringHelper::strToChineseCharacters($model->title))
            {
                $title_initial = mb_substr($chinese[0][0],0,1,'utf-8');
                $pinyin = new Pinyin();
                $model->title_initial = ucwords($pinyin->abbr($title_initial));
            }

            if($model->save())
            {
                $transaction->commit();
                return $this->message('更新配置成功',$this->redirect(['uninstall']));
            }
            else
            {
                $error = $this->analysisError($model->getFirstErrors());
                throw new \Exception($error);
            }
        }
        catch (\Exception $e)
        {
            $transaction->rollBack();
            return $this->message($e->getMessage(),$this->redirect(['uninstall']),'error');
        }
    }

    /**
     * 创建
     *
     * @return mixed|string
     */
    public function actionCreate()
    {
        $request  = Yii::$app->request;

        $model = new Addons();
        $model->install = 'install.php';
        $model->uninstall = 'uninstall.php';
        $model->upgrade = 'upgrade.php';
        if($model->load($request->post()))
        {
            // 全部post
            $allPost = $request->post();

            if(!is_writable(Yii::getAlias('@addons')))
            {
                return $this->message('您没有创建目录写入权限，无法使用此功能',$this->redirect(['create']),'error');
            }

            $model->name = trim($model->name);
            $addons_dir = Yii::getAlias('@addons');
            // 创建目录结构
            $files = [];
            $addon_dir = "$addons_dir/{$model->name}/";
            $addon_name = "{$model->name}Addon.php";
            $files[] = $addon_dir;
            $files[] = "{$addon_dir}{$addon_name}";

            /**
             * 微信消息
             */
            $wechat_message = '[';
            if($model->wechat_message)
            {
                $files[] = "{$addon_dir}WechatMessage.php";

                foreach ($model->wechat_message as $key => $value)
                {
                    $key >= 1 && $wechat_message .= ',';
                    $wechat_message .= "'{$value}'";
                }
            }
            $wechat_message .= ']';

            // 后台初始化视图
            $admin_view_name = StringHelper::toUnderScore($model->name);

            $files[] = "{$addon_dir}common/";
            $files[] = "{$addon_dir}common/models/";
            $files[] = "{$addon_dir}common/models/{$model->name}.php";
            $files[] = "{$addon_dir}admin/";
            $files[] = "{$addon_dir}admin/controllers/";
            $files[] = "{$addon_dir}admin/controllers/{$model->name}Controller.php";
            $files[] = "{$addon_dir}admin/views/";
            $files[] = "{$addon_dir}admin/views/{$admin_view_name}/";
            $files[] = "{$addon_dir}admin/views/{$admin_view_name}/index.php";
            $files[] = "{$addon_dir}home/";
            $files[] = "{$addon_dir}home/controllers/";
            $files[] = "{$addon_dir}home/controllers/{$model->name}Controller.php";
            $files[] = "{$addon_dir}home/views/";

            // 小程序支持
            if($model->wxapp_support)
            {
                $files[] = "{$addon_dir}api/";
                $files[] = "{$addon_dir}api/controllers/";
                $files[] = "{$addon_dir}api/controllers/PagesController.php";
            }

            // 参数设置支持
            if($model->setting == true)
            {
                $files[] = "{$addon_dir}Setting.php";
                $files[] = "{$addon_dir}common/models/SettingForm.php";
                $files[] = "{$addon_dir}admin/views/setting/";
                $files[] = "{$addon_dir}admin/views/setting/index.php";
            }

            $model['install'] && $files[] = "{$addon_dir}{$model['install']}";
            $model['uninstall'] && $files[] = "{$addon_dir}{$model['uninstall']}";
            $model['upgrade'] && $files[] = "{$addon_dir}{$model['upgrade']}";
            FileHelper::createDirOrFiles($files);

            /*********************************是否嵌入规则*********************************/
            $is_rule = $model->is_rule ? 'true' : 'false';

            /*********************************小程序*********************************/

            $wxapp_support = $model->wxapp_support ? 'true' : 'false';
            $wxapp_support_str = "";
            if($model->wxapp_support)
            {
                $wxapp_support_str = "<?php
namespace addons\\{$model->name}\\api\\controllers;

use Yii;
use common\\components\\WxApp;

/**
 * 小程序初始化
 * 
 * Class PagesController
 * @package addons\\api\\controllers\\PagesController
 */
class PagesController extends WxApp
{

}
            
            ";
            }

            /*********************************钩子*********************************/

            $hook = 'false';
            $hookStr = "";
            if($model->hook)
            {
                $hook = 'true';
                $hookStr = "
    /**
     * 钩子
     * 
     * @param string \$addon 模块名字
     * @param null \$config 参数
     * @return string
     */
    public function actionHook(\$addon, \$config = null)
    {
        return \$this->rederHook(\$addon,[
        ]);
    }";
            }

            // 参数
            $setting = 'false';
            $settingStr = "";
            if($model->setting)
            {
                $setting = 'true';
                $settingStr = "
    /**
     * 配置默认首页
     * 
     * @return string
     */
    public function actionDisplay()
    {
        \$request  = Yii::\$app->request;
        \$model = new SettingForm();
        if(\$request->isPost)
        {
            \$data = \$request->post();
            \$config = \$data['SettingForm'];
            \$this->setConfig(\$config);
        }

        \$model->attributes = \$this->getConfig();
        return \$this->renderAddon('index',[
            'model' => \$model
        ]);
    }
                ";
            }

            /*********************************必要配置文件*********************************/

            // 导航
            $bindings = AddonsHelp::bindingsToString($allPost['bindings'],'cover');
            !empty($bindings) && $bindings .=",
            ";
            $bindings .= AddonsHelp::bindingsToString($allPost['bindings'],'menu');

            // 配置信息
            $Addon = "<?php 
namespace addons\\{$model->name};

class {$model->name}Addon
{
    /**
     * 参数配置 
     * 
     * [true,false] 开启|关闭
     * 使用方法在当前文件下的Setting.php
     * @var bool
     */
    public \$setting = {$setting};
    
    /**
     * 钩子
     * 
     * [true,false] 开启|关闭
     * 使用方法在当前文件下的Setting.php
     * @var bool
     */
    public \$hook = {$hook};
    
     /**
     * 小程序
     * 
     * [true,false] 开启|关闭
     * @var bool
     */
    public \$wxappSupport = {$wxapp_support};
    
    /**
     * 类别
     * 
     * @var string 
     * [
     *      'plug'      => \"功能插件\",  
     *      'business'  => \"主要业务\",
     *      'customer'  => \"客户关系\",
     *      'activity'  => \"营销及活动\",
     *      'services'  => \"常用服务及工具\",
     *      'biz'       => \"行业解决方案\",
     *      'h5game'    => \"H5游戏\",
     *      'other'     => \"其他\",
     * ]
     */
    public \$type = '{$model->type}';
    
     /**
     * 微信接收消息类别
     * 
     * @var array 
     */
    public \$wechatMessage = {$wechat_message};
    
     /**
     * 是否嵌入规则
     * 
     * 处理微信文字消息
     * [true,false] 开启|关闭
     * @var bool
     */
    public \$is_rule = {$is_rule};
    
    /**
     * 配置信息
     * 
     * @var array
     */
    public \$info = [
        'name' => '{$model->name}',
        'title' => '{$model->title}',
        'brief_introduction' => '{$model->brief_introduction}',
        'description' => '{$model->description}',
        'author' => '{$model->author}',
        'version' => '{$model->version}',
    ];
    
    /**
     * 后台菜单
     * 
     * 例如
     * public \$bindings = [
     *      'cover' => [
     *      ]，
     *     'menu' => [
     *         [
     *              'title' => '碎片列表',
     *              'route' => 'Debris/index',
     *              'icon' => 'fa fa-weixin',
     *          ]
     *       ...
     *     ],
     * ];
     * @var array
     */
    public \$bindings = [
            {$bindings}
    ];
    
    /**
     * 保存在当前模块的根目录下面
     * 
     * 例如 public \$install = 'install.php';
     * 安装SQL,只支持php文件
     * @var string
     */
    public \$install = '{$model['install']}';
    
    /**
     * 卸载SQL
     * 
     * @var string
     */
    public \$uninstall = '{$model['uninstall']}';
    
    /**
     * 更新SQL
     * 
     * @var string
     */
    public \$upgrade = '{$model['upgrade']}';
}
            ";

            /*********************************后台控制器*********************************/

            $AdminController = "<?php
namespace addons\\{$model->name}\\admin\\controllers;

use yii;
use yii\data\Pagination;
use common\\components\\Addons;
use addons\\{$model->name}\\common\\models\\{$model->name};

/**
 * {$model->title}控制器
 * 
 * Class {$model->name}Controller
 * @package addons\\{$model->name}\\admin\\controllers
 */
class {$model->name}Controller extends Addons
{
     /**
     * 首页
     * 
     * @return string
     */
    public function actionIndex()
    {
        // \$data = {$model->name}::find();
        // \$pages = new Pagination(['totalCount' => \$data->count(), 'pageSize' => \$this->_pageSize]);
        // \$models = \$data->offset(\$pages->offset)
        //     ->limit(\$pages->limit)
        //     ->all();

        \$pages = '';
        \$models = '';
        
        return \$this->renderAddon('index',[
            'models' => \$models,
            'pages' => \$pages,
        ]);
    }

    /**
     * 编辑
     * 
     * @return string|yii\web\Response
     */
    public function actionEdit()
    {
        \$request  = Yii::\$app->request;
        \$id = \$request->get('id');
        \$model = \$this->findModel(\$id);

        if (\$model->load(Yii::\$app->request->post()) && \$model->save())
        {
            return \$this->redirectAddon(['index']);
        }

        return \$this->renderAddon('edit',[
            'model' => \$model
        ]);
    }

    /**
     * 删除
     */
    public function actionDelete()
    {
        \$id =  Yii::\$app->request->get('id');
        if(\$this->findModel(\$id)->delete())
        {
            \$this->message(\"删除成功\",\$this->redirectAddon(['index']));
        }
        else
        {
            \$this->message(\"删除失败\",\$this->redirectAddon(['index']),'error');
        }
    }
    
    /**
     * 返回模型
     * 
     * @param \$id
     * @return \$this|{$model->name}|static
     */
    protected function findModel(\$id)
    {
        if (empty(\$id))
        {
            \$model = new {$model->name};
            return \$model->loadDefaultValues();
        }

        if (empty((\$model = {$model->name}::findOne(\$id))))
        {
            return new {$model->name};
        }

        return \$model;
    }
}
            ";

            /*********************************后台模型*********************************/

            $CommonModel = "<?php
namespace addons\\{$model->name}\\common\\models;

use Yii;
use yii\\db\\ActiveRecord;

class {$model->name} extends ActiveRecord
{

}
            ";

            /*********************************前台控制器*********************************/

            $HomeController = "<?php
namespace addons\\{$model->name}\\home\\controllers;

use yii;
use common\\components\\Addons;
use addons\\{$model->name}\\common\\models\\{$model->name};

/**
 * {$model->title}控制器
  * 
 * Class {$model->name}Controller
 * @package addons\\{$model->name}\\home\\controllers
 */
class {$model->name}Controller extends Addons
{
    /**
    * 首页
    */
    public function actionIndex()
    {
        return \$this->renderAddon('index',[
        ]);
    }
}
            ";

            /*********************************配置信息*********************************/

            $Setting = "<?php
namespace addons\\{$model->name};

use yii;
use common\\components\\Addons;
use addons\\{$model->name}\\common\\models\\SettingForm;

/**
 * 全局配置
 * 
 * Class Setting
 * @package addons\\{$model->name}
 */
class Setting extends Addons
{
    {$settingStr}
    {$hookStr}
}
            ";

            $WechatInfo = "<?php
namespace addons\\{$model->name};

use Yii;
use common\\components\\Addons;

/**
 * 微信消息类
 * 
 * Class WechatMessage
 * @package addons\\{$model->name}
 */
class WechatMessage implements \jianyan\basics\common\interfaces\WxMsgInterface
{
    /**
    * object \$message 微信用户传递的消息 
    */
    public function run(\$message)
    {
    	// 这里定义此模块进行消息处理时的具体过程, 请查看RageFrame文档来编写你的代码
    }
}
            ";

            /*********************************配置模型*********************************/
            $SettingForm = "<?php
namespace addons\\{$model->name}\common\models;

use Yii;

class SettingForm extends \yii\base\Model
{
    public \$site_title;
    public \$share_title;
    public \$share_desc;
    public \$share_pic;
    public \$share_url;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['site_title'], 'required'],
            [['share_title','share_desc','share_pic','share_url'], 'string'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'site_title' => '网站标题',
            'share_title' => '分享标题',
            'share_desc' => '分享描述',
            'share_pic' => '分享图片',
            'share_url' => '分享链接',
        ];
    }
}
            ";

            /*********************************配置页面*********************************/
            $SettingIndex = "<?php
use yii\widgets\ActiveForm;

\$this->title = '参数设置';
\$this->params['breadcrumbs'][] = ['label' => \$this->title];
?>
            ";

            $SettingIndex .=  <<<HTML
            
<div class="wrapper wrapper-content animated fadeInRight">
    <?php \$form = ActiveForm::begin([
        'options' => [
            'enctype' => 'multipart/form-data'
        ]
    ]); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>基本设置</h5>
                </div>
                <div class="ibox-content">
                    <div class="col-sm-12">
                        <?= \$form->field(\$model, 'site_title')->textInput() ?>
                    </div>　
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>分享设置</h5>
                </div>
                <div class="ibox-content">
                    <div class="col-sm-12">
                        <?= \$form->field(\$model, 'share_title')->textInput() ?>
                        <?= \$form->field(\$model, 'share_desc')->textarea() ?>
                        <?= \$form->field(\$model, 'share_pic')->widget('backend\widgets\webuploader\Image', [
                            'boxId' => 'share_pic',
                            'options' => [
                                'multiple'   => false,
                            ]
                        ])?>
                        <?= \$form->field(\$model, 'share_url')->textInput() ?>
                        <div class="hr-line-dashed"></div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12 col-sm-offset-2">
                            <button class="btn btn-primary" type="submit">保存内容</button>
                        </div>
                    </div>　
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
HTML;

            // 写入模块配置
            file_put_contents("{$addon_dir}{$model->name}Addon.php", $Addon);
            // 写入后台控制器
            file_put_contents("{$addon_dir}admin/controllers/{$model->name}Controller.php", $AdminController);
            file_put_contents("{$addon_dir}admin/views/{$admin_view_name}/index.php", '初始化页面...');
            // 写入前台控制器
            file_put_contents("{$addon_dir}home/controllers/{$model->name}Controller.php", $HomeController);
            // 写入模型
            file_put_contents("{$addon_dir}common/models/{$model->name}.php", $CommonModel);
            // 写入参数
            if($model->setting == true)
            {
                file_put_contents($addon_dir.'Setting.php', $Setting);
                file_put_contents($addon_dir.'common/models/SettingForm.php', $SettingForm);
                file_put_contents($addon_dir.'admin/views/setting/index.php', $SettingIndex);
            }

            // 写入文件
            $model['install'] && file_put_contents("{$addon_dir}/{$model['install']}", '<?php');
            $model['uninstall'] && file_put_contents("{$addon_dir}/{$model['uninstall']}", '<?php');
            $model['upgrade'] && file_put_contents("{$addon_dir}/{$model['upgrade']}", '<?php');
            $model->wechat_message && file_put_contents($addon_dir.'WechatMessage.php', $WechatInfo);
            $model->wxapp_support && file_put_contents("{$addon_dir}api/controllers/PagesController.php", $wxapp_support_str);

            // 移动图标
            if($model->cover)
            {
                copy(Yii::getAlias('@rootPath').'\web'.$model->cover,$addon_dir.'icon.jpg'); // 拷贝到新目录
            }

            return $this->message('生成模块成功',$this->redirect(['install']));
        }

        return $this->render('create',[
            'model' => $model,
            'addonsType' => Yii::$app->params['addonsType']
        ]);
    }

    /**
     * 插件首页
     *
     * @return array|string
     */
    public function actionIndex()
    {
        $request = Yii::$app->request;
        if($request->isAjax)
        {
            $keyword = $request->get('keyword');
            $type = $request->get('type');

            $data = Addons::find()
                ->where(['status' => StatusEnum::ENABLED])
                ->andFilterWhere(['like','title',$keyword])
                ->andFilterWhere(['type' => $type]);

            $type != 'plug' && $list = $data->andFilterWhere(['<>','type','plug']);
            $list = $data->asArray()
                ->all();

            foreach ($list as &$vo)
            {
                if(file_exists(AddonsHelp::getAddons($vo['name']) . 'icon.jpg'))
                {
                    $vo['cover'] = "/addons/{$vo['name']}/icon.jpg";
                }
                else
                {
                    $vo['cover'] = "/resource/backend/img/icon.jpg";
                }

                $vo['link'] = Url::to(['binding','addons' => $vo['name']]);
                $vo['updateConfig'] = Url::to(['update-config','name' => $vo['name']]);
                $vo['upgrade'] = Url::to(['upgrade','name' => $vo['name']]);
                $vo['uninstall'] = Url::to(['uninstall','name' => $vo['name']]);
            }

            $result = $this->setResult();
            $result->code = 200;
            $result->message = '获取成功';
            $result->data = $list;

            return $this->getResult();
        }
        else
        {
            $models = Addons::find()
                ->where(['status' => StatusEnum::ENABLED])
                ->andWhere(['<>','type','plug'])
                ->asArray()
                ->all();

            $addonsType = Yii::$app->params['addonsType']['addon']['child'];

            return $this->render('index',[
                'list' => Addons::regroupType($models),
                'models' => $models,
                'addonsType' => $addonsType,
            ]);
        }
    }

    /**
     * 插件后台导航页面
     *
     * @return bool|string
     */
    public function actionBinding()
    {
        $request  = Yii::$app->request;
        $addonName = $request->get('addon');

        if(!($model = Addons::getAddon($addonName)))
        {
            return $this->message('插件不存在',$this->redirect(['index']),'error');
        }

        $list = AddonsBinding::getList($addonName);
        if(!$list)
        {
            return $this->message('插件菜单未配置',$this->redirect(['index']),'error');
        }

        // 优先跳转到业务功能菜单
        if(isset($list['menu'][0]))
        {
            return $this->redirect(['execute','route' => $list['menu'][0]['route'],'addon' => $addonName]);
        }

        /** 插件信息加入公共配置 **/
        Yii::$app->params['addon']['info'] = ArrayHelper::toArray($model);
        Yii::$app->params['addon']['binding'] = $list;

        return $this->render('binding',[

        ]);
    }

    /**
     * 导航链接
     *
     * @return bool|string
     */
    public function actionCover()
    {
        $request  = Yii::$app->request;
        $id = $request->get('id','');
        $addon = $request->get('addon');

        if(!($model = Addons::getAddon($addon)))
        {
            throw new NotFoundHttpException('插件不存在');
        }

        /**插件信息加入公共配置**/
        Yii::$app->params['addon']['info'] = $model;
        Yii::$app->params['addon']['binding'] = AddonsBinding::getList($model['name']);

        if(!$id && Yii::$app->params['addon']['binding']['cover'])
        {
            $id = Yii::$app->params['addon']['binding']['cover'][0]['id'];
        }

        return $this->render('cover',[
            'id' => $id,
            'model' => $model,
            'binding' => Yii::$app->params['addon']['binding'],
        ]);
    }

    /**
     * 转换二维码
     */
    public function actionQr()
    {
        $getUrl = Yii::$app->request->get('shortUrl');

        $qr = Yii::$app->get('qr');
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', $qr->getContentType());

        return $qr->setText($getUrl)
            ->setSize(150)
            ->setMargin(7)
            ->writeString();
    }

    /**
     * 后台插件页面实现
     *
     * @param string $route 路由
     * @param string $addon 模块名称
     * @return bool
     */
    public function actionExecute($route, $addon)
    {
        return $this->skip(AddonsHelp::analysisBusinessRoute($route, $addon));
    }

    /**
     * 后台插件设置实现
     *
     * @param string $route 路由
     * @param string $addon 模块名称
     * @return bool
     */
    public function actionCentre($route, $addon)
    {
        return $this->skip(AddonsHelp::analysisBaseRoute($route, $addon));
    }

    /**
     * 渲染页面
     *
     * @param $through
     * @return bool
     * @throws \yii\web\UnauthorizedHttpException
     */
    protected function skip($through)
    {
        $class = $through['class'];
        $actionName = $through['actionName'];

        if(!($model = Addons::getAddon($through['addon'])))
        {
            throw new NotFoundHttpException('插件不存在');
        }

        if(!class_exists($class))
        {
            throw new NotFoundHttpException($class . '未找到');
        }

        $list = new $class($through['controller'],Yii::$app->module);
        if(!method_exists($list,$actionName))
        {
            throw new NotFoundHttpException($through['controllerName'] . '/' . $actionName . '方法未找到');
        }

        /** 插件信息加入公共配置 **/
        Yii::$app->params['addon']['info'] = ArrayHelper::toArray($model);
        Yii::$app->params['addon']['binding'] = AddonsBinding::getList($through['addon']);

        return $list->$actionName();
    }

    /**
     * 返回模型
     *
     * @param $id
     * @return Addons|null|static
     */
    protected function findModel($id)
    {
        if ($model = Addons::findOne($id))
        {
            return $model;
        }

        return new Addons();
    }
}