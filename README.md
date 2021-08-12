#### 使用说明

1. 下载下来后 composer install

2. 进入站点目录，执行php server.php start -d 看能否运行

3. worker配置文件在application/extra目录下

4. 逻辑操作在application/push/controller/Event.php中

5. 具体数据库https://gitee.com/zzf199407/database.git


用法
```
前端页面插入一下代码
<div class="laykefu-min">咨询客服</div>
<link href="http://你的域名/static/customer/css/laykefu.css" rel="stylesheet" type="text/css" />
<link href="http://你的域名/static/css/chat/css/jquery-sina-emotion.min.css" rel="stylesheet">
<script src="http://你的域名/static/customer/js/laykefu.js"></script>
<script type="text/javascript" src="http://你的域名/static/css/chat/js/jquery-sina-emotion.js"></script>

<script type="text/javascript">
程序初始化
laykefu.init({
    group: 1,//客服分组
    socket: '',//聊天服务器地址（随便填）
    face_path: 'http://chat.tuanzf.top/static/customer/images/face',//表情包路径
    upload_url: '/index/upload/uploadImg',//图片上传路径
});
```


