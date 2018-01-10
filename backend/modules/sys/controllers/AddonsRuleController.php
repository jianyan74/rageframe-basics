<?php
namespace jianyan\basics\backend\modules\sys\controllers;

use Yii;
use yii\data\Pagination;
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

    protected $_addonModel;

    public function init()
    {
        $addon = Yii::$app->request->get('addon','');
        if(!($addonModel = Addons::getAddon($addon)))
        {
            throw new NotFoundHttpException('插件不存在');
        }

        /**插件信息加入公共配置**/
        Yii::$app->params['addon']['info'] = $addonModel;
        Yii::$app->params['addon']['binding'] = AddonsBinding::getList($addonModel['name']);

        $this->_module = $addon;
        $this->_addonModel = $addonModel;
        parent::init();
    }

    /**
     * 首页
     *
     * @return string
     */
    public function actionIndex()
    {
        $request = Yii::$app->request;
        $keyword = $request->get('keyword','');

        $data = Rule::find()->with('ruleKeyword')
            ->andWhere(['module' => $this->_module])
            ->andFilterWhere(['like', 'name', $keyword]);

        $pages = new Pagination(['totalCount' =>$data->count(), 'pageSize' =>$this->_pageSize]);
        $models = $data->offset($pages->offset)
            ->orderBy('displayorder desc,append desc')
            ->limit($pages->limit)
            ->all();

        return $this->render('index',[
            'module' => $this->_module,
            'models' => $models,
            'pages' => $pages,
            'keyword' => $keyword,
            'addonModel' => $this->_addonModel,
        ]);
    }

    /**
     * 编辑
     *
     * @return mixed|string|yii\web\Response
     */
    public function actionEdit()
    {
        $request = Yii::$app->request;
        $id = $request->get('id', '');

        // 回复规则
        $rule = $this->findRuleModel($id);

        // 默认关键字
        $keyword = new RuleKeyword();
        // 基础
        $model = $this->findModel($id);
        $model->addon = $this->_module;

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
                if(!$rule->save())
                {
                    throw new \Exception('插入失败！');
                }

                // 获取规则ID
                $model->rule_id = $rule->id;
                // 其他匹配包含关键字
                $otherKeywords = Yii::$app->request->post('ruleKey',[]);
                $resultKeywords = $keyword->updateKeywords($keyword->content, $otherKeywords, $ruleKeywords, $rule->id, $this->_module, $rule);

                if($model->save() && $resultKeywords)
                {
                    $transaction->commit();
                    return $this->redirect(['index','addon' => $this->_module]);
                }
                else
                {
                    throw new \Exception('插入失败');
                }
            }
            catch (\Exception $e)
            {
                $transaction->rollBack();
                return $this->message($e->getMessage(),$this->redirect(['index','addon' => $this->_module]),'error');
            }
        }

        return $this->render('edit',[
            'rule' => $rule,
            'model' => $model,
            'keyword' => $keyword,
            'title' => '规则管理',
            'ruleKeywords' => $ruleKeywords,
            'addonModel' => $this->_addonModel,
            'binding' => Yii::$app->params['addon']['binding'],
        ]);
    }

    /**
     * 删除
     *
     * @param $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findRuleModel($id)->delete();
        return $this->redirect(['index','addon' => $this->_module]);
    }

    /**
     * 返回模型
     *
     * @param $id
     * @return array|ReplyAddon|null|\yii\db\ActiveRecord
     */
    protected function findModel($id)
    {
        if (empty($id))
        {
            $model = new ReplyAddon;
            return $model;
        }

        if (empty(($model = ReplyAddon::findOne($id))))
        {
            $model = new ReplyAddon;
            return $model;
        }

        return $model;
    }
}
