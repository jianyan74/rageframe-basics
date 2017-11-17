<?php
namespace jianyan\basics\backend\modules\sys\controllers;

use Yii;
use yii\web\Response;
use jianyan\basics\common\models\sys\Database;
use backend\controllers\MController;

/**
 * 数据库备份还原控制器
 *
 * Class DataBaseController
 * @package jianyan\basics\backend\modules\sys\controllers
 */
class DataBaseController extends MController
{
    protected $path;
    protected $config;

    public function init()
    {
        $path   = Yii::$app->params['dataBackupPath'];
        // 读取备份配置
        $config = [
            'path'     => realpath($path) . DIRECTORY_SEPARATOR,
            'part'     => Yii::$app->params['dataBackPartSize'],
            'compress' => Yii::$app->params['dataBackCompress'],
            'level'    => Yii::$app->params['dataBackCompressLevel'],
            'lock'     => Yii::$app->params['dataBackLock'],
        ];
        $this->path     = $path;
        $this->config   = $config;

        // 判断目测是否存在，不存在则创建
        if(!is_dir($path))
        {
            mkdir($path, 0755, true);
        }
    }

    /**
     * 备份列表
     */
    public function actionBackups()
    {
        $db      = Yii::$app->db;
        $models  = $db->createCommand('SHOW TABLE STATUS')->queryAll();
        $models  = array_map('array_change_key_case', $models);

        return $this->render('backups', [
            'models' => $models
        ]);
    }

    /**
     * 备份检测
     *
     * @return array
     */
    public function actionExport()
    {
        $result = $this->setResult();

        Yii::$app->response->format = Response::FORMAT_JSON;
        $tables = Yii::$app->request->post('tables');
        if(empty($tables))
        {
            $result->message = "请选择要备份的表！";
            return $this->getResult();
        }

        // 读取备份配置
        $config = $this->config;

        // 检查是否有正在执行的任务
        $lock = "{$config['path']}".$config['lock'];

        if(is_file($lock))
        {
            $result->message = "检测到有一个备份任务正在执行，请稍后或清理缓存后再试！";
            return $this->getResult();
        }
        else
        {
            // 创建锁文件
            file_put_contents($lock, time());
        }

        // 检查备份目录是否可写
        if (!is_writeable($config['path']))
        {
            $result->message = "备份目录不存在或不可写，请检查后重试！";
            return $this->getResult();
        }

        // 生成备份文件信息
        $file = [
            'name' => date('Ymd-His', time()),
            'part' => 1,
        ];

        $result->message =  "初始化失败，备份文件创建失败！";

        // 创建备份文件
        $Database = new Database($file, $config);
        if(false !== $Database->create())
        {
            // 缓存配置信息
            Yii::$app->session->set('backup_config', $config);
            // 缓存文件信息
            Yii::$app->session->set('backup_file', $file);
            // 缓存要备份的表
            Yii::$app->session->set('backup_tables', $tables);

            $tab = ['id' => 0, 'start' => 0];

            $result->code = 200;
            $result->message = "初始化成功！";
            $result->data = [
                'tables'    => $tables,
                'tab'       => $tab
            ];

            return $this->getResult();
        }

        return $this->getResult();
    }

    /**
     * 开始备份
     * @return array
     */
    public function actionExportStart()
    {
        $result = $this->setResult();

        $tables = Yii::$app->session->get('backup_tables');
        $file   = Yii::$app->session->get('backup_file');
        $config = Yii::$app->session->get('backup_config');

        $id     = Yii::$app->request->post('id');
        $start  = Yii::$app->request->post('start');

        // 备份指定表
        $Database = new Database($file,$config);
        $start    = $Database->backup($tables[$id], $start);
        if($start === false)
        {
            $result->message = "备份出错";
            return $this->getResult();
        }
        elseif ($start === 0)
        {
            // 下一表
            if(isset($tables[++$id]))
            {
                $tab = ['id' => $id, 'start' => 0];

                $result->code = 200;
                $result->message = "备份完成";// 对下一个表进行备份
                $result->data = [
                    'tablename' => $tables[--$id],
                    'achieveStatus' => 0,
                    'tab' => $tab,
                ];

                return $this->getResult();
            }
            else
            {
                // 备份完成，清空缓存
                unlink($config['path'] . $config['lock']);
                Yii::$app->session->set('backup_tables', null);
                Yii::$app->session->set('backup_file', null);
                Yii::$app->session->set('backup_config', null);

                $result->code = 200;
                $result->message = "备份完成";
                $result->data = [
                    'tablename' => $tables[--$id],
                    'achieveStatus' => 1
                ];

                return $this->getResult();
            }
        }
        else
        {
            $tab  = ['id' => $id, 'start' => $start[0]];
            $rate = floor(100 * ($start[0] / $start[1]));

            $result->code = 200;
            $result->message = "正在备份...({$rate}%)";// 对下一个表进行备份
            $result->data = [
                'tablename' => $tables[$id],
                'achieveStatus' => 0,
                'tab' => $tab,
            ];

            return $this->getResult();
        }
    }

    /**
     * 优化表
     *
     * @param String $tables 表名
     */
    public function actionOptimize()
    {
        $result = $this->setResult();

        $tables  = Yii::$app->request->post('tables','');
        $result->message = "请指定要优化的表";
        if($tables)
        {
            $Db      = \Yii::$app->db;
            // 判断是否是数组
            if(is_array($tables))
            {
                $tables = implode('`,`', $tables);
                $list = $Db->createCommand("OPTIMIZE TABLE `{$tables}`")->queryAll();

                $result->message = "数据表优化出错请重试";
                if($list)
                {
                    $result->code = 200;
                    $result->message = "数据表优化完成";
                }

                return $this->getResult();
            }
            else
            {
                $list = $Db->createCommand("REPAIR TABLE `{$tables}`")->queryOne();

                // 判断是否成功
                $result->message = "数据表'{$tables}'优化出错！错误信息:". $list['Msg_text'];
                if($list['Msg_text'] == "OK")
                {
                    $result->code = 200;
                    $result->message = "数据表'{$tables}'优化完成！";
                }
            }
        }

        return $this->getResult();
    }

    /**
     * 修复表
     *
     * @param String $tables 表名
     */
    public function actionRepair()
    {
        $result = $this->setResult();
        $tables  = Yii::$app->request->post('tables','');

        $result->message = "请指定要修复的表";
        if($tables)
        {
            $Db      = \Yii::$app->db;
            // 判断是否是数组
            if(is_array($tables))
            {
                $tables = implode('`,`', $tables);
                $result->message = "数据表修复出错请重试";
                if($list = $Db->createCommand("REPAIR TABLE `{$tables}`")->queryAll())
                {
                    $result->code = 200;
                    $result->message = "数据表修复化完成";
                }
            }
            else
            {
                $list = $Db->createCommand("REPAIR TABLE `{$tables}`")->queryOne();
                $result->message = "数据表'{$tables}'修复出错！错误信息:". $list['Msg_text'];
                if($list['Msg_text'] == "OK")
                {
                    $result->code = 200;
                    $result->message = "数据表'{$tables}'修复完成！";
                }
            }
        }

        return $this->getResult();
    }

    /********************************************************************************/
    /************************************还原数据库************************************/
    /********************************************************************************/

    /**
     * 还原列表
     */
    public function actionRestore()
    {
        Yii::$app->language = "";

        // 文件夹路径
        $path    = $this->path;
        $flag    = \FilesystemIterator::KEY_AS_FILENAME;
        $glob    = new \FilesystemIterator($path,  $flag);

        $list = [];
        foreach ($glob as $name => $file)
        {
            // 正则匹配文件名
            if(preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql(?:\.gz)?$/', $name))
            {
                $name = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d');

                $date = "{$name[0]}-{$name[1]}-{$name[2]}";
                $time = "{$name[3]}:{$name[4]}:{$name[5]}";
                $part = $name[6];

                if(isset($list["{$date} {$time}"]))
                {
                    $info = $list["{$date} {$time}"];
                    $info['part'] = max($info['part'], $part);
                    $info['size'] = $info['size'] + $file->getSize();
                }
                else
                {
                    $info['part'] = $part;
                    $info['size'] = $file->getSize();
                }

                $extension        = strtoupper(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
                $info['compress'] = ($extension === 'SQL') ? '-' : $extension;
                $info['time']     = strtotime("{$date} {$time}");
                $info['filename'] = $file->getBasename();
                $list["{$date} {$time}"] = $info;
            }
        }

        krsort($list);

        return $this->render('restore', [
            'list' => $list
        ]);
    }

    /**
     * 初始化还原
     */
    public function actionRestoreInit()
    {
        $result = $this->setResult();

        $time = Yii::$app->request->post('time');

        $config = $this->config;
        // 获取备份文件信息
        $name  = date('Ymd-His', $time) . '-*.sql*';
        $path  = realpath($config['path']) . DIRECTORY_SEPARATOR . $name;
        $files = glob($path);

        $list = [];
        $size = 0;
        foreach($files as $name => $file)
        {
            $size     += filesize($file);
            $basename = basename($file);
            $match    = sscanf($basename, '%4s%2s%2s-%2s%2s%2s-%d');
            $gz       = preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql.gz$/', $basename);
            $list[$match[6]] = array($match[6], $file, $gz);
        }
        // 排序数组
        ksort($list);

        $result->message = "备份文件可能已经损坏，请检查！";

        // 检测文件正确性
        $last = end($list);
        if(count($list) === $last[0])
        {
            Yii::$app->session->set('backup_list', $list); // 缓存备份列表

            $result->code = 200;
            $result->message = "初始化完成";
            $result->data = [
                'part' => 1,
                'start' => 0,
            ];
        }

        return $this->getResult();
    }

    /**
     * 开始还原到数据库
     */
    public function actionRestoreStart()
    {
        set_time_limit(0);
        $result = $this->setResult();

        $config = $this->config;
        $part  = Yii::$app->request->post('part');
        $start = Yii::$app->request->post('start');

        $list  = Yii::$app->session->get('backup_list');
        $arr   = [
            'path'     => realpath($config['path']).DIRECTORY_SEPARATOR,
            'compress' => $list[$part][2]
        ];
        $db    = new Database($list[$part],$arr);
        $start = $db->import($start);

        if (false === $start)
        {
            $result->message = "备份文件可能已经损坏，请检查！";
            return $this->getResult();
        }
        elseif(0 === $start)
        {
            // 下一卷
            if(isset($list[++$part]))
            {
                $result->code = 200;
                $result->message   = "正在还原...#{$part}";
                $result->data = [
                    'part' => $part,
                    'start1' => $start,
                    'start' => 0,
                    'achieveStatus' => 0,
                ];

                return $this->getResult();
            }
            else
            {
                Yii::$app->session->set('backup_list', null);
                $result->code = 200;
                $result->message = "还原完成";

                return $this->getResult();
            }
        }
        else
        {
            if($start[1])
            {
                $rate = floor(100 * ($start[0] / $start[1]));
                $result->code = 200;
                $result->message = "正在还原...#{$part} ({$rate}%)";
                $result->data = [
                    'part' => $part,
                    'start' => $start[0],
                    'achieveStatus' => 0,
                ];

                return $this->getResult();
            }
            else
            {
                $result->code = 200;
                $result->message = "正在还原...#{$part}";
                $result->data = [
                    'part' => $part,
                    'start' => $start[0],
                    'gz' => 1,
                    'start1' => $start,
                    'achieveStatus' => 0,
                ];

                return $this->getResult();
            }
        }
    }

    /**
     * 删除文件
     */
    public function actionDelete()
    {
        $config = $this->config;
        $time   = Yii::$app->request->get('time');

        if($time)
        {
            $name  = date('Ymd-His', $time) . '-*.sql*';
            $path  = realpath($config['path']) . DIRECTORY_SEPARATOR . $name;
            array_map("unlink", glob($path));
            if(count(glob($path)))
            {
                return $this->message('文件删除失败，请检查权限!',$this->redirect(['restore']),'error');
            }
            else
            {
                return $this->message('文件删除成功',$this->redirect(['restore']));
            }
        }
        else
        {
            return $this->message('文件删除失败',$this->redirect(['restore']),'error');
        }
    }
}