<?php
/* --------------------------------------------------------------------------------------

【基本情報】
作成者 ：西原（web1914)
概要  ：戻るボタン値保持
更新日：2020/02/04

【注意】
作業「前」に前日までのファイルのコピーを作成する。
コピーしたファイルを本日用のフォルダに移す。
コピーしたファイルで本日分の作業を開始する。

----------------------------------------------------------------------------------------- */


session_start();

$_SESSION['register']['buyer_name']=$_POST['buyer_name'];
$_SESSION['register']['buyer_high_postalcode']=$_POST['buyer_high_postalcode'];
$_SESSION['register']['buyer_low_postalcode']=$_POST['buyer_low_postalcode'];
$_SESSION['register']['buyer_address_1']=$_POST['buyer_address_1'];
$_SESSION['register']['buyer_address_2']=$_POST['buyer_address_2'];
$_SESSION['register']['buyer_address_3']=$_POST['buyer_address_3'];
$_SESSION['register']['buyer_id']=$_POST['buyer_id'];

$_SESSION['register']['seller_name']=$_POST['seller_name'];
$_SESSION['register']['seller_high_postalcode']=$_POST['seller_high_postalcode'];
$_SESSION['register']['seller_low_postalcode']=$_POST['seller_low_postalcode'];
$_SESSION['register']['seller_address_1']=$_POST['seller_address_1'];
$_SESSION['register']['seller_address_2']=$_POST['seller_address_2'];
$_SESSION['register']['seller_address_3']=$_POST['seller_address_3'];
$_SESSION['register']['seller_id']=$_POST['seller_id'];
$_SESSION['register']['seller_office_name']=$_POST['seller_office_name'];

// 秋野追記
$_SESSION['register']['back'] = true;

header('Location:register.php');
exit;

?>
<!DOCTYPE html>            
<html lang ="ja">
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
    </body>
</html>
