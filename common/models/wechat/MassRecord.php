<?php

namespace jianyan\basics\common\models\wechat;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%wechat_mass_record}}".
 *
 * @property string $id
 * @property string $group_name
 * @property string $fans_num
 * @property string $msg_type
 * @property string $content
 * @property integer $group
 * @property string $attach_id
 * @property string $media_id
 * @property integer $type
 * @property integer $status
 * @property string $cron_id
 * @property string $send_time
 * @property string $final_send_time
 * @property string $append
 */
class MassRecord extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wechat_mass_record}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fans_num', 'tag_id', 'attach_id', 'status', 'cron_id', 'send_time', 'final_send_time', 'append'], 'integer'],
            [['tag_name'], 'string', 'max' => 50],
            [['msg_type','type'], 'string', 'max' => 10],
            [['content'], 'string', 'max' => 10000],
            [['media_id'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tag_name' => '标签名称',
            'fans_num' => '粉丝数量',
            'msg_type' => '消息类别',
            'content' => '内容',
            'tag_id' => '标签id',
            'attach_id' => '资源关联id',
            'media_id' => '微信资源id',
            'type' => '类别',
            'status' => '状态',
            'cron_id' => '定时id',
            'send_time' => '发送时间',
            'final_send_time' => '实际发送时间',
            'append' => '创建时间',
        ];
    }

    /**
     * @return array
     * 行为插入时间戳
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['append'],
                ],
            ],
        ];
    }
}
