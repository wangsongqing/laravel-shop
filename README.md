# laravel-shop系统

项目环境要求
 1. php 大于 7.3
 2. laravel8.0以上
 3. mysql
 4. redis

安装步骤
 1. composer install
 2. 新建数据库: laravel-shop
 3. 修改 .env.example 为 .env 文件，修改相应的配置
    ````
    必须修改配置有：
    APP_URL=
    APP_NAME=
    
    DB_CONNECTION=
    DB_HOST=
    DB_PORT=
    DB_DATABASE=
    DB_USERNAME=
    DB_PASSWORD=
    
    REDIS_HOST=127.0.0.1
    REDIS_PASSWORD=null
    REDIS_PORT=6379
    
    MAIL_MAILER=smtp
    MAIL_HOST=127.0.0.1
    MAIL_PORT=1025
    MAIL_USERNAME=null
    MAIL_PASSWORD=null
    MAIL_ENCRYPTION=null
    MAIL_FROM_ADDRESS=null
    MAIL_FROM_NAME="${APP_NAME}"
    ````
 4. 执行数据库迁移命令：php artisan migrate
 5. 后台访问地址 {项目地址}/admin
 6. 后台登录用户名:admin 密码:admin
 7. 执行数据填充:php artisan db:seed --class=ProductsSeeder
 8. 如果是本测试的话可以用 mailhog 来代替邮箱服务
 9. 执行软链接命令: php artisan storage:link
