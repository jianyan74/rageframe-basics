<?php
namespace jianyan\basics\backend\modules\wechat\controllers;

use Yii;
use jianyan\basics\common\models\wechat\Attachment;
use jianyan\basics\common\models\wechat\MassRecord;
use jianyan\basics\common\models\wechat\FansTags;
use jianyan\basics\common\models\wechat\Rule;

/**
 * 群发记录控制器
 *
 * Class MassRecordController
 * @package jianyan\basics\backend\modules\wechat\controllers
 */
class MassRecordController extends WController
{
    /**
     * 群发消息
     *
     * @var array
     */
    protected $_send = [
        'text' => 'sendText',
        'news' => 'sendNews',
        'voice' => 'sendVoice',
        'image' => 'sendImage',
        'video' => 'sendVideo',
        'card' => 'sendCard',
    ];

    /**
     * 获取粉丝分组 - 群发
     *
     * @return string
     */
    public function actionSendFans($attach_id)
    {
        $attachment = Attachment::getOne($attach_id);

        $model = $this->findModel('');
        $model->attach_id = $attachment->id;
        $model->media_id = $attachment->media_id;
        $model->type = $attachment->type;

        if ($model->load(Yii::$app->request->post()))
        {
            try
            {
                $broadcast = $this->_app->broadcasting;
                $method = $this->_send[$model->type];

                if(!$model['tag_id'])
                {
                    $model->tag_name = '全部粉丝';
                    $result = $broadcast->$method($model->media_id);
                }
                else
                {
                    $result = $broadcast->$method($model->media_id, $model->tag_id);

                    // 获取分组信息
                    $tag = FansTags::getTag($model['tag_id']);

                    $model->tag_name = $tag['name'];
                    $model->fans_num = $tag['count'];
                }

                $model->final_send_time = time();
                $model->msg_id = $result['msg_id'];
                $model->save();
            }
            catch (\Exception $e)
            {
                // 接口调用错误提示
                return $this->message($e->getMessage(),$this->redirect(['attachment/' . $model['type'] . '-index']),'error');
            }

            return $this->message("发送成功",$this->redirect(['attachment/' . $model['type'] . '-index']));
        }

        return $this->renderAjax('send-fans',[
            'model' => $model,
            'tags' => FansTags::getTags($this->_app),
        ]);
    }

    /**
     * 返回模型
     *
     * @param $id
     * @return $this|MassRecord|static
     */
    protected function findModel($id)
    {
        if (empty($id))
        {
            $model = new MassRecord;
            return $model->loadDefaultValues();
        }

        if (empty(($model = MassRecord::findOne($id))))
        {
            return new MassRecord;
        }

        return $model;
    }
}
