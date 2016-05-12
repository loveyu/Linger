<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-2-21
 * Time: 下午2:50
 * LyCore
 * Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
 */

namespace ULib;

use CLib\Image;
use Core\Log;


/**
 * 用户头像处理类
 * Class Avatar
 * @package ULib
 */
class Avatar{
	private static $list = [
		20,
		35,
		50,
		64,
		80,
		100,
		120,
		150,
		180,
		200,
		240,
		300,
		320,
		360,
		380
	];

	/**
	 * @param string $avatar
	 * @param User   $user
	 * @return string
	 */
	public static function get($avatar, $user){
		$avatar = hook()->apply("Avatar_convert", $avatar);
		switch(strtolower($avatar)){
			case "{gravatar}":
				return self::getGravatar(md5($user->getEmail()));
			case "{user_upload}":
				return self::local_avatar($user->getId());
		}
		return self::default_avatar();
	}

	/**
	 * 依据MD5值获取头像
	 * @param string $sid
	 * @return string
	 */
	public static function getGravatar($sid){
		if(is_ssl()){
			return "https://secure.gravatar.com/avatar/" . $sid;
		} else{
			return "https://secure.gravatar.com/avatar/" . $sid;
		}
	}

	public static function getSizeOfAvatar($avatar_url, $avatar_sql, $size){
		if($avatar_sql == "{default}"){
			$avatar_sql = cfg()->get('option', 'default_avatar');
			$avatar_sql = "{$avatar_sql}";
		}
		switch(strtolower($avatar_sql)){
			case "{gravatar}":
				return $avatar_url . "?s=" . $size;
			case "{user_upload}":
			case "{default}":
				$i = strrpos($avatar_url, '/');
				return substr($avatar_url, 0, $i) . "/" . self::getSizeList($size) . substr($avatar_url, $i);
		}
		return $avatar_url;
	}

	/**
	 * 获取合法的头像大小
	 * @param $size
	 * @return int
	 */
	private static function getSizeList($size){
		for($i = 0; $i < count(self::$list); $i++){
			if(self::$list[$i] == $size){
				return $size;
			}
			if(self::$list[$i] > $size){
				return self::$list[$i];
			}
		}
		return 120;
	}

	/**
	 * 返回网站默认头像地址
	 * @return string
	 */
	public static function default_avatar(){
		return hook()->apply("Avatar_default_avatar", get_file_url("avatar/default.jpg"));
	}

	private static function local_avatar($id){
		return hook()->apply("Avatar_local_avatar", get_file_url("avatar/{$id}.jpg"), $id);
	}

	/**
	 * @param User $user
	 * @return string
	 */
	public static function upload_avatar($user){
		$path = _BasePath_ . "/avatar/";
		if(is_file($path . $user->getId() . ".jpg")){
			return self::local_avatar($user->getId());
		}
		return "";
	}


	/**
	 * @param $size
	 * @param $file
	 * @return bool
	 */
	public static function createOtherSizeAvatar($size, $file){
		$size = (int)$size;
		if(!in_array($size, self::$list)){
			return false;
		}
		$path = _BasePath_ . "/avatar/$file";
		if(!is_file($path)){
			return false;
		}
		try{
			if(!is_dir(_BasePath_ . "/avatar/$size")){
				if(!mkdir(_BasePath_ . "/avatar/$size")){
					return false;
				}
			}
			c_lib()->load('image');
			$image = new Image(Image::IMAGE_GD);
			$image->open(_BasePath_ . "/avatar/" . $file);
			$image->thumb($size, $size, Image::IMAGE_THUMB_FIXED);
			$image->save(_BasePath_ . "/avatar/$size/" . $file, 'jpeg', false);
		} catch(\Exception $ex){
			Log::write("Convert avatar error." . $ex->getMessage(), Log::WARN);
			return false;
		}
		return true;
	}

	public static function getAvatarContent($size, $file){
		$path = _BasePath_ . "/avatar/$size/$file";
		if(is_file($path)){
			return file_get_contents($path);
		} else{
			return NULL;
		}
	}

	public function process_avatar($file, $width){
		c_lib()->load('image');
		$image = new Image(Image::IMAGE_GD);
		$image->open(_BasePath_ . "/avatar/" . $file);
		$img_w = $image->width();
		$img_h = $image->height();
		$x = $img_h / $img_w;
		$x_h = ($img_h < $img_w) ? $width / $x : $width * $x;
		$image->thumb($x_h, $x_h, Image::IMAGE_THUMB_SCALE);
		$image->thumb($width, $width, Image::IMAGE_THUMB_CENTER);
		$image->save(_BasePath_ . "/avatar/" . $file, 'jpeg');
		$this->clearOtherAvatar($file);
	}

	private function clearOtherAvatar($avatar_name){
		foreach(self::$list as $v){
			$path = _BasePath_ . "/avatar/$v/" . $avatar_name;
			if(is_file($path)){
				if(!@unlink($path)){
					Log::write("Delete avatar error." . $path, Log::WARN);
				}
			}
		}
	}

}