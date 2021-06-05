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
//session_start();
include '../inc/config.php';
include '../inc/db_access.php';
include '../inc/functions.php';
include '../inc/variables.php';

//echo "<pre>"; var_dump($_SESSION); echo "</pre>";



$sql ="
select sum(review_good),sum(review_bad) from k2g1_item left join k2g1_order
on k2g1_item.item_id = k2g1_order.order_item_id
right join k2g1_review on k2g1_order.order_id = k2g1_review.review_order_id
where k2g1_item.item_seller_id = ?
    ";



$row = sql($sql,true,$_SESSION["seller"]["seller_id"]);

//echo "<pre>"; var_dump($row); echo "</pre>";

$p = $row[0]["sum(review_good)"];
$m = $row[0]["sum(review_bad)"];

if($p == null){
    $p = 0;
}
if($m == null){
    $m = 0;
}

if(($p + $m) == 0){
    $hyouka = "未評価";
}else{
     $hyouka = round ($p / ($p + $m) *100);
}

//echo "<pre>"; var_dump($hyouka); echo "</pre>";





$sql_array = array(
    "index1"	=> "select * from k2g1_seller",
    "index2"	=> "select * from k2g1_seller where item_id = ? and item_genre_id = ?"
);



$row = sql($sql_array["index1"],true);




$sql2 =  "select seller_name from k2g1_seller where seller_id = ?";
$row2 = sql($sql2,true,$_SESSION["seller"]["seller_id"]);
/*
echo "<pre>";
var_dump($row2);
echo "</pre>";
*/

$name = $row2[0]["seller_name"];




//------------------------------------and item_deleted = 0


$sql3 = "select item_name,item_price,item_quantity from k2g1_item 
         where item_seller_id = ?
         and item_quantity != 0 and item_deleted = 0
         limit 10";
$row3 = sql($sql3,true,$_SESSION["seller"]["seller_id"]);

//echo "<pre>"; var_dump($row3); echo "</pre>";






//select k2g1_review.review_coment from k2g1_review left join k2g1_order
//right join k2g1_item on k2g1_order.order_id = k2g1_item.item_id
//where k2g1_item.item_seller_id = ?

//select * from k2g1_review


$sql4 ="
select item_name , item_id,k2g1_order.order_id, review_coment,k2g1_order.order_user_id,k2g1_review.review_good,k2g1_review.review_bad,item_seller_id
from k2g1_review ,k2g1_order ,k2g1_item
where k2g1_review.review_order_id = k2g1_order.order_id
and k2g1_order.order_item_id = k2g1_item.item_id
and item_seller_id = ?
limit 10"
;

$row4 = sql($sql4,true,$_SESSION["seller"]["seller_id"]);

//echo "<pre>"; var_dump($row4); echo "</pre>";
/*



// ここで使うconfig.php内の変数。（必要に応じて var_dump で確認）
		// 中身：false
		// 中身：head タグ内で規定するメタタグとか
		// 中身：header タグ内で規定するタイトルタグとか

// ここで使う変数の宣言と初期値設定。


/* --------------------------------------------------------------------------------------- */



if($_SERVER["REQUEST_METHOD"] == "POST"){
    $pflag = true;
    
    // 【処理No.を記載】ログイン処理
    
}

// GETかPOSTにかかわらず必要な処理をここ以降で書く

?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<?= $head_common_tag ?>
	<script>
		// 以降JSの書き方例 基本的にPHPと同じ
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
	<title>出品者詳細</title>
</head>
<body>
	<?php //include '../inc/var_dump.php' ?>
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
				<h2>出品者詳細</h2>
			</header>
			<span class="err"><?= $err_msg ?></span>
			<br>
			<!-- title、h2以外、実際に記載するのはこっから下 -->
			
			
			
				
	
	出品者：<?=$name?>
	評価：<?=$hyouka?>点<br>
	
	
	
	
	主な取り扱い商品
	
	<table>
	
	<?php 
	
	foreach($row3 as $key => $val){
	    
	      echo "<tr>";
?>
<?php
	foreach( $val as $key2 => $val2){
	    
	      echo "<td>".$val2."</td>";
	    
	}
	
	echo "</tr>";
	}
	?>
	</table>
			
	レビュー		
			
	<table>
	<?php 
	foreach($row4 as $key => $val){
	    echo "<tr>";
	    echo  "<td>".$val["order_user_id"]."</td>";
	    echo  "<td>".$val["item_name"]."</td>";
	    echo  "<td>".$val["review_coment"]."</td>";
	    if($val["review_good"] == 0) {
	        echo "<td>👇 </tr>";
	    }else{
	        echo "<td>👆</tr>";
	    }

	    echo "</tr>";
	}
	?>	
	</table>			
			
<input type="button" onclick="location.href='./index.php'" value="TOPへ">
			<!-- 実際に記載するのはこっから上 -->
		</section>
	</main>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
</body>
</html>
