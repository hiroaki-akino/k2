<?php
/* --------------------------------------------------------------------------------------

【基本情報】
作成者 ：西原（web1914)
概要  ：会員登録入力画面
更新日：2020/02/06
更新者：秋野

【注意】
作業「前」に前日までのファイルのコピーを作成する。
コピーしたファイルを本日用のフォルダに移す。
コピーしたファイルで本日分の作業を開始する。

----------------------------------------------------------------------------------------- */


/* ----- 共通処理 ----------------------------------------------------------------------- */
include '../inc/config.php';
include '../inc/db_access.php';
include '../inc/functions.php';
include '../inc/variables.php';


// var_dump($datebase_dsn);
// var_dump($datebase_user);
// var_dump($datebase_password);


// ここで使うconfig.php内の変数。（必要に応じて var_dump で確認）
$pflag;					// 中身：false
$head_common_tag;		// 中身：head タグ内で規定するメタタグとか
$header_common_tag;		// 中身：header タグ内で規定するタイトルタグとか
$datebase_dsn;			// 中身：DB情報


// ここで使うSQL文の一覧表示と配列変数への設定。
// 注意：変数の値などを使用してSQL文を作成したい時は「?」に置き換えて表示する。
// 注意：SQL() の引数（SQL文用の引数は３つ目以降に記載）は「?」に指定した値の順に記載する。
$pflg= 0; $errflg=0; $type=""; $checked=""; $err=[]; $title_type="会員情報登録入力";
// 買い手
$buyer_high_postalcode=""; $buyer_low_postalcode=""; $buyer_address_1=""; $buyer_address_2=""; $buyer_address_3="";
$buyer_name=""; $buyer_id=""; $buyer_pw="";
$err['buyer_name'] =""; $err['buyer_pw'] =""; $err['buyer_id']=""; $err['buyer_address_1']="";$err['buyer_address_2']="";
$err['buyer_address_3']=""; $err['buyer_high_postalcode']=""; $err['buyer_low_postalcode']="";

// 売り手
$seller_high_postalcode=""; $seller_low_postalcode=""; $seller_address_1=""; $seller_address_2=""; $seller_address_3="";
$seller_name=""; $seller_id=""; $seller_pw=""; $seller_office_name="";

$err['seller_name'] =""; $err['seller_pw'] =""; $err['seller_id']=""; $err['seller_address_1']="";$err['seller_address_2']="";
$err['seller_address_3']=""; $err['seller_high_postalcode']=""; $err['seller_low_postalcode']=""; 

function h($a){
    return htmlspecialchars( "$a", ENT_QUOTES) ;
}

$address_1_val_array	= array(
    "北海道地方"	=> array("北海道"),
    "東北地方"		=> array("青森県","岩手県","宮城県","秋田県","山形県","福島県"),
    "関東地方"		=> array("茨城県","栃木県","群馬県","埼玉県","千葉県","東京都","神奈川県"),
    "中部地方" 		=> array("新潟県","富山県","石川県","福井県","山梨県","長野県","岐阜県","静岡県","愛知県"),
    "近畿地方"		=> array("三重県","滋賀県","京都府","大阪府","兵庫県","奈良県","和歌山県"),
    "中国地方"		=> array("鳥取県","島根県","岡山県","広島県","山口県"),
    "四国地方"		=> array("徳島県","香川県","愛媛県","高知県"),
    "九州地方"		=> array("福岡県","佐賀県","長崎県","熊本県","大分県","宮崎県","鹿児島県","沖縄県")
);

// 秋野追記（会員ID重複判定（応急処置やけど）に使う変数）
// 購入者の場合の重複処理
$sql1_1 = "";
$sql1_2 = "";
$row1_1 = "";
$row1_2 = "";
// 出品者の場合の重複処理
$sql2_1 = "";
$sql2_2 = "";
$row2_1 = "";
$row2_2 = "";


//===== ポスト：リクエスト処理  =====
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pflg = true;			// POSTフラグ：ON
    
	$type = $_POST['type'];
	// セッション破棄
	if(isset($_SESSION['register']) ) {
		unset($_SESSION['register']);
	}	
    
    //----- 買い手入力チェック ------------------------------------------------------------
    if($type==0) {
        
        // 氏名----------------------------------------------------------------------------
		if($_POST["buyer_name"] ) {
			$buyer_name = h($_POST["buyer_name"] );
		} else {
			$buyer_name = "";
		}
		if (strlen($buyer_name) == 0) {
				$err['buyer_name'] = "blank";
				$errflg++;
		}
        // 買い手id----------------------------------------------------------------------
        if($_POST['buyer_id'] ) {
            $buyer_id=h($_POST['buyer_id'] );
        } else {
            $buyer_id="";
		}

		$sql1_1 = 'SELECT COUNT(*) AS cnt FROM k2g1_buyer WHERE buyer_id = ?' ;
		$row1_1 = sql($sql1_1,false,$_POST['buyer_id']);
		if($row1_1[0]["cnt"] == 0){
			// 秋野追記↓
			$sql1_2 = 'SELECT COUNT(*) AS cnt FROM k2g1_seller WHERE seller_id = ?' ;
			$row1_2 = sql($sql1_2,false,$_POST['buyer_id']);
			if($row1_2[0]["cnt"] == 0){	
			// 秋野追記↑
				//登録処理する
				if(strlen($buyer_id) ==0 ) {
					$err['buyer_id'] ="blank" ;
					$errflg++;
				} elseif(strlen($buyer_id)>0) {
					if(preg_match("/^[a-zA-Z0-9]+$/", $buyer_id)) {
						if(strlen($buyer_id)<6 || strlen($buyer_id) >12){
							$err['buyer_id'] ="length";
							$errflg++;
						}
					} else {
						$err['buyer_id']="str";
						$errflg++;
					}
				}
			} else {
				//user_idかぶりで処理しない
				$err['buyer_id']="duplicate";
				$errflg++;
			}
		} else {
			//user_idかぶりで処理しない
			$err['buyer_id']="duplicate";
			$errflg++;
		}

        // 買い手郵便番号番号------------------------------------------------------------
        // customer_high_postalcode
        if($_POST['buyer_high_postalcode'] ) {
            $buyer_high_postalcode=h(mb_convert_kana($_POST['buyer_high_postalcode'],'n','UTF-8') );
        }else {
            $buyer_high_postalcode="";
        }
        if(strlen($buyer_high_postalcode)==0 ) {
            $err['buyer_high_postalcode']='blank';
            $errflg++;
        }elseif(strlen($buyer_high_postalcode) !==3) {
            $err['buyer_high_postalcode']="length";
            $errflg++;
        }elseif(!is_numeric($buyer_high_postalcode) ) {
            $err['buyer_high_postalcode']='type';
            $errflg++;
		}
		
        // buyer_low_postalcode---------------------------------------------------------------
        if($_POST['buyer_low_postalcode'] ) {
            $buyer_low_postalcode=h(mb_convert_kana($_POST['buyer_low_postalcode'],'n','UTF-8') );
        }else {
            $buyer_low_postalcode="";
        }
        if(strlen($buyer_low_postalcode)==0 ) {
            $err['buyer_low_postalcode']='blank';
            $errflg++;
        }elseif(strlen($buyer_low_postalcode) !== 4) {
            $err['buyer_low_postalcode']="length";
            $errflg++;
        }elseif(!is_numeric($buyer_low_postalcode) ) {
            $err['buyer_low_postalcode']='type';
            $errflg++;
		}
        // 買い手都道府県-------------------------------------------------------------
        if($_POST['buyer_address_1'] ) {
            $buyer_address_1=h($_POST['buyer_address_1'] );
        } else {
            $buyer_address_1="";
		}
		if(empty($buyer_address_1) ) {
			$err['buyer_address_1']="blank";
			$errflg++;
		}
        // 買い手市区町村--------------------------------------------------------------
        if($_POST['buyer_address_2'] ) {
            $buyer_address_2=h($_POST['buyer_address_2'] );
        } else {
            $buyer_address_2="";
        }
        if(strlen($buyer_address_2)==0 ) {
            $err['buyer_address_2']="blank";
            $errflg++;
		}
        // 買い手番地-------------------------------------------------------------------------
        if($_POST['buyer_address_3'] ) {
            $buyer_address_3=h($_POST['buyer_address_3'] );
        } else {
            $buyer_address_3="";
        }
        if(strlen($buyer_address_3)==0 ) {
            $err['buyer_address_3']="blank";
            $errflg++;
		}
		
        // 買い手パスワード---------------------------------------------------------------------
        if($_POST["buyer_pw"] ) {
            $buyer_pw = h($_POST["buyer_pw"] );
        } else {
            $buyer_pw = "";
        }
        if (strlen($buyer_pw) == 0) {
            $err['buyer_pw'] = "blank";
            $errflg++;
        } elseif(strlen($buyer_pw)<6 || strlen($buyer_pw) >12) {
            $err['buyer_pw'] ="length";
            $errflg++;
        }
        
        //----- 売り手入力チェック -----
    }elseif($type==1) {
        // 売り手氏名
        if($_POST["seller_name"] ) {
            $seller_name = h($_POST["seller_name"] );
        } else {
            $seller_name = "";
        }
        if (strlen($seller_name) == 0) {
            $err['seller_name'] = "blank";
            $errflg++;
		}
		
        // 売り手id----------------------------------------------------------------------
        if($_POST['seller_id'] ) {
            $seller_id=h($_POST['seller_id'] );
        } else {
            $seller_id="";
		}

		$sql2_1 = 'SELECT COUNT(*) AS cnt FROM k2g1_buyer WHERE buyer_id = ?' ;
		$row2_1 = sql($sql2_1,false,$_POST['seller_id']);
		if($row2_1[0]["cnt"] == 0){
			// 秋野追記↓
			$sql2_2 = 'SELECT COUNT(*) AS cnt FROM k2g1_seller WHERE seller_id = ?' ;
			$row2_2 = sql($sql2_2,false,$_POST['seller_id']);
			if($row2_2[0]["cnt"] == 0){	
				// 秋野追記↑
				if(strlen($seller_id) ==0 ) {
					$err['seller_id'] ="blank" ;
					$errflg++;
				} elseif(strlen($seller_id)>0) {
					if(preg_match("/^[a-zA-Z0-9]+$/", $seller_id)) {
						if(strlen($seller_id)<6 || strlen($seller_id) >12){
							$err['seller_id'] ="length";
							$errflg++;
						}
					} else {
						$err['seller_id']="str";
						$errflg++;
					}
				}
			} else {
				//user_idかぶりで処理しない
				$err['seller_id']="duplicate";
				$errflg++;
			}
		} else {
			//user_idかぶりで処理しない
			$err['buyer_id']="duplicate";
			$errflg++;
		}

        // 売り手郵便番号番号------------------------------------------------------------
        // seller_high_postalcode
        if($_POST['seller_high_postalcode'] ) {
            $seller_high_postalcode=h(mb_convert_kana($_POST['seller_high_postalcode'],'n','UTF-8') );
        }else {
            $seller_high_postalcode="";
        }
        if(strlen($seller_high_postalcode)==0 ) {
            $err['seller_high_postalcode']='blank';
            $errflg++;
        }elseif(strlen($seller_high_postalcode) !==3) {
            $err['seller_high_postalcode']="length";
            $errflg++;
        }elseif(!is_numeric($seller_high_postalcode) ) {
            $err['seller_high_postalcode']='type';
            $errflg++;
		}
		
        // seller_low_postalcode---------------------------------------------------------------
        if($_POST['seller_low_postalcode'] ) {
            $seller_low_postalcode=h(mb_convert_kana($_POST['seller_low_postalcode'],'n','UTF-8') );
        }else {
            $seller_low_postalcode="";
        }
        if(strlen($seller_low_postalcode)==0 ) {
            $err['seller_low_postalcode']='blank';
            $errflg++;
        }elseif(strlen($seller_low_postalcode) !== 4) {
            $err['seller_low_postalcode']="length";
            $errflg++;
        }elseif(!is_numeric($seller_low_postalcode) ) {
            $err['seller_low_postalcode']='type';
            $errflg++;
		}
        // 売り手都道府県-------------------------------------------------------------
        if($_POST['seller_address_1'] ) {
            $seller_address_1=h($_POST['seller_address_1'] );
        } else {
            $seller_address_1="";
        }
        if(empty($seller_address_1) ) {
            $err['seller_address_1']="blank";
            $errflg++;
		}
		
        // 売り手市区町村--------------------------------------------------------------
        if($_POST['seller_address_2'] ) {
            $seller_address_2=h($_POST['seller_address_2'] );
        } else {
            $seller_address_2="";
        }
        if(strlen($seller_address_2)==0 ) {
            $err['seller_address_2']="blank";
            $errflg++;
		}
		
        // 売り手番地-------------------------------------------------------------------------
        if($_POST['seller_address_3'] ) {
            $seller_address_3=h($_POST['seller_address_3'] );
        } else {
            $seller_address_3="";
        }
        if(strlen($seller_address_3)==0 ) {
            $err['seller_address_3']="blank";
            $errflg++;
		}

        // 売り手パスワード---------------------------------------------------------------------
        if($_POST["seller_pw"] ) {
            $seller_pw = h($_POST["seller_pw"] );
        } else {
            $seller_pw = "";
        }
        if (strlen($seller_pw) == 0) {
            $err['seller_pw'] = "blank";
            $errflg++;
        } elseif(strlen($seller_pw)<6 || strlen($seller_pw) >12) {
            $err['seller_pw'] ="length";
            $errflg++;
		}
		// 企業名-------------------------------------------------------------------------------
		if($_POST["seller_office_name"] ) {
			$seller_office_name=h($_POST['seller_office_name'] );
		} else {
			$seller_office_name="";
		}
	}

} else {

	// 秋野(mail.php or mail_sent.php)→西原さん(register.php))への受け取り処理（作成：あきの）
	// GET送信時の処理に記載する。
	// mail.php から遷移した場合（URL方式）の処理。
	// mail_sent.php から遷移した場合（暗証番号入力方式）は、mail_sent.php 内で処理済み。
	if(isset($_GET["hash"])){ 
		$mail_hash = $_GET["hash"]; 
		$sql_out_put_mail_limit_time_array = sql("select mail_limit_time from k2g1_mail where mail_hash = ?",false,$mail_hash);
		// 該当のデータが削除されていた場合（既にURLからアクセス済みの場合）
		if($sql_out_put_mail_limit_time_array == false){
			header("Location:mail.php?type=deleted");
			exit;
			// echo "NG!（既にログイン済み）";
		}else{
			// echo "DB:",date('Y-m-d H:i:s',strtotime($sql_out_put_mail_limit_time_array[0]["mail_limit_time"]));
			// echo "now:",date('Y-m-d H:i:s');
			if(date('Y-m-d H:i:s',strtotime($sql_out_put_mail_limit_time_array[0]["mail_limit_time"])) >= date('Y-m-d H:i:s')){
				sql("delete from k2g1_mail where mail_hash = ?",false,$mail_hash);
				if(!isset($_SESSION["user"]) || (isset($_GET['logout']) && $_GET['logout']) ) {
					$_SESSION["user"]["id"]		= "guest";
					$_SESSION["user"]["name"]	= "ゲスト";
					$_SESSION["user"]["type"]	= "0";
				}
				// echo "OK!";
			}else{
				header("Location:mail.php?type=same");
				exit;
				// echo "NG!（有効期限切れ）";			
			}
		}
	}else{
		// 暗証番号入力方式の場合の処理
		// 下記セッションが存在しているハズなのでここで切り分け。
		// ログインに成功した時のみmail_sent.phpで代入される下記セッションでログイン成否を判定。
		if(isset($_SESSION["mail"]["login"]) && $_SESSION["mail"]["login"]){
			// ２回目のログインを防ぐ為にここでセッションを破棄。
			unset($_SESSION["mail"]);
		}else{
			// 不本意やけどコレも弾く。
			if(isset($_SESSION['register']['back']) && $_SESSION['register']['back']) {
				unset($_SESSION['register']['back']);
			}else{
				// パラメータがない時（単にURLを打った時はindex.phpにリダイレクト）かつ、上記セッションもない時かつ、register.check.phpから戻って来てない場合
				// index.phpに入ると強制的にguest扱いになるので、index.phpに入ったブラウザから直リンクを打つとページに行けてしまう。
				// でも、こうすることで、単純なリンク打ちではindex.phpに戻される。
				// また、適当なハッシュ値を入れたとしてもDBに格納されているものでなければ上記の処理でエラー扱いになる。
				// ハッシュ値は一度でも当該ページにログインが成功した時では削除されるので、メールを飛ばす（DBに登録する）事無く２回ログインすることはできない。
				header("Location:index.php");
			}
		}
	}
	// 直前まで表示していたページをURLのパラメータから取得
	if(isset($_GET["page"])){ 
		$_SESSION["pre_page"] = $_GET["page"]; 
	}
	// item.phpから遷移した場合、直前まで表示していた商品の商品idをURLパラメータより取得
	if(isset($_GET["item"])){ 
		$_SESSION["item"]["item_id"] = $_GET["item"]; 
	}
}

// $_SESSION['name']="";
// if(isset($_SESSION['buyer_name']) ) {
// 	$_SESSION['name']=$_SESSION['buyer_name'];
// 	$buyer_name =$_SESSION['name'];
// }

if(isset($_SESSION['register']['buyer_name']) ) {
	$buyer_name=$_SESSION['register']['buyer_name'];
}
if(isset($_SESSION['register']['buyer_high_postalcode']) ) {
	$buyer_high_postalcode=$_SESSION['register']['buyer_high_postalcode'];
}
if(isset($_SESSION['register']['buyer_low_postalcode']) ) {
	$buyer_low_postalcode=$_SESSION['register']['buyer_low_postalcode'];
}
if(isset($_SESSION['register']['buyer_address_1']) ) {
	$buyer_address_1=$_SESSION['register']['buyer_address_1'];
}
if(isset($_SESSION['register']['buyer_address_2']) ) {
	$buyer_address_2=$_SESSION['register']['buyer_address_2'];
}
if(isset($_SESSION['register']['buyer_address_3']) ) {
	$buyer_address_3=$_SESSION['register']['buyer_address_3'];
}
if(isset($_SESSION['register']['buyer_id']) ) {
	$buyer_id = $_SESSION['register']['buyer_id'];
}

if(isset($_SESSION['register']['seller_name']) ) {
	$seller_name=$_SESSION['register']['seller_name'];
}
if(isset($_SESSION['register']['seller_high_postalcode']) ) {
	$seller_high_postalcode=$_SESSION['register']['seller_high_postalcode'];
}
if(isset($_SESSION['register']['seller_low_postalcode']) ) {
	$seller_low_postalcode=$_SESSION['register']['seller_low_postalcode'];
}
if(isset($_SESSION['register']['seller_address_1']) ) {
	$seller_address_1=$_SESSION['register']['seller_address_1'];
}
if(isset($_SESSION['register']['seller_address_2']) ) {
	$seller_address_2=$_SESSION['register']['seller_address_2'];
}
if(isset($_SESSION['register']['seller_address_3']) ) {
	$seller_address_3=$_SESSION['register']['seller_address_3'];
}
if(isset($_SESSION['register']['seller_id']) ) {
	$seller_id = $_SESSION['register']['seller_id'];
}
if(isset($_SESSION['register']['seller_office_name']) ) {
	$seller_office_name=$_SESSION['register']['seller_office_name'];
}


?>
<!DOCTYPE html>
<html>
	<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#">
		<?= $head_common_tag ?>
		<meta charset="UTF-8">
		<link href ="../css/default.css" media ="all" rel ="stylesheet">
		<title>register.php</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<style>
			body > header,
			main > header{
				margin    : auto;
				max-width : 800px;
			}
			div{
				margin: auto;
				text-align : center;
				max-width  : 800px;
			}
			div > header{
				position : relative;
			}
			.limit{
				position   : absolute ; 
				top        : -50px; 
				right      : 20px;
				text-align : center; 
			}
			section > header{
				text-align : left;
			}
			fieldset{
				padding : 20px 0px 30px;
			}
			fieldset legend{
				padding : 0px 20px;
			}
			fieldset label{
				display        : inline-block;
				width          : 20%;
				vertical-align : middle;
				font-size      : 20px;
			}
			input[type="submit"]{
				width 		: 40%;
				margin-top	: 2em; 
			}
			th {
				background-color	: var(--color-light-orange);
			}

			/* 秋野追記 ↓ -----------------------------------------------------　*/
			#buyer_form th{
				background-color : var(--color-light-navy);
				color : white;
				/* color			 : var(--color-light-orange); */
			}
			.buyer_table{
				/* border	   : 5px solid var(--color-light-navy); */
				box-shadow : 4px 4px 6px gray;
			}
			.seller_table{
				/* border     : 5px solid var(--color-light-orange); */
				box-shadow : 4px 4px 6px gray;
			}
			/* 秋野追記 ↑ ----------------------------------------------------- */

			th>span {
				background:red;
				color:white;
				font-size:13px;
			}
			td>span {
				color:red;
				font-size:13px;
			}

			/* 秋野追記 ↓ */
			input[type="radio"]{
				display : none;
			}
			.buyer_select {
				display: block;
    			float: left;
				height           : 40px;
				width  			 : calc(100%/2);
				border-radius	 : 5px 5px 0px 0px;
				background-color : var(--color-light-navy);
				color			 : var(--color-light-orange);
				text-align		 : center;
				padding			 : 0.5em 0 0 0;
			}
			.seller_select {
				display: block;
    			float: left;
				height           : 40px;
				width            : calc(100%/2);
				border-radius	 : 5px 5px 0px 0px;
				background-color : var(--color-light-orange);
				color			 : var(--color-light-navy);
				text-align		 : center;
				padding			 : 0.5em 0 0 0;
			}
			.buyer_select:hover,
			.seller_select:hover {
				font-weight	: bold;
			}
			/* 秋野追記 ↑ */

		</style>
		<script type="text/javascript">
			function formChange(){
				document.getElementsByName('select');	
				radio = document.getElementsByName('select');
				// 秋野追記（下記文は確認画面表示時はエラー（確認画面表示時はradioボタンは存在しないから）になるので１処理加えた。）
				if(radio.length != 0){
					if(radio[0].checked) {
						//customer-form表示
						document.getElementById('buyer_form').style.display  = "";
						document.getElementById('seller-form').style.display = "none";
					}else if(radio[1].checked) {
						//seller-form表示
						document.getElementById('seller-form').style.display = "";
						document.getElementById('buyer_form').style.display  = "none";
					}
				}

				// 秋野追記↓
				// 購入者登録時「同意する」ボタンのチェック確認（チェックしてなけば登録ボタンを無効化）
				if(document.getElementById("buyer_agree_btn")){
					const buyer_agree_button  = document.getElementById("buyer_agree_btn");
					const buyer_reg_button	  = document.getElementById("buyer_btn");
					buyer_reg_button.disabled = true;
					buyer_agree_button.onchange = function(){
						if(!this.checked == true){
							buyer_reg_button.disabled = true;
						}else{
							buyer_reg_button.disabled = false;
						}
					}
				}

				// 出品者登録時「同意する」ボタンのチェック確認（チェックしてなけば登録ボタンを無効化）
				if(document.getElementById("seller_agree_btn")){
					const seller_agree_button  = document.getElementById("seller_agree_btn");
					const seller_reg_button	   = document.getElementById("seller_btn");
					seller_reg_button.disabled = true;
					seller_agree_button.onchange = function(){
						if(!this.checked == true){
							seller_reg_button.disabled = true;
						}else{
							seller_reg_button.disabled = false;
						}
					}
				}	
				// 秋野追記↑
			}	 
			//オンロードさせ、リロード時に選択を保持
			window.onload =formChange;
		</script>	
	</head>
	<body>
		<?php include '../inc/var_dump.php' ?>
		<header>
			<?php echo $header_common_tag ?>
		</header>
		<main>
			<header>
				<h2><?= $title_type ?></h2>
			</header>
			<?php if($pflg == false || $errflg > 0) : /* 初回（GET）又は エラー有り（POST) */?>
				<div id="div1a" class="div1">
					<!-- <h1 align="center">会員情報入力</h1> -->
					<!-- <hr>		 -->
					<label class="buyer_select"><input type="radio" id="buyer_select" name="select" value="hoge1" onclick="formChange();" checked="checked" />購入者用アカウント</label>
					<label class="seller_select"><input type="radio" id="seller_select" name="select" value="hoge2" onclick="formChange();"  />出品者用アカウント</label>
					<!-- 買い手form ------------------------------------------------------------------------------------------------------------------------------- -->
					<form id="buyer_form" action="" method="POST" id="buyer_form">
						<table class="buyer_table" border="1" width="100%">
							<tr>
								<th>郵便番号 <span>必須</span></th>
								<td>
									<input type="text" name="buyer_high_postalcode" size ="5" placeholder="例) 123" value="<?= $buyer_high_postalcode ?>"  />
									<?php echo " - "; ?>
									<input type="text" name="buyer_low_postalcode" size ="5" placeholder="4567" value="<?= $buyer_low_postalcode ?>"  />
									<?php if($err['buyer_high_postalcode'] =='blank' || $err['buyer_low_postalcode'] =='blank'): ?>
										<br><span class="error">* 郵便番号を入力してください</span>
									<?php elseif($err['buyer_high_postalcode'] =='type' || $err['buyer_low_postalcode'] =='type'): ?>
										<br><span class="error">* 郵便番号を数値で入力してください</span>
									<?php elseif($err['buyer_high_postalcode'] =='length' || $err['buyer_low_postalcode'] =='length'):?>
										<br><span class="error">* 郵便番号を正しく入力してください</span>
									<?php endif;?>
								</td>
							</tr>
							<tr>
								<th>都道府県 <span>必須</span></th>
								<td>
									<select name="buyer_address_1" >
										<option value="">都道府県を選択</option>
										<?php foreach($address_1_val_array as $key => $val): ?>
											<optgroup label="<?php echo $key;?>">
											<?php foreach($val as $val2): ?>
												<option value="<?php echo $val2;?>" <?php if($val2==$buyer_address_1) echo "selected"; ?>  label="<?php echo $val2;?>"></option>
											<?php endforeach; ?>
											</optgroup>									
										<?php endforeach; ?>
									</select>
									<?php if($err['buyer_address_1'] =='blank'): ?>
										<br><span class="error">* 都道府県を選択してください</span>
									<?php endif; ?>
								</td>
							</tr>
							<tr>
								<th>市区町村 <span>必須</span></th>
								<td>
									<input type="text"  name="buyer_address_2" value="<?=$buyer_address_2?>" />
									<?php if($err['buyer_address_2'] =='blank'): ?>
										<br><span class="error">* 市区町村を入力してください</span>
									<?php endif; ?>
								</td>
							</tr>
							<tr>
								<th>番地 <span>必須</span></th>
								<td>
									<input type="text"  name="buyer_address_3" value="<?=$buyer_address_3?>" />
									<?php if($err['buyer_address_3'] =='blank'): ?>
										<br><span class="error">* 番地を入力してください</span>
									<?php endif; ?>
								</td>
							</tr>
							<tr>
								<th>氏名 <span>必須</span></th>
								<td>
									<input type="text"  name="buyer_name" value="<?php echo $buyer_name ?>" />
									<?php if($err['buyer_name'] =='blank'): ?>
										<br><span class="error">* 氏名を入力してください</span>
									<?php endif; ?>
								</td>
							</tr>
							<tr>
								<th>ユーザーID <span>必須</span></th>
								<td>
									<input type="text" name="buyer_id" placeholder="6~12文字の半角英数字" value="<?php echo $buyer_id ?>"  />
									<?php if($err['buyer_id']=="duplicate"): ?>
										<br><span class="error">* 指定されたIDはすでに登録されています</span>
									<?php elseif($err['buyer_id'] =='blank'): ?>
										<br><span class="error">* idを入力してください</span>
									<?php elseif($err['buyer_id']=="length"): ?>
										<br><span class="error">* idは6～12文字で入力してください</span>
									<?php elseif($err['buyer_id']=="str"): ?>
										<br><span class="error">* idは半角英数字で入力してください</span>
									<?php endif;?>
								</td>
							</tr>
							<tr>
								<th>パスワード <span>必須</span></th>
								<td>
									<input type="password" name="buyer_pw" placeholder="6~12文字の半角英数字" />
									<?php if($err['buyer_pw'] =='blank'): ?>
										<br><span class="error">* パスワードを入力してください</span>
									<?php elseif($err['buyer_pw'] =='length'): ?>
										<br><span class="error">* パスワードは6~12文字で入力してください</span>
									<?php endif;?>
								</td>
							</tr>
						</table>
						<input type="hidden" name="type" value="0">
						<input type="submit" name="buyer_btn" value="次へ進む" >
					</form>

					<!-- 売り手form ---------------------------------------------------------------------------------------------------------------------------------- -->
					<form class="seller_form" action="" method="POST" id="seller-form">
						<table  class="seller_table" border="1" width="100%">
							<tr>
								<th>郵便番号 <span>必須</span></th>
								<td>
									<input type="text" name="seller_high_postalcode" size ="5" placeholder="例) 123" value="<?= $seller_high_postalcode ?>"  />
									<?php echo " - "; ?>
									<input type="text" name="seller_low_postalcode" size ="5" placeholder="4567" value="<?= $seller_low_postalcode ?>"  />
									<?php if($err['seller_high_postalcode'] =='blank' || $err['seller_low_postalcode'] =='blank'): ?>
										<br><span class="error">* 郵便番号を入力してください</span>
									<?php elseif($err['seller_high_postalcode'] =='type' || $err['seller_low_postalcode'] =='type'): ?>
										<br><span class="error">* 郵便番号を数値で入力してください</span>
									<?php elseif($err['seller_high_postalcode'] =='length' || $err['seller_low_postalcode'] =='length'):?>
										<br><span class="error">* 郵便番号を正しく入力してください</span>
									<?php endif;?>
								</td>
							</tr>
							<tr>
								<th>都道府県 <span>必須</span></th>
								<td>
									<select name="seller_address_1">
										<option value="">都道府県を選択</option>
										<?php foreach($address_1_val_array as $key => $val): ?>
											<optgroup label="<?php echo $key;?>">
											<?php foreach($val as $val2): ?>
												<option value="<?php echo $val2;?>" <?php if($val2==$seller_address_1) echo "selected"; ?>  label="<?php echo $val2;?>"></option>
											<?php endforeach; ?>
											</optgroup>									
										<?php endforeach; ?>
									</select>
									<?php if($err['seller_address_1'] =='blank'): ?>
										<br><span class="error">* 都道府県を選択してください</span>
									<?php endif; ?>
								</td>
							</tr>
							<tr>
								<th>市区町村 <span>必須</span></th>
								<td>
									<input type="text"  name="seller_address_2" value="<?=$seller_address_2?>" />
									<?php if($err['seller_address_2'] =='blank'): ?>
										<br><span class="error">* 市区町村を入力してください</span>
									<?php endif; ?>
								</td>
							</tr>
							<tr>
								<th>番地 <span>必須</span></th>
								<td>
									<input type="text"  name="seller_address_3" value="<?=$seller_address_3?>" />
									<?php if($err['seller_address_3'] =='blank'): ?>
										<br><span class="error">* 番地を入力してください</span>
									<?php endif; ?>
								</td>
							</tr>
							<tr>
								<th>氏名 <span>必須</span></th>
								<td>
									<input type="text"  name="seller_name" value="<?=$seller_name?>" />
									<?php if($err['seller_name'] =='blank'): ?>
										<br><span class="error">* 氏名を入力してください</span>
									<?php endif; ?>
								</td>
							</tr>
							<tr>
								<th>企業名(任意)</th>
								<td><input type="text" name="seller_office_name" value="<?=$seller_office_name ?>" /></td>
							</tr>
							<tr>
								<th>ユーザーID <span>必須</span></th>
								<td>
									<input type="text" name="seller_id" placeholder="6~12文字の半角英数字" value="<?=$seller_id?>"/>
									<?php if($err['seller_id']=="duplicate"): ?>
										<br><span class="error">* 指定されたIDはすでに登録されています</span>
									<?php elseif($err['seller_id'] =='blank'): ?>
										<br><span class="error">* IDを入力してください</span>
									<?php elseif($err['seller_id']=="length"): ?>
										<br><span class="error">* IDは6～12文字で入力してください</span>
									<?php elseif($err['seller_id']=="str"): ?>
											<br><span class="error">* IDは半角英数字で入力してください</span>
									<?php endif;?>
								</td>
							</tr>
							<tr>
								<th>パスワード <span>必須</span></th>
								<td>
									<input type="password" placeholder="6~12文字の半角英数字" name="seller_pw"/>
									<?php if($err['seller_pw'] =='blank'): ?>
										<br><span class="error">* パスワードを入力してください</span>
									<?php elseif($err['seller_pw'] =='length'): ?>
										<br><span class="error">* パスワードは6~12文字で入力してください</span>
									<?php endif;?>
								</td>
							</tr>
						</table>
						<input type="hidden" name="type" value="1">
						<input type="submit" name="seller_btn" value="次へ進む" >
					</form>
				</div>

			<?php else:?>
				<div id="div1b" class="div1">
					<!--買い手登録画面 ----------------------------------------------------------------------------------------------------------------------------------------->
					<?php if($type==0): ?>
						<form action="reg-confirm.php" method="POST" id="form1">
							<table border="1">
								<tr>
									<th>ユーザータイプ</th>
									<td>購入者</td>
								</tr>
								<tr>
									<th>郵便番号</th><td><?= $buyer_high_postalcode." - ".$buyer_low_postalcode ?></td>
								</tr>
								<tr>
									<th>都道府県</th><td><?= $buyer_address_1?></td>
								</tr>
								<tr>
									<th>市区町村</th><td><?= $buyer_address_2?></td>
								</tr>
								<tr>
									<th>番地</th><td><?= $buyer_address_3?></td>
								</tr>
								<tr>
									<th>氏名</th><td><?= $buyer_name?></td>
								</tr>
								<tr>
									<th>ユーザーID</th><td><?= $buyer_id?></td>
								</tr>
								<tr>
									<th>パスワード</th>
									<td>
										<?php for($i=0; $i < mb_strlen($buyer_pw); $i++): ?>
											<?php echo "*";?>
										<?php endfor;?>
									</td>
								</tr>
							</table>
							<?php foreach($_POST as $key => $val): ?>
								<input type="hidden" name="<?php echo $key;?>" value="<?php echo $val;?>">
							<?php endforeach;?>
							<!-- 秋野追記↓ -->
							<br>
							この内容で登録致します。<br>
							<br>
							【！ご注意！】
							<br>
							「<font style="color:red!important;font-weight:bold!important;">このシステムは実習課題であり実際に購入を行えるサイトではなく、<br>金銭のやり取りも発生することはありません。</font>」<br>
							上記に同意する<input type="checkbox" id="buyer_agree_btn"><br>
							<!-- 秋野追記↑ -->
							<input type="submit" name="buyer_btn2" id="buyer_btn" value="登録" >
						</form>
						<form action="register.check.php" method="POST">
							<input type="submit" value="戻る">
							<?php foreach($_POST as $key => $val): ?>
								<input type="hidden" name="<?php echo $key;?>" value="<?php echo $val;?>">
							<?php endforeach;?>
						</form>
					<!--売り手登録画面 --------------------------------------------------------------------------------------------------------------------------------------------->
					<?php elseif($type==1): ?>
						<form action="reg-confirm.php" method="POST" id="form2">
							<table border="1">
								<tr>
									<th>ユーザータイプ</th>
									<td>出品者</td>
								</tr>
								<tr>
									<th>郵便番号</th><td><?= $seller_high_postalcode." - ".$seller_low_postalcode ?></td>
								</tr>
								<tr>
									<th>都道府県</th><td><?= $seller_address_1?></td>
								</tr>
								<tr>
									<th>市区町村</th><td><?= $seller_address_2?></td>
								</tr>
								<tr>
									<th>番地</th><td><?= $seller_address_3?></td>
								</tr>
								<tr>
									<th>氏名</th><td><?= $seller_name?></td>
								</tr>
								<tr>
									<th>企業名</th><td><?= $seller_office_name?></td>
								</tr>
								<tr>
									<th>ユーザーID</th><td><?= $seller_id?></td>
								</tr>
								<tr>
									<th>パスワード</th>
									<td>
										<?php for($i=0; $i < mb_strlen($seller_pw); $i++): ?>
											<?php echo "*";?>
										<?php endfor;?>
									</td>
								</tr>
							</table>
							<?php foreach($_POST as $key => $val): ?>
								<input type="hidden" name="<?php echo $key;?>" value="<?php echo $val;?>">
							<?php endforeach;?>
							<!-- 秋野追記↓ -->
							<br>
							この内容で登録致します。<br>
							<br>
							【！ご注意！】
							<br>
							「<font style="color:red!important;font-weight:bold!important;">このシステムは実習課題であり実際に購入を行えるサイトではなく、<br>金銭のやり取りも発生することはありません。</font>」<br>
							上記に同意する<input type="checkbox" id="seller_agree_btn"><br>
							<!-- 秋野追記↑ -->
							<input type="submit" name="seller_btn2" id="seller_btn" value="登録" >
						</form>
						<form action="register.check.php" method="POST">
							<input type="submit" value="戻る">
							<?php foreach($_POST as $key => $val): ?>
								<input type="hidden" name="<?php echo $key;?>" value="<?php echo $val;?>">
							<?php endforeach;?>
						</form>
						<!-- ----------------------------------------------------------------------------------------------------------------------------------------------- -->
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</main>
		<footer>
			<?= $footer_common_tag ?>
		</footer>
	</body>
</html>