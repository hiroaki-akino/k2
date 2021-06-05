<?php
/* --------------------------------------------------------------------------------------

【基本情報】
作成者 ：西原（web1914)
概要  ：会員登録完了画面
更新日：2020/02/04

【注意】
作業「前」に前日までのファイルのコピーを作成する。
コピーしたファイルを本日用のフォルダに移す。
コピーしたファイルで本日分の作業を開始する。

----------------------------------------------------------------------------------------- */



/* ----- 共通処理 ----------------------------------------------------------------------- */
// session_start();
include '../inc/config.php';
include '../inc/db_access.php';
include '../inc/functions.php';
include '../inc/variables.php';

// ここで使うconfig.php内の変数。（必要に応じて var_dump で確認）
$pflag;					// 中身：false
$head_common_tag;		// 中身：head タグ内で規定するメタタグとか
$header_common_tag;		// 中身：header タグ内で規定するタイトルタグとか
$datebase_dsn;			// 中身：DB情報


// ここで使うSQL文の一覧表示と配列変数への設定。
// 注意：変数の値などを使用してSQL文を作成したい時は「?」に置き換えて表示する。
// 注意：SQL() の引数（SQL文用の引数は３つ目以降に記載）は「?」に指定した値の順に記載する。
function h($a) {
    return htmlspecialchars( "$a", ENT_QUOTES) ;
}

// exit;
if($_SERVER["REQUEST_METHOD"] == "POST"){
	$pflag = true;

	if(empty($_POST) ) {
		header('Location:register.php');
	}

	if($_POST['type']==0) {
		$sql1='INSERT INTO k2g1_buyer SET
		buyer_id=? ,
		buyer_pw=? ,
		buyer_name=? ,
		buyer_high_postalcode=? ,
		buyer_low_postalcode=? ,
		buyer_address_1=? ,
		buyer_address_2=? ,
		buyer_address_3=? ,
		buyer_registration=NOW()'
		;
		$buyer_id=h($_POST['buyer_id'] );
		$buyer_pw=h(password_hash($_POST['buyer_pw'] ,PASSWORD_DEFAULT) );
		$buyer_name = h($_POST['buyer_name'] );
		$buyer_high_postalcode = h(mb_convert_kana($_POST['buyer_high_postalcode'],'n','UTF-8') );
		$buyer_low_postalcode = h(mb_convert_kana($_POST['buyer_low_postalcode'],'n','UTF-8') );
		$buyer_address_1 = h($_POST['buyer_address_1'] );
		$buyer_address_2 = h($_POST['buyer_address_2'] );
		$buyer_address_3 = h($_POST['buyer_address_3'] );
		sql($sql1, false ,$buyer_id , $buyer_pw , $buyer_name , $buyer_high_postalcode , $buyer_low_postalcode , $buyer_address_1 , $buyer_address_2 , $buyer_address_3 );

		$sql3='SELECT * FROM k2g1_buyer WHERE buyer_id=? AND buyer_name=?' ;
		$login=sql($sql3, false ,$buyer_id,$buyer_name );
		

		var_dump($login[0]["buyer_id"]);
		var_dump($login[0]["buyer_name"]);
		
			echo "<script>alert('登録完了しました')</script>";
			

		$_SESSION["user"]['id']=$login[0]["buyer_id"];
		$_SESSION["user"]['name']=$login[0]["buyer_name"];
		$_SESSION["user"]['type']= 2;

	} elseif($_POST['type']==1) {
		$sql2='INSERT INTO k2g1_seller SET
		seller_id=? ,
		seller_pw=? ,
		seller_name=? ,	
		seller_high_postalcode=? ,
		seller_low_postalcode=? ,
		seller_address_1=? ,
		seller_address_2=? ,
		seller_address_3=? ,
		seller_office_name=? ,
		seller_registration=NOW()'
		;
		$seller_id = h($_POST['seller_id'] );
		$seller_pw = h(password_hash($_POST['seller_pw'], PASSWORD_DEFAULT) );
		$seller_name = h($_POST['seller_name'] );
		$seller_high_postalcode = h(mb_convert_kana($_POST['seller_high_postalcode'],'n','UTF-8') );
		$seller_low_postalcode = h(mb_convert_kana($_POST['seller_low_postalcode'],'n','UTF-8') );
		$seller_address_1 = h($_POST['seller_address_1'] );
		$seller_address_2 = h($_POST['seller_address_2'] );
		$seller_address_3 = h($_POST['seller_address_3'] );
		$seller_office_name = h($_POST['seller_office_name'] );
		sql($sql2 , true , $seller_id , $seller_pw , $seller_name , $seller_high_postalcode , $seller_low_postalcode , $seller_address_1 , $seller_address_2 , $seller_address_3 , $seller_office_name);

		$sql4='SELECT * FROM k2g1_seller WHERE seller_id=? AND seller_name=?' ;
		$login=sql($sql4, false ,$seller_id,$seller_name );

		$_SESSION["user"]['id']=$login[0]["seller_id"];
		$_SESSION["user"]['name']=$login[0]["seller_name"];
		$_SESSION["user"]['type']= 1;
	}
	
	if($_SESSION['pre_page']=='index'){
		header('Location:index.php');
	}elseif($_SESSION['pre_page'] == 'item') {
		header('Location:item.php');
	}
	// */
} else {
	header('Location:mail.php') ;
}
?>
<!DOCTYPE html>            
<html lang ="ja">
    <head>
		<?= $head_common_tag ?>
		<link href ="../css/default.css" media ="all" rel ="stylesheet">
		<title>reg-confirm.php</title>
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
				width  : 40%;
				height : 60px;
			}
		</style>
	</head>
	<body>
		<header>
		<?php echo $header_common_tag ?>
		</header>
		<main>
			<header>
				<h1>登録完了</h1>
			</header>
			<div>
				<!-- 買い手--------------------------------------------------------------------------------------- -->
				<?php if($_SESSION["user"]['type']== 2): ?>
					ID : <?=$login[0]["buyer_id"] ?>
					<script>
						function jump_top(){
							location.href = 'index.php';
						}
						function jump_buyer_mypage(){
							location.href = 'buyer_mypage.php';
						}
					</script>
					<input type ='button' value ='topページへ'  onclick ='jump_top()'>
					<input type ='button' value ='購入者ページへ'  onclick ='jump_buyer_mypage()'>
				<!-- 売り手----------------------------------------------------------------------------- -->
				<?php elseif($_SESSION["user"]['type']== 1): ?>				
					ID : <?=$login[0]["seller_id"] ?>
					<script>
						function jump_seller_mypage(){
							location.href = 'seller_mypage.php';
						}
					</script>
					<input type ='button' value ='topページへ'  onclick ='jump_top()'>
					<input type ='button' value ='出品者ページへ'  onclick ='jump_seller_mypage()'>
				<?php endif;?>		
			</div>
		</main>
		<footer>
			<?= $footer_common_tag ?>
		</footer>
	</body>
</html>