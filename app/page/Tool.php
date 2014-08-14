<?php
/**
 * Created by PhpStorm.
 * User: hzy
 * Date: 14-2-8
 * Time: 下午9:27
 */

namespace UView;


use Core\Page;
use ULib\Avatar;
use ULib\Captcha;

class Tool extends Page{
	public function captcha(){
		$this->__lib('Captcha');
		$c = new Captcha();
		header("Content-Type: image/JPEG");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		imagejpeg($c->create());
	}

	public function captcha_verify($code = NULL, $id = 0){
		header("Content-Type: Application/json; charset=utf-8");
		$this->__lib('Captcha');
		$c = new Captcha();
		echo json_encode(["status" => $c->verify($code, false, $id)]);
	}

	public function redirect(){
		$go = urldecode(req()->_plain()->get('go'));
		if(filter_var($go, FILTER_VALIDATE_URL)){
			redirect($go, 'Location', 302); //使用跳转模式，可直接访问图片
		} else{
			redirect();
		}
	}

	public function avatar($size = 0, $file = NULL){
		$this->__lib('Avatar');
		if(Avatar::createOtherSizeAvatar($size, $file)){
			header("Content-Type: image");
			echo Avatar::getAvatarContent($size, $file);
		} else{
			$this->__load_404();
		}
	}

	public function go_user(){
		redirect(user_link(req()->get('name')));
	}
} 