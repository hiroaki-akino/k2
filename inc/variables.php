<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成　：秋野浩朗（web1902)
概要　：全てのファイルに影響する共通使用変数設定の為のファイル
更新者：秋野
更新日：2020/2/7

【主な処理】
共通使用変数の宣言
各種エラー内容の配列宣言(JSで使うエラーは除く)

----------------------------------------------------------------------------------------- */


/* ----- 共通処理 ----------------------------------------------------------------------- */

// ここで使用する変数の宣言
$err_msg			= "";
$err_array			= array();
$head_common_tag	= "";
$header_common_tag	= "";
$header_index_tag	= "";
$footer_common_tag	= "";

/* --------------------------------------------------------------------------------------- */



// POST送信時の判定変数
// 初期値を false にしとく
$pflag	= false;

// エラーを表示する為の変数
// 必要に応じて、各自のファイルの中で下記エラー変数から該当エラーを選んで代入して使用する。
// sample.php のテンプレ内のh2タグの下にデフォルトで記載してるので必要に応じて内容を変更して使用する。
// エラー出る位置を統一したいという意図。
$err_msg	= "";

// 各種ページで使用するエラーの一覧変数。
// キーは使用するファイル名（拡張子なし）。重複があれば適当に使い回す。随時追加可。
// 使用するときは all を加えて使用する。
// JS処理によるエラーはここにはないのであしからず。
$err_array = array(
	"all"				=> "<i class=\"fas fa-exclamation-triangle\"></i> ",
	"all2"				=> "当サイトに直接ログインがあった為、トップ画面に戻ります。",
	"item1"				=> "購入個数を選択してください。",
	"login1"			=> "アカウント属性を選択して下さい。",
	"login1"			=> "IDとPWの両方を入力して下さい。",
	"login2"			=> "IDもしくはPWが間違っています。",
	"mail1"				=> "メールアドレスを入力して下さい。",
	"mail2"				=> "メールの送信に失敗しました。暫く時間を置いてもう一度送信下さい。",
	"mail3"				=> "メール送信後、30分以内にお客様による操作がございませんでしたので、URLが無効となりました。<br>
							大変お手数をお掛けして申し訳ございませんが、以下より再度メールを送信下さい。",
	"mail4"				=> "メール送信後、30分以内にお客様による操作がございませんでしたので、暗証番号が無効となりました。<br>
							大変お手数をお掛けして申し訳ございませんが、以下より再度メールを送信下さい。",
	"mail5"				=> "既に送信したメールのURLよりアクセスがございましたので、URLが無効となりました。<br>
							再登録を行う場合は、お手数ですが、以下より再度メールを送信下さい。",
	"mail_sent1"		=> "暗証番号を入力して下さい。",
	"mail_sent2"		=> "入力した番号に誤りがあります。",
	"register1"			=> "必要項目を全て入力して下さい。",
	"seller_mypage1"	=> "在庫数に変動があった為、処理を取り消しました。（最新の個数を表示）",
	"evaluate_form1"	=> "コメントと評価を入力して下さい。"
);

// 共有の head 内のタグ
$head_common_tag .= "<meta charset=\"UTF-8\">";
$head_common_tag .= "<meta name=\"robots\" content=\"index,follow,snippet,archive\">";						// 以降SEO対策
$head_common_tag .= "<meta property=\"og:site_name\" content=\"中古家電.com\"/>";
$head_common_tag .= "<meta property=\"og:title\" content=\"中古家電.com【公式】\"/>";
$head_common_tag .= "<meta property=\"og:type\" content=\"article\" />";									// index のみ website と記載する。
$head_common_tag .= "<meta property=\"og:url\" content=\"http://websystem.rulez.jp/19/web19g1/src/\" />";
$head_common_tag .= "<meta property=\"og:image\" content=\"../img/fav_flog.png\" />";
$head_common_tag .= "<meta property=\"og:description\" 
					content=\"中古家電を取扱う話題の新星ECサイト！取扱う商品の量と質は業界トップクラス！\"/>";
$head_common_tag .= "<meta property=\"og:locale\" content=\"ja_JP\" />";
$head_common_tag .= "<meta name=\"format-detection\" content=\"telephone=no\">";
$head_common_tag .= "<meta name=\"keywords\" content=\"中古家電,家電,フリーマーケット,パソコン,カメラ\">";
$head_common_tag .= "<meta name=\"description\" 
					content=\"中古家電を取扱う話題の新星ECサイト！取扱う商品の量と質は業界トップクラス！\">";
$head_common_tag .= "<link rel=\"icon\" type=\"png\" href=\"../img/fav_flog.png\" >";
$head_common_tag .= "<link rel=\"stylesheet\" href=\"../css/default.css\" >";
$head_common_tag .= "<link href=\"https://use.fontawesome.com/releases/v5.6.1/css/all.css\" rel=\"stylesheet\">";
$head_common_tag .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">";

// 共有の header 内のタグ
$header_common_tag .= "<h1>中古家電.com</h1>";
if(isset($_SESSION["user"])){
	// ユーザがゲストの時は右上の表示は「トップに戻る」
	if($_SESSION["user"]["id"] == "guest"){
		$header_common_tag .= "<p>ゲスト 様</p>";
		$header_common_tag .= "<a id=\"a1\" href=\"index.php\">トップに戻る</a>";
	}else{
		// 既にログインしている場合は「ログアウト（index.php に戻る）」を表示する。
		$header_common_tag .= "<p>{$_SESSION["user"]["name"]} 様</p>";
		$header_common_tag .= "<div>";
		$header_common_tag .= "<a id=\"a1\" href=\"index.php\">トップに戻る</a>";
		$header_common_tag .= "<br>";
		$header_common_tag .= "<a id=\"a2\" href=\"index.php?logout=true\">ログアウト</a>";
		$header_common_tag .= "</div>";
	}
}

// index.php の header 内のタグ
$header_index_tag .= "<h1>中古家電.com</h1>";
if(isset($_SESSION["user"])){
	// 初回アクセス時は「トップに戻る」を表示する。
	if($_SESSION["user"]["id"] == "guest" || (isset($_GET['logout']) && $_GET['logout']) ){
		$header_index_tag .= "<p>ゲスト 様</p>";
	}else{
		// 既にログインしている場合は「ログアウト（index.php に戻る）」を表示する。
		$header_index_tag .= "<p>{$_SESSION["user"]["name"]} 様</p>";
		$header_index_tag .= "<a id=\"a1\" href=\"index.php?logout=true\">ログアウト</a>";
	}
}

// 共有の footer 内のタグ
if(isset($_SESSION["user"])){
	$footer_common_tag .= "<p>お問い合わせは<a href=\"mailto:minami.gisen@gmail.com
							?subject=お問い合わせ&amp;body=----------------------------------------%0D%0A
							会員ID：{$_SESSION["user"]["name"]} 様%0D%0A当項目は削除しないで下さい。%0D%0A
							----------------------------------------%0D%0A 以降にお問い合わせ内容を記載下さい。\">コチラ</a></p>";
}else{
	$footer_common_tag .= "<p>お問い合わせは<a href=\"mailto:minami.gisen@gmail.com
							?subject=お問い合わせ&amp;body=----------------------------------------%0D%0A
							会員ID：ゲスト様%0D%0A当項目は削除しないで下さい。%0D%0A
							----------------------------------------%0D%0A 以降にお問い合わせ内容を記載下さい。\">コチラ</a></p>";
}
$footer_common_tag .= "<i class=\"far fa-copyright\"></i>";
$footer_common_tag .= "<small> 2020 Team Akaike</small>";

?>