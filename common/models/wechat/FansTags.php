<?php

namespace jianyan\basics\common\models\wechat;

use Yii;
use EasyWeChat\Factory;

/**
 * This is the model class for table "{{%wechat_fans_groups}}".
 *
 * @property integer $id
 * @property string $tags
 */
class FansTags extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wechat_fans_tags}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tags'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'      => 'ID',
            'tags'    => '标签',
        ];
    }

    /**
     * 获取标签信息
     * @return mixed
     */
    public static function getTags($app)
    {
        if (empty(($model = self::find()->one())))
        {
            $list = $app->user_tag->list();

            $model = new self();
            $model->tags = serialize($list['tags']);
            $model->save();
        }

        return unserialize($model->tags);
    }

    /**
     * 获取单个标签信息
     * @param $id
     * @return array|mixed
     */
    public static function getTag($id)
    {
        $model = self::find()->one();
        $tags = unserialize($model->tags);
        foreach ($tags as $vo)
        {
            if($vo['id'] == $id)
            {
                return $vo;
            }
        }
    }

    /**
     * 获取标签信息并保存到数据库
     */
    public static function updateTagsList($app)
    {
        $list = $app->user_tag->list();

        $tags = $list['tags'];
        if (empty(($model = FansTags::find()->one())))
        {
            $model = new self();
        }

        $model->tags = serialize($tags);
        $model->save();

        return $tags;
    }

    /**
     * 删除粉丝关联标签
     *
     * @return bool
     */
    public function beforeDelete()
    {
        FansTagMap::deleteAll(['tag_id' => $this->id]);
        return parent::beforeDelete();
    }
}
