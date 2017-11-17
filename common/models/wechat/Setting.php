<?php
namespace jianyan\basics\common\models\wechat;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use common\enums\StatusEnum;
use jianyan\basics\common\models\sys\Addons;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%wechat_setting}}".
 *
 * @property integer $id
 * @property integer $is_msg_history
 * @property integer $msg_history_date
 * @property integer $is_utilization_stat
 * @property integer $append
 * @property integer $updated
 */
class Setting extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wechat_setting}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['history','special'],'string'],
            [['append', 'updated'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                    => 'ID',
            'history'               => '参数',
            'special'               => '特殊消息回复',
            'append'                => 'Append',
            'updated'               => 'Updated',
        ];
    }

    /**
     * 特殊消息回复类别 - 关键字
     */
    const SPECIAL_TYPE_KEYWORD = 1;
    /**
     * 特殊消息回复类别 - 模块
     */
    const SPECIAL_TYPE_MODUL = 2;

    /**
     * 获取参数消息
     * @param $name
     * @return array
     */
    public static function getSetting($name)
    {
        $defaultList = [];
        switch ($name)
        {
            // 历史消息参数设置
            case 'history' :
                $defaultList = [
                    'is_msg_history' => [
                        'title'  => '开启历史消息记录',
                        'status' => StatusEnum::ENABLED,
                    ],
                    'is_utilization_stat' => [
                        'title'  => '开启利用率统计',
                        'status' => StatusEnum::ENABLED,
                    ],
                    'msg_history_date' => [
                        'title'  => '历史消息记录天数',
                        'value'  => 0,
                    ],
                ];
                break;

            // 特殊消息回复
            case 'special' :

                // 获取支持的模块
                $modules = Addons::getModuleList();

                $list = Account::$mtype;
                $defaultList = [];
                foreach ($list as $key => $value)
                {
                    $defaultList[$key]['title'] = $value;
                    $defaultList[$key]['type'] = self::SPECIAL_TYPE_KEYWORD;
                    $defaultList[$key]['content'] = '';
                    $defaultList[$key]['module'] = [];

                    foreach ($modules as $module)
                    {
                        $wechat_message = unserialize($module['wechat_message']);
                        foreach ($wechat_message as $item)
                        {
                            if($key == $item)
                            {
                                $defaultList[$key]['module'][$module['name']] = $module['title'];
                                break;
                            }
                        }
                    }
                }

                break;
        }

        $model = self::findModel();

        if($model[$name])
        {
            $defaultList = ArrayHelper::merge($defaultList,unserialize($model[$name]));
        }
        return $defaultList;
    }

    /**
     * @return array|Setting|null|ActiveRecord
     */
    public static function findModel()
    {
        if (empty(($model = Setting::find()->one())))
        {
            return new Setting;
        }

        return $model;
    }

    /**
     * 行为插入时间戳
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['append','updated'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated'],
                ],
            ],
        ];
    }
}
