<?php
namespace jianyan\basics\backend\controllers;

use Yii;
use yii\base\InvalidConfigException;

/**
 * 快速创建增删改查基类
 *
 * Class CurdController
 * @package jianyan\basics\backend\controllers
 */
class CurdController extends \backend\controllers\MController
{
    /**
     * 基础属性类
     *
     * @var
     */
    public $modelClass;

    /**
     * 是否启用curl视图
     *
     * 启用将直接使用curl的视图显示数据
     * @var bool
     */
    public $curdView = false;

    /**
     * 通过ajax的方式加载编辑视图
     *
     * @var bool
     */
    public $ajaxShowView = false;

    /**
     * 首页查询字段
     *
     * @var array
     */
    public $indexSearch = [];

    /**
     * 首页显示字段
     *
     * @var
     */
    public $indexColumns = [];

    /**
     * 首页按钮
     *
     * @var array
     */
    public $indexButtons = [
        'edit',
        'status',
        'delete'
    ];

    /**
     * 编辑显示字段
     *
     * @var
     */
    public $editColumns;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->modelClass === null)
        {
            throw new InvalidConfigException('"modelClass" 属性必须设置');
        }
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => 'jianyan\basics\backend\actions\IndexAction',
                'modelClass' => $this->modelClass,
                'curdView' => $this->curdView,
                'ajaxShowView' => $this->ajaxShowView,
                'search' => $this->indexSearch,
                'columns' => $this->indexColumns,
                'buttons' => $this->indexButtons,
            ],
            'edit' => [
                'class' => 'jianyan\basics\backend\actions\EditAction',
                'modelClass' => $this->modelClass,
                'columns' => $this->editColumns,
            ],
            'ajax-update' => [
                'class' => 'jianyan\basics\backend\actions\AjaxUpdateAction',
                'modelClass' => $this->modelClass,
            ],
            'delete' => [
                'class' => 'jianyan\basics\backend\actions\DeleteAction',
                'modelClass' => $this->modelClass,
            ],
        ];
    }
}