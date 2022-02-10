# 常用组件，都封装在这里

## controllers

从 v3.0.0 开始，移除 API 相关代码。

## generators

自定义 gii generators。

- search: 实现了为 model 创建 search model 的功能

使用方法：

在 main-local.php 中按照以下内容修改：

```php
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

## migrations

继承了为 mdmsoft/yii2-admin 模块创建第一个默认用户的 migration，使用方式

```shell
./yii migrate --migrationPath=@buddysoft/widget/migrations
```

## BDAR 类

- 自动处理 sid 字段
- 根据配置参数自动处理 secretFields 和 exceptFields

```php
    // 不需要返回给客户端的字段，所有 Model 通用
    'secretFields' => ['id', 'createdAt', 'updatedAt', 'accessToken'],
    // 在某些请求中，依然需要返回给客户端，但是存在于 secretFields 数组中的字段
    'exceptFields' => [
        [
            'routes' => ['site/login'], // 如果设置了 url rules 重定向，必须使用重定向后的路由
            'models' => ['User'],       // 需要排除特殊字段的 Model 类名字
            'fields' => ['accessToken'],// 需要排除的字段名字
        ],
        [
            'routes' => ['notice/index', 'notice/view', 'receipt/index', 'receipt/view'],
            'models' => ['Notice', 'Receipt'],
            'fields' => ['createdAt', 'updatedAt'],
        ],
    ],

```