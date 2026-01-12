<?php
declare (strict_types = 1);

function db_pdo(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    // LOCAL
    // $host = 'srv1889.hstgr.io';
    // $db   = 'u190625922_alphalabs';
    // $user = 'u190625922_useralphalabs';
    // $pass = 'al257425227!AG';

    // DEV

    $host = 'localhost'; //'srv952.hstgr.io';
    $db   = 'u902229595_des_alphalabs';
    $user = 'u902229595_user_alphalabs';
    $pass = 'al257425227!AG';
    $port = 3306;
    // PROD
    // $host = 'localhost';'srv952.hstgr.io';
    // $db = 'u902229595_alphalabs';
    // $user = 'u902229595_useralphalabs';
    // $pass = 'al257425227!AG';

    /**
     * DbConnection/meucon.php
     *
     * Uso:
     *   require_once __DIR__ . '/DbConnection/meucon.php';
     *   $pdo = db_pdo();
     */

    try {
        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,

            // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
            // Importante para reduzir "conexões por hora" (reuso pelo worker)
            PDO::ATTR_PERSISTENT         => true,
            // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        ]);

        return $pdo;

    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['erro' => 'Erro na conexão com o banco de dados']);
        exit;
    }
}