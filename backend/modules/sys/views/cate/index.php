<?php
use yii\helpers\Url;

$this->title = '分类管理';
$this->params['breadcrumbs'][] = ['label' =>  $this->title];

?>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>分类管理</h5>
                    <div class="ibox-tools">
                        <a class="btn btn-primary btn-xs" href="<?= Url::to(['edit'])?>" data-toggle='modal' data-target='#ajaxModal'>
                            <i class="fa fa-plus"></i>  创建分类
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th width="50">折叠</th>
                            <th>分类名称</th>
                            <th>排序</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?= $this->render('tree', [
                            'models'=>$models,
                            'parent_title' =>"无",
                            'pid' => 0,
                        ])?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
