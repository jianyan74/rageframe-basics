<?php
namespace jianyan\basics\common\helpers;

use yii;
use yii\helpers\Url;

/**
 * 插件Url生成辅助类
 *
 * Class AddonsUrl
 * @package jianyan\basics\common\helpers
 */
class AddonsUrl
{
    /**
     * 生成模块Url
     *
     * @param array $url
     * @param bool $scheme
     * @return bool| string
     */
    public static function to(array $url, $scheme = false)
    {
        return urldecode(Url::to(AddonsHelp::regroupUrl($url, AddonsHelp::THROUNGH_ADDONS_EXECUTE),$scheme));
    }

    /**
     * 通过绝对路径生成模块Url
     *
     * @return string
     */
    public static function toAbsoluteUrl(array $url, $scheme = false)
    {
        return urldecode(Yii::$app->urlManager->createUrl(AddonsHelp::regroupUrl($url, AddonsHelp::THROUNGH_ADDONS_EXECUTE),$scheme));
    }

    /**
     * 生成插件前台Url
     *
     * @return string
     */
    public static function toFront(array $url, $scheme = false)
    {
        return urldecode(Yii::$app->urlManagerFrontend->createAbsoluteUrl(AddonsHelp::regroupUrl($url, AddonsHelp::THROUNGH_ADDONS_EXECUTE), $scheme));
    }

    /**
     * 生成插件微信Url
     *
     * @return string
     */
    public static function toWechat(array $url, $scheme = false)
    {
        return urldecode(Yii::$app->urlManagerWechat->createAbsoluteUrl(AddonsHelp::regroupUrl($url, AddonsHelp::THROUNGH_ADDONS_EXECUTE), $scheme));
    }

    /**
     * 生成插件微信Api Url
     *
     * @return string
     */
    public static function toWechatApi(array $url, $scheme = false)
    {
        return urldecode(Yii::$app->urlManagerWechat->createAbsoluteUrl(AddonsHelp::regroupUrl($url, AddonsHelp::THROUNGH_API_EXECUTE), $scheme));
    }

    /**
     * 生成插件基类跳转链接
     *
     * @return string
     */
    public static function toRoot(array $url, $scheme = false)
    {
        return urldecode(Url::to(AddonsHelp::regroupUrl($url, AddonsHelp::THROUNGH_ADDONS_CENTRE), $scheme));
    }
}
