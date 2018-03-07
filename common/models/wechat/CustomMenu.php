<?php
namespace jianyan\basics\common\models\wechat;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%wechat_custom_menu}}".
 *
 * @property integer $id
 * @property integer $type
 * @property string $title
 * @property integer $sex
 * @property integer $group_id
 * @property integer $client_platform_type
 * @property string $area
 * @property string $data
 * @property integer $status
 * @property integer $is_deleted
 * @property string $append
 * @property integer $updated
 */
class CustomMenu extends ActiveRecord
{
    const TYPE_CUSTOM = 1;
    const TYPE_INDIVIDUATION = 2;

    public static $typeExplain = [
        self::TYPE_CUSTOM => '默认菜单',
        self::TYPE_INDIVIDUATION => '个性化菜单',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wechat_custom_menu}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['id', 'type', 'sex', 'tag_id', 'client_platform_type', 'status', 'append', 'updated'], 'integer'],
            [['data','menu_data','province'], 'string'],
            [['title'], 'string', 'max' => 30],
            [['city', 'language'], 'string', 'max' => 50],
            [['title'], 'verifyEmpty'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '菜单类型',
            'title' => '菜单名称',
            'sex' => '性别',
            'tag_id' => '分组',
            'client_platform_type' => '客户端类型',
            'city' => '市',
            'province' => '省',
            'language' => '语言',
            'data' => '数据',
            'status' => '状态',
            'menu_data' => '微信数据',
            'append' => '创建时间',
            'updated' => '修改时间',
        ];
    }

    /**
     * 验证是否全部为空
     *
     * @return bool|void
     */
    public function verifyEmpty()
    {
        if($this->type == self::TYPE_INDIVIDUATION && empty($this->sex) && empty($this->tag_id) && empty($this->client_platform_type) && empty($this->city) && empty($this->province) && empty($this->language))
        {
            return $this->addError('sex', '菜单显示对象至少要有一个匹配信息是不为空的');
        }

        return true;
    }

    /**
     * 同步菜单
     *
     * @param $list
     * @param string $type
     */
    public static function Sync($list,$type = 'menu')
    {
        foreach($list as $value)
        {
            $model = new CustomMenu;
            $model = $model->loadDefaultValues();
            $model->title = "默认菜单";
            empty($value['menuid']) && $value['menuid'] = '';
            $model->menu_id = $value['menuid'];

            // 个性化菜单
            if($type == "conditionalmenu")
            {
                $model->title = "个性化菜单";
                $model->type = CustomMenu::TYPE_INDIVIDUATION;
                $model->attributes = $value['matchrule'];
            }

            $data = [];
            $buttons = $value['button'];
            foreach ($buttons as &$button)
            {
                $arr = [];
                $arr['name'] = $button['name'];
                $arr['type'] = 'click';
                $arr['content'] = '';

                // 判断是否有子菜单
                if(!empty($button['sub_button']))
                {
                    //$button['sub_button'] = $button['sub_button']['list'];
                    //unset($button['sub_button']['list']);

                    foreach ($button['sub_button'] as $sub)
                    {
                        $sub_button = [];
                        $sub_button['name'] = $sub['name'];
                        $sub_button['type'] = $sub['type'];

                        if($sub['type'] == 'view')
                        {
                            $sub_button['content'] = $sub['url'];
                        }
                        else if($sub['type'] == 'miniprogram')
                        {
                            $sub_button['appid'] = $sub['appid'];
                            $sub_button['pagepath'] = $sub['pagepath'];
                            $sub_button['url'] = $sub['url'];
                        }
                        else
                        {
                            $sub_button['content'] = $sub['key'];
                        }

                        $arr['sub'][] = $sub_button;
                    }
                }
                else
                {
                    $arr['type'] = $button['type'];
                    if ($button['type'] == 'view')
                    {
                        $arr['content'] = $button['url'];
                    }
                    elseif($button['type'] == 'miniprogram')
                    {
                        $arr['appid'] = $button['appid'];
                        $arr['pagepath'] = $button['pagepath'];
                        $arr['url'] = $button['url'];
                    }
                    else
                    {
                        $arr['content'] = $button['key'];
                    }
                }

                $data[] = $arr;
            }

            $model->menu_data = serialize($buttons);
            $model->data = serialize($data);

            if(!(CustomMenu::find()->where(['menu_id' => $value['menuid']])->orWhere(['menu_data' => $buttons])->one()))
            {
                $model->save();
            }
        }
    }

    /**
     * 修改默认菜单状态
     *
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $this->status = 1;
        return parent::beforeSave($insert);
    }

    /**
     * 修改其他菜单状态
     *
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->type == self::TYPE_CUSTOM)
        {
            CustomMenu::updateAll(['status' => -1],['and',['not in', 'id', [$this->id]],['type' => self::TYPE_CUSTOM]]);
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * 行为插入时间戳
     *
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
