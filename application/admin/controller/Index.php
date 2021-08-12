<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Validate;
use think\Session;
class index extends Main
{
    public function index()
    {
        return $this->fetch();
  }
   public function welcome()
    {



		return $this->fetch();
  }
   public function adminlist()
    {

		$data = Db::name('Admin')
            ->alias('a')
            ->join('auth_group_access b','b.uid=a.id','left')
			->join('auth_group c','c.id = b.group_id')
            ->field('a.*,c.title')
            ->order('a.id asc')
            ->paginate(12);

		$this->assign('users', $data);
        return $this->fetch();
    }
   public function showAdd()
    {
        $auth_group = Db::name('auth_group')
        ->field('id,title')
        ->order('id desc')
        ->select();
		$this->assign('auth_group', $auth_group);
        return $this->fetch('admin_add');
    }
	public function add_Admin()
	{   
	    $group_id=input('group_id');
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
		$adminname=Db::name('admin')->where($map)->find();
		if($name==$adminname['name']){
		return ['msg'=>'用户名已存在'];
		}
		$save['name'] = $name;
		$save['pw'] = md5($pw);
        $save['login_ip'] = '0.0.0.0';
        $save['add_time']   = date('Y-m-d h:i:s', time());
        $db = Db::name('admin')->insert($save);
        $adminId = Db::name('admin')->getLastInsID();
        Db::name('auth_group_access')->insert(['uid'=>$adminId,'group_id'=>$group_id]);
		return ['msg'=>'添加成功','code'=>'200'];
	}
	    public function showEdit($id)
    {
        
        $data = Db::name('Admin')
            ->alias('a')
            ->join('auth_group_access b','b.uid=a.id','left')
            ->field('a.*,b.group_id')
            ->where('id', $id)
            ->find();
        $auth_group = Db::name('auth_group')
        ->field('id,title')
        ->order('id desc')
        ->select();
        $this->assign('auth_group', $auth_group);
        $this->assign('data', $data);
        return $this->fetch('admin_edit');
    }
		public function edit_Admin()
	{   
	    $group_id=input('group_id');
		$state=input('state');
		$id=input('id');
		$name=input('name');
		$pw=input('password');
		$c_pw=input('check_password');
		$admin = Db::name('Admin')
            ->where('id', $id)
            ->find();
		if(!$admin)
		{
		return ['msg'=>'退出后，重试'];	
		}
		if($admin['name']<>$name)
		{
		$validate=new Validate([
		'name|用户名'=>'require|min:5',
		]);
		$adminname=Db::name('admin')
		->where('name',$name)
		->where('id','<>',$id)
		->find();
		if($adminname)
		{
		return ['msg'=>'用户名已存在'];
		}
		$data['name']=$name;
		$save['name']=$name;
		}
		if($pw<>$c_pw)
		{
		return ['msg'=>'两次密码不一致'];
		}
		if($admin['pw']==md5($pw))
		{
		return ['msg'=>'新密码与旧密码一致'];
		}
		if($pw)
		{
		$validate=new Validate([
		'pw|密码'=>'require|min:6',
		'c_pw|重复密码'=>'require|min:6',
		]);	
		$data['pw']=$pw;
		$data['c_pw']=$c_pw;
		$save['pw']=md5($pw);
		}
		if(!empty($validate)){
		if(!$validate->check($data)){
        return ['msg'=>$validate->getError()];	
		}
		}
		if($state=='on')
		{
		$state='0';
		}
		else{
		$state='1';
		}
		if($state<>$admin['state'])
		{
		$save['state']=$state;
		}
        if(!empty($save))
{
		$db_admin=Db::name('admin')->where('id', $id)->update($save);
}
		$groupid=Db::name('auth_group_access')->where('uid', $id)->find();
			
		if($group_id <> $groupid['group_id'])
		{
		$db_group=Db::name('auth_group_access')->where('uid', $id)->update(['group_id'=>$group_id]);	
		}
		if(!empty($db_admin) || !empty($db_group))
		{
		return ['msg'=>'修改成功','code'=>'200'];
		}
		else{
			return ['msg'=>'未修改'];
		}
	}
	//删除管理员
    public function deleteAdmin()
    {
        $id =input('id');
        $name =  Db::name('admin')
            ->where('id',$id)
            ->value('name');
        if ((int) $id !== 1) {
            if($name!==session('admin')){
                 $db = Db::name('admin')
                ->where('id', $id)
                ->delete();
				if($db!== false){
					Db::name('auth_group_access')
                ->where('uid', $id)
                ->delete();
                return ['msg'=>'删除成功','code'=>'200'];
		         }
		         else{
			     return ['msg'=>'删除失败'];
		         }
            }else{
                 return ['msg'=>'无法删除当前登录用户'];
            }
        } else {
                 return ['msg'=>'超级管理员无法删除'];
        }
    }
	 public function deleteallAdmin()
    {
        $id =input('id/a');
		$adminid=in_array("1",$id);
		$name=in_array(session('admin_id'),$id);
		if($name!==false){
			return ['msg'=>'无法删除当前登录用户'];
		}
		if($adminid!==true){
			$db = Db::name('admin')
                ->where('id','in', $id)
                ->delete();
			if($db!== false){
				Db::name('auth_group_access')
                ->where('uid','in', $id)
                ->delete();
                return ['msg'=>'删除成功','code'=>'200'];
		         }
                 else{
			     return ['msg'=>'删除失败'];
		         }				 
		}
		else
		{
			return ['msg'=>'包含超级管理员'];
		}
    }
	//禁用启用管理员
    public function stateAdmin()
    {
        $id =input('id');
		$state=input('checked');
        $name =  Db::name('admin')
            ->where('id',$id)
            ->value('name');
        if ((int) $id !== 1) {
            if($name!==session('admin')){
                 $db = Db::name('admin')
                ->where('id', $id)
                ->update(['state'=>$state]);
				if($db!== false){
                return ['msg'=>'成功','code'=>'200'];
		         }
		         else{
			     return ['msg'=>'失败'];
		         }
            }else{
                 return ['msg'=>'无法禁用当前登录用户'];
            }
        } else {
                 return ['msg'=>'超级管理员无法禁用'];
        }
    }
	//管理员用户组
	    function groups(){
        $role = Db::name('auth_group')
            ->order('id asc')
            ->paginate('12');
        $this->assign('role',$role);
        return $this->fetch();
    }
	//添加管理员用户组
	function addRole(){
        $auth_group = input('role_name');
        if(!empty($auth_group)){
            $res = Db::name('auth_group')
            ->where('title',$auth_group)
            ->find();
            if(empty($res)){
                 Db::name('auth_group')
                ->insert(['title'=>$auth_group,'status'=>0]);
                $this->success('添加成功');
            }else{
                $this->error('系统中已经存在该用户组');
            }
        }else{
            $this->error('请输入角色名称再添加');
        }
    }
	//删除管理员用户组
    function delRole(){
        $id  = input('id');
        if($id!=='1'){
            $res =  Db::name('auth_group')
            ->delete($id);
            $this->success('删除成功');
        }else{
            $this->error('超级管理组无法删除');
        }
    }
    function showRoleEdit($id){
        $data = Db::name('auth_group')
        ->where('id',$id)
        ->find();
        return $this->fetch('groups_edit',['data'=>$data]);
    }
	//编辑管理员用户组
    function edit_Groups(){
        $title = input('title');
		$id = input('id');
		$status = input('status');
		if($status=='on')
		{
		$status='1';
		}
		else{
		$status='0';
		}		
        if($id==1){
		return ['msg'=>'超级管理员无法编辑'];
        }
		$validate=new Validate([
		'title|用户组名'=>'require|min:2',
		]);
		$data=[
		'title'=>$title,
		];
		if(!$validate->check($data)){
        return ['msg'=>$validate->getError()];	
		}
		$db=Db::name('auth_group')
            ->where('id',$id)
            ->update(['title'=>$title,'status'=>$status]);
		if($db!== false){
                return ['msg'=>'修改成功','code'=>'200'];
		         }
		         else{
			     return ['msg'=>'失败'];
		         }

    }
	//编辑权限
	function showAuth($id){
        $this->assign('id',$id);
        return $this->fetch('edit_auth');
    }

}
