<?php
namespace jianyan\basics\backend\modules\wechat\controllers;

use yii\helpers\Html;
use jianyan\basics\common\models\wechat\CustomMenuArea;

/**
 * 自定义菜单省市
 *
 * Class CustomMenuAreaController
 * @package jianyan\basics\backend\modules\wechat\controllers
 */
class CustomMenuAreaController extends WController
{
    /**
     * @param $val
     * @return array
     */
   public function actionIndex($val)
   {
       $result = $this->setResult();

       $model = CustomMenuArea::findOne(['title' => $val,'level' => 2]);
       $model = CustomMenuArea::getCityList($model->id);

       $str = Html::tag('option','市', ['value'=>'']) ;
       foreach($model as $value => $name)
       {
           $str .= Html::tag('option',Html::encode($name),array('value' => $value));
       }

       $result->code = 200;
       $result->data = $str;
       $result->message = '获取成功';
       return $this->getResult();
   }
}