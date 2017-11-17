<?php
namespace jianyan\basics\backend\modules\wechat\controllers;

use yii;
use yii\web\UploadedFile;
use jianyan\basics\common\models\wechat\Setting;
use common\helpers\StringHelper;

/**
 * 参数设置控制器
 *
 * Class SettingController
 * @package jianyan\basics\backend\modules\wechat\controllers
 */
class SettingController extends WController
{
    /**
     * 参数设置
     *
     * @return string|yii\web\Response
     */
    public function actionHistoryStat()
    {
        if ($setting = Yii::$app->request->post('setting'))
        {
            $setting['msg_history_date']['value'] = (int)$setting['msg_history_date']['value'];

            $model = Setting::findModel();
            $model->history = serialize($setting);
            if($model->save())
            {
                return $this->redirect(['history-stat']);
            }
        }

        return $this->render('history-stat',[
            'list' => Setting::getSetting('history'),
        ]);
    }

    /**
     * 特殊消息回复
     *
     * @return string|yii\web\Response
     */
    public function actionSpecialMessage()
    {
        if ($setting = Yii::$app->request->post('setting'))
        {
            $model = Setting::findModel();
            $model->special = serialize($setting);
            if($model->save())
            {
                return $this->redirect(['special-message']);
            }
        }

        return $this->render('special-message',[
            'list' => Setting::getSetting('special'),
        ]);
    }

    /**
     * 上传JS接口安全域名文件
     *
     * @return string
     */
    public function actionUploadAuthFile()
    {
        if(Yii::$app->request->isPost)
        {
            $file = $_FILES['jsFile'];
            $file_name = $file['name'];// 原名称
            $file_exc = StringHelper::clipping($file_name);// 后缀
            if($file_exc != '.txt')
            {
                return $this->message('文件类型必须是txt',$this->redirect(['upload-auth-file']),'error');
            }

            // 利用yii2自带的上传
            $uploadFile = UploadedFile::getInstanceByName('jsFile');
            $uploadFile->saveAs(Yii::getAlias("@rootPath") . '/web/' . $file_name);

            return $this->message('上传成功',$this->redirect(['upload-auth-file']));
        }

        return $this->render('upload-auth-file',[

        ]);
    }
}
