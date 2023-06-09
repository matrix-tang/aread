<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once './MysqliDb.php';
require_once './config.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use \PhpOffice\PhpSpreadsheet\IOFactory;

// 配置
$highFile = "/Users/tony/Desktop/stock/high.xlsx"; // 一年新高选出来的股票
$date = date("Ymd", time());

$highData = readAXlsx($highFile);

$model = new MysqliDb($config);
$stockList = $model->where("insert_date", $date)->where("stock_type", 2)->get("stock");
if (count($stockList) > 0) {
    echo "------------------------------------------------ 一年新高股票，数据已经处理" . PHP_EOL;
    return;
}

foreach ($highData as $highKey => $highValue) {
    // 跳过第一行和最后一行
    if ($highKey == 1 || $highValue["R"] == "undefined") {
        continue;
    }
    $arr = explode("-", $highValue["I"], 3);
    if (count($arr) >= 3) {
        $insertData = [
            "stock_id" => "{$highValue["A"]}",
            "stock_name" => "{$highValue["B"]}",
            "stock_plate" => $arr[1],
            "stock_plate_desc" => "{$highValue["I"]}",
            "stock_type" => 2,
            "change" => "{$highValue["D"]}",
            "insert_date" => "{$date}",
        ];
        // print_r($insertData);
        $model->insert("stock", $insertData);
        echo "插入一年新高股票：" . json_encode($insertData) . PHP_EOL;
    }
}

function readAXlsx($file)
{
    $file = iconv("utf-8", "gb2312", $file);

    $objRead = IOFactory::createReader(IOFactory::READER_XLSX);
    // $canRead = $objRead->canRead($file);
    $objRead->setReadDataOnly(true);
    $obj = $objRead->load($file);
    $currSheet = $obj->getSheet(0);
    // var_dump($currSheet);
    $columnH = $currSheet->getHighestColumn();
    $columnCnt = Coordinate::columnIndexFromString($columnH);
    // echo $columnCnt . " 列" . PHP_EOL;
    $rowCnt = $currSheet->getHighestRow();
    // echo $rowCnt . " 行" . PHP_EOL;
    $data = [];
    for ($_row = 1; $_row <= $rowCnt; $_row++) {
        for ($_column = 1; $_column <= $columnCnt; $_column++) {
            $cellName = Coordinate::stringFromColumnIndex($_column);
            $cellId = $cellName . $_row;
            // $cell     = $currSheet->getCell($cellId);
            $data[$_row][$cellName] = trim($currSheet->getCell($cellId)->getFormattedValue());
        }
    }

    return $data;
}
