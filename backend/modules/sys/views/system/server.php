<?php
use yii\helpers\Url;
use yii\helpers\Html;

$this->title = '服务器信息';
$this->params['breadcrumbs'][] = ['label' => '系统', 'url' => ['/sys/system/index']];
$this->params['breadcrumbs'][] = ['label' =>  $this->title];
?>

<?= Html::jsFile('/resource/backend/js/plugins/echarts/echarts-all.js')?>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="ibox float-e-margins">
        <div class="ibox-title">
            <h5><i class="fa fa-cog"></i>  服务器参数</h5>
        </div>
        <div class="ibox-content">
            <table class="table">
                <tr>
                    <td>服务器域名地址</td>
                    <td><?= $info['server']['domainIP'] ?></td>
                    <td>服务器标识</td>
                    <td><?= $info['server']['flag'] ?></td>
                </tr>
                <tr>
                    <td>操作系统</td>
                    <td><?= $info['server']['os'] ?></td>
                    <td>服务器解析引擎</td>
                    <td><?= $info['server']['webEngine'] ?></td>
                </tr>
                <tr>
                    <td>服务器语言</td>
                    <td><?= $info['server']['language'] ?></td>
                    <td>服务器端口</td>
                    <td><?= $info['server']['webPort'] ?></td>
                </tr>
                <tr>
                    <td>服务器主机名</td>
                    <td><?= $info['server']['name'] ?></td>
                    <td>站点绝对路径</td>
                    <td><?= $info['server']['webPath'] ?></td>
                </tr>
                <tr>
                    <td>服务器当前时间</td>
                    <td><span id="divTime"></span></td>
                    <td>服务器已运行时间</td>
                    <td><?= $info['sysInfo']['uptime'] ?></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="ibox float-e-margins">
        <div class="ibox-title">
            <h5><i class="fa fa-cog"></i>  服务器硬件数据</h5>
        </div>
        <div class="ibox-content" id="sys-hardware">
            <table class="table">
                <tr>
                    <td>CPU</td>
                    <td><?= $info['sysInfo']['cpu']['num'] ?></td>
                    <td>CPU型号</td>
                    <td><?= $info['sysInfo']['cpu']['model'] ?></td>
                </tr>
                <tr>
                    <td>CPU使用情况</td>
                    <td colspan="3">
                        <?= $info['cpuUse'] ?>
                    </td>
                </tr>
                <tr>
                    <td>硬盘使用情况</td>
                    <td colspan="3">
                        总空间：<?= $info['hd']['t'] ?>G　
                        已使用：<?= $info['hd']['u'] ?>G　
                        使用率：<?= $info['hd']['PCT'] ?>%
                        <div class="progress progress-striped active">
                            <div style="width: <?= $info['hd']['PCT'] ?>%" class="progress-bar progress-bar-danger"></div>
                            <div style="width: <?= 100 - $info['hd']['PCT'] ?>%" class="progress-bar progress-bar-success"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>内存使用情况</td>
                    <td colspan="3">
                        物理内存共：<?= $info['sysInfo']['memTotal'] ?>　
                        已使用：<?= $info['sysInfo']['memUsed'] ?>　
                        空闲：<?= $info['sysInfo']['memFree'] ?>　
                        使用率：<?= $info['sysInfo']['memPercent'] ?>%
                        <div class="progress progress-striped active">
                            <div style="width: <?= $info['sysInfo']['memPercent'] ?>%" class="progress-bar progress-bar-danger"></div>
                            <div style="width: <?= 100 - $info['sysInfo']['memPercent'] ?>%" class="progress-bar progress-bar-success"></div>
                        </div>
                        <br>
                        Cache化内存为：<?= $info['sysInfo']['memCached'] ?>　
                        使用率：<?= $info['sysInfo']['memCachedPercent'] ?>%　
                        |　Buffers缓冲为：<?= $info['sysInfo']['memBuffers'] ?>　
                        <div class="progress progress-striped active">
                            <div style="width: <?= $info['sysInfo']['memCachedPercent'] ?>%" class="progress-bar progress-bar-danger"></div>
                            <div style="width: <?= 100 - $info['sysInfo']['memCachedPercent'] ?>%" class="progress-bar progress-bar-success"></div>
                        </div>
                        <br>
                        真实内存使用：<?= $info['sysInfo']['memRealUsed'] ?>　
                        真实内存空闲：<?= $info['sysInfo']['memRealFree'] ?>　
                        使用率：<?= $info['sysInfo']['memRealPercent'] ?>
                        <div class="progress progress-striped active">
                            <div style="width: <?= $info['sysInfo']['memRealPercent'] ?>%" class="progress-bar progress-bar-danger"></div>
                            <div style="width: <?= 100 - $info['sysInfo']['memRealPercent'] ?>%" class="progress-bar progress-bar-success"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>系统平均负载</td>
                    <td colspan="2"><?= $info['sysInfo']['loadAvg'] ?></td>
                    <td>负载说明：一分钟、五分钟、以及十五分钟的系统负载均值</td>
                </tr>
            </table>
        </div>
    </div>
    <div class="ibox float-e-margins">
        <div class="ibox-title">
            <h5><i class="fa fa-cog"></i>  服务器实时网络数据</h5>
        </div>
        <div class="ibox-content">
            <table class="table">
                <tr>
                    <td>总发送</td>
                    <td id="netWork_allOutSpeed"><?= $info['netWork']['allOutSpeed'] ?></td>
                    <td>总接收</td>
                    <td id="netWork_allInputSpeed"><?= $info['netWork']['allInputSpeed'] ?></td>
                </tr>
                <tr>
                    <td>发送速度</td>
                    <td id="netWork_currentOutSpeed"><?= $info['netWork']['currentOutSpeed'] ?> KB/s</td>
                    <td>接收速度</td>
                    <td id="netWork_currentInputSpeed"><?= $info['netWork']['currentInputSpeed'] ?> KB/s</td>
                </tr>
                <tr>
                    <td colspan="4">
                        <div id="main" style="width: 100%;height:400px;"></div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<script type="text/html" id="model">
    <table class="table">
        <tr>
            <td>CPU</td>
            <td>{{sysInfo.cpu.num}}</td>
            <td>CPU型号</td>
            <td>{{sysInfo.cpu.model}}</td>
        </tr>
        <tr>
            <td>CPU使用情况</td>
            <td colspan="3">
                {{cpuUse}}
            </td>
        </tr>
        <tr>
            <td>硬盘使用情况</td>
            <td colspan="3">
                总空间：{{hd.t}}G　
                已使用：{{hd.u}}G　
                使用率：{{hd.PCT}}%
                <div class="progress progress-striped active">
                    <div style="width: {{hd.PCT}}%" class="progress-bar progress-bar-danger"></div>
                    <div style="width: {{100 - hd.PCT}}%" class="progress-bar progress-bar-success"></div>
                </div>
            </td>
        </tr>
        <tr>
            <td>内存使用情况</td>
            <td colspan="3">
                物理内存共：{{sysInfo.memTotal}}　
                已使用：{{sysInfo.memUsed}}　
                空闲：{{sysInfo.memFree}}　
                使用率：{{sysInfo.memPercent}}%
                <div class="progress progress-striped active">
                    <div style="width: {{sysInfo.memPercent}}%" class="progress-bar progress-bar-danger"></div>
                    <div style="width: {{100 - sysInfo.memPercent}}%" class="progress-bar progress-bar-success"></div>
                </div>
                <br>
                Cache化内存为：{{sysInfo.memCached}}　
                使用率：{{sysInfo.memCachedPercent}}%　
                |　Buffers缓冲为：{{sysInfo.memBuffers}}　
                <div class="progress progress-striped active">
                    <div style="width: {{sysInfo.memCachedPercent}}%" class="progress-bar progress-bar-danger"></div>
                    <div style="width: {{100 - sysInfo.memCachedPercent}}%" class="progress-bar progress-bar-success"></div>
                </div>
                <br>
                真实内存使用：{{sysInfo.memRealUsed}}　
                真实内存空闲：{{sysInfo.memRealFree}}　
                使用率：{{sysInfo.memRealPercent}}
                <div class="progress progress-striped active">
                    <div style="width: {{sysInfo.memRealPercent}}%" class="progress-bar progress-bar-danger"></div>
                    <div style="width: {{100 - sysInfo.memRealPercent}}%" class="progress-bar progress-bar-success"></div>
                </div>
            </td>
        </tr>
        <tr>
            <td>系统平均负载</td>
            <td colspan="2">{{sysInfo.loadAvg}}</td>
            <td>负载说明：一分钟、五分钟、以及十五分钟的系统负载均值</td>
        </tr>
    </table>
</script>

<script type="text/javascript">
    var myChart = echarts.init(document.getElementById('main'));
    var currentOutSpeed = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
    var currentInputSpeed = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
    var chartTime = <?= json_encode($info['chartTime'])?>;

    function chartOption() {
        var option = {
            title : {
                subtext: '单位 KB/s'
            },
            tooltip : {
                trigger: 'axis'
            },
            legend: {
                data:['发送速度','接收速度']
            },
            toolbox: {
                show : false,
                feature : {
                    mark : {show: true},
                    dataView : {show: true, readOnly: false},
                    magicType : {show: true, type: ['line', 'bar', 'stack', 'tiled']},
                    restore : {show: true},
                    saveAsImage : {show: true}
                }
            },
            calculable : true,
            xAxis : [
                {
                    type : 'category',
                    boundaryGap : false,
                    data : chartTime
                }
            ],
            yAxis : [
                {
                    type : 'value'
                }
            ],
            series : [
                {
                    name:'发送速度',
                    type:'line',
                    smooth:true,
                    itemStyle: {normal: {areaStyle: {type: 'default'}}},
                    data:currentOutSpeed
                },
                {
                    name:'接收速度',
                    type:'line',
                    smooth:true,
                    itemStyle: {normal: {areaStyle: {type: 'default'}}},
                    data:currentInputSpeed
                }
            ]
        };

        return option;
    }

    myChart.setOption(chartOption()); // 加载图表

    $(document).ready(function(){
        setTime();
        setInterval(setTime, 1000);
        setInterval(getServerInfo, 3000);

    });

    function setTime(){
        var d = new Date(), str = '';
        str += d.getFullYear() + ' 年 '; //获取当前年份
        str += d.getMonth() + 1 + ' 月 '; //获取当前月份（0——11）
        str += d.getDate() + ' 日  ';
        str += d.getHours() + ' 时 ';
        str += d.getMinutes() + ' 分 ';
        str += d.getSeconds() + ' 秒 ';
        $("#divTime").text(str);
    }

    function getServerInfo(){
        $.ajax({
            type : "get",
            url  : "<?= Url::to(['server'])?>",
            dataType : "json",
            data: {},
            success: function(data){
                if(data.code == 200) {
                    var html = template('model',data.data);
                    $('#sys-hardware').html(html);

                    var netWork = data.data.netWork;
                    $('#netWork_allOutSpeed').text(netWork.allOutSpeed);
                    $('#netWork_allInputSpeed').text(netWork.allInputSpeed);
                    $('#netWork_currentOutSpeed').text(netWork.currentOutSpeed + ' KB/s');
                    $('#netWork_currentInputSpeed').text(netWork.currentInputSpeed + ' KB/s');

                    currentOutSpeed.shift();
                    currentInputSpeed.shift();
                    currentOutSpeed.push(netWork.currentOutSpeed);
                    currentInputSpeed.push(netWork.currentInputSpeed);
                    chartTime = data.data.chartTime;
                    myChart.setOption(chartOption()); // 加载图表
                }else{
                    alert(data.msg);
                }
            }
        });
    }
</script>
