<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成者：星野（web1918)
修正者：秋野（web1902）
概要　：ログインページ
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
$header_common_tag;		// 中身：header タグ内で規定するタイトルタグとか
$footer_common_tag;		// 中身：footer タグ内で規定するタイトルタグとか

// ここで使う変数の宣言と初期値設定。
$id			= "";
$pw			= "";
$login_ok	= false;
$user_name	= "";
$user_type	= "";

// ここで使うSQL文の一覧表示と配列変数への設定。
$sql_array = array(
	"login1"	=> "select buyer_pw,buyer_name from k2g1_buyer where buyer_id = ?",
	"login2"	=> "update k2g1_buyer set buyer_pw = ? where buyer_id = ?",
	"login3"	=> "select seller_pw,seller_name from k2g1_seller where seller_id = ?",
	"login4"	=> "update k2g1_seller set seller_pw = ? where seller_id = ?"
);

/* --------------------------------------------------------------------------------------- */



if($_SERVER["REQUEST_METHOD"] == "POST"){
	$pflag = true;
	// 【処理No.を記載】ログイン処理
	if($_POST["id"] == "" || $_POST["pw"] == ""){
		$err_msg .= $err_array["all"];
		$err_msg .= $err_array["login1"];
	}else{
		$id = htmlspecialchars($_POST["id"],ENT_QUOTES);
		$pw = htmlspecialchars($_POST["pw"],ENT_QUOTES);
		switch($_POST["type"]){
			case "buyer":
				$sql_output_user_array = sql($sql_array["login1"],false,$id);
				// var_dump($sql_output_user_array);
				if(!empty($sql_output_user_array)){
					foreach($sql_output_user_array as $key => $val){
						foreach($val as $key2 => $val2){
							if($key2 == "buyer_pw"){
								if(password_verify($pw,$val2)){
									if(password_needs_rehash($val2,PASSWORD_DEFAULT)){
										sql($sql_array["login2"],false,password_hash($pw,PASSWORD_DEFAULT),$id);
									}
								$login_ok = true;
								$user_type	= 2;
								}else{
									$err_msg .= $err_array["all"];
									$err_msg .= $err_array["login2"];
								}
							}else{
								if($key2 == "buyer_name"){
									$user_name = $val2;
								}
							}
						}
					}
				}else{
					// IDが存在しない時
					// echo "id該当なし";
					$err_msg .= $err_array["all"];
					$err_msg .= $err_array["login2"];
				}
				break;
			case "seller":
				$sql_output_user_array = sql($sql_array["login3"],false,$id);
				if(!empty($sql_output_user_array)){
					foreach($sql_output_user_array as $key => $val){
						foreach($val as $key2 => $val2){
							if($key2 == "seller_pw"){
								if(password_verify($pw,$val2)){
									if(password_needs_rehash($val2,PASSWORD_DEFAULT)){
										sql($sql_array["login4"],false,password_hash($pw,PASSWORD_DEFAULT),$id);
									}
								$login_ok 	= true;
								$user_type	= 1;
								}else{
									$err_msg .= $err_array["all"];
									$err_msg .= $err_array["login2"];
								}
							}else{
								if($key2 == "seller_name"){
									$user_name = $val2;
								}
							}
						}
					}
				}else{
					// IDが存在しない時
					// echo "id該当なし";
					$err_msg .= $err_array["all"];
					$err_msg .= $err_array["login2"];
				}
				break;
		}
		if($login_ok){
			$_SESSION["user"]["id"]		= $id;
			$_SESSION["user"]["name"]	= $user_name;
			$_SESSION["user"]["type"]	= $user_type;
			switch($_SESSION["pre_page"]){
				case "index":
					if($user_type == 1){
						header("Location:seller_mypage.php");
						exit;
					}else{
						header("Location:index.php");
						exit;
					}
					break;
				case "item":
					header("Location:item.php");
					exit;
					break;
			}
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
		// tab の切替。ちょっと強引やけど。
		window.onload = function(){
			const login_li_1_tag = document.getElementById("login_div_1");
			const login_li_2_tag = document.getElementById("login_div_2");
			const login_form_1_tag = document.getElementById("login_form_1");
			const login_form_2_tag = document.getElementById("login_form_2");
			login_form_1_tag.classList.add('output');
			login_form_2_tag.classList.add('not_output');
			login_li_1_tag.addEventListener("click",tab_change,false);
			login_li_2_tag.addEventListener("click",tab_change,false);
		}

		const tab_change = function(){
			const login_form_1_tag = document.getElementById("login_form_1");
			const login_form_2_tag = document.getElementById("login_form_2");
			switch(event.target.id){
				case "login_div_1":
					login_form_1_tag.classList.add('output');
					login_form_1_tag.classList.remove('not_output');
					login_form_2_tag.classList.add('not_output');
					login_form_2_tag.classList.remove('output');
					break;
				case "login_div_2":
					login_form_1_tag.classList.add('not_output');
					login_form_1_tag.classList.remove('output');
					login_form_2_tag.classList.add('output');
					login_form_2_tag.classList.remove('not_output');
					break;
			}
		}
	</script>
	<style>
		/* 以降CSSの書き方 */
		.login_section_1{
			display : flex;
			margin	: auto;
		}
		.login_div_1{
			height			: 40px;
			width			: calc(100%/2);
			list-style		: none;
			border-radius	: 5px 5px 0px 0px;
			display			: inline-block;
		}
		.login_div_1:hover{
			font-weight	: bold;
		}
		#login_div_1{
			background-color : var(--color-light-navy);
			color			 : var(--color-light-orange);
			text-align		 : center;
			padding			 : 0.5em 0 0 0;
		}
		#login_div_2{
			background-color : var(--color-light-orange);
			color			 : var(--color-light-navy);
			text-align		 : center;
			padding			 : 0.5em 0 0 0;
		}
		.login_form_1{
			border			: 5px solid var(--color-light-navy);
			box-shadow		: 4px 4px 6px gray;
			font-size		: 15px;
		}
		.login_form_2{
			border			: 5px solid var(--color-light-orange);
			box-shadow		: 4px 4px 6px gray;
			font-size		: 15px;
		}
		.output{
			display : block; 
		}
		.not_output{
			display : none;
		}
		#seller_id:hover,
		#seller_password:hover{
			background-color : var(--color-light-orange);
		}
	</style>
	<title>ログイン</title>
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
				<h2>ログイン</h2>
			</header>
			<span class="err"><?= $err_msg ?></span>
			<br>
			<!-- title、h2以外、実際に記載するのはこっから下 -->
			<section class="login_section_1">
				<div id="login_div_1" class="login_div_1"> 購入者の方 </div>
				<div id="login_div_2" class="login_div_1"> 出品者の方 </div>
			</section>
			<section>
				<form id="login_form_1" class="login_form_1" action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="POST">
					<p><font color="red">【！！！！アカウント情報を更新しました！！！！】</font></p>
					<ul>
						<li>赤池　ID：akaike2 , PW：akaike</li>
						<li>秋野　ID：akino2 , PW：akino</li>
						<li>奥野　ID：okuno2 , PW：okuno</li>
						<li>西原　ID：nishihara2 , PW：nishihara</li>
						<li>星野　ID：hoshino2 , PW：hoshino</li>
						<li>山本　ID：yamamoto2 , PW：yamamoto</li>
					</ul>
					<table>
						<caption>購入者アカウントのログインページです。</caption>
						<tbody>
							<tr>
								<td>購入者ID</td>
								<td>
									<?= create_input("text","buyer_id","id","","20",$id,"","","IDを入力ください") ?>
								</td>
							</tr>
							<tr>
								<td>PW</td>
								<td>
									<?= create_input("password","buyer_password","pw","","20",$pw,"","","PWを入力ください") ?>
								<td>
							</tr>
							<tr>
								<td colspan="2">
									<?= create_input("hidden","type1","type","","20","buyer","","","") ?>
									<?= create_input("submit","btn","btn","","20","ログイン","","","") ?>
								</td>
							</tr>
						</tbody>
					</table>
				</form>
				<form id="login_form_2" class="login_form_2" action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="POST">
					<p><font color="red">【！！！！アカウント情報を更新しました！！！！】</font></p>
					<ul>
						<li>赤池　ID：akaike , PW：akaike</li>
						<li>秋野　ID：akino , PW：akino</li>
						<li>奥野　ID：okuno , PW：okuno</li>
						<li>西原　ID：nishihara , PW：nishihara</li>
						<li>星野　ID：hoshino , PW：hoshino</li>
						<li>山本　ID：yamamoto , PW：yamamoto</li>
					</ul>
					<table>
						<caption>出品者アカウントのログインページです。</caption>
						<tbody>
							<tr>
								<td>出品者ID</td>
								<td>
									<?= create_input("text","seller_id","id","","20",$id,"","","IDを入力ください") ?>
								</td>
							</tr>
							<tr>
								<td>PW</td>
								<td>
									<?= create_input("password","seller_password","pw","","20",$pw,"","","PWを入力ください") ?>
								<td>
							</tr>
							<tr>
								<td colspan="2">
									<?= create_input("hidden","type2","type","","20","seller","","","") ?>
									<?= create_input("submit","btn","btn","","20","ログイン","","","") ?>
								</td>
							</tr>
						</tbody>
					</table>
				</form>
				<form id="login_form_3" class="login_form_3" action="index.php" method="GET">
					<!-- 特に処理は必要ないけど、追加機能とかも考慮して一応formにしておいた。 -->
					<?= create_input("submit","btn","btn","","20","トップ画面に戻る","","","") ?>
				</form>
			</section>
			<!-- 実際に記載するのはこっから上 -->
		</section>
	</main>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
</body>
</html>