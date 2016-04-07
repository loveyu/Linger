<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-2-26
 * Time: 上午10:14
 * LyCore
 * Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
 */

namespace ULib;


class Server{
	private $code = 0;

	public function add($name, $url){
		$name = strtolower(trim($name));
		$url = trim($url);
		$this->nameCheck($name);
		$this->urlCheck($url);
		if(db()->insert("server", [
				'name' => $name,
				'url' => $url
			]) < 0
		){
			$this->throwMsg(-1);
		}
	}

	public function delete($name){
		$name = strtolower(trim($name));
		$this->nameCheck($name);
		if(db()->delete("server", ['name' => $name]) === false){
			$this->throwMsg(-1);
		}
	}

	public function getNowServer(){
		try{
			$rt = $this->get(picture_server());
			return isset($rt[0]) ? $rt[0] : $rt;
		} catch(\Exception $ex){
			return false;
		}
	}

	public function get($name = NULL){
		$list = [];
		if($name === NULL){
			$list = db()->select("server", [
				'name',
				'url',
				'meta'
			]);
		} else{
			if(is_array($name)){
				array_map('trim', $name);
				array_map('strtolower', $name);
				$name = array_flip(array_flip($name));
				$list = db()->select("server", [
					'name',
					'url',
					'meta'
				], ['name' => $name]);
			} else{
				$list = db()->select("server", [
					'name',
					'url',
					'meta'
				], ['name' => strtolower(trim($name))]);
			}
		}
		for($i = 0, $l = count($list); $i < $l; $i++){
			$list[$i]['meta'] = @unserialize($list[$i]['meta']);
		}
		return $list;
	}

	private function nameCheck($name){
		if(preg_match("/^[a-z]+$/", $name) < 1){
			$this->throwMsg(-3);
		}
	}

	private function urlCheck($url){
		if(!filter_var($url, FILTER_VALIDATE_URL) || substr($url, -1) != "/"){
			if(substr($url,0,2)=="//"){
				return $this->urlCheck("http:".$url);
			}
			$this->throwMsg(-2);
		}
	}

	public function update($name, $url, $m_k, $m_v){
		$name = strtolower(trim($name));
		$this->nameCheck($name);
		$this->urlCheck($url);
		$meta = NULL;
		if(is_array($m_k) && is_array($m_v) && ($l = count($m_k)) > 0 && $l == count($m_v)){
			//如果存在META标签
			$m_l = [];
			for($i = 0; $i < $l; $i++){
				if(!empty($m_k[$i])){
					$m_l[$m_k[$i]] = $m_v[$i];
				}
			}
			if(!empty($m_l)){
				$meta = serialize($m_l);
			}
		}
		if(db()->update("server", [
				'url' => $url,
				'meta' => $meta
			], ['name' => $name]) === false
		){
			$this->throwMsg(-1);
		}
	}

	private function throwMsg($code){
		$this->code = intval($code);
		throw new \Exception($this->getMsg($this->code), $this->code);
	}

	public function getMsg($code){
		switch($code){
			case -2:
				return ___("Url format error.");
			case -1:
				return ___("Database write error.") . debug("ERROR" . implode(", ", db()->error()['write']));
			case -3:
				return ___("Name is error, only allow a-z.");
		}
		return ___("Unknown Error");
	}
} 