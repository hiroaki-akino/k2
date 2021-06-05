<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成者：秋野浩朗（web1902)
概要　：各種売り上げ集計処理
更新日：2020/2/11

----------------------------------------------------------------------------------------- */



/* ----- 共通処理 ----------------------------------------------------------------------- */

// include '/Applications/MAMP/htdocs/web/k2/k2_config.php';
// include '/Applications/MAMP/htdocs/web/k2/inc/config.php';
// include '/Applications/MAMP/htdocs/web/k2/inc/db_access.php';

// echo(include '/Applications/MAMP/htdocs/web/k2/k2_config.php');
// echo(include '/Applications/MAMP/htdocs/web/k2/inc/config.php');
// echo(include '/Applications/MAMP/htdocs/web/k2/inc/db_access.php');


// ここで使う変数の宣言と初期値設定。
$sql_output_daily_sales_array	= array();

// ここで使うSQL文の一覧表示と配列変数への設定。
$sql_array = array(
	// 出品者毎の毎日の売上を集計する。
	"sum_add1"	=> "SELECT date_sub(CURRENT_DATE(),interval 1 day),
					item_seller_id,seller_name,format(sum(item_price * order_quantity),0) as 'sum' 
					from k2g1_order
					left join k2g1_item on order_item_id = item_id
					left join k2g1_seller on item_seller_id = seller_id
					where date(order_time) = date_sub(CURRENT_DATE(),interval 1 day)
					group by item_seller_id",
	"sum_add2"	=> "SELECT cast(order_time as date),item_seller_id,seller_name,
					format(sum(item_price * order_quantity),0) as 'sum' 
					from k2g1_order
					left join k2g1_item on order_item_id = item_id
					left join k2g1_seller on item_seller_id = seller_id
					group by order_time,item_seller_id",
	"sum_add3"	=> "select * from k2g1_item"
);

/* --------------------------------------------------------------------------------------- */

// localhostでcronを動かす時のPDOのhost名は「localhost」ではなく「127.0.0.1」じゃないとダメ。
// pdo 処理においてmysqlサーバーは「localhost」と指定されたら、ライブラリからオーバーライドしてローカルソケットに接続するので、
// cron使用時はオーバーライドに失敗して意図した場所に接続できない。
// https://mgng.mugbum.info/1150
$dsn = "mysql:host=127.0.0.1;dbname=k2g1";
$user = "root";
$pass = "root";
$sql  = $sql_array["sum_add1"];

// 実際のPDOの処理
try{
	$db = new PDO($dsn,$user,$pass);
	$db->exec("SET NAMES utf8");
	$db->setAttribute(PDO::ATTR_CASE,PDO::CASE_LOWER);
	// プリペアドステートメント
	$db_result = $db->prepare($sql);
	// SQL文の実行
	if(!$db_result->execute()){
		echo "【SQL:err1】［内容］構文エラー(文法or記述ミス)［入力したSQL文］",$sql;
		//return false;
	}
	$count = $db_result->rowCount();
	if($count !== FALSE){
		if($count == 0){
			if($empty){
				// 空値OK な SQL文の場合の処理。NULLを返す。
				$db = NULL;
				//return NULL;
			}else{
				// 空値NG な SQL文で空値になった時の為のエラー処理。
				echo "【SQL:err2】［内容］対象行なし［実行したSQL文］",$sql;
				$db = NULL;
				//return false;
			}
		}else{
			$rows = $db_result->fetchall(PDO::FETCH_ASSOC);
			$result = $rows;
		}
	}else{
		echo "【SQL:err3】［内容］構文エラー（実行時）［実行したSQL文］",$sql;
		$db = NULL;
		//return false;
	}
	$db = NULL;
}
catch (Exception $e){
	echo "MSG:" .$e->getMessage()."<br>";
	echo "CODE:".$e->getCode()."<br>";
	echo "LINE:".$e->getLine()."<br>";
	$db = NULL;
	//return false;
}

// $sql_output_daily_sales_array = sql($sql_array["sum_add2"],true);
// var_dump($sql_output_daily_sales_array);
// クーロン使用時はファイルを絶対パスにする。
if(($fp = fopen("/Applications/MAMP/htdocs/web/k2/file/daily_sales.csv","a")) !== FALSE){
	//foreach ($sql_output_daily_sales_array as $line){
	if(!empty($result)){
		foreach ($result as $line){
			fputcsv($fp,$line);
		}
	}else{
		fputcsv($fp,array(date('Y/m/d',mktime(0,0,0,date('n'),date('j')-1,date('Y'))),"取引なし"));
	}
	fclose($fp);
}
?>