<?php

namespace jianyan\basics\common\models\wechat;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%wechat_fans_stat}}".
 *
 * @property string $id
 * @property string $new_attention
 * @property string $cancel_attention
 * @property integer $cumulate_attention
 * @property string $date
 */
class FansStat extends \yii\db\ActiveRecord
{
    /**
     * 新关注
     */
    const NEW_ATTENTION = 1;
    /**
     * 取消关注
     */
    const CANCEL_ATTENTION = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wechat_fans_stat}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['new_attention', 'cancel_attention', 'cumulate_attention'], 'integer'],
            [['date'], 'required'],
            [['date'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'new_attention' => '今日新关注',
            'cancel_attention' => '今日取消关注',
            'cumulate_attention' => '累计关注',
            'date' => '日期',
        ];
    }

    /**
     * 类别 1 关注 2 取消关注
     * @param $type
     */
    public static function addStat($type)
    {
        $date = date('Y-m-d');

        $model = self::findOne(['date' => $date]);
        if(!$model)
        {
            $model = new self;
            $model->loadDefaultValues();
            $model->date = $date;
        }

        $type == 1 ? $model->new_attention +=1 : $model->cancel_attention +=1;

        return $model->save();
    }

    /**
     * 关注计算
     */
    public static function upFollowNum()
    {
        if(!($today = self::find()->where(['date' => date('Y-m-d')])->one()))
        {
            $today = new self();
            $today->date = date('Y-m-d');
            $today->append = strtotime($today->date);
        }

        $today->new_attention += 1;
        $today->save();
    }

    /**
     * 取消关注计算
     */
    public static function upUnFollowNum()
    {
        if(!($today = self::find()->where(['date' => date('Y-m-d')])->one()))
        {
            $today = new self();
            $today->date = date('Y-m-d');
            $today->append = strtotime($today->date);
        }

        $today->cancel_attention += 1;
        $today->save();
    }

    /**
     * @param $app
     * @return bool
     */
    public static function getFansStat($app)
    {
        // 缓存设置
        $cacheKey = 'fans:status:todaylock';
        if(Yii::$app->cache->get($cacheKey))
        {
            return true;
        }

        $sevenDays = [
            date('Y-m-d', strtotime('-1 days')),
            date('Y-m-d', strtotime('-2 days')),
            date('Y-m-d', strtotime('-3 days')),
            date('Y-m-d', strtotime('-4 days')),
            date('Y-m-d', strtotime('-5 days')),
            date('Y-m-d', strtotime('-6 days')),
            date('Y-m-d', strtotime('-7 days')),
        ];

        $models = self::find()
            ->where(['in','date',$sevenDays])
            ->all();

        $statUpdate = false;
        $weekStat = [];
        foreach ($models as $model)
        {
            $weekStat[$model['date']] = $model;
        }

        // 查询数据是否有
        foreach ($sevenDays as $sevenDay)
        {
            if (empty($weekStat[$sevenDay]) || $weekStat[$sevenDay]['cumulate_attention'] <= 0)
            {
                $statUpdate = true;
                break;
            }
        }

        if (empty($statUpdate))
        {
            return true;
        }

        // 获取微信统计数据
        $stats = $app->stats;
        // 增减
        $userSummary = $stats->userSummary($sevenDays[6], $sevenDays[0]);
        // 累计用户
        $userCumulate = $stats->userCumulate($sevenDays[6], $sevenDays[0]);

        $list = [];
        if (!empty($userSummary['list']))
        {
            foreach ($userSummary['list'] as $row)
            {
                $key = $row['ref_date'];
                $list[$key]['new_attention'] = $row['new_user'];
                $list[$key]['cancel_attention'] = $row['cancel_user'];
            }
        }

        if (!empty($userCumulate['list']))
        {
            foreach ($userCumulate['list'] as $row)
            {
                $key = $row['ref_date'];
                $list[$key]['cumulate_attention'] = $row['cumulate_user'];
            }
        }

        // 更新到数据库
        foreach ($list as $key => $value)
        {
            $model = new self();
            if(isset($weekStat[$key]))
            {
                $model = $weekStat[$key];
            }

            $model->attributes = $value;
            $model->date = $key;
            $model->append = strtotime($key);
            $model->save();
        }

        // 今日累计关注统计计算
        $cumulate_attention = Fans::getCountFollowFans();
        if(!($today = self::find()->where(['date' => date('Y-m-d')])->one()))
        {
            $today = new self();
            $today->date = date('Y-m-d');
            $today->append = strtotime($today->date);
        }

        $today->cumulate_attention = $cumulate_attention;
        $today->save();

        Yii::$app->cache->set($cacheKey,true,7200);
        return true;
    }

    /**
     * 行为
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['updated'],
                ],
            ],
        ];
    }

}
