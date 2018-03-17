<?php
use yii\helpers\Url;
use common\helpers\SysArrayHelper;

?>
<?php foreach($models as $k => $model){ ?>
    <tr id = <?= $model['id']?>  class="<?php echo $pid?>">
        <td>
            <?php if(!empty($model['-'])){ ?>
                <div class="fa fa-minus-square cf" style="cursor:pointer;"></div>
            <?php } ?>
        </td>
        <td>
            <?= SysArrayHelper::itemsLevel($model['level'], $models, $k)?>
            <?= $model['title']?>&nbsp;
            <!--禁止显示二级分类再次添加三级分类-->
            <?php if($model['pid'] == 0){ ?>
                <a href="<?= Url::to(['edit','pid' => $model['id'],'parent_title'=>$model['title'],'level'=>$model['level']+1])?>" data-toggle='modal' data-target='#ajaxModal'>
                    <i class="fa fa-plus-circle"></i>
                </a>
            <?php } ?>
        </td>
        <td class="col-md-1"><input type="text" class="form-control" value="<?= $model['sort']?>" onblur="rfSort(this)"></td>
        <td>
            <a href="<?= Url::to(['edit','id'=>$model['id'],'parent_title' => $parent_title])?>" data-toggle='modal' data-target='#ajaxModal'><span class="btn btn-info btn-sm">编辑</span></a>&nbsp
            <?php echo $model['status'] == 0 ? '<span class="btn btn-primary btn-sm" onclick="rfStatus(this)">启用</span>': '<span class="btn btn-default btn-sm"  onclick="rfStatus(this)">禁用</span>' ;?>
            <a href="<?= Url::to(['delete','id'=>$model['id']])?>"  onclick="rfDelete(this);return false;"><span class="btn btn-warning btn-sm">删除</span></a>&nbsp
        </td>
    </tr>
    <?php if(!empty($model['-'])){ ?>
        <?= $this->render('tree', [
            'models'=>$model['-'],
            'parent_title' =>$model['title'],
            'pid' => $model['id']." ".$pid,
        ])?>
    <?php } ?>
<?php } ?>




