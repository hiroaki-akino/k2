<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成　：秋野浩朗（web1902)
概要　：全てのファイルに影響するDB接続の為のPDO処理
更新者：秋野
更新日：2020/2/11

【主な処理】
共通PDO処理

【ボツ処理】
個別のSQL関連の処理関数（複雑過ぎたためボツ扱い）ほか

----------------------------------------------------------------------------------------- */


/* ----- 共通処理 ----------------------------------------------------------------------- */

// 【ボツ処理】ボツ扱い。個人課題で使う。
// include 'sql_array.php';

// ここで使う変数（初期値つき）。


/* --------------------------------------------------------------------------------------- */



/* ----- PDO処理系 ----------------------------------------------------------------------- */

// 引数：1,作成したSQL文(String) 
//		2,SQL文の実行結果が空値でも OK かどうか（Boolean）（true : 空値 OK / false : 空値 NG）
//	   [3,以降 [省略可能] SQL 文で使用する引数（複数指定可能）]
// 処理：引数を基にしたPDO処理
// 戻値：値が存在する場合				...「該当のデータ（二重連想配列）」
//		値が存在しない場合で引数２がtrue ...「NULL」
//		上記以外の場合				   ...「false」　
// 備考：トランザクション処理適用（処理失敗時はロールバック）。静的プレースホルダー適用（勝手にDBサーバー側でバインド処理する）。明示的キャスト適用。各種エラー吐き出しあり。
//		関数使用時は結果を変数で受け取ること。（例：$sql_output_何たら_array = sql($sql_array["index1"],false,$val1,$val2) みたいな感じ。）
//		取得したデータの取扱がよくわからん時は取り敢えず $sql_output_何たら_array を二重foreaachすればデータ取れる。
function sql() {
	global $database_dsn,$database_user,$database_password,$config_db_no; // 個人課題の時は$sql_arrayを含める。
	$dsn		= $database_dsn[$config_db_no];			// DSNの設定（詳細はk2_config.phpとconfig.php参照)
	$user		= $database_user[$config_db_no][0];		// DBのユーザーの設定（本来はサイト上の会員種別で切り分けるが今回はレンタルサーバの都合上「0」で固定)
	$pass		= $database_password[$config_db_no];	// DBのPWの設定
	$arguments	= func_get_args();						// 可変長引数の取得
	$sql		= $arguments[0];						// １個目の引数（SQL文を入れるという運用）を取得
	$empty		= $arguments[1];						// ２個目の引数（空値リターンを許可するかどうかを入れるという運用）を取得
	$sql_type	= strtolower(mb_substr($sql,0,6));		// SQL文の初めの６文字を取得（なんとこれで全種類切り分けれる笑 select,insert,update,deleteは全て６文字）
	$val_array	= array();								// ３個目以降の引数（SQL文内で使用する引数を入れるという運用。複数可）を代入するための配列
	$param_id	= 1;									// バインド変数をカウントする為の変数
	$param_type	= "";									// ３個目以降の引数の型（バインド変数に明示的にキャストする為に使う。静的やから別にいらんけどなんとなく。）
	$db_result	= "";									// DBサーバーでの処理結果（成功か失敗か）
	$result		= array();								// 上記が成功時に、引数に指定したSQL文実行後の結果（SELECT文の場合は取得したデータを二重連想配列で返してくる）

	// SQL文で使う引数が指定されている場合の処理
	if(isset($arguments[2])){
		for($i=2;$i<count($arguments);$i++){
			$val_array[] = $arguments[$i];
		}
	}
	// 実際のPDOの処理
	try{
		$db = new PDO($dsn,$user,$pass,array(
			// 例外のスローを許可
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			// PDO側でのエミュレーションを停止（静的プレースホルダー使うから）
			PDO::ATTR_EMULATE_PREPARES => false
		));
		$db->exec("SET NAMES utf8");
		$db->setAttribute(PDO::ATTR_CASE,PDO::CASE_LOWER);

		// トランザクション（排他ロック）処理（select文以外は排他ロック）
		if($sql_type == "select"){

			// プリペアドステートメント
			$db_result = $db->prepare($sql);

			// DB側での明示的キャスト & バインド処理（静的プレースホルダーになってるハズ）
			if(isset($val_array)){
				for($i=0;$i<count($val_array);$i++){
					switch (gettype($val_array[$i])){
						case 'boolean':
							$param_type = PDO::PARAM_BOOL;
							break;
						case 'integer':
							$param_type = PDO::PARAM_INT;
							break;
						case 'double':
							$param_type = PDO::PARAM_STR;
							break;
						case 'string':
							$param_type = PDO::PARAM_STR;
							break;
						case 'NULL':
							$param_type = PDO::PARAM_NULL;
							break;
						default:
							$param_type = PDO::PARAM_STR;
					}
					$db_result->bindValue($param_id,$val_array[$i],$param_type);
					$param_id++;
				}
			}

			// SQL文の実行
			if(!$db_result->execute()){
				// echo "【SQL:err1】［sqlno］",$sqlno,"［内容］構文エラー(文法or記述ミス)［入力したSQL文］",$sql;
				echo "【SQL:err1】［内容］構文エラー(文法or記述ミス)［入力したSQL文］",$sql;
				return false;
			}
			$count = $db_result->rowCount();
		}else{
			// select文以外の時の処理
			// トランザクション（排他ロック）処理
			$db->beginTransaction();

			// プリペアドステートメント
			$db_result = $db->prepare($sql);

			// DB側での明示的キャスト & バインド処理（静的プレースホルダーになってるハズ）
			if(isset($val_array)){
				for($i=0;$i<count($val_array);$i++){
					switch (gettype($val_array[$i])){
						case 'boolean':
							$param_type = PDO::PARAM_BOOL;
							break;
						case 'integer':
							$param_type = PDO::PARAM_INT;
							break;
						case 'double':
							$param_type = PDO::PARAM_STR;
							break;
						case 'string':
							$param_type = PDO::PARAM_STR;
							break;
						case 'NULL':
							$param_type = PDO::PARAM_NULL;
							break;
						default:
							$param_type = PDO::PARAM_STR;
					}
					$db_result->bindValue($param_id,$val_array[$i],$param_type);
					$param_id++;
				}
			}

			// SQL文の実行
			$count = $db_result->execute();
			if(!$count){
				// echo "【SQL:err1】［sqlno］",$sqlno,"［内容］構文エラー(文法or記述ミス)［入力したSQL文］",$sql;
				echo "【SQL:err1】［sqlno］［内容］構文エラー(文法or記述ミス)［入力したSQL文］",$sql;
				// ここでのエラーはそもそもSQL文が実行されてない（ただの文法ミス）のでロールバック不要。
				return false;
			}
		}
		if($count !== FALSE){
			if($count == 0){
				if($sql_type != "select"){
					// トランザクション処理（ロールバックで処理を無効にする）
					$db->rollback();
				}
				$db = NULL;
				if($empty){
					// 空値OK な SQL文の場合の処理。NULLを返す。
					return NULL;
				}else{
					// 空値NG な SQL文で空値になった時の為のエラー処理。
					// echo "【SQL:err2】［sqlno］",$sqlno,"［内容］対象行なし［実行したSQL文］",$sql;
					echo "【SQL:err2】［内容］対象行なし［実行したSQL文］",$sql;
					return false;
				}
			}else{
				$rows = $db_result->fetchall(PDO::FETCH_ASSOC);

				// ほんまはココでいい感じに分割処理したいけど、
				// グループでは無理なので不本意やけど取得したデータ群をそのまま返す。
				$result = $rows;

				// 【ボツ処理】以下は複雑であった為、却下。個人課題の時に使う。
				// // SQL文の条件に合致したデータを上から「1行ごと」に$row に代入。
				// foreach($rows as $row){
				// 	// 各ページに合わせて関数処理。内容はsql_func() に記載。
				// 	// if($count == 1 && !is_array(sql_func($row,$sqlno,$val_array))){
				// 	// 	// データが１つの場合は単数形の変数の値を返す。
				// 	// 	$result = sql_func($row,$sqlno,$val_array);
				// 	// }else{
				// 	// 	// データが複数になる場合は配列を返す。
				// 	// 	$result[] = sql_func($row,$sqlno,$val_array);
				// 	// }
				// }

			}
		}else{
			//echo "【SQL:err3】［sqlno］",$sqlno,"［内容］構文エラー（実行時）［実行したSQL文］",$sql;
			echo "【SQL:err3】［内容］構文エラー（実行時）［実行したSQL文］",$sql;
			if($sql_type != "select"){
				// トランザクション処理（ロールバックで処理を無効にする）
				$db->rollback();
			}
			$db = NULL;
			return false;
		}
		// 全部成功した時の処理。
		if($sql_type != "select"){
			// トランザクション処理（処理成功時は結果にコミットする。ライザッｐ）
			$db->commit();
		}
		$db = NULL;
	}
	catch (Exception $e){
		echo "MSG:" .$e->getMessage()."<br>";
		echo "CODE:".$e->getCode()."<br>";
		echo "LINE:".$e->getLine()."<br>";
		if($sql_type != "select"){
			// トランザクション処理（ロールバックで処理を無効にする）
			$db->rollback();
		}
		$db = NULL;
		return false;
	}
	return $result;
}


/* --------------------------------------------------------------------------------------- */

// 【ボツ処理】以下の処理は複雑であった為、却下。個人課題の時に使う。
// function sql_func($row,$sqlno,$val_array){
// 	// DB から取得する値が複数になる場合に使用
// 	$result_array = array();	
// 	// DB から取得する値が単一の場合に使用
// 	$result = "";

// 	switch ($sqlno){
// 		case "index1":
// 			$result = $row["genre_name"];
// 			return $result;
// 			break;
// 		case "index2":
// 			$result = $row["count(item_id)"];
// 			return $result;
// 			break;
// 		case "index3":
// 			$result_array["item_id"]		= $row["item_id"];
// 			$result_array["item_name"]		= $row["item_name"];
// 			$result_array["item_seller_id"] = $row["item_seller_id"];
// 			$result_array["seller_name"]	= $row["seller_name"];
// 			$result_array["item_price"]		= $row["item_price"];
// 			$result_array["item_quantity"]	= $row["item_quantity"];
// 			return $result_array;
// 			break;
			
// 		// 以降は過去の処理。参考になる・・・・かも。
// 		// case "index1":
// 		// 	if($val == $row["user_id"]){
// 		// 		if(password_verify($val2,$row["user_pw"])){
// 		// 			$check = true;
// 		// 			if(password_needs_rehash($row["user_pw"],PASSWORD_DEFAULT)){
// 		// 				$sql_output_index_new_algo_pw = password_hash($val2,PASSWORD_DEFAULT);
// 		// 			}
// 		// 			break;
// 		// 		}
// 		// 	}
// 		// 	$check = false;
// 		// 	break;
// 		// case "form1":
// 		// 	$sql_output_form_sq_array[$row["sq_id"]] = $row["sq_q"];
// 		// 	break;
// 		// case "form2":
// 		// 	$sql_output_form_colomn_array[] = $row["column_name"];
// 		// 	break;
// 		// case "form3":
// 		// 	if(!$same){
// 		// 		if($val === $row["user_id"]){
// 		// 			$check = false;
// 		// 			$same  = true;
// 		// 			break;
// 		// 		}else{
// 		// 			$check = true;
// 		// 		}
// 		// 	}
// 		// 	break;
// 		// case "confilm1":
// 		// 	if(!$same){
// 		// 		if($val === $row["user_id"]){
// 		// 			$check = false;
// 		// 			$same  = true;
// 		// 			break;
// 		// 		}else{
// 		// 			$check = true;
// 		// 		}
// 		// 	}
// 		// 	break;
// 		// case "confilm2":
// 		// 	$check = true;
// 		// 	break;
// 		// case "repass2_1":
// 		// 	$check = true;
// 		// 	$sql_output_repass2_user_sq = $row["user_secret_q"];
// 		// 	break;
// 		// case "repass2_2":
// 		// 	if($val2 == $row["user_secret_a"]){
// 		// 		$check = true;
// 		// 		break;
// 		// 	}else{
// 		// 		$check = false;
// 		// 		break;
// 		// 	}
// 		// case "repass2_5":
// 		// 	$check = true;
// 		// 	$sql_output_repass2_user_miss = $row["user_miss"];
// 		// 	break;
// 		// case "main2_1":
// 		// 	$sql_output_main2_rtest_gr_id_array[]		= $row["gr_id"];
// 		// 	$sql_output_main2_rtest_gr_score_array[]	= $row["gr_score"];
// 		// 	$sql_output_main2_rtest_gr_time_array[]		= $row["gr_time"];
// 		// 	break;
// 		// case "main2_2":
// 		// 	$sql_output_main2_dtest_gr_id_array[]		= $row["gr_id"];
// 		// 	$sql_output_main2_dtest_gr_score_array[]	= $row["gr_score"];
// 		// 	$sql_output_main2_dtest_gr_time_array[]		= $row["gr_time"];
// 		// 	break;
// 		// case "main2_3":
// 		// 	$sql_output_main2_etest_gr_id_array[]		= $row["gr_id"];
// 		// 	$sql_output_main2_etest_gr_score_array[]	= $row["gr_score"];
// 		// 	$sql_output_main2_etest_gr_time_array[]		= $row["gr_time"];
// 		// 	break;
// 		// case "main4_1":
// 		// 	$sql_output_main4_qu_status_array[]		= $row["qu_status"];
// 		// 	$sql_output_main4_qu_no_array[]			= $row["qu_no"];
// 		// 	$sql_output_main4_qu_title_array[]		= $row["qu_title"];
// 		// 	$sql_output_main4_qu_cor_array[]		= $row["an_su_c/an_su_a"];
// 		// 	$sql_output_main4_qu_eva_array[]		= $row["an_evaluation/an_su_e"];
// 		// 	$sql_output_main4_qu_comment_array[]	= $row["qu_comment"];
// 		// 	break;
// 		// case "main5_1":
// 		// 	$sql_output_main5_colomn_array[] = $row["column_name"];
// 		// 	break;
// 		// case "main5_2":
// 		// 	foreach($sql_output_main5_colomn_array as $val){
// 		// 		$sql_output_main5_user_array[$val] = $row[$val];
// 		// 	}
// 		// 	break;
// 		// case "main6_1":
// 		// case "main6_2":
// 		// case "main6_3":
// 		// case "main6_4":
// 		// case "main6_5":
// 		// 	$sql_output_main6_qu_status_array[]		= $row["qu_status"];
// 		// 	$sql_output_main6_qu_del_array[]		= $row["qu_delete"];
// 		// 	$sql_output_main6_qu_id_array[]			= $row["qu_id"];
// 		// 	$sql_output_main6_qu_no_array[]			= $row["qu_no"];
// 		// 	$sql_output_main6_qu_title_array[]		= $row["qu_title"];
// 		// 	$sql_output_main6_qu_cor_array[]		= $row["an_su_c/an_su_a"];
// 		// 	$sql_output_main6_qu_eva_array[]		= $row["an_evaluation/an_su_e"];
// 		// 	$sql_output_main6_qu_comment_array[]	= $row["qu_comment"];
// 		// 	break;
// 		// case "main7_1":
// 		// 	$sql_output_main7_user_status_array[]	= $row["user_delete"];
// 		// 	$sql_output_main7_user_status_2_array[]	= $row["user_freeze"];
// 		// 	$sql_output_main7_user_id_array[]		= $row["user_id"];
// 		// 	$sql_output_main7_user_regist_array[]	= $row["user_registration"];
// 		// 	break;
// 		// case "main7_2":
// 		// 	$sql_output_main7_user_lastlog_array[] = $row["max(log_log)"];
// 		// 	$sql_output_main7_user_loginsu_array[] = $row["count(log_log)"];
// 		// 	break;
// 		// case "main7_3":
// 		// 	$sql_output_main7_user_qusum_array[] = $row["count(qu_no)"];
// 		// 	break;
// 		// case "main8_1":
// 		// 	$sql_output_main8_user_total = $row["count(user_id)"];
// 		// 	break;
// 		// case "main8_2":
// 		// 	$sql_output_main8_user_total_effective = $row["count(user_id)"];
// 		// 	break;
// 		// case "main8_3":
// 		// 	$sql_output_main8_qu_total = $row["count(qu_no)"];
// 		// 	break;
// 		// case "main8_4":
// 		// 	$sql_output_main8_qu_total_effective  = $row["count(qu_no)"];
// 		// 	break;
// 		// case "main8_5":
// 		// 	$sql_output_main8_log_total = $row["count(log_id)"];
// 		// 	break;
// 		// case "main8_6":
// 		// 	$sql_output_main8_log_total_user = $row["count(log_id)"];
// 		// 	break;
// 		// case "main8_7":
// 		// 	$sql_output_main8_log_total_guest = $row["count(log_id)"];
// 		// 	break;
// 		// case "dojo1":
// 		// case "dojo2":
// 		// 	//select qu_no,qu_title,qu_id,an_su_c/an_su_a,an_evaluation/an_su_e from k1_question,k1_answer where qu_no = an_no order by an_su_a asc ,an_no desc;
// 		// 	$sql_output_dojo_qu_no_array[]		= $row["qu_no"];
// 		// 	$sql_output_dojo_qu_title_array[]	= $row["qu_title"];
// 		// 	$sql_output_dojo_qu_id_array[]		= $row["qu_id"];
// 		// 	$sql_output_dojo_qu_cor_array[]		= $row["an_su_c/an_su_a"];
// 		// 	$sql_output_dojo_qu_eva_array[]		= $row["an_evaluation/an_su_e"];
// 		// 	break;
// 		// case "que_test1":
// 		// case "que_test2":
// 		// case "que_test3":
// 		// 	$sql_output_test_qno_array[] = $row["qu_no"];
// 		// 	$check = true;
// 		// 	break;
// 		// case "question1":
// 		// 	$sql_output_question_qu_array["qu_no"] 				= $row["qu_no"];
// 		// 	$sql_output_question_qu_array["qu_title"] 			= $row["qu_title"];
// 		// 	$sql_output_question_qu_array["qu_id"] 				= $row["qu_id"];
// 		// 	$sql_output_question_qu_array["qu_question"] 		= $row["qu_question"];
// 		// 	$sql_output_question_qu_array["qu_answer_1"] 		= $row["qu_answer_1"];
// 		// 	$sql_output_question_qu_array["qu_answer_2"] 		= $row["qu_answer_2"];
// 		// 	$sql_output_question_qu_array["qu_answer_3"] 		= $row["qu_answer_3"];
// 		// 	$sql_output_question_qu_array["qu_answer_4"] 		= $row["qu_answer_4"];
// 		// 	$sql_output_question_qu_array["qu_answer_correct"] 	= $row["qu_answer_correct"];
// 		// 	$sql_output_question_qu_array["qu_time_limit"] 		= $row["qu_time_limit"];
// 		// 	$sql_output_question_qu_array["qu_explanation"] 	= $row["qu_explanation"];
// 		// 	$check = true;
// 		// 	break;
// 		// case "answer1":
// 		// 	if($val == $row["qu_id"]){
// 		// 		$check = false;
// 		// 		break;
// 		// 	}
// 		// 	$check = true;
// 		// 	break;
// 		// case "ans_test1":
// 		// 	$sql_output_ans_test_highscore = $row["gr_score"];
// 		// 	if($row["gr_score"] == ""){
// 		// 		$sql_output_ans_test_status = "first_time";
// 		// 		$check = true;
// 		// 		break;
// 		// 	}
// 		// 	if($val > $row["gr_score"]){
// 		// 		$sql_output_ans_test_status = "new_record";
// 		// 		$check = true;
// 		// 	}else{
// 		// 		if($val == $row["gr_score"]){
// 		// 			$sql_output_ans_test_status = "same_record";
// 		// 			$check = true;
// 		// 		}else{
// 		// 			$check = false;
// 		// 		}
// 		// 	}
// 		// 	break;
// 		// case "que_create1":
// 		// 	$sql_output_form_colomn_array[] = $row["column_name"];
// 		// 	break;
// 		// case "que_create2":
// 		// 	$sql_output_que_create_qu_array["qu_no"]				= $row["qu_no"];
// 		// 	$sql_output_que_create_qu_array["qu_title"]				= $row["qu_title"];
// 		// 	$sql_output_que_create_qu_array["qu_id"]				= $row["qu_id"];
// 		// 	$sql_output_que_create_qu_array["qu_question"]			= $row["qu_question"];
// 		// 	$sql_output_que_create_qu_array["qu_answer_1"]			= $row["qu_answer_1"];
// 		// 	$sql_output_que_create_qu_array["qu_answer_2"]			= $row["qu_answer_2"];
// 		// 	$sql_output_que_create_qu_array["qu_answer_3"]			= $row["qu_answer_3"];
// 		// 	$sql_output_que_create_qu_array["qu_answer_4"]			= $row["qu_answer_4"];
// 		// 	$sql_output_que_create_qu_array["qu_answer_correct"]	= $row["qu_answer_correct"];
// 		// 	$sql_output_que_create_qu_array["qu_time_limit"]		= $row["qu_time_limit"];
// 		// 	$sql_output_que_create_qu_array["qu_explanation"]		= $row["qu_explanation"];
// 		// 	break;
// 		default:
// 			$result = true;
// 	}
// 	return true;
// }

?>