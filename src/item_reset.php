<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成者：山本賢澄（web1921)
概要　：在庫の在庫数と削除フラグのリセット(デモ、検証用)
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
$msg;
$sql_output_array			= array();
$tds						= "<td>";
$tde						="</td>";

// ここで使うSQL文の一覧表示と配列変数への設定。
// 注意：変数の値などを使用してSQL文を作成したい時は「?」に置き換えて表示する。
// 注意：SQL() の引数（SQL文用の引数は３つ目以降に記載）は「?」に指定した値の順に記載する。
$sql_array = array(
	"item_reset1"	=> "select item_id,item_name,item_seller_id,genre_name,condition_rank,item_price,item_quantity,item_description,item_image_path,item_time,item_deleted
						from k2g1_item left join k2g1_genre on item_genre_id = genre_id
						left join k2g1_condition on item_condition_id = condition_id
						where  item_deleted = 1 or item_quantity = 0",
	"item_reset2"	=> "UPDATE k2g1_item SET item_deleted = 0 WHERE item_id = ?",
	"item_reset3"	=> "UPDATE k2g1_item SET item_quantity = 100,item_deleted = 0",
	"item_reset4"	=> "UPDATE k2g1_item SET item_quantity = 10 WHERE item_quantity = 0"
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
		switch($_POST["btn"]){
			case "reset":
				$item_id = htmlspecialchars($_POST["item_id"],ENT_QUOTES);
				if(false === ($sql_output_item_array = sql($sql_array["item_reset2"],true,$item_id))){
					$msg = "sqlエラー";
				}else{
					$msg = "削除フラグを0にしました";
				}
				break;
			case "all_reset":
				break;
			case "在庫のない商品の在庫数を10個にする":
				if(false === ($sql_output_item_array = sql($sql_array["item_reset4"],true))){
					$msg = "sqlエラー";
				}else{
					$msg = "在庫数が0個の商品を一括で10個にしました。";
				}
			/* 秋野追記（ファイルのダウンロード処理）⇩ */
			case "日次データをダウンロード":
				$filepath = "../file/daily_sales.csv";
				$filename = "日次売上集計表.csv";
				header('Content-Type: application/force-download');
				header('Content-Disposition: attachment; filename="'.$filename.'"');
				header("Content-Description: File Transfer");
				header('Content-Transfer-Encoding: binary');
				header('Content-Length: '.filesize($filepath));
				// ファイルを読み込みダウンロードを実行
				readfile($filepath);
				exit;
			/* 秋野追記（ファイルのダウンロード処理）↑ */
			default:
				break;
		}
		// 初期表示のためのデータ取得
		if(false === ($sql_output_item_array = sql($sql_array["item_reset1"],true))){
			$msg = "sqlエラー";
		}
}else{
		// 初期表示のためのデータ取得
		if(false === ($sql_output_item_array = sql($sql_array["item_reset1"],true))){
			$msg = "sqlエラー";
		}
}

// GETかPOSTにかかわらず必要な処理をここ以降で書く

?>

<!DOCTYPE html>
<html lang="ja">
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#">
	<?= $head_common_tag ?>
	<script>
		// 以降JSの書き方例 基本的にPHPと同じ
		function check(){
			if(window.confirm('送信してよろしいですか？')){ // 確認ダイアログを表示
				return true; // 「OK」時は送信を実行
			}else{ // 「キャンセル」時の処理
				window.alert('キャンセルされました'); // 警告ダイアログを表示
				return false; // 送信を中止
			}
		}
	</script>
	<style>
		/* 以降CSSの書き方 */
		body > header,
		main > header{
			margin    : auto;
			max-width : 800px;
		}
	</style>
	<title>検証(デモ)用在庫リセット</title>
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
				<h2>検証用在庫リセットページ</h2>
			</header>
			<span class="err"><?= $err_msg ?><br>
				<?php
					if(isset($msg)){
						echo $msg;
					}
				?>
			</span>
			<p>在庫数が0個か、削除された在庫が表示されます。</p>
			<p>"all_reset"でこれらの全てのデータの在庫数を"100"、削除フラグを"0"にします。</p>
			<p>"在庫のない商品の在庫数を10個にする"で在庫数が0個の商品の在庫数を一括で10個にします。</p>
			<p>行ごとの個別のボタンではその商品の削除フラグのみ"0"に戻します。</p>
			<!-- title、h2以外、実際に記載するのはこっから下 -->
			<div>
				<form action="<?=$_SERVER['SCRIPT_NAME']?>" method="post" onSubmit="return check()">
					<?= create_input("submit","","btn","","","all_reset","","","") ?>
					<?= create_input("submit","","btn","","","在庫のない商品の在庫数を10個にする","","","") ?>
				</form>
			</div>
			<table border="1px">
				<caption>deleted or zero</caption>
				<thead>
					<tr>
						<th>商品id</th>
						<th>商品名</th>
						<th>出品者id</th>
						<th>分類</th>
						<th>状態</th>
						<th>価格</th>
						<th>在庫数</th>
						<th>商品説明</th>
						<th>画像パス</th>
						<th>登録日時</th>
						<th>削除フラグ</th>
					</tr>
				</thead>
				<tbody>
					<?php
						$i = 1;
						if(!isset($sql_output_item_array)){
							echo "<tr><td colspan=\"11\">該当するデータがありません。</td></tr>";
						}else{
							foreach($sql_output_item_array as $val){
								echo "<tr>";
								echo "<form id=\"form_1_$i\" action=\"{$_SERVER["SCRIPT_NAME"]}\" method=\"POST\" onSubmit=\"return check()\">";
								echo $tds.$val["item_id"];
								echo create_input("hidden","","item_id","","",$val["item_id"],"","","").$tde;
								echo $tds.$val["item_name"].$tde;
								echo $tds.$val["item_seller_id"].$tde;
								echo $tds.$val["genre_name"].$tde;
								echo $tds.$val["condition_rank"].$tde;
								echo $tds.$val["item_price"].$tde;
								echo $tds.$val["item_quantity"].$tde;
								echo $tds.$val["item_description"].$tde;
								echo $tds.$val["item_image_path"].$tde;
								echo $tds.$val["item_time"].$tde;
								echo $tds.$val["item_deleted"];
								echo create_input("submit","","btn","","","reset","","","").$tde;
								echo "</form>";
								echo "</tr>";
								$i++;
							}
						}

					?>
				</tbody>
			</table>
			<!-- 実際に記載するのはこっから上 -->
		</section>
		<!-- 秋野追記↓ -->
		<br>
		<section>
			<header>
				<h2>各種売り上げ集計データダウンロード</h2>
			</header>
			<span class="err"><?= $err_msg ?></span>
			<p>出品者毎の売り上げ集計データ（CSV形式）のダウンロードができます。</p>
			<form action="<?=$_SERVER['SCRIPT_NAME']?>" method="POST">
				<?= create_input("submit","","btn","","","日次データをダウンロード","","","") ?>
			</form>
		</section>
		<!-- 秋野追記↑ -->
	</main>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
</body>
</html>