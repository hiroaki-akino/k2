<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成者：秋野浩朗（web1902)
概要　：ajax の時の DB 通信処理
更新日：2020/2/5

----------------------------------------------------------------------------------------- */



/* ----- 共通処理 ----------------------------------------------------------------------- */

include '../inc/config.php';
include '../inc/db_access.php';

// ここで使うinclude したファイルの変数。（必要に応じて var_dump で確認）

// ここで使う変数の宣言と初期値設定。
$search_item_word				= "";
$search_sql_item_word			= "";
$genre_array					= array();
$genre_chose_val_array			= array();
$display_number_array			= array("10件","20件","50件","全件");
$display_number_chose_val_array	= array();
$display_number					= "";
$display_order_array			= array("新着順","価格が高い順","価格が低い順","評価が高い順");
$display_order_chose_val_array	= array();
$display_order					= "";
$display_order_type				= "";
$item_number					= "";
$result_array					= array();
$result							= "";
$sql_output_item_array			= array();

// ここで使うSQL文の一覧表示と配列変数への設定。
// VSコードやとSQL文の最初のコマンド命令語を大文字にすると以降のコマンドも色変えて出してくれるからそれを適用。
// 個人的には全部小文字派やけど、便利なので最初の文字だけ大文字にしてる（横着？なんのことかね）
$sql_array = array(
	"index1"	=> "SELECT genre_name from k2g1_genre",
	// 商品件数表示のデフォルト文（ユーザによる検索情報の指定があれば各種条件を追記する）
	"index2"	=> "SELECT count(item_id) from k2g1_item,k2g1_genre,k2g1_seller
					where item_genre_id = genre_id and item_seller_id = seller_id and
					item_quantity != 0 and item_deleted = 0",
	// 商品一覧表示のデフォルト文（ユーザによる検索情報の指定があれば各種条件を追記する）
	"index3"	=> "SELECT item_id,item_name,item_seller_id,seller_name,seller_office_name,
					format(item_price,0) as 'item_price',item_quantity,item_image_path,condition_rank,
					(select ( sum(review_good) / (sum(review_good) + sum(review_bad)) )
	 				 from k2g1_item s_item
					 left join k2g1_order on item_id = order_item_id 
					 left join k2g1_review k2g1_review on order_id = review_order_id 
					 where m_item.item_seller_id = s_item.item_seller_id
					 group by s_item.item_seller_id
					) as 'evaluation'
					from k2g1_item m_item
					left join k2g1_seller on item_seller_id = seller_id 
					left join k2g1_genre on item_genre_id = genre_id 
					left join k2g1_order on item_id = order_item_id
					left join k2g1_condition on item_condition_id = condition_id
					where item_quantity != 0 and item_deleted = 0"
);

/* --------------------------------------------------------------------------------------- */



header("Content-Type: application/json; charset=UTF-8"); //ヘッダー情報の明記。必須。

switch($_POST["function_no"]){
	case "index1":
		// $search_item_word 					= htmlspecialchars($_POST["search_item_word"],ENT_QUOTES);
		// $genre_chose_val_array[0]			= $_POST["genre_name"];
		// $display_number_chose_val_array[0]	= $_POST["display_number"];
		// $display_order_chose_val_array[0]	= $_POST["display_order"];
		// switch($display_number_chose_val_array[0]){
		// 	case "10件":
		// 		$display_number	= 10;
		// 		break;
		// 	case "20件":
		// 		$display_number	= 20;
		// 		break;
		// 	case "50件":
		// 		$display_number	= 50;
		// 		break;
		// 	case "全件":
		// 		$display_number	= "all";
		// 		break;
		// }
		// switch($display_order_chose_val_array[0]){
		// 	case "新着順":
		// 		$display_order		= "item_time";
		// 		$display_order_type = "desc";
		// 		break;
		// 	case "価格が高い順":
		// 		$display_order		= "cast(item_price as signed)";
		// 		$display_order_type = "desc";
		// 		break;
		// 	case "価格が低い順":
		// 		$display_order		= "cast(item_price as signed)";
		// 		$display_order_type = "asc";
		// 		break;
		// 	case "評価が高い順":
		// 		$display_order		= "evaluation";
		// 		$display_order_type = "desc";
		// 		break;
		// }
		// if($search_item_word == ""){
		// 	// 検索条件の分岐（where 句の処理）
		// 	if($genre_chose_val_array[0] == "全て"){
		// 		// ◆キーワード：なし、ジャンル：全て（指定なし）、他：任意の選択項目
		// 		// 全件取得の為、処理なし。
		// 	}else{
		// 		// ◆キーワード：なし、ジャンル：選択項目、他：任意の選択項目
		// 		$sql_array["index3"] .= " AND genre_name = '$genre_chose_val_array[0]' ";
		// 		// 上記の条件の時は商品表示件数の条件も変更する
		// 		$sql_array["index2"] .= " AND genre_name = '$genre_chose_val_array[0]' ";
		// 	}
		// 	// 並び順の分岐（order 句の処理）
		// 	if($display_number == "all"){
		// 		// limit all にしたかったけど、SQLのverによって通らない時がある？
		// 		$sql_array["index3"] .= " order by {$display_order} {$display_order_type}";
		// 	}else{
		// 		$sql_array["index3"] .= " order by {$display_order} {$display_order_type} limit {$display_number}";
		// 	}
		// 	// SQL文の実行（検索条件に適合した商品情報と商品表示件数の取得処理）
		// 	$sql_output_item_array		  = sql($sql_array["index3"],true);
		// 	$sql_output_item_number_array = sql($sql_array["index2"],true);
		// }else{
		// 	// 明示的バインド処理（キャスト）をして曖昧検索（パラメータマーカー使う）するときはこうする。
		// 	// 参考URL：https://www.php.net/manual/ja/pdostatement.execute.php
		// 	// 参考URL：https://teratail.com/questions/96423
		// 	$search_sql_item_word .= "%";
		// 	$search_sql_item_word .= $search_item_word;
		// 	$search_sql_item_word .= "%";
		// 	// 検索条件の分岐（where 句の処理）
		// 	if($genre_chose_val_array[0] == "全て"){
		// 		// ◆キーワード：あり（プレースホルダー処理）、ジャンル：全て（指定なし）、他：任意の選択項目
		// 		// 以降の処理でも同じ文を使用。コードがうざいので以降はインデントなしで記載する。
		// 		$sql_array["index3"] .= " AND (item_name like ? or (
		// 									case when seller_office_name = '' or seller_office_name is NULL 
		// 										then seller_name like ? 
		// 										else seller_office_name like ? 
		// 									end)
		// 								) ";
		// 		// 上記の条件の時は商品表示件数も変更する。
		// 		$sql_array["index2"] .= " AND (item_name like ? or ( case when seller_office_name = '' or seller_office_name is NULL then seller_name like ? else seller_office_name like ? end)) ";
		// 	}else{
		// 		// ◆キーワード：あり（プレースホルダー処理）、ジャンル：選択項目、他：任意の選択項目
		// 		$sql_array["index3"] .= " AND (item_name like ? or ( case when seller_office_name = '' or seller_office_name is NULL then seller_name like ? else seller_office_name like ? end)) ";
		// 		$sql_array["index3"] .= " AND genre_name = '{$genre_chose_val_array[0]}' ";
		// 		// ◆上記の条件の時は商品表示件数も変更する。
		// 		$sql_array["index2"] .= " AND (item_name like ? or ( case when seller_office_name = '' or seller_office_name is NULL then seller_name like ? else seller_office_name like ? end)) ";
		// 		$sql_array["index2"] .= " AND genre_name = '{$genre_chose_val_array[0]}' ";
		// 	}
		// 	// 並び順の分岐（order 句の処理）
		// 	if($display_number == "all"){
		// 		$sql_array["index3"] .= " order by {$display_order} {$display_order_type} ";
		// 		$sql_array["index3"] .= " , item_time desc ";
		// 	}else{
		// 		$sql_array["index3"] .= " order by {$display_order} {$display_order_type} ";
		// 		$sql_array["index3"] .= " , item_time desc ";
		// 		$sql_array["index3"] .= " limit {$display_number}";
		// 	}
		// 	// SQL文の実行（検索条件に適合した商品情報と商品表示件数の取得処理）
		// 	$sql_output_item_array = sql($sql_array["index3"],true,$search_sql_item_word,$search_sql_item_word,$search_sql_item_word);
		// 	$sql_output_item_number_array = sql($sql_array["index2"],true,$search_sql_item_word,$search_sql_item_word,$search_sql_item_word);
		// }
		$page_now_no 						= $_POST["page_now_no"];
		$page_display_number 				= $_POST["page_display_number"];
		$search_item_word 					= htmlspecialchars($_POST["search_item_word"],ENT_QUOTES);
		$genre_chose_val_array[0]			= $_POST["genre_name"];
		$display_number_chose_val_array[0]	= $_POST["display_number"];
		$display_number_offset_val			= $page_now_no - 1;
		$display_order_chose_val_array[0]	= $_POST["display_order"];
			
		switch($display_number_chose_val_array[0]){
			case "10件":
				$display_number	= 10;
				$display_number_offset_val *= 10;
				break;
			case "20件":
				$display_number	= 20;
				$display_number_offset_val *= 20;
				break;
			case "50件":
				$display_number	= 50;
				$display_number_offset_val *= 50;
				break;
			case "全件":
				$display_number	= "all";
				break;
		}
		switch($display_order_chose_val_array[0]){
			case "新着順":
				$display_order		= "item_time";
				$display_order_type = "desc";
				break;
			case "価格が高い順":
				$display_order		= "cast(item_price as signed)";
				$display_order_type = "desc";
				break;
			case "価格が低い順":
				$display_order		= "cast(item_price as signed)";
				$display_order_type = "asc";
				break;
			case "評価が高い順":
				$display_order		= "evaluation";
				$display_order_type = "desc";
				break;
		}
		if($search_item_word == ""){
			// 検索条件の分岐（where 句の処理）
			if($genre_chose_val_array[0] == "全て"){
				// ◆キーワード：なし、ジャンル：全て（指定なし）、他：任意の選択項目
				// 全件取得の為、処理なし。
			}else{
				// ◆キーワード：なし、ジャンル：選択項目、他：任意の選択項目
				$sql_array["index3"] .= " AND genre_name = '$genre_chose_val_array[0]' ";
				// 上記の条件の時は商品表示件数の条件も変更する
				$sql_array["index2"] .= " AND genre_name = '$genre_chose_val_array[0]' ";
			}
			// 並び順の分岐（order 句の処理）
			if($display_number == "all"){
				// limit all にしたかったけど、SQLのverによって通らない時がある？
				$sql_array["index3"] .= " order by {$display_order} {$display_order_type}";
			}else{
				$sql_array["index3"] .= " order by {$display_order} {$display_order_type} limit {$display_number} offset {$display_number_offset_val}";
			}
			// SQL文の実行（検索条件に適合した商品情報と商品表示件数の取得処理）
			$sql_output_item_array		  = sql($sql_array["index3"],true);
			$sql_output_item_number_array = sql($sql_array["index2"],true);
		}else{
			// 明示的バインド処理（キャスト）をして曖昧検索（パラメータマーカー使う）するときはこうする。
			// 参考URL：https://www.php.net/manual/ja/pdostatement.execute.php
			// 参考URL：https://teratail.com/questions/96423
			$search_sql_item_word .= "%";
			$search_sql_item_word .= $search_item_word;
			$search_sql_item_word .= "%";
			// 検索条件の分岐（where 句の処理）
			if($genre_chose_val_array[0] == "全て"){
				// ◆キーワード：あり（プレースホルダー処理）、ジャンル：全て（指定なし）、他：任意の選択項目
				// 以降の処理でも同じ文を使用。コードがうざいので以降はインデントなしで記載する。
				$sql_array["index3"] .= " AND (item_name like ? or (
											case when seller_office_name = '' or seller_office_name is NULL 
												then seller_name like ? 
												else seller_office_name like ? 
											end)
										) ";
				// 上記の条件の時は商品表示件数も変更する。
				$sql_array["index2"] .= " AND (item_name like ? or ( case when seller_office_name = '' or seller_office_name is NULL then seller_name like ? else seller_office_name like ? end)) ";
			}else{
				// ◆キーワード：あり（プレースホルダー処理）、ジャンル：選択項目、他：任意の選択項目
				$sql_array["index3"] .= " AND (item_name like ? or ( case when seller_office_name = '' or seller_office_name is NULL then seller_name like ? else seller_office_name like ? end)) ";
				$sql_array["index3"] .= " AND genre_name = '{$genre_chose_val_array[0]}' ";
				// ◆上記の条件の時は商品表示件数も変更する。
				$sql_array["index2"] .= " AND (item_name like ? or ( case when seller_office_name = '' or seller_office_name is NULL then seller_name like ? else seller_office_name like ? end)) ";
				$sql_array["index2"] .= " AND genre_name = '{$genre_chose_val_array[0]}' ";
			}
			// 並び順の分岐（order 句の処理）
			if($display_number == "all"){
				$sql_array["index3"] .= " order by {$display_order} {$display_order_type} ";
				$sql_array["index3"] .= " , item_time desc ";
			}else{
				$sql_array["index3"] .= " order by {$display_order} {$display_order_type} ";
				$sql_array["index3"] .= " , item_time desc ";
				$sql_array["index3"] .= " limit {$display_number} offset {$display_number_offset_val}";
			}
			// SQL文の実行（検索条件に適合した商品情報と商品表示件数の取得処理）
			$sql_output_item_array = sql($sql_array["index3"],true,$search_sql_item_word,$search_sql_item_word,$search_sql_item_word);
			$sql_output_item_number_array = sql($sql_array["index2"],true,$search_sql_item_word,$search_sql_item_word,$search_sql_item_word);
		}
		
		// 商品件数のデータを分割。データ自体は既に取得ずみ。
		foreach($sql_output_item_number_array as $key => $val){
			foreach($val as $key2 => $val2){
				$item_number = $val2;
			}
		}
		
		$result_array["item_number"] = $item_number;
		$result_array["item_array"]  = $sql_output_item_array;
		
		// 最後にjson形式に変更
		echo json_encode($result_array);
		exit;
		break;
}

?>
