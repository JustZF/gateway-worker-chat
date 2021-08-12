<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Validate;
use think\Session;
use think\Request;
class chat extends Main
{
    public function index()
    {
        return $this->fetch();
  }
   
   public function chatlist()
    {
		$data = Db::name('chat_user')
            ->order('id asc')
            ->paginate(12);
        //获取当前域名
        $request = Request::instance();
        $domain = $request->domain();


		$this->assign('users', $data);
		$this->assign('url', $domain);
        return $this->fetch();
    }
   public function showAdd()
    {
        return $this->fetch('chat_add');
    }
	public function add_Chat()
	{   
		$name=input('name');
		$pw=input('password');
		$c_pw=input('check_password');
		$validate=new Validate([
		'name|用户名'=>'require|min:5',
		'pw|密码'=>'require|min:6',
		'c_pw|重复密码'=>'require|min:6',
		]);

		$data=[
		'name'=>$name,
		'pw'=>$pw,
		'c_pw'=>$c_pw,
		];
		if(!$validate->check($data)){
        return ['msg'=>$validate->getError()];	
		}
		if($pw<>$c_pw)
		{
		return ['msg'=>'两次密码不一致'];
		}
		$map['name']=$name;
		$chatname=Db::name('chat_user')->where($map)->find();
		if($name==$chatname['name']){
		return ['msg'=>'用户名已存在'];
		}
		$save['name'] = $name;
		$save['pw'] = md5($pw);
        $save['login_ip'] = '0.0.0.0';
        $save['add_time']   = date('Y-m-d h:i:s', time());
        $db = Db::name('chat_user')->insert($save);
		return ['msg'=>'添加成功','code'=>'200'];
	}
	    public function showEdit($id)
    {
        
        $data = Db::name('chat_user')
            ->where('id', $id)
            ->find();
        $this->assign('data', $data);
        return $this->fetch('chat_edit');
    }
		public function edit_Chat()
	{   
		$state=input('state');
		$id=input('id');
		$name=input('name');
		$pw=input('password');
		$c_pw=input('check_password');
		if($pw){
		$validate=new Validate([
		'name|用户名'=>'require|min:5',
		'pw|密码'=>'require|min:6',
		'c_pw|重复密码'=>'require|min:6',
		]);
		$data=[
		'name'=>$name,
		'pw'=>$pw,
		'c_pw'=>$c_pw,
		];
		if($pw<>$c_pw)
		{
		return ['msg'=>'两次密码不一致'];
		}
		$save['pw'] = md5($pw);
		}
		else{
		$validate=new Validate([
		'name|用户名'=>'require|min:5',
		]);
		$data=[
		'name'=>$name,
		];
		if($c_pw)
		{
		return ['msg'=>'两次密码不一致'];
		}	
		}
		if(!$validate->check($data)){
        return ['msg'=>$validate->getError()];	
		}
		$chatname=Db::name('chat_user')
		->where('name',$name)
		->where('id','<>',$id)
		->find();
		if($chatname)
		{
		return ['msg'=>'用户名已存在'];
		}
		if($state=='on')
		{
		$state='0';
		}
		else{
		$state='1';
		}
		$save['name'] = $name;
		$save['state'] = $state;		
        $db = Db::name('chat_user')->where('id', $id)->update($save);
		if($db!== false){
		return ['msg'=>'修改成功','code'=>'200'];
		}
		else{
			return ['msg'=>'未修改成功'];
		}
	}
	//删除
    public function deleteChat()
    {
        $id =input('id');
        $db = Db::name('chat_user')
        ->where('id', $id)
        ->delete();
		if($db!== false){
                return ['msg'=>'删除成功','code'=>'200'];
		         }
		         else{
			     return ['msg'=>'删除失败'];
		         }
    }
	 public function deleteallChat()
    {
        $id =input('id/a');
			$db = Db::name('chat_user')
                ->where('id','in', $id)
                ->delete();
			if($db!== false){
                return ['msg'=>'删除成功','code'=>'200'];
		         }
                 else{
			     return ['msg'=>'删除失败'];
		         }				 
		
    }
	//禁用启用
    public function stateChat()
    {
        $id =input('id');
		$state=input('checked');
                 $db = Db::name('chat_user')
                ->where('id', $id)
                ->update(['state'=>$state]);
				if($db!== false){
                return ['msg'=>'成功','code'=>'200'];
		         }
		         else{
			     return ['msg'=>'失败'];
		         }
        
    }
	   public function record()
    {
		$foid=input('foid');
		$sql=Db::name('chat_record')
		    ->where('foid',$foid)
			->order('id DESC')
			->buildSql();
		$data=Db::table($sql.'a')
		     ->group('ftoid')
			 ->paginate(12);

		$this->assign('list', $data);
        return $this->fetch();
    }
	public function recordShow(){
		$foid=input('foid');
		$ftoid=input('ftoid');
		$page=input('page');
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
		$foid=input('foid');
		$ftoid=input('ftoid');
		
       $lista = Db::name('chat_record')
	         ->where('ftoid',$ftoid)
			 ->where('foid',$foid)
			 ->order('id DESC')
			 ->limit($start,10)
			 ->select();

		return  $lista;
	}
}
