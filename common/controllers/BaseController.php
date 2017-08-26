<?php
namespace jianyan\basics\common\controllers;

use Yii;
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
     * ajax信息返回
     * @var
     */
    protected $result;

    /**
     * 默认分页大小
     * @var int
     */
    public $_pageSize = 20;

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
        return $this->result ? $this->result : new ResultDataHelper();
    }

    /**
     * 获取消息返回
     * @return array
     */
    public function getResult()
    {
        $result = [
            'code' => strval($this->result->code),
            'message' => trim($this->result->message),
            'data' => $this->result->data ? $this->result->data : [],
        ];

        // 测试环境显示处理时间信息 方便优化
        //$result['use_time'] = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];

         return ArrayHelper::toArray($result);
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