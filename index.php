<?php
require_once 'MyQueryBuilder.php';

// Заполнить конфиги своими данными баз, я тестировал на mysql и pgsql
$myConfig = [
    'type' => 'mysql',
    'host' => '',
    'db_name' => '',
    'username' => '',
    'password' => '',
];
$pgConfig = [
    'type' => 'pgsql',
    'host' => '',
    'db_name' => '',
    'username' => '',
    'password' => '',
];
// создаём queryBuilder'ы
$myDb = new MyQueryBuilder($myConfig);
$pgDb = new MyQueryBuilder($pgConfig);


// select из MySQL
$myUsers = $myDb->table('users')
    ->select('id, name')
    ->orderBy('id', 'desc')
    ->limit(2)
    ->get();

foreach ($myUsers as $user) {
    echo $user->id . ' - ' . $user->name . PHP_EOL;
}

// select из pgSQL
$pgUsers = $pgDb->table('users')
    ->select('id, name')
    ->where('name', '=', 'Max')
    ->get();

foreach ($pgUsers as $user) {
    echo $user->id . ' - ' . $user->name . PHP_EOL;
}

// добавление записи
$myDb->table('users')->insert(['name' => 'Bertram', 'age' => 24]);

// изменение записи
$myDb->table('users')->where('id', '=', '1')->update(['name' => 'Sam', 'age' => 25]);

// удаление записи
$myDb->table('users')->where('id', '=', '1')->delete();
