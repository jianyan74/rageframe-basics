<?php
namespace jianyan\basics\common\interfaces;

/**
 * 微信消息处理接口类
 *
 * Interface WxMsgInterface
 * @package jianyan\basics\common\interfaces
 */
interface WxMsgInterface
{
    /**
     * 微信发来的文字消息格式为：
     *
     * array(
     *     'ToUserName' => 'gh_test',
     *     'FromUserName' => 'oXtOes8fAzWA4cIhnNB4C5ORQFOs',
     *     'CreateTime' => '1509085635',
     *     'MsgType' => 'text',
     *     'Content' => '北京天气',
     *     'MsgId' => '6481473449631879587',
     * )
     * 其他的自行打印
     *
     * @param array $message
     * @return mixed
     */
    public function run($message);
}