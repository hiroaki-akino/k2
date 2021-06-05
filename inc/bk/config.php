<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成：秋野浩朗（web1902)
概要：全てのファイルに影響する共通処理用のファイル

【主な処理】
k2_config.phpの読み込み
session_start()
各種var_dump(検証用なので本番時はココをコメントアウトする)
共通使用変数の宣言
各種エラー内容の配列宣言(JSで使うエラーは除く)
index.php以外で使う直接アクセスの制御関数
共通headタグ変数作成(各ページの使いたいとこでechoする)
共通headerタグ変数作成(各ページの使いたいとこでechoする)
共通footerタグ変数作成(各ページの使いたいとこでechoする)
共通inputタグ(POST時再表示)変数作成(各ページの使いたいとこでechoする、radio除く)
共通boxタグ変数作成(各ページの使いたいとこでechoする)
【要修正】共通PDO処理(SQLインジェクション万歳、静的プレースホルダーつける)
個別のSQL関連の処理関数

----------------------------------------------------------------------------------------- */


/* ----- 共通処理 ----------------------------------------------------------------------- */

// k1_config.phpのパスとファイル名を変数にしとく。
$config_path = "../";
$config_file = "k2_config.php";

// k1_config.phpをインクルード。
include($config_path.$config_file);

// セッションスタート。
session_start();

// 【要削除】検証用
var_dump($_COOKIE);
var_dump($_SESSION);
var_dump($_POST);
var_dump($_GET);

// 共通で使う変数（初期値つき）。
// 使用するファイルが限定される場合は変数のミドルネームにファイル名を入れてる。
$pflag									= false;
$err_msg								= "";
$head_common_tag						= "";
$header_common_tag						= "";
$footer_common_tag						= "";
$sql_output_index_genre_array			= array();
$sql_output_index_item_sum				= "";
$sql_output_index_genre_array			= array();
$sql_output_index_item_sum				= "";
$sql_output_index_item_id_array			= array();
$sql_output_index_item_name_array		= array();
$sql_output_index_item_seller_id_array	= array();
$sql_output_index_seller_name_array		= array();
$sql_output_index_item_price_array		= array();
$sql_output_index_item_quantity_array	= array();
// $sql_output_index_new_algo_pw			= "";
// $sql_output_form_sq_array				= array();
// $sql_output_form_colomn_array			= array();
// $sql_output_repass2_user_sq 			= "";
// $sql_output_repass2_user_miss			= "";
// $sql_output_main2_rtest_gr_id_array		= array();
// $sql_output_main2_rtest_gr_score_array	= array();
// $sql_output_main2_rtest_gr_time_array	= array();
// $sql_output_main2_dtest_gr_id_array		= array();
// $sql_output_main2_dtest_gr_score_array	= array();
// $sql_output_main2_dtest_gr_time_array	= array();
// $sql_output_main2_etest_gr_id_array		= array();
// $sql_output_main2_etest_gr_score_array	= array();
// $sql_output_main2_etest_gr_time_array	= array();
// $sql_output_main4_qu_status_array		= array();
// $sql_output_main4_qu_no_array			= array();
// $sql_output_main4_qu_title_array		= array();
// $sql_output_main4_qu_cor_array			= array();
// $sql_output_main4_qu_eva_array			= array();
// $sql_output_main4_colomn_array			= array();
// $sql_output_main4_qu_comment_array		= array();
// $sql_output_main5_user_array			= array();
// $sql_output_main5_user_total			= "";
// $sql_output_main5_user_total_effective	= "";
// $sql_output_main6_qu_status_array		= array();
// $sql_output_main6_qu_del_array			= array();
// $sql_output_main6_qu_id_array			= array();
// $sql_output_main6_qu_no_array			= array();
// $sql_output_main6_qu_title_array		= array();
// $sql_output_main6_qu_cor_array			= array();
// $sql_output_main6_qu_eva_array			= array();
// $sql_output_main6_qu_comment_array		= array();
// $sql_output_main7_user_status_array		= array();
// $sql_output_main7_user_status_2_array	= array();
// $sql_output_main7_user_id_array			= array();
// $sql_output_main7_user_regist_array		= array();
// $sql_output_main7_user_lastlog_array	= array();
// $sql_output_main7_user_loginsu_array	= array();
// $sql_output_main7_user_qusum_array		= array();
// $sql_output_main8_user_total			= "";
// $sql_output_main8_user_total_effective	= "";
// $sql_output_main8_qu_total				= "";
// $sql_output_main8_qu_total_effective	= "";
// $sql_output_main8_log_total				= "";
// $sql_output_main8_log_total_user		= "";
// $sql_output_main8_log_total_guest		= "";
// $sql_output_dojo_qu_title_array			= array();
// $sql_output_dojo_qu_id_array			= array();
// $sql_output_dojo_qu_cor_array			= array();
// $sql_output_dojo_qu_eva_array			= array();
// $sql_output_test_qno_array				= array();
// $sql_output_question_qu_array			= array();
// $sql_output_ans_test_status				= "false";
// $sql_output_ans_test_highscore			= "";
// $sql_output_que_create_qu_array			= array();

// 各種ページで使用するエラー一覧。適当に使いまわす。
// JS処理によるエラーは各ファイルのJSにしかないのであしからず。
$err_array = array(
	"all"		=> "<i class=\"fas fa-exclamation-triangle\"></i> ",
	"index0"	=> "当サイトに直接ログインがあった為、トップ画面に戻ります。",
	"index1"	=> "IDとPWの両方を入力してください。",
	"index2"	=> "IDもしくはPWが間違っています。",
	"index3"	=> "連続して一定回数の誤入力があった為、暫くログインできません。",
	"repass1_1"	=> "そのようなIDは存在しません。",
	"repass1_2"	=> "連続して一定回数の誤入力があった為、暫くログインできません。",
	"repass1_3"	=> "IDを入力して下さい。",
	"repass2_1"	=> "秘密の質問の答えが違います。",
	"repass2_2"	=> "連続して一定回数の誤入力があった為、暫くログインできません。",
	"repass2_3"	=> "秘密の質問の答えを入力して下さい。",
	"form1"		=> "IDを半角英数字で入力して下さい。",
	"form2"		=> "該当IDは既に他のユーザが使用済みです。",
	"confilm1"	=> "該当IDは別会員と重複している為、登録できません。"
);

// 
$session_user_name_array = array(
	"guest" => "ゲスト様",
	"guest" => "ゲスト様"
);

/* --------------------------------------------------------------------------------------- */


/* ----- 不正アクセス処理系 ------------------------------------------------------------------ */

// 会員専用ページ（）でGET送信があった時の処理。
// session["user"]がない ＝ ログインしていない ＝ 不正アクセス。
function session_user_check($session_user){
	if(!isset($session_user)){
		header("Location:index.php");
		exit;
	}
}

/* --------------------------------------------------------------------------------------- */


/* ----- HTMLタグ作成系 ------------------------------------------------------------------ */

// 共有のhead内のタグ
$head_common_tag .= "<meta charset=\"UTF-8\">";											// charset=utf8。常識やけど。
$head_common_tag .= "<meta name=\"robots\" content=\"noindex,follow\">";				// SEO対策（インデックス:×、クロール:〇）
$head_common_tag .= "<meta name=\"format-detection\" content=\"telephone=no\">";		// IOS対策（電話番号表示を電話リンク化しない）
$head_common_tag .= "<link rel=\"icon\" type=\"image/png\" href=\"./image/mi.png\" >";	// ファビコンのリンク
$head_common_tag .= "<link rel=\"stylesheet\" href=\"./css/default.css\" >";			// 共通CSSのリンク
$head_common_tag .= "<link href=\"https://use.fontawesome.com/releases/v5.6.1/css/all.css\" rel=\"stylesheet\">";	// fontawesomeの読み込み

// 共有のheader内(index.php以外)のタグ
$header_common_tag .= "<h1>中古家電.com</h1>";
if(isset($_SESSION["user"])){
	if($_SESSION["user"]["id"] == "guest"){
		$header_common_tag .= "<p>ゲスト 様</p>";
		$header_common_tag .= "<a id=\"a1\" href=\"index.php\">トップに戻る</a>";
	}else{
		$header_common_tag .= "<p>{$_SESSION["user"]["name"]} 様</p>";
		$header_common_tag .= "<a id=\"a1\" href=\"index.php\">ログアウト</a>";
	}
}

// 共有のfooter内のタグ
$footer_common_tag .= "<p>お問い合わせは<a href=\"mailto:minami.gisen@gmail.com?subject=お問い合わせ&amp;body=----------------------------------------%0D%0A会員ID：{$_SESSION["user"]["name"]} 様%0D%0A 当項目は削除しないで下さい。%0D%0A----------------------------------------%0D%0A 以降にお問い合わせ内容を記載下さい。\">コチラ</a></p>";
$footer_common_tag .= "<i class=\"far fa-copyright\"></i>";
$footer_common_tag .= "<small> 2020 Tean Akaike</small>";

// inputタグ生成関数
// 引数：global変数,$pflag
//		1,タイプ(String) 2,id名(String) 3,name名(String) 4,class名(String) 5,サイズ値(Int)
//		6,value値(変数) 7,$attribute(String) 8,$attr_val(String) 9,$placeholder値(String)
// 処理：引数を基にしたinputタグを作成（post時の再表示機能付き）
// 戻値：上記inputタグ
// 備考：使用時はechoすること。hidden,button,submit にはPOST再表示機能はつけない。radio の時の処理は入れていない。自作願う。
//		引数７は他に追加したい属性名を追記、引数８にその値を書く。（例：引数７に onchange 、引数８に function() 指定で「onchange="function()"」と表示される。 ）
function create_input($type,$id,$name,$class,$size,$val,$attribute,$attr_val,$placeholder){
	global $pflag;
	$input_tag = "";
	$input_tag .= "<input type=\"{$type}\"" ;
	$input_tag .= "id=\"{$id}\" name=\"{$name}\" class=\"{$class}\" size=\"{$size}\"";
	if($type == "hidden" || $type == "button" || $type == "submit" ){
		$input_tag .= "value=\"{$val}\"";
	}else{
		if($pflag && !empty($val)){
			$input_tag .= "value=\"{$val}\"";
		}
	}
	if(!empty($attribute)){
		$input_tag .= "$attribute=\"{$attr_val}\"";
	}
	if(!empty($placeholder)){
		$input_tag .= "placeholder=\"{$placeholder}\"";
	}
	$input_tag .= ">";
	return $input_tag;
}

// checkbox / selectboxタグ生成関数
// 引数：global変数,$pflag
//		1,タイプ(String) 2,id名(String) 3,name名(String) 4,class名(String)
//		5,element_array(配列変数)   ：配列の内容(引数1でselectbox指定時は連想配列（詳細は備考を参照）も可)
//		6,chose_val_array(配列変数) ：POST送信再表示用に前画面で選択した番号or値
//		7,val_type(true/false)     ：valueの値（true：引数5で指定した$element_arrayがそのまま入る、false:0から採番した数値が入る）
//		8,$default(true/false)     ：初期選択の有無（true：引数5で指定した$element_arrayの0番目が初期値として選択された状態で表示される。false：何も選択されていない状態で表示される）
// 処理：引数を基にしたcheck/selectboxタグを作成（post時の再表示機能付き）
// 戻値：上記boxタグ(各boxのvalue値は引数5で決めた値、表示は引数4の配列データ)
// 備考：引数１は checkbox または selectbox を文字列で指定のコト。当該関数使用時はechoすること。
//		引数３の name名に［］を追記する必要はない。
//		selectbox の時は引数5に連想配列を指定すると連想配列の key が optgroup として自動生成される。
function create_box($type,$id,$name,$class,$element_array,$chose_val_array,$val_type,$default){
	global $pflag;
	$box_tag = "";
	// checkboxの時の処理
	if($type == "checkbox"){
		for($i=0;$i<count($element_array);$i++){
			$box_tag .= "<label><input type=\"checkbox\" id=\"{$id}\" name=\"{$name}[]\" ";
			if($val_type){
				$box_tag .= " value=\"$element_array[$i]\" ";
			}else{
				$box_tag .= " value=\"{$i}\" ";
			}
			if($pflag){
				for($j=0;$j<count($chose_val_array);$j++){
					if($val_type){
						if($element_array[$i] == $chose_val_array[$j]){
							$box_tag .= " checked=\"checked\" ";
						}
					}else{
						if($i == $chose_val_array[$j]){
							$box_tag .= " checked=\"checked\" ";
						}
					}
				}
			}else{
				if($i == 0 && $default){
					$box_tag .= " checked=\"checked\" ";
				}
			}
			$box_tag .= ">" . $element_array[$i] . "</label>";
		}
	}
	// selectboxの時の処理
	if($type == "selectbox"){
		$box_tag .= "<select size=\"1\" id=\"{$id}\" name=\"{$name}[]\" >";
		// 普通の配列か連想配列かを判定
		if(array_values($element_array) === $element_array){
			// $element_array が普通の配列の時の処理（optgroup タグなし)
			for($i=0;$i<count($element_array);$i++){
				if($i == 0 && $default){
					$box_tag .= "<option value=\"\"";
				}else{
					if($val_type){
						$box_tag .= "<option value=\"{$element_array[$i]}\"";
					}else{
						$box_tag .= "<option value=\"{$i}\"";
					}
				}
				if($pflag){
					if($chose_val_array[0] == $element_array[$i]){
						$box_tag .= "selected";
					}
				}
				$box_tag .= ">$element_array[$i]</option>";
			}
			$box_tag .= "</select>";
		}else{
			// $element_array が連想配列の時の処理（optgroup タグ作成)
			$i = 0;
			foreach($element_array as $key => $val){
				if($i == 0 && $default){
					$box_tag .= "<option value=\"\">$val</option>";
					$i++;
				}else{
					$box_tag .= "<optgroup label=\"{$key}\">";
					foreach($val as $val2){
						if($val_type){
							$box_tag .= "<option value=\"{$val2}\"";
						}else{
							$box_tag .= "<option value=\"{$i}\"";
						}
						if($pflag){
							if($chose_val_array[0] == $val2){
								$box_tag .= "selected";
							}
						}
						$box_tag .= ">$val2</option>";
						$i++;
					}
				}
			}
			$box_tag .= "</select>";
		}
	}
	return $box_tag;
}

/* --------------------------------------------------------------------------------------- */


/* ----- PDO処理系 ----------------------------------------------------------------------- */

// 引数：1,SQL文の種類(String) 2,使用するSQL文の添え字(String)、
//       3,各SQL文ごとの個別処理の指定・別関数sql()で使用(String)、 
//		 4,引数１、5,引数２、6,引数３（配列）
// 処理：引数を基にしたPDO処理
// 戻値：true / false　
// 備考：取得する値は sql() 内で固有の global 変数に代入。
function sql($type,$sqlno,$funcno,$val,$val2,$val_array) {
	global $database_dsn,$dbname,$database_user,$database_password,$err_array,$sql_array;
	$userno = "0"; // 強制的に検証したい時に使う。通常はコメントアウトする行。
	$dsn   = $database_dsn;
	$user  = $database_user[$userno];
	$pass  = $database_password[$userno];
	$check = false;
	$same  = false;

	// 使用するSQL文を各ページの名前をキーに一覧で配列可
	$sql_array = array(
		"index1"	=> "select * from k2g1_genre",
		"index2"	=> "select count(item_id) from k2g1_item where item_quantity != 0 and item_deleted = 0",
		"index3"	=> "select item_id,item_name,item_seller_id,seller_name,item_price,item_quantity 
						from k2g1_seller,k2g1_item,k2g1_genre 
						where seller_id = item_seller_id and item_genre_id = genre_id 
							and genre_name = '${val}' 
							and item_quantity != 0 and item_deleted = 0",
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

	if($sqlno == "confilm2"){
		// 新規会員登録時の処理。
		// 大量の入力値を配列にしてココに送ってるので、配列を分解して set 以降に組み込む処理。
		foreach($val_array as $key => $val){
			// 以下の項目以外をINSERTする。
			if($key == "user_registration" ||  $key == "user_miss" ||
			  $key == "user_freeze_time" || $key == "user_delete" ||
			  $key == "user_freeze" || $key == "user_mod_date"){
			}else{
				if($key == "user_pw"){
					$val = password_hash($val,PASSWORD_DEFAULT);
				}
			  $sql_array[$sqlno] .= $key . "='" . $val . "' ,";
			}
		}
		// 最後に現在日時を、登録日時（カラム名：user_registration）に加える。
		$sql_array[$sqlno] .= "user_registration = now()";
	}
	if($sqlno == "confilm5"){
		// 会員修正時の処理。
		// 大量の入力値を配列にしてココに送ってるので、配列を分解して set 以降に組み込む処理。
		foreach($val_array as $key => $val_f ){
			if($key == "user_registration" ||  $key == "user_miss" ||
			  $key == "user_freeze_time" || $key == "user_delete" ||
			  $key == "user_freeze" || $key == "user_mod_date"){
			}else{
				if($key == "user_pw"){
					$val_f = password_hash($val_f,PASSWORD_DEFAULT);
				}
				$sql_array[$sqlno] .= $key . "='" . $val_f . "' ,";
			}
		}
		// 最後に現在日時を、修正日時（カラム名：user_mod_date）に加える。
		$sql_array[$sqlno] .= "user_mod_date = now() ";
		// update の条件に user_id = 現在ログインしているID を加える。
		$sql_array[$sqlno] .= "where user_id = '{$val_array["user_id"]}' ";
	}

	try{
		$db = new PDO($dsn,$user,$pass);
		$db->exec("SET NAMES utf8");
		$db->setAttribute(PDO::ATTR_CASE,PDO::CASE_LOWER);

		// sql文の選択。候補は上の$sql_array参照のこと。
		$sql = $sql_array[$sqlno];

		// 排他ロック処理（select 文以外は排他ロック。になってるはず…）
		if($type == "select"){
			$result = $db->prepare($sql);
			if(!$result->execute()){
				echo "【SQL:err1】［sqlno］",$sqlno,"［内容］構文エラー(文法or記述ミス)［入力したSQL文］",$sql;
				return false;
			}
			$count = $result->rowCount();
		}else{
			// select 文以外の場合の処理
			$db->beginTransaction();
			//プリペアドステートメント / ロック
			$result = $db->prepare($sql);
			$count  = $result->execute();
			if(!$count){
				echo "【SQL:err1】［sqlno］",$sqlno,"［内容］構文エラー(文法or記述ミス)［入力したSQL文］",$sql;
				return false;
			}
		}
		if($count !== FALSE){
			if($count == 0){
				// 空値が帰る可能性があるSQL文はここでエラー除外しとく。
				if(!$sqlno == "index2" && !$sqlno == "index3"){
					// 空値が帰らないSQL文で空値になった時の為のエラー処理。
					echo "【SQL:err2】［sqlno］",$sqlno,"［内容］対象行なし［実行したSQL文］",$sql;
				}
				if($type != "select"){
					$db->rollback();
				}
				$db = NULL;
				return false;
			}else{
				$rows = $result->fetchall(PDO::FETCH_ASSOC);
				foreach($rows as $row){

					// 各ページに合わせて関数処理。内容は下記参照のコト。
					$check = sql_func($row,$funcno,$check,$val,$val2);

				}
			}
		}else{
			echo "【SQL:err3】［sqlno］",$sqlno,"［内容］構文エラー（実行時）［実行したSQL文］",$sql;
			if($type != "select"){
				$db->rollback();
			}
			$db = NULL;
			return false;
		}
		if($type != "select"){
			$db->commit();
		}
		$db = NULL;
	}
	catch (Exception $e){
		echo "MSG:" .$e->getMessage()."<br>";
		echo "CODE:".$e->getCode()."<br>";
		echo "LINE:".$e->getLine()."<br>";
		$db->rollback();
		$db = NULL;
		return false;
	}
	if($check){
		return true;
	}else{
		return false;
	}
}


/* --------------------------------------------------------------------------------------- */


function sql_func($row,$funcno,$check,$val,$val2){
	global 
	$sql_output_index_genre_array,
	$sql_output_index_item_sum,
	$sql_output_index_item_id_array,
	$sql_output_index_item_name_array,
	$sql_output_index_item_seller_id_array,
	$sql_output_index_seller_name_array,
	$sql_output_index_item_price_array,
	$sql_output_index_item_quantity_array;
	// $sql_output_index_new_algo_pw,
	// $sql_output_form_sq_array,
	// $sql_output_form_colomn_array,
	// $sql_output_repass2_user_sq,
	// $sql_output_repass2_user_miss,
	// $sql_output_main2_rtest_gr_id_array,
	// $sql_output_main2_rtest_gr_score_array,
	// $sql_output_main2_rtest_gr_time_array,
	// $sql_output_main2_dtest_gr_id_array,
	// $sql_output_main2_dtest_gr_score_array,
	// $sql_output_main2_dtest_gr_time_array,
	// $sql_output_main2_etest_gr_id_array,
	// $sql_output_main2_etest_gr_score_array,
	// $sql_output_main2_etest_gr_time_array,
	// $sql_output_main4_qu_status_array,
	// $sql_output_main4_qu_no_array,
	// $sql_output_main4_qu_title_array,
	// $sql_output_main4_qu_cor_array,
	// $sql_output_main4_qu_eva_array,
	// $sql_output_main4_qu_comment_array,
	// $sql_output_main5_colomn_array,
	// $sql_output_main5_user_array,
	// $sql_output_main5_user_total,
	// $sql_output_main5_user_total_effective,
	// $sql_output_main6_qu_status_array,
	// $sql_output_main6_qu_del_array,
	// $sql_output_main6_qu_id_array,
	// $sql_output_main6_qu_no_array,
	// $sql_output_main6_qu_title_array,
	// $sql_output_main6_qu_cor_array,
	// $sql_output_main6_qu_eva_array,
	// $sql_output_main6_qu_comment_array,
	// $sql_output_main7_user_status_array,
	// $sql_output_main7_user_status_2_array,
	// $sql_output_main7_user_id_array,
	// $sql_output_main7_user_regist_array,
	// $sql_output_main7_user_lastlog_array,
	// $sql_output_main7_user_loginsu_array,
	// $sql_output_main7_user_qusum_array,
	// $sql_output_main8_user_total,
	// $sql_output_main8_user_total_effective,
	// $sql_output_main8_qu_total,
	// $sql_output_main8_qu_total_effective,
	// $sql_output_main8_log_total,
	// $sql_output_main8_log_total_user,
	// $sql_output_main8_log_total_guest,
	// $sql_output_dojo_qu_no_array,
	// $sql_output_dojo_qu_title_array,
	// $sql_output_dojo_qu_id_array,
	// $sql_output_dojo_qu_cor_array,
	// $sql_output_dojo_qu_eva_array,
	// $sql_output_test_qno_array,
	// $sql_output_question_qu_array,
	// $same,
	// $sql_output_ans_test_status,
	// $sql_output_ans_test_highscore,
	// $sql_output_que_create_qu_array;

	switch ($funcno){
		case "index1":
			$sql_output_index_genre_array[$row["genre_id"]] = $row["genre_name"];
			break;
		case "index2":
			$sql_output_index_item_sum = $row["count(item_id)"];
			break;
		case "index3":
			echo $val;
			$sql_output_index_item_id_array[]		 = $row["item_id"];
			$sql_output_index_item_name_array[]		 = $row["item_name"];
			$sql_output_index_item_seller_id_array[] = $row["item_seller_id"];
			$sql_output_index_seller_name_array[]	 = $row["seller_name"];
			$sql_output_index_item_price_array[]	 = $row["item_price"];
			$sql_output_index_item_quantity_array[]	 = $row["item_quantity"];
			break;
		// case "index1":
		// 	if($val == $row["user_id"]){
		// 		if(password_verify($val2,$row["user_pw"])){
		// 			$check = true;
		// 			if(password_needs_rehash($row["user_pw"],PASSWORD_DEFAULT)){
		// 				$sql_output_index_new_algo_pw = password_hash($val2,PASSWORD_DEFAULT);
		// 			}
		// 			break;
		// 		}
		// 	}
		// 	$check = false;
		// 	break;
		// case "form1":
		// 	$sql_output_form_sq_array[$row["sq_id"]] = $row["sq_q"];
		// 	break;
		// case "form2":
		// 	$sql_output_form_colomn_array[] = $row["column_name"];
		// 	break;
		// case "form3":
		// 	if(!$same){
		// 		if($val === $row["user_id"]){
		// 			$check = false;
		// 			$same  = true;
		// 			break;
		// 		}else{
		// 			$check = true;
		// 		}
		// 	}
		// 	break;
		// case "confilm1":
		// 	if(!$same){
		// 		if($val === $row["user_id"]){
		// 			$check = false;
		// 			$same  = true;
		// 			break;
		// 		}else{
		// 			$check = true;
		// 		}
		// 	}
		// 	break;
		// case "confilm2":
		// 	$check = true;
		// 	break;
		// case "repass2_1":
		// 	$check = true;
		// 	$sql_output_repass2_user_sq = $row["user_secret_q"];
		// 	break;
		// case "repass2_2":
		// 	if($val2 == $row["user_secret_a"]){
		// 		$check = true;
		// 		break;
		// 	}else{
		// 		$check = false;
		// 		break;
		// 	}
		// case "repass2_5":
		// 	$check = true;
		// 	$sql_output_repass2_user_miss = $row["user_miss"];
		// 	break;
		// case "main2_1":
		// 	$sql_output_main2_rtest_gr_id_array[]		= $row["gr_id"];
		// 	$sql_output_main2_rtest_gr_score_array[]	= $row["gr_score"];
		// 	$sql_output_main2_rtest_gr_time_array[]		= $row["gr_time"];
		// 	break;
		// case "main2_2":
		// 	$sql_output_main2_dtest_gr_id_array[]		= $row["gr_id"];
		// 	$sql_output_main2_dtest_gr_score_array[]	= $row["gr_score"];
		// 	$sql_output_main2_dtest_gr_time_array[]		= $row["gr_time"];
		// 	break;
		// case "main2_3":
		// 	$sql_output_main2_etest_gr_id_array[]		= $row["gr_id"];
		// 	$sql_output_main2_etest_gr_score_array[]	= $row["gr_score"];
		// 	$sql_output_main2_etest_gr_time_array[]		= $row["gr_time"];
		// 	break;
		// case "main4_1":
		// 	$sql_output_main4_qu_status_array[]		= $row["qu_status"];
		// 	$sql_output_main4_qu_no_array[]			= $row["qu_no"];
		// 	$sql_output_main4_qu_title_array[]		= $row["qu_title"];
		// 	$sql_output_main4_qu_cor_array[]		= $row["an_su_c/an_su_a"];
		// 	$sql_output_main4_qu_eva_array[]		= $row["an_evaluation/an_su_e"];
		// 	$sql_output_main4_qu_comment_array[]	= $row["qu_comment"];
		// 	break;
		// case "main5_1":
		// 	$sql_output_main5_colomn_array[] = $row["column_name"];
		// 	break;
		// case "main5_2":
		// 	foreach($sql_output_main5_colomn_array as $val){
		// 		$sql_output_main5_user_array[$val] = $row[$val];
		// 	}
		// 	break;
		// case "main6_1":
		// case "main6_2":
		// case "main6_3":
		// case "main6_4":
		// case "main6_5":
		// 	$sql_output_main6_qu_status_array[]		= $row["qu_status"];
		// 	$sql_output_main6_qu_del_array[]		= $row["qu_delete"];
		// 	$sql_output_main6_qu_id_array[]			= $row["qu_id"];
		// 	$sql_output_main6_qu_no_array[]			= $row["qu_no"];
		// 	$sql_output_main6_qu_title_array[]		= $row["qu_title"];
		// 	$sql_output_main6_qu_cor_array[]		= $row["an_su_c/an_su_a"];
		// 	$sql_output_main6_qu_eva_array[]		= $row["an_evaluation/an_su_e"];
		// 	$sql_output_main6_qu_comment_array[]	= $row["qu_comment"];
		// 	break;
		// case "main7_1":
		// 	$sql_output_main7_user_status_array[]	= $row["user_delete"];
		// 	$sql_output_main7_user_status_2_array[]	= $row["user_freeze"];
		// 	$sql_output_main7_user_id_array[]		= $row["user_id"];
		// 	$sql_output_main7_user_regist_array[]	= $row["user_registration"];
		// 	break;
		// case "main7_2":
		// 	$sql_output_main7_user_lastlog_array[] = $row["max(log_log)"];
		// 	$sql_output_main7_user_loginsu_array[] = $row["count(log_log)"];
		// 	break;
		// case "main7_3":
		// 	$sql_output_main7_user_qusum_array[] = $row["count(qu_no)"];
		// 	break;
		// case "main8_1":
		// 	$sql_output_main8_user_total = $row["count(user_id)"];
		// 	break;
		// case "main8_2":
		// 	$sql_output_main8_user_total_effective = $row["count(user_id)"];
		// 	break;
		// case "main8_3":
		// 	$sql_output_main8_qu_total = $row["count(qu_no)"];
		// 	break;
		// case "main8_4":
		// 	$sql_output_main8_qu_total_effective  = $row["count(qu_no)"];
		// 	break;
		// case "main8_5":
		// 	$sql_output_main8_log_total = $row["count(log_id)"];
		// 	break;
		// case "main8_6":
		// 	$sql_output_main8_log_total_user = $row["count(log_id)"];
		// 	break;
		// case "main8_7":
		// 	$sql_output_main8_log_total_guest = $row["count(log_id)"];
		// 	break;
		// case "dojo1":
		// case "dojo2":
		// 	//select qu_no,qu_title,qu_id,an_su_c/an_su_a,an_evaluation/an_su_e from k1_question,k1_answer where qu_no = an_no order by an_su_a asc ,an_no desc;
		// 	$sql_output_dojo_qu_no_array[]		= $row["qu_no"];
		// 	$sql_output_dojo_qu_title_array[]	= $row["qu_title"];
		// 	$sql_output_dojo_qu_id_array[]		= $row["qu_id"];
		// 	$sql_output_dojo_qu_cor_array[]		= $row["an_su_c/an_su_a"];
		// 	$sql_output_dojo_qu_eva_array[]		= $row["an_evaluation/an_su_e"];
		// 	break;
		// case "que_test1":
		// case "que_test2":
		// case "que_test3":
		// 	$sql_output_test_qno_array[] = $row["qu_no"];
		// 	$check = true;
		// 	break;
		// case "question1":
		// 	$sql_output_question_qu_array["qu_no"] 				= $row["qu_no"];
		// 	$sql_output_question_qu_array["qu_title"] 			= $row["qu_title"];
		// 	$sql_output_question_qu_array["qu_id"] 				= $row["qu_id"];
		// 	$sql_output_question_qu_array["qu_question"] 		= $row["qu_question"];
		// 	$sql_output_question_qu_array["qu_answer_1"] 		= $row["qu_answer_1"];
		// 	$sql_output_question_qu_array["qu_answer_2"] 		= $row["qu_answer_2"];
		// 	$sql_output_question_qu_array["qu_answer_3"] 		= $row["qu_answer_3"];
		// 	$sql_output_question_qu_array["qu_answer_4"] 		= $row["qu_answer_4"];
		// 	$sql_output_question_qu_array["qu_answer_correct"] 	= $row["qu_answer_correct"];
		// 	$sql_output_question_qu_array["qu_time_limit"] 		= $row["qu_time_limit"];
		// 	$sql_output_question_qu_array["qu_explanation"] 	= $row["qu_explanation"];
		// 	$check = true;
		// 	break;
		// case "answer1":
		// 	if($val == $row["qu_id"]){
		// 		$check = false;
		// 		break;
		// 	}
		// 	$check = true;
		// 	break;
		// case "ans_test1":
		// 	$sql_output_ans_test_highscore = $row["gr_score"];
		// 	if($row["gr_score"] == ""){
		// 		$sql_output_ans_test_status = "first_time";
		// 		$check = true;
		// 		break;
		// 	}
		// 	if($val > $row["gr_score"]){
		// 		$sql_output_ans_test_status = "new_record";
		// 		$check = true;
		// 	}else{
		// 		if($val == $row["gr_score"]){
		// 			$sql_output_ans_test_status = "same_record";
		// 			$check = true;
		// 		}else{
		// 			$check = false;
		// 		}
		// 	}
		// 	break;
		// case "que_create1":
		// 	$sql_output_form_colomn_array[] = $row["column_name"];
		// 	break;
		// case "que_create2":
		// 	$sql_output_que_create_qu_array["qu_no"]				= $row["qu_no"];
		// 	$sql_output_que_create_qu_array["qu_title"]				= $row["qu_title"];
		// 	$sql_output_que_create_qu_array["qu_id"]				= $row["qu_id"];
		// 	$sql_output_que_create_qu_array["qu_question"]			= $row["qu_question"];
		// 	$sql_output_que_create_qu_array["qu_answer_1"]			= $row["qu_answer_1"];
		// 	$sql_output_que_create_qu_array["qu_answer_2"]			= $row["qu_answer_2"];
		// 	$sql_output_que_create_qu_array["qu_answer_3"]			= $row["qu_answer_3"];
		// 	$sql_output_que_create_qu_array["qu_answer_4"]			= $row["qu_answer_4"];
		// 	$sql_output_que_create_qu_array["qu_answer_correct"]	= $row["qu_answer_correct"];
		// 	$sql_output_que_create_qu_array["qu_time_limit"]		= $row["qu_time_limit"];
		// 	$sql_output_que_create_qu_array["qu_explanation"]		= $row["qu_explanation"];
		// 	break;
		default:
			$check = true;
	}
	return $check;
}

?>