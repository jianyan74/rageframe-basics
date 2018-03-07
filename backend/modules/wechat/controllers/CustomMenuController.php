<?php
namespace jianyan\basics\backend\modules\wechat\controllers;

use yii;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;
use common\helpers\ResultDataHelper;
use jianyan\basics\common\models\wechat\CustomMenu;
use jianyan\basics\common\models\wechat\FansTags;

/**
 * 自定义菜单
 *
 * Class CustomMenuController
 * @package jianyan\basics\backend\modules\wechat\controllers
 */
class CustomMenuController extends WController
{
    /**
     * 菜单类型
     * 注: 有value属性的在提交菜单是该类型的值必须设置为此值, 没有的则不限制
     * @var array
     */
    public $menuTypes = [
        'click' => [
            'name' => '发送消息 ',
            'meta' => 'key',
            'alert' => '微信服务器会通过消息接口推送消息类型为event的结构给开发者（参考消息接口指南），并且带上按钮中开发者填写的key值，开发者可以通过自定义的key值与用户进行交互；'
        ],
        'view' => [
            'name' => '跳转网页',
            'meta' => 'url',
            'alert' => '微信客户端将会打开开发者在按钮中填写的网页URL，可与网页授权获取用户基本信息接口结合，获得用户基本信息。'
        ],
        'scancode_waitmsg' => [
            'name' => '扫码',
            'meta' => 'key',
            'value' => 'rselfmenu_0_0',
            'alert' => '微信客户端将调起扫一扫工具，完成扫码操作后，将扫码的结果传给开发者，同时收起扫一扫工具，然后弹出“消息接收中”提示框。'
        ],
        'scancode_push' => [
            'name' => '扫码(等待信息)',
            'meta' => 'key',
            'value' => 'rselfmenu_0_1',
            'alert' => '微信客户端将调起扫一扫工具，完成扫码操作后显示扫描结果（如果是URL，将进入URL），且会将扫码的结果传给开发者。'
        ],
        'location_select' => [
            'name' => '地理位置',
            'meta' => 'key',
            'value' => 'rselfmenu_2_0',
            'alert' => '微信客户端将调起地理位置选择工具，完成选择操作后，将选择的地理位置发送给开发者的服务器，同时收起位置选择工具。'
        ],
        'pic_sysphoto' => [
            'name' => '拍照发图',
            'meta' => 'key',
            'value' => 'rselfmenu_1_0',
            'alert' => '微信客户端将调起系统相机，完成拍照操作后，会将拍摄的相片发送给开发者，并推送事件给开发者，同时收起系统相机。'
        ],
        'pic_photo_or_album' => [
            'name' => '拍照相册 ',
            'meta' => 'key',
            'value' => 'rselfmenu_1_1',
            'alert' => '微信客户端将弹出选择器供用户选择“拍照”或者“从手机相册选择”。用户选择后即走其他两种流程。'
        ],
        'pic_weixin' => [
            'name' => '相册发图 ',
            'meta' => 'key',
            'value' => 'rselfmenu_1_2',
            'alert' => '微信客户端将调起微信相册，完成选择操作后，将选择的相片发送给开发者的服务器，并推送事件给开发者，同时收起相册。'
        ],
        'miniprogram' => [
            'name' => '关联小程序',
            'meta' => 'key',
            'alert' => '点击该菜单跳转到关联的小程序'
        ],
    ];

    /**
     * 自定义菜单首页
     *
     * @return string
     */
    public function actionIndex()
    {
        try
        {
            $menu = $this->_app->menu;
            $menus = $menu->current();
        }
        catch (\Exception $e)
        {
            throw new NotFoundHttpException($e->getMessage());
        }

        $type   = Yii::$app->request->get('type', CustomMenu::TYPE_CUSTOM);
        $data   = CustomMenu::find()->where(['type' => $type]);
        $pages  = new Pagination(['totalCount' =>$data->count(), 'pageSize' =>$this->_pageSize]);
        $models = $data->offset($pages->offset)
            ->orderBy('status desc,id desc')
            ->limit($pages->limit)
            ->all();

        return $this->render('index',[
            'pages'   => $pages,
            'models'  => $models,
            'type'  => $type,
            'types'  => CustomMenu::$typeExplain,
        ]);
    }

    /**
     * 创建菜单
     */
    public function actionEdit()
    {
        $request  = Yii::$app->request;
        $id       = $request->get('id');
        $type     = $request->get('type');
        $model    = $this->findModel($id);

        if (Yii::$app->request->isPost)
        {
            $postInfo = Yii::$app->request->post();
            $model = $this->findModel($postInfo['id']);
            $model->attributes = $postInfo;

            $buttons = [];
            foreach ($postInfo['list'] as &$button)
            {
                $arr = [];
                if(isset($button['sub']))
                {
                    $arr['name'] = $button['name'];
                    foreach ($button['sub'] as &$sub)
                    {
                        $sub_button = [];
                        $sub_button['name'] = $sub['name'];
                        $sub_button['type'] = $sub['type'];

                        if($sub['type'] == 'click' || $sub['type'] == 'view')
                        {
                            $sub_button[$this->menuTypes[$sub['type']]['meta']] = $sub['content'];
                        }
                        else if($sub['type'] == 'miniprogram')
                        {
                            $sub_button['appid'] = $sub['appid'];
                            $sub_button['pagepath'] = $sub['pagepath'];
                            $sub_button['url'] = $sub['url'];
                        }
                        else
                        {
                            $sub_button[$this->menuTypes[$sub['type']]['meta']] = $this->menuTypes[$sub['type']]['value'];
                        }

                        $arr['sub_button'][] = $sub_button;
                    }
                }
                else
                {
                    $arr['name'] = $button['name'];
                    $arr['type'] = $button['type'];

                    if($button['type'] == 'click' || $button['type'] == 'view')
                    {
                        $arr[$this->menuTypes[$button['type']]['meta']] = $button['content'];
                    }
                    else if($button['type'] == 'miniprogram')
                    {
                        $arr['appid'] = $button['appid'];
                        $arr['pagepath'] = $button['pagepath'];
                        $arr['url'] = $button['url'];
                    }
                    else
                    {
                        $arr[$this->menuTypes[$button['type']]['meta']] = $this->menuTypes[$button['type']]['value'];
                    }
                }

                $buttons[] = $arr;
            }

            $model->data = serialize($postInfo['list']);
            $model->menu_data = serialize($buttons);

            // 判断写入是否成功
            if (!$model->save())
            {
                return ResultDataHelper::result(422, $this->analysisError($model->getFirstErrors()));
            }

            $menu = $this->_app->menu;
            // 个性化菜单
            if($model->type == CustomMenu::TYPE_INDIVIDUATION)
            {
                $matchRule = [
                    "tag_id" => $model->tag_id,
                    "sex" => $model->sex,
                    "country" => "中国",
                    "province" => $model->province,
                    "city" => $model->city,
                    "client_platform_type" => $model->client_platform_type,
                    "language" => $model->language,
                ];

                if (($menuResult = $menu->create($buttons, $matchRule)) && isset($menuResult['errcode'])) // 自定义菜单
                {
                    return ResultDataHelper::result(422, $menuResult['errmsg']);
                }
                else
                {
                    $model->menu_id = $menuResult['menuid'];
                    $model->save();
                }
            }
            else
            {
                if (($menuResult = $menu->create($buttons)) && $menuResult['errcode'] != 0) // 自定义菜单
                {
                    return ResultDataHelper::result(422, $menuResult['errmsg']);
                }
            }

            return ResultDataHelper::result(200, "修改成功");
        }

        return $this->render('edit', [
            'model' => $model,
            'menuTypes' => $this->menuTypes,
            'type' => $type,
            'fansTags' => FansTags::getTags($this->_app)
        ]);
    }

    /**
     * 删除菜单
     *
     * @param $id
     * @return mixed
     */
    public function actionDelete($id, $type)
    {
        $model = $this->findModel($id);

        // 个性化菜单删除
        !empty($model['menu_id']) && $this->_app->menu->delete($model['menu_id']);

        if ($model->delete())
        {
            return $this->message("删除成功",$this->redirect(['index','type' => $type]));
        }
        else
        {
            return $this->message("删除失败",$this->redirect(['index','type' => $type]),'error');
        }
    }

    /**
     * 替换菜单为当前的菜单
     *
     * @param $id
     * @return yii\web\Response
     */
    public function actionSave($id)
    {
        if ($id)
        {
            $model = $this->findModel($id);
            $model->save();

            $menu = $this->_app->menu;
            $menu->create(unserialize($model->menu_data));
        }

        return $this->redirect(['index']);
    }

    /**
     * 同步菜单
     */
    public function actionSync()
    {
        $menu = $this->_app->menu;
        // 获取菜单列表
        $list = $menu->list();

        // 开始获取同步
        $default_menu = [];
        !empty($list['menu']) && $default_menu[] = $list['menu'];
        CustomMenu::Sync($default_menu, 'menu');

        // 个性化菜单
        if (!empty($list['conditionalmenu']))
        {
            CustomMenu::Sync($list['conditionalmenu'], 'conditionalmenu');
        }

        return ResultDataHelper::result(200, '同步菜单成功');
    }

    /**
     * 返回模型
     *
     * @param $id
     * @return $this|CustomMenu|static
     */
    protected function findModel($id)
    {
        if (empty($id))
        {
            $model = new CustomMenu;
            return $model->loadDefaultValues();
        }

        if (empty(($model = CustomMenu::findOne($id))))
        {
            return new CustomMenu;
        }

        return $model;
    }
}