<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-3-27
 * Time: 下午10:15
 * Filename: Message.php
 */

namespace UView;

use Core\Page;

class Message extends Page{
	/**
	 * @var \ULib\Theme 主题操作
	 */
	private $theme;

	function __construct(){
		if(!is_login()){
			redirect_to_login();
		}
		$this->theme = theme();
		$this->theme->setBreadcrumb("消息中心", "Message");
		l_h("html_tag.php");
	}

	public function main(){
		$this->theme->setBreadcrumb("消息中心");
		$this->theme->header_add($this->theme->css(get_bootstrap_plugin_url("markdown/markdown.min.css")));
		$this->theme->header_add($this->theme->js([
			'src' => get_bootstrap_plugin_url("markdown/markdown.js")
		]));
		$this->theme->header_add($this->theme->js([
			'src' => get_style("message_action.js")
		]));
		$this->__view("User/header.php");
		$this->__view("Message/main.php");
		$this->__view("User/footer.php");
	}

	public function inbox(){
		$this->__lib('Message');
		$mg = new \ULib\Message();
		$req = req()->_plain();
		$mg->setPager($req->get('page'), 12);
		$data = $mg->getInbox(login_user()->getId());
		$this->theme->setBreadcrumb("收信箱");
		$this->theme->setTitle("收信箱");
		$this->theme->header_add($this->theme->js([
			'src' => get_style("message_action.js")
		]));
		$this->__view("User/header.php");
		$this->__view("Message/inbox.php", [
			'list' => $data,
			'count' => $mg->getCount()
		]);
		$this->__view("User/footer.php");
	}

	public function outbox(){
		$this->__lib('Message');
		$mg = new \ULib\Message();
		$req = req()->_plain();
		$mg->setPager($req->get('page'), 12);
		$data = $mg->getOutbox(login_user()->getId());
		$this->theme->setBreadcrumb("发信箱");
		$this->theme->setTitle("发信箱");
		$this->theme->header_add($this->theme->js([
			'src' => get_style("message_action.js")
		]));
		$this->__view("User/header.php");
		$this->__view("Message/outbox.php", [
			"list" => $data,
			'count' => $mg->getCount()
		]);
		$this->__view("User/footer.php");
	}

	public function view(){
		$this->__lib('Message');
		$mg = new \ULib\Message();
		$req = req()->_plain();
		$data = [
			'content' => '',
			'error' => ''
		];
		try{
			$data['content'] = $mg->read($req->req('id'), login_user()->getId());
			$this->theme->header_add($this->theme->css(get_bootstrap_plugin_url("markdown/markdown.min.css")));
			$this->theme->header_add($this->theme->js([
				'src' => get_bootstrap_plugin_url("markdown/markdown.js")
			]));
			$this->theme->header_add($this->theme->js([
				'src' => get_style("message_action.js")
			]));
		} catch(\Exception $ex){
			$data['error'] = $ex->getMessage();
		}
		$this->theme->setBreadcrumb("查看消息");
		$this->theme->setTitle("查看消息");
		$this->__view("User/header.php");
		$this->__view("Message/view.php", $data);
		$this->__view("User/footer.php");
	}

	public function option(){
		$this->theme->setBreadcrumb("通知设置");
		$this->theme->setTitle("通知设置");
		$this->__view("User/header.php");
		$this->__view("Message/option.php");
		$this->__view("User/footer.php");
	}
} 