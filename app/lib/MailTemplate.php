<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 14-2-7
 * Time: 下午8:41
 */

namespace ULib;

use CLib\Mail;
use Core\Log;


/**
 * 邮件模板类
 * Class MailTemplate
 * @package ULib
 */
class MailTemplate{

	/**
	 * 模板中的内容
	 * @var string
	 */
	private $content;

	/**
	 * 邮件标题
	 * @var string
	 */
	private $title = NULL;

	/**
	 * 邮件的纯文本内容
	 * @var string
	 */
	private $text;


	/**
	 * 对应的变量值列表
	 * @var array
	 */
	private $replace = [];

	/**
	 * 赋值状态标记
	 * @var bool
	 */
	private $is_set_values = false;

	/**
	 * @var bool 是否注册了当前使用pushover发送，成功后自动设置为false
	 */
	private $is_register_pushover = false;

	/**
	 * 构造函数
	 * @param string $path 对应的模板文件名称
	 * @throws \Exception 模板有误的错误信息
	 */
	public function __construct($path){
		$path = cfg()->get('mail_template') . "/" . trim($path);
		if(is_file($path) && is_readable($path)){
			$this->content = $this->get_template($path);
		} else{
			throw(new \Exception(___("Template is not exists.")));
		}
	}

	/**
	 * @param $path
	 * @return mixed
	 */
	private function get_template($path){
		$content = file_get_contents($path);
		$matches = $this->getParamList($content);
		$system_array = $this->getSystemParams();
		for($i = 0; $i < count($matches[1]); $i++){
			if(isset($system_array[$matches[1][$i]])){
				$matches[1][$i] = $system_array[$matches[1][$i]];
			} else{
				$matches[1][$i] = $matches[0][$i];
			}
		}
		return str_replace($matches[0], $matches[1], $content);
	}

	/**
	 * @param array $user_info
	 */
	public function setUserInfo($user_info){
		foreach($user_info as $k => $v){
			$this->replace["user_" . $k] = $v;
		}
	}

	/**
	 * 设置对应的变量列表
	 * @param $array
	 */
	public function setValues($array){
		$matches = $this->getParamList($this->content);
		$array = array_merge($this->replace, $array);
		for($i = 0; $i < count($matches[1]); $i++){
			if(isset($array[$matches[1][$i]])){
				$matches[1][$i] = $array[$matches[1][$i]];
			}
		}
		$this->content = str_replace($matches[0], $matches[1], $this->content);
		$this->getTitle();
		$this->getText();
		$this->content = preg_replace("/^[\\s]+\\n?/", "", $this->content);
		$this->is_set_values = true;
	}

	/**
	 * 获取网站系统参数
	 * @return array
	 */
	private function getSystemParams(){
		return [
			'site_url' => site_url(),
			'site_title' => site_title(),
			'site_desc' => site_desc(),
			'admin_email' => admin_email(),
			'site_time' => date("Y-m-d H:i:s"),
		];
	}

	/**
	 * 返回模板中对应的变量
	 * @param string $content 模板内容
	 * @return array
	 */
	private function getParamList($content){
		preg_match_all("/{([0-9a-zA-Z_]+)}/", $content, $matches);
		return $matches;
	}


	/**
	 * 获取内容
	 * @return string
	 */
	public function getContent(){
		return $this->content;
	}

	/**
	 * 取得邮件标题
	 * @return string
	 */
	public function getTitle(){
		if($this->title !== NULL){
			return $this->title;
		}
		preg_match_all("/<title>([\\s\\S]+?)<\\/title>/", $this->content, $matches);
		if(isset($matches[1])){
			$this->title = strip_tags(implode(", ", $matches[1]));
		}
		$this->content = preg_replace("/<title>([\\s\\S]+?)<\\/title>/", "", $this->content);
		return $this->title;
	}

	/**
	 * 取得邮件描述文字
	 */
	private function getText(){
		preg_match_all("/<!--T([\\s\\S]+?)-->/", $this->content, $matches);
		if(isset($matches[1])){
			$matches = array_map("trim", $matches[1]);
			$this->text = trim(strip_tags(implode("\r\n", $matches)));
		}
		$this->content = preg_replace("/<!--T([\\s\\S]+?)-->/", "", $this->content);
	}

	/**
	 * 设置当前邮件推送，而不是发送
	 * @param boolean $is_register_pushover
	 */
	public function setIsRegisterPushover(bool $is_register_pushover){
		$this->is_register_pushover = $is_register_pushover;
	}


	/**
	 * 邮件发送
	 * @param string      $name
	 * @param string      $email
	 * @param string|null $title
	 * @param string|null $textContent
	 * @param bool        $queue
	 * @throws \Exception
	 */
	public function mailSend($name, $email, $title = NULL, $textContent = NULL, $queue = true){
		if(!hook()->apply("MailTemplate_mailSend", true)){
			//是否取消所有邮件发送记录
			Log::write(___("Mail send is cancel.") . print_r(func_get_args(), true), Log::NOTICE);
			return;
		}
		if(!$this->is_set_values){
			$this->setValues([]);
		}
		try{
			c_lib()->load('mail');
			$mail = new Mail();
			if($title === NULL){
				$title = $this->title;
			}
			$mail->Subject = $title;
			$mail->addAddress($email, $name);
			$mail->msgHTML($this->content);
			if($textContent === NULL){
				$mail->AltBody = $this->text;
			} else{
				$mail->AltBody = $textContent;
			}

			if($this->is_register_pushover){
				$rt_id = Pushover::push($mail->Subject, $mail->AltBody . "\n" . strip_tags($this->content));
				$this->is_register_pushover = false;
				if($rt_id > 0){
					//邮件通过通知进行触发，不经过邮件系统
					return;
				}
			}

			if(hook()->apply('MailTemplate_mailSend_noQueue',!$queue)){
				$mail->send();
			} else{
				lib()->load('Queue');
				$queue = new Queue();
				$mail_call = new MailCallback($mail);
				$queue->add($mail_call, NULL, [
					'lib' => ['MailTemplate'],
					'c_lib' => ['mail']
				]);
			}
		} catch(\Exception $ex){
			throw new \Exception(___("Mail Send Error.") . debug(" :" . $ex->getMessage()));
		}
	}
}

if(!interface_exists('QueueCallback')){
	lib()->load('Queue');
}

/**
 * 邮件队列回调
 * Class MailCallback
 * @package ULib
 */
class MailCallback implements QueueCallback{
	/**
	 * @var Mail
	 */
	private $mail;

	/**
	 * @param Mail $mail_t
	 */
	function __construct(Mail $mail_t){
		$this->mail = clone $mail_t;
	}

	/**
	 * @param int   $id
	 * @param mixed $param
	 * @return mixed
	 */
	public function run($id, $param){
		$this->mail->send();
	}


}