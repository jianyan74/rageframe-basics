<?php
namespace jianyan\basics\backend\modules\wechat\controllers;

use yii;
use yii\data\Pagination;
use jianyan\basics\common\models\wechat\QrcodeStat;
use common\helpers\ExcelHelper;

/**
 * 二维码扫描统计
 * Class QrStatController
 * @package jianyan\basics\backend\modules\wechat\controllers
 */
class QrStatController extends WController
{
    /**
     * 首页
     */
    public function actionIndex()
    {
        $request  = Yii::$app->request;
        $type     = $request->get('type','');
        $keyword  = $request->get('keyword','');
        $from_date  = $request->get('from_date',date('Y-m-d',strtotime("-60 day")));
        $to_date  = $request->get('to_date',date('Y-m-d',strtotime("+1 day")));

        $where = [];
        if($keyword)
        {
            $where = ['like', 'name', $keyword];//标题
        }

        $data = QrcodeStat::find()
            ->where($where)
            ->andFilterWhere(['type' => $type])
            ->andFilterWhere(['between','append',strtotime($from_date),strtotime($to_date)]);

        $attention_data = clone $data;
        $scan_data = clone $data;

        $pages = new Pagination(['totalCount' =>$data->count(), 'pageSize' =>$this->_pageSize]);
        $models = $data->offset($pages->offset)
            ->orderBy('append desc')
            ->limit($pages->limit)
            ->all();

        //关注统计
        $attention_count = $attention_data->andWhere(['type' => QrcodeStat::TYPE_ATTENTION])->count();
        //扫描统计
        $scan_count = $scan_data->andWhere(['type' => QrcodeStat::TYPE_SCAN])->count();

        return $this->render('index',[
            'models' => $models,
            'pages' => $pages,
            'type' => $type,
            'attention_count' => $attention_count,
            'scan_count' => $scan_count,
            'keyword' => $keyword,
            'from_date' => $from_date,
            'to_date' => $to_date,
        ]);
    }

    /**
     * 删除
     * @param $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        if(QrcodeStat::findOne($id)->delete())
        {
            return $this->message("删除成功",$this->redirect(['index']));
        }
        else
        {
            return $this->message("删除失败",$this->redirect(['index']),'error');
        }
    }

    /**
     * 导出
     */
    public function actionExport()
    {
        $request  = Yii::$app->request;
        $type     = $request->get('type');
        $keyword  = $request->get('keyword');
        $from_date  = $request->get('from_date');
        $to_date  = $request->get('to_date');

        $dataList = QrcodeStat::find()
            ->where(['between','append',strtotime($from_date),strtotime($to_date)])
            ->andFilterWhere(['type' => $type])
            ->andFilterWhere(['like', 'name', $keyword])
            ->orderBy('append desc')
            ->with('fans')
            ->asArray()
            ->all();

        $header = [
            ['field' => 'id', 'name' =>  'ID', 'type' => 'text'],
            ['field' => 'name', 'name' =>  '场景名称', 'type' => 'text'],
            ['field' => 'fans.openid', 'name' =>  'openid', 'type' => 'text'],//表示获取二维数组的字段
            ['field' => 'fans.nickname', 'name' =>  '昵称', 'type' => 'text'],
            ['field' => 'scene_str', 'name' =>  '场景值', 'type' => 'text'],
            ['field' => 'scene_id', 'name' =>  '场景ID', 'type' => 'text'],
            ['field' => 'type', 'name' =>  '关注扫描', 'type' => 'selectd', 'rule' => ['' => '全部','1' => '关注','2' => '扫描']],
            ['field' => 'append', 'name' => '创建日期', 'type' => 'date', 'rule' => 'Y-m-d H:i:s'],
        ];

        //导出Excel
        ExcelHelper::exportExcelData($dataList, $header, '扫描统计_' . time());
    }
}
