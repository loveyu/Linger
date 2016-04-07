<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-3-28
 * Time: 下午10:37
 * Filename: Notice.php
 */

namespace ULib;

/**
 * Class Notice
 * @package ULib
 */
class Notice extends AppException{
	/**
	 * @var array
	 */
	private $mail_list;
	/**
	 * @var array
	 */
	private $message_list;
	/**
	 * @var array
	 */
	private $option_mail;
	/**
	 * @var array
	 */
	private $option_message;

	/**
	 * @var int 用户ID
	 */
	private $user_id;

	/**
	 * 初始化
	 */
	function __construct($uid){
		$this->user_id = intval($uid);
		$user = User::UserStack($this->user_id);
		if(!is_object($user)){
			$this->throwMsg(-2);
		}
		$this->getMailList();
		$this->getMessageList();
		$this->option_mail = array_fill_keys(array_keys($this->mail_list), false);
		$this->option_message = array_fill_keys(array_keys($this->message_list), true);
		$this->getOption();
	}

	/**
	 * 获取选项值
	 */
	private function getOption(){
		//设置默认值
		//		$this->option_mail['follow_me'] = true;
		$this->option_mail['comment_gallery'] = true;
		$this->option_mail['comment_reply'] = true;
		$this->option_mail['send_message'] = true;
		//		$this->option_mail['send_system_message'] = true;
		$this->option_mail['exception_login'] = true;
		$this->option_mail['login_restrictions'] = true;
		//		$this->option_mail['site_activity_information'] = true;
		$this->option_message['site_activity_information'] = false;
		$meta = @unserialize(User::getUser($this->user_id)->getMeta()->get(['user_notice'], '')['user_notice']);
		if(isset($meta['mail']) && is_array($meta['mail'])){
			$this->option_mail = array_merge($this->option_mail, $meta['mail']);
		}
		if(isset($meta['message']) && is_array($meta['message'])){
			$this->option_message = array_merge($this->option_message, $meta['message']);
		}
	}

	/**
	 * 更新选项
	 * @param string[] $mail
	 * @param string[] $message
	 * @param int      $user_id
	 */
	public function update($mail, $message, $user_id){
		if(!is_array($mail)){
			$mail = [];
		}
		if(!is_array($message)){
			$message = [];
		}
		if($user_id < 1){
			$this->throwMsg(-1);
		}
		$this->option_mail = array_fill_keys(array_keys($this->option_mail), false);
		$this->option_message = array_fill_keys(array_keys($this->option_message), false);
		foreach($mail as $k => $v){
			if(isset($this->option_mail[$k]) && $v){
				$this->option_mail[$k] = true;
			}
		}
		foreach($message as $k => $v){
			if(isset($this->option_message[$k]) && $v){
				$this->option_message[$k] = true;
			}
		}
		User::getUser($user_id)->getMeta()->set([
			'user_notice' => serialize([
				'mail' => $this->option_mail,
				'message' => $this->option_message
			])
		]);
	}

	/**
	 * @return array
	 */
	public function getMailList(){
		if($this->mail_list === NULL){
			$this->mail_list = [
				'follow_me' => ___("When someone follow about me"),
				'follow_gallery' => ___("When someone follow about my gallery"),
				'comment_picture' => ___("When someone commented on my picture"),
				'comment_gallery' => ___("When someone commented on my gallery"),
				'comment_reply' => ___("When someone replies to my comment"),
				'like_pic' => ___("When someone like my pictures"),
				'like_gallery' => ___("When someone like my gallery"),
				'like_comment' => ___("When someone like my comment"),
				'send_message' => ___("When someone sends a message to me"),
				'send_system_message' => ___("When the system sends a message to me"),
				'exception_login' => ___("When an exception is generated login information"),
				'login_restrictions' => ___("Error login too many times"),
				'site_activity_information' => ___("When the site has information on activities"),
			];
		}
		return $this->mail_list;
	}

	/**
	 * @return array
	 */
	public function getMessageList(){
		if($this->message_list === NULL){
			$this->message_list = [
				'follow_me' => ___("When someone follow about me"),
				'follow_gallery' => ___("When someone follow about my gallery"),
				'comment_picture' => ___("When someone commented on my picture"),
				'comment_gallery' => ___("When someone commented on my gallery"),
				'comment_reply' => ___("When someone replies to my comment"),
				'like_pic' => ___("When someone like my pictures"),
				'like_gallery' => ___("When someone like my gallery"),
				'like_comment' => ___("When someone like my comment"),
				'exception_login' => ___("When an exception is generated login information"),
				'login_restrictions' => ___("Error login too many times"),
				'site_activity_information' => ___("When the site has information on activities"),
			];
		}
		return $this->message_list;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function getOptionMail($key){
		if(isset($this->option_mail[$key])){
			return $this->option_mail[$key];
		} else{
			return false;
		}
	}

	/**
	 * @param string $key
	 * @return string
	 */
	public function getOptionMessage($key){
		if(isset($this->option_message[$key])){
			return $this->option_message[$key];
		} else{
			return false;
		}
	}

	/**
	 * 获取异常信息
	 * @param int $code
	 * @return mixed
	 */
	public function getMsg($code){
		// TODO: Implement getMsg() method.
		switch(intval($code)){
			case -1:
				return ___("Update param is miss.");
			case -2:
				return ___("Notice class initialization error.");
		}
		return ___("Unknown error.");
	}


}