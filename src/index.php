<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成者：秋野浩朗（web1902)
概要　：トップページ
更新日：2020/2/7

----------------------------------------------------------------------------------------- */



/* ----- 共通処理 ----------------------------------------------------------------------- */

include '../inc/config.php';
include '../inc/db_access.php';
include '../inc/functions.php';
include '../inc/variables.php';

// ここで使うinclude したファイルの変数。（必要に応じて var_dump で確認）
$pflag;					// 中身：false
$head_common_tag;		// 中身：head タグ内で規定するメタタグとか
$header_index_tag;		// 中身：header タグ内で規定するタイトルタグとか
$footer_common_tag;		// 中身：footer タグ内で規定するタイトルタグとか

// ここで使う変数の宣言と初期値設定。
$_SESSION["pre_page"]			= basename(__FILE__, ".php");
$search_item_word				= "";
$search_sql_item_word			= "";
$genre_array					= array();
$genre_chose_val_array			= array();
$display_number_array			= array("10件","20件","50件","全件");
$display_number_chose_val_array	= array();
$display_number					= "";
$display_number_offset_val		= "";
$display_order_array			= array("新着順","価格が高い順","価格が低い順","評価が高い順");
$display_order_chose_val_array	= array();
$display_order					= "";
$display_order_type				= "";
$item_number					= "";
$login							= false;
$page_display_number			= 3;
$page_low_dif_number  			= "";
$page_high_dif_number			= "";
$page_sum_no 					= 1;
$page_now_no 					= 1;
$page_display_number_min_chnage	= 0;
$page_display_number_disable	= false;

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



if($_SERVER["REQUEST_METHOD"] == "POST"){
	$pflag = true;
	// 押下されたボタン毎の処理
	switch($_POST["btn"]){
		case "ログイン":
			$_SESSION["pre_page"] = "index";
			header("Location:login.php");
			exit;
			break;
		case "マイページ":
			// 会員の種類毎にページ遷移先を変更
			switch($_SESSION["user"]["type"]){
				case 1:
					header("Location:seller_mypage.php");
					exit;
					break;
				case 2:
					header("Location:buyer_mypage.php");
					exit;
					break;
			}
		case "新規会員登録":
			$_SESSION["pre_page"] = "index";
			header("Location:mail.php");
			exit;
			break;
		case "検索":
			// 現在のページ数を取得（SQLで表示するデータの件数を変える＆ページネーション機能の為）
			if(isset($_POST["page"])){
				$page_now_no = $_POST["page"];
			}
			// ページネーション機能の為の処理（SQLとは関係ない）
			if(isset($_POST["page_display_number"])){
				$page_display_number = $_POST["page_display_number"];	
			}
			// 各種値を取得（SQL文の条件に使う）
			$search_item_word 				= htmlspecialchars($_POST["search_item_word"],ENT_QUOTES);
			$genre_chose_val_array			= $_POST["genre_name"];
			$display_number_chose_val_array	= $_POST["display_number"];
			$display_number_offset_val 		= $page_now_no - 1;
			$display_order_chose_val_array	= $_POST["display_order"];
			// 取得するデータの数の変更
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
			// 取得するデータの順番の変更
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
			// フリーワード検索があるかどうかを判別
			if($search_item_word == ""){
				// 検索条件の分岐（where 句の処理）
				if($genre_chose_val_array[0] == "全て"){
					// 【パターン１】キーワード：なし、ジャンル：全て（指定なし）、他：任意の選択項目
					// 全件取得の為、処理なし。
				}else{
					// 【パターン２】キーワード：なし、ジャンル：選択項目、他：任意の選択項目
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
				// 後学のために
				// 明示的バインド処理（キャスト）をして曖昧検索（パラメータマーカー使う）するときはこうする。
				// 参考URL：https://www.php.net/manual/ja/pdostatement.execute.php
				// 参考URL：https://teratail.com/questions/96423
				$search_sql_item_word .= "%";
				$search_sql_item_word .= $search_item_word;
				$search_sql_item_word .= "%";
				// 検索条件の分岐（where 句の処理）
				if($genre_chose_val_array[0] == "全て"){
					// 【パターン３】キーワード：あり（プレースホルダー処理）、ジャンル：全て（指定なし）、他：任意の選択項目
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
					// 【パターン４】キーワード：あり（プレースホルダー処理）、ジャンル：選択項目、他：任意の選択項目
					$sql_array["index3"] .= " AND (item_name like ? or ( case when seller_office_name = '' or seller_office_name is NULL then seller_name like ? else seller_office_name like ? end)) ";
					$sql_array["index3"] .= " AND genre_name = '{$genre_chose_val_array[0]}' ";
					// 上記の条件の時は商品表示件数も変更する。
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
			break;
		case "商品詳細":
			$_SESSION["item"]["item_id"] = $_POST["item_id"];;
			header("Location:item.php");
			exit;
			break;
		case "出品者詳細":
			$_SESSION["seller"]["seller_id"] = $_POST["item_seller_id"];;
			header("Location:seller.php");
			exit;
			break;
	}
}else{
	// GET時にSESSION［user］がない、もしくは各種ページからログアウトして画面遷移してくればゲスト扱い。
	if(!isset($_SESSION["user"]) || (isset($_GET['logout']) && $_GET['logout']) ) {
		$_SESSION["user"]["id"]		= "guest";
		$_SESSION["user"]["name"]	= "ゲスト";
		$_SESSION["user"]["type"]	= "0";
	}
	// GET時は全ての商品の件数を取得。
	$sql_output_item_number_array = sql($sql_array["index2"],true);
	// GET時は商品情報を全ての種類から新着順に10件取得。
	$sql_array["index3"] .= " order by item_time desc limit 10";
	$sql_output_item_array = sql($sql_array["index3"],true);
	// GET時は表示関連の初期値を以下に設定。
	$genre_chose_val_array[0]			= "全て";
	$display_number_chose_val_array[0]	= "10件";
	$display_number						= "10";
	$display_order_chose_val_array[0]	= "新着順";
	$display_order						= "item_time";
	$display_order_type 				= "desc";
}


// GETかPOSTにかかわらず必要な処理をここ以降で書く

// ジャンルの種類の情報を取得 & データ分割。
$sql_output_genre_array = sql($sql_array["index1"],false);
foreach($sql_output_genre_array as $key => $val){
	foreach($val as $key2 => $val2){
		$genre_array[] = $val2;
	}
}

// 商品件数のデータを分割。データ自体は既に取得ずみ。
foreach($sql_output_item_number_array as $key => $val){
	foreach($val as $key2 => $val2){
		$item_number = $val2;
	}
}

// ページネーション機能でのページ設定
if($display_number != "all"){
	// 表示件数/該当商品数を切り上げ（2.8とかなら3ページになる）
	$page_sum_no = ceil($item_number / $display_number);
}else{
	// all は全件表示なのでページ数は１ページになる。
	$page_sum_no = 1;
	$page_now_no = 1;
}

// 会員専用ページ（inndex_add.php）を表示するかどうかの判定
if($_SESSION["user"]["id"] != "guest"){
	$login = true;
}

?>

<!DOCTYPE html>
<html lang="ja">
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# website: http://ogp.me/ns/website#">
	<?= $head_common_tag ?>
	<meta property="og:type" content="website">
	<script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<!-- <script src="../js/ajax.js"></script> -->
	<script>
		window.onload = function(){
			// 表示件数タブを変えた時は強制的にPOST送信してページを再構成（再構築するのでPOST送信にした）
			// 頑張ればajaxでもいけるけど、apendchildとか面倒いし、ajaxでPHP処理するのも面倒いので苦肉の策としてこうした
			const display_number_tag = document.getElementById("display_number");
			display_number_tag.addEventListener("change",function(){
				event.srcElement.form.submit();
			});

			// 並び順を変えた時はajax通信でデータ取得してデータを入れ替え
			const display_order_tag = document.getElementById("display_order");
			display_order_tag.addEventListener("change",function(){
				ajax();
			});

			// ページネーションの為の処理
			const page_display_number_tag = document.getElementById("page_display_number");
			page_display_number_tag.setAttribute("form","form1");
			// 1 指定するとページネーションの意味なくなるけど最低表示件数を１に指定。
			page_display_number_tag.setAttribute("min", "1");
			page_display_number_tag.value = "<?= $page_display_number ?>";
			
			// ページネーションの為の処理２
			const page_tags_array = document.getElementsByName("page");
			for(var i = 0 ; i < page_tags_array.length ; i++ ){
				// 現在表示中のページタブの色を変えてボタン機能を無効にする。
				if(page_tags_array[i].value == "<?= $page_now_no ?>" ){
					page_tags_array[i].style.backgroundColor = "midnightblue";
					page_tags_array[i].style.color = "white";
					page_tags_array[i].disabled = "disabled";
				}
			}
		}
		// ajax通信処理関数
		const ajax = function(){
			// 1. ajax の開始
			$.ajax({	
				url	 : "./index_ajax.php",					// 通信先のURL
				type : "POST",								// 使用するHTTPメソッド
				data :{										// 送信するデータ（キー：値の形式で記載。）
					'function_no'			: "index1", 
					'search_item_word'		: $('#search_item_word').val(),
					'genre_name'			: $('#genre_name').val(),
					'display_number'		: $('#display_number').val(),
					'display_order'			: $('#display_order').val(),
					'page_now_no'			: $('#page_now_no').text(),
					'page_display_number'	: $('#page_display_number').val(),
				},
				dataType : "json"							// 応答のデータの種類 (xml/html/script/json【推奨】/jsonp/text)
				// timespan : 10000							// 通信のタイムアウトの設定(ミリ秒)　今回はなし。
			})
			// 2. done は通信に成功した時に実行される処理
			.done(function(data,textStatus,jqXHR) {
				$(".err").text("通信に成功しました");			// 通信成功したかどうかの確認用
				// console.log(data);						// 取得したデータの確認用
				Object.keys(data).forEach(function(key){
					// 取得したデータをキー毎に分割
					switch(key){
						// 検索条件に該当する商品件数に関する処理
						case "item_number":
							$("#item_number").text(data[key]);
							break;
						// 検索条件に該当する商品情報に関する処理
						case "item_array":
							for(var i = 0 ; i < data[key].length ; i++){
								Object.keys(data[key][i]).forEach(function(key2){
									var id = key2 + "_" + i;
									switch(key2){
										// seller_office_name があれば該当箇所に seller_office_name を表示
										// ややこしいけど、表示する箇所の ID は seller_name にしてるから
										case "seller_office_name":
											if(data[key][i]["seller_office_name"]){
												$("#seller_name_" + i).text(data[key][i][key2]);
											}
											break;
										// item_image_path の場合は属性値の src と alt を変更する
										case "item_image_path":
											$("#" + id).attr("src",data[key][i][key2]);
											$("#" + id).attr("alt",data[key][i]["item_name"] + "の画像");
											break;
										// evaluation の場合は以下の通り
										case "evaluation":
											if(data[key][i][key2]){
												$("#" + id).text((data[key][i][key2] * 100).toFixed(2) + "点");
											}else{
												$("#" + id).text("未評価");
											}
											break;
										// item_id の場合は input なので val() で代入
										case "item_id":
											$("#" + id).val(data[key][i][key2]);
											break;
										// item_seller_id の場合も input なので val() で代入
										case "item_seller_id":
											$("#" + id).val(data[key][i][key2]);
											break;
										// 他のカラムはそのまま代入
										default:
											$("#" + id).text(data[key][i][key2]);
											break;
									}
								});
							}
							break;
					}
				})
			})
			// 3. fail は通信に失敗した時に実行される処理
			.fail(function(jqXHR, textStatus, errorThrown ) {
				$(".err").text("通信に失敗しました");
			})
			// 4. always は成功/失敗に関わらず実行される処理
			.always(function(){
				// alert("ajax 通信終了");
			});
		}

		// 商品名がクリックされた時の処理（POST送信）
		function to_item(no){
			const hidden_tag = document.createElement('input');
			hidden_tag.type	 = "hidden";
			hidden_tag.name	 = "btn";
			hidden_tag.value = "商品詳細";
			event.target.appendChild(hidden_tag);
			const form = document.getElementById("item_name_form_" + no);
			form.submit();
		}

		// 出品者名がクリックされた時の処理（POST送信）
		function to_seller(no){
			const hidden_tag = document.createElement('input');
			hidden_tag.type	 = "hidden";
			hidden_tag.name	 = "btn";
			hidden_tag.value = "出品者詳細";
			event.target.appendChild(hidden_tag);
			const form = document.getElementById("seller_name_form_" + no);
			form.submit();
		}
	</script>
	<style>
		/* 特にこだわりないので適当に変更してください */
		body > header{
			margin    : auto;
			max-width : 80%;
		}
		body > header > a{
			float : right;
		}
		section{
			margin     : auto;
			text-align : center;
			/* max-width  : 2000px; */
		}
		section>section{
			margin-top	: 3em;
		}
		.form1_div1a{
			text-align		: var(--basic-align);
			justify-content	: space-between;
			width			: 80%;
			margin			: auto;
		}
		.form1_div2a{
			text-align	: right;
			position	: relative;
			right		: 0;
		}
		.div1_flex_parent{
			display       : flex;
			align-items   : flex-start;
			align-content : flex-start;
			width		  : 100%;
			margin		  : auto;
		}
		.section1_flex_children{
			border		  : 1px solid var(--color-light-gray);
			border-radius : 10px;
			padding       : 4px;
			margin-bottom : 1em;
			height	  	  : var(--responce-em-height);
			max-width	: 15em;
			min-width	: 22%;
		}
		.section1_flex_children h4{
			height	: 3.5em;
		}
		.img_div{
			width  			: var(--responce-small-width);
			height			: var(--responce-small-width);
			max-width		: 100%;
			margin			: auto;
			position		: relative;
		}
		.img_div img{
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
		.div2_flex_parent{
			display         : flex;
			justify-content : center;
			align-items     : center;
		}
		.section1_flex_children p{
			margin-block-start	: 0.5em;
			margin-block-end	: 0.5em;
		}
		.section1_fles_children h4{
			margin-block-start	: 1em;
			margin-block-end	: 0.5em;
		}
		input[type="text"],
		select,
		option{
			margin-left	: 0.5em;
		}
		.form1_div2a>div{
			padding		: 0.5em;
		}
	</style>
	<title>中古家電.com</title>
</head>
<body>
	<?php include '../inc/var_dump.php' ?>
	<header>
		<?= $header_index_tag ?>
		<?php
			// ヘッダー部分に各種ボタンを記載
			if($_SESSION["user"]["id"] == "guest"){
				// ゲストの場合
				echo "<form action=\"{$_SERVER["SCRIPT_NAME"]}\" method=\"POST\">";
				echo create_input("submit","","btn","","20","ログイン","","","");
				echo "</form>";
				echo "<form action=\"{$_SERVER["SCRIPT_NAME"]}\" method=\"POST\">";
				echo create_input("submit","","btn","","20","新規会員登録","","","");
				echo "</form>";
			}else{
				// 会員の場合
				echo "<form action=\"{$_SERVER["SCRIPT_NAME"]}\" method=\"POST\">";
				echo create_input("submit","","btn","","20","マイページ","","","");
				echo "</form>";
			}
		?>
	</header>
	<main>
		<section>
			<header>
				<h2>トップページ</h2>
			</header>
			<span class="err"><?= $err_msg ?></span>
			<br>
			<form id="form1" action="<?= $_SERVER["SCRIPT_NAME"]?>" method="POST">
				<div class="form1_div1a res_row">
					<div>
						検索ワード<?= create_input("text","search_item_word","search_item_word","","40",$search_item_word,"","","商品名もしくは出品者名を入力") ?>
						<br><br>
						ジャンル<?= create_box("selectbox","genre_name","genre_name","",$genre_array,$genre_chose_val_array,true,true) ?>
					</div>
					<div class="form1_div2a">
						<div>
							<?= create_input("submit","","btn","","20","検索","","","") ?>
							<?= create_input("hidden","","btn","","20","検索","","","") ?>
						</div>
						表示件数<?= create_box("selectbox","display_number","display_number","",$display_number_array,$display_number_chose_val_array,true,true) ?>
						<br>
						並び順<?= create_box("selectbox","display_order","display_order","",$display_order_array,$display_order_chose_val_array,true,true) ?>
					</div>
				</div>
			</form>
			<section>
				<header>
					<h3>商品一覧</h3>
					ジャンル：<?= $genre_chose_val_array[0] ?>　商品件数：<span id="item_number"><?= $item_number ?></span> 件
				</header>
				<div class="div1_flex_parent mult_rows">
					<?php
						// 取得した商品情報をsectionタグ（フレックスボックス子要素）に格納
						if(empty($sql_output_item_array)){
							echo "<section><h4>該当する商品はありません。<h4></section>";
						}else{
							for($i=0;$i<count($sql_output_item_array);$i++){
								echo "<section id=\"item_section_$i\" name=\"item_section\" class=\"section1_flex_children\">";
								// 画像パスの設定
								echo "<div class=\"img_div\"><img id=\"item_image_path_$i\" src=\"{$sql_output_item_array[$i]["item_image_path"]}\" alt=\"{$sql_output_item_array[$i]["item_name"]}の画像\"
										width=\"100%\" height=\"100%\" ></div>";
								// 商品ページに遷移する為のフォーム
								echo "<form id=\"item_name_form_$i\" action=\"{$_SERVER["SCRIPT_NAME"]}\" method=\"POST\">";
								// 商品名の設定
								echo "<h4 id=\"item_name_$i\" onclick=\"to_item({$i})\" class=\"like_a\">";
								echo $sql_output_item_array[$i]["item_name"],"</h4>"; 
								// 商品ID（hidden）の設定
								echo create_input("hidden","item_id_$i","item_id","","20",$sql_output_item_array[$i]["item_id"],"","","");
								echo "</form>";
								// 出品者詳細ページに遷移する為のフォーム
								echo "<form id=\"seller_name_form_$i\" action=\"{$_SERVER["SCRIPT_NAME"]}\" method=\"POST\">";
								// 出品者名の設定
								echo "<p id=\"seller_name_$i\" onclick=\"to_seller({$i})\" class=\"like_a\">";
								if($sql_output_item_array[$i]["seller_office_name"] != ""){
									echo $sql_output_item_array[$i]["seller_office_name"],"</p>";
								}else{
									echo $sql_output_item_array[$i]["seller_name"],"</p>";
								}
								// 出品者ID（hidden）の設定
								echo create_input("hidden","item_seller_id_$i","item_seller_id","","20",$sql_output_item_array[$i]["item_seller_id"],"","","");
								echo "</form>";
								// 出品者への評価の設定
								if(empty($sql_output_item_array[$i]["evaluation"])){
									echo "<p>出品者への評価：<span id=\"evaluation_$i\">未評価</span></p>";
								}else{
									echo "<p>出品者への評価：<p id=\"evaluation_$i\">",round(($sql_output_item_array[$i]["evaluation"] * 100),2) ,"</span>点</p>"; 
								}
								// 商品の価格の設定
								echo "<p>価格：￥<span id=\"item_price_$i\">",$sql_output_item_array[$i]["item_price"],"</span></p>";
								// 商品の状態の設定
								echo "<p>状態：<span id=\"item_condition_$i\">",$sql_output_item_array[$i]["condition_rank"],"</span></p>";
								// 商品の在庫数の設定
								echo "<p>残り：<span id=\"item_quantity_$i\">",$sql_output_item_array[$i]["item_quantity"],"</span>個</p>";
								echo "</section>";
							}
						}
					?>
				</div>
			</section>
			<footer>
				<?php
				if(!empty($sql_output_item_array)){
					echo "現在のページ <span id=\"page_now_no\">{$page_now_no}</span> / <span>{$page_sum_no}</span> ";
					echo "<br>";
					echo "<div class=\"div2_flex_parent\">";
					// input type=numberに値を入れたり他の処理するにはこの処理がいる。
					$page_display_number = (int)$page_display_number;
					switch($page_sum_no){
						// 合計ページが表示するページタブ数より少ないか同じ時
						case $page_sum_no <= $page_display_number:
							// 合計ページ分だけページタブを表示
							for($i = 1 ; $i < $page_sum_no + 1 ; $i++ ){
								echo create_input("submit","","page","","10",$i,"form","form1","");
							}
							// 合計ページが４未満の時はページタブ数選択機能を非表示にする。
							// 選んでも意味ないから。しかもエラーの元になる。
							if($page_sum_no < 4 ){
								$page_display_number_disable = true;
							}
							break;
						// 合計ページが表示するページタブ数より多い時
						default :
							switch($page_now_no){
								// 現在のページが表示するページタブ数より小さい（＝ページタブが１番から始まる）
								case $page_now_no < $page_display_number || ( $page_now_no == 1 && $page_display_number == 1 ):
									for($i = 1 ; $i < $page_display_number + 1 ; $i++){
										echo create_input("submit","","page","","10",$i,"form","form1","");
									}
									echo "<div>・・・</div>";
									echo "<button type=\"subm\" name=\"page\" value=\"{$page_sum_no}\" form=\"form1\">最後（{$page_sum_no}ページ）</button>";
									break;
								// 現在のページが表示するページタブ数より大きい（現在ページが大体真ん中にくるように表示する場合）
								case $page_now_no >= $page_display_number :
									echo "<button type=\"submit\" name=\"page\" value=\"1\" form=\"form1\">最初（1ページ）</button>";
									echo "<div>・・・</div>"; 
									if ($page_display_number % 2 == 0) {
										$page_low_dif_number  = $page_display_number / 2 - 1 ;
										$page_high_dif_number = $page_display_number / 2 ;
									}else{
										$page_low_dif_number  = floor($page_display_number / 2) ;
										$page_high_dif_number = floor($page_display_number / 2) ;
									}
									if($page_now_no < $page_sum_no - $page_high_dif_number){
										for($i = ( $page_now_no - $page_low_dif_number ) ; $i <= ( $page_now_no + $page_high_dif_number ) ; $i++){
											echo create_input("submit","","page","","10",$i,"form","form1","");
										}
										echo "<div>・・・</div>";
										echo "<button type=\"subm\" name=\"page\" value=\"{$page_sum_no}\" form=\"form1\">最後（{$page_sum_no}ページ）</button>";
									}else{
										for($i = ( $page_sum_no - ( $page_display_number - 1 ) ) ; $i <= $page_sum_no ; $i++){
											echo create_input("submit","","page","","10",$i,"form","form1","");
										}
									}
									break;
							}
						break;
					}
					echo "</div>";
					echo "<br>";
					if(!$page_display_number_disable){
						echo "表示中のページタブ数（おまけ機能）";
						echo create_input("number","page_display_number","page_display_number","","5",$page_display_number,"max",$page_sum_no,"");
						echo create_input("submit","","","","10","変更","form","form1","");
					}else{
						echo create_input("hidden","page_display_number","page_display_number","","5",$page_display_number,"max",$page_sum_no,"");
					}
				}
				?>
			</footer>
			<?php
				// 会員の場合はおすすめを表示するコンテンツをinclude。
				// 分ける必要なかったけど、別コンテンツをincludeしてみたかった笑。
				if($login){
					include "index_add.php";
				} 
			?>
		</section>
	</main>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
</body>
</html>