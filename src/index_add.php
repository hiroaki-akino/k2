<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成者：秋野浩朗（web1902)
概要　：トップページでの会員専用コンテンツ（会員ならインクルード追加する）
更新日：2020/2/7

----------------------------------------------------------------------------------------- */



/* ----- 共通処理 ----------------------------------------------------------------------- */

// ここで使う変数の宣言と初期値設定。


// ここで使うSQL文の一覧表示と配列変数への設定。
$sql_2_array = array(
	// 購入履歴有無確認のSQL文	
	"index_add1"	=> "select order_id from k2g1_order where order_user_id = ? " ,
	// 購入履歴がない場合のおススメ商品チョイス
	// （状態高い順、評価高い順、新着順で３件表示）
	"index_add2"	=> "SELECT item_id,item_name,item_seller_id,seller_name,seller_office_name,
						format(item_price,0) as 'item_price',item_quantity,item_image_path,condition_rank,
						(select ( sum(review_good) / (sum(review_good) + sum(review_bad)) )
						from k2g1_item s_item
						left join k2g1_order on item_id = order_item_id 
						left join k2g1_review k2g1_review on order_id = review_order_id 
						where m_item.item_seller_id = s_item.item_seller_id
						group by s_item.item_seller_id
						) as 'evaluation'
						from k2g1_item m_item
						left join k2g1_seller on item_seller_id = seller_id 
						left join k2g1_genre on item_genre_id = genre_id 
						left join k2g1_order on item_id = order_item_id
						left join k2g1_condition on item_condition_id = condition_id
						where item_quantity != 0 and item_deleted = 0
						order by item_condition_id asc,evaluation desc , item_id desc
						limit 3",
	// 購入履歴がある場合のおススメ商品チョイス
	//（過去購入履歴の内、最多購入ジャンルの中から、購入平均価格以下のものを、状態が良い順、新着順で３件表示）
	"index_add3"	=> "SELECT item_id,item_name,item_seller_id,seller_name,seller_office_name,
						format(item_price,0) as 'item_price',item_quantity,item_image_path,condition_rank,
						(select ( sum(review_good) / (sum(review_good) + sum(review_bad)) )
							from k2g1_item s_item
							left join k2g1_order on item_id = order_item_id 
							left join k2g1_review k2g1_review on order_id = review_order_id 
							where m_item.item_seller_id = s_item.item_seller_id
							group by s_item.item_seller_id
						) as 'evaluation'
						from k2g1_item m_item
						left join k2g1_seller on item_seller_id = seller_id 
						left join k2g1_genre on item_genre_id = genre_id 
						left join k2g1_order on item_id = order_item_id
						left join k2g1_condition on item_condition_id = condition_id
						where item_genre_id = (
							select item_genre_id 
							from (select item_genre_id,count(item_genre_id) as cnt,
									sum((order_quantity * item_price)) as sum_price
									from k2g1_item,k2g1_order 
									where item_id = order_item_id and order_user_id = ?
									group by item_genre_id
									order by cnt desc, sum_price desc limit 1
								) w 
						)
						and item_price <= (
							select sum((item_price * order_quantity)) / sum(order_quantity)
							from k2g1_item,k2g1_order
							where item_id = order_item_id and order_user_id = ?
							group by order_user_id
						)
						and item_quantity != 0 and item_deleted = 0
						order by item_condition_id asc,item_id desc
						limit 3"
);
$order_array = array();

/* --------------------------------------------------------------------------------------- */



$user_name = $_SESSION["user"]["name"];
// 購入履歴があるかどうかの判定
$order_array = sql($sql_2_array["index_add1"],false,$_SESSION["user"]["id"]);
if(empty($order_array)){
	// 購入履歴がない場合
	$sql_output_item_user_array = sql($sql_2_array["index_add2"],true);
}else{
	// 購入履歴がある場合
	$sql_output_item_user_array = sql($sql_2_array["index_add3"],false,$_SESSION["user"]["id"],$_SESSION["user"]["id"]);
	if(count($sql_output_item_user_array) < 3 ){
		$w = 3 - count($sql_output_item_user_array);
		// echo $w;
		switch($w){
			case 3 :
				array_push($sql_output_item_user_array,sql($sql_2_array["index_add2"],true)[0]);
				array_push($sql_output_item_user_array,sql($sql_2_array["index_add2"],true)[1]);
				array_push($sql_output_item_user_array,sql($sql_2_array["index_add2"],true)[2]);
				break;
			case 2 :
				array_push($sql_output_item_user_array,sql($sql_2_array["index_add2"],true)[0]);
				array_push($sql_output_item_user_array,sql($sql_2_array["index_add2"],true)[1]);
				break;
			case 1 :
				array_push($sql_output_item_user_array,sql($sql_2_array["index_add2"],true)[0]);
				break;
		}
	}
}
// echo "<pre>";
// var_dump($sql_output_item_user_array);
// echo "</pre>";
//$sql_output_item_user_array = sql($sql_2_array["index_add0"],false);

?>

<section>
	<header>
		<h3><?= $user_name ?> 様へおススメの商品</h3>
	</header>
	<div class="div1_flex_parent res_row">
		<?php
			for($i=0;$i<count($sql_output_item_user_array);$i++){
				echo "<section id=\"item_section_$i\" name=\"item_section\" class=\"section1_flex_children\">";
				// 画像パスの設定
				echo "<div class=\"img_div\"><img id=\"item_image_path_$i\" src=\"{$sql_output_item_user_array[$i]["item_image_path"]}\" alt=\"{$sql_output_item_user_array[$i]["item_name"]}の画像\"
						width=\"100%\" height=\"100%\" ></div>";
				// 商品ページに遷移する為のフォーム
				echo "<form id=\"item_name_form2_$i\" action=\"{$_SERVER["SCRIPT_NAME"]}\" method=\"POST\">";
				// 商品名の設定
				echo "<h4 id=\"item_name_$i\" onclick=\"to_item2({$i})\" class=\"like_a\">";
				echo $sql_output_item_user_array[$i]["item_name"],"</h4>"; 
				// 商品ID（hidden）の設定
				echo create_input("hidden","item_id_$i","item_id","","20",$sql_output_item_user_array[$i]["item_id"],"","","");
				echo "</form>";
				// 出品者詳細ページに遷移する為のフォーム
				echo "<form id=\"seller_name_form2_$i\" action=\"{$_SERVER["SCRIPT_NAME"]}\" method=\"POST\">";
				// 出品者名の設定
				echo "<p id=\"seller_name_$i\" onclick=\"to_seller2({$i})\" class=\"like_a\">";
				if($sql_output_item_user_array[$i]["seller_office_name"] != ""){
					echo $sql_output_item_user_array[$i]["seller_office_name"],"</p>";
				}else{
					echo $sql_output_item_user_array[$i]["seller_name"],"</p>";
				}
				// 出品者ID（hidden）の設定
				echo create_input("hidden","item_seller_id_$i","item_seller_id","","20",$sql_output_item_user_array[$i]["item_seller_id"],"","","");
				echo "</form>";
				// 出品者への評価の設定
				if(empty($sql_output_item_user_array[$i]["evaluation"])){
					echo "<p>出品者への評価：<span id=\"evaluation_$i\">未評価</span></p>";
				}else{
					echo "<p>出品者への評価：<p id=\"evaluation_$i\">",round(($sql_output_item_user_array[$i]["evaluation"] * 100),2) ,"</span>点</p>"; 
				}
				// 商品の価格の設定
				echo "<p>価格：￥<span id=\"user_item_price_$i\">",$sql_output_item_user_array[$i]["item_price"],"</span></p>";
				// 商品の状態の設定
				echo "<p>状態：<span id=\"user_item_condition_$i\">",$sql_output_item_user_array[$i]["condition_rank"],"</span></p>";
				// 商品の在庫数の設定
				echo "<p>残り：<span id=\"user_item_quantity_$i\">",$sql_output_item_user_array[$i]["item_quantity"],"</span>個</p>";
				echo "</section>";
			}
		?>
	</div>
</section>