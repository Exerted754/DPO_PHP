<?php
$input = file_get_contents('test_c.json');
$data = json_decode($input, true);

// Функция для формирования SQL-запроса
function buildSqlQuery($data) {
    $query = '';
    
    // SELECT
    if (isset($data['select']) && !empty($data['select'])) {
        $query .= 'select ' . implode(', ', $data['select']) . "\n";
    } else {
        $query .= "select *\n";
    }
    
    // FROM
    if (isset($data['from']) && !empty($data['from'])) {
        $query .= 'from ' . $data['from'] . "\n";
    } else {
        throw new Exception('Field "from" is required');
    }
    
    // WHERE
    if (isset($data['where']) && !empty($data['where'])) {
        $query .= 'where ' . buildWhereCondition($data['where']) . "\n";
    }
    
    // ORDER BY
    if (isset($data['order']) && !empty($data['order'])) {
        foreach ($data['order'] as $field => $direction) {
            $query .= 'order by ' . $field . ' ' . $direction . "\n";
            break; // Согласно условию, только одно поле для сортировки
        }
    }
    
    // LIMIT
    if (isset($data['limit']) && !empty($data['limit'])) {
        $query .= 'limit ' . $data['limit'] . ';';
    } else {
        $query = rtrim($query) . ';';
    }
    
    return $query;
}

// Функция для построения условий WHERE
function buildWhereCondition($conditions, $operator = 'and') {
    $result = [];
    
    foreach ($conditions as $key => $value) {
        // Проверяем, является ли ключ оператором AND или OR
        if (strpos($key, 'and_') === 0) {
            $subCondition = buildWhereCondition($value, 'and');
            $result[] = "($subCondition)";
        } elseif (strpos($key, 'or_') === 0) {
            $subCondition = buildWhereCondition($value, 'or');
            $result[] = "($subCondition)";
        } else {
            // Обычное условие
            $field = $key;
            $operation = '';
            
            // Определяем операцию и поле
            if (strpos($key, '<=') === 0) {
                $operation = '<=';
                $field = substr($key, 2);
            } elseif (strpos($key, '>=') === 0) {
                $operation = '>=';
                $field = substr($key, 2);
            } elseif (strpos($key, '<') === 0) {
                $operation = '<';
                $field = substr($key, 1);
            } elseif (strpos($key, '>') === 0) {
                $operation = '>';
                $field = substr($key, 1);
            } elseif (strpos($key, '=') === 0) {
                $operation = '=';
                $field = substr($key, 1);
            } elseif (strpos($key, '!') === 0) {
                $operation = '!';
                $field = substr($key, 1);
            }
            
            // Формируем условие в зависимости от типа значения и операции
            if ($value === null) {
                // Для null значений
                if ($operation === '=' || $operation === '' || $operation === '==') {
                    $result[] = "$field is null";
                } elseif ($operation === '!') {
                    $result[] = "$field is not null";
                }
            } elseif (is_bool($value)) {
                // Для булевых значений
                if ($operation === '=' || $operation === '' || $operation === '==') {
                    $result[] = "$field is " . ($value ? 'true' : 'false');
                } elseif ($operation === '!') {
                    $result[] = "$field is not " . ($value ? 'true' : 'false');
                }
            } elseif (is_string($value)) {
                // Для строковых значений
                if ($operation === '=') {
                    $result[] = "$field = '$value'";
                } elseif ($operation === '!') {
                    $result[] = "$field != '$value'";
                } elseif (in_array($operation, ['<', '<=', '>', '>='])) {
                    $result[] = "$field $operation '$value'";
                } else {
                    // Для обычного поля без операции используем LIKE
                    $result[] = "$field like '$value'";
                }
            } elseif (is_numeric($value)) {
                // Для числовых значений
                if ($operation === '=') {
                    $result[] = "$field = $value";
                } elseif ($operation === '!') {
                    $result[] = "$field != $value";
                } elseif (in_array($operation, ['<', '<=', '>', '>='])) {
                    $result[] = "$field $operation $value";
                } else {
                    $result[] = "$field = $value";
                }
            }
        }
    }
    
    return implode(" $operator ", $result);
}

// Выводим результат
try {
    echo buildSqlQuery($data);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
