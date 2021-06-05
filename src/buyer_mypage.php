<?php



/* --------------------------------------------------------------------------------------

【基本情報】
作成者：星野紘輝（web1918)
概要　：エクセルファイル「k2_議事メモ 兼 プログラム等一覧」に記載しているファイルの概要
更新日：2020/1/29）

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
$sql_output_buyer_mypage_user_array			= array();						// ユーザー情報取得用配列
$db_up										= false;						// 以下会員情報変更用の変数
$user_name									= "";							// 
$high_postalcode							= "";							// 
$low_postalcode								= "";							// 
$address1									= "";							// 
$address2									= "";							// 
$address3									= "";							// 
$user_id									= "";							// ここまで会員情報変更用の変数
$sql_output_buyer_mypage_history_array		= array();						// 購入履歴取得用配列
$msg										= "";
$tab_btn									= "radio1a";					// デフォルトタブの切替用変数
$tab_section								= "buyer_mypage_section1a";		// デフォルトタブの切替用変数

// ここで使うSQL文の一覧表示と配列変数への設定。
// 注意：変数の値などを使用してSQL文を作成したい時は「?」に置き換えて表示する。
// 注意：SQL() の引数（SQL文用の引数は３つ目以降に記載）は「?」に指定した値の順に記載する。
$sql_array = array(
		"buyer_mypage3"	=> "UPDATE k2g1_buyer SET buyer_name = ?,buyer_high_postalcode = ?,buyer_low_postalcode = ?,buyer_address_1 = ?,buyer_address_2 = ?,buyer_address_3 = ? WHERE k2g1_buyer.buyer_id = ?",
		"buyer_mypage1"	=> "select order_id
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
		"buyer_mypage2"	=> "select * from k2g1_buyer where buyer_id = ?"
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
function h($text){
	return htmlspecialchars($text,ENT_QUOTES);
}

 // POST処理
if($_SERVER["REQUEST_METHOD"] == "POST"){
	echo "<hr>";
	$tab_btn		= "radio1".$_POST["buyer_mypage_tab"];
	$tab_section	= "buyer_mypage_section1".$_POST["buyer_mypage_tab"];

	//var_dump($_POST);
	// 処理分岐
	switch($_POST["btn"]){
		// 出品者評価ページへの遷移処理
		case '出品者評価':
			echo "出品者評価処理";
			$_SESSION['evaluate_form']['order_id'] = $_POST["order_id"];
			//$_SESSION['evaluate_form']['item_id'] = $_POST["item_id"];
			//$_SESSION['evaluate_form']['seller_id'] = $_POST["seller_id"];
			header("Location: evaluate_form.php");
			exit;
			break;
		// 会員情報変更処理
		case '確定':
			echo "更新する処理";
			$user_name			= h($_POST["buyer_name"]);
			$high_postalcode	= h($_POST["buyer_high_postalcode"]);
			$low_postalcode		= h($_POST["buyer_low_postalcode"]);
			$address1			= h($_POST["buyer_address_1"]);
			$address2			= h($_POST["buyer_address_2"]);
			$address3			= h($_POST["buyer_address_3"]);
			$user_id			= h($_SESSION["user"]["id"]);
			if(($db_up = sql($sql_array["buyer_mypage3"],true,$user_name,$high_postalcode,$low_postalcode,$address1,$address2,$address3,$user_id)) === false){
				
			}
			$_SESSION["user"]["name"] = $user_name;
			header("Location: ".$_SERVER["SCRIPT_NAME"]);
			exit;
			break;
	}
}else{
	session_buyer_user_check($_SESSION["user"]);
}
//GETかPOSTかに関わらず行う処理

//var_dump($_SESSION);
//var_dump($_SESSION ["user"]["id"]);

// 購入履歴情報の取得
if(!($sql_output_buyer_mypage_history_array = sql($sql_array["buyer_mypage1"],false,$_SESSION["user"]["id"]))){
	$sql_output_buyer_mypage_history_array = array();
	$msg = "購入履歴はありません";
}
$sql_output_buyer_mypage_user_array = sql($sql_array["buyer_mypage2"],true,$_SESSION["user"]["id"]);
echo "<pre>";
// var_dump($sql_output_buyer_mypage_user_array);
echo "</pre>";
?>

<!DOCTYPE html>
<html lang="ja">
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#">
	<?= $head_common_tag ?>
	<title></title>
	<script>
		// デフォルトタブの表示関数
		window.onload = function(){
			get("<?=$tab_btn?>").checked = true;
			get("<?=$tab_section?>").style.display = "block";
		}
	</script>
    <style>
/* ======== タブ ======== */
		.buyer_mypage_nav1{
			display			: flex;
			flex-direction	: row;
			justify-content	: space-between;
			width			: 80%;
			margin			: auto;
			border-radius		: 2px;
		}
		.label1{
			border				: dotted 1px #aaaaaa;
			width				: 5em;
			height				: 2em;
			background-color	: var(--color-light-navy);
			color				: white;
			text-align			: center;
			vertical-align		: bottom;
			padding				: 0.5em;
			width				: 50%;
			border-radius		: 2px;
		}
		.seller_mypage_label:hover{
			opacity	: 0.5;
		}
		.radio1{
			display	: none;
		}
		.radio1:checked + .label1{
			background-color: var(--color-orange);
			color			: black;
			font-weight		: bold;
		}
		section{
    		display: none;
    	}
/* ======== 購入履歴 ======== */
    	.buyer_mypage_section1 {
  			overflow: auto;
  			scroll-snap-type: y mandatory;
			width: 80%;
			margin :auto;
		}
		.buyer_mypage_div1a{
			border		: solid 1px var(--color-light-gray);
			width		: 100%;
			height		: 80%;
			margin		: auto;
			padding		: 0.5em;
			box-sizing	: border-box;
		}
		.buyer_mypage_div2a{
			border-bottom	: dotted 1px var(--color-light-gray);
		}
		.buyer_mypage_div2a input{
			font-size	: 12px;
			height		: 2.5em;
		}
		.buyer_mypage_div3b{
			padding-bottom	: 0.5em;
			justify-content	: space-between;
		}
		.buyer_mypage_div4a{
			width  			: var(--responce-small-width);
			height			: var(--responce-small-width);
			max-width		: 100%;
			margin			: var(--responce-margin);
			position		: relative;
			min-width		: 30%;	
		}
		.buyer_mypage_div4a img{
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
		.buyer_mypage_div4b{
			padding			: 1em;
		}
		.buyer_mypage_div4c{
			padding			: 1em;
		}
		.buyer_mypage_div1a h4{
			margin	: 0.5em 0;
		}
/* ======== 会員情報 ======== */
		.buyer_mypage_div1b{
			border		: solid 1px var(--color-light-gray);
			padding		: 0.5em;
			box-sizing	: border-box;
			width		: 100%;
			margin		: auto;
		}
		.buyer_mypage_div2b{
			padding			: 0.5em;
			border-bottom	: dotted 1px var(--color-light-gray); 
		}
		#buyer_low_postalcode,
		#buyer_high_postalcode{
			width	: 5em;
		}
		#d_btn{
			text-align	: right;
		}
		#d_btn input[type="button"]{
			font-size	: var(--font-small);
			height		: 2.5em;
		}
		.buyer_mypage_div3c{
    		font-weight: bold;
		}
		.buyer_mypage_div3d{
			font-size	: calc(var(--font-small) - 2px);
			font-weight	: bold;
		}
    </style>
</head>
<body>
	<?php // include '../inc/var_dump.php' ?>
	<header>
	<?= $header_common_tag ?>
	</header>
	<!------ タブメニュー ------>
	<nav class="buyer_mypage_nav1">
		<input type="radio" id="radio1a" class="radio1" name="radio1" onclick="tab_change('buyer','a')">
		<label class="label1" for="radio1a">購入履歴</label>
		<input type="radio" id="radio1b" class="radio1" name="radio1" onclick="tab_change('buyer','b')">
		<label class="label1" for="radio1b">会員情報</label>
	</nav>
	<!------購入履歴------>
	<section id="buyer_mypage_section1a" class="buyer_mypage_section1" style="display: none">
		<header>
				<h2>購入履歴</h2>
			</header>
		<div class="buyer_mypage_div1a overflow">
		  <?=$msg?>
		  <?php foreach($sql_output_buyer_mypage_history_array as $key => $val): ?>
		  <!-- ここから注文一件分 -->
			<form action="<?=$_SERVER["SCRIPT_NAME"]?>" method="post">
				<input type="hidden" name="buyer_mypage_tab" value="a">
				<div class="buyer_mypage_div2a">
					<div class="buyer_mypage_div3a">
						<h4>注文番号：<?=$val["order_id"]?></h4>
						<input type="hidden" name="order_id" value="<?=$val["order_id"]?>">
						<span>注文日時：<?=$val["order_time"]?></span><br>
						<span>出荷状況：<?php if($val["order_shipped"]){echo "出荷済み";}else{echo "未出荷";} ?></span>
					</div>
					<div class="buyer_mypage_div3b res_row">
						<div class="buyer_mypage_div4a">
							<div>
								<img src="<?=$val["item_image_path"]?>">
							</div>
						</div>
						<div class="buyer_mypage_div4b">
							<div>
								<span>商品ID：</span>
								<span><?=$val["order_item_id"]?></span>
							</div>
							<div class="buyer_mypage_div5a">
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
						<div class="buyer_mypage_div4c">
							<div>
								<span>出品者ID</span>
								<span><?=$val["item_seller_id"]?></span>
							</div>
							<div>
								<?php if($val["order_evaluated"] === 0){ ?>
								<input type="submit" value="出品者評価" name="btn">
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
	</section>
	<!------会員情報------>
	<section id="buyer_mypage_section1b" class="buyer_mypage_section1" style="display: none">
			<header>
				<h2>会員情報確認・変更</h2>
			</header>
		<div class="buyer_mypage_div1b">
			<form action='buyer_mypage.php' method='POST'>
				<input type="hidden" name="buyer_mypage_tab" value="b">
				<div class="buyer_mypage_div2b">
					<div class="buyer_mypage_div3c">
						ID:<?=$sql_output_buyer_mypage_user_array[0]["buyer_id"];?>
					</div>
				</div>
				<div class="buyer_mypage_div2b">
					郵便番号
					<div id ="d_buyer_postalcode" class="buyer_mypage_div3c">
						<input type="text" required value="<?=str_pad($sql_output_buyer_mypage_user_array[0]['buyer_high_postalcode'],3,0,STR_PAD_LEFT);?>" id ="buyer_high_postalcode" name="buyer_high_postalcode">
						－
						<input type="text" required value="<?=str_pad($sql_output_buyer_mypage_user_array[0]['buyer_low_postalcode'],4,0,STR_PAD_LEFT);?>" id ="buyer_low_postalcode" name="buyer_low_postalcode">
					</div>
					<div id="d_buyer_postalcode_r" class="buyer_mypage_div3d"></div>
				</div>
				<div class="buyer_mypage_div2b">
					都道府県名
					<div id ="d_buyer_address_1" class="buyer_mypage_div3c">
						<input type="text" required value="<?=$sql_output_buyer_mypage_user_array[0]['buyer_address_1']; ?>" id ="buyer_address_1" name="buyer_address_1">
					</div>
					<div id ="d_buyer_address_1_r" class="buyer_mypage_div3d"></div>		
				</div>
				<div class="buyer_mypage_div2b">
					市町村名
					<div id ="d_buyer_address_2" class="buyer_mypage_div3c">
						<input type="text" required value="<?=$sql_output_buyer_mypage_user_array[0]['buyer_address_2']; ?>" id ="buyer_address_2" name="buyer_address_2">
					</div>
					<div id ="d_buyer_address_2_r" class="buyer_mypage_div3d"></div>		
				</div>
				<div class="buyer_mypage_div2b">
					番地
					<div id ="d_buyer_address_3" class="buyer_mypage_div3c">
						<input type="text" required value="<?=$sql_output_buyer_mypage_user_array[0]['buyer_address_3']; ?>" id ="buyer_address_3" name="buyer_address_3">
					</div>
					<div id ="d_buyer_address_3_r" class="buyer_mypage_div3d"></div>		
				</div>
				<div class="buyer_mypage_div2b">
					氏名
					<div id ="d_buyer_name" class="buyer_mypage_div3c">
						<input type ="text" required value="<?=$sql_output_buyer_mypage_user_array[0]['buyer_name']; ?>" id ="buyer_name" name="buyer_name">
					</div>
					<div id ="d_buyer_name_r" class="buyer_mypage_div3d"></div>
				</div>
				<div id ="d_btn" class="buyer_mypage_div2b">
					<input type="button" value="確認"   id="btn1" onclick="confirm_chenge('buyer')" style="display: block">
					<input type="button" value="再編集" id="btn3" onclick="rewrite_change('buyer')" style="display: none">
					<input type="submit" value="確定"   id="btn2" name="btn" onsubmit="check()" style="display: none">
				</div>
			</form>
		</div>
	</section>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
	<script src="../js/mypage_functions.js"></script>
</body>
</html>