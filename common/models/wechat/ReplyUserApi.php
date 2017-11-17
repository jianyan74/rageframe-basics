<?php
namespace jianyan\basics\common\models\wechat;

use Yii;
use yii\web\NotFoundHttpException;

/**
 * This is the model class for table "{{%wechat_reply_user_api}}".
 *
 * @property integer $id
 * @property integer $rule_id
 * @property string $api_url
 * @property string $description
 * @property string $default
 * @property integer $cache_time
 */
class ReplyUserApi extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wechat_reply_user_api}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rule_id', 'cache_time'], 'integer'],
            [['api_url', 'description'], 'string', 'max' => 255],
            [['default'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rule_id' => '规则ID',
            'api_url' => '文件列表',
            'description' => '备注说明',
            'default' => '默认回复文字',
            'cache_time' => '缓存时间',
        ];
    }

    /**
     * 获取接口数据
     * @param $model
     * @param $content
     * @return bool|mixed|string
     */
    public static function getApiData($model, $content)
    {
        try
        {
            // 读取接口信息
            if($model->cache_time > 0)
            {
                // 尝试从缓存中取回 $data
                $cache = Yii::$app->cache;
                $key = 'user_api_cache' . $model->api_url . $content;
                $data = $cache->get($key);
                if ($data === false)
                {
                    $data = self::ApiData($model, Yii::$app->params['wxMessage']);
                    $cache->set($key, $data, $model->cache_time);
                }
            }
            else
            {
                $data = self::ApiData($model, Yii::$app->params['wxMessage']);
            }

            return $data;
        }
        catch (\Exception $e)
        {
            Yii::warning($e->getMessage());
            return '接口异常,请联系管理员';
        }
    }

    /**
     * @param $model
     * @param $content
     * @return mixed
     * @throws NotFoundHttpException
     */
    protected static function ApiData($model, $content)
    {
        $class = Yii::$app->params['userApiNamespace'] . '\\' . $model->api_url;
        if(!class_exists($class))
        {
            throw new NotFoundHttpException($class . '未找到');
        }

        $class = new $class;
        if(!method_exists($class,'run'))
        {
            throw new NotFoundHttpException($class . '/run 方法未找到');
        }

        return $class->run($content);
    }

    /**
     * @return array
     */
    public static function getList()
    {
        $api_dir = Yii::$app->params['userApiPath'];
        // 获取api列表
        $dirs = array_map('basename', glob($api_dir.'/*'));
        $list = [];
        foreach ($dirs as $dir)
        {
            // 正则匹配文件名
            if(preg_match('/Api.(php)$/', $dir))
            {
                $list[] = $dir;
            }
        }

        $arr = [];
        foreach ($list as $value)
        {
            $key = str_replace(".php","",$value);
            $arr[$key] = $value;
        }

        return $arr;
    }
}
