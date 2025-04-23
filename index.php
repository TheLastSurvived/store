<?php
session_start(); 

$error = '';
$date = '';
$stock = 0;
$price = 0;
$cost = $_SESSION['cost'] ?? 0.01;


function fibonacci($n) {
    if ($n <= 0) return 0;
    if ($n == 1) return 1;
    
    $a = 0;
    $b = 1;
    
    for ($i = 2; $i <= $n; $i++) {
        $c = $a + $b;
        $a = $b;
        $b = $c;
    }
    
    return $b;
    
}


function daysStart($date) {
    //$startDate = new DateTime('2021-01-13');
    $startDate = new DateTime(date('Y-m-d'));
    $selectedDate = new DateTime($date);
    
    if ($selectedDate < $startDate) {
        return -1; 
    }
    
    $interval = $startDate->diff($selectedDate);
    return $interval->days;
}


function calculateStock($days) {
    $stock = 0;
    for ($i = 1; $i <= $days; $i++) {
        $stock += fibonacci($i);
    }
    return $stock;
}

function calculatePrice($days, $cost) {
    $totalCost = $cost + ($days * 0.5);
    return round($totalCost * 1.3, 2);
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_cost'])) {
    $newCost = $_POST['new_cost'] ?? '';
    
    if (!is_numeric($newCost) || $newCost <= 0) {
        $error = 'Себестоимость должна быть положительным числом';
    } else {
        $_SESSION['cost'] = floatval($newCost); 
        $cost = $_SESSION['cost'];
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['date'])) {
    $date = $_POST['date'] ?? '';
    
    if (empty($date)) {
        $error = 'Пожалуйста, выберите дату';
    } else {
        try {
            $selectedDate = new DateTime($date);
            $days = daysStart($date);
            
            if ($days < 0) {
                $error = "Выбрана дата раньше, чем сегодняшняя!";
            } else {
                $stock = calculateStock($days);
                $price = calculatePrice($days, $cost);
            }
        } catch (Exception $e) {
            $error = 'Некорректный формат даты!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Склад</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
        <h1>Система для СОС - Левый носок</h1>

        <div class="cost-form">
            <h3>Изменение себестоимости</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="new_cost">Текущая себестоимость: <?php echo $cost; ?> руб.</label>
                    <input type="number" step="0.01" id="new_cost" name="new_cost" min="0.01" value="<?php echo $cost; ?>" required>
                </div>
                <button type="submit" name="change_cost">Изменить себестоимость</button>
            </form>
        </div>
        
        <div class="form-date">
            <h3>Определение ценников на товар</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="date">Дата:</label>
                    <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($date); ?>" required>
                </div>
                <button type="submit">Применить</button>
            </form>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['date']) && empty($error)): ?>
            <div class="info">
                <h3>Информация на <?php echo htmlspecialchars($date); ?></h3>
                <p><strong>Остаток на складе:</strong> <?php echo $stock; ?> шт.</p>
                <p><strong>Цена товара:</strong> <?php echo $price; ?> руб.</p>
                <p>Ценники на сегодня: <strong><?php echo $price; ?> руб.</strong> на каждый товар</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>