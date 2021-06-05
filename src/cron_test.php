<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成：秋野浩朗（web1902)
概要：確認用

----------------------------------------------------------------------------------------- */

// test.txtにクーロンテストを書込む。
// $file = "{$_SERVER['DOCUMENT_ROOT']}/web/k2/file/test.txt";
$file = "/Applications/MAMP/htdocs/web/k2/file/test.txt";
$current = file_get_contents($file);
date_default_timezone_set('Asia/Tokyo');
$current .= date("Y-m-d H:i:s")."（クーロンテスト！）\n";
file_put_contents($file, $current);

?>