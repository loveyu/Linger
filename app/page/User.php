<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-2-20
 * Time: 上午11:19
 * LyCore
 * Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
 */

namespace UView;

use Core\Page;

class User extends Page{

	/**
	 * @var \ULib\Theme
	 */
	private $theme;

	function __construct(){
		parent::__construct();
		if(!is_login()){
			redirect_to_login();
		}
		$this->theme = theme();
		$this->theme->setBreadcrumb("用户中心", "User");
	}

	public function main(){
		$this->__view("User/header.php");
		$this->__view("User/user_info.php");
		$this->__view("User/footer.php");
	}

	public function edit_info(){
		$this->theme->setBreadcrumb("编辑信息");
		$this->theme->setTitle("编辑信息");
		$this->theme->header_add($this->theme->css(get_bootstrap_plugin_url("markdown/markdown.min.css")));
		$this->theme->header_add($this->theme->js([
			'src' => get_bootstrap_plugin_url("markdown/markdown.js")
		]));
		$this->__view("User/header.php");
		$this->__view("User/edit_info.php");
		$this->__view("User/footer.php");
	}

	public function password(){
		$this->theme->setBreadcrumb("密码与安全");
		$this->theme->setTitle("密码与安全");
		$this->__view("User/header.php");
		$reset_password_meta = login_user()->getMeta()->get([
			'Reset_password_code',
			'Reset_password_time'
		], '');
		$this->__view("User/edit_password.php", $reset_password_meta);
		$this->__view("User/footer.php");
	}

	public function activation($code = NULL){
		$this->theme->setBreadcrumb("用户激活");
		$this->theme->setTitle("用户激活");
		$this->__view("User/header.php");
		$this->__view("User/activation.php", ['code' => $code]);
		$this->__view("User/footer.php");
	}

	public function email(){
		$this->theme->setBreadcrumb("更改邮箱");
		$this->theme->setTitle("更改邮箱");
		$this->__view("User/header.php");
		$this->__view("User/edit_email.php");
		$this->__view("User/footer.php");
	}

	public function edit_avatar(){
		$this->theme->setBreadcrumb("切换头像");
		$this->theme->setTitle("切换头像");
		$this->__view("User/header.php");
		$user = login_user();
		$this->__view("User/edit_avatar.php", [
			'type' => $user->getAvatarSql(),
			'avatar' => $user->getAvatar()
		]);
		$this->__view("User/footer.php");
	}

}