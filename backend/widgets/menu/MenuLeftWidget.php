<?php
namespace jianyan\basics\backend\widgets\menu;

use yii;
use yii\base\Widget;
use jianyan\basics\common\models\sys\Addons;
use jianyan\basics\backend\modules\sys\models\Menu;

/**
 * Class MainLeftWidget
 * @package backend\widgets\left
 */
class MenuLeftWidget extends Widget
{
    public function run()
    {
        return $this->render('index', [
            'models'=> Menu::getMenus(Menu::TYPE_MENU,Menu::STATUS_ON),
            'plug' => Addons::getPlugList()
        ]);
    }
}

?>