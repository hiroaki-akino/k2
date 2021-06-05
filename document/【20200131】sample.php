<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成者：秋野浩朗（web1902)
概要　：エクセルファイル「k2_議事メモ 兼 プログラム等一覧」に記載しているファイルの概要
更新日：更新した日付を記載（例：2020/1/14）

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
$_SESSION["pre_page"] 		= basename(__FILE__, ".php");
$search_item_word			= "";
$genre_array				= array();
$display_array				= array("10問表示","全問表示");
$address_1_val_array = array(
	"都道府県を選択",
	"北海道地方"	=> array("北海道"),
	"東北地方"		=> array("青森県","岩手県","宮城県","秋田県","山形県","福島県"),
	"関東地方"		=> array("茨城県","栃木県","群馬県","埼玉県","千葉県","東京都","神奈川県"),
	"中部地方" 		=> array("新潟県","富山県","石川県","福井県","山梨県","長野県","岐阜県","静岡県","愛知県"),
	"近畿地方"		=> array("三重県","滋賀県","京都府","大阪府","兵庫県","奈良県","和歌山県"),
	"中国地方"		=> array("鳥取県","島根県","岡山県","広島県","山口県"),
	"四国地方"		=> array("徳島県","香川県","愛媛県","高知県"),
	"九州地方"		=> array("福岡県","佐賀県","長崎県","熊本県","大分県","宮崎県","鹿児島県","沖縄県")
);

// ここで使うSQL文の一覧表示と配列変数への設定。
// 注意：変数の値などを使用してSQL文を作成したい時は「?」に置き換えて表示する。
// 注意：SQL() の引数（SQL文用の引数は３つ目以降に記載）は「?」に指定した値の順に記載する。
$sql_array = array(
	"index1"	=> "select genre_name from k2g1_genre",
	"index2"	=> "select count(item_id) from k2g1_item where item_quantity != 0 and item_deleted = 0",
	"index3"	=> "select item_id,item_name,item_seller_id,seller_name,item_price,item_quantity 
					from k2g1_seller,k2g1_item,k2g1_genre 
					where seller_id = item_seller_id and item_genre_id = genre_id 
					and genre_name = ? 
					and item_quantity != 0 and item_deleted = 0"
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



if($_SERVER["REQUEST_METHOD"] == "POST"){
	$pflag = true;

	// 【処理No.を記載】選択した解答を取得（未選択時は空値を代入）
	$qu_no = htmlspecialchars($_POST["qu_no"],ENT_QUOTES);
	if(isset($_POST["answer"])){
		$an_no = htmlspecialchars($_POST["answer"],ENT_QUOTES);
	}else{
		$an_no = "";
	}

	// 【1-2】選択された回答の正誤判断チェック **/
	if($_SESSION["question"]["qu_answer_correct"] == $an_no){
		// 正解してた時の処理
		switch($_SESSION["question"]["type"]){
			case "dojo":
				// 【1-3】k1_answer テーブルの累計回答数と累計正解数を+1にする処理（config.php参照）
				sql("select",$_SESSION["user"]["type"],"question2","question2",$qu_no,"","");
				// 【1-4】正誤結果をセッションに代入（0:不正解、1:正解）
				// 【1-7】結果表示ページにリダイレクト
				$_SESSION["question"]["result"] = 1;
				header("Location:answer.php");
				exit;
				break;

			case "r_test":
			case "d_test":
			case "e_test":
				// 上記の通り。
				sql("select",$_SESSION["user"]["type"],"question2","question2",$qu_no,"","");
				// 正誤結果を各問題毎のセッションに代入（0:不正解、1:正解）
				// 問題数カウントを+1にする(10問で終了(下の処理で判定))
				$_SESSION["question"]["result"][] = 1;
				$_SESSION["question"]["su"]++;
				break;

			case "create":
			case "confirm":
			case "modify":
			case "admin_approval":
				// 正誤結果をセッションに代入（0:不正解、1:正解）
				// 結果表示ページにリダイレクト（試験運用なので累計回答数とかはDBに登録しない。というか未登録なのでできない。）
				$_SESSION["question"]["result"] = 1;
				// 荒技（JSを参照）
				$qu_create_to_ans_submit = "true";
				break;
		}
	}
}else{
	// GET時の不正アクセスの制御処理（直リンクアクセスはindex.phpにリダイレクト。詳細な処理は config.php を確認）
	session_user_check($_SESSION["user"]);
}

// GETかPOSTにかかわらず必要な処理をここ以降で書く
// 各種タイプ毎にタイトル変更
switch($_SESSION["question"]["type"]){
	case "dojo":
		$title_type = "一問一答道場";
		break;
	case "create":
	case "confirm":
		$title_type = "レビューモード（内容確認）";
		break;
	case "modify":
		$title_type = "修正モード（修正内容確認）";
		break;
	case "admin_approval":
		$title_type = "承認モード（管理者確認）";
		break;
}

// 表示する問題の情報を上記の問題番号をキーにDBから取得
// config.phpで$sql_output_question_qu_arrayが書き換えられて、当該ページに各項目に自動で代入。
sql("select",$_SESSION["user"]["type"],"question1","question1",$qu_no,"","");
$_SESSION["question"]["qu_title"]			= $sql_output_question_qu_array["qu_title"];
$_SESSION["question"]["qu_question"]		= $sql_output_question_qu_array["qu_question"];
$_SESSION["question"]["qu_answer_correct"]	= $sql_output_question_qu_array["qu_answer_correct"];
$_SESSION["question"]["qu_explanation"]		= $sql_output_question_qu_array["qu_explanation"];
// 解答結果表示ページ（answer.php）で、各問題の正解内容（番号ではない）を表示する処理。
$w													= $sql_output_question_qu_array["qu_answer_correct"];
$_SESSION["question"]["qu_answer_correct_state"]	= $sql_output_question_qu_array["qu_answer_${w}"];

// foreachする場合
foreach($_SESSION["question"]["qu_no"] as $key => $val){
	if($key == $_SESSION["question"]["su"]){
		$qu_no = $val;
	}
}

// 二重foreachする場合
foreach($_GET["question"] as $key => $val){
	if(is_array($val)){
		foreach($val as $key2 => $val2){
			$sql_output_question_qu_array[$key] = $val2;
		}
	}else{
		$sql_output_question_qu_array[$key] = $_GET["question"][$key];
	}
}

// forループする場合
for( $i = 0 ; $i < count($array) ; $i++ ){

}

?>

<!DOCTYPE html>
<html lang="ja">
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#">
	<?= $head_common_tag ?>
	<script>
		// 以降JSの書き方例 基本的にPHPと同じ
		window.onload = function(){

			// 【2−1】ログアウト（トップに戻る）の確認処理。
			var a1_tag = document.getElementById("a1");
			a1_tag.addEventListener("click",function(){
				event.preventDefault();
				var confirm_result = confirm("「ログアウト」してログイン画面に戻りますか？");
				if(confirm_result){ 
					location.href = "index.php";
				}
			});

			// 制限時間タイマー処理。
			var limit_time         = <?= $sql_output_question_qu_array["qu_time_limit"] ?> * 1000;
			var limit_progress_tag = document.getElementById("limit_progress");
			limit_progress_tag.setAttribute("max",limit_time/10);
			var old_time = Date.now();
			var timer = setInterval(function(){
				var now_time = Date.now();
				var zan      = now_time - old_time; 
				var limit    = limit_time-zan;
				var limit_1  = ("00" + (Math.ceil((limit)/1000)-1)).slice(-2);
				var limit_2  = ("00" + Math.ceil((limit)/10)).slice(-2);
				document.getElementById("limit").style.fontSize = "50px";
				document.getElementById("limit").innerHTML      = limit_1 + ":" + limit_2;
				document.getElementById("limit").style.color    = "black";
				var limit_val                   = limit_1 * 1 + limit_2;
				limit_progress_tag.value        = limit_val;
				limit_progress_tag.style.height = "20px";

				if(limit_1 < 3){
					document.getElementById("limit").style.color = "red";
					limit_progress_tag.style.color               = "red";
				}
				if(limit_2 > 90 || limit_2 < 10 ){
					document.getElementById("limit").style.transition = "0.2s";
					document.getElementById("limit").style.fontWeight = "bold";
					document.getElementById("limit").style.fontSize   = "52px";
				}else{
					document.getElementById("limit").style.transition = "0.2s";
					document.getElementById("limit").style.fontWeight = "normal";
					document.getElementById("limit").style.fontSize   = "50px";
				}
				if(limit <= 0){
					clearInterval(timer);
					document.getElementById("limit").innerHTML = "00:00";
					document.getElementById("form").submit();
				}
			},10);
		}
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
		input[type="submit"]{
			width  : 40%;
			height : 60px;
		}
	</style>
	<title>問題</title>
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
				<h2>当該ページの固有のタイトル</h2>
			</header>
			<span class="err"><?= $err_msg ?></span>
			<br>
			<!-- title、h2以外、実際に記載するのはこっから下 -->
			<table>
				<caption>table_title</caption>
				<tbody>
					<tr>
						<td>aaaaaaa</td>
						<td>bbbbbbb</td>
					</tr>
				</tbody>
			</table>
			<section>
				<header>
					<h3>必要に応じて設定</h3>
				</header>
				<br>
				<form id="form" action="question.php" method="POST">
					<?php
						if($_SESSION["question"]["type"] == "create" || $_SESSION["question"]["type"] == "modify"){
							foreach($sql_output_question_qu_array as $key => $val){
								$name = "question[{$key}]";
								if(is_array($val)){
									foreach($val as $key2 => $val2){
										echo create_input("hidden","question",$name,"",$val2,"","","");
									}
								}else{
									echo create_input("hidden","question",$name,"",$val,"","","");
								}
							}
						}
						echo create_input("hidden","qu_no","qu_no","",$qu_no,"","","");
						echo create_input("submit","btn","btn","10","解答","","","");
					?>
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