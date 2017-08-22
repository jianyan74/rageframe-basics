<?php
namespace jianyan\basics\backend\modules\sys\controllers;

use yii;
use yii\helpers\Html;
use yii\web\Response;
use jianyan\basics\common\models\sys\Provinces;
use backend\controllers\MController;

/**
 * 省市区联动控制器
 * Class ProvincesController
 * @package jianyan\basics\backend\modules\sys\controllers
 */
class ProvincesController extends MController
{
    /**
     * 首页
     */
    public function actionIndex($pid, $type_id = 0)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $str = "--请选择市--";

        $model = Provinces::getCityList($pid);
        if($type_id == 1 && !$pid)
        {
            return Html::tag('option','--请选择市--', ['value'=>'']) ;
        }
        else if($type_id == 2 && !$pid)
        {
            return Html::tag('option','--请选择区--', ['value'=>'']) ;
        }
        else if($type_id == 2 && $model)
        {
            $str = "--请选择区--";
        }

        $str = Html::tag('option',$str, ['value'=>'']) ;
        foreach($model as $value=>$name)
        {
            $str .= Html::tag('option',Html::encode($name),array('value'=>$value));
        }

        return $str;
    }
}