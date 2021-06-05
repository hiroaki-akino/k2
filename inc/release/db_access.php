<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成　：秋野浩朗（web1902)
概要　：全てのファイルに影響するDB接続の為のPDO処理
更新者：秋野
更新日：2020/2/4

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
	$sql_type	= mb_substr($sql,0,6);					// SQL文の初めの６文字を取得（なんとこれで全種類切り分けれる笑 select,insert,update,deleteは全て６文字）
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

		//【ボツ処理】複雑になってしまうのでグループ課題では使わない。個人課題で使う。
		// sql文の選択。候補はinclude してきた sql_array.php の $sql_array 参照。
		// $sql = $sql_array[$sqlno];

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
				// echo "【SQL:err1】［内容］構文エラー(文法or記述ミス)［入力したSQL文］",$sql;
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
			$count  = $db_result->execute();
			if(!$count){
				// echo "【SQL:err1】［sqlno］",$sqlno,"［内容］構文エラー(文法or記述ミス)［入力したSQL文］",$sql;
				// echo "【SQL:err1】［sqlno］［内容］構文エラー(文法or記述ミス)［入力したSQL文］",$sql;
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
					// echo "【SQL:err2】［内容］対象行なし［実行したSQL文］",$sql;
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
			// echo "【SQL:err3】［sqlno］",$sqlno,"［内容］構文エラー（実行時）［実行したSQL文］",$sql;
			// echo "【SQL:err3】［内容］構文エラー（実行時）［実行したSQL文］",$sql;
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
		// echo "MSG:" .$e->getMessage()."<br>";
		// echo "CODE:".$e->getCode()."<br>";
		// echo "LINE:".$e->getLine()."<br>";
		if($sql_type != "select"){
			// トランザクション処理（ロールバックで処理を無効にする）
			$db->rollback();
		}
		$db = NULL;
		return false;
	}
	return $result;
}

?>