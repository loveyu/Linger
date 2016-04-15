<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-3-15
 * Time: 下午7:22
 * Filename: CommentManagement.php
 */

namespace ULib;

use Core\Log;


/**
 * 评论管理类
 * Class CommentManagement
 * @package ULib
 */
class CommentManagement extends AppException{
	/**
	 * @var \CLib\Sql
	 */
	private $db;

	/**
	 * @var string[] 评论关系表列表
	 */
	private $type_list = [
		'pictures' => 'pictures_has_comments',
		'gallery' => 'gallery_has_comments',
		'posts' => 'posts_has_comments'
	];

	private static $instance;

	/**
	 * 构造函数
	 */
	function __construct(){
		$this->db = db();
		if(self::$instance === NULL){
			self::$instance = $this;
		}
	}


	/**
	 * 获取对类型的表名称
	 * @param string $type
	 * @return bool|string
	 */
	public function getTable($type){
		if(isset($this->type_list[$type])){
			return $this->type_list[$type];
		} else{
			return false;
		}
	}

	/**
	 * @return \ULib\CommentManagement
	 */
	public static function getInstance(){
		if(self::$instance === NULL){
			self::$instance = new CommentManagement();
		}
		return self::$instance;
	}

	/**
	 * 获取评论类型信息
	 * @param string    $type
	 * @param int|int[] $id
	 * @return array|false
	 */
	public function getCommentTypeInfo($type, $id){
		if(!isset($this->type_list[$type])){
			return false;
		}
		$rt = [];
		switch($type){
			case 'pictures':
				$s = $this->db->select("pictures", [
					'id',
					'pic_name',
					'users_id'
				], ['id' => $id]);
				if(isset($s[0]['users_id'])){
					foreach($s as $v){
						$i = [];
						$i['title'] = ($v['pic_name'] ? : ___("Number of ") . $v['id']) . " [" . ___("PICTURE") . "]";
						$i['link'] = picture_link($v['id']);
						$i['user_id'] = $v['users_id'];
						$rt[$v['id']] = $i;
					}
				}
				break;
			case 'gallery':
				$s = $this->db->select("gallery", [
					'id',
					'gallery_title',
					'users_id'
				], ['id' => $id]);
				if(isset($s[0]['users_id'])){
					foreach($s as $v){
						$i = [];
						$i['title'] = $v['gallery_title'] . " [" . ___("GALLERY") . "]";
						$i['link'] = gallery_link($v['id']);
						$i['user_id'] = $v['users_id'];
						$rt[$v['id']] = $i;
					}
				}
				break;
			case 'posts':
				$s = $this->db->select("posts", [
					'id',
					'post_title',
					'post_name',
					'users_id'
				], ['id' => $id]);
				if(isset($s[0]['users_id'])){
					foreach($s as $v){
						$i = [];
						$i['title'] = $v['post_title'] . " [" . ___("POST") . "]";
						$i['link'] = post_link($v['post_name']);
						$i['user_id'] = $v['users_id'];
						$rt[$v['id']] = $i;
					}
				}
				break;
			default:
				return false;
		}
		if(!is_array($id)){
			$rt = reset($rt);
			if(empty($rt['title'])){
				Log::write(___("Comment get type data error."));
				return false;
			}
		} else{
			if(($v = reset($id)) === false || !isset($rt[$id[$v]]['user_id'])){
				return false;
			}
		}
		return $rt;
	}

	/**
	 * 获取评论的信息
	 * @param int $id
	 * @return array|bool
	 */
	public function getCommentType($id){
		lib()->load('Comment');
		$join = [];
		$column = [];
		foreach($this->type_list as $k => $v){
			$index = "[>]$v";
			$join[$index] = [];
			$join[$index]['id'] = "comments_id";
			$column[$v . "." . $k . "_id"] = $k . "_id";
		}
		$info = $this->db->select("comments", $join, array_merge([
			'comments.id' => 'comment_id',
			'comments.users_id' => 'user_id',
			'comments.comment_content' => 'comment_content',
			'comments.comment_time' => 'comment_time',
			'comments.comment_parent' => 'comment_parent',
			'comments.comment_status' => 'comment_status',
			'comments.comment_top' => 'comment_top',
			'comments.comment_like_count' => 'comment_like_count',
			'comments.comment_agent' => 'comment_agent',
			'comments.comment_ip' => 'comment_ip',
		], $column), ['comments.id' => $id]);
		if($info === false){
			Log::write(___("Get comment type error."), Log::SQL);
			return false;
		}
		if(!isset($info[0]['user_id'])){
			return false;
		}
		$comment = [];
		$ts = array_keys($this->type_list);
		$type = [];
		foreach($info[0] as $k => $v){
			$index = strpos($k, '_');
			if($index === false){
				continue;
			}
			$n = substr($k, 0, $index);
			if($n == 'comment'){
				$comment[$k] = $v;
			} else if(in_array($n, $ts) && $v > 0){
				$type[$n] = $v;
			}
		}
		return [
			'comment' => new Comment($comment, $info[0]['user_id']),
			'type' => $type
		];
	}

	/**
	 * 删除一条评论，
	 * @param int    $id
	 * @param string $type
	 * @param int    $user_id
	 */
	public function delete($id, $type, $user_id){
		$id = intval($id);
		$type = trim($type);
		if(!isset($this->type_list[$type])){
			$this->throwMsg(-4);
		}
		$user_id = intval($user_id);
		$comment = $this->db->get("comments", [
			'comment_parent',
			'comment_parent_top'
		], [
			'AND' => [
				'id' => $id,
				'users_id' => $user_id
			]
		]);
		if(empty($comment)){
			$this->throwMsg(-3);
		}
		$writer = $this->db->getWriter();
		$writer->pdo->beginTransaction();
		$parent = intval($comment['comment_parent']);
		$parent_top = intval($comment['comment_parent_top']);
		if($parent !== 0){
			//存在上级回复，即非顶级评论，将所有指向它的评论指向它的上级
			if($writer->update("comments", ['comment_parent' => $parent], ['comment_parent' => $id]) === false){
				$writer->pdo->rollBack();
				$this->throwMsg(-1);
			}
		} elseif($parent_top === 0){
			//不存在上级回复，且自身为顶级回复
			//取第一条回复
			$top = $this->db->select('comments', ['id'], [
				'comment_parent_top' => $id,
				'LIMIT' => 1
			]);
			if(isset($top[0])){
				$top = $top[0];
				if($writer->update('comments', [
						'comment_parent_top' => NULL,
						'comment_parent' => NULL
					], ['id' => $top['id']]) === false
				){
					$writer->pdo->rollBack();
					$this->throwMsg(-1);
				}
				if($writer->update('comments', ['comment_parent' => $top['id']], ['comment_parent' => $id]) === false){
					$writer->pdo->rollBack();
					$this->throwMsg(-1);
				}
				if($writer->update('comments', ['comment_parent_top' => $top['id']], ['comment_parent_top' => $id]) === false){
					$writer->pdo->rollBack();
					$this->throwMsg(-1);
				}
			} elseif($top === false){
				$writer->pdo->rollBack();
				$this->throwMsg(-1);
			}
		}
		//针对无法触发DELETE触发器的计数器添加和删除操作
		$this->deleteRelationTable($id, $this->type_list[$type], $writer);
		$rt = $this->db->delete("comments", [
			'AND' => [
				'id' => $id,
				'users_id' => $user_id
			]
		]);
		if($rt === false){
			$writer->pdo->rollBack();
			$this->throwMsg(-1);
		} else if($rt < 1){
			$writer->pdo->rollBack();
			$this->throwMsg(-2);
		}
		$writer->pdo->commit();
	}

	/**
	 * 对某一评论添加一个顶(TOP)
	 * @param int $id
	 * @return int 返回顶的最新数量
	 */
	public function topAdd($id){
		$id = intval($id);
		$db = db();
		if($id > 0){
			$top = $db->get("comments", ['comment_top'], ['id' => $id]);
		}
		if(isset($top['comment_top'])){
			$n = 1 + $top['comment_top'];
			if($db->update("comments", ['comment_top' => $n], ['id' => $id]) < 1){
				$this->throwMsg(-5);
			} else{
				return $n;
			}
		} else{
			$this->throwMsg(-3);
		}
		return 0;
	}

	/**
	 * 获取评论的喜欢人数
	 * @param int $id
	 * @return int
	 */
	public function getCommentLikeCount($id){
		$s = $this->db->get("comments", ['comment_like_count'], ['id' => intval($id)]);
		if(!isset($s['comment_like_count'])){
			return 0;
		} else{
			return 0 + $s['comment_like_count'];
		}
	}

	/**
	 * 取消或者喜欢某一评论
	 * @param int $c_id
	 * @param int $u_id
	 */
	public function like($c_id, $u_id){
		$c_id = intval($c_id);
		$u_id = intval($u_id);
		if($this->db->has("users_like_comments", [
			'AND' => [
				'users_id' => $u_id,
				'comments_id' => $c_id
			]
		])
		){
			if($this->db->delete("users_like_comments", [
					'AND' => [
						'users_id' => $u_id,
						'comments_id' => $c_id
					]
				]) === false
			){
				$this->throwMsg(-6);
			}
			hook()->apply('CommentManagement_unlike', NULL, $c_id, $u_id);
		} else{
			if($this->db->insert("users_like_comments", [
					'users_id' => $u_id,
					'comments_id' => $c_id,
					'like_time' => date("Y-m-d H:i:s")
				]) < 0
			){
				$this->throwMsg(-7);
			}
			hook()->apply('CommentManagement_like', NULL, $c_id, $u_id);
		}
	}

	/**
	 * 针对MYSQL级联操作无法触发级联操作的BUG
	 * @param int    $comment_id 评论ID
	 * @param string $table      对应的数据表
	 * @param \medoo $db_writer  数据库操作的引用
	 */
	private function deleteRelationTable($comment_id, $table, &$db_writer){
		if($db_writer->delete($table, ['comments_id' => $comment_id]) === false){
			$db_writer->pdo->rollBack();
			$this->throwMsg(-1);
		}
	}

	/**
	 * 获取异常
	 * @param int $code
	 * @return string
	 */
	public function getMsg($code){
		switch(intval($code)){
			case -1:
				Log::write(___("Delete comment error.") . implode(",", $this->db->error()['write']), Log::SQL);
				return ___("Delete comment error.");
			case -2:
				return ___("No comment had delete.");
			case -3:
				return ___("Comment is not exists.");
			case -4:
				return ___("Comment type id not defined.");
			case -5:
				return ___("Comment top add error.");
			case -6:
				Log::write(___("Cancel comment like error.") . implode(",", $this->db->error()['write']), Log::SQL);
				return ___("Cancel comment like error.");
			case -7:
				Log::write(___("Like comment error.") . implode(",", $this->db->error()['write']), Log::SQL);
				return ___("Like comment error.");
			default:
				return ___("Unknown error.");
		}
	}

}