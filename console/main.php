<?php

/**
 * TSMD 模块配置文件
 *
 * @link https://tsmd.thirsight.com/
 * @copyright Copyright (c) 2008 thirsight
 * @license https://tsmd.thirsight.com/license/
 */

return [
    // 设置路径别名，以便 Yii::autoload() 可自动加载 TSMD 自定的类
    'aliases' => [
        // yii2-tsmd-post 路径
        '@tsmd/post' => __DIR__ . '/../src',
    ],

    // 设置模块
    'modules' => [
        'post' => [
            'class' => 'tsmd\post\Module',
        ],
    ],
    // 设置命令行模式控制器
    // ./yii migrate-post/create 'tsmd\post\migrations\M200602000000CreatePostTable'
    // ./yii migrate-post/new
    // ./yii migrate-post/up
    // ./yii migrate-post/down 1
    'controllerMap' => [
        'migrate-post' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationPath' => [],
            'migrationNamespaces' => [
                'tsmd\post\migrations',
            ]
        ],
    ],
];
