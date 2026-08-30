<?php
/**
 * t-ro.jp お問い合わせフォーム メール送信スクリプト
 * tro-contact.html から fetch(POST) で呼ばれ、rino.office.tk@gmail.com へメールを送る。
 */
mb_language('Japanese');
mb_internal_encoding('UTF-8');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo '{"ok":false,"error":"method"}';
  exit;
}

// スパム対策: 人間には見えない欄に入力があれば静かに成功を返して破棄
if (!empty($_POST['website'])) {
  echo '{"ok":true}';
  exit;
}

$ftype    = trim($_POST['ftype']    ?? '');
$fname    = trim($_POST['fname']    ?? '');
$fcompany = trim($_POST['fcompany'] ?? '');
$fphone   = trim($_POST['fphone']   ?? '');
$femail   = trim($_POST['femail']   ?? '');
$fmessage = trim($_POST['fmessage'] ?? '');

if ($fname === '' || $fphone === '' || $femail === '' || $fmessage === '') {
  http_response_code(400);
  echo '{"ok":false,"error":"missing"}';
  exit;
}
if (!filter_var($femail, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo '{"ok":false,"error":"email"}';
  exit;
}
if (mb_strlen($fname) > 100 || mb_strlen($fcompany) > 200 || mb_strlen($fphone) > 30 || mb_strlen($fmessage) > 5000) {
  http_response_code(400);
  echo '{"ok":false,"error":"length"}';
  exit;
}

$to      = 'rino.office.tk@gmail.com';
$subject = '【t-ro.jp】お問い合わせ（' . ($ftype !== '' ? $ftype : '種別未選択') . '）' . $fname . ' 様';

$body  = "t-ro.jp のお問い合わせフォームから送信がありました。\n";
$body .= "----------------------------------------\n";
$body .= "お問い合わせ種別: " . $ftype . "\n";
$body .= "お名前: " . $fname . "\n";
$body .= "会社名: " . ($fcompany !== '' ? $fcompany : '（未記入）') . "\n";
$body .= "電話番号: " . $fphone . "\n";
$body .= "メールアドレス: " . $femail . "\n";
$body .= "----------------------------------------\n";
$body .= "お問い合わせ内容:\n" . $fmessage . "\n";
$body .= "----------------------------------------\n";
$body .= "送信日時: " . date('Y-m-d H:i:s') . "\n";

$fromAddr = 'no-reply@t-ro.jp';
$headers  = 'From: ' . mb_encode_mimeheader('東京リノベオフィスHP', 'UTF-8') . " <{$fromAddr}>\r\n";
$headers .= "Reply-To: {$femail}\r\n";

$ok = mb_send_mail($to, $subject, $body, $headers, '-f ' . $fromAddr);

echo $ok ? '{"ok":true}' : '{"ok":false,"error":"send"}';
