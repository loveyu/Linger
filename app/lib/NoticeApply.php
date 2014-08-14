<?php
/**
 * Created by Loveyu.
 * User: loveyu
 * Date: 14-3-29
 * Time: 下午1:06
 * Filename: NoticeApply.php
 */

namespace ULib;

use CLib\Input;
use Core\Log;

lib()->load('Notice', 'Message', 'MailTemplate');

/**
 * 针对提醒功能的应用
 * Class NoticeApply
 * @package ULib
 */
class NoticeApply{

	/**
	 * @var Notice[]
	 */
	private $list;

	/**
	 * @var Message
	 */
	private $message;

	/**
	 * 构造方法，添加消息提醒的钩子
	 */
	function __construct(){
		$this->message = new Message();
		$hook = hook();
		$hook->add('FollowManagement_follow', [
			$this,
			'mail_follow_me'
		]);
		$hook->add('FollowManagement_follow', [
			$this,
			'message_follow_me'
		]);
		$hook->add('FollowManagement_follow_gallery', [
			$this,
			'mail_follow_gallery'
		]);
		$hook->add('FollowManagement_follow_gallery', [
			$this,
			'message_follow_gallery'
		]);
		$hook->add('PictureComment_comment', [
			$this,
			'mail_comment_picture'
		]);
		$hook->add('PictureComment_comment', [
			$this,
			'message_comment_picture'
		]);
		$hook->add('GalleryComment_comment', [
			$this,
			'mail_comment_gallery'
		]);
		$hook->add('GalleryComment_comment', [
			$this,
			'message_comment_gallery'
		]);
		$hook->add('Comment_reply', [
			$this,
			'mail_comment_reply'
		]);
		$hook->add('Comment_reply', [
			$this,
			'message_comment_reply'
		]);
		$hook->add('Picture_like', [
			$this,
			'mail_like_pic'
		]);
		$hook->add('Picture_like', [
			$this,
			'message_like_pic'
		]);
		$hook->add('Gallery_like', [
			$this,
			'mail_like_gallery'
		]);
		$hook->add('Gallery_like', [
			$this,
			'message_like_gallery'
		]);
		$hook->add('CommentManagement_like', [
			$this,
			'mail_like_comment'
		]);
		$hook->add('CommentManagement_like', [
			$this,
			'message_like_comment'
		]);
		$hook->add('Message_send_success', [
			$this,
			'mail_send_message'
		]);
		$hook->add('Message_send_success', [
			$this,
			'mail_send_system_message'
		]);
		$hook->add('UserLogin_PostLogin_Success', [
			$this,
			'mail_exception_login'
		]);
		$hook->add('UserLogin_PostLogin_Success', [
			$this,
			'message_exception_login'
		]);
		$hook->add('UserLogin_PostLogin_restrictions', [
			$this,
			'mail_login_restrictions'
		]);
		$hook->add('UserLogin_PostLogin_restrictions', [
			$this,
			'message_login_restrictions'
		]);
	}

	/**
	 * 提醒参数判断
	 * @param int    $uid  用户ID
	 * @param string $type 消息类型
	 * @param string $name 提醒名称
	 * @return bool
	 */
	private function notice($uid, $type, $name){
		$uid = intval($uid);
		if(!isset($this->list[$uid])){
			try{
				//当用户不存在时，Notice会抛出异常信息
				$this->list[$uid] = new Notice(User::getUser($uid)->getId());
			} catch(\Exception $ex){
				Log::write(_("NoticeApply notice create ex.") . $ex->getMessage(), Log::NOTICE);
				unset($this->list[$uid]);
				return false;
			}
		}
		switch(strtolower($type)){
			case "mail":
				return $this->list[$uid]->getOptionMail($name);
			case "message":
				return $this->list[$uid]->getOptionMessage($name);
		}
		return false;
	}

	public function mail_comment_picture($rt, $pic_id, $user_id, $parent, $parent_top, $content, $picture_info){
		try{
			unset($parent, $parent_top); //未使用
			$pic_user = $picture_info['user_id'];
			if($pic_user == $user_id){
				//自己评论时不发送
				return $rt;
			}
			if(!$this->notice($pic_user, 'mail', 'comment_picture')){
				return $rt;
			}
			$user = User::getUser($pic_user);
			$comment_user = User::getUser($user_id);
			$mt = new MailTemplate("mail_notice/comment_picture.html");
			$mt->setUserInfo($user->getInfo());
			$mt->setValues([
				'comment_name' => $comment_user->getAliases(),
				'picture_name' => $picture_info['pic_name'] ? : "Number " . $pic_id,
				'comment_user_url' => user_link($comment_user->getName()),
				'picture_display_url' => $picture_info['pic_display_url'],
				'picture_page' => picture_link($pic_id),
				'comment_content' => get_markdown($content)
			]);
			$mt->mailSend($user->getName(), $user->getEmail());
		} catch(\Exception $ex){
			Log::write(_("NoticeApply comment_picture create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}

	public function message_comment_picture($rt, $pic_id, $user_id, $parent, $parent_top, $content, $picture_info){
		try{
			unset($parent, $parent_top); //未使用
			$pic_user = $picture_info['user_id'];
			if($pic_user == $user_id){
				//自己评论时不发送
				return $rt;
			}
			if(!$this->notice($pic_user, 'message', 'comment_picture')){
				return $rt;
			}
			$user = User::getUser($pic_user);
			$comment_user = User::getUser($user_id);
			$mt = new MailTemplate("message_notice/comment_picture.md");
			$mt->setUserInfo($user->getInfo());
			$mt->setValues([
				'comment_name' => $comment_user->getAliases(),
				'picture_name' => $picture_info['pic_name'] ? : "Number " . $pic_id,
				'comment_user_url' => user_link($comment_user->getName()),
				'picture_display_url' => $picture_info['pic_display_url'],
				'picture_page' => picture_link($pic_id),
				'comment_content' => $content
			]);
			$this->message->addNoticeMsg($mt->getTitle(), $mt->getContent(), $user->getId());
		} catch(\Exception $ex){
			Log::write(_("NoticeApply message_comment_picture create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}

	public function mail_comment_gallery($rt, $gallery_id, $user_id, $parent, $parent_top, $content, $gallery_info){
		try{
			unset($parent, $parent_top); //未使用
			$g_user = $gallery_info['user_id'];
			if($g_user == $user_id){
				//自己评论时不发送
				return $rt;
			}
			if(!$this->notice($g_user, 'mail', 'comment_gallery')){
				return $rt;
			}
			$user = User::getUser($g_user);
			$comment_user = User::getUser($user_id);
			$mt = new MailTemplate("mail_notice/comment_gallery.html");
			$mt->setUserInfo($user->getInfo());
			$mt->setValues([
				'comment_name' => $comment_user->getAliases(),
				'gallery_title' => $gallery_info['gallery_title'],
				'gallery_page' => gallery_link($gallery_id),
				'comment_user_url' => user_link($comment_user->getName()),
				'comment_content' => get_markdown($content)
			]);
			$mt->mailSend($user->getName(), $user->getEmail());
		} catch(\Exception $ex){
			Log::write(_("NoticeApply mail_comment_gallery create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}

	public function message_comment_gallery($rt, $gallery_id, $user_id, $parent, $parent_top, $content, $gallery_info){
		try{
			unset($parent, $parent_top); //未使用
			$g_user = $gallery_info['user_id'];
			if($g_user == $user_id){
				//自己评论时不发送
				return $rt;
			}
			if(!$this->notice($g_user, 'message', 'comment_gallery')){
				return $rt;
			}
			$user = User::getUser($g_user);
			$comment_user = User::getUser($user_id);
			$mt = new MailTemplate("message_notice/comment_gallery.md");
			$mt->setUserInfo($user->getInfo());
			$mt->setValues([
				'comment_name' => $comment_user->getAliases(),
				'gallery_title' => $gallery_info['gallery_title'],
				'gallery_page' => gallery_link($gallery_id),
				'comment_user_url' => user_link($comment_user->getName()),
				'comment_content' => $content
			]);
			$this->message->addNoticeMsg($mt->getTitle(), $mt->getContent(), $user->getId());
		} catch(\Exception $ex){
			Log::write(_("NoticeApply message_comment_gallery create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}

	public function mail_comment_reply($rt, $comment_type, $the_id, $user_id, $parent, $parent_top, $content){
		try{
			if(!is_array($rt)){
				lib()->load('CommentManagement');
				$rt = CommentManagement::getInstance()->getCommentType($parent);
			}
			if(!isset($rt['comment']) || !isset($rt['type']) || count($rt['type']) !== 1){
				return $rt;
			}
			/**
			 * @var Comment $comment ;
			 */
			$comment = & $rt['comment'];
			if($comment->getUser()->getId() != $user_id && $this->notice($comment->getUser()->getId(), 'mail', 'comment_reply')){
				$reply_user = User::getUser($user_id);
				if(!isset($rt['data_info'])){
					$data_info = CommentManagement::getInstance()->getCommentTypeInfo($comment_type, $the_id);
					$rt['data_info'] = & $data_info;
				} else{
					$data_info = $rt['data_info'];
				}
				if(!isset($data_info['user_id'])){
					return $rt;
				}
				$mt = new MailTemplate("mail_notice/reply_comment.html");
				$mt->setUserInfo($comment->getUser()->getInfo());
				$mt->setValues([
					'post_title' => $data_info['title'],
					'post_link' => $data_info['link'],
					'comment_content' => get_markdown($comment->getCommentContent()),
					'comment_like_count' => $comment->getCommentLikeCount(),
					'comment_time' => $comment->getCommentTime(),
					'reply_user_name' => $reply_user->getAliases(),
					'reply_user_url' => user_link($reply_user->getName()),
					'reply_comment' => get_markdown($content),
				]);
				$mt->mailSend($comment->getUser()->getName(), $comment->getUser()->getEmail());
				//对顶级评论处理，待添加
				//TODO
				unset($parent_top);
			}
		} catch(\Exception $ex){
			Log::write(_("NoticeApply mail_comment_reply create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}

	public function message_comment_reply($rt, $comment_type, $the_id, $user_id, $parent, $parent_top, $content){
		try{
			if(!is_array($rt)){
				lib()->load('CommentManagement');
				$rt = CommentManagement::getInstance()->getCommentType($parent);
			}
			if(!isset($rt['comment']) || !isset($rt['type']) || count($rt['type']) !== 1){
				return $rt;
			}
			/**
			 * @var Comment $comment ;
			 */
			$comment = & $rt['comment'];
			if($comment->getUser()->getId() != $user_id && $this->notice($comment->getUser()->getId(), 'message', 'comment_reply')){
				$reply_user = User::getUser($user_id);
				if(!isset($rt['data_info'])){
					$data_info = CommentManagement::getInstance()->getCommentTypeInfo($comment_type, $the_id);
					$rt['data_info'] = & $data_info;
				} else{
					$data_info = $rt['data_info'];
				}
				if(!isset($data_info['user_id'])){
					return $rt;
				}
				$mt = new MailTemplate("message_notice/reply_comment.md");
				$mt->setUserInfo($comment->getUser()->getInfo());
				$mt->setValues([
					'post_title' => $data_info['title'],
					'post_link' => $data_info['link'],
					'comment_content' => $comment->getCommentContent(),
					'comment_like_count' => $comment->getCommentLikeCount(),
					'comment_time' => $comment->getCommentTime(),
					'reply_user_name' => $reply_user->getAliases(),
					'reply_user_url' => user_link($reply_user->getName()),
					'reply_comment' => $content,
				]);
				$this->message->addNoticeMsg($mt->getTitle(), $mt->getContent(), $comment->getUser()->getId());
				//对顶级评论处理，待添加
				//TODO
				unset($parent_top);
			}
		} catch(\Exception $ex){
			Log::write(_("NoticeApply message_comment_reply create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}

	public function mail_like_pic($rt, $pid, $uid){
		try{
			if(!is_array($rt)){
				lib()->load('Picture');
				$pic = new Picture();
				$rt = $pic->get_simple_pic($pid);
			}
			if(!isset($rt['pic_id']) || $rt['user_id'] == $uid || !$this->notice($rt['user_id'], 'mail', 'like_pic')){
				return $rt;
			}
			$user = User::getUser($rt['user_id']);
			$like_user = User::getUser($uid);
			$mt = new MailTemplate("mail_notice/like_picture.html");
			$mt->setUserInfo($user->getInfo());
			$mt->setValues([
				'like_user_name' => $like_user->getAliases(),
				'picture_name' => $rt['pic_name'] ? : "Number " . $pid,
				'like_user_url' => user_link($like_user->getName()),
				'picture_display_url' => $rt['pic_display_url'],
				'picture_page' => picture_link($pid),
				'like_count' => $rt['pic_like_count']
			]);
			$mt->mailSend($user->getName(), $user->getEmail());
		} catch(\Exception $ex){
			Log::write(_("NoticeApply mail_like_pic create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}

	public function message_like_pic($rt, $pid, $uid){
		try{
			if(!is_array($rt)){
				lib()->load('Picture');
				$pic = new Picture();
				$rt = $pic->get_simple_pic($pid);
			}
			if(!isset($rt['pic_id']) || $rt['user_id'] == $uid || !$this->notice($rt['user_id'], 'message', 'like_pic')){
				return $rt;
			}
			$user = User::getUser($rt['user_id']);
			$like_user = User::getUser($uid);
			$mt = new MailTemplate("message_notice/like_picture.md");
			$mt->setUserInfo($user->getInfo());
			$mt->setValues([
				'like_user_name' => $like_user->getAliases(),
				'picture_name' => $rt['pic_name'] ? : "Number " . $pid,
				'like_user_url' => user_link($like_user->getName()),
				'picture_display_url' => $rt['pic_display_url'],
				'picture_page' => picture_link($pid),
				'like_count' => $rt['pic_like_count']
			]);
			$this->message->addNoticeMsg($mt->getTitle(), $mt->getContent(), $user->getId());
		} catch(\Exception $ex){
			Log::write(_("NoticeApply message_like_pic create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}

	public function mail_like_gallery($rt, $gid, $uid){
		try{
			if(!is_array($rt)){
				lib()->load('Gallery');
				$rt = Gallery::getSimpleInfo($gid);
			}
			if(!isset($rt['gallery_id']) || $rt['users_id'] == $uid || !$this->notice($rt['users_id'], 'mail', 'like_gallery')){
				return $rt;
			}
			$user = User::getUser($rt['users_id']);
			$like_user = User::getUser($uid);
			$mt = new MailTemplate("mail_notice/like_gallery.html");
			$mt->setUserInfo($user->getInfo());
			$mt->setValues([
				'like_user_name' => $like_user->getAliases(),
				'gallery_title' => $rt['gallery_title'],
				'like_user_url' => user_link($like_user->getName()),
				'gallery_page' => gallery_link($gid),
				'like_count' => $rt['gallery_like_count']
			]);
			$mt->mailSend($user->getName(), $user->getEmail());
		} catch(\Exception $ex){
			Log::write(_("NoticeApply mail_like_gallery create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}

	public function message_like_gallery($rt, $gid, $uid){
		try{
			if(!is_array($rt)){
				lib()->load('Gallery');
				$rt = Gallery::getSimpleInfo($gid);
			}
			if(!isset($rt['gallery_id']) || $rt['users_id'] == $uid || !$this->notice($rt['users_id'], 'message', 'like_gallery')){
				return $rt;
			}
			$user = User::getUser($rt['users_id']);
			$like_user = User::getUser($uid);
			$mt = new MailTemplate("message_notice/like_gallery.md");
			$mt->setUserInfo($user->getInfo());
			$mt->setValues([
				'like_user_name' => $like_user->getAliases(),
				'gallery_title' => $rt['gallery_title'],
				'like_user_url' => user_link($like_user->getName()),
				'gallery_page' => gallery_link($gid),
				'like_count' => $rt['gallery_like_count']
			]);
			$this->message->addNoticeMsg($mt->getTitle(), $mt->getContent(), $user->getId());
		} catch(\Exception $ex){
			Log::write(_("NoticeApply message_like_gallery create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}

	public function mail_like_comment($rt, $c_id, $u_id){
		try{
			if(!is_array($rt)){
				lib()->load('CommentManagement');
				$rt = CommentManagement::getInstance()->getCommentType($c_id);
			}
			if(!isset($rt['comment']) || !isset($rt['type']) || count($rt['type']) !== 1){
				return $rt;
			}
			/**
			 * @var Comment $comment ;
			 */
			$comment = & $rt['comment'];
			if($comment->getUser()->getId() != $u_id && $this->notice($comment->getUser()->getId(), 'mail', 'like_comment')){
				$like_user = User::getUser($u_id);
				if(!isset($rt['data_info'])){
					$type = array_keys($rt['type'])[0];
					$data_info = CommentManagement::getInstance()->getCommentTypeInfo($type, $rt['type'][$type]);
					$rt['data_info'] = & $data_info;
				} else{
					$data_info = $rt['data_info'];
				}
				if(!isset($data_info['user_id'])){
					return $rt;
				}
				$mt = new MailTemplate("mail_notice/like_comment.html");
				$mt->setUserInfo($comment->getUser()->getInfo());
				$mt->setValues([
					'post_title' => $data_info['title'],
					'post_link' => $data_info['link'],
					'comment_content' => get_markdown($comment->getCommentContent()),
					'comment_like_count' => $comment->getCommentLikeCount(),
					'comment_time' => $comment->getCommentTime(),
					'like_user_name' => $like_user->getAliases(),
					'like_user_url' => user_link($like_user->getName())
				]);
				$mt->mailSend($comment->getUser()->getName(), $comment->getUser()->getEmail());
			}
		} catch(\Exception $ex){
			Log::write(_("NoticeApply mail_like_comment create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}

	public function message_like_comment($rt, $c_id, $u_id){
		try{
			if(!is_array($rt)){
				lib()->load('CommentManagement');
				$rt = CommentManagement::getInstance()->getCommentType($c_id);
			}
			if(!isset($rt['comment']) || !isset($rt['type']) || count($rt['type']) !== 1){
				return $rt;
			}
			/**
			 * @var Comment $comment ;
			 */
			$comment = & $rt['comment'];
			if($comment->getUser()->getId() != $u_id && $this->notice($comment->getUser()->getId(), 'message', 'like_comment')){
				$like_user = User::getUser($u_id);
				if(!isset($rt['data_info'])){
					$type = array_keys($rt['type'])[0];
					$data_info = CommentManagement::getInstance()->getCommentTypeInfo($type, $rt['type'][$type]);
					$rt['data_info'] = & $data_info;
				} else{
					$data_info = $rt['data_info'];
				}
				if(!isset($data_info['user_id'])){
					return $rt;
				}
				$mt = new MailTemplate("message_notice/like_comment.md");
				$mt->setUserInfo($comment->getUser()->getInfo());
				$mt->setValues([
					'post_title' => $data_info['title'],
					'post_link' => $data_info['link'],
					'comment_content' => $comment->getCommentContent(),
					'comment_like_count' => $comment->getCommentLikeCount(),
					'comment_time' => $comment->getCommentTime(),
					'like_user_name' => $like_user->getAliases(),
					'like_user_url' => user_link($like_user->getName())
				]);
				$this->message->addNoticeMsg($mt->getTitle(), $mt->getContent(), $comment->getUser()->getId());
			}
		} catch(\Exception $ex){
			Log::write(_("NoticeApply message_like_comment create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}

	public function mail_send_message($rt, $data, $msg_id){
		try{
			if($data['from_users_id'] === NULL || !$this->notice($data['to_users_id'], 'mail', 'send_message')){
				//检测是否为系统邮件
				return $rt;
			}
			lib()->load('Markdown');
			$from_user = User::getUser($data['from_users_id']);
			$to_user = User::getUser($data['to_users_id']);
			$mt = new MailTemplate("mail_notice/send_message.html");
			$mt->setUserInfo($to_user->getInfo());
			$mt->setValues([
				'from_user_name' => $from_user->getName(),
				'from_user_aliases' => $from_user->getAliases(),
				'from_user_url' => user_link($from_user->getName()),
				'msg_title' => $data['msg_title'] ? : "无标题信息",
				'msg_content' => Markdown::defaultTransform($data['msg_content']),
				'msg_link' => get_url([
					'Message',
					'view'
				], "?id=$msg_id"),
				'msg_datetime' => $data['msg_datetime']
			]);
			$mt->mailSend($to_user->getName(), $to_user->getEmail());
		} catch(\Exception $ex){
			Log::write(_("NoticeApply mail_send_message create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}

	public function mail_send_system_message($rt, $data, $msg_id){
		try{
			if($data['from_users_id'] !== NULL || !$this->notice($data['to_users_id'], 'mail', 'send_system_message')){
				//检测是否为系统邮件
				return $rt;
			}
			$to_user = User::getUser($data['to_users_id']);
			$mt = new MailTemplate("mail_notice/send_system_message.html");
			$mt->setUserInfo($to_user->getInfo());
			$mt->setValues([
				'msg_title' => $data['msg_title'] ? : "无标题信息",
				'msg_content' => get_markdown($data['msg_content']),
				'msg_link' => get_url([
					'Message',
					'view'
				], "?id=$msg_id"),
				'msg_datetime' => $data['msg_datetime']
			]);
			$mt->mailSend($to_user->getName(), $to_user->getEmail());
		} catch(\Exception $ex){
			Log::write(_("NoticeApply mail_send_system_message create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}

	/**
	 * 异常登录邮件提醒
	 * @param null $rt
	 * @param User $user
	 * @return null
	 */
	public function mail_exception_login($rt, $user){
		try{
			//TODO 待实现

		} catch(\Exception $ex){
			Log::write(_("NoticeApply mail_exception_login create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}

	/**
	 * 异常登录消息提醒
	 * @param null $rt
	 * @param User $user
	 * @return null
	 */
	public function message_exception_login($rt, $user){
		try{
			//TODO 待实现

		} catch(\Exception $ex){
			Log::write(_("NoticeApply message_exception_login create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}

	/**
	 * 登录限制邮件
	 * @param null $rt
	 * @param User $user
	 * @return null
	 */
	public function mail_login_restrictions($rt, $user){
		try{
			if(!$this->notice($user->getId(), 'mail', 'login_restrictions')){
				return $rt;
			}
			$mt = new MailTemplate("mail_notice/login_restrictions.html");
			$mt->setUserInfo($user->getInfo());
			c_lib()->load('input');
			$input = new Input();
			$mt->setValues([
				'login_ip' => $input->getRealIP(),
				'login_ua' => $input->getUA(),
				'login_count' => $user->getErrorLoginCount()
			]);
			$mt->mailSend($user->getName(), $user->getEmail());
		} catch(\Exception $ex){
			Log::write(_("NoticeApply mail_login_restrictions create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}

	/**
	 * 登录限制
	 * @param null $rt
	 * @param User $user
	 * @return null
	 */
	public function message_login_restrictions($rt, $user){
		try{
			if(!$this->notice($user->getId(), 'message', 'login_restrictions')){
				return $rt;
			}
			$mt = new MailTemplate("message_notice/login_restrictions.md");
			$mt->setUserInfo($user->getInfo());
			c_lib()->load('input');
			$input = new Input();
			$mt->setValues([
				'login_ip' => $input->getRealIP(),
				'login_ua' => $input->getUA(),
				'login_count' => $user->getErrorLoginCount()
			]);
			$this->message->addNoticeMsg($mt->getTitle(), $mt->getContent(), $user->getId());
		} catch(\Exception $ex){
			Log::write(_("NoticeApply message_login_restrictions create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}

	public function mail_site_activity_information($rt){
		try{
			//TODO

		} catch(\Exception $ex){
			Log::write(_("NoticeApply mail_site_activity_information create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}

	public function message_site_activity_information($rt){
		try{
			//TODO

		} catch(\Exception $ex){
			Log::write(_("NoticeApply message_site_activity_information create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}


	/**
	 * 发送邮件给关注图集的用户
	 * @param null     $rt    返回参数，此处为空
	 * @param int      $gid   图集ID
	 * @param int      $g_uid 图集用户ID
	 * @param int      $uid   关注的用户ID
	 * @param string[] $data  图集数据
	 * @return null
	 */
	public function mail_follow_gallery($rt, $gid, $g_uid, $uid, $data){
		try{
			if($this->notice($g_uid, 'mail', 'follow_gallery')){
				$user = User::getUser($uid);
				$follow_user = User::getUser($g_uid);
				$mt = new MailTemplate("mail_notice/follow_gallery.html");
				$mt->setUserInfo($follow_user->getInfo());
				if($gid != $data['gallery_id']){
					$data = [];
				}
				$mt->setValues(array_merge($data, [
					'follow_user_aliases' => $user->getAliases(),
					'follow_user_url' => user_link($user->getName()),
					'follow_user_name' => $user->getName(),
					'gallery_page_url' => gallery_link($gid)
				]));
				$mt->mailSend($follow_user->getName(), $follow_user->getEmail());
			}
		} catch(\Exception $ex){
			Log::write(_("NoticeApply mail_follow_gallery create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}

	/**
	 * 发送消息给关注图集的用户
	 * @param null     $rt    返回参数，此处为空
	 * @param int      $gid   图集ID
	 * @param int      $g_uid 图集用户ID
	 * @param int      $uid   关注的用户ID
	 * @param string[] $data  图集数据
	 * @return null
	 */
	public function message_follow_gallery($rt, $gid, $g_uid, $uid, $data){
		try{
			if($this->notice($g_uid, 'message', 'follow_gallery')){
				$user = User::getUser($uid);
				$follow_user = User::getUser($g_uid);
				$mt = new MailTemplate("message_notice/follow_gallery.md");
				$mt->setUserInfo($follow_user->getInfo());
				if($gid != $data['gallery_id']){
					$data = [];
				}
				$mt->setValues(array_merge($data, [
					'follow_user_aliases' => $user->getAliases(),
					'follow_user_url' => user_link($user->getName()),
					'follow_user_name' => $user->getName(),
					'gallery_page_url' => gallery_link($gid)
				]));
				$this->message->addNoticeMsg($mt->getTitle(), $mt->getContent(), $g_uid);
			}
		} catch(\Exception $ex){
			Log::write(_("NoticeApply message_follow_gallery create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}

	/**
	 * 发送邮件给关注的用户
	 * @param null $rt
	 * @param int  $f_id 被关注ID
	 * @param int  $u_id 关注者ID
	 * @return null
	 */
	public function mail_follow_me($rt, $f_id, $u_id){
		try{
			if($this->notice($f_id, 'mail', 'follow_me')){
				$user = User::getUser($u_id);
				$follow_user = User::getUser($f_id);
				$mt = new MailTemplate("mail_notice/follow_me.html");
				$mt->setUserInfo($follow_user->getInfo());
				$mt->setValues([
					'follow_user_aliases' => $user->getAliases(),
					'follow_user_url' => user_link($user->getName()),
					'follow_list_link' => get_url('Follow', 'ta'),
					'follow_user_name' => $user->getName()
				]);
				$mt->mailSend($follow_user->getName(), $follow_user->getEmail());
			}
		} catch(\Exception $ex){
			Log::write(_("NoticeApply mail_follow_me create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}

	/**
	 * 发送消息给关注用户
	 * @param null $rt
	 * @param int  $f_id 被关注ID
	 * @param int  $u_id 关注者ID
	 * @return null
	 */
	public function message_follow_me($rt, $f_id, $u_id){
		try{
			if($this->notice($f_id, 'message', 'follow_me')){
				$user = User::getUser($u_id);
				$follow_user = User::getUser($f_id);
				$mt = new MailTemplate("message_notice/follow_me.md");
				$mt->setUserInfo($follow_user->getInfo());
				$mt->setValues([
					'follow_user_aliases' => $user->getAliases(),
					'follow_user_url' => user_link($user->getName()),
					'follow_list_link' => get_url('Follow', 'ta'),
					'follow_user_name' => $user->getName()
				]);
				$this->message->addNoticeMsg($mt->getTitle(), $mt->getContent(), $f_id);
			}
		} catch(\Exception $ex){
			Log::write(_("Message to message_follow_me create a Exception.") . "EX:[" . $ex->getCode() . "]:" . $ex->getMessage(), Log::NOTICE);
		}
		return $rt;
	}
}