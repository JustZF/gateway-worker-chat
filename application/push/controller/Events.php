<?php 

namespace app\push\controller;

use \GatewayWorker\Lib\Gateway;
use think\Db;
use \think\Request;
class Events {
	//当客户端连接时触发

    public static function onConnect($client_id)
    {
        
        // 向所有人发送
        // Gateway::sendToAll("$client_id login\r\n");

        Gateway::sendToClient($client_id, json_encode([
            'type' => 'login',
            'client_id' => $client_id,
            'kefu_name' => '客服001',
            'kefu_id' => 5,
            'content' => '您好，我是博主Just，很高兴你能查阅我的博客，希望您能在我博客上能获取您需要知识，如有不明白的，您可以加我微信Just_Tuan,方便咨询。'
        ]));
    }

	/**
	 * 进程启动后初始化数据库连接
	 */
	public static function onWorkerStart ($worker)
	{

	}
	
	/**
	 * 有消息时
	 *
	 * @param int   $client_id
	 * @param mixed $message
	 *
	 * @throws Exception
	 */
	public static function onMessage ($client_id, $message)
	{
		$message_data = json_decode($message, true);
        if (!$message_data) {
            //   return ;
        }
        // 根据类型执行不同的业务
        switch ($message_data['type']) {
            case 'bild':
                if (!isset($message_data['room_id'])) {
                    Gateway::sendToClient($client_id, json_encode([
                        'type' => 'bild',
                        'client_id' => $client_id,
                        'room_id' => ''
                    ]));
                    return;
                } else {
                    Gateway::sendToClient($client_id, json_encode([
                        'type' => 'bild',
                        'client_id' => $client_id,
                        'room_id' => $message_data['room_id']
                    ]));
                }
                // echo json_encode($message_data);
                Gateway::bindUid($client_id, $message_data['room_id']);

                return;
                // 获取token
            case 'getToken':
                $user_token = Db::name('user_token')
                    ->where('user_id', $message_data['uid'])
                    ->find();
                $token = empty($user_token['token']) ? '' : $user_token['token'];
                $request = Request::instance();
                if(!$user_token || empty($user_token['token'])) {
                    $token = base64_encode($request->ip() . time());
                    $add['user_id'] = $message_data['uid'];
                    $add['token'] = $token;
                    Db::name("user_token")->insert($add);
                }
                Gateway::sendToClient($client_id, json_encode([
                    'type' => 'token',
                    'client_id' => $client_id,
                    'token' => $token
                ]));
                return;
                // 客户端回应服务端的心跳
            case 'pong':
                return;
                // 客户端登录 message格式: {type:login, name:xx, room_id:1} ，添加到客户端，广播给所有客户端xx进入聊天室
            case 'login':
                // 判断是否有房间号
                if (!isset($message_data['room_id'])) {
                    Gateway::sendToClient($client_id, json_encode([
                        'type' => 'bild',
                        'client_id' => $client_id,
                        'room_id' => ''
                    ]));
                    return;
                }
                return;
                /*
                // 把房间号昵称放到session中
                $room_id = $message_data['room_id'];
                $client_name = htmlspecialchars($message_data['client_name']);
                $_SESSION['room_id'] = $room_id;
                $_SESSION['client_name'] = $client_name;
                // 获取房间内所有用户列表 
                $clients_list = Gateway::getClientSessionsByGroup($room_id);
                foreach($clients_list as $tmp_client_id=>$item)
                {
                    $clients_list[$tmp_client_id] = $item['client_name'];
                }
                $clients_list[$client_id] = $client_name;
                
                // 转播给当前房间的所有客户端，xx进入聊天室 message {type:login, client_id:xx, name:xx} 
                $new_message = array('type'=>$message_data['type'], 'client_id'=>$client_id, 'client_name'=>htmlspecialchars($client_name), 'time'=>date('Y-m-d H:i:s'));
                Gateway::sendToGroup($room_id, json_encode($new_message));
                Gateway::joinGroup($client_id, $room_id);
               
                // 给当前用户发送用户列表 
                $new_message['client_list'] = $clients_list;
                Gateway::sendToCurrentClient(json_encode($new_message));
                return;
                */
                // 客户端发言 message: {type:say, to_client_id:xx, content:xx}
            case 'say':
                if (isset($message_data['group'])) {
                    $group = $message_data['group'];
                } else {
                    $group = $message_data['toid'] . $message_data['room_id'];
                }
                Gateway::joinGroup($client_id, $group);
                $date = array(
                    'type' => 'say',
                    'content' => nl2br(htmlspecialchars($message_data['content'])),
                    'room_id' => $message_data['room_id'],
                    'toid' => $message_data['toid'],
                    'time' => date('Y-m-d H:i:s'),
                    'group' => $group,
                );
                // 如果不在线就先存起来
                if (!Gateway::isUidOnline($message_data['toid'])) {
                    if(isset($message_data['custmor'])) {
                        //如果是客人的话 回复礼貌用语
                        $data['type'] = 'say';
                        $data['content'] = "抱歉，客服暂不在线";
                        $data['toid'] = $message_data['room_id'];
                        $data['room_id'] = $message_data['toid'];
                        $data['time'] = date('Y-m-d H:i:s');
                        $data['group'] = $group;
                        Gateway::sendToUid($message_data['room_id'], json_encode($data));
                    }
                    $date['isonline'] = 0;
                } else {
                    Gateway::sendToUid($message_data['toid'], json_encode($date));
                    $date['isonline'] = 1;
                }
                $date['type'] = "save";
                Gateway::sendToUid($message_data['room_id'], json_encode($date));





                //echo "date" . json_encode($date) . "\n";
                return;
                /*
                // 非法请求
                if(!isset($_SESSION['room_id']))
                {
                    throw new \Exception("\$_SESSION['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']}");
                }
                $room_id = $_SESSION['room_id'];
                $client_name = $_SESSION['client_name'];
                
                // 私聊
                if($message_data['to_client_id'] != 'all')
                {
                    $new_message = array(
                        'type'=>'say',
                        'from_client_id'=>$client_id, 
                        'from_client_name' =>$client_name,
                        'to_client_id'=>$message_data['to_client_id'],
                        'content'=>"<b>对你说: </b>".nl2br(htmlspecialchars($message_data['content'])),
                        'time'=>date('Y-m-d H:i:s'),
                    );
                    Gateway::sendToClient($message_data['to_client_id'], json_encode($new_message));
                    $new_message['content'] = "<b>你对".htmlspecialchars($message_data['to_client_name'])."说: </b>".nl2br(htmlspecialchars($message_data['content']));
                    return Gateway::sendToCurrentClient(json_encode($new_message));
                }
                
                $new_message = array(
                    'type'=>'say', 
                    'from_client_id'=>$client_id,
                    'from_client_name' =>$client_name,
                    'to_client_id'=>'all',
                    'content'=>nl2br(htmlspecialchars($message_data['content'])),
                    'time'=>date('Y-m-d H:i:s'),
                );
                return Gateway::sendToGroup($room_id ,json_encode($new_message));
				*/
            // case 'chatlist':
            //     $date = array(
            //         'type' => 'chatlist',
            //         'list' => Gateway::getAllUidList(),
            //     );
            //     Gateway::sendToUid($message_data['room_id'], json_encode($date));
        }
	}
	
	/**
	 * 当客户端断开连接时
	 *
	 * @param integer $client_id 客户端id
	 */
	public static function onClose ($client_id)
	{
		// debug
		echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id onClose:''\n";
		
	}
}