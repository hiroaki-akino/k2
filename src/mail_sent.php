<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成：秋野浩朗（web1902)
概要：メール送信後の確認画面
更新日：2020/01/31

【主な処理】

----------------------------------------------------------------------------------------- */



/* ----- 共通処理 ----------------------------------------------------------------------- */

include '../inc/config.php';
include '../inc/db_access.php';
include '../inc/functions.php';
include '../inc/variables.php';

// ここで使うinclude したファイルの変数。（必要に応じて var_dump で確認）
$pflag;					// 中身：false
$head_common_tag;		// 中身：head タグ内で規定するメタタグとか
$header_common_tag;		// 中身：header タグ内で規定するタイトルタグとか
$footer_common_tag;		// 中身：footer タグ内で規定するタイトルタグとか

// ここで使う変数の初期化。
$type					= "";
$pin_code				= "";
$sql_output_mail_array	= array();
// 検証用
$w						= "";

// ここで使うSQL文の一覧表示と配列変数への設定。
$sql_array = array(
	"mail_sent1"	=> "select mail_pin_code,mail_limit_time from k2g1_mail where mail_hash = ?",
	"mail_sent2"	=> "delete from k2g1_mail where mail_hash = ?"
);

/* --------------------------------------------------------------------------------------- */



if($_SERVER["REQUEST_METHOD"] == "POST"){
	$pflag = true;
	switch($_POST["btn"]){
		case "送信":
			if($_POST["pin_code"] != ""){
				$pin_code = htmlspecialchars($_POST["pin_code"],ENT_QUOTES);
				$sql_output_mail_array = sql($sql_array["mail_sent1"],false,$_SESSION["mail"]["mail_hash"]);
				if($pin_code == $sql_output_mail_array[0]["mail_pin_code"]){
					// echo date('Y-m-d H:i:s',strtotime($sql_output_mail_array[0]["mail_limit_time"]));
					// echo "<br>";
					// echo date('Y-m-d H:i:s');
					if(date('Y-m-d H:i:s',strtotime($sql_output_mail_array[0]["mail_limit_time"])) >= date('Y-m-d H:i:s')){
						sql($sql_array["mail_sent2"],false,$_SESSION["mail"]["mail_hash"]);
						$_SESSION["mail"]["login"] = true;
						header("Location:register.php");
						exit;
						break;
					}else{
						header("Location:mail.php?type=different");
						exit;
						break;
					}
				}else{
					$err_msg .= $err_array["all"];
					$err_msg .= $err_array["mail_sent2"];
				}
			}else{
				$err_msg .= $err_array["all"];
				$err_msg .= $err_array["mail_sent1"];
			}
			if(isset($_POST["type"])){ 
				$type = $_POST["type"]; 
			}
			break;
		case "メールアドレス入力画面に戻る":
			header("Location:mail.php");
			exit;
			break;
	}
}else{
	// mail.php から遷移した場合（URL方式）の処理
	// URL方式の場合			：type = same
	// 暗証番号入力方式の場合 	：type = different
	if(isset($_GET["type"])){ 
		$type = $_GET["type"]; 
	}
	// 検証用！mail.php から遷移した場合（暗証番号入力方式）の処理
	if(isset($_SESSION["mail"]["mail_hash"])){
		// 検証用の処理
		$w = $_SESSION["mail"]["mail_pin_code"];
	}
}

?>


<!DOCTYPE html>
<html lang="ja">
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#">
	<?= $head_common_tag ?>
	<script>
		// 以降JSの書き方例 基本的にPHPと同じ
	</script>
	<style>
		/* 以降CSSの書き方 */
	</style>
	<title>メール送信確認</title>
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
				<h2>メールの送信が完了しました！</h2>
			</header>
			<span class="err"><?= $err_msg ?></span>
			<br>
			<!-- title、h2以外、実際に記載するのはこっから下 -->
			<?php

				switch($type){
					case "same":
						echo "以降の処理はメールに記載のURLよりアクセス下さい。";
						echo "<br>";
						break;
					case "different":
						echo "<form action=\"{$_SERVER["SCRIPT_NAME"]}\" method=\"POST\">";
						echo "検証用（後で削除）:",$w; // 【！絶対削除！】【！絶対削除！】【！絶対削除！】【！絶対削除！】【！絶対削除！】【！絶対削除！】
						echo "<br>";
						echo "メールに記載される暗証番号を入力して下さい。<br>";
						echo create_input("hidden","","type","","",$type,"","","");
						echo create_input("text","pin_code","pin_code","","30",$pin_code,"","","暗証番号を入力ください");
						echo create_input("submit","","btn","","20","送信","","","");
						echo "</form>";
						break;
					default:
						echo "不正な操作がありました。";
				}
				echo "<form action=\"mail.php\" \"method=\"GET\">";
				echo create_input("submit","","btn","","20","メールアドレス入力画面に戻る","","","");
				echo "</form>";
			?>
			<!-- 実際に記載するのはこっから上 -->
		</section>
	</main>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
</body>
</html>