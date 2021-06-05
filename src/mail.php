<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成　：秋野浩朗（web1902)
概要　：新規登録時のメール送信フォーム
更新日：2020/1/31

----------------------------------------------------------------------------------------- */



/* ----- 共通処理 ----------------------------------------------------------------------- */

include '../inc/config.php';
include '../inc/db_access.php';
include '../inc/functions.php';
include '../inc/variables.php';

// ここで使うinclude したファイルの変数。（必要に応じて var_dump で確認）
$pflag;					// 中身：false
$head_common_tag;		// 中身：head タグ内で規定するメタタグとか
$header_common_tag;		// 中身：header タグ内で規定するタイトルタグとか
$footer_common_tag;		// 中身：footer タグ内で規定するタイトルタグとか

// ここで使う変数の初期化。
$mail_exist	= false;
$mail_to	= "";
$mail_url	= "./register.php";									// 【要変更！】取り敢えずローカルパス
$mail_url2	= "https://www.g096407.shop/2019/k2/register.php";	// 秋野個人のレンタルサーバの絶対パス
$url_token1	= "";
$url_token2	= "";
$url_token3	= "";
$pin_code	= "";
$mail_title	= "【中古家電.com】ご本人様 確認メール";
$mail_text	= "";
$mail_err 	= false;
$mail_text1	= "
	本メールは「中古家電.com」の新規登録におけるご本人様確認メールとなります。
	ご本人様確認の為、以下の「URLリンク」より弊社の会員登録ページにアクセス下さい。\n\n"
;
$mail_text2	= "
	※ メールが送信されてから30分が経過すると、上記URLは無効になります。
	※ URLが無効になった場合は、弊社WEBページよりメールを再送信下さい。
	※ 上記URLをクリックしても登録フォームに遷移しない場合は、お手数ですがURL全文をコピー&ペーストの上、WEBブラウザより直接アクセス下さい。
	※ 既にメールが送信されている場合は御了承下さい。
	※ 本メールに心当たりがない場合は本メールを破棄して下さい。
	※ 本メールは送信専用アドレスから送信しており、ご返信頂いても対応致しかねますので御了承下さい。\n
	南大阪義専 赤池班"
;
//【！絶対削除！】検証用でメールを模擬的に表示させるページのタメの処理【！絶対削除！】
$mail_text2	= "
	※ メールが送信されてから30分が経過すると、上記URLは無効になります。<br>
	※ URLが無効になった場合は、弊社WEBページよりメールを再送信下さい。<br>
	※ 上記URLをクリックしても登録フォームに遷移しない場合は、お手数ですがURL全文をコピー&ペーストの上、WEBブラウザより直接アクセス下さい。<br>
	※ 既にメールが送信されている場合は御了承下さい。<br>
	※ 本メールに心当たりがない場合は本メールを破棄して下さい。<br>
	※ 本メールは送信専用アドレスから送信しており、ご返信頂いても対応致しかねますので御了承下さい。<br>
	南大阪義専 赤池班"
;
$mail_text3	= "
	本メールは「中古家電.com」の新規登録におけるご本人様確認メールとなります。
	ご本人様確認の為、以下の４桁の「暗証番号」を弊社サイトに入力下さい。\n\n"
;
$mail_text4	= "
	※ メールが送信されてから30分が経過すると、上記の暗証番号は無効になります。
	※ 暗証番号が無効になった場合は、弊社WEBページよりメールを再送信下さい。
	※ 既にメールが送信されている場合は御了承下さい。
	※ 本メールに心当たりがない場合は本メールを破棄して下さい。
	※ 本メールは送信専用アドレスから送信しており、ご返信頂いても対応致しかねますので御了承下さい。\n
	南大阪義専 赤池班"
;

// ここで使うSQL文の一覧表示と配列変数への設定。
$sql_array = array(
	// minute second
	"mail1"	=> "select mail_hash from k2g1_mail where mail_hash = ?",
	"mail2"	=> "update k2g1_mail set mail_limit_time = (now() + interval 10 second) where mail_hash = ?",
	"mail3"	=> "insert into k2g1_mail values(?,'',now() + interval 10 second)",
	"mail4"	=> "update k2g1_mail set mail_pin_code = ? , mail_limit_time = (now() + interval 10 second) where mail_hash = ?",
	"mail5"	=> "insert into k2g1_mail values(?,?,now() + interval 10 second)"
);

/* --------------------------------------------------------------------------------------- */



if($_SERVER["REQUEST_METHOD"] == "POST"){
	$pflag = true;
	if($_POST["mail_to"] != ""){
		$mail_to	= htmlspecialchars($_POST["mail_to"],ENT_QUOTES);
		// 毎回同じ値を返して非可逆でURLエンコードが不要なハッシュを使用（特に機密性が高いものではない為）
		$url_token1	= hash('sha256',$mail_to);
		var_dump(sql($sql_array["mail1"],true,$url_token1));
		if(sql($sql_array["mail1"],true,$url_token1) != ""){
			$mail_exist = true;
		}
		switch($_POST["type"]){
			case "same":
				// メールにURL記載してそこからアクセスするパターン
				// メール内でのURL記載方法について https://asumeru.net/url_note
				$url_token2	= $_SESSION["pre_page"];
				if(isset($_SESSION["item"])){
					$url_token3	= $_SESSION["item"]["item_id"];
				}
				if($url_token3 != ""){
					$mail_url  .= '?hash='.$url_token1.'&page='.$url_token2.'&item='.$url_token3;
				}else{
					$mail_url  .= '?hash='.$url_token1.'&page='.$url_token2;
				}
				// 本番で使用する場合はコレ！
				// $mail_text .= $mail_text1 . $mail_url . "\n" . $mail_text2;
				
				//【！絶対削除！】検証用でメールを模擬的に表示させるページのタメの処理【！絶対削除！】
				$mail_text .= $mail_text1 . "<br><a href={$mail_url}>" . $mail_url . "</a><br>" . $mail_text2;
				
				if($mail_exist){
					sql($sql_array["mail2"],false,$url_token1);
				}else{
					sql($sql_array["mail3"],false,$url_token1);
				}
				//【！絶対削除！】検証用でメールを模擬的に表示させるページのタメの処理【！絶対削除！】
				$_SESSION["mail"]["mail_to"] 	= $mail_to;
				$_SESSION["mail"]["mail_title"] = $mail_title;
				$_SESSION["mail"]["mail_text"] 	= $mail_text;
				break;
			case "different":
				// メールに暗証番号を記載して画面に入力させるパターン
				// 4桁のランダムな数値を生成
				for($i = 0 ; $i < 4 ; $i++){
					$pin_code .= rand(0,9);
				}
				$mail_text .= $mail_text3 . $pin_code . "\n" . $mail_text4;
				if($mail_exist){
					sql($sql_array["mail4"],false,$pin_code,$url_token1);
				}else{
					sql($sql_array["mail5"],false,$url_token1,$pin_code);
				}
				// 暗証番号形式の時は 端末/ブラウザ が変わらないので「ハッシュ値」と「暗証番号」をセッションで飛ばす必要がある。
				$_SESSION["mail"]["mail_hash"]		= $url_token1;
				$_SESSION["mail"]["mail_pin_code"]	= $pin_code;
				break;
		}
		mb_language("Japanese");
		mb_internal_encoding("UTF-8");
		if(mb_send_mail($mail_to,$mail_title,$mail_text)){
			// 送信完了
			if($_POST["type"] == "same"){
				// 本番はこっち
				//header("Location:mail_sent.php?type=same");
				// 【！絶対削除！】実際のメール内容を擬似的に表現したページに遷移【！絶対削除！】
				header("Location:mail_text.php");
				exit;
			}else{
				header("Location:mail_sent.php?type=different");
				exit;
			}
		}else{
			// 送信失敗時
			$mail_err = true;
			$err_msg .= $err_array["all"];
			$err_msg .= $err_array["mail2"];
			//【！絶対削除！】検証用なので強制的に成功させる事にする。【！絶対削除！】
			if($_POST["type"] == "same"){
				//【！絶対削除！】実際にメールが送られた後の画面に遷移【！絶対削除！】
				// header("Location:mail_sent.php?type=same");
				// exit;
				//【！絶対削除！】実際のメール内容を擬似的に表現したページに遷移【！絶対削除！】
				header("Location:mail_text.php");
				exit;
			}else{
				//【！絶対削除！】実際にメールが送られた後の画面に遷移【！絶対削除！】
				header("Location:mail_sent.php?type=different");
				exit;
			}
		}
	}else{
		$err_msg .= $err_array["all"];
		$err_msg .= $err_array["mail1"];
	}
}

// GETかPOSTにかかわらず必要な処理をここ以降で書く


?>

<!DOCTYPE html>
<html lang="ja">
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#">
	<?= $head_common_tag ?>
	<script>
		window.onload = function(){
			const mail_li_1_tag = document.getElementById("mail_div_1");
			const mail_li_2_tag = document.getElementById("mail_div_2");
			const mail_form_1_tag = document.getElementById("mail_form_1");
			const mail_form_2_tag = document.getElementById("mail_form_2");
			mail_form_1_tag.classList.add('output');
			mail_form_2_tag.classList.add('not_output');
			mail_li_1_tag.addEventListener("click",tab_change,false);
			mail_li_2_tag.addEventListener("click",tab_change,false);
		}
		const tab_change = function(){
			const mail_form_1_tag = document.getElementById("mail_form_1");
			const mail_form_2_tag = document.getElementById("mail_form_2");
			switch(event.target.id){
				case "mail_div_1":
					mail_form_1_tag.classList.add('output');
					mail_form_1_tag.classList.remove('not_output');
					mail_form_2_tag.classList.add('not_output');
					mail_form_2_tag.classList.remove('output');
					break;
				case "mail_div_2":
					mail_form_1_tag.classList.add('not_output');
					mail_form_1_tag.classList.remove('output');
					mail_form_2_tag.classList.add('output');
					mail_form_2_tag.classList.remove('not_output');
					break;
			}
		}
	</script>
	<style>
		.mail_section_1{
			display : flex;
			margin	: auto;
		}
		.mail_div_1{
			height			: 60px;
			width			: calc(100%/2);
			list-style		: none;
			border-radius	: 5px 5px 0px 0px;
			display			: inline-block;
			text-align		: center;
			vertical-align	: middle;
			transition		: all 0.3s ease;
		}
		.mail_div_1:hover{
			font-weight	: bold;
		}
		#mail_div_1{
			background-color : var(--color-light-navy);
			color			 : var(--color-light-orange);
			text-align		 : center;
			padding			 : 0.5em 0 0 0;
		}
		#mail_div_2{
			background-color : var(--color-light-orange);
			color			 : var(--color-light-navy);
			text-align		 : center;
			padding			 : 0.5em 0 0 0;
		}
		.mail_form_1{
			border			: 5px solid var(--color-light-navy);
			box-shadow		: 4px 4px 6px gray;
			font-size		: var(--font-small);
		}
		.mail_form_2{
			border			: 5px solid var(--color-light-orange);
			box-shadow		: 4px 4px 6px gray;
			font-size		: var(--font-small);
		}
		.mail_font_1,
		.mail_font_2{
			font-size : 1.1em;
			color	  : red;
		}
		#mail_to_1:hover{
			background-color : lightpink;
		}
		#mail_to_2:hover{
			background-color : paleturquoise;
		}
		.output{
			display : block;
		}
		.not_output{
			display : none;
		}
		#seller_id:hover,
		#seller_password:hover{
			background-color : var(--color-light-orange);
		}
	</style>
	<title>メール送信フォーム</title>
</head>
<body>
	<?php include '../inc/var_dump.php' ?>
	<header>
		<?= $header_common_tag ?>
	</header>
	<main>
		<section>
			<header>
				<?php
					if(isset($_GET["type"])){
						switch($_GET["type"]){
							case "same":
								echo "<h2>URLの有効期限が無効になりました</h2>";
								$err_msg .= $err_array["all"];
								$err_msg .= $err_array["mail3"];
								break;
							case "different":
								echo "<h2>暗証番号の有効期限が無効になりました</h2>";
								$err_msg .= $err_array["all"];
								$err_msg .= $err_array["mail4"];
								break;
							case "deleted":
								echo "<h2>既に送信されたメールのURLにアクセス済みです。</h2>";
								$err_msg .= $err_array["all"];
								$err_msg .= $err_array["mail5"];
								break;
							default:
								echo "<h2>本人確認用メール送信フォーム</h2>";
								$err_msg .= $err_array["all"];
								$err_msg .= $err_array["mail3"];	
								break;
						}
					}else{
						if($mail_err){
							echo "<h2>メールの送信に失敗しました</h2>";
						}else{
							echo "<h2>本人確認用メール送信フォーム</h2>";
						}
					}
				?>
			</header>
			<span class="err"><?= $err_msg ?></span>
			<br>
			<?php 
				if(!isset($_GET["type"]) && !$mail_err){
					echo "<p>大変お手数ですが、下記入力フォームよりご本人様確認用メールを送信下さい。</p>";
				}
			?>
			<p>【ご注意】</p>
			<ul>
				<li><font class="mail_font_1">本サイトは、南大阪技専校における課題制作の一貫で作成した「デモサイト」となります。</font></li>
				<li>本サイトに掲載されている商品は実際に<font class="mail_font_1">購入できません。</font></li>
				<li>本サイトには適切なセキュリティ対策を施しておりますが、会員登録等に関しまして、個人情報の入力はお控え下さい。</li>
				<li>会員登録にあたり、本人確認の為のメールを送信致しますので、迷惑メール対策の設定変更をお願いいたします。</li>
				<li>なお、<font class="mail_font_1">入力頂いたメールアドレスはデータベースには登録されません。</font></li>
				<li>本サイトのご利用にあたっては上記に同意頂いたものとみなします。</li>
				<li>メールに記載されている「URLリンク」をクリック、もしくは「４桁の番号」を送信ボタン押下後に表示される画面に入力下さい。</li>
			</ul>
			<section class="mail_section_1">
				<div id="mail_div_1" class="mail_div_1"> メールを現在使用している端末で受信する場合 </div>
				<div id="mail_div_2" class="mail_div_1"> メールを現在とは別の端末で受信する場合 </div>
			</section>
			<section>
				<form id="mail_form_1" class="mail_form_1" action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="POST">
					<p>【！ご注意！】こちらは「<font class="mail_font_1">現在使用している端末</font>」でメールを受信する場合のお手続きになります。</p>	
					<p>ご登録までの流れ</p>
					<ol>
						<li>本画面にてメールアドレスを入力</li>
						<li>「<font class="mail_font_1">現在使用している端末</font>」で弊社からのメールを受信</li>
						<li>「<font class="mail_font_1">30分以内</font>」にメールに記載されている「<font class="mail_font_1">URL</font>」より弊社サイトにアクセス</li>
						<li>アクセス先の画面にて、所定の項目を入力して会員登録を行う</li>
					</ol>
					<p>
						メールアドレス：<?= create_input("text","mail_to_1","mail_to","","30",$mail_to,"","","Emailアドレスを入力ください") ?>
						<?= create_input("submit","","btn","","20","メール送信","","","") ?>
						<?= create_input("hidden","hidden_1","type","","20","same","","","") ?>
					</p>
				</form>
				<form id="mail_form_2" class="mail_form_2" action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="POST">
					<p>【！ご注意！】こちらは現在使用している端末とは「<font class="mail_font_2">別の端末</font>」でメールを受信する場合のお手続きになります。</p>	
					<p>ご登録までの流れ</p>
					<ol>
						<li>本画面にてメールアドレスを入力</li>
						<li>現在使用している端末とは「<font class="mail_font_2">別の端末</font>」で弊社からのメールを受信</li>
						<li>「<font class="mail_font_2">30分以内</font>」にメールに記載されている「<font class="mail_font_2">暗証番号</font>」をメール送信後の画面に入力</li>
						<li>移動先の画面にて、所定の項目を入力して会員登録を行う</li>
					</ol>
					<p>
						メールアドレス：<?= create_input("text","mail_to_2","mail_to","","30",$mail_to,"","","Emailアドレスを入力ください") ?>
						<?= create_input("submit","","btn","","20","メール送信","","","") ?>
						<?= create_input("hidden","hidden_2","type","","20","different","","","") ?>
					</p>
				</form>
			</section>
		</section>
	</main>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
</body>
</html>