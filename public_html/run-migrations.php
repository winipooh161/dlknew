<?php

// Проверка на то, что скрипт запущен из браузера
if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/html; charset=utf-8');
}

echo "Запуск миграций Laravel<br>";

try {
    // Определение путей
    $basePath = dirname(__DIR__);
    $artisanPath = $basePath . '/artisan';
    
    if (!file_exists($artisanPath)) {
        throw new Exception("Файл artisan не найден!");
    }
    
    // Запуск миграций через shell_exec
    $output = shell_exec('cd ' . escapeshellarg($basePath) . ' && php artisan migrate --force 2>&1');
    
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    echo "<br>Если произошла ошибка из-за существующих таблиц, вы можете попробовать обновить миграции:<br>";
    echo "<a href='?action=refresh'>Обновить миграции (внимание: это удалит все данные)</a><br>";
    
    // Проверка на запрос обновления миграций
    if (isset($_GET['action']) && $_GET['action'] === 'refresh') {
        echo "<br>Обновление миграций...<br>";
        $output = shell_exec('cd ' . escapeshellarg($basePath) . ' && php artisan migrate:refresh --force 2>&1');
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
    }
    
    echo "<br><a href='/chats'>Вернуться к чатам</a>";
    
} catch (Exception $e) {
    echo "<div style='color: red; font-weight: bold;'>Ошибка: " . $e->getMessage() . "</div>";
}
