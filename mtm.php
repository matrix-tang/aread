<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once './MysqliDb.php';
require_once './config.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use \PhpOffice\PhpSpreadsheet\IOFactory;

// 配置
$mtmFile = "/Users/tony/Desktop/stock/mtm.xlsx"; // 动量模型选出来的股票
$allFile = "/Users/tony/Desktop/stock/all.xlsx"; // 所有股票
$date = date("Ymd", time());

$mtmData = readAXlsx($mtmFile);
$allData = readAXlsx($allFile);

// 处理mtm数据，根据二级行业到二维数组
$mtmArr = [];
foreach ($mtmData as $mtmKey => $mtmValue) {
    $arr = explode("-", $mtmValue["L"], 3);
    $count = count($arr);
    if ($count >= 3) {
        $mtmArr[$arr[1]][] = $mtmValue;
    }
}
// print_r($mtmArr);

// 处理all数据，根据二级行业到二维数组
$allArr = [];
foreach ($allData as $allKey => $allValue) {
    // print_r($allKey);
    $arr = explode("-", $allValue["F"], 3);
    $count = count($arr);
    if ($count >= 3) {
        $allArr[$arr[1]][] = $allValue;
    }
}
// print_r($allArr);

$model = new MysqliDb($config);
$stockList = $model->where("insert_date", $date)->where("stock_type", 1)->get("stock");
if (count($stockList) > 0) {
    echo "------------------------------------------------插入动量模型，数据已经处理" . PHP_EOL;
    return;
}
// 计算动量分值
foreach ($mtmArr as $key => $value) {
    // 上榜数量
    $onListCnt = count($value);
    // 所有成分股数量
    $all2StockCnt = count($allArr[$key]);
    // echo "------------" . $onListCnt . "  " . $all2StockCnt . PHP_EOL;

    // 动量分值
    $mtmScore = ($onListCnt / $all2StockCnt) * $onListCnt;
    // echo $key . " -> " . $mtmScore . PHP_EOL;

    // 插入动量模型选取的股票
    foreach ($value as $stockInfo) {
        $insertArr = [
            "stock_id" => "{$stockInfo["A"]}",
            "stock_name" => "{$stockInfo["B"]}",
            "stock_plate" => "{$key}",
            "stock_plate_desc" => "{$stockInfo['L']}",
            "stock_type" => 1,
            "change" => "{$stockInfo['D']}",
            "insert_date" => "{$date}",
        ];
        $model->insert("stock", $insertArr);
        echo "插入动量模型股票：" . json_encode($insertArr) . PHP_EOL;
    }

    // 插入动量模型
    $insertMtmArr = [
        "stock_plate" => "{$key}",
        "stock_score" => $mtmScore,
        "insert_date" => "{$date}",
    ];
    $model->insert("mtm", $insertMtmArr);
    echo "插入动量模型：" . json_encode($insertMtmArr) . PHP_EOL;

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
