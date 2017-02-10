# 常用组件，都封装在这里


## controllers

- WebController: 对网页控制器的封装，目前基本是空的
- ActiveController:  对RESTFul API 控制器的封装，实现了 Base Auth
- ApiController：对返回格式化 JSON 数据的接口型控制器的封装

## generators

自定义 gii generators。

- search: 实现了为 model 创建 search model 的功能

使用方法：

在 main-local.php 中按照以下内容修改：

```
$config['modules']['gii'] = [
    'class' => 'yii\gii\Module',
    // 添加下面内容
    'allowedIPs' => ['127.0.0.1', '::1', '192.168.0.*', '192.168.178.20'],
    'generators' => [
        'search' => [
            'class' => 'buddysoft\widget\generators\search\Generator',
            'templates' => [
                'myCrud' => 'buddysoft\widget\generators\search\default',
            ]
        ]
    ]
];

```
