<?php
namespace jianyan\basics\backend\modules\sys\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use jianyan\basics\backend\modules\sys\models\Menu;
use jianyan\basics\common\library\ServerInfo;
use backend\controllers\MController;
use common\helpers\FileHelper;
use common\enums\StatusEnum;

/**
 * 系统菜单控制器
 *
 * Class SystemController
 * @package jianyan\basics\backend\modules\sys\controllers
 */
class SystemController extends MController
{
    /**
     * 主体框架
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'models' => Menu::getMenus(Menu::TYPE_SYS, StatusEnum::ENABLED),
        ]);
    }

    /**
     * 系统信息
     */
    public function actionInfo()
    {
        $db = Yii::$app->db;
        $models = $db->createCommand('SHOW TABLE STATUS')->queryAll();
        $models = array_map('array_change_key_case', $models);

        // 数据库大小
        $mysql_size = 0;
        foreach ($models as $model)
        {
            $mysql_size += $model['data_length'];
        }

        // 禁用函数
        $disable_functions = ini_get('disable_functions');
        $disable_functions = !empty($disable_functions) ? $disable_functions : '未禁用';

        // 附件大小
        $attachment_size = FileHelper::getDirSize(Yii::getAlias('@attachment'));
        return $this->render('info', [
            'models' => Menu::getMenus(Menu::TYPE_SYS, StatusEnum::ENABLED),
            'mysql_size' => $mysql_size,
            'attachment_size' => !empty($attachment_size) ? $attachment_size : 0,
            'disable_functions' => $disable_functions,
        ]);
    }

    /**
     * 服务器信息
     *
     * @return string
     */
    public function actionServer()
    {
        $server_info = new ServerInfo();
        $info = ArrayHelper::toArray($server_info);
        empty($info['sysInfo']['cpu']['model']) && $info['sysInfo']['cpu']['model'] = '目前只支持Linux系统';
        $info['sysInfo']['cpu']['num'] = !empty($info['sysInfo']['cpu']['num']) ? $info['sysInfo']['cpu']['num'] . ' 核心': '目前只支持Linux系统';
        $info['netWork']['allOutSpeed'] = !empty($info['netWork']['allOutSpeed']) ?  Yii::$app->formatter->asShortSize($info['netWork']['allOutSpeed']): '目前只支持Linux系统';
        $info['netWork']['allInputSpeed'] = !empty($info['netWork']['allInputSpeed']) ? Yii::$app->formatter->asShortSize($info['netWork']['allInputSpeed']): '目前只支持Linux系统';
        $info['netWork']['currentOutSpeed'] = !empty($info['netWork']['currentOutSpeed']) ? round($info['netWork']['currentOutSpeed'] / 1024, 2) : 0;
        $info['netWork']['currentInputSpeed'] = !empty($info['netWork']['currentInputSpeed']) ? round($info['netWork']['currentInputSpeed']  / 1024, 2) : 0;

        $num = 3;
        $num_arr = [];
        for ($i = 20; $i >= 1; $i--)
        {
            $num_arr[] = date('H:i:s', time() - $i * $num);
        }

        $info['chartTime'] = $num_arr;
        if(Yii::$app->request->isAjax)
        {
            $result = $this->setResult();
            $result->code = 200;
            $result->message = '获取成功';
            $result->data = $info;

            return $this->getResult();
        }

        return $this->render('server', [
            'info' => $info,
        ]);
    }
}