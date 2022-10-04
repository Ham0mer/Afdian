<?php
/**
 * 爱发电类
 * @author : hammer <oio_qwq@proton.me>
 * Licensed ( https://lsls.me )
 * Copyright (c) 2022~2099 https://lsls.me All rights reserved.
 */
namespace Ham0mer\Afdian;

class Afdian{
    private $userId, $token, $http;
    private $apiRootUrl = 'https://afdian.net/api/open/%s';

    /*
     * Name: __construct
     * Desc: 初始化函数，设定用户 ID 和 Token
     * Return: <Null> 无返回值
     * Params:
     *   String $userId 用户 ID
     *   String $token  用户 Token
     */
    public function __construct($userId, $token)
    {
        $this->userId = $userId;
        $this->token  = $token;
        $this->http   = new HttpRequest();
    }

    /*
     * Name: getSignature
     * Desc: 计算 API 请求签名
     * Return: <String> 计算出来的签名结果
     * Params:
     *   String $params 请求参数的 JSON 文本
     *   Int    $time   时间戳
     */
    public function getSignature($params, $time)
    {
        return md5("{$this->token}params{$params}ts{$time}user_id{$this->userId}");
    }

    /*
     * Name: queryServer
     * Desc: 访问 API 服务器进行查询
     * Return: <HttpResponse> 服务器返回的查询结果
     * Params:
     *   String $api    需要请求的 API 终端名称
     *   Array  $params 请求的参数内容
     */
    public function queryServer($api, $params) {
        if(!isset($api) || empty($api)) {
            return new HttpResponse("Empty api endpoint", [], '');
        }
        $params    = json_encode($params);
        $queryData = json_encode([
            'user_id' => $this->userId,
            'params'  => $params,
            'ts'      => time(),
            'sign'    => $this->getSignature($params, time())
        ]);
        return $this->http->query(sprintf($this->apiRootUrl, $api), $queryData, false, ['Content-Type: application/json']);
    }

    /*
     * Name: pingServer
     * Desc: 测试签名和服务器连接是否正常
     * Return: <Boolean> 检测结果
     * Params: 无参数
     */
    public function pingServer()
    {
        $result = $this->queryServer('ping', ['ping' => 'hello world']);
        if($result->status == 200) {
            $json = json_decode($result->data, true);
            if($json && is_array($json)) {
                return (isset($json['ec']) && $json['ec'] == 200);
            }
            return false;
        }
        return false;
    }

    /*
     * Name: getOrders
     * Desc: 查询订单列表
     * Return: <Array|String> 如果成功，则返回订单列表数组，失败则返回错误消息
     * Params:
     *   Int $page 指定要查询的页面，页面数量可以通过返回的 total_page 获得
     */
    public function getOrders($page = 1)
    {
        $result = $this->queryServer('query-order', ['page' => $page]);
        if($result->status == 200) {
            $json = json_decode($result->data, true);
            if($json && is_array($json)) {
                return (isset($json['ec']) && $json['ec'] == 200) ? $json : $json['em'];
            }
            return "Cannot parse json string";
        }
        return $result->status;
    }

    /*
     * Name: getSponsors
     * Desc: 查询赞助者列表
     * Return: <Array|String> 如果成功，则返回赞助者列表数组，失败则返回错误消息
     * Params:
     *   Int $page 指定要查询的页面，页面数量可以通过返回的 total_page 获得
     */
    public function getSponsors($page = 1)
    {
        $result = $this->queryServer('query-sponsor', ['page' => $page]);
        if($result->status == 200) {
            $json = json_decode($result->data, true);
            if($json && is_array($json)) {
                return (isset($json['ec']) && $json['ec'] == 200) ? $json : $json['em'];
            }
            return "Cannot parse json string";
        }
        return $result->status;
    }

    /*
     * Name: getAllOrders
     * Desc: 获取所有的订单，每一页都读取一次，因为需要循环查询，请尽量避免频繁调用
     * Return: <Array|String> 如果成功，则返回订单列表数组，失败则返回错误消息，注：本方法不会返回 getOrders 的附加信息，例如 total_page 等
     */
    public function getAllOrders($cacheTime = 0, $cacheFile = 'order_cache.json')
    {
        $orders = ["data" => ["list" => []]];
        $result = $this->getOrders(1);
        if(isset($result['data']['list'], $result['data']['total_page'])) {
            foreach($result['data']['list'] as $order) {
                $orders['data']['list'][] = $order;
            }
            for($i = 2;$i <= $result['data']['total_page'];$i++) {
                $result = $this->getOrders($i);
                if(isset($result['data']['list'])) {
                    foreach($result['data']['list'] as $order) {
                        $orders['data']['list'][] = $order;
                    }
                }
            }
        }
        return $orders;
    }

    /*
     * Name: getAllSponsors
     * Desc: 获取所有的赞助者，每一页都读取一次，因为需要循环查询，请尽量避免频繁调用
     * Return: <Array|String> 如果成功，则返回赞助者列表数组，失败则返回错误消息，注：本方法不会返回 getSponsors 的附加信息，例如 total_page 等
     */
    public function getAllSponsors($cacheTime = 0, $cacheFile = 'sponsor_cache.json')
    {
        $sponsors = ["data" => ["list" => []]];
        $result   = $this->getSponsors(1);
        if(isset($result['data']['list'], $result['data']['total_page'])) {
            foreach($result['data']['list'] as $order) {
                $sponsors['data']['list'][] = $order;
            }
            for($i = 2;$i <= $result['data']['total_page'];$i++) {
                $result = $this->getSponsors($i);
                if(isset($result['data']['list'])) {
                    foreach($result['data']['list'] as $order) {
                        $sponsors['data']['list'][] = $order;
                    }
                }
            }
        }
        return $sponsors;
    }

    /*
     * Name: getOrderById
     * Desc: 根据订单号获取订单信息
     * Return: <Array|String> 如果成功，则返回订单信息的数组，失败则返回错误消息
     * Params:
     *   Array $result  订单列表，可以通过 getOrders 或 getAllOrders 查询得到
     *   Int   $orderId 要查询的订单号
     */
    public function getOrderById($result, $orderId)
    {
        if(!isset($orderId, $result) || empty($result) || empty($orderId)) {
            return "Empty result or order id";
        }
        if(isset($result['data']['list'])) {
            foreach($result['data']['list'] as $order) {
                if($order['out_trade_no'] == $orderId) {
                    return $order;
                }
            }
        }
    }

    /*
     * Name: getOrderByUserId
     * Desc: 根据用户 ID 获取订单列表
     * Return: <Array|String> 如果成功，则返回订单信息的数组，失败则返回错误消息
     * Params:
     *   Array $result 订单列表，可以通过 getOrders 或 getAllOrders 查询得到
     *   Int   $userId 要查询的用户 ID
     */
    public function getOrderByUserId($result, $userId)
    {
        if(!isset($userId, $result) || empty($result) || empty($userId)) {
            return "Empty result or user id";
        }
        $orders = [];
        if(isset($result['data']['list'])) {
            foreach($result['data']['list'] as $order) {
                if($order['user_id'] == $userId) {
                    $orders[] = $order;
                }
            }
        }
        return $orders;
    }

    /*
     * Name: getOrderByPlanId
     * Desc: 根据赞助方案获取订单列表
     * Return: <Array|String> 如果成功，则返回订单信息的数组，失败则返回错误消息
     * Params:
     *   Array $result 订单列表，可以通过 getOrders 或 getAllOrders 查询得到
     *   Int   $planId 要查询的赞助方案 ID
     */
    public function getOrderByPlanId($result, $planId)
    {
        if(!isset($planId, $result) || empty($result) || empty($planId)) {
            return "Empty result or plan id";
        }
        $orders = [];
        if(isset($result['data']['list'])) {
            foreach($result['data']['list'] as $order) {
                if($order['plan_id'] == $planId) {
                    $orders[] = $order;
                }
            }
        }
        return $orders;
    }

    /*
     * Name: getSponsorById
     * Desc: 根据用户 ID 获取赞助者信息
     * Return: <Array|String> 如果成功，则返回赞助者信息的数组，失败则返回错误消息
     * Params:
     *   Array $result 赞助者列表，可以通过 getSponsors 或 getAllSponsors 查询得到
     *   Int   $userId 要查询的用户 ID
     */
    public function getSponsorById($result, $userId)
    {
        if(!isset($userId, $result) || empty($result) || empty($userId)) {
            return "Empty result or plan id";
        }
        if(isset($result['data']['list'])) {
            foreach($result['data']['list'] as $sponsor) {
                if(isset($sponsor['user'], $sponsor['user']['user_id']) && $sponsor['user']['user_id'] == $userId) {
                    return $sponsor;
                }
            }
        }
        return "User id not found";
    }

    /*
     * Name: getSponsorByName
     * Desc: 根据用户名字获取赞助者信息
     * Return: <Array|String> 如果成功，则返回赞助者信息的数组，失败则返回错误消息
     * Params:
     *   Array $result   赞助者列表，可以通过 getSponsors 或 getAllSponsors 查询得到
     *   Int   $userName 要查询的用户名字
     */
    public function getSponsorByName($result, $userName)
    {
        if(!isset($userName, $result) || empty($result) || empty($userName)) {
            return "Empty result or plan id";
        }
        if(isset($result['data']['list'])) {
            foreach($result['data']['list'] as $sponsor) {
                if(isset($sponsor['user'], $sponsor['user']['name']) && $sponsor['user']['name'] == $userName) {
                    return $sponsor;
                }
            }
        }
        return "User name not found";
    }
}
