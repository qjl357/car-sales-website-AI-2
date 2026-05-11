<?php
/**
 * car_sales 库 — 与当前 phpMyAdmin 结构一致：
 *
 * sellers（登录）
 *   id, fullName, address, phone, email, password, created_at, username
 *
 * 车源表 vehicles（搜索）：id, color, model, year, location, price, image_path, created_at
 */
declare(strict_types=1);

return [
    'pdo' => [
        'dsn'  => 'mysql:host=127.0.0.1;dbname=car_sales;charset=utf8mb4',
        'user' => 'root',
        'pass' => '',
        'options' => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ],
    ],

    'users_table'          => 'sellers',
    'username_column'      => 'username',
    'password_column'      => 'password',
    'redirect_after_login' => 'home.html',

    /** 登录成功后写入 $_SESSION 的 sellers 表列（须真实存在） */
    'users_session_columns' => ['fullName', 'phone', 'email'],

    /**
     * 车源搜索：columns 的「键」为程序逻辑名，「值」为数据库真实列名。
     * 前端省份筛选 POST province（英文省名，如 Beijing、Guangdong），按 location 列 LIKE 前缀匹配；库中 location 建议存英文或以所选省名开头。
     * mileage_km 留空：不使用里程列。
     * 「品牌」下拉按 model 列等值匹配（不区分大小写）。
     */
    'listings' => [
        'table'   => 'vehicles',
        'columns' => [
            'id'          => 'id',
            'model'       => 'model',
            'year'        => 'year',
            'color'       => 'color',
            'location'    => 'location',
            'price'       => 'price',
            'image_path'  => 'image_path',
            'created_at'  => 'created_at',
            'mileage_km'  => '',
        ],
    ],
];
