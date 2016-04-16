<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-2-15
 * Time: 下午10:02
 * LyCore
 * Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
 */

namespace ULib;

class Option{
	private $option;

	function __construct(){
		$this->option = $this->get_option();
		$this->set_option();
	}

	private function get_option(){
		$data = \db()->select("options", [
			"option_name",
			"option_value"
		], ['option_autoload' => 1]);
		$rt = [];
		if(is_array($data)){
			foreach($data as $v){
				$rt[$v['option_name']] = $v['option_value'];
			}
		}
		return $rt;
	}

	private function set_option(){
		\cfg()->merge('option', $this->option);
	}

	public function register_hook(){
		$hook = \hook();
		$hook->add('UserRegister_Register_before', function ($code){
			if($code === 0 && !allowed_register()){
				return -11;
			}
			return $code;
		});
		switch(site_mode()){
			case "https":
				if(!is_ssl()){
					header("Cache-Control: no-cache, must-revalidate");
					header("Pragma: no-cache");
					redirect("https://" . preg_replace("/^http[s]*:\\/\\//", "", URL_NOW), "header", 301);
				}
				$hook->add('get_url', function (){
					return site_url_ssl();
				});
				$hook->add("get_file_url", function (){
					return site_static_url_ssl();
				});
				break;
			case
				"all":
				if(is_ssl()){
					$hook->add('get_url', function (){
						return site_url_ssl();
					});
					$hook->add("get_file_url", function (){
						return site_static_url_ssl();
					});
				} else{
					$hook->add('get_url', function (){
						return site_url();
					});
					$hook->add("get_file_url", function (){
						return site_static_url();
					});
				}
				break;
			default:
				if(is_ssl()){
					header("Cache-Control: no-cache, must-revalidate");
					header("Pragma: no-cache");
					redirect("http://" . substr(URL_NOW, 7), "header", 301);
				}
				$hook->add('get_url', function (){
					return site_url();
				});
				$hook->add("get_file_url", function (){
					return site_static_url();
				});
		}
		$hook->add("UserRegister_CodeMsg", function ($msg, $code){
			if($code == -11){
				return ___("User register already closed.");
			}
			return $msg;
		});
		if(!login_captcha()){
			//将验证码检测返回TRUE
			$hook->add("UserLogin_Captcha", function (){
				return true;
			});
		}
		if(email_notice()){
			$hook->add('UserRegister_Register_success', function ($user_id){
				\lib()->load('MailTemplate');
				$user = User::getUser($user_id);
				$mt = new MailTemplate("new_user_registered.html");
				$mt->setUserInfo($user->getInfo());
				$mt->mailSend(site_title() . " Manager", admin_email());
			});
		}
		if(strtolower(default_avatar_config()) == "gravatar"){
			$hook->add("Avatar_convert", function ($avatar){
				if(strtolower($avatar) == "{default}"){
					return "{gravatar}";
				}
				return $avatar;
			});
		}
	}

	/**
	 * 更新一个选项
	 * @param array $list 更新的数据列表
	 * @param int   $auto 不存在的数据是否添加为自动加载
	 * @throws \Exception
	 */
	public function update($list, $auto = 1){
		$db = \db()->getWriter();
		$db->pdo->beginTransaction();
		foreach($list as $name => $v){
			if(isset($this->option[$name]) && $this->option[$name] == $v){
				continue;
			}
			if(($row = $db->update("options", ['option_value' => $v], ['option_name' => $name])) === false){
				$db->pdo->rollBack();
				throw new \Exception(___("Update option error") . debug("ERROR:" . implode(", ", $db->error())));
			}
			if($row == 0){
				if($db->insert("options", [
						'option_value' => $v,
						'option_name' => $name,
						'option_autoload' => (int)$auto
					]) < 0
				){
					$db->pdo->rollBack();
					throw new \Exception(___("Insert unknown option error.") . debug("ERROR:" . implode(", ", $db->error()) . ",SQL:" . $db->last_query()));
				}
			}
		}
		$db->pdo->commit();
		foreach($list as $name => $v){
			$this->option[$name] = $v;
		}
		$this->set_option();
	}

	public function select_list($list){
		$none = [];
		foreach($list as $name){
			if(!isset($this->option[$name])){
				$none[] = $name;
				$this->option[$name] = '';
			}
		}
		$data = \db()->select("options", [
			"option_name",
			"option_value"
		], ["option_name" => $none]);
		foreach($data as $v){
			$this->option[$v['option_name']] = $v['option_value'];
		}
		$this->set_option();
		$rt = [];
		foreach($list as $name){
			$rt[$name] = $this->option[$name];
		}
		return $rt;
	}

}