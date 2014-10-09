<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 14-2-5
 * Time: 下午6:39
 */

namespace UView;

use \Core\Page;
use ULib\Server;
use ULib\User;
use ULib\VersionUpdate;

class Control extends Page{
	public function __construct(){
		if(!is_login()){
			redirect(array(
				"Home",
				"login"
			));
		} else if(!login_user()->Permission("Control")){
			redirect(array(
				'Home',
				'permission'
			));
		}
		header("Content-Type: text/html; charset: utf-8");
	}

	public function main(){
		if(count(u()->getUriInfo()->getUrlList()) == 1){
			$this->__lib('VersionUpdate');
			(new VersionUpdate())->update_script();
			$this->__view("Control/main.php");
		} else{
			$this->__view("Control/main_show.php");
		}
	}

	public function user(){
		$this->__view("Control/user.php");
	}

	public function user_edit($id = 0){
		try{
			$user = new User(0 + $id);
			$info = $user->getInfo();
			$info['avatar'] = $user->getAvatarSql(); //修正头像选项
			$this->__view("Control/user_edit.php", ["info" => $info]);
		} catch(\Exception $ex){
			echo "<h3 class='text-danger'>用户信息加载失败:</h3><p>" . $ex->getMessage() . "</p>";
		}
	}

	public function check_update(){
		$this->__lib("VersionUpdate");
		$info = (new VersionUpdate())->get_update_info();
		$this->__view("Control/check_update.php", ['info' => $info]);
	}

	public function user_add(){
		$this->__view("Control/user_add.php");
	}


	public function option(){
		$this->__view("Control/option.php");
	}

	public function user_permission($id = 0){
		try{
			$user = User::getUser(0 + $id);
			$info = $user->Permission();
			$this->__view("Control/user_permission.php", [
				"info" => implode("\n", $info),
				"user_id" => $user->getId()
			]);
		} catch(\Exception $ex){
			echo "<h3 class='text-danger'>用户权限加载失败:</h3><p>" . $ex->getMessage() . "</p>";
		}
	}

	public function pic_server(){
		lib()->load('Server');
		$server = new Server();
		$this->__view("Control/pic_server.php", ['list' => $server->get()]);
	}

	public function pic_server_edit($name = NULL){
		lib()->load('Server');
		$server = new Server();
		$table = $server->get($name);
		$table = count($table) == 1 ? $table[0] : false;
		$this->__view("Control/pic_server_edit.php", ['list' => $table]);
	}

	public function thumbnail(){
		$this->__view("Control/thumbnail.php");
	}

	public function permalink(){
		$this->__view("Control/permalink.php");
	}

	public function message(){
		$this->__view("Control/message.php");
	}

	public function message_send(){
		$this->__view("Control/message_send.php");
	}
	public function footer(){
		$this->__view("Control/footer.php");
	}

	public function cdn(){
		l_h('html_tag.php');
		$this->__view("Control/cdn.php");
	}
} 