<?php
/* --------------------------------------------------------------------------------------

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

$_SESSION ["pre_page"] = "item";

// ここで使うconfig.php内の変数。（必要に応じて var_dump で確認）
$pflag;					// 中身：false
$head_common_tag;		// 中身：head タグ内で規定するメタタグとか
$header_common_tag;		// 中身：header タグ内で規定するタイトルタグとか
$datebase_dsn;			// 中身：DB情報


// ここで使うSQL文の一覧表示と配列変数への設定。
// 注意：変数の値などを使用してSQL文を作成したい時は「?」に置き換えて表示する。
// 注意：SQL() の引数（SQL文用の引数は３つ目以降に記載）は「?」に指定した値の順に記載する。

$sql_array = array(
		"index1"	=> "
						select * 
						from k2g1_item
						,k2g1_condition
						where k2g1_item.item_condition_id = k2g1_condition.condition_id
						and k2g1_item.item_id = ?"
					);
//結合テストまでの仮の値を設定
// ここで使う変数の宣言と初期値設定。

$user_id = "";
$item = "";
$db_item_array = array();
$err_msg = array();						//errorメッセージ
$session_flg = 0; 						//セッションフラグ 1でセッションエラーとする
$user_id = $_SESSION["user"]["id"] ;
$item  = $_SESSION["item"]["item_id"];

// 初期処理
// DB接続
$db_item_array = sql($sql_array["index1"],true,$_SESSION['item']['item_id']);
$_SESSION["seller"]["seller_id"] = $db_item_array[0]["item_seller_id"];

if($_SESSION["user"]["id"] == null){
	$session_flg = 1;
}
// POST処理
if($_SERVER["REQUEST_METHOD"] == "POST"){
	// マイページ遷移処理の追加に伴いswitch分岐に変更しました
	switch($_POST["btn"]){
		case "購入（確認画面）":
			$_SESSION["item"]["kounyukosu"] = $_POST["kounyukosu"];
			$_SESSION["item"]["btn_back_cansel"] = false;
			header("Location: buy.conform.php");
			exit;
			break;
		case "戻る":
			header("Location: index.php");
			exit;
			break;
		case "ログイン":
				header("Location: login.php");
			exit;
			break;
		case "新規会員登録":
			header("Location: mail.php");
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
	}
}else{
	// GET時の不正アクセスの制御処理（直リンクアクセスはindex.phpにリダイレクト。詳細な処理は config.php を確認）
	session_user_check($_SESSION["user"]);
}
//echo "<pre>"; var_dump($db_item_array); echo "</pre>";  
?>

<html>
<head>
	<?= $head_common_tag ?>
	<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#">
	<script>
		function jump_seller(){
			alert();
			location.href = 'seller.php';
		}
		function func_goukei(){		
			var money = <?=(int)($db_item_array[0]["item_price"])  ?> * document.getElementById("kingaku").value;
			document.getElementById("goukeikingaku").innerHTML = Number(money).toLocaleString(); 
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
		.res_wid{
			width	: var(--responce-large-width);
			height	: var(--responce-large-height);
		}
		.item_div1a{
			margin			: auto;
		}
		.item_div2a{
			width  			: var(--responce-large-width);
			height			: var(--responce-large-height);
			max-width		: 50%;
			max-height		: 45%;
			position		: relative;
			margin			: var(--responce-margin);
			margin-bottom	: 1em;
			box-sizing		: border-box;
		}
		.item_div2a>img{
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
		.item_div2b{
			padding			: 1em 3em;
			border-radius	: 5px;
		}
		.item_div2b>div{
			margin-bottom	: 0.5em;
		}
		.item_div3a{
			margin-top	: 1em;
		}
		.item_div3a>input{
			width	: 100%;
			height	: 2em;
		}
		.item_div1b>h4{
			margin-bottom	: 0;
		}
		.item_div2c{
			min-height	: 5em;
			padding		: 0 1em;
		}
	</style>
	<title>中古家電.com</title>
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
			}else{
				echo "<form action=\"{$_SERVER["SCRIPT_NAME"]}\" method=\"POST\">";
				echo create_input("submit","index_input_2","btn","","20","マイページ","","","");
				echo "</form>";
			}
			?>
		</form>
	</header>
	<main>
	<!------ 商品詳細 ------>
		<section>
			<h2><?=$db_item_array[0]["item_name"] ?></h2>
			<form action="<?= $_SERVER["SCRIPT_NAME"] ?>" method="POST">
				<div class="item_div1a res_row">
				<!-- 商品画像 -->
					<div class="item_div2a res_wid">
						<img src="<?=$db_item_array[0]["item_image_path"] ?>" alt="商品イメージ">
					</div>
				<!-- 商品情報 -->
					<div class="item_div2b">
						<div class="row">出品者：
							<div id="syupin" onclick="jump_seller()"><?=$db_item_array[0]["item_seller_id"]?></div>
						</div>
						<div>状態：<?=$db_item_array[0]["condition_rank"] ?></div>
						<div>在庫数：<?=$db_item_array[0]["item_quantity"] ?></div>
						<div>価格：¥<?= number_format($db_item_array[0]["item_price"])?></div>
						<div class="row">合計金額：¥
							<div id="goukeikingaku"><?=number_format($db_item_array[0]["item_price"])?></div>
						</div>
						<div>
							購入個数：
							<input type="number" name="kounyukosu" min="1" max="<?=$db_item_array[0]["item_quantity"]?>" onChange="func_goukei()" id="kingaku" value="1">
						</div>
					<!-- ユーザータイプごとの表示制御(ボタン) -->
						<div class="item_div3a bottom">
						<!-- ログイン済みのとき -->		
							<?php if($_SESSION["user"]["id"] != "guest"){ ?>
							<input type="submit" name="btn" value="購入（確認画面）">
						<!-- 未ログインのとき -->
							<?php }else{ ?>
								会員登録しないと購入できません
								<input type="submit"  name="btn" value="ログイン">
								<input type="submit" name="btn" value="新規会員登録">
							<?php } ?>
							<input type="submit"  name="btn" value="戻る">
						</div>
					</div>
				</div>
				<div class="item_div1b">
					<h4>商品説明：</h4>
					<div class="item_div2c border">
						<p><?=$db_item_array[0]["item_description"]?></p>
					</div>
				</div>
			</form>
		</section>
	</main>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
</body>
</html>