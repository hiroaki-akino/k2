<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成者：奥野涼那（web1907)
概要　：出品者評価
更新日：20200121

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




// ここで使うconfig.php内の変数。（必要に応じて var_dump で確認）
		// 中身：false
		// 中身：head タグ内で規定するメタタグとか
		// 中身：header タグ内で規定するタイトルタグとか

// ここで使う変数の宣言と初期値設定。
/* --------------------------------------------------------------------------------------- */
//echo "<pre>";
//var_dump($_SESSION);
//echo "</pre>";

//echo "<pre>";
//var_dump($_SESSION["evaluate_form"]["order_id"]);
//echo "</pre>";


$sql = "
	select * from k2g1_order,k2g1_item,k2g1_seller
	where k2g1_order.order_item_id = k2g1_item.item_id
	and k2g1_order.order_id = ?
	and item_seller_id = seller_id
	"
;


$order_arr = sql($sql,false,$_SESSION["evaluate_form"]["order_id"]);
//echo "<pre>";
//var_dump($order_arr);
//echo "</pre>";



if($_SERVER["REQUEST_METHOD"] == "POST"){
	if( $_POST["comment"] == null ){
		$err_msg .= $err_array["all"];
		$err_msg .= $err_array["evaluate_form1"];
	}else{
		if($_POST["hyouka"][0] == null || $_POST["hyouka"][1] == null ){
			$err_msg .= $err_array["all"];
			$err_msg .= $err_array["evaluate_form1"];
		}else{
			//ＤＢ
			if($_POST["hyouka"] =="bad" ){
				$good = 0;
				$bad = 1;
			}else{
				$good = 1;
				$bad = 0;
			}
			$sql ="
				INSERT INTO `k2g1_review` (`review_order_id`, `review_time`, `review_good`, `review_bad`, `review_coment`) 
				VALUES (?, now(), ?, ?, ?)
				";	
			$ins_order = sql($sql,true,$_SESSION["evaluate_form"]["order_id"],$good,$bad,$_POST["comment"]);
			//var_dump($ins_order);
			//echo "フラグたったい";
			$sql5 = "UPDATE `k2g1_order` SET `order_evaluated` = '1' WHERE `k2g1_order`.`order_id` = ? ";
			$ins_order = sql($sql5,true,$_SESSION["evaluate_form"]["order_id"]);	
			header("Location: buyer_mypage.php");
		}
	}
}

// GETかPOSTにかかわらず必要な処理をここ以降で書く

?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<?= $head_common_tag ?>
	<script>
		// 以降JSの書き方例 基本的にPHPと同じ
		//function msg(){
			//if (document.getElementById('comment').value == "" )  {
			   // alert('空文字です');
			//} else {
			
			//alert("送信しました");
			// window.location.href = 'buyer_mypage.php';

			//}
		//}
	</script>
	<style>
		/* 以降CSSの書き方 */
		body > header,
		main > header{
			margin    : auto;
			max-width : 800px;
		}
		.limit{
			position   : absolute ; 
			top        : -50px; 
			right      : 20px;
			text-align : center; 
		}
		fieldset{
			padding : 20px 0px 30px;
		}
		fieldset label{
			display        : inline-block;
			width          : 20%;
			vertical-align : middle;
			font-size      : 20px;
		}

	</style>
	<title>出品者評価</title>
</head>
<body>
<?php include '../inc/var_dump.php' ?>
	<header>
		<?= $header_common_tag ?>
	</header>
	<main>
		<!-- 以降HTMLの記載例。-->
		<!-- インデント必須。タグは２行に分けて挟む。ただし直接文字記載するタグは１行にまとめる。 -->
		<!-- ここにないものはセマンティックコーディングに沿って記載 -->
		<!-- 例：独立したコンテンツならarticle、なくてもいい補足ならaside、主要なリンクならnav使うとか。 -->
		<!-- 例：わからんかったらsectionタグ使う。divタグはどうしようもない最終手段として使う。 -->
		<!-- ちなみに全体に影響するCSSはそれ前提でセレクタ指定するから変な構成は控えてねー -->
		<!-- uタグとかテキスト装飾系は意味を理解して使用する。CSSが目的ならCSSでやる。 -->
		<section>
			<header>
				<h2>出品者評価</h2>
			</header>
			<span class="err"><?= $err_msg ?></span>
			<br>
			<!-- title、h2以外、実際に記載するのはこっから下 -->
			<p>評価する出品者名：　<?= $order_arr[0]["seller_name"] ?></p>
			<p>購入した商品名　:　<?= $order_arr[0]["item_name"] ?></p>
			<br>
			<form action="evaluate_form.php" method="POST" >
				<table width="50%" text-align="right">
					<tr>
						<td width="20%">出品者へのコメント</td>
						<td><input type="text" name="comment" id="comment" value="" size="30" placeholder="コメントを入力して下さい"></td>
					</tr>
					<tr>
						<td width="20%">出品者への評価</td>
						<td>
							<label><input type="radio" name="hyouka" value="good">👆（Good！）</label>
							<label><input type="radio" name="hyouka" value="bad">👇（Bad！）</label>
						</td>
					</tr>
				</table>
				<input type="submit" value="送信"  onclick = "msg()">
			</form>
			<!-- 実際に記載するのはこっから上 -->
		</section>
	</main>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
</body>
</html>