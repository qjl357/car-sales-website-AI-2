<?php
/**
 * 复制为 db_config.php 后按需修改。
 *
 * sellers：fullName, address, phone, email, password, created_at, username（登录用 username + password）
 *
 * 车源表 vehicles（列与当前库一致）：
 * CREATE TABLE IF NOT EXISTS vehicles (
 *   id INT AUTO_INCREMENT PRIMARY KEY,
 *   color VARCHAR(64) NOT NULL,
 *   model VARCHAR(128) NOT NULL,
 *   year SMALLINT NOT NULL,
 *   location VARCHAR(128) NOT NULL,
 *   price DECIMAL(12,2) NOT NULL,
 *   image_path VARCHAR(512) DEFAULT NULL,
 *   created_at DATETIME DEFAULT CURRENT_TIMESTAMP
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
    'redirect_after_login' => 'publish.php',

    'users_session_columns' => ['fullName', 'phone', 'email'],

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
