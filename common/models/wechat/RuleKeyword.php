<?php

namespace jianyan\basics\common\models\wechat;

use Yii;
use EasyWeChat\Message\Image;
use EasyWeChat\Message\Video;
use EasyWeChat\Message\Voice;
use EasyWeChat\Message\News;
use jianyan\basics\common\models\sys\Addons;
use common\enums\StatusEnum;

/**
 * This is the model class for table "{{%wechat_rule_keyword}}".
 *
 * @property string $id
 * @property integer $rule_id
 * @property string $module
 * @property string $content
 * @property integer $type
 * @property integer $displayorder
 * @property integer $status
 */
class RuleKeyword extends \yii\db\ActiveRecord
{
    const TYPE_MATCH = 1;
    const TYPE_INCLUDE = 2;
    const TYPE_REGULAR = 3;
    const TYPE_TAKE = 4;

    /**
     * @var array
     */
    public static $typeExplain = [
        self::TYPE_MATCH => '直接匹配关键字',
        self::TYPE_INCLUDE => '正则表达式',
        self::TYPE_REGULAR => '包含关键字',
        self::TYPE_TAKE => '直接接管',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wechat_rule_keyword}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rule_id', 'type', 'displayorder', 'status'], 'integer'],
            [['module', 'content'], 'required'],
            [['module'], 'string', 'max' => 50],
            [['content'], 'string', 'max' => 255],
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
            'module' => '模块ID',
            'content' => '关键字',
            'type' => '类别',
            'displayorder' => '排序',
            'status' => '状态',
        ];
    }

    /**
     * 关键字查询匹配
     * @param string $content 内容
     * @return array|bool
     */
    public static function match($content)
    {
        $keyword = RuleKeyword::find()->andWhere(['or',
            ['and', '{{type}} = :typeMatch', '{{content}} = :content'], // 直接匹配关键字
            ['and', '{{type}} = :typeInclude', 'INSTR(:content, {{content}}) > 0'], // 包含关键字
            ['and', '{{type}} = :typeRegular', ' :content REGEXP {{content}}'], // 正则匹配关键字
        ])->addParams([
                ':content' => $content,
                ':typeMatch' => self::TYPE_MATCH,
                ':typeInclude' => self::TYPE_INCLUDE,
                ':typeRegular' => self::TYPE_REGULAR
            ])
            ->andWhere(['status' => StatusEnum::ENABLED])
            ->orderBy('displayorder desc,id desc')
            ->one();

        if($keyword)
        {
            // 查询直接接管的
            $takeKeyword = RuleKeyword::find()
                ->where(['type' => self::TYPE_TAKE,'status' => StatusEnum::ENABLED])
                ->andFilterWhere(['>','displayorder',$keyword->displayorder])
                ->orderBy('displayorder desc,id desc')
                ->one();
            $takeKeyword && $keyword = $takeKeyword;

            $result = [
                'keyword_id' => $keyword->id,
                'rule_id' => $keyword->rule_id,
                'module' => $keyword->module,
            ];

            // 列表
            $ruleModels = [
                Rule::RULE_MODULE_BASE => ReplyBasic::find(),
                Rule::RULE_MODULE_NEWS => ReplyNews::find()->with('news'),
                // Rule::RULE_MODULE_MUSIC => '音乐回复',
                Rule::RULE_MODULE_IMAGES => ReplyImages::find(),
                Rule::RULE_MODULE_VOICE => ReplyVoice::find(),
                Rule::RULE_MODULE_VIDEO => ReplyVideo::find(),
                Rule::RULE_MODULE_USER_API => ReplyUserApi::find(),
                // Rule::RULE_MODULE_WX_CARD => '微信卡卷回复',
            ];

            // 默认为模块回复不需要查询对应的表
            if(isset($ruleModels[$keyword->module]))
            {
                $table = $ruleModels[$keyword->module];
                // 模型
                $model = $table->where(['rule_id' => $keyword->rule_id])
                    ->orderBy('rand()')
                    ->one();
            }

            switch ($keyword->module)
            {
                // 文字回复
                case  Rule::RULE_MODULE_BASE :
                    $result['content'] = $model->content;

                    break;
                // 图文回复
                case  Rule::RULE_MODULE_NEWS :
                    $news = $model->news;
                    $news_list = [];
                    if($news)
                    {
                        $count_news = count($news);
                        foreach ($news as $vo)
                        {
                            $new_news = new News([
                                'title' => $vo['title'],
                                'description' => $vo['digest'],
                                'url' => $vo['url'],
                                'image' => $vo['thumb_url'],
                            ]);

                            $count_news == 1 ? $news_list = $new_news : $news_list[] = $new_news;
                        }
                    }

                    $result['content'] = $news_list;

                    break;
                // 图片回复
                case  Rule::RULE_MODULE_IMAGES :
                    $result['content'] = new Image([
                        'media_id' => $model->mediaid,
                    ]);

                    break;
                // 视频回复
                case Rule::RULE_MODULE_VIDEO :
                    $result['content'] = new Video([
                        'title' => $model->title,
                        'media_id' => $model->mediaid,
                        'description' => $model->description,
                    ]);

                    break;
                // 语音回复
                case Rule::RULE_MODULE_VOICE :
                    $result['content'] = new Voice([
                        'media_id' => $model->mediaid
                    ]);

                    break;
                // 自定义接口回复
                case Rule::RULE_MODULE_USER_API :
                    $result['content'] = $model->default;
                    if($api_content = ReplyUserApi::getApiData($model, $content))
                    {
                        $result['content'] = $api_content;
                    }

                    break;
                // 默认为模块回复
                default :
                    $reply = Addons::getWechatMessage(Yii::$app->params['wxMessage'], $keyword->module);
                    if($reply)
                    {
                        $result['content'] = $reply;
                    }

                    break;
            }

            return $result;
        }

        return false;
    }

    /**
     * 批量插入关键字
     * @param $otherKeywords -匹配、包含关键字、直接接管
     * @param $rule_id -规则id
     * @param $module -模块id
     * @param $rule
     * @return bool
     */
    public function batchInsert($otherKeywords,$rule_id,$module,$rule)
    {
        if(!$otherKeywords)
        {
            return true;
        }

        $field = ['rule_id','module','content','displayorder','status','type'];

        $rows = [];

        foreach ($otherKeywords as $key => $value)
        {
            foreach ($value as $content)
            {
                $rows[] = [$rule_id,$module,$content,$rule->displayorder,$rule->status,$key];
            }
        }

       return Yii::$app->db->createCommand()->batchInsert(RuleKeyword::tableName(),$field, $rows)->execute();
    }

    /**
     * 删除不在的关键字
     * @param $rule_id
     * @param $keywords
     */
    public function removeKeywords($rule_id, $type, $keywords)
    {
        return RuleKeyword::deleteAll(['and',['rule_id' => $rule_id],['type' => $type],['in','content',$keywords]]);
    }

    /**
     * 批量插入关键字
     * @param $matchKeywords -关键字
     * @param $otherKeywords -匹配、包含关键字、直接接管
     * @param $ruleKeywords -老的关键字
     * @param $rule_id -规则id
     * @param $module -模块id
     * @param $rule
     * @return bool
     */
    public function updateKeywords($matchKeywords, $otherKeywords, $ruleKeywords, $rule_id, $module,$rule)
    {
        if(!isset($otherKeywords[self::TYPE_TAKE]))
        {
            RuleKeyword::deleteAll(['rule_id'=>$rule_id,'type'=>self::TYPE_TAKE]);
        }

        // 获取新的关键字
        $otherKeywords[self::TYPE_MATCH] = explode(',',$matchKeywords);

        // 给关键字赋值默认值
        foreach (self::$typeExplain as $key => $value)
        {
            !isset($otherKeywords[$key]) && $otherKeywords[$key] = [];
        }

        foreach ($otherKeywords as $key => &$vo)
        {
            $vo = array_unique($vo);

            if ($diff = array_diff($ruleKeywords[$key],$vo))
            {
                $this->removeKeywords($rule_id,$key,array_values($diff));
            }

            if (empty($vo = array_diff($vo,$ruleKeywords[$key])))
            {
                unset($otherKeywords[$key]);
            }
        }

        return $this->batchInsert($otherKeywords,$rule_id,$module,$rule);
    }


    /**
     * 批量更新
     * @param $displayorder - 显示顺序
     * @param $status - 状态
     * @param $rule_id - 规则id
     */
    public static function updateAllDisplayorder($displayorder, $status, $rule_id)
    {
        RuleKeyword::updateAll(['displayorder' => $displayorder,'status'=>$status],['rule_id' => $rule_id]);
    }

    /**
     * 验证是否有直接接管
     * @param $ruleKeyword
     * @return bool
     */
    public static function verifyTake($ruleKeyword)
    {
        foreach ($ruleKeyword as $item)
        {
            if($item->type == self::TYPE_TAKE)
            {
                return true;
            }
        }
        return false;
    }

    /**
     * 关联规则
     * @return \yii\db\ActiveQuery
     */
    public function getRule()
    {
        return $this->hasOne(Rule::className(), ['id' => 'rule_id']);
    }

    /**
     * @param $rule_id
     * @return array|RuleKeyword|null|\yii\db\ActiveRecord
     * 返回模型
     */
    public static function findModel($rule_id)
    {
        if (empty($rule_id))
        {
            return new RuleKeyword;
        }

        if (empty(($model = RuleKeyword::find()->where(['rule_id'=>$rule_id])->one())))
        {
            return new RuleKeyword;
        }

        return $model;
    }
}