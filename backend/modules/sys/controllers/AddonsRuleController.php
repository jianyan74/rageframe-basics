<?php
namespace jianyan\basics\backend\modules\sys\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use jianyan\basics\common\models\wechat\Rule;
use jianyan\basics\common\models\wechat\ReplyAddon;
use jianyan\basics\common\models\wechat\RuleKeyword;
use jianyan\basics\common\models\sys\AddonsBinding;
use jianyan\basics\common\models\sys\Addons;
use jianyan\basics\backend\modules\wechat\controllers\RuleController;

/**
 * 规则回复
 *
 * Class AddonsRuleController
 * @package jianyan\basics\backend\modules\sys\controllers
 */
class AddonsRuleController extends RuleController
{
    public $_module = Rule::RULE_MODULE_ADDON;

    /**
     * 编辑
     *
     * @return mixed|string|yii\web\Response
     */
    public function actionEdit()
    {
        $request = Yii::$app->request;
        $addon = $request->get('addon');
        if(!($addonModel = Addons::getAddon($addon)))
        {
            throw new NotFoundHttpException('插件不存在');
        }

        /**插件信息加入公共配置**/
        Yii::$app->params['addon']['info'] = $addonModel;
        Yii::$app->params['addon']['binding'] = AddonsBinding::getList($addonModel['name']);

        // 回复规则
        $rule = $this->findRuleModel($addon);
        // 默认关键字
        $keyword = new RuleKeyword();
        // 基础
        $model = $this->findModel($addon);

        // 关键字列表
        $ruleKeywords = [
            RuleKeyword::TYPE_MATCH => [],
            RuleKeyword::TYPE_REGULAR => [],
            RuleKeyword::TYPE_INCLUDE => [],
            RuleKeyword::TYPE_TAKE => [],
        ];

        if($rule['ruleKeyword'])
        {
            foreach ($rule['ruleKeyword'] as  $value)
            {
                $ruleKeywords[$value['type']][] = $value['content'];
            }
        }

        if ($rule->load(Yii::$app->request->post()) && $model->load(Yii::$app->request->post()) && $keyword->load(Yii::$app->request->post()))
        {
            $transaction = Yii::$app->db->beginTransaction();
            try
            {
                // 编辑
                if($rule->save())
                {
                    // 获取规则ID
                    $model->rule_id = $rule->id;
                    // 其他匹配包含关键字
                    $otherKeywords = Yii::$app->request->post('ruleKey',[]);
                    $resultKeywords = $keyword->updateKeywords($keyword->content, $otherKeywords, $ruleKeywords, $rule->id, $addon, $rule);

                    if($model->save() && $resultKeywords)
                    {
                        $transaction->commit();
                        return $this->redirect(['/wechat/reply-addon/edit','addon' => $addon]);
                    }
                    else
                    {
                        throw new \Exception('插入失败');
                    }
                }
                else
                {
                    throw new \Exception('插入失败！');
                }
            }
            catch (\Exception $e)
            {
                $transaction->rollBack();
                return $this->message($e->getMessage(),$this->redirect(['rule/index']),'error');
            }
        }

        return $this->render('edit',[
            'rule' => $rule,
            'model' => $model,
            'keyword' => $keyword,
            'title' => '规则管理',
            'ruleKeywords' => $ruleKeywords,
            'addonModel' => $addonModel,
            'binding' => Yii::$app->params['addon']['binding'],
        ]);
    }

    /**
     * 返回规则模型
     *
     * @param $id
     * @return $this|Rule|static
     */
    protected function findRuleModel($addon)
    {
        if (empty(($model = Rule::find()->with('ruleKeyword')->where(['module' => $addon])->one())))
        {
            $model = new Rule;
            $model->module = $addon;
            return $model->loadDefaultValues();
        }

        return $model;
    }

    /**
     * 返回模型
     *
     * @param $id
     * @return array|ReplyAddon|null|\yii\db\ActiveRecord
     */
    protected function findModel($addon)
    {
        if (empty($addon))
        {
            $model = new ReplyAddon;
            $model->addon = $addon;
            return $model;
        }

        if (empty(($model = ReplyAddon::find()->where(['addon' => $addon])->one())))
        {
            $model = new ReplyAddon;
            $model->addon = $addon;
            return $model;
        }

        return $model;
    }
}
