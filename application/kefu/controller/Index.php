<?php
namespace app\kefu\controller;
use think\Controller;
use think\Db;
use think\Validate;
use think\Session;
use \Workerman\Worker;
use think\Cookie;
use \GatewayWorker\Lib\Gateway;
class index extends Main
{
    public function index()
    { 
		$path=APP_PATH."config.txt";
        $sysconfig = json_decode(file_get_contents($path),true);	
		$this->assign('sysconfig', $sysconfig);	
        return $this->fetch();
  }
   public function welcome()
    {

		return $this->fetch();
  }
   public function get_list()
    {
		$data = input("post.");
		if($data)
		{
		if(Cookie('fid')==$data['room_id'])
		{
			$save['state'] = '1';		
            Db::name('chat_record')
			->where('fid','eq', $data['room_id'])
			->where('state','eq','0')
			->update($save);
		}
		}
            $romid = session('kefu');
			$map['toid']=$romid;
            $info  = Db::name('chat_record')->where($map)->group('fid')->select();
			$list=[];
			foreach($info as $key=>$value)
			{
				$list[$key]['isonline']=Gateway::isUidOnline($value['fid']);				
                $weiducount=Db::name('chat_record')
				->where('state','eq','0')
			    ->where('fid','eq',$value['fid'])
			    ->count();
                $news_data=Db::name('chat_record')
			    ->where('fid','eq',$value['fid'])
				->order('id desc')
                ->find();				
				$list[$key]['id']=$value['id'];
				$list[$key]['fid']=$value['fid'];
				$list[$key]['time']=$news_data['time'];
				$list[$key]['content']=$news_data['content'];	
                $list[$key]['state']=$news_data['state'];
                $list[$key]['weiducount']=$weiducount;				
			}
		array_multisort(array_column($list,'isonline'),SORT_DESC,array_column($list,'time'),SORT_DESC,$list);
            return $list;       
    }   
  
   public function save_message()
    {
		$data = input("post.");
        $message['fid']=$data['room_id'];
        $message['toid']=$data['toid'];
        $message['content']=$data['content'];
        $message['time']=$data['time'];
        $message['isonline']=$data['isonline'];
        $message['type'] =$data['type'];
		$message['foid'] =$data['room_id'];
		$message['ftoid'] =$data['toid'];
        Db::name("chat_record")->insert($message);	
  }
  public function duihua()
    {
		$path=APP_PATH."config.txt";
        $sysconfig = json_decode(file_get_contents($path),true);
		$this->assign('sysconfig', $sysconfig);
		return $this->fetch();
  }
     public function chat_record()
    {
		$data = input("post.");
		$map['foid']=session('kefu');
		$map['ftoid']=$data['fid'];
		$count=Db::name('chat_record')
            ->where($map)
			->count();
		if($count>10)
		{
		   $count=$count-10;
		}
		else{
			$count=0;
		}
		$message = Db::name('chat_record')
            ->where($map)
            ->order('id asc')
			->limit($count,10)
            ->select();

			// var_dump($map);
		return $message;
			
    }
   public function record()
    {
		$sql=Db::name('chat_record')
		    ->where('foid',session('kefu'))
			->order('id DESC')
			->buildSql();
		$data=Db::table($sql.'a')
		     ->group('ftoid')
			 ->paginate(12);

		$this->assign('list', $data);
        return $this->fetch();
    }
/*	
	   public function recordShow()
    {
        return $this->fetch();
    }

	public function recordShowlist(){
		$ftoid=input('id');
		$page=input('page');
		$foid=session('kefu');
        $page_size = 10;  
        $count = Db::name('chat_record')
		->where('ftoid',$ftoid)
		->where('foid',$foid)
		->count();  //总记录数

        $data['total_page'] = ceil($count/$page_size);  //总页数
        $cur_page = intval($page-1);  //默认为1 
        $data['res'] = Db::name('chat_record')
		    ->where('ftoid',$ftoid)
			->where('foid',$foid)
			->order('id desc')
            ->limit(($cur_page*$page_size),$page_size)  //limit默认要从零开始
            ->select();
        return $data;
    }
*/
public function recordShow(){
		$ftoid=input('id');
		$page=input('page');
		$foid=session('kefu');
        $page_size = 10;  
        $count = Db::name('chat_record')
		->where('ftoid',$ftoid)
		->where('foid',$foid)
		->count();  //总记录数
		$this->assign('count', $count);
        return $this->fetch();
}

public function recordShowlist(){
		$start = input('page');
        $foid=session('kefu');
		$ftoid=input('id');
		
       $list = Db::name('chat_record')
	         ->where('ftoid',$ftoid)
			 ->where('foid',$foid)
			 ->order('id DESC')
			 ->limit($start,10)
			 ->select();

		return  $list;
	}







}
