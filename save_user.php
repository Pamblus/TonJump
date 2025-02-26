<?php
header('Content-Type: application/json');

// Получаем и валидируем входные данные
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Базовые проверки структуры JSON
if ($data === null || json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON format']);
    exit;
}

// Проверка обязательных полей
$requiredFields = ['telegram_id', 'price'];
foreach ($requiredFields as $field) {
    if (!isset($data[$field])) {
        echo json_encode(['status' => 'error', 'message' => "Missing required field: $field"]);
        exit;
    }
}

// Валидация Telegram ID (допускаем только цифры)
if (!preg_match('/^\d+$/', $data['telegram_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Telegram ID format']);
    exit;
}

// Валидация цены
$price = filter_var($data['price'], FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0.01]]);
if ($price === false) {
    echo json_encode(['status' => 'error', 'message' => 'Price must be a positive number']);
    exit;
}

// Нормализация данных
$data['price'] = round($price, 2); // Округляем до копеек
$filename = 'users/id_' . $data['telegram_id'] . '.json';

// Проверка и сохранение рекорда
if (file_exists($filename)) {
    $currentData = json_decode(file_get_contents($filename), true);
    
    if (isset($currentData['price']) && $currentData['price'] >= $data['price']) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Record not updated (current record is same or higher)',
            'current_price' => $currentData['price']
        ]);
        exit;
    }
}

// Сохранение данных с блокировкой файла
try {
    file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    echo json_encode([
        'status' => 'success',
        'message' => 'Record updated',
        'new_price' => $data['price']
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to save data: ' . $e->getMessage()
    ]);
}
?>
