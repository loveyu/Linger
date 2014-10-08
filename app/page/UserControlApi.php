<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 14-2-11
 * Time: 下午6:47
 */

namespace UView;


use Core\Page;
use ULib\Message;
use ULib\Meta;
use ULib\Server;
use ULib\User;
use ULib\UserCheck;
use ULib\UserControl;
use ULib\UserManagement;
use ULib\UserRegister;
use ULib\VersionUpdate;

/**
 * 用于用户后台控制的API
 * Class UserControlApi
 * @package UView
 */
class UserControlApi extends Page{
	/**
	 * @var array 最终用户返回消息
	 */
	private $rt_msg = [
		'status' => false,
		'code' => NULL,
		'msg' => '',
		'content' => NULL
	];

	/**
	 * 发送状态头
	 */
	public function __construct(){
		header('Content-type: application/json; Charset=utf-8');
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		if(!is_login()){
			$this->rt_msg['msg'] = '用户未登陆';
			exit;
		} else if(!login_user()->Permission("Control")){
			$this->rt_msg['msg'] = '权限不足';
			exit;
		}
	}

	/**
	 * 析构方法，输出JSON数据
	 */
	function __destruct(){
		echo json_encode($this->rt_msg);
	}

	/**
	 * 获取菜单列表
	 */
	public function menu_list(){
		$this->rt_msg['content'] = [

			[
				'name' => '控制中心',
				'id' => 'Control',
				'url' => '#main',
				'sub' => []
			],
			[
				'name' => '用户中心',
				'id' => 'UserCenter',
				'url' => get_url("User"),
				'sub' => []
			],
			[
				'name' => '消息中心',
				'id' => 'Message',
				'url' => "#message",
				'sub' => [
					[
						'name' => '发送系统消息',
						'id' => 'Send',
						'url' => '#message_send',
						'sub' => []
					]
				]
			],
			[
				'name' => '用户管理',
				'id' => 'User',
				'url' => '#user',
				'sub' => [
					[
						'name' => '添加用户',
						'id' => 'Add',
						'url' => '#user_add',
						'sub' => []
					]
				]
			],
			[
				'name' => '图片服务器',
				'id' => 'PicServer',
				'url' => '#pic_server',
				'sub' => []
			],
			[
				'name' => '网站选项',
				'id' => 'Option',
				'url' => '#option',
				'sub' => [
					[
						'name' => '缩略图设置',
						'id' => 'Thumbnail',
						'url' => '#thumbnail',
						'sub' => []
					],
					[
						'name' => '固定连接设置',
						'id' => 'Permalink',
						'url' => '#permalink',
						'sub' => []
					],
					[
						'name' => 'CDN设置',
						'id' => 'CDN',
						'url' => '#cdn',
						'sub' => []
					]
				]
			]
		];
		$this->rt_msg['status'] = true;
	}

	/**
	 * 用户权限修改
	 */
	public function permission(){
		$req = req()->_plain();
		if($req->is_post()){
			lib()->load("UserControl");
			$uc = new UserControl();
			$uid = $req->post("id");
			$permission = $req->post("permission");
			try{
				switch($req->post('operate')){
					case "add":
						$uc->PermissionAdd($uid, $permission);
						$this->rt_msg['status'] = true;
						break;
					case "set":
						$uc->PermissionSet($uid, $permission);
						$this->rt_msg['status'] = true;
						break;
				}
			} catch(\Exception $ex){
				$this->rt_msg['msg'] = $ex->getMessage();
			}
		} else{
			$this->rt_msg['msg'] = "必须以POST提交";
		}
	}

	public function send_activation_mail(){
		$req = req()->_plain();
		if($req->is_post()){
			try{
				$uid = intval($req->post('id'));
				$user = User::getUser($uid);
				if($user->is_active()){
					$this->rt_msg['msg'] = "用户已激活，无需重复发送邮件";
				} else{
					lib()->load('UserRegister');
					$ur = new UserRegister();
					if($ur->SendActivationMail($user)){
						$this->rt_msg['status'] = true;
					} else{
						$this->rt_msg['msg'] = $ur->SendActivationMsg();
					}
				}
			} catch(\Exception $ex){
				$this->rt_msg['msg'] = $ex->getMessage();
			}
		} else{
			$this->rt_msg['msg'] = "必须以POST提交";
		}
	}

	/**
	 * 更新网站选项信息
	 */
	public function option_update(){
		$req = req()->_plain();
		$list = [
			'site_title' => $req->post('site_title'),
			'site_desc' => $req->post('site_desc'),
			'site_url' => $req->post('site_url'),
			'site_static_url' => $req->post('site_static_url'),
			'comment_one_page' => intval($req->post('comment_one_page')),
			'admin_email' => $req->post('admin_email'),
			'comment_deep' => $req->post('comment_deep'),
			'default_avatar' => $req->post('default_avatar'),
			'allowed_register' => $req->post('allowed_register') ? "yes" : "no",
			'allowed_comment' => $req->post('allowed_comment') ? "yes" : "no",
			'email_notice' => $req->post('email_notice') ? "yes" : "no",
			'comment_order_desc' => $req->post('comment_order_desc') ? "yes" : "no",
			'login_captcha' => $req->post('login_captcha') ? "yes" : "no",
		];
		if(substr($list['site_url'], -1) != '/'){
			$list['site_url'] .= "/";
			if(!filter_var($list['site_url'], FILTER_VALIDATE_URL)){
				$this->rt_msg['msg'] = "网站地址不合法";
				return;
			}
		}
		if(substr($list['site_static_url'], -1) != '/'){
			$list['site_static_url'] .= "/";
			if(!filter_var($list['site_static_url'], FILTER_VALIDATE_URL)){
				$this->rt_msg['msg'] = "网站静态地址不合法";
				return;
			}
		}
		try{
			option()->update($list);
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
		}
	}

	/**
	 * 获取用户列表
	 */
	public function user_list(){
		$req = req()->_plain();
		$page = $req->get('page');
		$number = $req->get('number');
		$search = $req->get('search');
		$search_type = $req->get('search_type');
		$order = $req->get('order');
		lib()->load('UserManagement');
		$um = new UserManagement();
		try{
			$um->set_order($order);
			$um->set_search($search_type, $search);
			$this->rt_msg['content'] = [];
			$this->rt_msg['content']['data'] = $um->get_users($page, $number);
			$this->rt_msg['content']['info'] = $um->get_page_info($page, $number);
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
		}
	}

	public function get_user_meta(){
		$id = req()->_plain()->get('id');
		$id = intval($id);
		if($id < 1){
			$this->rt_msg['msg'] = "错误的用户ID";
		} else{
			try{
				lib()->load('Meta');
				$meta = new Meta("user_meta", "users_id", $id);
				$this->rt_msg['content'] = $meta->get_all();
				$this->rt_msg['status'] = true;
			} catch(\Exception $ex){
				$this->rt_msg['msg'] = $ex->getMessage();
			}
		}
	}

	public function user_add(){
		$req = req()->_plain();
		if($req->is_post()){
			lib()->load("UserRegister", "UserCheck");
			try{
				$ur = new UserRegister();
				hook()->add('UserRegister_Captcha', function (){
					//通过钩子去掉用户注册验证码
					return true;
				});
				$id = $ur->Register($req->post('email'), UserCheck::MakeHashChar($req->post('password')), $req->post('name'), "ADMIN");
				if($id > 0){
					$this->rt_msg['status'] = true;
					$this->rt_msg['content'] = $id;
				} else{
					$this->rt_msg['msg'] = $ur->CodeMsg($id);
				}
			} catch(\Exception $ex){
				$this->rt_msg['msg'] = $ex->getMessage();
			}
		} else{
			$this->rt_msg['msg'] = "必须以POST方式提交数据";
		}
	}

	public function user_edit(){
		$data = req()->post();
		try{
			$user = new User($data['id']);
			unset($data['id']);
			$user->set($data);
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
		}
	}

	public function user_change_status(){
		$req = req()->_plain();
		if($req->is_post()){
			$id = intval($req->post('id'));
			$status = intval($req->post('status'));
			try{
				$user = new User($id);
				$user->set(['status' => $status]);
				$this->rt_msg['status'] = true;
			} catch(\Exception $ex){
				$this->rt_msg['msg'] = $ex->getMessage();
			}
		} else{
			$this->rt_msg['msg'] = "必须以POST方式提交数据";
		}
	}

	public function pic_server_add(){
		lib()->load('Server');
		$server = new Server();
		$req = req()->_plain();
		try{
			$server->add($req->post('name'), $req->post('url'));
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function pic_server_delete(){
		try{
			lib()->load('Server');
			$server = new Server();
			$server->delete(req()->_plain()->post('name'));
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function pic_server_set(){
		$req = req();
		if($req->is_post()){
			try{
				option()->update(['picture_server' => trim(strtolower($req->post('name')))]);
				$this->rt_msg['status'] = true;
			} catch(\Exception $ex){
				$this->rt_msg['code'] = $ex->getCode();
				$this->rt_msg['msg'] = $ex->getMessage();
			}
		} else{
			$this->rt_msg['msg'] = "必须以POST方式提交数据";
		}
	}

	public function pic_server_edit(){
		$req = req();
		if($req->is_post()){
			try{
				lib()->load('Server');
				$server = new Server();
				$server->update($req->post('name'), $req->post('url'), $req->post('meta_key'), $req->post('meta_value'));
				$this->rt_msg['status'] = true;
			} catch(\Exception $ex){
				$this->rt_msg['code'] = $ex->getCode();
				$this->rt_msg['msg'] = $ex->getMessage();
			}
		} else{
			$this->rt_msg['msg'] = "必须以POST方式提交数据";
		}
	}

	public function thumbnail(){
		$req = req();
		if($req->is_post()){
			try{
				option()->update([
					'image_thumbnail_width' => $req->post('image_thumbnail_width'),
					'image_thumbnail_height' => $req->post('image_thumbnail_height'),
					'image_hd_width' => $req->post('image_hd_width'),
					'image_display_width' => $req->post('image_display_width')
				]);
				$this->rt_msg['status'] = true;
			} catch(\Exception $ex){
				$this->rt_msg['msg'] = $ex->getMessage();
			}
		} else{
			$this->rt_msg['msg'] = "必须以POST方式提交数据";
		}
	}

	public function permalink(){
		$req = req()->_plain();
		if($req->is_post()){
			try{
				$data = [
					'picture' => $req->post('picture'),
					'picture_pager' => $req->post('picture_pager'),
					'gallery' => $req->post('gallery'),
					'gallery_pager' => $req->post('gallery_pager'),
					'user' => $req->post('user'),
					'post' => $req->post('post'),
					'post_pager' => $req->post('post_pager'),
					'post_list' => $req->post('post_list'),
					'post_list_pager' => $req->post('post_list_pager'),
					'gallery_list' => $req->post('gallery_list'),
					'gallery_list_pager' => $req->post('gallery_list_pager'),
					'user_gallery_list' => $req->post('user_gallery_list'),
					'user_gallery_list_pager' => $req->post('user_gallery_list_pager'),
					'time_line' => $req->post('time_line'),
				];
				option()->update(['router_list' => serialize($data)]);
				$this->rt_msg['status'] = true;
			} catch(\Exception $ex){
				$this->rt_msg['msg'] = $ex->getMessage();
			}
		} else{
			$this->rt_msg['msg'] = "必须以POST方式提交数据";
		}
	}

	public function permalink_reset(){
		$req = req();
		if($req->is_post() && $req->post('time') != NULL){
			try{
				option()->update(['router_list' => serialize([])]);
				$this->rt_msg['status'] = true;
			} catch(\Exception $ex){
				$this->rt_msg['msg'] = $ex->getMessage();
			}
		} else{
			$this->rt_msg['msg'] = "重置表单参数有误";
		}
	}

	public function message_send(){
		$req = req()->_plain();
		if($req->is_post()){
			try{
				$this->__lib('Message');
				$m = new Message();
				//0表示系统用户
				$this->rt_msg['content'] = $m->send($req->post('title'), $req->post('users'), htmlspecialchars(req()->post('content'), ENT_NOQUOTES), 0);
				$this->rt_msg['status'] = true;
			} catch(\Exception $ex){
				$this->rt_msg['msg'] = $ex->getMessage();
			}
		} else{
			$this->rt_msg['msg'] = "必须以POST方式提交数据";
		}
	}

	public function message_system(){
		$req = req()->_plain();
		try{
			$this->__lib('Message');
			$ms = new Message();
			$ms->setPager($req->get('page'), $req->get('number'));
			$this->rt_msg['content'] = [];
			$this->rt_msg['content']['data'] = $ms->getSystemMessage();
			$this->rt_msg['content']['page'] = $ms->getCount();
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function message_read(){
		$req = req()->_plain();
		try{
			$this->throwMsgCheck('is_get');
			$this->__lib('Message');
			$ms = new Message();
			$this->rt_msg['content'] = $ms->getMessageContent($req->get('id'));
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function message_del(){
		try{
			$this->throwMsgCheck('is_post');
			$req = req()->_plain();
			$this->__lib('Message');
			$ms = new Message();
			$ms->SystemDelete($req->post('id'));
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function cdn(){
		try{
			$this->throwMsgCheck('is_post');
			$req = req()->_plain();
			$data = [
				'status' => $req->post('status') == '1',
				'list' => $req->post('list')
			];
			option()->update(['cdn' => serialize($data)]);
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function checkUpdate(){
		try{
			$this->__lib("VersionUpdate");
			$msg = (new VersionUpdate())->check(req()->req('force')=="1");
			if(!empty($msg)){
				$this->rt_msg['content'] = $msg;
				$this->rt_msg['status'] = true;
			}
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	private function throwMsgCheck($str){
		$msg = NULL;
		foreach(func_get_args() as $v){
			switch($v){
				case "is_post":
					req()->is_post() or $msg = "必须使用POST请求访问";
					break;
				case "is_get":
					req()->is_get() or $msg = "必须使用GET请求访问";
					break;
				default:
					$msg = "未知异常信息";
			}
			if($msg !== NULL){
				throw new \Exception($msg);
			}
		}
	}
}