<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-3-12
 * Time: 下午12:37
 * Filename: CommentData.php
 */

namespace ULib;


use CLib\Input;
use Core\Log;
use Core\Page;

/**
 * 评论数据输出
 * Class CommentData
 * @package ULib
 */
abstract class CommentData extends Page{
	/**
	 * @var string 评论视图路径
	 */
	private $view_path;
	/**
	 * @var string 视图样式
	 */
	private $view_style;
	/**
	 * @var int 每页显示评论数量
	 */
	private $one_page = 10;
	/**
	 * @var string 默认评论提交API地址
	 */
	protected $action_page = 'CommentApi';
	/**
	 * @var string 评论方法地址
	 */
	protected $action_method = 'post';
	/**
	 * @var string 对应的关系表
	 */
	private $relation_table;
	/**
	 * @var string 关系吧对应的字段
	 */
	private $relation_field;

	/**
	 * @var string 对应的评论类型，用于区分表，针对MYSQL级联BUG
	 */
	private $comment_type;
	/**
	 * @var int 引用的字段ID
	 */
	protected $id = 0;
	/**
	 * @var \CLib\Sql 临时数据库操作
	 */
	protected $db;

	/**
	 * @var int 当前页数
	 */
	protected $page = 0;

	/**
	 * @var array 统计信息
	 */
	private $count_info = [
		'now' => 0,
		'count' => 0,
		'max' => 0
	];

	/**
	 * @var Comment[] 评论列表信息
	 */
	private $comment_top_list = [];

	/**
	 * 构造方法
	 * @param string $table        关系表名
	 * @param string $field        关系表ID
	 * @param int    $relation_id  关系表ID的值
	 * @param string $comment_type 评论类型
	 * @param string $style        视图样式
	 */
	function __construct($table, $field, $relation_id, $comment_type, $style = ''){
		parent::__construct();
		$this->relation_table = trim($table);
		$this->relation_field = trim($field);
		$this->id = intval($relation_id);
		$this->comment_type = trim($comment_type);
		$cfg = cfg();
		$this->view_path = $cfg->get('comment_view_path');
		if(empty($this->view_path)){
			$this->view_path = "Comment";
		}
		if(empty($style)){
			$this->view_style = "";
		} else{
			$this->view_style = $style . "/";
		}
		$this->one_page = comment_one_page();
		$this->db = db();
	}

	/**
	 * 检查是否允许评论
	 * @param int $id
	 * @return bool
	 */
	abstract function checkAllowComment($id);

	/**
	 * 显示数据
	 * @param bool $hidden_form 是否隐藏评论表格
	 */
	public function show($hidden_form){
		$error = NULL;
		try{
			$this->count();
			$this->comment_top_list = $this->parse($this->query());
		} catch(\Exception $ex){
			$error = $ex->getMessage();
		}
		$data = [
			'action' => get_url($this->action_page, $this->action_method),
			'id' => $this->id,
			'hidden_form' => $hidden_form != NULL,
			'count_info' => $this->count_info,
			'error' => $error,
			'type' => $this->comment_type,
			'is_closed' => !$this->checkAllowComment($this->id),
		];
		$this->__view($this->view_path . "/" . $this->view_style . "display.php", $data);
	}

	/**
	 * 输出评论
	 * @param Comment[] $comment_list 评论列表
	 * @param int       $deep         深度
	 */
	public function show_comment($comment_list = NULL, $deep = 1){
		if($comment_list === NULL){
			$comment_list = & $this->comment_top_list;
		}
		$deep = intval($deep);
		if($deep === 1){
			$deep_class = " comment-reply-top";
		} else if($deep > 1 && $deep <= comment_deep()){
			$deep_class = " comment-reply-deep";
		} else{
			$deep_class = "";
		}
		foreach($comment_list as &$v){
			echo "<div id='Comment-id-", $v->getCommentId(), "' class='comment-list$deep_class'>\n";
			$this->__view($this->view_path . "/" . $this->view_style . "comment.php", ['comment' => &$v]);
			$sub = $v->getSubNode();
			if(count($sub) > 0){
				$this->show_comment($sub, $deep + 1);
			}
			echo "</div>\n";
		}
	}

	/**
	 * 生成统计信息
	 */
	private function count(){
		$id = $this->db->quote($this->id);
		$stmt = $this->db->getReader()->query("select count(*) from `comments` inner join `{$this->relation_table}` on `{$this->relation_table}`.`comments_id` = `comments`.`id` where `{$this->relation_table}`.`{$this->relation_field}`= {$id} AND `comments`.`comment_parent_top` is NULL");
		if($stmt === false){
			$this->throwMsg(-1);
		}
		$this->count_info['count'] = +$stmt->fetchColumn();
		if($this->count_info['count'] === 0){
			$this->throwMsg(-10);
		}
		$this->count_info['number'] = $this->one_page;
		$this->count_info['max'] = intval(ceil($this->count_info['count'] / $this->count_info['number']));
		$this->count_info['now'] = abs(intval($this->page));
		if($this->count_info['now'] === 0){
			if(comment_order_desc()){
				//如果倒序排列显示最后一页
				$this->count_info['now'] = $this->count_info['max'];
			} else{
				//顺序排列显示第一页
				$this->count_info['now'] = 1;
			}
		}
		if($this->count_info['now'] > $this->count_info['max']){
			$this->count_info['now'] = $this->count_info['max'] + 1;
			$this->throwMsg(-9);
		}
	}

	/**
	 * 解析的时候子返回顶级评论，回复需单独解析
	 * @param array $data
	 * @return Comment[]
	 */
	private function parse($data){
		//echo "PARSE:" . c()->getTimer()->get_second() . "<br>";
		$this->__lib('Comment');
		/**
		 * @var Comment[] $rt
		 */
		$rt = [];
		for($i = 0, $l = count($data); $i < $l; $i++){
			$user_info = [];
			$comment = [];
			foreach($data[$i] as $k => $v){
				$index = strpos($k, '_');
				switch($index){
					case 4:
						$user_info[substr($k, 5)] = $v;
						break;
					case 7:
						$comment[$k] = $v;
						break;
				}
			}
			//从堆栈获取用户
			$user = User::UserStack(+$user_info['id']);
			if(!is_object($user)){
				$user = new User($user_info, true);
			}
			$rt[$comment['comment_id']] = new Comment($comment, $user->getId());
		}
		$top = [];
		foreach(array_keys($rt) as $v){
			$parent = $rt[$v]->getCommentParent();
			if($parent == 0){
				$top[] = & $rt[$v];
			} else{
				$rt[$parent]->createSubNode(intval($v));
			}
		}
		//		echo "PARSE END:" . c()->getTimer()->get_second() . "<br>";
		return $top;
	}

	/**
	 * 针对数据查询
	 * 要求返回数据确保评论字段的存在，以便排序
	 * @return array
	 */
	private function query(){
		//echo "QUERY:" . c()->getTimer()->get_second() . "<br>";
		if(!isset($this->count_info['max'])){
			$this->throwMsg(-7);
		}
		$tops = $this->db->select("comments", [
			'[><]' . $this->relation_table => ['id' => 'comments_id']
		], [
			'comments.id' => 'id'
		], [
			'AND' => [
				$this->relation_table . "." . $this->relation_field => $this->id,
				'comments.comment_parent_top' => NULL
			],
			'LIMIT' => [
				$this->count_info['number'] * ($this->count_info['now'] - 1),
				$this->count_info['number']
			]
		]);
		$ids = [];
		foreach($tops as $v){
			$ids[] = +$v['id'];
		}
		$list = $this->db->select("comments", [
			'[><]users' => ['users_id' => 'id'],
			'[>]users_like_comments' => [
				'id' => 'comments_id',
				'______' => ['users_like_comments.users_id' => is_login() ? login_user()->getId() : 0]
			]
		], [
			'users.id' => 'user_id',
			'users.user_name' => 'user_name',
			'users.user_aliases' => 'user_aliases',
			'users.user_email' => 'user_email',
			'users.user_url' => 'user_url',
			'users.user_status' => 'user_status',
			'users.user_registered_time' => 'user_registered_time',
			'users.user_last_login_time' => 'user_last_login_time',
			'users.user_avatar' => 'user_avatar',
			'comments.id' => 'comment_id',
			'comments.comment_content' => 'comment_content',
			'comments.comment_time' => 'comment_time',
			'comments.comment_parent' => 'comment_parent',
			'comments.comment_status' => 'comment_status',
			'comments.comment_top' => 'comment_top',
			'comments.comment_like_count' => 'comment_like_count',
			'comments.comment_agent' => 'comment_agent',
			'comments.comment_ip' => 'comment_ip',
			'users_like_comments.like_time' => 'comment_like_time'
		], [
			'OR' => [
				'comments.id' => $ids,
				'comments.comment_parent_top' => $ids
			],
			'ORDER' => 'comments.id ' . (comment_order_desc() ? "DESC" : "ASC")
		]);
		if($list === false){
			$this->throwMsg(-8);
			Log::write(implode(",", $this->db->error()['read']), Log::SQL);
		}
		//		print_r($this->db->last_query());
		//		var_dump($list);
		//		echo "QUERY END:" . c()->getTimer()->get_second() . "<br>";
		return $list;
	}

	/**
	 * 提交评论
	 * @param string $content
	 * @param int    $reply
	 * @param int    $user_id
	 * @return int
	 */
	public function comment($content, $reply, $user_id){
		if(!$this->checkAllowComment($this->id)){
			$this->throwMsg(-11);
		}
		$user = User::getUser($user_id);
		$content = $this->comment_check($content);
		$reply = intval($reply);
		if($reply < 0){
			$this->throwMsg(-3);
		}
		$top = 0;
		if($reply > 0){
			$reply_info = $this->reply_comment_info($reply);
			if(!isset($reply_info['users_id'])){
				$this->throwMsg(-4);
			}
			$top = intval($reply_info['comment_parent_top']);
			if($top === 0 && $reply_info['comment_parent'] == 0){
				$top = $reply;
			}
		}
		return $this->insert_comment($content, $reply, $top, $user->getId());
	}

	/**
	 * 插入评论内容
	 * @param string $content
	 * @param int    $parent
	 * @param int    $parent_top
	 * @param int    $user_id
	 * @return int
	 */
	private function insert_comment($content, $parent, $parent_top, $user_id){
		c_lib()->load('input');
		$input = new Input();
		$ip = $input->getIp()->binIp();
		$ua = $input->getUA();
		$write = $this->db->getWriter();
		$write->pdo->beginTransaction();
		$info = [
			'users_id' => $user_id,
			'comment_content' => $content,
			'comment_time' => $input->time(),
			'comment_parent' => $parent ? $parent : NULL,
			'comment_parent_top' => $parent_top ? $parent_top : NULL,
			'comment_ip' => $ip,
			'comment_agent' => $ua
		];
		$info['comment_status'] = $this->get_comment_status($info);
		$comment_id = $write->insert("comments", $info);
		if($comment_id < 1){
			$write->pdo->rollBack();
			Log::write(implode(",", $write->error()), Log::SQL);
			$this->throwMsg(-5);
		}
		$has_flag = $write->insert($this->relation_table, [
			'comments_id' => $comment_id,
			$this->relation_field => $this->id,
			'users_id' => $user_id,
			'object_users_id' => $this->get_object_users_id()
		]);
		if($has_flag < 0){
			$write->pdo->rollBack();
			Log::write(implode(",", $write->error()), Log::SQL);
			$this->throwMsg(-6);
		}
		$write->pdo->commit();
		$rt = $this->comment_hook($content, $parent, $parent_top, $user_id);
		if($parent != 0){
			hook()->apply('Comment_reply', $rt, $this->comment_type, $this->id, $user_id, $parent, $parent_top, $content);
		}
		return $comment_id;
	}

	/**
	 * 获取该条评论状态的抽象函数
	 * @param array $info 评论数组信息
	 * @return int
	 */
	abstract function get_comment_status($info);

	/**
	 * 返回评论页面的固定连接
	 * @param int $number
	 * @return string
	 */
	abstract function get_comment_pager($number);

	/**
	 * 对评论可添加的抽象钩子调用
	 * @param string $content    评论内容
	 * @param int    $parent     回复ID
	 * @param int    $parent_top 顶级ID
	 * @param int    $user_id    用户ID
	 * @return mixed 返回值
	 */
	abstract function comment_hook($content, $parent, $parent_top, $user_id);

	/**
	 * 获取该对象的用户ID
	 * @return int
	 */
	abstract function get_object_users_id();

	/**
	 * 获取回复的评论的判断信息
	 * @param int $reply
	 * @return array|bool
	 */
	private function reply_comment_info($reply){
		$info = $this->db->get("comments", [
			'users_id',
			'comment_content',
			'comment_time',
			'comment_parent',
			'comment_status',
			'comment_top',
			'comment_parent_top'
		], ['id' => $reply]);
		return $info;
	}

	/**
	 * 对评论内容进行检测，并过滤
	 * @param string $comment
	 * @return string
	 */
	private function comment_check($comment){
		$comment = trim($comment);
		if(strlen($comment) < 1){
			$this->throwMsg(-2);
		}
		return hook()->apply("CommentData_comment_check", $comment);
	}

	/**
	 * 抛出异常
	 * @param int $code
	 * @throws \Exception
	 */
	protected function throwMsg($code){
		$code = intval($code);
		switch($code){
			case -1:
				$msg = _("Comment relation table error.");
				break;
			case -2:
				$msg = _("Comment data is empty.");
				break;
			case -3:
				$msg = _("Reply id is error.");
				break;
			case -4:
				$msg = _("Reply comment is not exists.");
				break;
			case -5:
				$msg = _("Comment error on data.");
				break;
			case -6:
				$msg = _("Comment error on table.");
				break;
			case -7:
				$msg = _("Comment can't count.");
				break;
			case -8:
				$msg = _("Comment load error.");
				break;
			case -9:
				$msg = _("Comment page not found.");
				break;
			case -10:
				$msg = _("Oh, no comments yet, you can write something at now.");
				break;
			case -11:
				$msg = _("Comment are closed.");
				break;
			default:
				$msg = _("Unknown error");
		}
		throw new \Exception($msg, $code);
	}
} 