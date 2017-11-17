<?php
namespace jianyan\basics\backend\modules\sys\controllers;

use yii;
use yii\data\Pagination;
use jianyan\basics\common\models\sys\Article;
use jianyan\basics\common\models\sys\Tag;
use jianyan\basics\common\models\sys\TagMap;
use common\enums\StatusEnum;
use backend\controllers\MController;

/**
 * 文章管理控制器
 *
 * Class ArticleController
 * @package jianyan\basics\backend\modules\sys\controllers
 */
class ArticleController extends MController
{
    /**
     * @return array
     */
    public function actions()
    {
        return [
            'upload' => Yii::$app->params['ueditorConfig']
        ];
    }

    /**
     * 首页
     */
    public function actionIndex()
    {
        $request  = Yii::$app->request;
        $type     = $request->get('type',1);
        $keyword  = $request->get('keyword');
        $cate_id  = $request->get('cate_id','');

        $where = [];
        if($keyword)
        {
            if($type == 1)
            {
                $where = ['like', 'title', $keyword];// 标题
            }
        }

        $data = Article::find()->where($where)->andFilterWhere(['>=','status',StatusEnum::DISABLED]);
        !empty($cate_id) && $data->andWhere(['cate_id' => $cate_id]);
        $pages = new Pagination(['totalCount' =>$data->count(), 'pageSize' => $this->_pageSize]);
        $models = $data->offset($pages->offset)
            ->orderBy('sort asc,append desc')
            ->limit($pages->limit)
            ->all();

        return $this->render('index',[
            'models'  => $models,
            'pages'   => $pages,
            'type'    => $type,
            'keyword' => $keyword,
            'cate_id' => $cate_id,
        ]);
    }

    /**
     * 编辑/新增
     *
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        $request        = Yii::$app->request;
        $id     = $request->get('id');
        $model          = $this->findModel($id);

        // 文章标签
        $tags = Tag::find()->with([
            'tagMap' => function($query) use ($id){
                $query->andWhere(['article_id' => $id]);
            },])->all();

        if ($model->load(Yii::$app->request->post()) && $model->save())
        {
            // 更新文章标签
            TagMap::addTags($model->id,$request->post('tag'));
            return $this->redirect(['index']);
        }

        return $this->render('edit', [
            'model'     => $model,
            'tags'      => $tags,
        ]);
    }

    /**
     * 逻辑删除
     *
     * @param $id
     * @return mixed
     */
    public function actionHide($id)
    {
        $model = $this->findModel($id);
        $model->status = StatusEnum::DELETE;

        if($model->save())
        {
            return $this->message("删除成功",$this->redirect(['index']));
        }
        else
        {
            return $this->message("删除失败",$this->redirect(['index']),'error');
        }
    }

    /**
     * 还原
     *
     * @param $id
     * @return mixed
     */
    public function actionShow($id)
    {
        $model = $this->findModel($id);
        $model->status = StatusEnum::ENABLED;

        if($model->save())
        {
            return $this->message("还原成功",$this->redirect(['recycle']));
        }
        else
        {
            return $this->message("还原失败",$this->redirect(['recycle']),'error');
        }
    }

    /**
     * 回收站
     *
     * @return string
     */
    public function actionRecycle()
    {
        $request  = Yii::$app->request;
        $type     = $request->get('type',1);
        $keyword  = $request->get('keyword');
        $cate_id  = $request->get('cate_id','');

        $where = [];
        if($keyword)
        {
            if($type == 1)
            {
                $where = ['like', 'title', $keyword];// 标题
            }
        }

        $data = Article::find()->where($where)->andFilterWhere(['>=','status',StatusEnum::DELETE]);
        !empty($cate_id) && $data->andWhere(['cate_id' => $cate_id]);
        $pages = new Pagination(['totalCount' =>$data->count(), 'pageSize' => $this->_pageSize]);
        $models = $data->offset($pages->offset)
            ->orderBy('sort asc,append desc')
            ->limit($pages->limit)
            ->all();

        return $this->render('recycle',[
            'models'  => $models,
            'pages'   => $pages,
            'type'    => $type,
            'keyword' => $keyword,
            'cate_id' => $cate_id,
        ]);
    }

    /**
     * 删除
     *
     * @param null $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        if($this->findModel($id)->delete())
        {
            return $this->message("删除成功",$this->redirect(['recycle']));
        }
        else
        {
            return $this->message("删除失败",$this->redirect(['recycle']),'error');
        }
    }

    /**
     * 一键清空
     *
     * @return mixed
     */
    public function actionDeleteAll()
    {
        Article::deleteAll(['status' => StatusEnum::DELETE]);
        return $this->message("清空成功",$this->redirect(['recycle']));
    }

    /**
     * 返回模型
     *
     * @param $id
     * @return $this|Article|static
     */
    protected function findModel($id)
    {
        if (empty($id))
        {
            $model = new Article;
            return $model->loadDefaultValues();
        }

        if (empty(($model = Article::findOne($id))))
        {
            $model = new Article;
            return $model->loadDefaultValues();
        }

        return $model;
    }
}