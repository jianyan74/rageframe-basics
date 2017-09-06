<?php
use yii\helpers\Url;
use common\helpers\AddonsHelp;

$this->title = '已安装的插件';
$this->params['breadcrumbs'][] = ['label' => '系统', 'url' => ['/sys/system/index']];
$this->params['breadcrumbs'][] = ['label' =>  $this->title];
?>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="tabs-container">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="<?= Url::to(['uninstall'])?>"> 已安装的插件</a></li>
                    <li><a href="<?= Url::to(['install'])?>"> 安装插件</a></li>
                    <li><a href="<?= Url::to(['create'])?>"> 设计新插件</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active">
                        <div class="panel-body">
                            <p><input type="text" class="form-control query" placeholder="请输入您要查找的内容..." id="all"></p>
                            <table class="table table-hover">
                                <tbody id="listAddons">
                                <?php foreach ($list as $key => $vo){ ?>
                                    <tr id ="<?= $vo['id'] ?>">
                                        <td class="feed-element" style="width: 70px;">
                                            <?php if(file_exists(AddonsHelp::getAddons($vo['name']).'icon.jpg')){ ?>
                                                <img alt="image" class="img-rounded m-t-xs img-responsive" src="<?php echo "/addons/{$vo['name']}/icon.jpg"; ?>" width="64" height="64">
                                            <?php }else{ ?>
                                                <img alt="image" class="img-rounded m-t-xs img-responsive" src="/resource/backend/img/icon.jpg" width="64" height="64">
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <h4>
                                                <?php echo $vo['title'] ?> <small>( 标识：<?php echo $vo['name'] ?> 版本：<?php echo $vo['version'] ?> 作者：<?php echo $vo['author'] ?> )</small>
                                                <?php if($vo['wxapp_support'] == true){ ?>
                                                    <span class="label label-info">小程序</span><?php } ?>
                                                <?php if($vo['type'] == 'plug'){ ?>
                                                    <span class="label label-info">功能插件</span><?php } ?>
                                                <?php if($vo['hook'] == 1){ ?>
                                                    <span class="label label-info">钩子</span>
                                                <?php } ?>
                                            </h4>
                                            <?php echo $vo['brief_introduction'] ?> <a href="#" class="show-description">详细介绍</a>
                                        </td>
                                        <td>
                                            <a href="<?php echo Url::to(['upgrade','name' => $vo['name']])?>"><span class="btn btn-info btn-sm">更新</span></a>&nbsp
                                            <?php echo $vo['status'] == -1 ? '<span class="btn btn-primary btn-sm" onclick="status(this)">启用</span>': '<span class="btn btn-default btn-sm"  onclick="status(this)">禁用</span>' ;?>
                                            <a href="<?php echo Url::to(['uninstall','name' => $vo['name']])?>" data-method="post"><span class="btn btn-warning btn-sm">卸载</span></a>&nbsp
                                        </td>
                                    </tr>
                                    <tr id ="description-<?= $vo['id'] ?>" style="display: none">
                                        <td></td>
                                       <td colspan="2">
                                           <?php echo $vo['description'] ?>
                                       </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                            <div class="row">
                                <div class="col-sm-12">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--列表-->
<script type="text/html" id="listModel">
    {{each list as value i}}
    <tr id = "{{value.id}}">
        <td class="feed-element" style="width: 70px;">
            <img alt="image" class="img-rounded m-t-xs img-responsive" src="{{value.cover}}" width="64" height="64">
        </td>
        <td>
            <h4>
                {{value.title}}<small> ( 标识：{{value.name}} 版本：{{value.version}} 作者：{{value.author}} )</small>
                {{if value.wxapp_support == true}}
                    <span class="label label-info">小程序</span>
                {{/if}}
                {{if value.type == 'plug'}}
                    <span class="label label-info">功能插件</span>
                {{/if}}
                {{if value.hook == 1}}
                    <span class="label label-info">钩子</span>
                {{/if}}
            </h4>
            {{value.brief_introduction}} <a href="#" class="show-description">详细介绍</a>
        </td>
        <td>
            <a href="{{value.upgrade}}"><span class="btn btn-info btn-sm">更新</span></a>&nbsp
            {{if value.status == -1 }}
            <span class="btn btn-primary btn-sm" onclick="status(this)">启用</span>
            {{else}}
            <span class="btn btn-default btn-sm"  onclick="status(this)">禁用</span>'
            {{/if}}
            <a href="{{value.uninstall}}" data-method="post"><span class="btn btn-warning btn-sm">卸载</span></a>&nbsp
        </td>
    </tr>
    <tr id ="description-{{value.id}}" style="display: none">
        <td></td>
        <td colspan="2">
            {{value.description}}
        </td>
    </tr>
    {{/each}}
</script>

<script>
    $('.query').keyup(function () {
        var value = $(this).val();
        $('#listAddons').html('');
        $.ajax({
            type:"get",
            url:"<?php echo  Url::to(['index'])?>",
            dataType: "json",
            data: {keyword:value},
            success: function(data){
                if(data.flg == 1) {
                    $('#listAddons').html('');
                    var html = template('listModel', data);
                    $('#listAddons').append(html);
                }else{
                    alert(data.msg);
                }
            }
        });
    });

    //显示或者隐藏介绍
    $(document).on("click",".show-description",function(){
        var id = $(this).parent().parent().attr('id');

        if($("#description-"+id).is(":hidden")){
            $("#description-"+id).show();
        }else{
            $("#description-"+id).hide();
        }
    });

    //status => 1:启用;-1禁用;
    function status(obj){
        var status = "";
        var id = $(obj).parent().parent().attr('id');
        var self = $(obj);

        if(self.hasClass("btn-primary")){
            status = 1;
        } else {
            status = -1;
        }

        $.ajax({
            type:"get",
            url:"<?= Url::to(['update-ajax'])?>",
            dataType: "json",
            data: {id:id,status:status},
            success: function(data){
                if(data.flg == 1) {
                    if(self.hasClass("btn-primary")){
                        self.removeClass("btn-primary").addClass("btn-default");
                        self.text('禁用');
                    } else {
                        self.removeClass("btn-default").addClass("btn-primary");
                        self.text('启用');
                    }
                }else{
                    alert(data.msg);
                }
            }
        });
    }
</script>
