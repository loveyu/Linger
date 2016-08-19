<?php
/**
 * User: loveyu
 * Date: 2016/8/20
 * Time: 1:26
 */

namespace ULib;

/**
 * 设置一个专有推送信息接口
 * Class Pushover
 * @package ULib
 */
class Pushover{

	/**
	 * 推送一个公共消息到某一私有服务器
	 * @param string $title   消息标题
	 * @param string $content 消息内容
	 * @param int    $delay   延迟
	 * @return int 返回一个标示
	 */
	public static function push($title, $content, $delay = 0){
		$cfg = array_merge(array(), cfg()->get('pushover_beanstalk_cfg'));
		$client = new Beanstalk($cfg);
		$client->connect();
		$id = $client->put(0, $delay, 0, json_encode(array(
			'type' => 'pushover',
			'content' => $content,
			'title' => $title,
		)));
		$client->disconnect();
		return (int)$id;
	}
}