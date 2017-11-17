<?php
namespace jianyan\basics\backend\modules\sys\controllers;

use yii;
use yii\caching\FileCache;
use backend\controllers\MController;

/**
 * 缓存清理控制器
 *
 * Class CacheController
 * @package jianyan\basics\backend\modules\sys\controllers
 */
class CacheController extends MController
{
    /**
     * 缓存清理控制器
     *
     * @return string
     */
    public function actionClear()
    {
        // 删除后台文件缓存
        Yii::$app->cache->flush();

        // 清理前台文件缓存
        $frontend_cache_path = Yii::getAlias('@frontend') . '/runtime/cache';
        if (is_dir($frontend_cache_path))
        {
            $cache = new FileCache();
            $cache->cachePath = $frontend_cache_path;
            $cache->gc(true, false);
        }

        // 清理微信文件缓存
        $wechat_cache_path = Yii::getAlias('@wechat') . '/runtime/cache';
        if (is_dir($wechat_cache_path))
        {
            $cache = new FileCache();
            $cache->cachePath = $wechat_cache_path;
            $cache->gc(true, false);
        }

        // 删除备份缓存
        $path = Yii::$app->params['dataBackupPath'];
        $lock = realpath($path) . DIRECTORY_SEPARATOR . Yii::$app->params['dataBackLock'];
        array_map("unlink", glob($lock));

        return $this->render('clear',[
        ]);
    }
}