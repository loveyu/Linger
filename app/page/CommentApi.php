<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-3-12
 * Time: 下午10:20
 * Filename: CommentApi.php
 */

namespace UView;


use Core\Page;
use ULib\CommentManagement;
use ULib\GalleryComment;
use ULib\PictureComment;
use ULib\PostComment;

/**
 * 评论操作API
 * Class CommentApi
 * @package UView
 */
class CommentApi extends Page{
	/**
	 * @var array 最终用户返回消息
	 */
	private $rt_msg = [
		'status' => false,
		'code' => NULL,
		'msg' => '',
		'content' => NULL
	];

	/**
	 * 发送状态头
	 */
	public function __construct(){
		parent::__construct();
		header('Content-type: application/json; Charset=utf-8');
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
	}

	public function post_picture(){
		try{
			$this->throwMsgCheck('is_login', 'is_post', 'is_active');
			$this->__lib("PictureComment");
			//转义评论
			$req = req()->_plain();
			$pc = new PictureComment($req->post('id'));
			$this->rt_msg['content'] = $pc->comment($req->post('comment'), $req->post('reply'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['status'] = false;
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function post_gallery(){
		try{
			$this->throwMsgCheck('is_login', 'is_post', 'is_active');
			$this->__lib("GalleryComment");
			//转义评论
			$req = req()->_plain();
			$pc = new GalleryComment($req->post('id'));
			$this->rt_msg['content'] = $pc->comment($req->post('comment'), $req->post('reply'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['status'] = false;
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function top(){
		try{
			$this->throwMsgCheck('is_login', 'is_post', 'is_active');
			$this->__lib("CommentManagement");
			$req = req();
			$cm = new CommentManagement();
			$this->rt_msg['content'] = $cm->topAdd($req->post('id'));
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['status'] = false;
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function like(){
		try{
			$this->throwMsgCheck('is_login', 'is_post', 'is_active');
			$this->__lib("CommentManagement");
			$req = req();
			$cm = new CommentManagement();
			$cm->like($req->post('id'), login_user()->getId());
			//不返回最新数据，直接使用JS操作
			//$this->rt_msg['content'] = $cm->getCommentLikeCount($req->post('id'));
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['status'] = false;
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function post(){
		try{
			$this->throwMsgCheck('is_login', 'is_post');
			$this->rt_msg['msg'] = "没有任何类型的评论使用默认评论接口！";
		} catch(\Exception $ex){
			$this->rt_msg['status'] = false;
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function post_post(){
		try{
			$this->throwMsgCheck('is_login', 'is_post', 'is_active');
			$this->__lib("PostComment");
			//转义评论
			$req = req()->_plain();
			$pc = new PostComment($req->post('id'));
			$this->rt_msg['content'] = $pc->comment($req->post('comment'), $req->post('reply'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['status'] = false;
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	public function delete(){
		try{
			$this->throwMsgCheck('is_post', 'is_login');
			$this->__lib('CommentManagement');
			$cm = new CommentManagement();
			$cm->delete(req()->post('id'), req()->post('type'), login_user()->getId());
			$this->rt_msg['status'] = true;
		} catch(\Exception $ex){
			$this->rt_msg['status'] = false;
			$this->rt_msg['msg'] = $ex->getMessage();
			$this->rt_msg['code'] = $ex->getCode();
		}
	}

	/**
	 * 常规检查
	 * @param string $str
	 * @throws \Exception
	 */
	private function throwMsgCheck($str){
		$msg = NULL;
		foreach(func_get_args() as $v){
			switch($v){
				case "is_login":
					is_login() or $msg = "你必须登录才能操作";
					break;
				case "is_post":
					req()->is_post() or $msg = "必须使用POST请求访问";
					break;
				case "is_get":
					req()->is_get() or $msg = "必须使用GET请求访问";
					break;
				case "is_active":
					is_login() && login_user()->is_active() or $msg = "必须的激活用户才能访问";
					break;
				default:
					$msg = "未知异常信息";
			}
			if($msg !== NULL){
				throw new \Exception($msg);
			}
		}
	}

	/**
	 * 析构方法，输出JSON数据
	 */
	function __destruct(){
		echo json_encode($this->rt_msg);
	}
} 