<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成者：山本賢澄（web1921)
概要　：エクセルファイル「k2_議事メモ 兼 プログラム等一覧」に記載しているファイルの概要
更新日：2020/2/4

【注意】
本日分の作業は、本日の日付のフォルダ内のファイルで作業する。
過去フォルダのファイルは更新しないこと！

----------------------------------------------------------------------------------------- */



/* ----- 共通処理 ----------------------------------------------------------------------- */

include '../inc/config.php';
include '../inc/db_access.php';
include '../inc/functions.php';
include '../inc/variables.php';

// ここで使う include したファイルの変数。（必要に応じて var_dump で確認）
$pflag;					// 中身：false
$head_common_tag;		// 中身：head タグ内で規定するメタタグとか
$header_common_tag;		// 中身：header タグ内で規定するタイトルタグとか
$footer_common_tag;		// 中身：footer タグ内で規程するタグとか

// ここで使う変数の宣言と初期値設定。
$_SESSION["pre_page"] 						= basename(__FILE__, ".php");
$item_genre_id								= "";						// 出品登録用商品分類
$item_name									= "";						// 出品登録用商品名
$item_condition_id							= "";						// 出品登録用商品状態
$item_price									= "";						// 出品登録用商品単価
$item_description							= "";						// 出品登録用商品説明
$item_quantity								= "";						// 兼用在庫数
$filename									= "";						// ファイルアップロード用ファイル名
$fileinfo									= "";						// ファイルアップロード用ファイル情報
$ext										= "";						// ファイルアップロード用拡張子
$movepath									= "";						// ファイルアップロード用ファイル移動先
$moveok										= "";						// ファイルアップロード確認用移動完了
$err_flag									= 0;						// ファイルアップロード確認用エラーフラグ
$item_image_path							= "../img/default.jpg";		// ファイルアップロード用
$msg										= array();					// 検証用表示変数
$genre_array								= array(					// セレクトボックス用分類配列
	1										=> "パソコン",
	2										=> "PCパーツ",
	3										=> "スマホ",
	4										=> "カメラ",
	5										=> "デジタルオーディオプレーヤー",
	6										=> "オーディオ",
	7										=> "美容家電",
	8										=> "健康家電",
	9										=> "テレビ",
	10										=> "レコーダー",
	11										=> "電子辞書",
	12										=> "冷蔵庫",
	13										=> "洗濯機",
	14										=> "キッチン家電",
	15										=> "その他"
);
$condition_array							= array(					// セレクトボックス用商品状態配列
	1										=> "未使用",
	2										=> "美品",
	3										=> "目立ったキズや汚れなし",
	4										=> "キズや汚れあり",
	5										=> "ジャンク"
);
$sql_output_seller_mypage_item_array		= array();					// データベース取得用商品各種情報
$sql_output_seller_mypage_item_quantity		= "";						// 在庫数変動エラー用商品在庫数取得変数
$sql_input_seller_mypage_item_quantity		= array();					// 在庫数変更用変更数
$sql_output_seller_mypage_order_array		= array();
$section_array								= array(					// セクションごとの名前とID
	"item_list"								=> "seller_mypage_section1a",		// 在庫リストのセクション
	"item_register"							=> "seller_mypage_section1b",		// 出品登録のセクション
	"order_list"							=> "seller_mypage_section1c",		// 受注リストのセクション
	"my_information"						=> "seller_mypage_section1d",		// 会員情報のセクション
	"order_history"							=> "seller_mypage_section1e"		// 購入履歴のセクション
);
$max_file_size								= 2097152;
$tab_btn									= "seller_mypage_radio1a";			// デフォルトタブの切替用変数
$tab_section								= "seller_mypage_section1a";		// デフォルトタブの切替用変数
$order_user									= 0;

// ここで使うSQL文の一覧表示と配列変数への設定。
// 注意：変数の値などを使用してSQL文を作成したい時は「?」に置き換えて表示する。
// 注意：SQL() の引数（SQL文用の引数は３つ目以降に記載）は「?」に指定した値の順に記載する。
$sql_array = array(
	"seller_mypage1"	=> "select item_id,item_name,genre_name,condition_rank,item_price,item_quantity,item_image_path
							from k2g1_item left join k2g1_genre on item_genre_id = genre_id
							left join k2g1_condition on item_condition_id = condition_id
							where item_seller_id = ? and item_deleted = 0",
	"seller_mypage2"	=> "select item_quantity from k2g1_item where item_id = ?",
	"seller_mypage3"	=> "UPDATE k2g1_item SET item_quantity = ? WHERE item_id = ?",
	"seller_mypage4"	=> "UPDATE k2g1_item SET item_deleted = 1 WHERE item_id = ?",
	"seller_mypage5"	=> "INSERT INTO k2g1_item SET item_name = ?,item_seller_id = ?,item_price = ?,item_quantity = ?,item_condition_id = ?,item_genre_id = ?,item_description = ?,item_image_path = ?,item_time = now()",
	"seller_mypage6"	=> "select item_id from k2g1_item where item_time = (select max(item_time) from k2g1_item where item_seller_id = ?)",
	"seller_mypage7"	=> "UPDATE k2g1_item SET item_image_path = ? WHERE item_id = ?",
	"seller_mypage8"	=> "DELETE FROM k2g1_item WHERE item_id = ?",
	"seller_mypage9"	=> "select order_id
							,order_item_id
							,order_quantity
							,order_evaluated
							,order_shipped
							,order_time
							,item_name
							,item_seller_id
							,item_price
							,item_image_path
							,genre_name
							,condition_rank
							,seller_name
							,seller_office_name
							from k2g1_order,k2g1_item,k2g1_genre,k2g1_seller,k2g1_condition
							where k2g1_order.order_item_id = k2g1_item.item_id
							and k2g1_item.item_genre_id = k2g1_genre.genre_id
							and k2g1_seller.seller_id = k2g1_item.item_seller_id
							and k2g1_condition.condition_id = k2g1_item.item_condition_id
							and k2g1_order.order_user_id = ?
							order by k2g1_order.order_time desc",
	"seller_mypage10"	=> "select * from k2g1_seller where seller_id = ?",
	"seller_mypage11"	=> "UPDATE k2g1_seller SET seller_name = ?,seller_office_name = ?,seller_high_postalcode = ?,seller_low_postalcode = ?,seller_address_1 = ?,seller_address_2 = ?,seller_address_3 = ? WHERE seller_id = ?",
	"seller_mypage12"	=> "select order_id,item_id,item_name,buyer_id,buyer_name,seller_id,seller_name,order_quantity,order_shipped,order_high_postalcode,order_low_postalcode,order_address_1,order_address_2,order_address_3,item_price
							from k2g1_order
							join k2g1_item on order_item_id = item_id
							left join k2g1_buyer on order_user_id = buyer_id
							left join k2g1_seller on order_user_id = seller_id
							where item_seller_id = ?
							order by order_shipped asc,order_time desc",
	"seller_mypage13"	=> "UPDATE k2g1_order SET order_shipped = 1 where order_id = ?"
);

/* 【！超重要！】SQL文の書き方とsql() の使い方
例1			  ：引数$val, $val2 を基にSQLからDBを参照したい場合
作成したいSQL文	：select * from k2g1_item where item_name = ${val} and item_genre_id = ${val2};
配列に記載する文 ：select * from k2g1_item where item_name = ? and item_genre_id = ? ;
sql()の記載方法	：sql($sql_array["index1"],true,$val,$val2);

例２		   ：引数なしでSQLからDBを参照したい場合
作成したいSQL文	：select * from k2g1_item;
配列に記載する文 ：select * from k2g1_item;
sql()の記載方法	：sql($sql_array["index1"],true);
備考			：sql()の引数は３つ目以降は省略可能。上位２つは必須。

*/



/* --------------------------------------------------------------------------------------- */
function h($text){
	return htmlspecialchars($text,ENT_QUOTES);
}

// ポスト処理
if($_SERVER["REQUEST_METHOD"] == "POST"){
	$pflag = true;
	// 【12-0】タブ切り替え処理
			$tab_btn		= "seller_mypage_radio1".$_POST["seller_mypage_tab"];
			$tab_section	= "seller_mypage_section1".$_POST["seller_mypage_tab"];

	// 各種機能の処理分岐
	switch($_POST["submit"]){
		/* ---- 【12a-2】在庫数の増減処理 ---- */
		case "反映":
			$item_id 		= (int)htmlspecialchars($_POST["item_id"],ENT_QUOTES);
			$item_quantity	= (int)(htmlspecialchars($_POST["item_quantity"],ENT_QUOTES));
			$item_changes	= (int)(htmlspecialchars($_POST["item_changes"],ENT_QUOTES));
			// 処理時点での在庫数の取得
			$sql_output_seller_mypage_item_quantity = sql($sql_array["seller_mypage2"],false,$item_id);
			// 表示時点での在庫数と比較
			if($sql_output_seller_mypage_item_quantity[0]["item_quantity"] === $item_quantity){
				// 減数が在庫数より大きい場合はエラー
				if(($sql_output_seller_mypage_item_quantity[0]["item_quantity"] + ($item_chenges)) < 0){
					$section1a_msg 	= "在庫数を0未満にはできません。";
				}else{
					$sql_update_item_quantity = $sql_output_seller_mypage_item_quantity[0]["item_quantity"] + $item_changes;
					// 在庫数の更新
					if(false === sql($sql_array["seller_mypage3"],true,$sql_update_item_quantity,$item_id)){
						$section1a_msg = "在庫数更新エラー。";
					}
				}
			}
			break;
		/* ---- 【12a-3】在庫の削除処理 ---- */
		case "削除":
			$item_id 		= (int)htmlspecialchars($_POST["item_id"],ENT_QUOTES);
			if((sql($sql_array["seller_mypage4"],true,$item_id)) === false){
				$section1a_msg = "削除処理に失敗しました。";
			}
			break;
		/* ---- 【12b-1】出品登録処理 ---- */
		case "登録":
			$item_genre_id		= htmlspecialchars($_POST["item_genre_id"],ENT_QUOTES);
			$item_name			= htmlspecialchars($_POST["item_name"],ENT_QUOTES);
			$item_condition_id	= htmlspecialchars($_POST["item_condition_id"],ENT_QUOTES);
			$item_price			= htmlspecialchars($_POST["item_price"],ENT_QUOTES);
			$item_description	= htmlspecialchars($_POST["item_description"],ENT_QUOTES);
			$item_quantity		= htmlspecialchars($_POST["item_quantity"],ENT_QUOTES);

			if($err_flag === 0){
				// 画像以外のデータをDBに書き込み(update)
				if((sql($sql_array["seller_mypage5"],true,$item_name,$_SESSION["user"]["id"],$item_price,$item_quantity,$item_condition_id,$item_genre_id,$item_description,$item_image_path)) === false){
					$section1b_msg = "商品の登録に失敗しました。";
				// 書き込んだデータの商品IDを取得
				}elseif(($sql_output_seller_mypage_item_array = sql($sql_array["seller_mypage6"],false,$_SESSION["user"]["id"])) === false){
					$section1b_msg = "商品の登録に失敗しました。";
				}else{
					$item_id = $sql_output_seller_mypage_item_array[0]["item_id"];
				}
			}
			if(isset($_POST["upfile"])){
				// ファイルが選択されている場合の処理
				while(true){
					if(strlen($_FILES["upfile"]["name"]) == 0){
						$section1b_msg = "ファイルが正しく指定されていません。";
						$err_flag = 1;
						break;
					}
					$filename = strtolower($_FILES["upfile"]["name"]);
					$fileinfo = pathinfo($filename);
					$ext = $fileinfo["extension"];
					if($_FILES["upfile"]["size"] == 0){
						$section1b_msg = "ファイルが存在しないか、ファイルの内容が空です。";
						$err_flag = 1;
						break;
					}
					if((int)PHP_VERSION < 7){
						if(strncmp(strtoupper(PHP_OS), "WIN", 3) == 0){
							$filename = mb_comvert_encoding($filename, "SJIS", "UTF-8");
						}
					}
					$movepath = "../img/".$item_id.".".$ext;
					$moveok = move_uploaded_file($_FILES["upfile"]["tmp_name"],$movepath);
					if(!$moveok){
						$section1b_msg = "画像ファイルのアップロードに失敗しました。";
						$err_flag = 1;
						break;
					}
					$item_image_path = $movepath;
					break;
				}
			}
			switch($err_flag){
				case 0:
					// ファイルアップロード完了でファイルパス更新
					sql($sql_array["seller_mypage7"],true,$item_image_path,$item_id);
					$section1b_msg = "商品の登録が完了しました。";
					break;
				case 1:
					// ファイルアップロード失敗で該当データ削除
					sql($sql_array["seller_mypage8"],true.$item_id);
					$section1b_msg = "画像ファイルのアップロードに失敗しました。違う画像でお試しください。";
					break;
			}
			break;
		// 【】出荷処理
		case "出荷":
			switch(sql($sql_array["seller_mypage13"],true,$_POST["order_id"])){
				case null:
					$section1c_msg = "出荷しました。";
					break;
				case false:
					$section1c_msg = "出荷処理に失敗しました。";
					break;
				default:
					break;
			}
			break;
		// 【】会員情報変更処理
		case '確定':
			$user_name			= h($_POST["seller_name"]);
			$high_postalcode	= h($_POST["seller_high_postalcode"]);
			$low_postalcode		= h($_POST["seller_low_postalcode"]);
			$address1			= h($_POST["seller_address_1"]);
			$address2			= h($_POST["seller_address_2"]);
			$address3			= h($_POST["seller_address_3"]);
			$user_id			= h($_SESSION["user"]["id"]);
			$office_name		= h($_POST["seller_office_name"]);
			if(($db_up = sql($sql_array["seller_mypage11"],true,$user_name,$office_name,$high_postalcode,$low_postalcode,$address1,$address2,$address3,$user_id)) === false){
				$section1d_msg = "会員情報の変更に失敗しました。";
			}
			$_SESSION["user"]["name"] = $user_name;
			header("Location: ".$_SERVER["SCRIPT_NAME"]);
			exit;
			break;
		// 【】出品者評価ページへの遷移処理
		case "出品者評価":
			$_SESSION['evaluate_form']['order_id'] = $_POST["order_id"];
			//$_SESSION['evaluate_form']['item_id'] = $_POST["item_id"];
			//$_SESSION['evaluate_form']['seller_id'] = $_POST["seller_id"];
			header("Location: evaluate_form.php");
			exit;
			break;
	}
}else{
	// GET時の不正アクセスの制御処理（直リンクアクセスはindex.phpにリダイレクト。詳細な処理は config.php を確認）
	session_seller_user_check($_SESSION["user"]);
}

// GETかPOSTにかかわらず必要な処理をここ以降で書く
// 【12a-1】在庫リスト初期表示のためのデータ取得
if(false === ($sql_output_seller_mypage_item_array = sql($sql_array["seller_mypage1"],true,$_SESSION["user"]["id"]))){
	$section1a_msg = "在庫データの読込に失敗しました。";
}
// 【】受注リスト(出荷リスト)データ取得
if(false === ($sql_output_seller_mypage_order_array = sql($sql_array["seller_mypage12"],true,$_SESSION["user"]["id"]))){
	$section1c_msg = "受注データの読込に失敗しました。";
}
// 購入履歴情報の取得
if(false === ($sql_output_seller_mypage_history_array = sql($sql_array["seller_mypage9"],true,$_SESSION["user"]["id"]))){
	$sql_output_seller_mypage_history_array = array();
	$section1e_msg = "購入履歴の読込に失敗しました。";
}
// 会員情報の取得
$sql_output_seller_mypage_user_array = sql($sql_array["seller_mypage10"],true,$_SESSION["user"]["id"]);
?>

<!DOCTYPE html>
<html lang="ja">
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#">
	<?=$head_common_tag?>
	<script>
		// デフォルトタブの表示関数
		window.onload = function(){
			get("<?=$tab_btn?>").checked = true;
			get("<?=$tab_section?>").style.display = "block";
		}
	</script>
	<style>
/* ======== タブ ======== */
		.seller_mypage_nav1{
			display: flex;
			flex-direction: row;
			border-bottom: solid 5px var(--color-light-gray);
			justify-content: space-between;
		}
		.seller_mypage_label1{
			border				: dotted 1px #aaaaaa;
			width				: 5em;
			height				: 2.5em;
			background-color	: var(--color-light-navy);
			color				: white;
			text-align			: center;
			vertical-align		: bottom;
			padding				: 0.5em;
			width				: 20%;
		}
		.seller_mypage_label:hover{
			opacity	: 0.5;
		}
		.seller_mypage_radio1{
			display: none;
		}
		.seller_mypage_section1{
			display: none;
		}
		.seller_mypage_radio1:checked + .seller_mypage_label1{
			background-color: var(--color-orange);
			color			: black;
			font-weight		: bold;
		}
		.seller_mypage_section1{
			width			: 100%;
		}
		/* css4で出来るらしい処理(:has)
		main:has(#seller_mypage_radio1a:checked)>#seller_mypage_section1a,
		main:has(#seller_mypage_radio1b:checked)>#seller_mypage_section1b,
		main:has(#seller_mypage_radio1c:checked)>#seller_mypage_section1c,
		main:has(#seller_mypage_radio1d:checked)>#seller_mypage_section1d,
		main:has(#seller_mypage_radio1e:checked)>#seller_mypage_section1e{
			display: block;
		}*/
/* ======== 在庫リスト ======== */
		.overflow{
			max-height	: 80vh;
			border		: solid 1px var(--color-light-gray);
		}
		.item_list{
			border-bottom	: dotted 1px var(--color-light-gray);
			padding			: 1em;
		}
		.item_list>h4{
			margin	: 0.5em;
		}
		h4 input{
			height	: 2em;
			width	: 3em;
		}
		.img_div{
			min-width	: 10%;
			max-width	: 10em;
		}
		.img_div>img{
			width	: 100%;
			height	: auto;
		}
		#seller_mypage_section1a .res_row>div{
			padding	: 1em 3em;
		}
		.res_row>div>div{
			margin	: 3px 0;
		}
		.item_list input[type="submit"]{
			width		: 3em;
			height		: 2.5em;
			font-size	: 12px;
			margin		: 0;
		}
		.item_list input[type="number"]{
			width		: 3em;
			height		: 2em;
		}

/* ======== 出品登録 ======== */
		.seller_mypage_section1b_div1{
			width		: 30em;
			padding		: 1em;
			max-width	: 100%;
		}
		#seller_mypage_section1b_div2a{
			display		: flex;
			max-width	: 100%;
		}
		.seller_mypage_section1b_div4{
			border			: solid 1px;
			height			: 2em;
			border-collapse	: collapse;
		}
		.seller_mypage_section1b_textarea1{
			height: 15em;
		}
		#seller_mypage_section1b select{
			border		: none;
			font-size	: 1em;
			height		: 100%;
			width		: 100%;
			display		: block;
		}
		#seller_mypage_section1b input[type="text"],
		#seller_mypage_section1b input[type="file"]{
			border		: none;
			font-size	: 1em;
			height		: 100%;
			width		: 100%;
			display		: block;
		}
		#seller_mypage_section1b textarea{
			border		: none;
			font-size	: 1.3em;
			height		: 97%;
			width		: 97%;
			display		: block;
		}
		#seller_mypage_section1b textarea:hover{
			background-color : var(--color-light-orange);
		}
		
		.column1{
			width		: 5em;
			text-align	: center;
			max-width	: 30%;
		}
		.column2{
			width		: 20em;
			max-width	: 70%;
		}
		#seller_mypage_section1b_div2b{
			text-align	: right;
			width		: 80%;
		}
/* ======== 受注リスト ======== */
		.seller_mypage_section1c_div1a{
			border		: solid 1px var(--color-light-gray);
			width		: 100%;
			height		: 80vh;
			box-sizing	: border-box;
		}
		.seller_mypage_section1c_div2a{
			padding			: 0.5em;
			width			: 90%;
			margin			: auto;
			border-bottom	: dotted 1px var(--color-light-gray);
		}
		.seller_mypage_section1c_div3a{
			width			: 100%;
			padding			: 0.5em;
			justify-content	: space-between;
		}
		.seller_mypage_section1c_div4a{
			padding		: 0.5em;
			min-width	: 30%;
			max-width	: 50%;
		}
		.seller_mypage_section1c_div4b{
			padding		: 0.5em;
			min-width	: 30%;
			max-width	: 50%;
		}
		.seller_mypage_section1c_div5a{
			padding-left	: 1em;
		}
		.seller_mypage_section1c_div5b{
			padding-left	: 1em;
		}
/* ======== 会員情報 ======== */
		.seller_mypage_section1d_div1b{
			border		: solid 1px var(--color-light-gray);
			padding		: 0.5em;
			box-sizing	: border-box;
			width		: 100%;
			margin		: auto;
		}
		.seller_mypage_section1d_div2b{
			padding			: 0.5em;
			border-bottom	: dotted 1px var(--color-light-gray); 
		}
		#seller_low_postalcode,
		#seller_high_postalcode{
			width	: 5em;
		}
		#d_btn{
			text-align	: right;
		}
		#d_btn input[type="button"]{
			font-size	: var(--font-small);
			height		: 2.5em;
		}
		.seller_mypage_section1d_div3c{
    		font-weight: bold;
		}
		.seller_mypage_section1d_div3d{
			font-size	: calc(var(--font-small) - 2px);
			font-weight	: bold;
		}
/* ======== 購入履歴 ======== */
		.seller_mypage_section1e>div {
  			overflow: auto;
  			scroll-snap-type: y mandatory;
			width: 80%;
			margin :auto;
		}
		.seller_mypage_div1a{
			border		: solid 1px var(--color-light-gray);
			width		: 100%;
			height		: 80%;
			margin		: auto;
			padding		: 0.5em;
			box-sizing	: border-box;
		}
		.seller_mypage_div2a{
			border-bottom	: dotted 1px var(--color-light-gray);
		}
		.seller_mypage_div2a input{
			font-size	: 12px;
			height		: 2.5em;
		}
		.seller_mypage_div3b{
			padding-bottom	: 0.5em;
			justify-content	: space-between;
		}
		.seller_mypage_div4a{
			width  			: var(--responce-small-width);
			height			: var(--responce-small-width);
			max-width		: 100%;
			margin			: var(--responce-margin);
			position		: relative;
			min-width		: 30%;	
		}
		.seller_mypage_div4a img{
			position: absolute;
			top				: 0;
			bottom			: 0;
			left			: 0;
			right			: 0;
			height			: auto;
			width			: auto;
			max-width		: 100%;
			max-height		: 100%;
			margin			: auto;
			border-radius	: 5px;			
		}
		.seller_mypage_div4b{
			padding			: 1em;
		}
		.seller_mypage_div4c{
			padding			: 1em;
		}
		.seller_mypage_div1a h4{
			margin	: 0.5em 0;
		}
	</style>
	<title>マイページ</title>
</head>
<body>
	<?php  include '../inc/var_dump.php'?>
	<header>
		<?=$header_common_tag?>
	</header>
	<main>
	<!------ タブメニュー ------>
		<nav class="seller_mypage_nav1">
			<input type="radio" class="seller_mypage_radio1" id="seller_mypage_radio1a" name="seller_mypage_radio1" onclick="tab_change('seller','a')">
			<label class="seller_mypage_label1" for="seller_mypage_radio1a">在庫リスト</label>
			<input type="radio" class="seller_mypage_radio1" id="seller_mypage_radio1b" name="seller_mypage_radio1" onclick="tab_change('seller','b')">
			<label class="seller_mypage_label1" for="seller_mypage_radio1b">出品登録</label>
			<input type="radio" class="seller_mypage_radio1" id="seller_mypage_radio1c" name="seller_mypage_radio1" onclick="tab_change('seller','c')">
			<label class="seller_mypage_label1" for="seller_mypage_radio1c">受注リスト</label>
			<input type="radio" class="seller_mypage_radio1" id="seller_mypage_radio1d" name="seller_mypage_radio1" onclick="tab_change('seller','d')">
			<label class="seller_mypage_label1" for="seller_mypage_radio1d">会員情報</label>
			<input type="radio" class="seller_mypage_radio1" id="seller_mypage_radio1e" name="seller_mypage_radio1" onclick="tab_change('seller','e')">
			<label class="seller_mypage_label1" for="seller_mypage_radio1e">購入履歴</label>
		</nav>
	<!------ 在庫リスト ------>
		<section id="<?=$section_array["item_list"]?>" class="seller_mypage_section1">
			<header>
				<h2>在庫リスト</h2>
			</header>
			<span class="err">
				<?=$err_msg?><br>
			</span>
			<span class="err"><?php if(!empty($section1a_msg)){echo $section1a_msg;}?></span>
			<!-- title、h2以外、実際に記載するのはこっから下 -->
			<div class="overflow">
				<?php
					if(empty($sql_output_seller_mypage_item_array)){
						echo"出品された商品はありません。";
					}else{ 
						foreach($sql_output_seller_mypage_item_array as $val){
				?>
					<div class="item_list">
					<form action="<?=$_SERVER["SCRIPT_NAME"]?>" method="post">
						<h4>商品ID：<?=$val["item_id"]?>　　<input type="submit" name="submit" value="削除"></h4>
							<input type="hidden" name="seller_mypage_tab" value="a">
							<input type="hidden" name="item_id" value="<?=$val["item_id"]?>">
							<input type="hidden" name="item_quantity" value="<?=$val["item_quantity"]?>">
							<div class="res_row">
								<div class="img_div">
									<img src="<?=$val["item_image_path"]?>">
								</div>
								<div>
									<div><?=$val["item_name"]?></div>
									<div>分類：<span><?=$val["genre_name"]?></span></div>
									<div>状態：<span><?=$val["condition_rank"]?></span></div>
									<div>￥ <span><?=number_format($val["item_price"])?></span></div>
									<div>在庫数：<span><?=$val["item_quantity"]?></span></div>
									<div>追加/削減
										<input type="number" name="item_changes" size="1" min="<?="-".$val["item_quantity"]?>" max="999" placeholder="0">
										<input type="submit" name="submit" value="反映">
									</div>
								</div>
							</div>
					</form>
					</div>
				<?php }}?>
			</div>
			<!-- 実際に記載するのはこっから上 -->
		</section>
	<!------ 出品登録 ------>
		<section id="<?=$section_array["item_register"]?>" class="seller_mypage_section1">
			<header>
				<h2>出品登録</h2>
			</header>
			<span class="err">
				<?=$err_msg?><br>
			</span>
			<span class="err"><?php if(!empty($section1b_msg)){echo $section1b_msg;}?></span>
			<!-- title、h2以外、実際に記載するのはこっから下 -->
			<div class="seller_mypage_section1b_div1">
				<span>(※)画像ファイルを選択しなくても商品登録はできます</span><br>
				<span>(※)画像ファイルがない場合はデフォルトイメージで登録されます</span>
				<form name="form_2" action="<?=$_SERVER['SCRIPT_NAME']?>" enctype="multipart/form-data" method="post" onSubmit="check()">
					<?php echo create_input("hidden","","seller_mypage_tab","","","b","","","");	// タブ識別用hidden?>
					<!-- 項目見出し要素 -->
					<div id="seller_mypage_section1b_div2a">
						<div class="column1">
							<div class="seller_mypage_section1b_div4">分類</div>
							<div class="seller_mypage_section1b_div4">商品名</div>
							<div class="seller_mypage_section1b_div4">状態</div>
							<div class="seller_mypage_section1b_div4">価格</div>
							<div class="seller_mypage_section1b_textarea1 seller_mypage_section1b_div4">商品説明</div>
							<div class="seller_mypage_section1b_div4">個数</div>
							<div class="seller_mypage_section1b_div4">画像</div>
						</div>
						<!-- 各入力項目 -->
						<div class="column2">
							<!-- 分類セレクトメニュー -->
							<div class="seller_mypage_section1b_div4">
								<select name="item_genre_id" required>
										<?php 
											foreach($genre_array as $key => $val){
												echo "<option value=\"".$key."\">".$val."</option>";
											}
										?>
								</select>
							</div>
							<!-- 商品名入力フォーム -->
							<div class="seller_mypage_section1b_div4"><?=create_input("text","","item_name","","","","required","required","商品名を入力して下さい")?></div>
							<!-- 状態セレクトメニュー -->
							<div class="seller_mypage_section1b_div4">
								<select name="item_condition_id" required>
										<?php 
											$i = 1;
											foreach($condition_array as $key => $val){
												echo "<option value=\"".$key."\">".$val."</option>";
												$i++;
											}
										?>
								</select>
							</div>
							<!-- 商品単価入力フォーム -->
							<div class="seller_mypage_section1b_div4"><input type="number" name="item_price" min="1" max="999999" required value="1000"></div>
							<!-- 商品説明入力フォーム -->
							<div class="seller_mypage_section1b_textarea1 seller_mypage_section1b_div4"><textarea name="item_description" required rows="15" cols="40" placeholder="商品の説明を入力してください"></textarea></div>
							<!-- 商品個数入力フォーム -->
							<div class="seller_mypage_section1b_div4"><input type="number" name="item_quantity" required min="1" max="999" value="1"></div>
							<!-- ファイルアップロードフォーム -->
							<input type="hidden" name="MAX_FILE_SIZE" value="<?=$max_file_size?>">
							<div class="seller_mypage_section1b_div4"><input type="file" name="upfile"></div>
							</div>
					</div>
					<!-- 送信ボタン -->
					<div id="seller_mypage_section1b_div2b">
						<input type="button" name="btn1" value="確認" style="display:none">
						<input type="submit" name="submit" value="登録">
						<input type="button" name="btn2" value="再編集"  style="display:none">
					</div>
				</form>
			</div>
			<!-- 実際に記載するのはこっから上 -->
		</section>
	<!------ 受注リスト ------>
		<section id="<?=$section_array["order_list"]?>" class="seller_mypage_section1">
			<header>
				<h2>受注リスト</h2>
			</header>
			<span class="err"><?=$err_msg?></span>
			<span class="err"><?php if(!empty($section1c_msg)){echo $section1c_msg;}?></span>
			<br>
			<!-- title、h2以外、実際に記載するのはこっから下 -->
			<div class="seller_mypage_section1c_div1a overflow">
			<!-- ここから一件分 -->
				<?php
					if(empty($sql_output_seller_mypage_item_array)){
						echo"注文された商品はありません。";
					}else{ 
						foreach($sql_output_seller_mypage_order_array as $val){
							// 購入者のタイプ判断
							if(empty($val["buyer_id"])){
								$order_user = 1;	// seller
							}else{
								$order_user = 0;	// buyer
							}
				?>
					<div class="seller_mypage_section1c_div2a">
						<h4>注文ID：<?=$val["order_id"]?></h4>
						<form action="<?=$_SERVER["SCRIPT_NAME"]?>" method="post">
							<input type="hidden" name="seller_mypage_tab" value="c">
							<input type="hidden" name="order_id" value="<?=$val["order_id"]?>">
							<div class="seller_mypage_section1c_div3a res_row">
								<div class="seller_mypage_section1c_div4a">
									<div>【商品ID】
										<div class="seller_mypage_section1c_div5a"><span><?=$val["item_id"]?></span></div>
									</div>
									<div>【商品名】
										<div class="seller_mypage_section1c_div5a"><span><?=$val["item_name"]?></span></div>
									</div>
									<div>【購入個数】
										<div class="seller_mypage_section1c_div5a"><span><?=$val["order_quantity"]?></span></div>
									</div>
									<div>【合計金額】
										<div class="seller_mypage_section1c_div5a"><span>￥<?=($val["item_price"] * $val["order_quantity"])?></span></div>
									</div>
									<div>【出荷状況】
										<div class="seller_mypage_section1c_div5a"><span><?php if($val["order_shipped"]){echo "出荷済み";}else{echo "未出荷";}?></span></div>
									</div>
								</div>
								<div class="seller_mypage_section1c_div4b">
									<div>【購入者ID】
										<div class="seller_mypage_section1c_div5b"><span><?php if($order_user){echo $val["seller_id"];}else{echo $val["buyer_id"];}?></span></div>
									</div>
									<div>【購入者名】
										<div class="seller_mypage_section1c_div5b"><span><?php if($order_user){echo $val["seller_name"];}else{echo $val["buyer_name"];}?></span></div>
									</div>
									<div>【郵便番号】
										<div class="seller_mypage_section1c_div5b">
											<span><?=str_pad($val["order_high_postalcode"],3,0,STR_PAD_LEFT)?></span>-
											<span><?=str_pad($val["order_low_postalcode"],4,0,STR_PAD_LEFT)?></span>
										</div>
									</div>
									<div>【住所】
										<div class="seller_mypage_section1c_div5b">
											<span><?=$val["order_address_1"]?></span>
											<span><?=$val["order_address_2"]?></span><br>
											<span><?=$val["order_address_3"]?></span>
										</div>
									</div>
									<div>
										<div class="seller_mypage_section1c_div5b">
											<?php if($val["order_shipped"] === 0){ ?>
												<input type="submit" name="submit" value="出荷">
											<?php } ?>
										</div>
									</div>
								</div>
							</div>
						</form>
					</div>
				<?php }}?>
			<!-- ここまで一件分 -->
			</div>				
			<!-- 実際に記載するのはこっから上 -->
		</section>
	<!------ 会員情報 ------>
		<section id="<?=$section_array["my_information"]?>" class="seller_mypage_section1">
			<header>
				<h2>会員情報</h2>
			</header>
			<span class="err"><?=$err_msg?></span>
			<br>
			<span class="err"><?php if(!empty($section1d_msg)){echo $section1d_msg;}?></span>
			<!-- title、h2以外、実際に記載するのはこっから下 -->
			<div class="seller_mypage_section1d_div1b">
			<form action='seller_mypage.php' method='POST'>
				<input type="hidden" name="seller_mypage_tab" value="b">
				<div class="seller_mypage_section1d_div2b">
					<div class="seller_mypage_section1d_div3c">
						ID:<?=$sql_output_seller_mypage_user_array[0]["seller_id"];?>
					</div>
				</div>
				<div class="seller_mypage_section1d_div2b">
					郵便番号
					<div id ="d_seller_postalcode" class="seller_mypage_section1d_div3c">
						<input type="text" required value="<?=str_pad($sql_output_seller_mypage_user_array[0]['seller_high_postalcode'],3,0,STR_PAD_LEFT);?>" id ="seller_high_postalcode" name="seller_high_postalcode">
						－
						<input type="text" required value="<?=str_pad($sql_output_seller_mypage_user_array[0]['seller_low_postalcode'],4,0,STR_PAD_LEFT);?>" id ="seller_low_postalcode" name="seller_low_postalcode">
					</div>
					<div id="d_seller_postalcode_r" class="seller_mypage_section1d_div3d"></div>
				</div>
				<div class="seller_mypage_section1d_div2b">
					都道府県名
					<div id ="d_seller_address_1" class="seller_mypage_section1d_div3c">
						<input type="text" required value="<?=$sql_output_seller_mypage_user_array[0]['seller_address_1']; ?>" id ="seller_address_1" name="seller_address_1">
					</div>
					<div id ="d_seller_address_1_r" class="seller_mypage_section1d_div3d"></div>		
				</div>
				<div class="seller_mypage_section1d_div2b">
					市町村名
					<div id ="d_seller_address_2" class="seller_mypage_section1d_div3c">
						<input type="text" required value="<?=$sql_output_seller_mypage_user_array[0]['seller_address_2']; ?>" id ="seller_address_2" name="seller_address_2">
					</div>
					<div id ="d_seller_address_2_r" class="seller_mypage_section1d_div3d"></div>		
				</div>
				<div class="seller_mypage_section1d_div2b">
					番地
					<div id ="d_seller_address_3" class="seller_mypage_section1d_div3c">
						<input type="text" required value="<?=$sql_output_seller_mypage_user_array[0]['seller_address_3']; ?>" id ="seller_address_3" name="seller_address_3">
					</div>
					<div id ="d_seller_address_3_r" class="seller_mypage_section1d_div3d"></div>		
				</div>
				<div class="seller_mypage_section1d_div2b">
					氏名
					<div id ="d_seller_name" class="seller_mypage_section1d_div3c">
						<input type="text" required value="<?=$sql_output_seller_mypage_user_array[0]['seller_name']; ?>" id ="seller_name" name="seller_name">
					</div>
					<div id ="d_seller_name_r" class="seller_mypage_section1d_div3d"></div>
				</div>
				<div class="seller_mypage_section1d_div2b">
					企業・事業所名
					<div id ="d_seller_office_name" class="seller_mypage_section1d_div3c">
						<input type="text" value="<?=$sql_output_seller_mypage_user_array[0]['seller_office_name']; ?>" id ="seller_office_name" name="seller_office_name">
					</div>
					<div id ="d_seller_office_name_r" class="seller_mypage_section1d_div3d"></div>
				</div>
				<div id ="d_btn" class="seller_mypage_section1d_div2b">
					<input type="button" value="確認"   id="btn1" onclick="confirm_chenge('seller')" style="display: block">
					<input type="button" value="再編集" id="btn3" onclick="rewrite_change('seller')" style="display: none">
					<input type="submit" value="確定"   id="btn2" name="submit" onsubmit="check()" style="display: none">
				</div>
			</form>
			</div>
			<!-- 実際に記載するのはこっから上 -->
		</section>
	<!------ 購入履歴 ------>
		<section id="<?=$section_array["order_history"]?>" class="seller_mypage_section1">
			<header>
				<h2>購入履歴</h2>
			</header>
			<span class="err"><?=$err_msg?></span>
			<br>
			<span class="err"><?php if(!empty($section1e_msg)){echo $section1e_msg;}?></span>
			<!-- title、h2以外、実際に記載するのはこっから下 -->
			<div class="seller_mypage_div1a overflow">
				<span><?php foreach($msg as $val){echo $val;}?>
				<?php foreach($sql_output_seller_mypage_history_array as $key => $val): ?>
			<!-- ここから注文一件分 -->
				<form action="<?=$_SERVER["SCRIPT_NAME"]?>" method="post">
					<input type="hidden" name="seller_mypage_tab" value="a">
					<div class="seller_mypage_div2a">
						<div class="seller_mypage_div3a">
							<h4>注文番号：<?=$val["order_id"]?></h4>
							<input type="hidden" name="order_id" value="<?=$val["order_id"]?>">
							<span>注文日時：<?=$val["order_time"]?></span><br>
							<span>出荷状況：<?php if($val["order_shipped"]){echo "出荷済み";}else{echo "未出荷";} ?></span>
						</div>
						<div class="seller_mypage_div3b res_row">
							<div class="seller_mypage_div4a">
								<img src="<?=$val["item_image_path"]?>">
							</div>
							<div class="seller_mypage_div4b">
								<div>
									<span>商品ID：</span>
									<span><?=$val["order_item_id"]?></span>
								</div>
								<div class="seller_mypage_div5a">
									<span>商品名</span>
									<span><?=$val["item_name"]?></span>
								</div>
								<div>
									<span>購入個数</span>
									<span><?=$val["order_quantity"]?></span>
								</div>
								<div>
									<span>￥</span>
									<span><?=($val["item_price"] * $val["order_quantity"])?></span>
								</div>
							</div>
							<div class="seller_mypage_div4c">
								<div>
									<span>出品者ID</span>
									<span><?=$val["item_seller_id"]?></span>
								</div>
								<div>
									<?php if($val["order_evaluated"] === 0){ ?>
									<input type="submit" value="出品者評価" name="submit">
									<?php }else{ ?>
									<span>評価済み</span>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>
				</form>
			<!-- ここまで注文一件分 -->
				<?php endforeach ?>
			</div>
			<!-- 実際に記載するのはこっから上 -->
		</section>
	</main>
	<footer>
		<?=$footer_common_tag?>
	</footer>
<script src="../js/mypage_functions.js"></script>
</body>
</html>
