<?php
namespace UView;

use \Core\Page;
use ULib\Avatar;
use ULib\CountMessage;
use ULib\Feed;
use ULib\FeedManagement;
use ULib\FollowManagement;
use ULib\Gallery;
use ULib\ListPic;
use ULib\Message;
use ULib\Picture;
use ULib\Post;
use ULib\Server;
use ULib\User;
use ULib\UserCheck;
use ULib\UserControl;
use ULib\UserLogin;
use ULib\UserRegister;

/**
 * Class UserApi
 * @package UView
 */
class UserApi extends Page{
	/**
	 * @var array 最终用户返回消息
	 */
	private $rt_msg = [
		'status' => false,
		'code' => NULL,
		'msg' => '',
		'content' => NULL
	];

	private $ajax = false;

	/**
	 * 发送状态头
	 */
	public function __construct(){
		parent::__construct();
		header('Content-type: application/json; Charset=utf-8');
		//header("Content-type: text/plain; Charset=utf-8");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
	}

	/**
	 * 用户注册
	 */
	public function register(){
		$req = req()->_plain();
		if($req->is_post()){
			lib()->load('UserRegister', 'User');
			$ur = new UserRegister();
			$this->rt_msg['code'] = $ur->Register($req->post('email'), $req->post('password'), $req->post('name'), $req->post('captcha'));
			if($this->rt_msg['code'] <= 0){
				$this->rt_msg['msg'] = $ur->CodeMsg($this->rt_msg['code']);
			} else{
				$this->rt_msg['status'] = true;
				$user = new User($this->rt_msg['code']);
				$this->rt_msg['content'] = $user->getInfo();
			}
		} else{
			$this->rt_msg['msg'] = '必须以POST方式提交';
		}
	}

	/**
	 * 用户登出
	 */
	public function logout(){
		try{
			lib()->load('UserLogin');
			UserLogin::Logout();
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
		}
	}

	/**
	 * 请求忘记用户密码
	 */
	public function forget_password(){
		try{
			$this->throwMsgCheck('is_post');
			$req = req();
			$email = $req->post('email');
			$captcha = $req->post('captcha');
			lib()->load("UserControl");
			$uc = new UserControl();
			$uc->reset_password($email, $captcha);
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function delete_reset_password_request(){
		try{
			lib()->load("UserControl");
			$this->throwMsgCheck('is_post', 'is_login');
			$uc = new UserControl();
			$uc->delete_reset_password_request(login_user());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	/**
	 * 重置用户密码
	 */
	public function reset_password(){
		try{
			$this->throwMsgCheck('is_post');
			$req = req();
			$user = $req->post('user');
			$code = $req->post('code');
			$password = $req->post('password');
			lib()->load("UserControl");
			$uc = new UserControl();
			$uc->reset_password_finish($user, $code, $password);
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
		}
	}

	/**
	 * 发送激活邮件
	 */
	public function send_activation_mail(){
		try{
			$this->throwMsgCheck('is_login');
			$user = login_user();
			if($user->getStatus() == 0){
				lib()->load('UserRegister');
				$ur = new UserRegister();
				if($ur->SendActivationMail($user)){
					$this->rt_msg['content'] = $user->getEmail();
					$this->rt_msg['status'] = true;
				} else{
					$this->rt_msg['msg'] = $ur->SendActivationMsg();
				}
			} else{
				$this->rt_msg['msg'] = "当前用户无需验证邮箱";
			}
		} catch(\Exception $ex){
			$this->rt_msg['status'] = false;
			$this->rt_msg['msg'] = $ex->getMessage();
		}
	}

	/**
	 * 用户根据激活邮件激活
	 * @param $code
	 */
	public function user_activation($code = NULL){
		if(!is_login()){
			$this->rt_msg['msg'] = "必须登录才能进行用户激活";
		} else{
			lib()->load("UserRegister");
			try{
				UserRegister::UserActivation(login_user(), $code);
				$this->rt_msg['status'] = true;
			} catch(\Exception $ex){
				$this->rt_msg['msg'] = $ex->getMessage();
			}
		}
	}

	public function markdown(){
		try{
			$this->throwMsgCheck('is_post');
			$this->rt_msg['content'] = get_markdown(htmlspecialchars(req()->post('content'), ENT_NOQUOTES));
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	/**
	 * 用户编辑自己的简单信息
	 */
	public function edit_info(){
		try{
			$this->throwMsgCheck('is_post', 'is_login');
			$req = req()->_plain();
			$user = login_user();
			$user->set([
				'aliases' => $req->post('aliases'),
				'url' => $req->post('url')
			]);
			$user->profile_message(htmlspecialchars(req()->post('profile_message'), ENT_NOQUOTES));
			$user->profile_video($req->post('video'));
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
		}
	}

	/**
	 * 修改用户密码
	 */
	public function edit_password(){
		try{
			$this->throwMsgCheck('is_post', 'is_login');
			$req = req()->_plain();
			lib()->load("UserControl");
			$uc = new UserControl();
			$uc->edit_user_password(login_user(), $req->post('old'), $req->post('new'));
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
		}
	}

	/**
	 * 用户POST登录
	 */
	public function login(){
		lib()->load('UserLogin');
		$ul = new UserLogin();
		try{
			$this->throwMsgCheck('is_post');
			$req = req()->_plain();
			$ul->PostLogin($req->post('account'), $req->post('password'), $req->post('captcha'), $req->post('save'));
			$this->rt_msg['status'] = true;
			$this->rt_msg['content'] = $ul->LoginContent();
		} catch(\Exception $ex){
			$this->rt_msg['code'] = $ul->getCode();
			$this->rt_msg['msg'] = $ex->getMessage();
		}
	}

	/**
	 * 用户名存在性检测
	 * @param $user
	 */
	public function user_check($user = NULL){
		lib()->load('UserCheck');
		$this->rt_msg['status'] = UserCheck::CheckName(strtolower($user)) === true;
	}

	/**
	 * 邮箱存在性检测
	 * @param $email
	 */
	public function email_check($email = NULL){
		lib()->load('UserCheck');
		$this->rt_msg['status'] = UserCheck::CheckEmail($email) === true;
	}

	/**
	 * 根据明文创建一个可供表单提交的HASH密码
	 * @param $str
	 */
	public function make_hash_char($str = NULL){
		lib()->load('UserCheck');
		$str = trim($str);
		if(empty($str)){
			$this->rt_msg['msg'] = "提交的字符有误";
		} else{
			$this->rt_msg['status'] = true;
			$this->rt_msg['content'] = UserCheck::MakeHashChar($str);
		}
	}

	/**
	 * 重置用户Cookie
	 */
	public function reset_cookie(){
		try{
			$this->throwMsgCheck('is_post', 'is_login');
			$req = req()->_plain();
			lib()->load("UserControl");
			$uc = new UserControl();
			$uc->reset_cookie(login_user(), $req->post('type'));
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
		}
	}

	/**
	 * 获取用户自己的信息
	 */
	public function user_info(){
		try{
			$this->throwMsgCheck('is_login');
			$user = login_user();
			$this->rt_msg['content'] = [
				'user_id' => $user->getId(),
				'email' => $user->getEmail(),
				'avatar' => $user->getAvatar(),
				'name' => $user->getName(),
				'aliases' => $user->getAliases(),
				'url' => $user->getUrl()
			];
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
		}
	}


	/**
	 * 编辑邮箱地址
	 */
	public function edit_email(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'edit_email');
			$req = req()->_plain();
			lib()->load("UserControl");
			$uc = new UserControl();
			$uc->edit_email(login_user(), $req->post('email'), $req->post('password'), $req->post('code'));
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
		}
	}

	/**
	 * 发送新的验证码到新邮箱
	 */
	public function edit_email_send_mail(){
		$req = req()->_plain();
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'edit_email');
			lib()->load("UserControl");
			$uc = new UserControl();
			$uc->edit_email_send_mail(login_user(), $req->post('email'), $req->post('password'));
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
		}

	}

	public function edit_avatar_type(){
		try{
			$this->throwMsgCheck('is_post', 'is_login');
			$req = req()->_plain();
			lib()->load("UserControl");
			$uc = new UserControl();
			$uc->edit_avatar(login_user(), $req->post('type'));
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
		}
	}

	public function user_avatar_upload(){
		try{
			$this->throwMsgCheck('is_post', 'is_login');
			lib()->load("UserControl");
			$uc = new UserControl();
			$uc->upload_avatar(login_user());
			$this->rt_msg['content'] = Avatar::upload_avatar(login_user());
			if(!req()->is_ajax()){
				header("Location: " . $_SERVER['HTTP_REFERER']);
			}
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	/**
	 * 获取某一图片的详细信息
	 */
	public function picture_url(){
		try{
			$req = req()->_plain();
			$this->throwMsgCheck('is_get', 'is_login');
			$this->__lib("Picture");
			$pic = new Picture();
			$this->rt_msg['content'] = $pic->get_pic($req->get('id'));
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	/**
	 * 图片上传
	 */
	public function picture_upload(){
		try{
			$this->ajax = true;
			$req = req()->_plain();
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			$this->__lib('Picture', 'Server');
			$pic = new Picture();
			$server = new Server();
			if(!array_key_exists('files', $_FILES)){
				$_FILES['files'] = array();
			}
			$this->rt_msg['content'] = $pic->add($req->post('name'), $req->post('tag'), $req->post('desc'), @$_FILES['files'], $server->getNowServer(), login_user());
			$this->rt_msg['status'] = count($this->rt_msg['content']['list']) > 0;
			if($this->rt_msg['status'] && $req->post('get_msg') == 1){
				$this->rt_msg['content']['msg'] = $pic->get(join(',', $this->rt_msg['content']['list']), login_user()->getId());
			}
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	/**
	 * 测试POST信息
	 */
	public function _post_info(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			$this->rt_msg['content'] = [
				'POST' => $_POST,
				'FILES' => $_FILES
			];
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function picture_delete(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			lib()->load('Picture');
			$pic = new Picture();
			$pic->delete(req()->post('id'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function picture_more_delete(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			lib()->load('Picture');
			$pic = new Picture();
			$ids = array_flip(array_flip(array_map('intval', explode(",", req()->post('id')))));
			$errors = [];
			foreach($ids as $id){
				try{
					$pic->delete($id, login_user()->getId());
				} catch(\Exception $ex){
					$errors[$id] = $ex->getMessage();
				}
			}
			if(count($errors) > 0){
				$this->rt_msg['msg'] = implode("\n", $errors);
				$this->rt_msg['content'] = count($errors);
			} else{
				$this->rt_msg['status'] = true;
			}
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}


	public function picture_remove_tag(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			lib()->load('Picture');
			$pic = new Picture();
			$req = req()->_plain();
			$pic->remove_tag($req->post('id'), $req->post('tag'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function picture_add_tag(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			lib()->load('Picture');
			$pic = new Picture();
			$req = req()->_plain();
			$rt = $pic->add_tag($req->post('id'), $req->post('tag'), login_user()->getId());
			if(is_array($rt) && count($rt) > 0){
				$this->rt_msg['status'] = true;
				$this->rt_msg['content'] = $rt;
			} else{
				$this->rt_msg['msg'] = "未添加任何内容到数组中";
			}
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function picture_edit_info(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			lib()->load('Picture');
			$pic = new Picture();
			$req = req()->_plain();
			$pic->edit_info($req->post('pic_id'), login_user()->getId(), $req->post('desc'), $req->post('status'), $req->post('name'));
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function picture_like(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			lib()->load('Picture');
			$pic = new Picture();
			$pic->like(req()->post('id'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function gallery_like(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			lib()->load('Gallery');
			$g = new Gallery();
			$g->like(req()->post('id'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function gallery_add(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			lib()->load('Gallery');
			$g = new Gallery();
			$rt = $g->add(req()->_plain()->post('gallery_title'), login_user()->getId());
			$this->rt_msg['content'] = intval($rt);
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function gallery_add_by_pics(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			lib()->load('Gallery');
			$g = new Gallery();
			$rt = $g->add(req()->_plain()->post('gallery_title'), login_user()->getId());
			$g = new Gallery($rt, login_user()->getId());
			$g->add_pic(req()->_plain()->post('pic_list'));
			$this->rt_msg['content'] = intval($rt);
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function gallery_add_tag(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			lib()->load('Gallery');
			$g = new Gallery();
			$req = req()->_plain();
			$rt = $g->add_tag($req->post('id'), $req->post('tag'), login_user()->getId());
			if(is_array($rt) && count($rt) > 0){
				$this->rt_msg['status'] = true;
				$this->rt_msg['content'] = $rt;
			} else{
				$this->rt_msg['msg'] = "未添加任何内容到数组中";
			}
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function gallery_remove_tag(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			lib()->load('Gallery');
			$g = new Gallery();
			$req = req()->_plain();
			$g->remove_tag($req->post('id'), $req->post('tag'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function gallery_edit_info(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			lib()->load('Gallery');
			$req = req()->_plain();
			$g = new Gallery($req->post('gallery_id'), login_user()->getId());
			$g->edit_info($req->post('gallery_title'), $req->post('gallery_description'), $req->post('gallery_comment_status'));
			$meta = req()->post('meta');
			if(is_array($meta) && count($meta) > 0){
				$g->set_meta_info($meta);
			}
			$g->updated();
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function gallery_add_pic(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			lib()->load('Gallery');
			$req = req()->_plain();
			$g = new Gallery($req->post('gallery_id'), login_user()->getId());
			$g->add_pic($req->post('list'));
			$this->rt_msg['content'] = $g->getPictures($g->getGalleryId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function gallery_remove_pic(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			lib()->load('Gallery');
			$req = req()->_plain();
			$g = new Gallery($req->post('gallery_id'), login_user()->getId());
			$g->remove_pic($req->post('list'));
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function gallery_delete(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			lib()->load('Gallery');
			$g = new Gallery();
			$g->delete(req()->_plain()->post('id'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function gallery_set_public(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			lib()->load('Gallery');
			$g = new Gallery(req()->_plain()->post('id'), login_user()->getId());
			$g->set_public();
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function gallery_set_draft(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			lib()->load('Gallery');
			$g = new Gallery(req()->_plain()->post('id'), login_user()->getId());
			$g->set_draft();
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function gallery_set_front_cover(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			lib()->load('Gallery');
			$g = new Gallery();
			$this->rt_msg['content'] = $g->set_front_cover(req()->_plain()->post('gallery_id'), req()->_plain()->post('pic_id'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function select_user_pic(){
		try{
			$this->throwMsgCheck('is_get', 'is_login', 'is_active');
			lib()->load('ListPic');
			$lp = new ListPic();
			$req = req()->_plain();
			if(strtolower($req->get('order')) === "asc"){
				$lp->order_type(false);
			}
			$lp->setDateBegin($req->get('time_begin'))->setDateEnd($req->get('time_end'));
			$lp->setTag($req->get('tag'))->setTagModeIsLike(strtolower($req->get('tag_like')) === 'like');
			$lp->setUser(login_user()->getId())->setPage($req->get('page'))->setLimit($req->get('one_page'));
			$this->rt_msg['content'] = $lp->get();
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}


	public function follow_user(){
		try{
			$this->throwMsgCheck('is_post', 'is_login');
			$this->__lib("FollowManagement");
			$fm = new FollowManagement();
			$fm->follow(req()->post('id'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function follow_user_cancel(){
		try{
			$this->throwMsgCheck('is_post', 'is_login');
			$this->__lib("FollowManagement");
			$fm = new FollowManagement();
			$fm->follow_cancel(req()->post('id'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function follow_gallery(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			$this->__lib("FollowManagement");
			$fm = new FollowManagement();
			$fm->follow_gallery(req()->post('id'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function follow_gallery_cancel(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			$this->__lib("FollowManagement");
			$fm = new FollowManagement();
			$fm->follow_gallery_cancel(req()->post('id'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function message_send(){
		try{
			$this->throwMsgCheck('is_post', 'is_login', 'is_active');
			$this->__lib('Message');
			$m = new Message();
			$req = req()->_plain();
			$this->rt_msg['content'] = $m->send($req->post('title'), $req->post('users'), htmlspecialchars(req()->post('content'), ENT_NOQUOTES), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function message_read(){
		try{
			$this->throwMsgCheck('is_login');
			$this->__lib('Message');
			$m = new Message();
			$req = req()->_plain();
			$this->rt_msg['content'] = $m->read($req->req('id'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function message_delete(){
		try{
			$this->throwMsgCheck('is_post', 'is_login');
			$this->__lib('Message');
			$m = new Message();
			$req = req()->_plain();
			$m->delete($req->post('id'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function message_read_flag(){
		try{
			$this->throwMsgCheck('is_post', 'is_login');
			$this->__lib('Message');
			$m = new Message();
			$req = req()->_plain();
			$m->set_read($req->post('id'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function message_option(){
		try{
			$this->throwMsgCheck('is_post', 'is_login');
			$req = req()->_plain();
			notice()->update($req->post('mail'), $req->post('message'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}


	public function post_create(){
		try{
			$this->throwMsgCheck('is_post', 'power/Posts');
			$req = req();
			$this->__lib("Post");
			$post = new Post();
			$this->rt_msg['content'] = $post->create(strip_tags($req->post('title')), strip_tags($req->post('name')), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function post_delete(){
		try{
			$this->throwMsgCheck('is_post', 'power/Posts');
			$this->__lib("Post");
			$post = new Post(req()->post('id'));
			$post->delete(login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function post_edit(){
		try{
			$this->throwMsgCheck('is_post', 'power/Posts');
			$this->__lib("Post");
			$req = req();
			$post = new Post($req->post('id'));
			$post->update(strip_tags($req->post('title')), strip_tags($req->post('name')), htmlspecialchars($req->post('content'), ENT_NOQUOTES), $req->post('category'), strip_tags($req->post('keyword')), strip_tags($req->post('description')), $req->post('status'), $req->post('allow_comment'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function count_views_add(){
		try{
			$this->throwMsgCheck('is_post');
			$this->__lib("CountMessage");
			$cm = new CountMessage();
			$req = req();
			$id = +$req->post('id');
			$type = trim($req->post('type'));
			if(in_array($type, $cm->getTypeList()) && $id > 0){
				if(($this->rt_msg['content'] = $cm->addCount($type, $id)) !== false){
					$this->rt_msg['status'] = true;
				}
			}
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function share_picture(){
		try{
			$this->throwMsgCheck('is_post', 'is_login');
			Feed::getInstance()->addPictureShare(NULL, req()->post('id'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function share_gallery(){
		try{
			$this->throwMsgCheck('is_post', 'is_login');
			Feed::getInstance()->addGalleryShare(NULL, req()->post('id'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function share_talk(){
		try{
			$this->throwMsgCheck('is_post', 'is_login');
			Feed::getInstance()->addTalk(NULL, req()->post('content'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function share_delete(){
		try{
			$this->throwMsgCheck('is_post', 'is_login');
			$this->__lib('FeedManagement');
			$fm = new FeedManagement();
			$fm->delete(req()->post('id'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function time_line(){
		try{
			$this->throwMsgCheck('is_get', 'is_ajax', 'is_login');
			$id = (int)req()->get('id');
			$list = Feed::getInstance()->getList(login_user()->getId(), $id);
			$u = [];
			$c = [];
			foreach($list as $fid => &$v){
				$id = $v->getUserId();
				$oid = $v->getObjUserId();
				if(!isset($u[$id])){
					$u[$id] = User::getUser($id)->getSimpleInfo();
					$u[$id]['link'] = user_link($u[$id]['name']);
				}
				if(!isset($u[$oid])){
					$u[$oid] = User::getUser($oid)->getSimpleInfo();
					$u[$oid]['link'] = user_link($u[$oid]['name']);
				}
				$c[$fid] = $v->getInfo();
			}
			$this->rt_msg['content'] = [
				'list' => $c,
				'user' => $u,
				'count' => count($list)
			];
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	private function throwMsgCheck($str){
		$msg = NULL;
		foreach(func_get_args() as $v){
			$split = explode("/", $v);
			switch($split[0]){
				case "is_login":
					is_login() or $msg = "你必须登录才能操作";
					break;
				case "is_post":
					req()->is_post() or $msg = "必须使用POST请求访问";
					break;
				case "is_get":
					req()->is_get() or $msg = "必须使用GET请求访问";
					break;
				case "is_active":
					is_login() && login_user()->is_active() or $msg = "必须的激活用户才能访问";
					break;
				case "un_active":
					is_login() && !login_user()->is_active() or $msg = "必须的未激活用户才能访问";
					break;
				case "edit_email":
					edit_email_action() or $msg = "只有指定用户可修改邮箱";
					break;
				case "power":
					is_login() && login_user()->Permission($split[1]) or $msg = "你的访问权限不足";
					break;
				case 'is_ajax':
					req()->is_ajax() or $msg = "必须使用AJAX请求访问";
					break;
				default:
					$msg = "未知异常信息";
			}
			if($msg !== NULL){
				throw new \Exception($msg);
			}
		}
	}

	/**
	 * 析构方法，输出JSON数据
	 */
	function __destruct(){
		if($this->ajax || req()->is_ajax() || (isset($_REQUEST['show_json']) && $_REQUEST['show_json'] == "1")){
			echo json_encode($this->rt_msg, JSON_UNESCAPED_UNICODE);
		} else{
			echo "状态:", $this->rt_msg['status'] ? "成功" : "错误", "\n状态码:", $this->rt_msg['code'], "\n信息:", $this->rt_msg['msg'];
		}
	}


}