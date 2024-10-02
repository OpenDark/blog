### 博客系统

- 获取
```
git clone https://gitee.com/opendark/blog.git
```

- 环境
```
php>=8.1 redis
扩展开启 redis file_info openssl gd zip
禁用函数检查 curl -Ss https://www.workerman.net/webman/check | php
```

- 执行：
```
Linux: php start.php start  开发调试模式
       php start.php start -d  守护进程模式
       其他命令 stop、reload、status、reload、connections
Windows: 运行windows.bat 或者 php windows.php
```

- 部署

```
一、创建数据库：导入doc/blog.sql
二、修改环境配置：复制.example.env 重命名为.env 
三、运行程序：php start.php start 没有报错则使用守护进程模式
```

- Nginx配置
```
root目录指向后端public目录

location /api/ {
    proxy_set_header Host $http_host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_set_header REMOTE-HOST $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_pass http://127.0.0.1:5959/;
}
location /ws {
    proxy_pass http://127.0.0.1:7979;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header X-Real-IP $remote_addr;
}
```

