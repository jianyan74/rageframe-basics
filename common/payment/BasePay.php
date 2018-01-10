<?php
namespace jianyan\basics\common\payment;

use Yii;

class BasePay
{
    public $_rfConfig;

    /**
     * BasePay constructor.
     */
	public function __construct()
    {
        $this->_rfConfig = Yii::$app->config->infoAll();
    }
}
