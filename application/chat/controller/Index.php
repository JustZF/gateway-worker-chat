<?php

namespace app\chat\controller;

use think\Controller;
use think\Db;
use think\Validate;
use think\Session;
use \Workerman\Worker;
use think\Cookie;
use \think\Request;

class index extends Controller
{
	public function ajaxPing()
	{
		$request = Request::instance();
		$userid = input('userid');
		if(!empty($userid)) {
			$user_token = Db::name('user_token')
			->where('user_id', $userid)
			->find();
			$token = empty($user_token['token']) ? '' : $user_token['token'];
			if(!$user_token || empty($user_token['token'])) {
				$token = base64_encode($request->ip() . time());
				$add['user_id'] = $userid;
				$add['token'] = $token;
				Db::name("user_token")->insert($add);
			}
		}
		return json(['token' => $token]);
	}

	public function index()
	{
		$toid = input('toid');
		$chat_user = Db::name('chat_user')
			->where('id', $toid)
			->find();
		if (!$chat_user) {
			return $this->error('非法连接');
		}
		$path = APP_PATH . "config.txt";
		$sysconfig = json_decode(file_get_contents($path), true);

		$request = Request::instance();
		if (!Cookie::has('name')) {
			$name = base64_encode($request->ip() . time());
			Cookie::set('name', $name);
		}
		$message = Db::name('chat_record')
			->where('ftoid', Cookie('name'))
			->order('id asc')
			->limit('10')
			->select();

		$this->assign('message', $message);
		$this->assign('chat_user', $chat_user['name']);
		$this->assign('sysconfig', $sysconfig);
		return $this->fetch();
	}

	public function index22()
	{
		$toid = input('toid');
		$chat_user = Db::name('chat_user')
			->where('id', $toid)
			->find();
		if (!$chat_user) {
			return $this->error('非法连接');
		}
		$path = APP_PATH . "config.txt";
		$sysconfig = json_decode(file_get_contents($path), true);

		$request = Request::instance();
		if (!Cookie::has('name')) {
			$name = base64_encode($request->ip() . time());
			Cookie::set('name', $name);
		}
		$message = Db::name('chat_record')
			->where('ftoid', Cookie('name'))
			->order('id asc')
			->limit('10')
			->select();

		$this->assign('message', $message);
		$this->assign('chat_user', $chat_user['name']);
		$this->assign('sysconfig', $sysconfig);
		return $this->fetch();
	}
	public function save_message()
	{
		$data = input("post.");
		$message['fid'] = $data['room_id'];
		$message['toid'] = $data['toid'];
		$message['content'] = $data['content'];
		$message['time'] = $data['time'];
		$message['isonline'] = $data['isonline'];
		$message['type'] = $data['type'];
		$message['foid'] = $data['toid'];
		$message['ftoid'] = $data['room_id'];
		Db::name("chat_record")->insert($message);
	}

	public function chat_record()
	{
		$data = input("post.");
		$map['foid'] = $data['foid'];
		$map['ftoid'] = Cookie('name');
		$count = Db::name('chat_record')
			->where($map)
			->count();
		if ($count > 10) {
			$count = $count - 10;
		} else {
			$count = 0;
		}
		$message = Db::name('chat_record')
			->where($map)
			->order('id asc')
			->limit($count, 10)
			->select();
		return $message;
	}
}
