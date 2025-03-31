<?php

// Проверка на то, что скрипт запущен из браузера
if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/html; charset=utf-8');
}

echo "Настройка базы данных<br>";

try {
    // Получение настроек из .env файла
    $envFile = dirname(__DIR__) . '/.env';
    if (!file_exists($envFile)) {
        throw new Exception("Файл .env не найден!");
    }
    
    $env = parse_ini_file($envFile);
    
    $host = $env['DB_HOST'] ?? '127.0.0.1';
    $port = $env['DB_PORT'] ?? '3306';
    $database = $env['DB_DATABASE'] ?? 'express';
    $username = $env['DB_USERNAME'] ?? 'root';
    $password = $env['DB_PASSWORD'] ?? '';
    
    echo "Проверка подключения к MySQL...<br>";
    
    // Подключение к MySQL
    $mysqli = new mysqli($host, $username, $password, '', (int)$port);
    
    if ($mysqli->connect_errno) {
        throw new Exception("Не удалось подключиться к MySQL: " . $mysqli->connect_error);
    }
    
    echo "Подключение к MySQL успешно!<br>";
    
    // Проверяем существование базы данных
    $result = $mysqli->query("SHOW DATABASES LIKE '$database'");
    
    if ($result->num_rows > 0) {
        echo "База данных '$database' уже существует.<br>";
    } else {
        echo "Создание базы данных '$database'...<br>";
        if (!$mysqli->query("CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
            throw new Exception("Ошибка при создании базы данных: " . $mysqli->error);
        }
        echo "База данных '$database' успешно создана!<br>";
    }
    
    echo "<br>Для выполнения миграций перейдите к <a href='/run-migrations.php'>run-migrations.php</a>";
    
} catch (Exception $e) {
    echo "<div style='color: red; font-weight: bold;'>Ошибка: " . $e->getMessage() . "</div>";
}
