<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成者：秋野浩朗（web1902)
概要　：トップページ
更新日：2020/2/3

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
$display_order_array			= array("新着順","価格が高い順","価格が低い順","評価が高い順");
$display_order_chose_val_array	= array();
$display_order					= "";
$display_order_type				= "";
$item_number					= "";
$login							= false;

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
	"index3"	=> "SELECT distinct(item_id),item_name,item_seller_id,seller_name,seller_office_name,
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
			$search_item_word 				= htmlspecialchars($_POST["search_item_word"],ENT_QUOTES);
			$genre_chose_val_array			= $_POST["genre_name"];
			$display_number_chose_val_array	= $_POST["display_number"];
			$display_order_chose_val_array	= $_POST["display_order"];
			switch($display_number_chose_val_array[0]){
				case "10件":
					$display_number	= 10;
					break;
				case "20件":
					$display_number	= 20;
					break;
				case "50件":
					$display_number	= 50;
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
					$sql_array["index3"] .= " order by {$display_order} {$display_order_type} limit {$display_number}";
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
					$sql_array["index3"] .= " limit {$display_number}";
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

// ページネーション機能
if($display_number != "all"){
	$page_sum_no = $item_number / $display_number;
}else{
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
<head>
	<?= $head_common_tag ?>
	<script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<!-- <script src="../js/ajax.js"></script> -->
	<script>
		window.onload = function(){
			const display_number_tag = document.getElementById("display_number");
			display_number_tag.addEventListener("change",function(){
				event.srcElement.form.submit();
			});
			const display_order_tag = document.getElementById("display_order");
			display_order_tag.addEventListener("change",function(){
				ajax();
				pagination();
			});
		}
		var ajax = function(){
			// 1. ajax の開始
			$.ajax({	
				url	 : "./index_ajax.php", 								// 通信先のURL
				type : "POST",											// 使用するHTTPメソッド
				data :{													// 送信するデータ（キー：値の形式で記載。）
					'function_no'		: "index1", 
					'search_item_word'	: $('#search_item_word').val(),
					'genre_name'		: $('#genre_name').val(),
					'display_number'	: $('#display_number').val(),
					'display_order'		: $('#display_order').val()
				},
				dataType : "json"										// 応答のデータの種類 (xml/html/script/json【推奨】/jsonp/text)
				//timespan : 10000										// 通信のタイムアウトの設定(ミリ秒)　今回はなし。
			})
			// 2. done は通信に成功した時に実行される処理
			.done(function(data,textStatus,jqXHR) {
				$(".err").text("通信に成功しました");				// 通信成功したかどうかの確認用
				console.log(data);							// 取得したデータの確認用
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
											break;
										// evaluation の場合は以下の通り
										case "evaluation":
											if(data[key][i][key2]){
												$("#" + id).text((data[key][i][key2] * 100).toFixed(2) + "点");
											}else{
												$("#" + id).text("未評価");
											}
											break;
										// 他のカラムはそのまま代入
										default:
											$("#" + id).text(data[key][i][key2]);
											break;
									}
								})
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
		var pagination = function(){
			//alert("aa");
		}
		function to_item(){
			const hidden_tag = document.createElement('input');
			hidden_tag.type	 = "hidden";
			hidden_tag.name	 = "btn";
			hidden_tag.value = "商品詳細";
			event.target.appendChild(hidden_tag);
			event.toElement.parentElement.submit();
		}
		function to_seller(){
			const hidden_tag = document.createElement('input');
			hidden_tag.type	 = "hidden";
			hidden_tag.name	 = "btn";
			hidden_tag.value = "出品者詳細";
			event.target.appendChild(hidden_tag);
			event.toElement.parentElement.submit();
		}
	</script>
	<style>
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
		.div1_frex_parent{
			display : flex;
			align-items : flex-start;
			align-content: flex-start;
		}
		.section1_frex_children{
			border        : 1px solid;
			border-radius : 10px;
			padding       : 4px;
		}
		.section1_frex_children img{
			min-height : 100px;
			min-width  : 200px;
		}
		.div2_frex_parent{
			display         : flex;
			justify-content : center;
			align-items     : center;
		}
	</style>
	<title>中古家電.com</title>
</head>
<body>
	<?php include '../inc/var_dump.php' ?>
	<header>
		<?= $header_index_tag ?>
		<?php
			if($_SESSION["user"]["id"] == "guest"){
				echo "<form action=\"{$_SERVER["SCRIPT_NAME"]}\" method=\"POST\">";
				echo create_input("submit","","btn","","20","ログイン","","","");
				echo "</form>";
				echo "<form action=\"{$_SERVER["SCRIPT_NAME"]}\" method=\"POST\">";
				echo create_input("submit","","btn","","20","新規会員登録","","","");
				echo "</form>";
			}else{
				echo "<form action=\"{$_SERVER["SCRIPT_NAME"]}\" method=\"POST\">";
				echo create_input("submit","","btn","","20","マイページ","","","");
				echo "</form>";
			}
			?>
		</form>
	</header>
	<main>
		<section>
			<header>
				<h2>トップページ</h2>
			</header>
			<span class="err"><?= $err_msg ?></span>
			<br>
			<form id="form1" action="<?= $_SERVER["SCRIPT_NAME"]?>" method="POST">
				検索ワード<?= create_input("text","search_item_word","search_item_word","","40",$search_item_word,"","","商品名もしくは出品者名を入力") ?>
				<br>
				ジャンル<?= create_box("selectbox","genre_name","genre_name","",$genre_array,$genre_chose_val_array,true,true) ?>
				<br>
				<?= create_input("submit","","btn","","20","検索","","","") ?>
				<?= create_input("hidden","","btn","","20","検索","","","") ?>
				<br>
				表示件数<?= create_box("selectbox","display_number","display_number","",$display_number_array,$display_number_chose_val_array,true,true) ?>
				<br>
				並び順<?= create_box("selectbox","display_order","display_order","",$display_order_array,$display_order_chose_val_array,true,true) ?>
			</form>
			<?php 
				if($login){
					include "index_add.php";
				} 
			?>
			<section>
				<header>
					<h3>商品一覧</h3>
					ジャンル：<?= $genre_chose_val_array[0] ?>　商品件数：<font id="item_number"><?= $item_number ?></font> 件
				</header>
				<div class="div1_frex_parent">
					<?php
						if(empty($sql_output_item_array)){
							echo "<section><h4>該当する商品はありません。<h4></section>";
						}else{
							for($i=0;$i<count($sql_output_item_array);$i++){
								echo "<section class=\"section1_frex_children\">";
								// 画像パスの設定
								echo "<img id=\"item_image_path_$i\" src=\"{$sql_output_item_array[$i]["item_image_path"]}\" alt=\"{$sql_output_item_array[$i]["item_name"]}の画像\"
										width=\"100%\" height=\"100%\" >";
								// 商品ページに遷移する為のフォーム
								echo "<form id=\"form_item_$i\" action=\"{$_SERVER["SCRIPT_NAME"]}\" method=\"POST\">";
								// 商品名の設定
								echo "<h4 id=\"item_name_$i\" onclick=\"to_item()\" class=\"like_a\">";
								echo $sql_output_item_array[$i]["item_name"],"</h4>"; 
								// 商品ID（hidden）の設定
								echo create_input("hidden","item_id_$i","item_id","","20",$sql_output_item_array[$i]["item_id"],"","","");
								echo "</form>";
								// 出品者詳細ページに遷移する為のフォーム
								echo "<form id=\"form_seller_$i\" action=\"{$_SERVER["SCRIPT_NAME"]}\" method=\"POST\">";
								// 出品者名の設定
								echo "<p id=\"seller_name_$i\" onclick=\"to_seller()\" class=\"like_a\">";
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
								echo "<p>価格：<span id=\"item_price_$i\">",$sql_output_item_array[$i]["item_price"],"</span>円</p>";
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
					echo "<div class=\"div2_frex_parent\">";
					echo "<form id=\"form_page_$i\" action=\"{$_SERVER["SCRIPT_NAME"]}\" method=\"POST\">";
					switch($page_sum_no){
						case 1 :
							echo create_input("button","footer_btn1","btn","","10",($page_now_no),"","","");
							break;
						case 2 :
							echo create_input("button","footer_btn1","btn","","10",($page_now_no),"","","");
							echo create_input("button","footer_btn2","btn","","10",($page_now_no + 1),"","","");
							break;
						case 3 :
							echo create_input("button","footer_btn1","btn","","10",($page_now_no),"","","");
							echo create_input("button","footer_btn2","btn","","10",($page_now_no + 1),"","","");
							echo create_input("button","footer_btn2","btn","","10",($page_now_no + 2),"","","");
							break;
						default :
							if($page_now_no > 2){
								echo create_input("button","footer_btn1","btn","","10","先頭ページ","","","");
								echo "<div>・・・</div>";
							}
							for($i = $page_now_no ; $i < $i + 3 ; $i++){
								echo create_input("button","footer_btn{$i}","btn","","10",$i,"","","");
							}
							if($page_now_no < $page_sum_no - 1){
								echo "<div>・・・</div>";
								echo create_input("button","footer_btn7","btn","","10","最終ページ","","","");
							}
						break;
					}
					echo "</form>";
					echo "</div>";
				?>
			</footer>
		</section>
	</main>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
</body>
</html>