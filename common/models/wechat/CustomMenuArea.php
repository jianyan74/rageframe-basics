<?php
namespace jianyan\basics\common\models\wechat;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%wechat_custom_menu_area}}".
 *
 * @property int $id
 * @property string $title 标题
 * @property int $pid 父级id
 */
class CustomMenuArea extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wechat_custom_menu_area}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pid','level'], 'integer'],
            [['title'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'pid' => 'Pid',
        ];
    }

    /**
     * 根据父级ID返回信息
     *
     * @param int $parentid
     * @return array
     */
    public static function getCityList($parentid = 0)
    {
        // 获取缓存信息
        $key = "wechat:coustom:menu:area:" . $parentid;
        $model = Yii::$app->cache->get($key);
        if(!$model)
        {
            $model = self::findAll(['pid' => $parentid]);
            // 设置缓存
            Yii::$app->cache->set($key, $model);
        }

        return ArrayHelper::map($model,'title','title');
    }

    /**
     * 根据父级标题返回信息
     *
     * @param int $parentid
     * @return array
     */
    public static function getCityTitle($title)
    {
        if($model = CustomMenuArea::findOne(['title' => $title,'level' => 2]))
        {
            return CustomMenuArea::getCityList($model->id);
        }

        return [];
    }
}
