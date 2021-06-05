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


// ここで使うSQL文の一覧表示と配列変数への設定。
// 注意：変数の値などを使用してSQL文を作成したい時は「?」に置き換えて表示する。
// 注意：SQL() の引数（SQL文用の引数は３つ目以降に記載）は「?」に指定した値の順に記載する。
$sql_array = array(
		"index1"	=> "select * from k2g1_order where order_user_id = ?"
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

/*
$sql = "
select
k2g1_order.order_id
,k2g1_order.order_item_id
,k2g1_order.order_quantity
,k2g1_order.order_evaluated
,k2g1_order.order_shipped
,k2g1_order.order_time
,k2g1_item.item_name
,k2g1_item.item_seller_id
,k2g1_item.item_price
,k2g1_genre.genre_name
,k2g1_condition.condition_rank
,k2g1_seller.seller_name
from k2g1_order, k2g1_item , k2g1_genre , k2g1_seller ,k2g1_condition
where k2g1_order.order_item_id = k2g1_item.item_id
and k2g1_item.item_genre_id = k2g1_genre.genre_id
and k2g1_seller.seller_id = k2g1_item.item_seller_id
and k2g1_condition.condition_id = k2g1_item.item_condition_id
and k2g1_order.order_user_id = ?
order by k2g1_order.order_time desc
";

var_dump($_SESSION);
var_dump($_SESSION ["user"]["id"]);
$db_arr = sql($sql,true,$_SESSION["user"]["id"]);


$sql2= "
select * from k2g1_buyer where buyer_id = ?
";

$user_arr = sql($sql2,true,$_SESSION["user"]["id"]);

echo "<pre>";
var_dump($user_arr);
echo "</pre>";
*/
if($_SERVER["REQUEST_METHOD"] == "POST"){
	echo "<hr>";
	var_dump($_POST);
	
	if($_POST["btn"] == '出品者評価'){
		echo "出品者評価処理";
		$_SESSION['evaluate_form']['order_id'] = $_POST["order_id"];
		//$_SESSION['evaluate_form']['item_id'] = $_POST["item_id"];
		//$_SESSION['evaluate_form']['seller_id'] = $_POST["seller_id"];
		header("Location: evaluate_form.php");
	}
	
	elseif($_POST["btn"] == '更新する'){
		echo "更新する処理";
		
		
		$sql3 ="
UPDATE `k2g1_buyer`
SET
`buyer_name` = ?,
`buyer_high_postalcode` = ?,
`buyer_low_postalcode` = ?,
`buyer_address_1` = ?,
`buyer_address_2` = ?,
`buyer_address_3` = ?
 WHERE `k2g1_buyer`.`buyer_id` = ?
";
		
		$db_up = sql($sql3,true,
					$_POST["buyer_name"],
					$_POST["buyer_high_postalcode"],
					$_POST["buyer_low_postalcode"],
					$_POST["buyer_address_1"],
					$_POST["buyer_address_2"],
					$_POST["buyer_address_3"],
					$_SESSION["user"]["id"]
				);
		
	}
	
	
}
$sql = "
select
k2g1_order.order_id
,k2g1_order.order_item_id
,k2g1_order.order_quantity
,k2g1_order.order_evaluated
,k2g1_order.order_shipped
,k2g1_order.order_time
,k2g1_item.item_name
,k2g1_item.item_seller_id
,k2g1_item.item_price
,k2g1_genre.genre_name
,k2g1_condition.condition_rank
,k2g1_seller.seller_name
from k2g1_order, k2g1_item , k2g1_genre , k2g1_seller ,k2g1_condition
where k2g1_order.order_item_id = k2g1_item.item_id
and k2g1_item.item_genre_id = k2g1_genre.genre_id
and k2g1_seller.seller_id = k2g1_item.item_seller_id
and k2g1_condition.condition_id = k2g1_item.item_condition_id
and k2g1_order.order_user_id = ?
order by k2g1_order.order_time desc
";

var_dump($_SESSION);
var_dump($_SESSION ["user"]["id"]);
$db_arr = sql($sql,true,$_SESSION["user"]["id"]);


$sql2= "
select * from k2g1_buyer where buyer_id = ?
";

$user_arr = sql($sql2,true,$_SESSION["user"]["id"]);

echo "<pre>";
var_dump($user_arr);
echo "</pre>";


?>

<!DOCTYPE html>
<html lang="ja">
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#">
	<?= $head_common_tag ?>
    <title></title>
    <style>
    	body > header,
		main > header{
			margin    : auto;
			max-width : 800px;
		}
    
    	.buyer_mypage_div1{
    		font-weight: bold;
    	}
    	section{
    		display: none;
    	}
    	#buyer_mypage_section1a{
    		display: block;
    	}
    </style>
    
    <script>
	function check(){
		if(window.confirm('送信してよろしいですか？')){ // 確認ダイアログを表示
			return true; // 「OK」時は送信を実行
		}else{ // 「キャンセル」時の処理
			window.alert('キャンセルされました'); // 警告ダイアログを表示
			return false; // 送信を中止
		}
	}
	function tab(character){
		var forEach = Array.prototype.forEach;
		var section = document.getElementsByTagName('section');
		forEach.call(section, function (element){
			element.style.display = "none";
		});
		var target = document.getElementById("buyer_mypage_section1" + character);
		target.style.display = "block";
	}
	</script>
	
    
</head>
    
    
    
    
    
    <body>
    	<?php include '../inc/var_dump.php' ?>
    	<header>
		<?= $header_common_tag ?>
		</header>
    	
	    <!------ タブメニュー ------>
	<nav class="buyer_mypage_nav1">
		<input type="radio" id="radio1a" class="radio1" name="radio1" onClick="tab('a')" checked>
		<label class="label1" for="radio1a">購入履歴</label>
		<input type="radio" id="radio1b" class="radio1" name="radio1" onClick="tab('b')">
		<label class="label1" for="radio1b">会員情報</label>
	</nav>	
	<section id="buyer_mypage_section1a" class="buyer_mypage_section1">
	<div style="height:100%; width:80%; overflow:scroll;">
    
    	<table border =1 >
	    	<tr>
	    		<th>注文番号</th>
		    	<th>ジャンル</th>
		    	<th>商品ID</th>
		    	<th>商品名</th>
		    	<th>出品者ＩＤ</th>
		    	<th>出品者名</th>
		    	<th>状態</th>
		    	<th>個数</th>
		    	<th>合計金額</th>
		    	<th>出荷状況</th>
		    	<th>評価</th>
		    	<th>購入日時</th>  
		    </tr>	
	    
    	<?php 
    	
    	foreach($db_arr as $key => $val){
    		
    		echo "<tr><form action='buyer_mypage.php' method='POST'>";
    		
    		echo "<td>";
			echo $db_arr[$key]["order_id"];
			echo "<input type ='hidden' name ='order_id' value ='".$db_arr[$key]['order_id']."'>";
    		echo "</td>";
		
			echo "<td>";
			echo $db_arr[$key]["genre_name"];
			echo "</td>";
		
			echo "<td>";
			echo $db_arr[$key]["order_item_id"];
			echo "<input type ='hidden' name ='item_id' value ='".$db_arr[$key]['order_item_id']."'>";
			
			echo "</td>";
			
			echo "<td>";
			echo $db_arr[$key]["item_name"];
			echo "</td>";
			
			echo "<td>";
			echo $db_arr[$key]["item_seller_id"];
			echo "<input type ='hidden' name ='seller_id' value ='".$db_arr[$key]['item_seller_id']."'>";
			echo "</td>";
			
			echo "<td>";
			echo $db_arr[$key]["seller_name"];
			echo "</td>";
			
			echo "<td>";
			echo $db_arr[$key]["condition_rank"];
			echo "</td>";
			
			echo "<td>";
			echo $db_arr[$key]["order_quantity"];
			echo "</td>";
			
			echo "<td>";
			echo $db_arr[$key]["item_price"] * $db_arr[$key]["order_quantity"];
			echo "</td>";
			
			echo "<td>";
			
			
			if($db_arr[$key]["order_shipped"] == 0){
				echo "未出荷";
			}else{
				echo "出荷済み";
			}
			echo "</td>";
			
			echo "<td>";
			if($db_arr[$key]["order_evaluated"] == 0){
				//評価できるボタン
				echo "<input type ='submit' value ='出品者評価' name = 'btn'>";
				
				
			}else{
				//評価済み
				echo "評価済";
			}
			
			
			
		
			echo "</td>";
			
			echo "<td>";
			echo $db_arr[$key]["order_time"];
			echo "</td>";
    		echo "</form></tr>";
    	}	
    	
    	
    	
    	?>
    
    	</table>
    	</div>
    </section>
<script>


    
function func_change(){

	var bhp = document.getElementById('buyer_high_postalcode').value;
	document.getElementById('d_buyer_high_postalcode').innerHTML = bhp;
	document.getElementById('hid_buyer_high_postalcode').value = bhp;

	var blp =  document.getElementById('buyer_low_postalcode').value;
	document.getElementById('d_buyer_low_postalcode').innerHTML = blp;
	document.getElementById('hid_buyer_low_postalcode').value = blp;
	
	var ba1 =  document.getElementById('buyer_address_1').value;
	document.getElementById('d_buyer_address_1').innerHTML = ba1;
	document.getElementById('hid_buyer_address_1').value = ba1;

	var ba2 =  document.getElementById('buyer_address_2').value;
	document.getElementById('d_buyer_address_2').innerHTML = ba2;
	document.getElementById('hid_buyer_address_2').value = ba2;

	var ba3 =  document.getElementById('buyer_address_3').value;
	document.getElementById('d_buyer_address_3').innerHTML = ba3;
	document.getElementById('hid_buyer_address_3').value = ba3;

	var bn =  document.getElementById('buyer_name').value;
	document.getElementById('d_buyer_name').innerHTML = bn;
	document.getElementById('hid_buyer_name').value = bn;
	



	var btn = "<input type='submit' value ='更新する' name = 'btn'>";
	
	document.getElementById('d_btn').innerHTML =btn;

}

</script>
    
<section id="buyer_mypage_section1b" class="buyer_mypage_section1">
    <form action='buyer_mypage.php' method='POST'>
    
    ID:<?=$user_arr[0]["buyer_id"];?>
    
    〒:<div id ="d_buyer_high_postalcode" class="buyer_mypage_div1"><input type ="text" value="<?=$user_arr[0]['buyer_high_postalcode']; ?>" id ="buyer_high_postalcode"></div>
    <div id ="d_buyer_low_postalcode" class="buyer_mypage_div1"><input type ="text" value="<?=$user_arr[0]['buyer_low_postalcode']; ?>" id ="buyer_low_postalcode"></div>
     <div id ="d_buyer_address_1" class="buyer_mypage_div1"> <input type ="text" value="<?=$user_arr[0]['buyer_address_1']; ?>" id ="buyer_address_1"></div>
      <div id ="d_buyer_address_2" class="buyer_mypage_div1">  <input type ="text" value="<?=$user_arr[0]['buyer_address_2']; ?>" id ="buyer_address_2"></div>
      <div id ="d_buyer_address_3" class="buyer_mypage_div1">    <input type ="text" value="<?=$user_arr[0]['buyer_address_3']; ?>" id ="buyer_address_3"></div>
         <div id ="d_buyer_name" class="buyer_mypage_div1">   <input type ="text" value="<?=$user_arr[0]['buyer_name']; ?>" id ="buyer_name"></div>
    
    
    
    
    
   <div id ="d_btn"> <input type ="button" value ="編集する" onclick="func_change()" id ="btn"></div>
   
   <input type='hidden' value ="" id ="hid_buyer_high_postalcode" name ="buyer_high_postalcode">
   <input type='hidden' value ="" id ="hid_buyer_low_postalcode" name ="buyer_low_postalcode">
   <input type='hidden' value ="" id ="hid_buyer_address_1" name ="buyer_address_1">
   <input type='hidden' value ="" id ="hid_buyer_address_2" name ="buyer_address_2">
   <input type='hidden' value ="" id ="hid_buyer_address_3" name ="buyer_address_3">
   <input type='hidden' value ="" id ="hid_buyer_name" name ="buyer_name">
    </form>
</section>
<footer>
		<?= $footer_common_tag ?>
	</footer>
    </body>
</html>