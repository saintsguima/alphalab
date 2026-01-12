<?php
require_once 'Globals/globals.php';
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Configurações
$UPLOAD_DIR  = __DIR__ . '/uploadscr'; // pasta física
$PUBLIC_PATH = 'uploadscr';            // rota pública
$MAX_BYTES   = 5 * 1024 * 1024;        // 5 MB (server-side)

// Extensões e MIME types permitidos (validação dupla)
$ALLOWED = [
    'csv' => 'text/csv',
];

function redirect_with($params)
{
    // Redireciona de volta para o form com mensagem
    $qs = http_build_query($params);
    header("Location: crud-cr.php?$qs");
    exit;
}

// Verifica existência do arquivo
if (! isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] === UPLOAD_ERR_NO_FILE) {
    redirect_with(['err' => 'Nenhum arquivo enviado.']);
}

// Trata erros nativos do PHP
$errCode = $_FILES['arquivo']['error'];
if ($errCode !== UPLOAD_ERR_OK) {
    $map = [
        UPLOAD_ERR_INI_SIZE   => 'Arquivo excede o limite do php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'Arquivo excede o limite do formulário.',
        UPLOAD_ERR_PARTIAL    => 'Upload feito parcialmente.',
        UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária ausente no servidor.',
        UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever o arquivo no disco.',
        UPLOAD_ERR_EXTENSION  => 'Upload bloqueado por extensão do PHP.',
    ];
    $msg = $map[$errCode] ?? "Erro de upload (código $errCode).";
    redirect_with(['err' => $msg]);
}

$tmpPath  = $_FILES['arquivo']['tmp_name'];
$origName = $_FILES['arquivo']['name'];
$size     = $_FILES['arquivo']['size'];

// Tamanho (server-side)
if ($size > $MAX_BYTES) {
    redirect_with(['err' => 'Arquivo excede 5 MB.']);
}

// Garante que é um upload válido
if (! is_uploaded_file($tmpPath)) {
    redirect_with(['err' => 'Arquivo inválido.']);
}

// Descobre extensão pela nome e MIME real pela assinatura
$ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

// Detecta MIME real
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($tmpPath) ?: 'application/octet-stream';

// Valida extensão e MIME
if (! array_key_exists($ext, $ALLOWED)) {
    redirect_with(['err' => 'Extensão não permitida.']);
}
// if ($ALLOWED[$ext] !== $mime) {
//   // Alguns PNGs/JPGs podem retornar variações (ex.: image/jpeg vs image/pjpeg).
//   // Se quiser, flexibilize aqui; por segurança, manteremos estrito:
//   redirect_with(['err' => 'Tipo de arquivo não permitido.']);
// }

// Cria pasta se não existir
if (! is_dir($UPLOAD_DIR)) {
    if (! mkdir($UPLOAD_DIR, 0755, true)) {
        redirect_with(['err' => 'Não foi possível criar a pasta de uploads.']);
    }
}

// Gera nome único e evita colisão
try {
    $unique = bin2hex(random_bytes(16)); // nome aleatório seguro
} catch (Exception $e) {
    // fallback simples
    $unique = sha1(uniqid('', true));
}
$finalName = $unique . '.' . $ext;
$destPath  = $UPLOAD_DIR . DIRECTORY_SEPARATOR . $finalName;

// Move o arquivo
if (! move_uploaded_file($tmpPath, $destPath)) {
    redirect_with(['err' => 'Falha ao mover o arquivo para a pasta de destino.']);
}

// Opcional: definir permissões (dependendo do host)
@chmod($destPath, 0644);

// URL pública para o arquivo
$fileUrl = $PUBLIC_PATH . '/' . $finalName;

//redirect_with(['msg' => "Upload concluído! Arquivo salvo em: $fileUrl"]);

// ===================== PROCESSAMENTO DO CSV =====================
// Importa a conexão
require_once __DIR__ . '/DbConnection/ALPHAConnection.php';
$pdo = db_pdo();

// Importa a classe
require_once __DIR__ . '/Classes/ProcessaCR.php';

// Pega datas do formulário
$dtInicio = $_POST['txtDtInicio'] ?? date('Y-m-d');
$dtFinal  = $_POST['txtDtFinal'] ?? date('Y-m-d');

// URL da API
$apiUrl = $GLOBALS['API_URL'] . "/contasreceber/incluir";

// Cria objeto da classe
$processor = new ProcessaCR($pdo, $dtInicio, $dtFinal, $apiUrl);

// Processa o arquivo
$result = $processor->process($destPath, $finalName, $origName);

// // Processa o arquivo
// $result = $processor->process($destPath, $finalName);

// Trata o resultado
$mensagem = "Upload concluído! Sucesso: {$result['ok']} | Falhas: {$result['fail']}";
if (! empty($result['errors'])) {
    $mensagem .= " | Erros: " . implode(' | ', array_slice($result['errors'], 0, 5));
}

// Redireciona de volta para a tela
redirect_with(['msg' => $mensagem]);
