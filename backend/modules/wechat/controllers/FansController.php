<?php
namespace jianyan\basics\backend\modules\wechat\controllers;

use jianyan\basics\common\models\wechat\FansTagMap;
use yii;
use yii\data\Pagination;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use jianyan\basics\common\models\wechat\Fans;
use jianyan\basics\common\models\wechat\FansTags;

/**
 * 粉丝管理
 *
 * Class FansController
 * @package jianyan\basics\backend\modules\wechat\controllers
 */
class FansController extends WController
{
    /**
     * 粉丝首页
     *
     * @return string
     */
    public function actionIndex()
    {
        $request  = Yii::$app->request;
        $follow   = $request->get('follow',1);
        $tag_id   = $request->get('tag_id','');
        $keyword  = $request->get('keyword','');

        $where = [];
        if($keyword)
        {
            $where = ['or',['like', 'f.openid', $keyword],['like', 'f.nickname', $keyword]];
        }

        // 关联角色查询
        $data = Fans::find()
            ->where($where)
            ->alias('f')
            ->andWhere(['f.follow' => $follow])
            ->joinWith("tags AS t", true, 'LEFT JOIN')
            ->filterWhere(['t.tag_id' => $tag_id]);

        $pages  = new Pagination(['totalCount' =>$data->count(), 'pageSize' => $this->_pageSize]);
        $models = $data->offset($pages->offset)
            ->with('tags','member')
            ->orderBy('followtime desc,unfollowtime desc')
            ->limit($pages->limit)
            ->all();

        // 全部标签
        $tags = FansTags::getTags($this->_app);
        $allTag = [];
        foreach ($tags as $tag)
        {
            $allTag[$tag['id']] = $tag['name'];
        }

        return $this->render('index',[
            'models'  => $models,
            'pages'   => $pages,
            'follow'  => $follow,
            'keyword' => $keyword,
            'tag_id' => $tag_id,
            'all_fans' => Fans::getCountFollowFans(),
            'fansTags' => $tags,
            'allTag' => $allTag,
        ]);
    }

    /**
     * 粉丝详情
     *
     * @param $id
     * @return string
     */
    public function actionView($id)
    {
        $model = Fans::findOne($id);

        return $this->renderAjax('view',[
            'model' => $model
        ]);
    }

    public function actionMoveTag($fan_id)
    {
        $fans = Fans::find()
            ->where(['id' => $fan_id])
            ->with('tags')
            ->asArray()
            ->one();

        // 用户当前标签
        $fansTags = [];
        foreach ($fans['tags'] as $value)
        {
            $fansTags[] = $value['tag_id'];
        }

        if(Yii::$app->request->isPost)
        {
            $tags = Yii::$app->request->post('tag_id',[]);

            FansTagMap::deleteAll(['fan_id' => $fan_id]);
            foreach ($tags as $tag_id)
            {
                // 判断新标签
                if(!in_array($tag_id, $fansTags))
                {
                    $this->_app->user_tag->tagUsers([$fans['openid']], $tag_id);
                }

                $model = new FansTagMap();
                $model->fan_id = $fan_id;
                $model->tag_id = $tag_id;
                $model->save();
            }

            foreach ($fansTags as $tag_id)
            {
                if(!in_array($tag_id, $tags))
                {
                    $this->_app->user_tag->untagUsers([$fans['openid']], $tag_id);
                }
            }

            return $this->redirect(['index']);
        }

        return $this->renderAjax('move-tag', [
            'tags' => FansTags::getTags($this->_app),
            'fansTags' => $fansTags,
        ]);
    }

    /**
     * 获取全部粉丝
     */
    public function actionGetAllFans()
    {
        $request = Yii::$app->request;
        $next_openid = $request->get('next_openid','');

        // 设置关注全部为为关注
        if (empty($next_openid))
        {
            Fans::updateAll(['follow' => Fans::FOLLOW_OFF ]);
        }

        // 获取全部列表
        $fans_list = $this->_app->user->list();
        $fans_count = $fans_list['total'];

        $total_page = ceil($fans_count / 500);
        for ($i = 0; $i < $total_page; $i++)
        {
            $fans = array_slice($fans_list['data']['openid'], $i * 500, 500);
            // 系统内的粉丝
            $system_fans = Fans::find()
                ->where(['in','openid',$fans])
                ->select('openid')
                ->asArray()
                ->all();

            $new_system_fans = [];
            foreach ($system_fans as $li)
            {
                $new_system_fans[$li['openid']] = $li;
            }

            $add_fans = [];
            foreach($fans as $openid)
            {
                if (empty($new_system_fans) || empty($new_system_fans[$openid]))
                {
                    $add_fans[] = [0,$openid,Fans::FOLLOW_ON,0,'',time(),time()];
                }
            }

            if (!empty($add_fans))
            {
                // 批量插入数据
                $field = ['member_id', 'openid','follow','followtime','tag','append','updated'];
                Yii::$app->db->createCommand()->batchInsert(Fans::tableName(),$field, $add_fans)->execute();
            }

            // 更新当前粉丝为关注
            Fans::updateAll(['follow' =>1 ],['in','openid',$fans]);
        }

        $result = [];
        $result['total'] = $fans_list['total'];
        $result['count'] = !empty($fans_list['data']['openid']) ? $fans_count : 0;
        $result['next_openid'] = $fans_list['next_openid'];

        Yii::$app->response->format = Response::FORMAT_JSON;
        return $result;
    }

    /**
     * 开始同步粉丝数据
     */
    public function actionSync()
    {
        $result = $this->setResult();

        $request = Yii::$app->request;
        $type = $request->post('type','') == 'all' ? 'all' : 'check';
        $page = $request->post('page',0);

        // 全部同步
        if ($type == 'all')
        {
            $limit = 10;
            $offset = $limit * $page;

            // 关联角色查询
            $data = Fans::find()->where(['follow' => 1]);
            $models = $data->offset($offset)
                ->orderBy('id desc')
                ->limit($limit)
                ->asArray()
                ->all();

            if(!empty($models))
            {
                // 同步粉丝信息
                foreach ($models as $fans)
                {
                    Fans::sync($fans['openid'], $this->_app);
                }

                $result->code = 200;
                $result->message = "同步成功!";
                $result->data = [
                    'page' => $page + 1
                ];

                return $this->getResult();
            }

            $result->message = "同步完成!";
            return $this->getResult();
        }

        // 选中同步
        if ($type == 'check')
        {
            $openids = $request->post('openids');
            if (empty($openids) || !is_array($openids))
            {
                $result->message = "请选择粉丝!";
                return $this->getResult();
            }

            // 系统内的粉丝
            $sync_fans = Fans::find()
                ->where(['in','openid',$openids])
                ->asArray()
                ->all();

            if (!empty($sync_fans))
            {
                // 同步粉丝信息
                foreach ($sync_fans as $fans)
                {
                    Fans::sync($fans['openid'],$this->_app);
                }
            }

            $result->message = "同步完成!";
            return $this->getResult();
        }
    }
}