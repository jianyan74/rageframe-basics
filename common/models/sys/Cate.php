<?php

namespace jianyan\basics\common\models\sys;

use Yii;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use common\enums\StatusEnum;
use common\helpers\SysArrayHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "{{%cate}}".
 *
 * @property integer $cate_id
 * @property string $title
 * @property integer $pid
 * @property integer $sort
 * @property integer $status
 * @property integer $level
 * @property string $groups
 * @property integer $append
 * @property integer $updated
 */
class Cate extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sys_article_cate}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title','status'], 'required'],
            [['pid', 'sort', 'status', 'level', 'append', 'updated'], 'integer'],
            [['title', 'group'], 'string', 'max' => 50],
            [['pid','sort', 'group'], 'default', 'value' => 0],
            [['level'], 'default', 'value' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'        => '分类id',
            'title'     => '分类名称',
            'pid'       => 'Pid',
            'sort'      => '排序',
            'status'    => '显示状态',
            'level'     => '级别',
            'group'     => '分组',
            'append'    => '创建时间',
            'updated'   => '修改时间',
        ];
    }

    /**
     * @param $cate_id
     */
    public static function getTitle($cate_id)
    {
        $cate = self::findOne($cate_id);
        return $cate ? $cate['title'] : "未选择";
    }

    /**
     * 根据父级ID返回信息
     * @param int $pid
     * @return array
     */
    public static function getList($pid = 0)
    {
        $cates = self::find()
            ->where(['pid' => $pid, 'status' => StatusEnum::ENABLED])
            ->all();

        return $cates;
    }

    /**
     * 获取下拉列表
     * @return array
     */
    public static function getTree()
    {
        $cates = self::find()
            ->where(['status' => StatusEnum::ENABLED])
            ->asArray()
            ->all();

        $cates = SysArrayHelper::itemsMerge($cates);
        return ArrayHelper::map(SysArrayHelper::itemsMergeDropDown($cates),'id','title');
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['append', 'updated'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated'],
                ],
            ],
        ];
    }
}
