# Afdian
## 可以用于查询爱发电的订单和赞助者信息与爱发电第三方登录
第三方登录我没申请下来，没测试等测试在更新
### 安装

~~~bash
composer require ham0mer/afdian
~~~

### 订单使用方法

~~~php
use \Ham0mer\Afdian\Afdian;
// 初始化 Afdian 对象
$afdian = new Afdian(USERID, TOKEN);
~~~
检测 User ID 与 Token 是否有效，与服务器连接是否正常

```php
echo sprintf("Ping status: %s\n", $afdian->pingServer() ? "Success" : "Failed");
```

获取所有的订单列表
```php
$orders = $afdian->getAllOrders();
print_r($orders);
```
在返回的订单列表里进一步查询，根据订单 ID 获取信息
```php
$order = $afdian->getOrderById($orders, "这里写你的订单号");
print_r($order);
```
获取所有的赞助者
```php
$sponsors = $afdian->getAllSponsors();
print_r($sponsors);
```
得到赞助者列表后，根据用户名查询赞助者信息
```php
$user = $afdian->getSponsorByName($sponsors, "Lain音酱");
print_r($user);
```

### 登录使用方法
```php
use \Ham0mer\Afdian\Login;
// 初始化 Login 对象
$client_id = "这里填写你的 Client ID";
$client_secret = "这里填写你的 Client Secret";
$siteurl = "这里填写你的网站地址";
$type = "tp"; // 填tp为ThinkPHP，填other为其他框架
$login = new Login($client_id,$client_secret,$siteurl,$type);

$login->login();
//返回信息
$login->callback($code,$state);
```

另外也可以直接查看 afdian.php，每个方法都写了详细的注释。

## Server Return
关于服务器返回的状态码以及更多信息，请查阅官方文档：

https://afdian.net/dashboard/dev

## License
本项目使用 MIT 协议开源
