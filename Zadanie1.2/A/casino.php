<?php
function calculateBalance($inputFile) {
    $file = fopen($inputFile, "r") or die("Unable to open file");

    // Количество ставок
    $numBets = intval(fgets($file));
    // Массив для хранения ставок
    $bets = [];

    // Чтение ставок
    for ($i = 0; $i < $numBets; $i++) {
        // Чтение идентификатора игры, суммы ставки и результата ставки
        list($gameId, $betAmount, $betResult) = explode(" ", trim(fgets($file)));
        $bets[] = [
            'gameId' => intval($gameId),   // Идентификатор игры
            'betAmount' => intval($betAmount), // Сумма ставки
            'betResult' => $betResult       // Результат ставки (L, R или D)
        ];
    }

    // Количество игр
    $numGames = intval(fgets($file));
    // Ассоциативный массив для хранения информации о играх
    $games = [];

    // Чтение информации о играх
    for ($i = 0; $i < $numGames; $i++) {
        // Чтение идентификатора игры, коэффициентов и результата игры
        list($gameId, $leftTeamOdds, $rightTeamOdds, $drawOdds, $gameResult) = explode(" ", trim(fgets($file)));
        $games[$gameId] = [
            'leftTeamOdds' => floatval($leftTeamOdds), // Коэффициент на победу левой команды
            'rightTeamOdds' => floatval($rightTeamOdds), // Коэффициент на победу правой команды
            'drawOdds' => floatval($drawOdds), // Коэффициент на ничью
            'gameResult' => $gameResult // Результат игры (L, R или D)
        ];
    }

    fclose($file);

    // Изначальный баланс
    $balance = 0;

    // Обработка каждой ставки
    foreach ($bets as $bet) {
        $gameId = $bet['gameId'];
        $betAmount = $bet['betAmount'];
        $betResult = $bet['betResult'];

        // Ищем игру по идентификатору
        if (isset($games[$gameId])) {
            $game = $games[$gameId];
            $odds = 0;

            // Определение коэффициента в зависимости от результата ставки и игры
            if ($betResult == 'L' && $game['gameResult'] == 'L') {
                $odds = $game['leftTeamOdds'];
            } elseif ($betResult == 'R' && $game['gameResult'] == 'R') {
                $odds = $game['rightTeamOdds'];
            } elseif ($betResult == 'D' && $game['gameResult'] == 'D') {
                $odds = $game['drawOdds'];
            }

            // Вычисляем баланс в зависимости от результата ставки
            if ($odds > 0) {
                $balance += $betAmount * $odds - $betAmount;
            } else {
                $balance -= $betAmount;
            }
        }
    }

    return $balance;
}

// Получаем все файлы в папке input с расширением .dat
$inputFiles = glob('input/*.dat');

// Проходим по каждому файлу и проверяем результат
foreach ($inputFiles as $inputFile) {
    $outputFile = 'output/' . basename($inputFile, '.dat') . '.ans';

    $calculatedBalance = calculateBalance($inputFile);
    $expectedBalance = trim(file_get_contents($outputFile));

    echo "Тест: " . basename($inputFile) . "\n";
    echo "Ожидаемый ответ: $expectedBalance\n";
    echo "Рассчитанный ответ: $calculatedBalance\n";

    if ($calculatedBalance == $expectedBalance) {
        echo "Тест пройден успешно!\n\n";
    } else {
        echo "Тест не пройден. Ошибка: \n";
        echo "Ожидалось: $expectedBalance\n";
        echo "Получено: $calculatedBalance\n\n";
    }
}
