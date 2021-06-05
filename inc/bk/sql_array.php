<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成：秋野浩朗（web1902)
概要：【ボツ処理】DB接続の為のPDO処理（db_access.php）で使用する「SQL 文」の一覧

【主な処理】
【ボツ処理】共通PDO処理（db_access.php）で使用する$sql_array の宣言（複雑すぎた？ためボツ扱い）
【ボツ処理】個別のSQL関連の処理関数（複雑すぎた？ためボツ扱い）

----------------------------------------------------------------------------------------- */


/* ----- 共通処理 ----------------------------------------------------------------------- */

// 特になし。

/* --------------------------------------------------------------------------------------- */


// 【ボツ処理】
// 使用するSQL文を各ページの名前をキーにして連想配列に格納。
// 【！超重要！】引数を使用してSQL文を作成する時は「?」を代入する。
// 【！超重要！】SQL() の引数（SQL文用の引数は３つ目以降に記載）は「?」に指定した値の順に記載する。

/* 【！超重要！】書き方と使い方の例
例１			：引数$val, $val2 を基にSQLからDBを参照したい場合
作成したいSQL文	：select * from k2g1_item where item_name = ${val} and item_genre_id = ${val2};
下記に記載する文：select * from k2g1_item where item_name = ? and item_genre_id = ? ;
sql()の記載方法	：sql("select","index1",true,$val,$val2);

例２			：引数なしでSQLからDBを参照したい場合
作成したいSQL文	：select * from k2g1_item;
下記に記載する文：select * from k2g1_item;
sql()の記載方法	：sql("select","index1",true);
備考			：sql()の引数は４つ目以降は省略可能。上位３つは必須。

*/

$sql_array = array(
	"index1"	=> "select genre_name from k2g1_genre",
	"index2"	=> "select count(item_id) from k2g1_item where item_quantity != 0 and item_deleted = 0",
	"index3"	=> "select item_id,item_name,item_seller_id,seller_name,item_price,item_quantity 
					from k2g1_seller,k2g1_item,k2g1_genre 
					where seller_id = item_seller_id and item_genre_id = genre_id 
					and genre_name = ? 
					and item_quantity != 0 and item_deleted = 0",
	
	// 使えるかもしれない過去の SQL 文
	// "index1"		=> "select user_id,user_pw from k1_user where user_id = '${val}' and user_delete = 0 and user_freeze = 0",
	// "index2"		=> "update k1_user set user_pw = '${val2}' where user_id = '${val}'",
	// "index3"		=> "insert into k1_log set log_id = '${val}' , log_log = now()",
	// "form2"		=> "select column_name from information_schema.columns where table_schema = '${dbname}' and table_name = 'k1_user'",
	// "main2_1"	=> "select gr_id,gr_score,gr_time from k1_grades where gr_type = 0 and gr_score IS NOT NULL order by gr_score desc , gr_time desc limit ${val}",
	// "main2_2"	=> "select gr_id,gr_score,gr_time from k1_grades where gr_type = 1 and gr_score != 'NULL' order by gr_score desc , gr_time desc limit ${val}",
	// "main2_3"	=> "select gr_id,gr_score,gr_time from k1_grades where gr_type = 2 and gr_score != 'NULL' order by gr_score desc , gr_time desc limit ${val}",
	// "main4_1"	=> "select qu_status,qu_no,qu_title,qu_id,an_su_c/an_su_a,an_evaluation/an_su_e,qu_comment from k1_question,k1_answer where qu_no = an_no and qu_id = '${val}' and qu_delete = 0 order by qu_status asc,qu_no desc",
	// "answer2"	=> "update k1_answer set an_evaluation = an_evaluation + '${val}' , an_su_e = an_su_e + 1 where an_no = '${val2}' ",
);


?>