<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Validate;
use think\Session;
class auth extends Main
{
    function index(){
        //获取权限列表
        $auth = Db::name('auth_rule')
	        ->order(['sort' => 'DESC', 'id' => 'ASC'])
	        ->select();
        $auth = array2Level($auth);
        return $this->fetch('index',['auth'=>$auth]);
    }
    //展示权限界面
    function showAdd(){
    	$auth = Db::name('auth_rule')->order(['sort' => 'DESC', 'id' => 'ASC'])->select();
        $auth = array2Level($auth);
    	return  $this->fetch('add',['auth'=>$auth]);
    }
	    //新增菜单
    function add(){
		$pid=input('pid');
		$title=input('title');
		$name=input('name');
		$icon=input('icon');
		$status=input('status');
		$sort=input('sort');
		$validate=new Validate([
		'title|菜单名'=>'require|min:2',
		]);
		$data=[
		'title'=>$title,
		];
		if(!$validate->check($data)){
        return ['msg'=>$validate->getError()];	
		}
			$db=Db::name('auth_rule')
			->insert(['pid'=>$pid,'title'=>$title,'name'=>$name,'icon'=>$icon,'status'=>$status,'sort'=>$sort]);
			if($db!== false){
             return ['msg'=>'添加成功','code'=>'200'];
		    }
		    else{
			 return ['msg'=>'失败'];
		     }
		
    }
    function showEdit(){
        $id  = input('id');		
		$auth = Db::name('auth_rule')->order(['sort' => 'DESC', 'id' => 'ASC'])->select();
        $auth = array2Level($auth);
        $pid = Db::name('auth_rule')
            ->where('id',$id)
            ->value('pid');
        $data  =   Db::name('auth_rule')
            ->where('id',$id)
            ->find();
		$this->assign('auth',$auth);
		$this->assign('pid',$pid);
        return  $this->fetch('edit',['data'=>$data]);
    }
	function edit(){
		$id = input('id');
		$pid = input('pid');
		$title = input('title');
		$name = input('name');
		$icon = input('icon');
		$status = input('status');
		$sort = input('sort');
		$validate=new Validate([
		'title|菜单名'=>'require|min:2',
		]);
		$data=[
		'title'=>$title,
		];
		if(!$validate->check($data)){
        return ['msg'=>$validate->getError()];	
		}
		$db=Db::name('auth_rule')
            ->where('id',$id)
            ->update(['pid'=>$pid,'title'=>$title,'name'=>$name,'icon'=>$icon,'status'=>$status,'sort'=>$sort]);
        if($db!== false){
             return ['msg'=>'修改成功','code'=>'200'];
		    }
		    else{
			 return ['msg'=>'失败'];
		     }
    }
    function delete(){
        $id = input('id');
        $juge = Db::name('auth_rule')
            ->where('pid',$id)
            ->find();
        if($id<100){
                 $this->error('系统节点无法删除');
        }
        if(!empty($juge)){
                $this->error('请先删除子菜单或功能');
        }else{
            if($id<100){
                 $this->error('系统节点无法删除');
            }else{
                 $db=Db::name('auth_rule')
                    ->delete($id);
                 if($db!== false){
             return ['msg'=>'删除成功','code'=>'200'];
		    }
		    else{
			 return ['msg'=>'失败'];
		     }
            }
        }
    }
	//获取权限规则数据
    public function getJson()
    {
        $id = input('id');
        $auth_group_data = Db::name('auth_group')->find($id);
        $auth_rules      = explode(',', $auth_group_data['rules']);
        $auth_rule_list  = Db::name('auth_rule')->field('id,pid,title')->select();

        foreach ($auth_rule_list as $key => $value) {
            in_array($value['id'], $auth_rules) && $auth_rule_list[$key]['checked'] = true;
        }
        return $auth_rule_list;
    }
	/**
     * 更新权限组规则
     */
    public function updateAuthGroupRule()
    {
        if ($this->request->isPost()){
            $post = $this->request->post();
            if($post['id']==1){
               $this->error('超级管理员信息无法编辑');
            }
            $group_data['id']    = $post['id'];
            $group_data['rules'] = is_array($post['auth_rule_ids']) ? implode(',', $post['auth_rule_ids']) : '';
            if (Db::name('auth_group')->where('id',$post['id'])->update($group_data) !== false) {
                $this->success('授权成功');
            } else {
                $this->error('授权失败');
            }
        }
    }
}
