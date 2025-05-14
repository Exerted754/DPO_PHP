<?php
$sections = simplexml_load_file('sections.xml');
$products = simplexml_load_file('products.xml');

// Собираем разделы
$data = [];
foreach ($sections->Раздел as $s) {
    $id = str_replace('-', '', (string)$s->Ид);
    $data[$id] = ['Ид' => (string)$s->Ид, 'Наименование' => (string)$s->Наименование, 'Товары' => []];
}

// Добавляем товары в разделы
foreach ($products->Товар as $p) {
    $prod = ['Ид' => (string)$p->Ид, 'Наименование' => (string)$p->Наименование, 'Артикул' => (string)$p->Артикул];
    foreach ($p->Разделы->ИдРаздела as $sid) {
        if (isset($data[$id])) $data[$id]['Товары'][] = $prod;
    }
}

// Создаём XML
$xml = new SimpleXMLElement('<ЭлементыКаталога><Разделы/></ЭлементыКаталога>');
foreach ($data as $d) {
    $s = $xml->Разделы->addChild('Раздел');
    $s->addChild('Ид', $d['Ид']);
    $s->addChild('Наименование', $d['Наименование']);
    $p = $s->addChild('Товары');
    foreach ($d['Товары'] as $t) {
        $n = $p->addChild('Товар');
        $n->addChild('Ид', $t['Ид']);
        $n->addChild('Наименование', $t['Наименование']);
        $n->addChild('Артикул', $t['Артикул']);
    }
}

$xml->asXML('output.xml');
?>