<?php
use yii\helpers\Url;

$this->title = '系统信息';
$this->params['breadcrumbs'][] = ['label' => '系统', 'url' => ['/sys/system/index']];
$this->params['breadcrumbs'][] = ['label' =>  $this->title];
?>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="col-sm-7">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5><i class="fa fa-cog"></i>  系统信息</h5>
            </div>
            <div class="ibox-content">
                <table class="table table-hover">
                    <tr>
                        <td width="150px">Yii2版本</td>
                        <td><?php echo Yii::getVersion(); ?></td>
                    </tr>
                    <tr>
                        <td>PHP版本</td>
                        <td><?= phpversion(); ?></td>
                    </tr>
                    <tr>
                        <td>Mysql版本</td>
                        <td><?= Yii::$app->db->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION); ?></td>
                    </tr>
                    <tr>
                        <td>Memcache扩展</td>
                        <td>
                            <?= extension_loaded('memcache')
                                ? '<span class="label label-primary">支持</span>'
                                : '<span class="label label-default">不支持</span>'; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Redis扩展</td>
                        <td>
                            <?= extension_loaded('redis')
                                ? '<span class="label label-primary">支持</span>'
                                : '<span class="label label-default">不支持</span>'; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>数据库大小</td>
                        <td><?= Yii::$app->formatter->asShortSize($mysql_size); ?></td>
                    </tr>
                    <tr>
                        <td>运行环境</td>
                        <td><?= $_SERVER['SERVER_SOFTWARE']; ?></td>
                    </tr>
                    <tr>
                        <td>PHP执行方式</td>
                        <td><?= php_sapi_name(); ?></td>
                    </tr>
                    <tr>
                        <td>当前附件目录</td>
                        <td><?= Yii::$app->request->hostInfo . Yii::getAlias('@attachurl'); ?>/</td>
                    </tr>
                    <tr>
                        <td>当前附件目录大小</td>
                        <td><?= Yii::$app->formatter->asShortSize($attachment_size); ?></td>
                    </tr>
                    <tr>
                        <td>文件上传限制</td>
                        <td><?= ini_get('upload_max_filesize'); ?></td>
                    </tr>
                    <tr>
                        <td>超时时间</td>
                        <td><?= ini_get('max_execution_time'); ?>秒</td>
                    </tr>
                    <tr>
                        <td>访问客户端信息</td>
                        <td><?= $_SERVER['HTTP_USER_AGENT'] ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-sm-5">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5><i class="fa fa-code"></i>  开发信息</h5>
            </div>
            <div class="ibox-content">
                <table class="table table-hover">
                    <tr>
                        <td width="150px">系统名称</td>
                        <td><?= Yii::$app->params['exploitSysName']?></td>
                    </tr>
                    <tr>
                        <td>系统版本</td>
                        <td><?= Yii::$app->params['exploitVersions']?></td>
                    </tr>
                    <tr>
                        <td>官网</td>
                        <td><?= Yii::$app->params['exploitOfficialWebsite']?></td>
                    </tr>
                    <tr>
                        <td>Git@OSC</td>
                        <td><?= Yii::$app->params['exploitGit@OSC']?></td>
                    </tr>
                    <tr>
                        <td>GitHub</td>
                        <td><?= Yii::$app->params['exploitGitHub']?></td>
                    </tr>
                    <tr>
                        <td>开发者</td>
                        <td><?= Yii::$app->params['exploitName']?></td>
                    </tr>
                    <tr>
                        <td>官方QQ群</td>
                        <td><a href="https://jq.qq.com/?_wv=1027&amp;k=4BeVA2r" target="_blank">655084090</a></td>
                    </tr>
                    <tr>
                        <td>问题反馈(qq同号)</td>
                        <td>751393839@qq.com</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>





