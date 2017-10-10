<?php
namespace jianyan\basics\common\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use common\helpers\ResultDataHelper;

/**
 * 基类控制器
 * Class BaseController
 * @package common\controllers
 */
class BaseController extends Controller
{
    /**
     * 默认分页大小
     * @var int
     */
    protected $_pageSize = 20;

    /**
     * ajax信息返回
     * @var
     */
    protected $_result;

    /**
     * 解析Yii2错误信息
     * @param $errors
     * @return string
     */
    public function analysisError($errors)
    {
        $errors = array_values($errors)[0];
        return $errors ? $errors : '操作失败';
    }

    /**
     * 写入消息返回
     * @return ResultDataHelper
     */
    public function setResult()
    {
        return $this->_result = new ResultDataHelper();
    }

    /**
     * 获取消息返回
     * @return array
     */
    public function getResult()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $result = [
            'code' => strval($this->_result->code),
            'message' => trim($this->_result->message),
            'data' => $this->_result->data ? ArrayHelper::toArray($this->_result->data) : [],
        ];

        // 测试环境显示处理时间信息 方便优化
        if (YII_ENV_TEST)
        {
            $result['use_time'] = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        }

        return $result;
    }

    /**
     * 打印调试
     * @param $array
     */
    public function p($array)
    {
        echo "<pre>";
        print_r($array);
    }
}