<?php
namespace jianyan\basics\common\models\wechat;

use Yii;

/**
 * This is the model class for table "{{%wechat_fans_tag_map}}".
 *
 * @property string $id
 * @property string $fan_id 粉丝id
 * @property int $tag_id 标签id
 */
class FansTagMap extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wechat_fans_tag_map}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fan_id', 'tag_id'], 'integer'],
            [['fan_id', 'tag_id'], 'unique', 'targetAttribute' => ['fan_id', 'tag_id']],
        ];
    }

    /**
     * 批量添加标签
     *
     * @param $fan_id
     * @param $data
     * @throws \yii\db\Exception
     */
    public static function add($fan_id, $data)
    {
        self::deleteAll(['fan_id' => $fan_id]);

        $field = ['fan_id', 'tag_id'];
        Yii::$app->db->createCommand()->batchInsert(self::tableName(),$field, $data)->execute();
    }

    /**
     * 关联粉丝
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFans()
    {
        return $this->hasOne(Fans::className(),['id' => 'fan_id']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fan_id' => '粉丝id',
            'tag_id' => '标签id',
        ];
    }
}
