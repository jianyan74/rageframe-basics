<?php
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\helpers\Html;

$this->title = $model->isNewRecord ? '创建' : '编辑';
$this->params['breadcrumbs'][] = ['label' => '自定义菜单', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?=Html::cssFile('/resource/backend/css/common.css')?>
<?=Html::jsFile('/resource/backend/js/vue.min.js')?>
<?=Html::jsFile('/resource/backend/js/Sortable.min.js')?>
<?=Html::jsFile('/resource/backend/js/vuedraggable.min.js')?>

<style>
	.menuView{
		height: 480px;
		position: relative;
		background-color: white;
	}
	.custommenu{
		position: relative;
	}
	.custommenu_sub_container{
		position: absolute;
		width: 100%;
		top: -5px;
		transform: translateY(-100%);
	}

    .phone-header {
        position: relative;
        background: transparent url(/resource/backend/img/bg_mobile_head_default.png);
        background-position: 0 0;
        overflow: hidden;
        text-align: center;
        padding-top: 30px;
        font-size: 15px;
        color: #fff;
        border: 1px solid #e7e7eb;
    }
    .ng-binding {
        font-size: 15px;
        line-height: 30px;
    }

    .btn-white{
        line-height: 25px;
    }
    .btn-white:hover{
        border: 1px solid #079200;
        background-color: #fff;
        color: #079200;
    }
    .phone-foot .btn {
        margin-bottom: 0;
        border-radius: 0;
    }

    .phone-foot{
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        border-top: 1px solid #e7e7eb;
        background: url(/resource/backend/img/bg-mobile-foot-default.png);
        background-position:0 0;
        background-repeat: no-repeat;
        padding-left: 43px;
        margin-bottom: 0;
    }

    .custommenu_sub_container .btn {
        font-size: 14px;
    }

    .flex-col .btn {
        background: #FAFAFA;;
    }
</style>

<div id="vueArea" class="wrapper wrapper-content animated fadeInRight">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row  col-sm-offset-2">
    	<!-- 菜单编辑模式 -->
        <div class="col-sm-3" style="width: 361px">
            <div class="ibox float-e-margins">
                <div class="phone-header">
                    <span class="ng-binding">自定义菜单</span>
                </div>
                <div class="flex-row flex-vDirection menuView">
                    <div class="flex-col"></div>
                    <div>
                        <draggable v-model="list" :options="{group:'mainMenu'}" class="flex-row phone-foot">
                            <div v-for="(item,index) in list" class="flex-col custommenu">
                                <div class="custommenu_sub_container">
                                    <draggable v-model="item.sub" :options="{group:'subMenu' + index}">
                                        <div v-for="sub in item.sub">
                                            <a class="btn btn-block btn-white" :class="{active:crtItem === sub}" @click="crtItem = sub">{{sub.name}}</a>
                                        </div>
                                    </draggable>
                                    <div v-show="item.sub.length < maxSubItemCount"><a class="btn btn-block btn-white" @click="addSubItem(item.sub)"><i class="fa fa-plus"></i></a></div>
                                </div>
                                <a class="btn btn-block btn-white" :class="{active:crtItem === item}" @click="crtItem = item">{{item.name}}</a>
                            </div>
                            <div class="flex-col" v-show="list.length < maxItemCount"><a class="btn btn-block btn-white" @click="addItem"><i class="fa fa-plus"></i> 添加菜单</a></div>
                        </draggable>
                    </div>
                </div>
                <div class="form-group" style="padding-top: 30px;">
                    <div class="hAlignCenter">
                        <a class="btn btn-primary" @click="submitForm">保存</a>
                        <a class="btn btn-white" @click="back">返回</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>菜单标题</h5>
                </div>
                <div class="ibox-content clearfix">
                    <div class="col-sm-12">
                        <?= $form->field($model, 'title')->textInput()->hint('给菜单起个名字吧！以便查找') ?>
                    </div>　
                </div>
            </div>
        </div>
        <!--
        	added by wzq 自定义菜单操作区
        -->
        <div class="col-sm-6" v-if="crtItem">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>菜单设置</h5>
                    <a @click="deleteCrtItem" class="pull-right">删除菜单</a>
                </div>
                <div class="ibox-content clearfix">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="control-label">菜单名称</label>
                            <input class="form-control" name="CustomMenu[title]" v-model="crtItem.name" aria-required="true" type="text">
                            <div class="help-block"></div>
                        </div>
                        <div class="form-group" v-show="!hasSubItem(crtItem)">
                            <label class="control-label">菜单动作</label>
                            <div class="row">
                                <div class="col-sm-12">
                                        <?php foreach ($menuTypes as $key => $menuType){ ?>
                                            <label>
                                                <input type="radio" value="<?= $key ?>" name="ipt" v-model="crtItem.type"> <i></i> <?= $menuType['name'] ?>
                                            </label>
                                        <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group" v-show="!hasSubItem(crtItem) && needContent(crtItem)">
                            <hr>
                            <input class="form-control" name="value" value="" aria-required="true" type="text" v-model="crtItem.content">
                        </div>
                    </div>　
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<script>
    $(function(){

        var list = '<?php echo json_encode(unserialize($model->data)) ?>';
        list = JSON.parse(list);

        for (var i=0; i<list.length; i++){
            var item = list[i];
            if(!item.sub)item.sub = [];
        }

        console.log(list);
        var vueArea = new Vue({
            data:{
                list: list ? list : [],
                maxItemCount: 3,
                maxSubItemCount: 5,
                crtItem: null,
                isSortMode: false
            },
            methods: {
                addItem: function(){
                    var newOne = {name: '菜单名称', sub:[], type:'click',content:''};
                    this.list.push(newOne);
                    this.crtItem = newOne;
                },
                addSubItem: function(subList){
                    var newOne = {name:'子菜单名称', type:'click',content:''};
                    subList.push(newOne);
                    this.crtItem = newOne;
                },
                deleteCrtItem: function(){
                	var self = this;

                	function doDelete(){
                        var itemIndex;
                        for (var i = 0; i < self.list.length; i++) {
                            var item = self.list[i];
                            if(item == self.crtItem)
                            {
                                self.list.splice(i, 1);
                                self.crtItem = null;
                                return;
                            }
                            itemIndex = item.sub.indexOf(self.crtItem);
                            if (itemIndex >= 0) {
                                item.sub.splice(itemIndex, 1);
                                self.crtItem = null;
                                return;
                            }
                        }
                    }

                	if(self.crtItem.sub){
                        appConfirm("您确定要删除这个菜单吗？", "删除后将无法恢复，请谨慎操作！", doDelete)
                    }else{
                        doDelete();
                    }
                },
                submitForm: function(){
                    //检查子菜单类别是否都填了
                    var self = this;
                    function checkValidate(item){
                        var needContent = self.needContent(item);
                        if(needContent && !item.content)
                        {
                            swalAlert('请填写"'+item.name+'"的' + needContent);
                            self.crtItem = item;
                            return false;
                        }
	                    if(item.type == 'view' && !new RegExp('^(http|https|ftp)\://([a-zA-Z0-9\.\-]+(\:[a-zA-Z0-9\.&%\$\-]+)*@)*((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])|localhost|([a-zA-Z0-9\-]+\.)*[a-zA-Z0-9\-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(\:[0-9]+)*(/($|[a-zA-Z0-9\.\,\?\'\\\+&%\$#\=~_\-]+))*$').test(item.content))
			    		{
                            swalAlert('您填写的链接地址格式不正确');
			            	self.crtItem = item;
			            	return false;
			    		}
                        return true;
                    }
                    for(var i = 0; i < this.list.length; i++) {
                        var item = this.list[i];
                        if(this.hasSubItem(item))
                        {
                            for(var j = 0; j < item.sub.length; j++)
                            {
                                var subItem = item.sub[j];
                                if(!checkValidate(subItem))
                                {
                                    return;
                                }
                            }
                        }
                        else
                        {
                            if(!checkValidate(item))
                            {
                                return;
                            }
                        }
                    }

                    var prevent = true;
                    if(prevent){
                        prevent = false;
                        var id = '<?php echo $model->id ?>';
                        var title = $('#custommenu-title').val();
                        $.ajax({
                            type:"post",
                            url:"<?= Url::to(['edit'])?>",
                            dataType: "json",
                            data: {id:id,list:this.list,title:title},
                            success: function(data){
                                prevent = true;
                                if(data.flg == 2) {
                                    alert(data.msg);
                                }else{
                                    window.location = '<?= Url::to(['index']) ?>';
                                }
                            }
                        });
                    }
                },
                back: function(){
                    window.history.go(-1);
                },
                hasSubItem: function(item){
                    return item.sub && item.sub.length > 0;
                },
                needContent: function(item){
                    var dic = {click: '触发关键字', view: '跳转链接'}
                    return dic[item.type];
                }
            }
        }).$mount('#vueArea');
    })
</script>

