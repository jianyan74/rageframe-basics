<?php
use yii\helpers\Url;

$this->title = '前台导航';
$this->params['breadcrumbs'][] = ['label' =>  $this->title];
?>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>前台导航</h5>
                    <div class="ibox-tools">
                        <a class="btn btn-primary btn-xs" href="<?= Url::to(['edit'])?>" data-toggle='modal' data-target='#ajaxModal'>
                            <i class="fa fa-plus"></i>  创建导航
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>标题</th>
                            <th>路由</th>
                            <th>图标css</th>
                            <th>排序</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?= $this->render('tree', [
                            'models'=>$models,
                            'parent_title' =>"无",
                        ])?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
