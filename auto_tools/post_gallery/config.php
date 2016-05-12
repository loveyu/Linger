<?php
/**
 * User: loveyu
 * Date: 2016/4/11
 * Time: 0:43
 */
/**
 * 哈希值转换
 * @param string $str
 * @return string
 */
$str_to_pwd_hash = function ($str){
	$arr = mb_split("/(?<!^)(?!$)/u", $str);
	sort($arr);
	return sha1($str . md5(implode('', $arr)));
};
return array(
	'url' => 'http://pitus.loc/',
	'token' => 'afOtaLDgmzCfhngPGsrkjghgiBiWaxvLAElEIACA',
);