<?php
namespace CLib;
/**
 * 安全函数类
 */
class Safe{
	/**
	 * 加密
	 * @param        $encrypt
	 * @param string $key
	 * @return string
	 */
	public static function encrypt($encrypt, $key = ''){
		if(!function_exists('mcrypt_create_iv')){
			return self::encrypt_self($encrypt, $key);
		}
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
		$pass_crypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $encrypt, MCRYPT_MODE_ECB, $iv);
		$encode = base64_encode($pass_crypt);
		return $encode;
	}

	/**
	 * 解密
	 * @param        $decrypt
	 * @param string $key
	 * @return string
	 */
	public static function decrypt($decrypt, $key = ''){
		if(!function_exists('mcrypt_create_iv')){
			return self::decrypt_self($decrypt, $key);
		}
		$decoded = base64_decode($decrypt);
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
		$decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_ECB, $iv);
		return $decrypted;
	}

	/**
	 * PHP简单加密
	 *
	 * @param        $txt
	 * @param string $key
	 * @return string
	 */
	public static function encrypt_self($txt, $key = ''){
		//当不存在mcrypt函数库时的加密方法
		srand((double)microtime() * 1000000);
		$encrypt_key = md5(rand(0, 32000));
		$ctr = 0;
		$tmp = '';
		for($i = 0; $i < strlen($txt); $i++){
			$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
			$tmp .= $encrypt_key[$ctr] . ($txt[$i] ^ $encrypt_key[$ctr++]);
		}
		return base64_encode(self::passport_key($tmp, $key));
	}

	/**
	 * PHP简单解密
	 *
	 * @param $txt
	 * @param $key
	 * @return string
	 */
	public static function decrypt_self($txt, $key){
		//当不存在mcrypt函数库时的解密方法
		$txt = self::passport_key(base64_decode($txt), $key);
		$tmp = '';
		for($i = 0; $i < strlen($txt); $i++){
			$md5 = $txt[$i];
			$tmp .= $txt[++$i] ^ $md5;
		}
		return $tmp;
	}

	/**
	 * 密钥生成
	 * @param $txt
	 * @param $encrypt_key
	 * @return string
	 */
	private static function passport_key($txt, $encrypt_key){
		$encrypt_key = md5($encrypt_key);
		$ctr = 0;
		$tmp = '';
		for($i = 0; $i < strlen($txt); $i++){
			$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
			$tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
		}
		return $tmp;
	}

}