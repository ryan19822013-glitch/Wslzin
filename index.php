<?php
// index.php - Gateway Checker para Railway
// Sistema otimizado para funcionar com arquivos locais

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Configuração
$apis = [
    'paypal' => [
        'name' => 'PayPal Checker',
        'file' => 'api.php',
        'description' => 'Validação via PayPal'
    ],
    'xikids' => [
        'name' => 'Xikids Checker',
        'file' => 'apicagadanocureturn119.php',
        'description' => 'Validação via Xikids'
    ],
    'remnanthouse' => [
        'name' => 'Remnant House Checker',
        'file' => 'apifudida.php',
        'description' => 'Validação via Remnant House'
    ],
    'pichau' => [
        'name' => 'Pichau Login Checker',
        'file' => 'apiloginpichauretornandoquantypedido.php',
        'description' => 'Validação de credenciais Pichau'
    ],
    'cakto' => [
        'name' => 'Cakto Checker (com Proxy)',
        'file' => 'apiretornowsluseproxy.php',
        'description' => 'Validação via Cakto com proxy'
    ],
    'cakto_noproxy' => [
        'name' => 'Cakto Checker (sem Proxy)',
        'file' => 'apiretornowslsemproxy.php',
        'description' => 'Validação via Cakto sem proxy'
    ],
    'iugu' => [
        'name' => 'Iugu/Close.fans Checker',
        'file' => 'iugu.php',
        'description' => 'Validação via Iugu e Close.fans'
    ],
    'gaylife' => [
        'name' => 'GayLife Magazine Checker',
        'file' => 'hot.php',
        'description' => 'Validação via GayLife Magazine (Stripe)'
    ],
    'paypal3' => [
        'name' => 'PayPal v3 Checker',
        'file' => 'paypal3.php',
        'description' => 'Validação PayPal v3'
    ],
    'paypal1' => [
        'name' => 'PayPal v1 Checker',
        'file' => 'paypal1.php',
        'description' => 'Validação PayPal v1'
    ],
    'stripeauth' => [
        'name' => 'Stripe Auth Checker',
        'file' => 'stripeauth.php',
        'description' => 'Validação Stripe (Tomhegna)'
    ],
    'everyorg' => [
        'name' => 'Every.org Checker',
        'file' => 'stripeadd0.php',
        'description' => 'Validação via Every.org'
    ],
    'stripe_gringa' => [
        'name' => 'Stripe Gringa Checker',
        'file' => 'stripeauthgringa.php',
        'description' => 'Validação Stripe (RedBlueChair)'
    ]
];

// Processar requisição AJAX
if (isset($_GET['action']) && $_GET['action'] == 'check') {
    header('Content-Type: application/json');
    
    $api_id = $_POST['api'] ?? $_GET['api'] ?? '';
    $cards = $_POST['cards'] ?? $_GET['lista'] ?? '';
    
    if (empty($api_id) || !isset($apis[$api_id])) {
        echo json_encode(['error' => 'API inválida']);
        exit;
    }
    
    if (empty($cards)) {
        echo json_encode(['error' => 'Nenhum cartão fornecido']);
        exit;
    }
    
    $api_file = $apis[$api_id]['file'];
    
    if (!file_exists($api_file)) {
        echo json_encode(['error' => "Arquivo da API não encontrado: $api_file"]);
        exit;
    }
    
    // Processar múltiplos cartões (um por linha)
    $lines = explode("\n", trim($cards));
    $results = [];
    $stats = ['live' => 0, 'die' => 0, 'error' => 0];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Chamar a API específica (versão local para Railway)
        $result = callLocalApi($api_file, $line);
        $results[] = $result;
        
        // Atualizar estatísticas
        if (stripos($result, '✅') !== false || 
            stripos($result, 'APROVADA') !== false || 
            stripos($result, 'LIVE') !== false ||
            stripos($result, 'Aprovada') !== false) {
            $stats['live']++;
        } elseif (stripos($result, '❌') !== false || 
                  stripos($result, 'REPROVADA') !== false || 
                  stripos($result, 'DIE') !== false ||
                  stripos($result, 'Reprovada') !== false) {
            $stats['die']++;
        } else {
            $stats['error']++;
        }
        
        // Pequeno delay entre requisições
        usleep(500000);
    }
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'results' => $results
    ]);
    exit;
}

// Função para testar API (ping simples)
if (isset($_GET['action']) && $_GET['action'] == 'test_api') {
    header('Content-Type: application/json');
    $file = $_GET['file'] ?? '';
    if ($file && file_exists($file)) {
        echo json_encode(['online' => true]);
    } else {
        echo json_encode(['online' => false]);
    }
    exit;
}

/**
 * Função CORRIGIDA para chamar APIs localmente no Railway
 * Em vez de usar cURL para chamar arquivos locais, usamos include
 */
function callLocalApi($api_file, $card_line) {
    // Verificar se o arquivo existe
    if (!file_exists($api_file)) {
        return "❌ ERRO | $card_line | Arquivo não encontrado: $api_file";
    }
    
    // Limpar buffers de saída anteriores
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Iniciar buffer de saída
    ob_start();
    
    try {
        // Definir a variável GET que a API espera
        $_GET['lista'] = $card_line;
        $_REQUEST['lista'] = $card_line;
        
        // Incluir o arquivo da API
        include($api_file);
        
        // Capturar a saída
        $result = ob_get_clean();
        
        // Se não houver saída, tentar novamente com cURL local
        if (empty(trim($result))) {
            // Fallback: usar cURL para localhost
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $port = $_SERVER['SERVER_PORT'] ?? '8080';
            
            $url = "$protocol://$host:$port/$api_file?lista=" . urlencode($card_line);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $result = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                return "❌ ERRO | $card_line | " . $error;
            }
        }
        
        // Limpar tags HTML da resposta se necessário
        $clean_result = strip_tags($result);
        if (empty(trim($clean_result))) {
            $clean_result = $result;
        }
        
        return trim($clean_result);
        
    } catch (Exception $e) {
        ob_end_clean();
        return "❌ ERRO | $card_line | " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gateway Checker - Railway Edition</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #0d0d1a 0%, #1a1a2e 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        h1 {
            text-align: center;
            color: #00ff88;
            margin-bottom: 10px;
            font-size: 2em;
            text-shadow: 0 0 10px rgba(0,255,136,0.3);
        }
        
        .subtitle {
            text-align: center;
            color: #888;
            margin-bottom: 30px;
        }
        
        .main-grid {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 20px;
        }
        
        @media (max-width: 800px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Sidebar */
        .sidebar, .cards-input, .results, .stats {
            background: rgba(10,10,25,0.7);
            border-radius: 15px;
            padding: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0,255,136,0.2);
        }
        
        .api-selector label, .cards-input label {
            display: block;
            color: #00ff88;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        select {
            width: 100%;
            padding: 12px;
            background: #0a0a1a;
            border: 1px solid #2a2a4a;
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            cursor: pointer;
        }
        
        select:focus {
            outline: none;
            border-color: #00ff88;
        }
        
        .api-info {
            background: #0a0a1a;
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .api-info p {
            color: #aaa;
            font-size: 12px;
            margin-top: 8px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .status-online {
            background: rgba(0,255,136,0.2);
            color: #00ff88;
            border: 1px solid #00ff88;
        }
        
        textarea {
            width: 100%;
            height: 180px;
            padding: 12px;
            background: #0a0a1a;
            border: 1px solid #2a2a4a;
            border-radius: 8px;
            color: #fff;
            font-family: monospace;
            font-size: 13px;
            resize: vertical;
        }
        
        textarea:focus {
            outline: none;
            border-color: #00ff88;
        }
        
        .format-hint {
            color: #666;
            font-size: 11px;
            margin-top: 8px;
        }
        
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(90deg, #00ff88, #00cc66);
            border: none;
            border-radius: 8px;
            color: #0a0a1a;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 15px;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0,255,136,0.3);
        }
        
        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
        }
        
        .stat-card {
            text-align: center;
        }
        
        .stat-card.live .stat-number { color: #00ff88; font-size: 32px; font-weight: bold; }
        .stat-card.die .stat-number { color: #ff4444; font-size: 32px; font-weight: bold; }
        .stat-card.error .stat-number { color: #ffaa00; font-size: 32px; font-weight: bold; }
        
        .stat-label {
            color: #aaa;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #2a2a4a;
        }
        
        .results-header h3 {
            color: #fff;
        }
        
        .clear-btn {
            background: none;
            border: 1px solid #ff4444;
            color: #ff4444;
            padding: 5px 15px;
            font-size: 12px;
            width: auto;
            margin: 0;
        }
        
        .clear-btn:hover {
            background: #ff4444;
            color: #fff;
            transform: none;
            box-shadow: none;
        }
        
        .results-list {
            max-height: 400px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 12px;
        }
        
        .result-item {
            padding: 8px 12px;
            border-bottom: 1px solid #1a1a3a;
            white-space: pre-wrap;
            word-break: break-all;
        }
        
        .result-item.live { color: #00ff88; background: rgba(0,255,136,0.05); }
        .result-item.die { color: #ff4444; background: rgba(255,68,68,0.05); }
        .result-item.error { color: #ffaa00; background: rgba(255,170,0,0.05); }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #00ff88;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #555;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔮 Gateway Checker</h1>
        <div class="subtitle">Railway Edition - Multi API Credit Card Validator</div>
        
        <div class="main-grid">
            <div class="sidebar">
                <div class="api-selector">
                    <label>🎯 Selecione a GATEWAY</label>
                    <select id="apiSelect">
                        <?php foreach ($apis as $id => $api): ?>
                            <option value="<?php echo $id; ?>">
                                <?php echo htmlspecialchars($api['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="api-info">
                    <div id="apiStatus"></div>
                    <p id="apiDesc"></p>
                </div>
            </div>
            
            <div>
                <div class="cards-input">
                    <label>💳 Cartões (um por linha)</label>
                    <textarea id="cardsInput" placeholder="4111111111111111|12|2026|123&#10;5111111111111111|01|2027|456"></textarea>
                    <div class="format-hint">📌 Formato: NUMERO|MES|ANO|CVV (um por linha)</div>
                    <button id="checkBtn">🔍 VERIFICAR CARTÕES</button>
                </div>
            </div>
        </div>
        
        <div class="stats" style="margin-top: 20px;">
            <div class="stat-card live">
                <div class="stat-number" id="liveCount">0</div>
                <div class="stat-label">✅ LIVE / APROVADAS</div>
            </div>
            <div class="stat-card die">
                <div class="stat-number" id="dieCount">0</div>
                <div class="stat-label">❌ DIE / REPROVADAS</div>
            </div>
            <div class="stat-card error">
                <div class="stat-number" id="errorCount">0</div>
                <div class="stat-label">⚠️ ERROS</div>
            </div>
        </div>
        
        <div class="results">
            <div class="results-header">
                <h3>📋 Resultados</h3>
                <button class="clear-btn" id="clearBtn">Limpar</button>
            </div>
            <div class="results-list" id="resultsList">
                <div style="color: #666; text-align: center; padding: 20px;">
                    Aguardando verificação...
                </div>
            </div>
        </div>
        
        <div class="footer">
            Gateway Checker | Railway Edition | Multi API System
        </div>
    </div>
    
    <script>
        const apis = <?php echo json_encode($apis); ?>;
        
        const apiSelect = document.getElementById('apiSelect');
        const apiDesc = document.getElementById('apiDesc');
        const apiStatus = document.getElementById('apiStatus');
        const cardsInput = document.getElementById('cardsInput');
        const checkBtn = document.getElementById('checkBtn');
        const clearBtn = document.getElementById('clearBtn');
        const resultsList = document.getElementById('resultsList');
        const liveCount = document.getElementById('liveCount');
        const dieCount = document.getElementById('dieCount');
        const errorCount = document.getElementById('errorCount');
        
        function updateApiInfo() {
            const selectedId = apiSelect.value;
            const api = apis[selectedId];
            
            if (api) {
                apiDesc.innerHTML = `📄 ${api.description}`;
                apiStatus.innerHTML = '<span class="status-badge status-online">🟢 API Ready</span>';
            }
        }
        
        function addResult(text, type = 'error') {
            const resultDiv = document.createElement('div');
            resultDiv.className = `result-item ${type}`;
            resultDiv.textContent = text;
            resultsList.appendChild(resultDiv);
            resultsList.scrollTop = resultsList.scrollHeight;
        }
        
        function clearResults() {
            resultsList.innerHTML = '';
            liveCount.textContent = '0';
            dieCount.textContent = '0';
            errorCount.textContent = '0';
        }
        
        function updateStats(stats) {
            liveCount.textContent = stats.live;
            dieCount.textContent = stats.die;
            errorCount.textContent = stats.error;
        }
        
        async function checkCards() {
            const api = apiSelect.value;
            const cards = cardsInput.value.trim();
            
            if (!cards) {
                alert('Por favor, insira pelo menos um cartão.');
                return;
            }
            
            const lines = cards.split('\n');
            let validCount = 0;
            for (const line of lines) {
                if (line.trim() && line.match(/\d+\|\d{1,2}\|\d{2,4}\|\d{3,4}/)) {
                    validCount++;
                }
            }
            
            if (validCount === 0) {
                alert('Nenhum cartão válido encontrado. Use: NUMERO|MES|ANO|CVV');
                return;
            }
            
            checkBtn.disabled = true;
            checkBtn.textContent = '⏳ VERIFICANDO...';
            
            clearResults();
            addResult(`🔍 Verificando ${validCount} cartão(ões) via ${apis[api].name}...`, 'error');
            
            const formData = new FormData();
            formData.append('api', api);
            formData.append('cards', cards);
            
            try {
                const response = await fetch('?action=check', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.error) {
                    addResult(`❌ Erro: ${data.error}`, 'error');
                } else if (data.success) {
                    updateStats(data.stats);
                    resultsList.innerHTML = '';
                    
                    for (const result of data.results) {
                        let type = 'error';
                        if (result.includes('✅') || result.includes('APROVADA') || result.includes('LIVE') || result.includes('Aprovada')) {
                            type = 'live';
                        } else if (result.includes('❌') || result.includes('REPROVADA') || result.includes('DIE') || result.includes('Reprovada')) {
                            type = 'die';
                        }
                        addResult(result, type);
                    }
                    
                    addResult(`\n📊 Concluído! LIVE: ${data.stats.live} | DIE: ${data.stats.die} | ERROS: ${data.stats.error}`, 'error');
                }
            } catch (error) {
                addResult(`❌ Erro na requisição: ${error.message}`, 'error');
            } finally {
                checkBtn.disabled = false;
                checkBtn.textContent = '🔍 VERIFICAR CARTÕES';
            }
        }
        
        apiSelect.addEventListener('change', updateApiInfo);
        checkBtn.addEventListener('click', checkCards);
        clearBtn.addEventListener('click', clearResults);
        
        cardsInput.value = `4111111111111111|12|2026|123
5111111111111111|01|2027|456`;
        
        updateApiInfo();
        
        const savedApi = localStorage.getItem('selectedApi');
        if (savedApi && apis[savedApi]) {
            apiSelect.value = savedApi;
            updateApiInfo();
        }
        
        apiSelect.addEventListener('change', () => {
            localStorage.setItem('selectedApi', apiSelect.value);
        });
    </script>
</body>
</html>