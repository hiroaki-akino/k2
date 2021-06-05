<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成　：秋野浩朗（web1902)
概要　：全てのファイルに影響する共通利用関数のファイル
更新者：秋野
更新日：2020/2/4

【主な処理】
１：セッション操作系関数
	session_start()
	index.php以外で使う直接アクセスの制御

２：検証系関数
	各種var_dump(検証用なので本番時はココをコメントアウトする)

３：HTML タグ作成系関数
	共通inputタグ(POST時再表示)変数作成(各ページの使いたいとこでechoする、radio除く)
	共通boxタグ変数作成(各ページの使いたいとこでechoする)

----------------------------------------------------------------------------------------- */


/* ----- 共通処理 ----------------------------------------------------------------------- */

// ここで使用する変数の宣言
$pflag　= false;

/* --------------------------------------------------------------------------------------- */


/* ----- １：SESSION 操作系 ---------------------------------------- */

// セッションスタート。
session_start();

// 不正アクセス処理（index.php以外のページでGET送信があった時の処理）
// session["user"]がない ＝ indwx.phpを経由していない ＝ 不正アクセス。
function session_user_check($session_user){
	if(!isset($session_user)){
		header("Location:index.php");
		exit;
	}
}

// 不正アクセス処理（出品者専用ページでGET送信があった時の処理）
// session["user"]がない ＝ ログインしていない ＝ 不正アクセス。
// session["user"]["type"]が 1 以外　＝　ゲストアカウント or 購入者アカウントで無理やりログインしようとしている　＝　不正アクセス
function session_seller_user_check($session_user){
	if(!isset($session_user)){
		header("Location:index.php");
		exit;
	}else{
		// 
		if($session_user["type"] != 1){
			header("Location:index.php");
			exit;
		}
	}
}

// 不正アクセス処理（購入者専用ページでGET送信があった時の処理）
// session["user"]がない ＝ ログインしていない ＝ 不正アクセス。
// session["user"]["type"]が 2 以外　＝　ゲストアカウント or 出品者アカウントで無理やりログインしようとしている　＝　不正アクセス
function session_buyer_user_check($session_user){
	if(!isset($session_user)){
		header("Location:index.php");
		exit;
	}else{
		// 
		if($session_user["type"] != 2){
			header("Location:index.php");
			exit;
		}
	}
}

/* --------------------------------------------------------------- */


/* ----- ２：検証系 ------------------------------------------------ */

// 各種変数の検証用関数。
// 本番時はコメントアウトする。
// var_dump($_COOKIE);
// var_dump($_SESSION);
// var_dump($_POST);
// var_dump($_GET);

/* --------------------------------------------------------------- */




/* ----- ３：HTMLタグ作成系 ---------------------------------------- */

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
	if(!empty($id)){
		$input_tag .= " id=\"{$id}\" " ;
	}
	if(!empty($name)){
		$input_tag .= " name=\"{$name}\" " ;
	}
	if(!empty($class)){
		$input_tag .= " class=\"{$class}\" " ;
	}
	if(!empty($size)){
		$input_tag .= " size=\"{$size}\" " ;
	}
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
					if($val_type){
						$box_tag .= "<option value=\"{$element_array[$i]}\" \"selected\" ";
					}else{
						$box_tag .= "<option value=\"{$i}\" \"selected\" ";
					}
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
					if($val_type){
						$box_tag .= "<optgroup label=\"{$key}\">";
						$box_tag .= "<option value=\"{$val}\" \"selected\">$val</option>";
					}else{
						$box_tag .= "<optgroup label=\"{$key}\">";
						$box_tag .= "<option value=\"{$i}\" \"selected\">$val</option>";
					}
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

/* --------------------------------------------------------------- */


?>