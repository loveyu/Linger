<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-3-28
 * Time: 上午11:15
 * Filename: Message.php
 */

namespace ULib;


use Core\Log;

/**
 * Class Message
 * @package ULib
 */
class Message extends AppException{
	/**
	 * @var \CLib\Sql
	 */
	private $db;

	/**
	 * @var int[] 分页统计信息
	 */
	private $count = [
		'page' => 1,
		'max' => 1,
		'count' => 0,
		'number' => 20,
	];

	/**
	 * 构造函数
	 */
	function __construct(){
		$this->db = db();
	}

	/**
	 * 设置分页信息
	 * @param int $page
	 * @param int $number
	 */
	public function setPager($page, $number = 20){
		$page = intval($page);
		$number = intval($number);
		$this->count['page'] = $page > 0 ? $page : 1;
		$this->count['number'] = $number > 5 ? ($number > 100 ? 100 : $number) : 5;
	}

	/**
	 * 获取统计
	 * @param int $id      用户ID
	 * @param int $flag_id 是否为收信箱或发信箱类型，为1为发信箱，2为收信箱,3为系统信息
	 * @return bool 是否能获取到当前页面
	 */
	private function getCountInfo($id, $flag_id){
		$count = 0;
		switch($flag_id){
			case 1:
				//排除已经删除的信息
				$count = $this->db->count("message", [
					'AND' => [
						'from_users_id' => $id,
						'from_del' => 0
					]
				]);
				break;
			case 2:
				$count = $this->db->count("message", [
					'AND' => [
						'to_users_id' => $id,
						'to_del' => 0
					]
				]);
				break;
			case 3:
				$count = $this->db->count("message", ['from_users_id' => NULL]);
				break;
			default:
				Log::write("\$flag_id=$flag_id is error. On " . __FILE__ . ":" . __LINE__);
				$this->throwMsg(-11);
		}
		$this->count['count'] = $count;
		$this->count['max'] = intval(ceil($count / $this->count['number']));
		if($this->count['page'] > $this->count['max']){
			$this->count['page'] = -1;
			return false;
		}
		return true;
	}

	/**
	 * 获取统计信息
	 * @return \int[]
	 */
	public function getCount(){
		return $this->count;
	}

	/**
	 * 获取系统信息
	 * @return array
	 */
	public function getSystemMessage(){
		if(!$this->getCountInfo(0, 3)){
			return [];
		}
		$rt = $this->db->select("message", ['[><]users' => ['to_users_id' => 'id']], [
			"message.id" => "msg_id",
			"message.msg_title" => "msg_title",
			"message.msg_datetime" => "msg_datetime",
			"message.is_read" => "msg_is_read",
			"message.to_del" => "msg_to_del",
			"message.from_del" => "msg_from_del",
			'users.id' => 'user_id',
			'users.user_name' => 'user_name',
			'users.user_aliases' => 'user_aliases',
			'users.user_email' => 'user_email',
			'users.user_url' => 'user_url',
		], [
			'message.from_users_id' => NULL,
			'LIMIT' => [
				$this->count['number'] * ($this->count['page'] - 1),
				$this->count['number']
			],
			'ORDER' => "message.id DESC"
		]);
		if($rt === false){
			Log::write(_("Get system message error on sql."), Log::SQL);
			return [];
		}
		return $rt;
	}

	/**
	 * 获取发信箱内容
	 * @param int $id
	 * @return array
	 */
	public function getOutbox($id){
		$id = intval($id);
		if(!$this->getCountInfo($id, 1)){
			return [];
		}
		$rt = $this->db->select("message", ['[><]users' => ['to_users_id' => 'id']], [
			"message.id" => "msg_id",
			"message.msg_title" => "msg_title",
			"message.msg_datetime" => "msg_datetime",
			"message.is_read" => "msg_is_read",
			"message.to_del" => "msg_to_del",
			"message.from_del" => "msg_from_del",
			'users.id' => 'user_id',
			'users.user_name' => 'user_name',
			'users.user_aliases' => 'user_aliases',
			'users.user_email' => 'user_email',
			'users.user_url' => 'user_url',
		], [
			'AND' => [
				//查询发送人ID，且发送人未删除
				'message.from_users_id' => $id,
				'message.from_del' => 0,
			],
			'LIMIT' => [
				$this->count['number'] * ($this->count['page'] - 1),
				$this->count['number']
			],
			'ORDER' => "message.id DESC"
		]);
		if($rt === false){
			Log::write(_("Get outbox message error on sql."), Log::SQL);
			return [];
		}
		return $rt;
	}

	public function SystemDelete($id){
		if(!login_user()->Permission('MessageSystem')){
			$this->throwMsg(-2);
		}
		$status = $this->db->delete("message", ['id' => intval($id)]);
		if($status < 1){
			$this->throwMsg(-20);
		}
	}

	public function delete($id, $uid){
		if(login_user()->Permission('MessageDeny')){
			$this->throwMsg(-10);
		}
		$id = intval($id);
		$uid = intval($uid);
		$msg = $this->db->get("message", [
			'id',
			'to_del',
			'from_del',
			'to_users_id',
			'from_users_id',
		], ['id' => $id]);
		if(!isset($msg['id']) || $msg['id'] != $id){
			$this->throwMsg(-12);
		}
		$data = [];
		switch($uid){
			case $msg['to_users_id']:
				if(!$msg['to_del']){
					$data = ['to_del' => 1];
				}
				break;
			case $msg['from_users_id']:
				if(!$msg['from_del']){
					$data = ['from_del' => 1];
				}
				break;
			default:
				$this->throwMsg(-14);
				break;
		}
		if(count($data) > 0){
			$rt = $this->db->update("message", $data, ['id' => $id]);
			if($rt === false){
				Log::write(_("Message set del error."), Log::SQL);
				$this->throwMsg(-15);
			}
		}
	}

	/**
	 * 发送信息
	 * @param string $title
	 * @param string $users
	 * @param string $content
	 * @param int    $send_user
	 * @return array
	 */
	public function send($title, $users, $content, $send_user){
		if(login_user()->Permission('MessageDeny')){
			$this->throwMsg(-10);
		}
		$title = trim($title);
		$users = array_map('trim', preg_split("/[\\s]+/", $users));
		$content = trim($content);
		if(strlen($content) < 1){
			$this->throwMsg(-4);
		}
		$send_user = intval($send_user);
		if($send_user < 0){
			$this->throwMsg(-1);
		} else if($send_user === 0){
			//系统信息
			if(!login_user()->Permission('MessageSystem')){
				$this->throwMsg(-2);
			}
		} else{
			//用户信息
			if(!User::getUser($send_user)->is_active()){
				//未激活不允许发送信息
				$this->throwMsg(-3);
			}
			if(count($users) == 1 && $users[0] == User::getUser($send_user)->getName()){
				$this->throwMsg(-13);
			}
		}
		if(count($users) < 1){
			$this->throwMsg(-6);
		}
		if($send_user > 0){
			$this->checkLastSend($send_user);
		}
		if($send_user > 0){
			$list = $this->checkUser($users, $send_user);
			if(($l = count($list)) === 0){
				$this->throwMsg(-6);
			} else if($l > hook()->apply("Message_send_number", 5)){
				$this->throwMsg(-7);
			}
		} else{
			//系统发信，可以使用ID序列
			if(empty($title)){
				//系统消息标题不能为空
				$this->throwMsg(-19);
			}
			$list_id = $this->db->select("users", ['id'], [
				'OR' => [
					'user_name' => $users,
					'id' => $users
				]
			]);
			$list = [];
			if($list_id === false){
				Log::write(_("select user id list error on message."), Log::SQL);
			}
			foreach($list_id as $v){
				$list[] = $v['id'];
			}
		}
		$send_user !== 0 or $send_user = NULL;
		$time = date("Y-m-d H:i:s");
		$status = [
			'ok' => 0,
			'error' => 0
		];
		foreach($list as $t_id){
			$data = [
				'msg_title' => $title,
				'msg_content' => $content,
				'msg_datetime' => $time,
				'from_users_id' => $send_user,
				'to_users_id' => $t_id
			];
			$data = hook()->apply('Message_send_before', $data);
			if(empty($data)){
				continue;
			}
			$insert = $this->db->insert("message", $data);
			if($insert > 0){
				++$status['ok'];
				//运行成功处理
				hook()->apply('Message_send_success', NULL, $data, $insert);
			} else{
				Log::write(_("Insert message error."), Log::SQL);
				++$status['error'];
			}
		}
		return $status;
	}

	public function getMessageContent($msg_id){
		$content = $this->db->get("message", [
			'id',
			'msg_title',
			'msg_content'
		], ['id' => $msg_id]);
		if(!isset($content['id'])){
			$this->throwMsg(-12);
		}
		lib()->load('Markdown');
		$content['msg_content'] = Markdown::defaultTransform($content['msg_content']);
		return $content;
	}

	/**
	 * 添加一个系统提示信息给用户
	 * @param string $title
	 * @param string $content
	 * @param string $uid
	 */
	public function addNoticeMsg($title, $content, $uid){
		$title = trim($title);
		$content = htmlspecialchars(trim($content), ENT_NOQUOTES);
		$uid = intval($uid);
		if(empty($title) || empty($content) || $uid < 1){
			$this->throwMsg(-17);
		}
		$data = [
			'msg_title' => $title,
			'msg_content' => $content,
			'msg_datetime' => date("Y-m-d H:i:s"),
			'from_users_id' => NULL,
			'to_users_id' => $uid
		];
		if($this->db->insert("message", $data) < 0){
			Log::write(_("Add message notice error."), Log::SQL);
			$this->throwMsg(-18);
		}
	}

	/**
	 * 获取收信箱的内容
	 * @param int $id
	 * @return array
	 */
	public function getInbox($id){
		$id = intval($id);
		if(!$this->getCountInfo($id, 2)){
			return [];
		}
		$rt = $this->db->select("message", ['[>]users' => ['from_users_id' => 'id']], [
			"message.id" => "msg_id",
			"message.msg_title" => "msg_title",
			"message.msg_datetime" => "msg_datetime",
			"message.is_read" => "msg_is_read",
			"message.to_del" => "msg_to_del",
			"message.from_del" => "msg_from_del",
			'users.id' => 'user_id',
			'users.user_name' => 'user_name',
			'users.user_aliases' => 'user_aliases',
			'users.user_email' => 'user_email',
			'users.user_url' => 'user_url',
		], [
			'AND' => [
				//查询发送人ID，且发送人未删除
				'message.to_users_id' => $id,
				'message.to_del' => 0,
			],
			'LIMIT' => [
				$this->count['number'] * ($this->count['page'] - 1),
				$this->count['number']
			],
			'ORDER' => "message.id DESC"
		]);
		if($rt === false){
			Log::write(_("Get inbox message error on sql."), Log::SQL);
			return [];
		}
		return $rt;
	}

	/**
	 * @param int $id
	 * @param int $uid
	 * @return array
	 */
	public function read($id, $uid){
		$id = intval($id);
		$uid = intval($uid);
		$sql = <<<EOM
SELECT `id`, `msg_title`, `msg_datetime`, `msg_content`, `is_read`, `from_users_id`, `to_users_id`
FROM `message`
WHERE
`id` = '{$id}'
AND
(
	(`from_users_id` = '{$uid}' AND `from_del` = 0)
	OR
	(`to_users_id` = '{$uid}' AND `to_del` = 0)
)
LIMIT 1;
EOM;
		$stmt = $this->db->getReader()->query($sql);
		$msg = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		if(isset($msg[0])){
			$msg = $msg[0];
		}
		if(!isset($msg['id']) || $msg['id'] != $id){
			$this->throwMsg(-12);
		}
		if($uid == $msg['to_users_id'] && !$msg['is_read']){
			$msg['is_read'] = 1;
			$msg['read_time'] = date("Y-m-d H:i:s");
			$s = $this->db->update("message", [
				'is_read' => 1,
				'read_time' => $msg['read_time']
			], ['id' => $id]);
			if($s === false){
				Log::write(_("Set "));
			}
		}
		lib()->load('Markdown');
		$msg['msg_content'] = Markdown::defaultTransform($msg['msg_content']);
		return $msg;
	}

	public function set_read($id, $uid){
		$data = [
			'is_read' => 1,
			'read_time' => date("Y-m-d H:i:s")
		];
		$rt = $this->db->update("message", $data, [
			'AND' => [
				'id' => $id,
				'to_users_id' => $uid,
				'is_read' => 0
			]
		]);
		if($rt < 1){
			$this->throwMsg(-16);
		}
	}

	/**
	 * 检测用户，根据字符串数组
	 * @param string[] $list
	 * @param int      $u_id
	 * @return int[]
	 */
	private function checkUser($list, $u_id){
		$list = array_flip(array_flip($list));
		if(count($list) > 10){
			$this->throwMsg(-5);
		}
		$ss = $this->db->select("users", ['id'], ['user_name' => $list]);
		$list = [];
		foreach($ss as $v){
			$list[] = intval($v['id']);
		}
		if(count($list) < 1){
			return [];
		}
		if($u_id !== 0){
			$ss = implode(",", $list);
			$sql = <<<EOM
SELECT `users_id`, `follow_users_id` FROM `users_follow_users` WHERE
(`users_id` = {$u_id} AND `follow_users_id` in ({$ss}))
OR
(`users_id` in ({$ss}) AND `follow_users_id` = {$u_id});
EOM;
			$stmt = $this->db->getReader()->query($sql);
			if($stmt === false){
				Log::write(_("Get sql message users error."), Log::SQL);
				$this->throwMsg(-8);
			}
			$ss = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			unset($stmt);
			$list = [];
			foreach($ss as $v){
				if(!isset($list[$v['users_id']])){
					$list[$v['users_id']] = intval($v['users_id']);
				}
				if(!isset($list[$v['follow_users_id']])){
					$list[$v['follow_users_id']] = intval($v['follow_users_id']);
				}
			}
			if(isset($list[$u_id])){
				unset($list[$u_id]);
			}
		}
		return $list;
	}

	/**
	 * 检查上次发送时间间隔，过短则返回异常
	 * @param $uid
	 */
	private function checkLastSend($uid){
		$get = $this->db->get("message", ['msg_datetime'], [
			'from_users_id' => $uid,
			'ORDER' => 'id DESC'
		]);
		if(isset($get['msg_datetime'])){
			$t = strtotime($get['msg_datetime']);
			if((time() - $t) < hook()->apply("Message_checkLastSend", 30)){
				$this->throwMsg(-9);
			}
		}
	}

	/**
	 * 获取异常信息，实现抽象函数
	 * @param int $code
	 * @return string
	 */
	public function getMsg($code){
		$code = intval($code);
		switch($code){
			case -1:
				return _("The sender id error!");
			case -2:
				return _("You have no permission to send system message.");
			case -3:
				return _("Your must activation you account.");
			case -4:
				return _("The message content is empty.");
			case -5:
				return _("You post users list is too big.");
			case -6:
				return _("Does not have a valid user.");
			case -7:
				return _("Too large number of users.");
			case -8:
				return _("Send error on users parse.");
			case -9:
				return _("Your message is sent too fast.");
			case -10:
				return _("You do not have permission to perform this operation.");
			case -11:
				return _("Get pager count error.");
			case -12:
				return _("This message was not found.");
			case -13:
				return _("You can't send message to yourself.");
			case -14:
				return _("Message does not belong to you.");
			case -15:
				return _("Delete message error, please try later.");
			case -16:
				return _("Flag message to read is error, maybe is already read or not found.");
			case -17:
				return _("Add notice error, data is verify error.");
			case -18:
				return _("Add message notice error.");
			case -19:
				return _("System message title can not be empty.");
			case -20:
				return _("System does not delete any message data.");
		}
		return _("Unknown error.");
	}

} 