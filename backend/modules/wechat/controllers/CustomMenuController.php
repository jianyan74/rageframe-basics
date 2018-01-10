<?php
namespace jianyan\basics\backend\modules\wechat\controllers;

use yii;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;
use jianyan\basics\common\models\wechat\CustomMenu;

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
//        'miniprogram' => [
//            'name' => '关联小程序',
//            'meta' => 'key',
//            'alert' => '点击该菜单跳转到关联的小程序'
//        ],
    ];

    /**
     * 自定义菜单首页
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

        // 关联角色查询
        $data   = CustomMenu::find();
        $pages  = new Pagination(['totalCount' =>$data->count(), 'pageSize' =>$this->_pageSize]);
        $models = $data->offset($pages->offset)
            ->orderBy('status desc,id desc')
            ->limit($pages->limit)
            ->all();

        return $this->render('index',[
            'pages'   => $pages,
            'models'  => $models,
        ]);
    }

    /**
     * 创建菜单
     */
    public function actionEdit()
    {
        $request  = Yii::$app->request;
        $id       = $request->get('id');
        $model    = $this->findModel($id);

        if (Yii::$app->request->isPost)
        {
            $result = $this->setResult();
            $result->message = "修改失败!";

            $postInfo = Yii::$app->request->post();
            $model    = $this->findModel($postInfo['id']);
            $model->title = $postInfo['title'];

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
                    else
                    {
                        $arr[$this->menuTypes[$button['type']]['meta']] = $this->menuTypes[$button['type']]['value'];
                    }
                }

                $buttons[] = $arr;
            }

            $model->data = serialize($postInfo['list']);
            $model->menu_data = serialize($buttons);

            if($model->save())
            {
                $menu = $this->_app->menu;
                $menu->create($buttons);

                $result->code = 200;
                $result->message = "修改成功!";
            }
            else
            {
                $result->message = $this->analysisError($model->getFirstErrors());
            }

            return $this->getResult();
        }

        return $this->render('edit', [
            'model' => $model,
            'menuTypes' => $this->menuTypes,
        ]);
    }

    /**
     * 个性化菜单
     */
    public function actionIndividuation()
    {

    }

    /**
     * 个性化菜单编辑
     */
    public function actionIndividuationEdit()
    {

    }

    /**
     * 删除自定义菜单
     * @param $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        if($this->findModel($id)->delete())
        {
            return $this->message("删除成功",$this->redirect(['index']));
        }
        else
        {
            return $this->message("删除失败",$this->redirect(['index']),'error');
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
        if($id)
        {
            $model = $this->findModel($id);
            $model->save();

            $menu = $this->_app->menu;
            $menu->add(unserialize($model->menu_data));
        }

        return $this->redirect(['index']);
    }

    /**
     * 同步菜单
     */
    public function actionSync()
    {
        $result = $this->setResult();

        $menu = $this->_app->menu;
        $current = $menu->current();

        if($current['is_menu_open'] == true)
        {
            $buttons = $current['selfmenu_info']['button'];
            $model = new CustomMenu;
            $model = $model->loadDefaultValues();
            $model->title = "默认菜单";

            $data = [];
            foreach ($buttons as &$button)
            {
                $arr = [];
                $arr['name'] = $button['name'];
                $arr['type'] = 'click';
                $arr['content'] = '';

                // 判断是否有子菜单
                if(isset($button['sub_button']))
                {
                    $button['sub_button'] = $button['sub_button']['list'];
                    unset($button['sub_button']['list']);

                    foreach ($button['sub_button'] as $sub)
                    {
                        $sub_button = [];
                        $sub_button['name'] = $sub['name'];
                        $sub_button['type'] = $sub['type'];

                        if($sub['type'] == 'view')
                        {
                            $sub_button['content'] = $sub['url'];
                        }
                        else
                        {
                            $sub_button['content'] = $sub['key'];
                        }

                        $arr['sub'][] = $sub_button;
                    }
                }
                else
                {
                    $arr['type'] = $button['type'];
                    if($button['type'] == 'view')
                    {
                        $arr['content'] = $button['url'];
                    }
                    else
                    {
                        $arr['content'] = $button['key'];
                    }
                }

                $data[] = $arr;
            }

            $model->menu_data = serialize($buttons);
            $model->data = serialize($data);

            if($menu = CustomMenu::find()->where(['menu_data' => $model->menu_data,'data' => $model->data])->one())
            {
                $menu->save();
            }
            else
            {
                $model->save();
            }

            $result->code = 200;
            $result->message = "同步成功";
        }
        else
        {
            $result->message = "当前没有菜单消息";
        }

        return $this->getResult();
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