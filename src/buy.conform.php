<?php
/* --------------------------------------------------------------------------------------
//購入金額、合計金額にコンマ
 * //新規にダイレクト
 * select フォーマット
 * 変数 to string　
 * faile pass 



【基本情報】
作成者：赤池茂伸（web1901)
概要　：商品詳細表示
更新日：2020/2/6
更新者：秋野

【注意】
作業「前」に前日までのファイルのコピーを作成する。
コピーしたファイルを本日用のフォルダに移す。
コピーしたファイルで本日分の作業を開始する。

----------------------------------------------------------------------------------------- */



/* ----- 共通処理 ----------------------------------------------------------------------- */
include '../inc/config.php';
include '../inc/db_access.php';
include '../inc/functions.php';
include '../inc/variables.php';

$_SESSION ["pre_page"] = "buy_conform";

if($_SESSION["item"]["btn_back_cansel"] === true ){
	$_SESSION["item"]["btn_back_cansel"] = false;
	header("Location: index.php");
}

// ここで使う。config.php内の変数。（必要に応じて var_dump で確認）
$pflag;					// 中身：false
$head_common_tag;		// 中身：head タグ内で規定するメタタグとか
$header_common_tag;		// 中身：header タグ内で規定するタイトルタグとか
$datebase_dsn;			// 中身：DB情報

//結合テストまでの仮の値を設定
// ここで使う変数の宣言と初期値設定。
$user_name ="";
$user_id = "";
$item = "";
$db_item_array = array();
$db_buyer_array = array();
$db_seller_array = array();
$db__array = array();
$err_msg = array();//errorメッセージ
$msg = array();//購入処理後のメッセージ

$session_flg = 0; //セッションフラグ 1でセッションエラーとする
$p_flg = false; //購入フラグ 
$kounyuukosuu = (int)$_SESSION["item"]["kounyukosu"];
$user_id = $_SESSION["user"]["id"] ;
$item  = $_SESSION["item"]["item_id"];

$sql_array = array("index1" => "
						select *
						from k2g1_item
						,k2g1_condition
						where k2g1_item.item_condition_id = k2g1_condition.condition_id
						and k2g1_item.item_id = ? ",
					"index2"	=> "select * from k2g1_buyer where buyer_id = ? ",  
					"index3"	=> "select * from k2g1_seller where seller_id =?"
				);
$db_item_array = sql($sql_array["index1"],true,$_SESSION["item"]["item_id"]);
$_SESSION["seller"]["seller_id"] = $db_item_array[0]["item_seller_id"];
//配送先取得
if($_SESSION["user"]["type"] == 2){
	//k2_buyer参照
	$db_buyer_array = sql($sql_array["index2"],true,$_SESSION["user"]["id"]);
}else{
	//k2_seller参照
	$db_seller_array = sql($sql_array["index3"],true,$_SESSION["user"]["id"]);
}
if($_SESSION["user"]["id"] == null){
	$session_flg = 1;
}
if($_SERVER["REQUEST_METHOD"] == "POST"){
	$_SESSION["item"]["btn_back_cansel"] = true;
	$p_flg = true;
	if($_POST["btn"] == "やめる"){
		header("Location: item.php");
	}else{
		if($_POST["btn"] == "購入"){if($_SESSION["user"]["type"] == 2 ){
		//buyer
				$sql_array["index4"] = "UPDATE k2g1_item ";
				$sql_array["index4"] .= "SET item_quantity = item_quantity - ? ";
				$sql_array["index4"] .= "WHERE item_id = ? ";	
				$sign_db = sql($sql_array["index4"],false,$kounyuukosuu,$_SESSION["item"]["item_id"]);
				//echo "<pre>購入できたのか？"; var_dump($sign_db); echo "</pre>"; 
				
				if($sign_db !== false){
					//order table inserte
					$sql_array["index5"] = "INSERT INTO k2g1_order ";
					$sql_array["index5"] .= " (order_time,
											   order_evaluated,
											   order_quantity,
											   order_item_id,
											   order_user_id,
											   order_shipped,
											   order_high_postalcode,
											   order_low_postalcode,
											   order_address_1,
											   order_address_2,
											   order_address_3 )";					
					$sql_array["index5"] .= " VALUES" ;				
					$sql_array["index5"] .= "(now(),'0',?,?,?,'0',?,?,?,?,?)";
					$sign_db = sql($sql_array["index5"],
							true,
							$kounyuukosuu,
							$_SESSION["item"]["item_id"],
							$_SESSION['user']['id'],
							$db_buyer_array[0]['buyer_high_postalcode'],
							$db_buyer_array[0]['buyer_low_postalcode'],
							$db_buyer_array[0]['buyer_address_1'],
							$db_buyer_array[0]['buyer_address_2'],
							$db_buyer_array[0]['buyer_address_3']);
					//echo "<pre>"; var_dump($sign_db); echo "</pre>"; 
					
				
					if($sign_db !==false){
						//購入処理成功
					}else{
						//購入処理失敗
						echo "insert error ...order";
						$err_msg[] = "insert error ...order";
					}
				}else{
					//itemテーブルに在庫がない処理。（在庫を引き落とせない）
					echo "error...item table";
					$err_msg[] = "update error...item table ";
					
				
				}
		}else{
		//sellerの場合
				$sql_array["index4"] = "UPDATE k2g1_item ";
				$sql_array["index4"] .= "SET item_quantity = item_quantity - ? ";
				$sql_array["index4"] .= "WHERE item_id = ? ";
				$sign_db = sql($sql_array["index4"],false,$kounyuukosuu,$_SESSION["item"]["item_id"]);
				
				//echo "<pre>購入できたのかseller？"; var_dump($sign_db); echo "</pre>";
				
				if($sign_db !== false){
					//seller insert
					$sql_array["index5"] = "INSERT INTO k2g1_order ";
					$sql_array["index5"] .= " (order_time,
											   order_evaluated,
											   order_quantity,
											   order_item_id,
											   order_user_id,
											   order_shipped,
											   order_high_postalcode,
											   order_low_postalcode,
											   order_address_1,
											   order_address_2,
											   order_address_3 )";
					$sql_array["index5"] .= " VALUES" ;
					$sql_array["index5"] .= "(now(),'0',?,?,?,'0',?,?,?,?,?)";
					$sign_db = sql($sql_array["index5"],
							true,
							$kounyuukosuu,
							$_SESSION["item"]["item_id"],
							$_SESSION['user']['id'],
							$db_seller_array[0]['seller_high_postalcode'],
							$db_seller_array[0]['seller_low_postalcode'],
							$db_seller_array[0]['seller_address_1'],
							$db_seller_array[0]['seller_address_2'],
							$db_seller_array[0]['seller_address_3']);
					if($sign_db  !==false){
						//購入処理
					}else{
						//購入処理失敗
						echo "insert error ...seller table";
						$err_msg[] = "error ...seller table";
					}
				}else{
					//itemテーブルに在庫がない処理。（在庫を引き落とせない）
					echo "update error...item table";
					$err_msg[] = "error...item table";
				}
			}
			//$kounyu_flg = 1; 
			if($err_msg == null){
				//購入処理成功時処理
				//注文番号を表示する。
				
				// 秋野修正　↓
				// 元々のSQL文
				// $sql_array["index6"] = "
				// 						select max(order_id) ,order_address_1,order_address_2,order_address_3
				// 						from k2g1_order
				// 						where order_user_id = ? ";
				// 秋野が修正したSQL文
				$sql_array["index6"] = "
										select order_id,order_address_1,order_address_2,order_address_3
										from k2g1_order
										where order_user_id = ? 
										and order_id = (select max(order_id) from k2g1_order where order_user_id = ? )
										";
				$last_order_id = sql($sql_array["index6"],false,$_SESSION["user"]["id"],$_SESSION["user"]["id"]);
				// var_dump($last_order_id);
				// 秋野修正　↑　
				$msg[] = "購入ありがとうございました。<br>";
				$msg[] = "<input type ='button' value = 'topページへ' onclick ='jump()'>";
			}else{
				//購入処理処理
				foreach($err_msg as $key => $val){
					$msg[] = "購入処理エラー";
					}
			}
		
		}
	}	
}
else{
	session_user_check($_SESSION["user"]);
}
//echo "<pre>"; var_dump($db_buyer_array); echo "</pre>"; 
//echo "<pre>"; var_dump($db_seller_array); echo "</pre>";  

?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<?= $head_common_tag ?>
	<script>
		function jump(){
			location.href="index.php";
		}
		function jump_seller(){
			alert();
			location.href = 'seller.php';
		}
	</script>
	<style>
		body > header{
			margin: auto;
		}
		body > header > a{
			float: right;
		}
		section{
			margin: auto;
			text-align:center;
		}
		.form1 {
			padding: 20px 0px;
			border: 2px solid mediumblue;
			border-radius: 10px ;
			box-shadow: 4px 4px 6px gray;
		}
		.form1 table{
			margin:auto;
			text-align:center;
		}
		.form1 table input{
			margin: 5px 0px;
		}
		.form2 {
			text-align:left;
			color: gray;
		}
		.form2 input[type="submit"],
		.form2 input[type="button"]{
			text-align:left;
			background:none;
			background-color:none;
			margin: 5px auto 0px;
			width: 50%;
			border:none;
			border-radius: 0px;
			background-color: none;
			box-shadow: none;
			font-size: 17px;
			color: blue;
			text-decoration: underline;
			transition: all 0.8s ease;
		}
		.form2 input[type="submit"]:hover,
		.form2 input[type="button"]:hover{
			background:none;
			background-color:none;
			border: none;
			font-size: 20px;
			font-style: bold;
			font-weight: none;
			color: blue;
			text-decoration: underline;
			opacity: none;
			transition: all 0.5s ease;
		}
		.buy_conform_div1a{
			margin	: auto;
		}
		.buy_conform_div2a{
			width  			: var(--responce-large-width);
			height			: var(--responce-large-width);
			max-height		: 45%;
			position		: relative;
			margin			: var(--responce-margin);
			margin-bottom	: 1em;
			box-sizing		: border-box;
		}
		.buy_conform_div2a>img{
			position		: absolute;
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

	</style><title>中古家電.com</title>
</head>
<body>
<?php include '../inc/var_dump.php' ?>
<header>
		<?= $header_common_tag ?>
		<?php
			if($_SESSION["user"]["id"] == "guest"){
				echo "<form action=\"{$_SERVER["SCRIPT_NAME"]}\" method=\"POST\">";
				echo create_input("submit","index_input_1","btn","","20","ログイン","","","");
				echo "</form>";
				echo "<form action=\"{$_SERVER["SCRIPT_NAME"]}\" method=\"POST\">";
				echo create_input("submit","index_input_3","btn","","20","新規会員登録","","","");
				echo "</form>";
			}/*else{
				echo "<form action=\"{$_SERVER["SCRIPT_NAME"]}\" method=\"POST\">";
				echo create_input("submit","index_input_2","btn","","20","マイページ","","","");
				echo "</form>";
			}*/
			?>
		</form>
	</header>
	<main>
	<?php	
	if($p_flg === true){
			if($err_msg == null){
	?>
		<h2>購入完了</h2>
		<div class="buy_conform_div1a">
			<div>注文番号：<?=$last_order_id[0]["order_id"] ?></div>
			<div>商品名：<?=$db_item_array[0]["item_name"] ?></div>
			<div>購入個数：<?=$kounyuukosuu ?></div>
			<div>価格：<?=number_format($db_item_array[0]["item_price"])  ?></div>
			<div>購入価格：<?= number_format($kounyuukosuu * $db_item_array [0]["item_price"]) ?></div>
			配送先住所
			<div><?=$last_order_id[0]["order_address_1"] ?></div>
			<div><?=str_pad($last_order_id[0]["order_address_2"],3,0,STR_PAD_LEFT) ?></div>
			<div><?=str_pad($last_order_id[0]["order_address_3"],4,0,STR_PAD_LEFT) ?></div>
			<div>購入ありがとうございました。</div>
			<input type ='button' value = 'topページへ' onclick ='jump()'>
		</div>
	<?php 
			}else{
				echo "購入処理エラーです。";
				//err内容表示
				foreach($err_msg as $key => $val ){
					echo $val."<br>";
				}
				echo "<div><input type ='button' value = 'topページへ' onclick ='jump()'></div>";
			}
	}else{
		if($session_flg == 1){
			echo "セッションがありません";
		}else{
	?><h2>購入確認</h2>
		<div class="buy_conform_div1a res_row">
			<div class="buy_conform_div2a"><img src= "<?=$db_item_array[0]["item_image_path"] ?>"  alt="01" width="40%"></div>
			<div>
				<h4><?=$db_item_array[0]["item_name"] ?></h4>
				<div class="row">出品者：<div id ="syupin" onclick = "jump_seller()"><?=$db_item_array [0]["item_seller_id"] ?></div></div>
				<div>状態：<?=$db_item_array[0] ["condition_rank"] ?></div>
				<div>購入個数：<?=$kounyuukosuu ?></div>
				<div>価格：<?=number_format($db_item_array[0]["item_price"])  ?></div>
				<div>購入価格：<?= number_format($kounyuukosuu * $db_item_array [0]["item_price"]) ?></div>
			</div>
		</div>
		<div class="buy_conform_div1b">
			<h4>配送先は、登録されている住所になります</h4>
			<div class="buy_conform_div2b">
	<?php if($_SESSION["user"]["type"] == 2 ){ ?>
				<table>
					<tr><td>氏名</td><td><?=$db_buyer_array[0]["buyer_name"]  ?></td></tr>
					<tr><td>郵便番号</td><td><?=str_pad($db_buyer_array[0]["buyer_high_postalcode"],3,0,STR_PAD_LEFT)."－".str_pad($db_buyer_array[0]["buyer_low_postalcode"],4,0,STR_PAD_LEFT) ?></td></tr>
					<tr><td>都道府県</td><td><?=$db_buyer_array[0]["buyer_address_1"]  ?></td></tr>
					<tr><td>市区町村</td><td><?=$db_buyer_array[0]["buyer_address_2"]  ?></td></tr>
					<tr><td>町域</td><td><?=$db_buyer_array[0]["buyer_address_3"]  ?></td></tr>
				</table>
			</div>
	<?php }else{ ?>
				<table>
					<tr><td>氏名</td><td><?=$db_seller_array[0]["seller_name"]  ?></td></tr>
					<tr><td>企業名</td><td><?=$db_seller_array[0]["seller_office_name"]  ?></td></tr>
					<tr><td>郵便番号</td><td><?=str_pad($db_seller_array[0]["seller_high_postalcode"],3,0,STR_PAD_LEFT)."－".str_pad($db_seller_array[0]["seller_low_postalcode"],4,0,STR_PAD_LEFT) ?></td></tr>
					<tr><td>都道府県</td><td><?=$db_seller_array[0]["seller_address_1"]  ?></td></tr>
					<tr><td>市区町村</td><td><?=$db_seller_array[0]["seller_address_2"]  ?></td></tr>
					<tr><td>町域</td><td><?=$db_seller_array[0]["seller_address_3"]  ?></td></tr>
				</table>
			</div>
	<?php } ?>
	<!-- 秋野修正↓ -->
	<br>
	この内容で間違いありませんか？<br>
	<br>
	【！ご注意！】<br>
	「<font style="color:red!important;font-weight:bold!important;">このシステムは実習課題であり実際に購入を行えるサイトではなく、金銭のやり取りも発生することはありません。</font>」<br>
	上記に同意する<input type ="checkbox" id ="chb" onclick ="last_check()"><br>
	<!-- 秋野修正↑ -->
	<script>
	function last_check(){
		if(document.getElementById('chb').checked){
			document.getElementById('bt_chd').disabled = false;
		}else{
			document.getElementById('bt_chd').disabled = true;
		}
	}
	</script>
	
	
<form action="<?= $_SERVER["SCRIPT_NAME"] ?>" method="POST">

	<input type ="submit" name = "btn" value ="購入" disabled id = "bt_chd" onclick ="checked_check()">
	<input type ="submit" name = "btn" value ="やめる">
</form>
	<?php
		}
	}
	?>
	</main>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
</body>
</html>