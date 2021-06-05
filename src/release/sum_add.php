<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成者：秋野浩朗（web1902)
概要　：各種売り上げ集計処理
更新日：2020/2/12

----------------------------------------------------------------------------------------- */



/* ----- 共通処理 ----------------------------------------------------------------------- */

include "{$_SERVER['DOCUMENT_ROOT']}/19/web19g1/inc/config.php";
include "{$_SERVER['DOCUMENT_ROOT']}/19/web19g1/inc/db_access.php";

// ここで使う変数の宣言と初期値設定。
$sql_output_daily_sales_array	= array();

// ここで使うSQL文の一覧表示と配列変数への設定。
$sql_array = array(
	// 出品者毎の毎日の売上を集計する。
	"sum_add1"	=> "SELECT item_seller_id,seller_name,sum(item_price * order_quantity) as 'sum' 
					from k2g1_order
					left join k2g1_item on order_item_id = item_id
					left join k2g1_seller on item_seller_id = seller_id
					where date(order_time) = date_sub(CURRENT_DATE(),interval 1 day)
					group by item_seller_id",
	"sum_add2"	=> "SELECT date_sub(CURRENT_DATE(),interval 1 day),item_seller_id,seller_name,
					sum(item_price * order_quantity) as 'sum' 
					from k2g1_order
					left join k2g1_item on order_item_id = item_id
					left join k2g1_seller on item_seller_id = seller_id
					group by item_seller_id",
);

/* --------------------------------------------------------------------------------------- */



$sql_output_daily_sales_array = sql($sql_array["sum_add2"],true);
// var_dump($sql_output_daily_sales_array);
// クーロン使用時はファイルを絶対パスにする。
if(($fp = fopen("{$_SERVER['DOCUMENT_ROOT']}/19/web19g1/file/daily_sales.csv","a")) !== FALSE){
	foreach ($sql_output_daily_sales_array as $line){
		fputcsv($fp,$line);
	}
	fclose($fp);
}

?>