<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);
// 変数の初期化
$clean = array();
$error = array();
// サニタイズ
if( !empty($_POST) ) {
	foreach( $_POST as $key => $value ) {
		$clean[$key] = htmlspecialchars( $value, ENT_QUOTES);
	} 
}
// 文字成型
$clean['tel'] = str_replace(array('-', 'ー', '−', '―', '‐'), '', $clean['tel']);
$clean['tel'] = str_replace(array(" ", "　"), "", $clean['tel']);
$clean['tel'] = mb_convert_kana($clean['tel'], "n");
$clean['email'] = str_replace(array(" ", "　"), "", $clean['email']);
$clean['email'] = mb_convert_kana($clean['email'], "askhc");
$clean['verifyemail'] = str_replace(array(" ", "　"), "", $clean['verifyemail']);
$clean['verifyemail'] = mb_convert_kana($clean['verifyemail'], "askhc");

if( !empty($clean['btn_confirm'])) {
	$error = validation($clean);

	if( empty($error) ) {
		$page_flag = 1;
		// セッションの書き込み
		session_start();
		$_SESSION['page'] = true;		
	}

} elseif( !empty($clean['btn_submit']) ) {
	session_start();
	if( !empty($_SESSION['page']) && $_SESSION['page'] === true ) {
		// セッションの削除
		unset($_SESSION['page']);
		$page_flag = 2;
		// 変数とタイムゾーンを初期化
		$header = null;
		$body = null;
		$admin_body = null;
		$auto_reply_subject = null;
		$auto_reply_text = null;
		$admin_reply_subject = null;
		$admin_reply_text = null;
		date_default_timezone_set('Asia/Tokyo');
		
		//日本語の使用宣言
		mb_language("ja");
		mb_internal_encoding("UTF-8");
	
		$header = "MIME-Version: 1.0\n";
		$header = "Content-Type: multipart/mixed;boundary=\"__BOUNDARY__\"\n";
		$header .= "From: hipetest@bpoc.co.jp\n";
		$header .= "Reply-To: hipetest@bpoc.co.jp\n";
	
		// 件名を設定
		$auto_reply_subject = 'RIGHT CORE SKILLTEST ADMIN EMAIL';
	
		// 本文を設定
		$auto_reply_text .= $clean['person_name'] . "様\n\n";
		$auto_reply_text .= "-----以下送信内容-----\n";
		$auto_reply_text .= "お名前:" . $clean['name'] . "\n";
		$auto_reply_text .= "フリガナ:" . $clean['frigana'] . "\n";
		$auto_reply_text .= "電話番号:" . $clean['tel'] . "\n";
		$auto_reply_text .= "メールアドレス:" . $clean['email'] . "\n";
		$auto_reply_text .= "ご希望の返信先:" . $clean['desiredreply'] . "\n";
		$auto_reply_text .= "お問い合わせ項目:" . $clean['inquiryitems'] . "\n";
		$auto_reply_text .= "ご希望の返信先:" . nl2br($clean['message']) . "\n";
		$auto_reply_text .= "----------------------------\n\n";
		// テキストメッセージをセット
		$body = "--__BOUNDARY__\n";
		$body .= "Content-Type: text/plain; charset=\"ISO-2022-JP\"\n\n";
		$body .= $auto_reply_text . "\n";
		$body .= "--__BOUNDARY__\n";
	
		// 自動返信メール送信
		mb_send_mail( $clean['email'], $auto_reply_subject, $body, $header);
	

		// 運営側へ送るメールの件名
		$admin_reply_subject = "RIGHT CORE SKILLTEST AUTO REPLY EMAIL";
	
		// 本文を設定
		$admin_reply_text .= "-----以下送信内容--------\n";
		$admin_reply_text .= "お問い合わせ内容: ";
		$admin_reply_text .= "お名前: " . $clean['name'] . "\n";
		$admin_reply_text .= "フリガナ: " . $clean['frigana'] . "\n";
		$admin_reply_text .= "電話番号:" . $clean['tel'] . "\n";
		$admin_reply_text .= "メールアドレス" . $clean['email'] . "\n";
		$admin_reply_text .= "ご希望の返信先" . $clean['desiredreply'] . "\n";
		$admin_reply_text .= "お問い合わせ項目" . $clean['inquiryitems'] . "\n";
		$admin_reply_text .= "お問い合わせ項目:" . nl2br($clean['message']) . "\n";
		$admin_reply_text .= "----------------------------\n\n";
		
		// テキストメッセージをセット
		$body = "--__BOUNDARY__\n";
		$body .= "Content-Type: text/plain; charset=\"ISO-2022-JP\"\n\n";
		$body .= $admin_reply_text . "\n";
		$body .= "--__BOUNDARY__\n";
	
		// 管理者へメール送信
		mb_send_mail('markariel.maata@bpoc.co.jp,', $admin_reply_subject, $body, $header);
		
	} else {
		$page_flag = 0;
	}	
}
function validation($data) {
	$error = array();

	// Check  name
	if( empty($data['name']) ) {
		$error['name'] = "「企業名」は入力必須項目です。";
	} elseif( 20 < mb_strlen($data['name']) ) {
		$error['name'] = "20文字以内で入力してください。";
	}

	// Check frigana name
	if( empty($data['frigana']) ) {
		$error['frigana'] = "「企業名」は入力必須項目です。";
	} elseif( 20 < mb_strlen($data['frigana']) ) {
		$error['frigana'] = "20文字以内で入力してください。";
	}


	// 電話番号のバリデーション
	if( empty($data['tel'])) {
		$error['tel'] = "「電話番号」は入力必須項目です。";
	} elseif( !preg_match( '/^[0-9]+[0-9.-]+$/', $data['tel'])) {
		$error['tel'] = "正しい形式で入力してください。";
	}

	// Check email
	if( empty($data['email']) ) {
		$error['email'] = "「メールアドレス」は入力必須項目です。";
	} elseif( !preg_match( '/^[0-9a-z_.\/?-]+@([0-9a-z-]+\.)+[0-9a-z-]+$/', $data['email']) ) {
		$error['email'] = "正しい形式で入力してください。";
	}

	// Check email
	if( empty($data['verifyemail']) ) {
		$error['verifyemail'] = "「メールアドレス」は入力必須項目です。";
	} elseif( !preg_match( '/^[0-9a-z_.\/?-]+@([0-9a-z-]+\.)+[0-9a-z-]+$/', $data['verifyemail']) ) {
		$error['verifyemail'] = "正しい形式で入力してください。";
	}

	if(trim(strtolower($data['email'])) != trim(strtolower($data['verifyemail']))){
		$error['emailmatch'] = "「メールアドレス」は同じではありません。";
	}

	// CHECK DESIRED REPLY
	if( empty($data['desiredreply']) ) {
		$error['desiredreply'] = "「お問い合わせ内容」は入力必須項目です。";
	}

	// CHECK DESIRED REPLY
	if( empty($data['inquiryitems']) ) {
		$error['inquiryitems'] = "「お問い合わせ内容」は入力必須項目です。";
	}

	if(empty($data['message'])) {
		$error['message'] = "「備考・ご質問等」は入力必須項目です。";
	}

	// プライバシーポリシー同意のバリデーション
	if( empty($data['agreement']) ) {
		$error['agreement'] = "プライバシーポリシーをご確認ください。";
	} elseif( (int)$data['agreement'] !== 1 ) {
		$error['agreement'] = "プライバシーポリシーをご確認ください。";
	}

	return $error;
}
?>

<?php if( $page_flag === 1 ):
	// 確認画面読み込み
require_once(dirname(__FILE__)."/inc/confirm.html");
 ?>
<?php elseif( $page_flag === 2 ):
	// サンクスページへリダイレクト
// $url = "https://www.e-vision.co.jp/lp/inc/thanks.php";
// header('Location: ' . $url, true, 301);
require_once(dirname(__FILE__)."/inc/thanks.html");
exit;
 ?>
<?php else:
	// フォーム画面読み込み
require_once(dirname(__FILE__)."/inc/form.html");
 ?>
<?php endif; ?>
