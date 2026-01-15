<?php
/**
 * ProcessaCarga.php (v3)
 * - Aceita registros SEM CnpjCpf desde que exista Cliente (Nome)
 * - Ignora a ÚLTIMA linha do CSV (não processa)
 * - Compatível com PHP 7.0+ (sem arrow function e sem typed properties)
 */
class ProcessaCarga
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Processa o CSV de carga.
     * @param string $csvPath Caminho do arquivo CSV
     * @return array { ok:int, fail:int, errors:array }
     */
    public function processCarga($csvPath)
    {
        $ok     = 0;
        $fail   = 0;
        $errors = [];

        if (! is_file($csvPath)) {
            return [
                'ok'     => 0,
                'fail'   => 1,
                'errors' => ["Arquivo não encontrado: {$csvPath}"],
            ];
        }

        $file = new SplFileObject($csvPath);
        $file->setFlags(SplFileObject::READ_CSV);
        $file->setCsvControl(';');

        // Cabeçalho esperado (com trim aplicado para corresponder)
        $cabecalhoEsperado = [
            'Titulo',
            'Cliente',
            'CnpjCpf',
            'E_mailFinanceiro',
            'Celular',
            'TotalExame',
            '',
        ];

        $cabecalhoEncontrado = false;
        $linhaNum            = 0;

        // Buffer para ignorar a última linha:
        // - Lemos a linha atual e só processamos a anterior.
        $prevRow     = null;
        $prevLineNum = null;

        foreach ($file as $row) {
            $linhaNum++;

            if (! is_array($row)) {
                continue;
            }

            // Algumas leituras retornam [null] no final; trate como vazio
            if (count($row) === 1 && ($row[0] === null || trim((string) $row[0]) === '')) {
                continue;
            }

            // Limpa para comparar cabeçalho
            $linhaAtualLimpa = [];
            foreach ($row as $v) {
                $linhaAtualLimpa[] = trim((string) ($v === null ? '' : $v));
            }

            // Ainda buscando cabeçalho
            if (! $cabecalhoEncontrado) {
                if ($linhaAtualLimpa === $cabecalhoEsperado) {
                    $cabecalhoEncontrado = true;
                    // reset de buffer quando achou cabeçalho
                    $prevRow     = null;
                    $prevLineNum = null;
                }
                continue;
            }

            // A partir daqui, são linhas de dados
            // Estratégia de ignorar última linha:
            // - Se já existe uma linha anterior no buffer, processa ela agora.
            // - A linha atual vira a "prev" e fica no buffer.
            if ($prevRow !== null) {
                $result = $this->processDataRow($prevRow, $prevLineNum);
                if ($result['status'] === 'ok') {
                    $ok++;
                } elseif ($result['status'] === 'skip') {
                    // não conta nada
                } else {
                    $fail++;
                    $errors[] = $result['error'];
                }
            }

            $prevRow     = $row;
            $prevLineNum = $linhaNum;
        }

        // IMPORTANTE: não processamos $prevRow aqui -> isso IGNORA a última linha

        if (! $cabecalhoEncontrado) {
            $errors[] = "ERRO FATAL: Cabeçalho ('" . implode(';', $cabecalhoEsperado) . "') não foi encontrado no arquivo.";
            $fail     = -1;
        }

        return ['ok' => $ok, 'fail' => $fail, 'errors' => $errors];
    }

    /**
     * Processa uma linha de dados já após o cabeçalho.
     * Retorna:
     * - ['status'=>'ok'] quando gravou/atualizou com sucesso
     * - ['status'=>'skip'] quando ignorou linha (totalizador/lixo)
     * - ['status'=>'fail','error'=>msg] quando deu erro
     */
    private function processDataRow(array $row, $linhaNum)
    {
                                                                       // Extração das colunas
        $clienteRaw  = trim((string) (isset($row[1]) ? $row[1] : '')); // Cliente
        $docRaw      = trim((string) (isset($row[2]) ? $row[2] : '')); // CnpjCpf
        $emailRaw    = trim((string) (isset($row[3]) ? $row[3] : '')); // E_mailFinanceiro
        $telefoneRaw = trim((string) (isset($row[4]) ? $row[4] : '')); // Celular
        $valRaw      = trim((string) (isset($row[5]) ? $row[5] : '')); // TotalExame

        // Tenta converter encoding do nome (evita warning se não precisar)
        if ($clienteRaw !== '') {
            $converted = @mb_convert_encoding($clienteRaw, 'UTF-8', 'ISO-8859-1');
            if ($converted !== false && $converted !== '') {
                $clienteRaw = $converted;
            }
        }

        $clienteNorm = $this->removerCaracteresEspeciais(strtoupper($clienteRaw));

        $valor = $this->parseMoney($valRaw);
        if ($valor === null) {
            return [
                'status' => 'fail',
                'error'  => "L{$linhaNum}: valor inválido em TotalExame: '{$valRaw}'",
            ];
        }

        // Regra: precisa ter Cliente (Nome).
        // Se não tiver, e parecer um totalizador/lixo, ignora sem erro.
        if ($clienteNorm === '') {
            if ($docRaw === '' && $emailRaw === '' && $telefoneRaw === '' && $valor > 0) {
                return ['status' => 'skip'];
            }

            return [
                'status' => 'fail',
                'error'  => "L{$linhaNum}: registro ignorado - coluna Cliente (Nome) vazia.",
            ];
        }

        // Agora permite CPF/CNPJ vazio, desde que tenha Nome (Cliente)
        $digits      = $this->onlyDigits($docRaw);
        $cpfDigits   = ($digits !== '') ? $digits : null;
        $isDocValido = $this->isCpfCnpj($docRaw);

        try {
            // Busca cliente:
            // - Se doc válido: busca por CPF/CNPJ
            // - Senão: busca por Nome (normalizado)
            if ($isDocValido) {
                $clienteId = $this->findUserIdByCpfCnpj($digits);
            } else {
                $clienteId = $this->findUserIdByName($clienteNorm);
            }

            if (! $clienteId) {
                $this->insertCliente($clienteRaw, $cpfDigits, $emailRaw, $telefoneRaw);
                return ['status' => 'ok'];
            }

            $this->updateCliente((int) $clienteId, $clienteRaw, $cpfDigits, $emailRaw, $telefoneRaw);
            return ['status' => 'ok'];
        } catch (Throwable $t) {
            return [
                'status' => 'fail',
                'error'  => "L{$linhaNum}: exceção - " . $t->getMessage(),
            ];
        } catch (Exception $e) {
            // Compatibilidade (caso Throwable não exista/pegue)
            return [
                'status' => 'fail',
                'error'  => "L{$linhaNum}: exceção - " . $e->getMessage(),
            ];
        }
    }

    /* ========================= AUXILIARES ========================= */

    private function onlyDigits($s)
    {
        if ($s === null) {
            return '';
        }

        $out = preg_replace('/\D+/', '', (string) $s);
        return $out === null ? '' : $out;
    }

    private function parseMoney($raw)
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }

        // pt-BR
        if (strpos($raw, ',') !== false) {
            $raw = str_replace('.', '', $raw);
            $raw = str_replace(',', '.', $raw);
        }

        if (! is_numeric($raw)) {
            return null;
        }

        return (float) $raw;
    }

    private function isCpfCnpj($docRaw)
    {
        $digits = $this->onlyDigits($docRaw);
        $len    = strlen($digits);
        return ($len === 11 || $len === 14);
    }

    private function findUserIdByCpfCnpj($digits)
    {
        $sql  = "SELECT Id FROM cliente WHERE CPF = :doc LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':doc' => $digits]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }

    private function findUserIdByName($name)
    {
        $sql  = "SELECT Id FROM cliente WHERE Nome = :name LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':name' => $name]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }

    private function updateCliente($clienteId, $clienteRaw, $cpfDigits, $emailRaw, $telefoneRaw)
    {
        $sql = "UPDATE cliente
                SET Nome = :Nome,
                    CPF = :CPF,
                    Telefone = :Telefone,
                    Email = :Email
                WHERE Id = :Id;";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':Nome'     => $this->removerCaracteresEspeciais(strtoupper($clienteRaw)),
            ':CPF'      => ($cpfDigits === null ? null : $this->onlyDigits($cpfDigits)),
            ':Telefone' => $this->onlyDigits($telefoneRaw),
            ':Email'    => $this->firstEmail($emailRaw),
            ':Id'       => (int) $clienteId,
        ]);
    }

    private function insertCliente($clienteRaw, $cpfDigits, $emailRaw, $telefoneRaw)
    {
        $sql = "INSERT INTO cliente (Nome, CPF, Telefone, Email, Ativo)
                VALUES (:Nome, :CPF, :Telefone, :Email, 1);";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':Nome'     => $this->removerCaracteresEspeciais(strtoupper($clienteRaw)),
            ':CPF'      => ($cpfDigits === null ? null : $this->onlyDigits($cpfDigits)),
            ':Telefone' => $this->onlyDigits($telefoneRaw),
            ':Email'    => $this->firstEmail($emailRaw),
        ]);
    }

    private function firstEmail($emailRaw)
    {
        $emailRaw = (string) $emailRaw;
        $parts    = explode(';', $emailRaw);
        return trim($parts[0]);
    }

    private function removerCaracteresEspeciais($texto)
    {
        if ($texto === null || $texto === '') {
            return '';
        }

        // Garante UTF-8
        $texto = mb_convert_encoding($texto, 'UTF-8', 'UTF-8');

        // Converte para MAIÚSCULO antes
        $texto = mb_strtoupper($texto, 'UTF-8');

        // Remove acentos manualmente (mais confiável que iconv)
        $map = [
            'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U',
            'Ç' => 'C',
        ];

        $texto = strtr($texto, $map);

        // Remove caracteres indesejados
        // MANTÉM letras, números, espaço e hífen
        $texto = preg_replace('/[^A-Z0-9\s\-\%]/u', '', $texto);

        // Normaliza múltiplos espaços
        $texto = preg_replace('/\s+/', ' ', $texto);

        return trim($texto);
    }

}
