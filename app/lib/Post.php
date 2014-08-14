<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-4-1
 * Time: 上午9:51
 * Filename: Post.php
 */

namespace ULib;


use Core\Log;

/**
 * Class Post
 * @package ULib
 */
class Post extends AppException{
	/**
	 * @var \CLib\Sql
	 */
	private $db;
	/**
	 * @var int|null
	 */
	private $post_id = NULL;
	/**
	 * @var null
	 */
	private $info = NULL;

	/**
	 * @var User
	 */
	private $post_user = NULL;

	/**
	 * @var null|string
	 */
	private $post_name = NULL;

	/**
	 * @param null $id        ID
	 * @param null $post_name 名称
	 */
	public function __construct($id = NULL, $post_name = NULL){
		$this->db = db();
		if($id !== NULL){
			$this->post_id = intval($id);
		}
		if($post_name !== NULL){
			$this->post_name = trim($post_name);
		}
	}

	/**
	 * @var int[] 分页统计信息
	 */
	private $count = [
		'page' => 1,
		'max' => 1,
		'count' => 0,
		'number' => 20,
	];

	/**
	 * 设置分页信息
	 * @param int $page
	 * @param int $number
	 */
	public function setPager($page, $number = 20){
		$page = intval($page);
		$number = intval($number);
		$this->count['page'] = $page > 0 ? $page : 1;
		$this->count['number'] = $number > 5 ? ($number > 100 ? 100 : $number) : 5;
	}

	/**
	 * @param $user_id
	 */
	public function delete($user_id){
		$user_id = intval($user_id);
		if($user_id < 1){
			if(!login_user()->Permission('Control')){
				$this->throwMsg(-4);
			}
		}
		if(!$this->existsCheck($this->post_id, $user_id)){
			$this->throwMsg(-5);
		}
		$rt = $this->db->delete("posts", ['id' => $this->post_id]);
		if($rt < 1){
			Log::write(_("delete post error."), Log::SQL);
			$this->throwMsg(-6);
		}
	}

	/**
	 * @param null $id
	 * @param null $user_id
	 * @return bool
	 */
	public function existsCheck($id = NULL, $user_id = NULL){
		if($id === NULL){
			$id = $this->post_id;
		}
		if($id < 1){
			return false;
		}
		if($user_id > 0){
			return $this->db->has("posts", [
				'AND' => [
					'id' => $id,
					'users_id' => $user_id
				]
			]);
		}
		return $this->db->has("posts", ['id' => $id]);
	}

	/**
	 * @param int $uid 等于0为全部，需权限，-1为公共全部，大于0为用户
	 * @return bool
	 */
	private function getCountInfo($uid){
		$count = 0;
		if($uid === 0 && User::getUser($uid)->Permission('Control')){
			$count = $this->db->count("posts");
		} elseif($uid === -1){
			$count = $this->db->count("posts", ['post_status' => 1]);
		} else{
			$count = $this->db->count("posts", ['users_id' => $uid]);
		}
		$this->count['count'] = $count;
		$this->count['max'] = intval(ceil($count / $this->count['number']));
		if($this->count['page'] > $this->count['max']){
			$this->count['page'] = -1;
			return false;
		}
		return true;
	}

	/**
	 * @return \int[]
	 */
	public function getCount(){
		return $this->count;
	}

	/**
	 * @return \ULib\User
	 */
	public function getPostUser(){
		return $this->post_user;
	}

	/**
	 * 获取文章的内容
	 * @return array|false|null
	 */
	public function getInfo(){
		if(!is_array($this->info)){
			if($this->post_id > 0){
				$where = ['posts.id' => $this->post_id];
			} else if(strlen($this->post_name) > 0){
				$where = ['posts.post_name' => $this->post_name];
			} else{
				return false;
			}
			$info = $this->db->select("posts", ['[><]users' => ['users_id' => 'id']], [
				'posts.id' => 'post_id',
				'posts.post_title' => 'post_title',
				'posts.users_id' => 'post_users_id',
				'posts.post_name' => 'post_name',
				'posts.post_time' => 'post_time',
				'posts.post_content' => 'post_content',
				'posts.post_update_time' => 'post_update_time',
				'posts.post_category' => 'post_category',
				'posts.post_status' => 'post_status',
				'posts.post_description' => 'post_description',
				'posts.post_keyword' => 'post_keyword',
				'posts.post_comment_count' => 'post_comment_count',
				'posts.post_allow_comment' => 'post_allow_comment',
				'users.id' => 'user_id',
				'users.user_name' => 'user_name',
				'users.user_aliases' => 'user_aliases',
				'users.user_email' => 'user_email',
				'users.user_url' => 'user_url',
				'users.user_status' => 'user_status',
				'users.user_registered_time' => 'user_registered_time',
				'users.user_last_login_time' => 'user_last_login_time',
				'users.user_avatar' => 'user_avatar',
			], $where);
			if(isset($info[0]['post_id'])){
				$user_info = [];
				$this->info = [];
				foreach($info[0] as $k => $v){
					if($k[0] === 'u'){
						$user_info[substr($k, 5)] = $v;
					} else{
						$this->info[$k] = $v;
					}
				}
				//从堆栈获取用户
				$this->post_user = User::UserStack(+$user_info['id']);
				if(!is_object($this->post_user)){
					$this->post_user = new User($user_info, true);
				}
			}
		}
		return $this->info;
	}

	/**
	 * @param $uid
	 * @return array|bool
	 */
	public function getList($uid){
		if(!$this->getCountInfo($uid)){
			return [];
		}
		$rt = $this->db->select("posts", [
			'id',
			'post_title',
			'post_name',
			'post_time',
			'post_update_time',
			'post_category',
			'post_status',
			'post_comment_count'
		], ['users_id' => $uid]);
		if($rt === false){
			Log::write(_("Select post list error."), Log::SQL);
			return [];
		}
		return $rt;
	}

	public function getPublicList(){
		if(!$this->getCountInfo(-1)){
			return [];
		}
		$rt = $this->db->select("posts", [
			'id',
			'post_title',
			'post_name',
			'post_time',
			'post_update_time',
			'post_category',
			'post_status',
			'post_content',
			'post_comment_count'
		], [
			'post_status' => 1,
			'ORDER' => 'id DESC',
			'LIMIT' => [
				($this->count['page'] - 1) * $this->count['number'],
				$this->count['number']
			]
		]);
		if($rt === false){
			Log::write(_("Select public post list error."), Log::SQL);
			return [];
		}
		return $rt;
	}

	/**
	 * @param $title
	 * @param $name
	 * @param $users_id
	 * @return array
	 */
	public function create($title, $name, $users_id){
		$title = trim($title);
		$name = trim($name);
		$this->checkTitle($title);
		$this->checkName($name);
		if($this->existsName($name)){
			$this->throwMsg(-7);
		}
		User::getUser($users_id);
		$data = [
			'post_title' => $title,
			'post_name' => $name,
			'post_time' => date("Y-m-d H:i:s"),
			'post_category' => $this->getCategory(0),
			'post_status' => 0,
			'post_update_time' => date("Y-m-d H:i:s"),
			'post_content' => '',
			'users_id' => $users_id
		];
		$insert = $this->db->insert("posts", $data);
		if($insert < 1){
			Log::write(_("Insert post content error."), Log::SQL);
			$this->throwMsg(-3);
		}
		$data['id'] = $insert;
		return $data;
	}

	/**
	 * 获取分类列表
	 * @param null|int $index
	 * @return array|string
	 */
	public function getCategory($index = NULL){
		static $rt = NULL;
		if($rt === NULL){
			$rt = hook()->apply('Post_getCategory', [
				'Default',
				'Notice',
				'News'
			]);
		}
		if($index !== NULL && isset($rt[$index])){
			return $rt[$index];
		}
		return $rt;
	}

	public function update($title, $name, $content, $category, $keyword, $description, $status, $allow_comment, $user_id){
		if(!$this->existsCheck($this->post_id, $user_id)){
			$this->throwMsg(-5);
		}
		$title = trim($title);
		$name = trim($name);
		$content = trim($content);
		$this->checkTitle($title);
		$this->checkName($name);
		if($this->existsName($name, $this->post_id)){
			$this->throwMsg(-9);
		}
		if(empty($content)){
			$this->throwMsg(-8);
		}
		if(!in_array($category, $this->getCategory())){
			$this->throwMsg(-10);
		}
		if($status < 0 || $status > 1){
			$this->throwMsg(-11);
		}
		$data = [
			'post_name' => $name,
			'post_title' => $title,
			'post_content' => $content,
			'post_category' => $category,
			'post_keyword' => $keyword,
			'post_description' => $description,
			'post_status' => $status,
			'post_update_time' => date("Y-m-d H:i:s"),
			'post_allow_comment' => $allow_comment > 0 ? 1 : 0
		];
		if($this->db->update('posts', $data, ['id' => $this->post_id]) < 0){
			Log::write(_("Update post error.") . Log::SQL);
			$this->throwMsg(-12);
		}
	}

	/**
	 * @param $title
	 */
	private function checkTitle($title){
		if(empty($title)){
			$this->throwMsg(-1);
		}
	}

	/**
	 * @param $name
	 */
	private function checkName($name){
		if(preg_match('/^[a-zA-Z0-9]+[a-zA-Z0-9_-]*$/', $name) !== 1){
			$this->throwMsg(-2);
		}
	}

	/**
	 * 检测名称是否存储，当$pid不为空时，排除$pid的那一栏
	 * @param string $name
	 * @param int    $pid
	 * @return bool
	 */
	private function existsName($name, $pid = NULL){
		if($pid > 0){
			return $this->db->has("posts", [
				'AND' => [
					'post_name' => $name,
					'id[!]' => $pid
				]
			]);
		}
		return $this->db->has("posts", ['post_name' => $name]);
	}

	/**
	 * 获取异常信息
	 * @param int $code
	 * @return mixed
	 */
	public function getMsg($code){
		switch(intval($code)){
			case -1:
				return _("Post title can no be empty.");
			case -2:
				return _("Post name check error.");
			case -3:
				return _("Post create error.");
			case -4:
				return _("You do not have permission.");
			case -5:
				return _("The post is not exists.");
			case -6:
				return _("Delete post error.");
			case -7:
				return _("This name is exists.");
			case -8:
				return _("Post content can not be empty!");
			case -9:
				return _("This post name is exists, please try for another.");
			case -10:
				return _("This post category is not exists.");
			case -11:
				return _("This post status code is error.");
			case -12:
				return _("Update post error.");
		}
		return _("Unknown error.");
	}

}