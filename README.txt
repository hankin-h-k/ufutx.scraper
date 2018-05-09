**友福同享图书交友平台**

#repos
  * ufutx.library: 图书馆平台基础库
  * ufutx.library.mp: 小程序库


#域名主机配置
参考:[跨域配置](http://to-u.xyz/2016/06/30/nginx-cors/)

```nginx
    add_header Access-Control-Allow-Origin *; //http://m.licaigou.com.cn;
    add_header Access-Control-Allow-Headers X-Requested-With;
    add_header Access-Control-Allow-Methods GET,POST,PUT,DELETE,OPTIONS;
    location /mobile {
        alias /var/www/mobile/release/mobile;
        index index.html;
    }

    location /static {
        alias /var/www/mobile/release/static;
        index index.html;
    }
```

# 网站安装

```
git clone git@repo.ufutx.net:ufutx.library
cd ufutx.library
composer update -vvv
cp .env.example .env
php artisan key:generate
## 后端初始化需要执行
# php artisan migrate:install
# php artisan migrate
php artisan passport:install
#linux/unix or mac
chmod 777 -R bootstrap/cache/ storage/logs/ storage/framework/ storage/app/
```

# 数据库
1.

# 接口设计　
  * 用户图书馆分类接口 --ok--
    * route@GET:/api/users/{user_id}/books或/api/user/books
    * return@增加分类数据library_sorts
  * 用户接口增加 --ok--
    * route@GET:/api/users/{user_id}或/api/user
    * return@ 用户关注的followings, 关注用户者:followers, 我是否关注:is_following
  * 关注/取消关注某人的接口: --ok--
    * route@PUT:/api/users/{user_id}/follow
    * return@ $user->is_following: true|false
  * 拉取自己关注的书友接口: --ok--
    * route@GET:/api/users/{user_id}/followings
  * 拉取自己被谁关注的接口 --ok--
    * route@GET:/api/users/{user_id}/followers
  * 图书馆增加分类数据 --ok--
    * route:get@api/libraries/id
    * return@ 增加分类数据library_sorts
  * 图书馆图书列表
    * route: get@api/libraries/{id}/books
  * 图书馆分类的图书列表
    * route: get@api/libraries/{id}/sorts/{sort_id}/books
*
