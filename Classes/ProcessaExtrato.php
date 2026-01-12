<?php
declare (strict_types = 1);

class ProcessaExtrato
{
    private PDO $pdo;
    private string $apiUrl;

    public function __construct(PDO $pdo, string $apiUrl)
    {
        $this->pdo    = $pdo;
        $this->apiUrl = $apiUrl;
    }

    /**
     * Lê um CSV (separador ';'), tenta localizar o cliente por CPF/CNPJ (11/14 dígitos) ou por Nome.
     * Se encontrar, chama a API externa. Caso contrário, registra na dlCargaCliente.
     */

    // public function processFileName(string $fileName): array
    // {
    //     $payload = ['Nome' => $fileName];

    //     $ch = curl_init();
    //     curl_setopt_array($ch, [
    //         CURLOPT_URL            => $this->apiUrl . "/extratos/incluir-arquivo-extrato",
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_POST           => true,
    //         CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    //         CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    //         CURLOPT_TIMEOUT        => 20,
    //     ]);

    //     $body   = curl_exec($ch);
    //     $errno  = curl_errno($ch);
    //     $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //     curl_close($ch);

    //     if ($errno !== 0) {
    //         return [
    //             'ok'     => false,
    //             'status' => 0,
    //             'error'  => "Erro cURL: $errno",
    //             'data'   => null,
    //             'id'     => null,
    //         ];
    //     }

    //     $json = json_decode((string) $body, true);
    //     // 2xx é sucesso "HTTP", mas a API ainda pode responder status lógico "erro"
    //     $httpOk = $status >= 200 && $status < 300;

    //     $apiStatus = is_array($json) ? ($json['status'] ?? null) : null;
    //     $apiOk     = $apiStatus === 'ok';
    //     $errorMsg  = is_array($json) ? ($json['mensagem'] ?? $json['message'] ?? null) : null;
    //     $id        = is_array($json) ? ($json['Id'] ?? $json['id'] ?? null) : null;

    //     return [
    //         'ok'     => ($httpOk && $apiOk),
    //         'status' => $status,
    //         'error'  => ($httpOk ? ($apiOk ? null : ($errorMsg ?: 'Falha na API')): ($errorMsg ?: 'HTTP não OK')),
    //         'data'   => $json,
    //         'id'     => $apiOk ? $id : null,
    //     ];
    // }

    public function processFileName(string $fileName): array
    {
        try {

            $fileName = trim($fileName);

            if ($fileName === '') {
                return [
                    'ok'     => false,
                    'status' => 400,
                    'error'  => 'Campo "Nome" é obrigatório.',
                    'data'   => null,
                    'id'     => null,
                ];
            }

            $sqlInsert = "INSERT INTO ArquivoExtrato (Nome) VALUES (:Nome)";
            $stmt      = $this->pdo->prepare($sqlInsert);
            $stmt->execute([
                ':Nome' => $fileName,
            ]);

            if ($stmt->rowCount() !== 1) {
                return [
                    'ok'     => false,
                    'status' => 500,
                    'error'  => 'Falha ao inserir registro.',
                    'data'   => null,
                    'id'     => null,
                ];
            }

            $id = (int) $this->pdo->lastInsertId();

            return [
                'ok'     => true,
                'status' => 201,
                'error'  => null,
                'data'   => ['status' => 'ok'],
                'id'     => $id,
            ];

        } catch (Throwable $e) {

            return [
                'ok'     => false,
                'status' => 500,
                'error'  => 'Erro na execução do registro: ' . $e->getMessage(),
                'data'   => null,
                'id'     => null,
            ];
        }
    }

    public function processExtrato(string $csvPath, string $BancoLayout, int $Id): array
    {
        if (! is_readable($csvPath)) {
            throw new RuntimeException("Arquivo não pode ser lido: {$csvPath}");
        }

        $resultLayout  = [];
        $resultExtrato = [];
        $resultExtrato = [];
        switch ($BancoLayout) {
            case '341I':
                $resultLayout = $this->processarLayout341I($csvPath, $Id, 1);
                // $resultLayout = ['status' => 'ok'];
                // $Id = 1;
                if ($resultLayout['status'] == 'ok') {
                    $resultExtrato = $this->processarExtrato($Id);
                }
                break;

            case '341II':
                // Código a ser executado para o Banco com layout 341II
                $resultLayout = $this->processarLayout341II($csvPath, $Id, 2);
                // $resultLayout = ['status' => 'ok'];
                // $Id = 1;
                if ($resultLayout['status'] == 'ok') {
                    $resultExtrato = $this->processarExtrato($Id);
                }
                break;
            case '341III':
                // Código a ser executado para o Banco com layout 341II
                $resultLayout = $this->processarLayout341III($csvPath, $Id, 5);
                // $resultLayout = ['status' => 'ok'];
                // $Id = 1;
                if ($resultLayout['status'] == 'ok') {
                    $resultExtrato = $this->processarExtrato($Id);
                }
                break;

            case '03':
                // Código a ser executado para o Banco com layout 03
                echo "Processando arquivo CSV com o layout do Banco 03.\n";
                $resultLayout = $this->processarLayout03($csvPath, $Id);
                break;

            default:
                // Se nenhum dos layouts acima for encontrado, uma exceção pode ser lançada
                // ou uma mensagem de erro pode ser retornada.
                echo "Erro: Layout de banco desconhecido: " . $BancoLayout . "\n";
                $resultExrato = ['erro' => 'Layout de banco inválido.'];
                return $resultExrato;
                break;
        }

        return $resultLayout;
    }

    public function processarLayout341II(string $csvPath, int $Id, int $layoutBanco): array
    {
        $resultLayout = $this->processarLayout341I($csvPath, $Id, $layoutBanco);

        return $resultLayout;
    }

    public function processarLayout341I(string $csvPath, int $Id, int $layoutBanco): array
    {
        $handle = fopen($csvPath, 'r');
        if (! $handle) {
            throw new RuntimeException("Falha ao abrir: {$csvPath}");
        }

        $this->pdo->beginTransaction();

        $stmt = $this->pdo->prepare("
            INSERT INTO Extratos
              (IdTipoBanco, IdArquivoExtrato, Data, Linha, Lancamento, Nome, CPFCNPJ, Valor)
            VALUES
              (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $linhasInseridas = 0;
        $started         = false;
        $linha           = 0;

        try {
            while (($row = fgetcsv($handle, 0, ';')) !== false) {

                if ($row === [null] || $row === false) {
                    $linha++;
                    continue; // linha vazia
                }

                $row = array_map([$this, 'normalizeCell'], $row);

                // Se ainda não começou, procura a primeira linha cuja coluna 0 é uma data dd/mm/yyyy
                if (! $started) {
                    if (isset($row[0]) && $this->isDatePtBr($row[0])) {
                        $started = true;
                    } else {
                        $linha++;
                        continue; // ainda no “lixo” de cabeçalho
                    }
                }

                // Daqui para frente, tentamos processar os registros que tenham ao menos 5 colunas
                if (count($row) < 5) {
                    $linha++;
                    continue;
                }

                // Mapeamento esperado:
                // 0: Data (dd/mm/yyyy)
                // 1: Lançamento
                // 2: Razão Social
                // 3: CPF/CNPJ (pode vir com máscara)
                // 4: Valor (R$)
                // 5: Saldo (R$)  -> pode não existir/vir vazio
                $IdTipoBanco      = $layoutBanco;
                $IdArquivoExtrato = $Id;
                $dataStr          = $row[0] ?? '';
                $lancamento       = $row[1] ?? '';
                $razaoSocial      = $row[2] ?? '';
                $documentoRaw     = $row[3] ?? '';
                $valorStr         = $row[4] ?? '';
                $saldoStr         = $row[5] ?? null;

                if ($saldoStr !== null) {
                    continue;
                }

                if (trim($documentoRaw) == "" && trim($razaoSocial) == "") {
                    continue;
                }

                if ($IdTipoBanco == 2) {
                    if (trim($razaoSocial) == "") {
                        $razaoSocial = $lancamento;
                    }
                }
                // Valida data
                if (! $this->isDatePtBr($dataStr)) {
                    // Se depois de começar aparecer linha sem data válida, apenas ignora
                    $linha++;
                    continue;
                }

                $dataSql = $this->datePtBrToSql($dataStr);

                // Documento só dígitos (CPF/CNPJ)
                $documento = preg_replace('/\D+/', '', $documentoRaw) ?: "";

                // Converte valores monetários (aceita "1.234,56", "1234,56", "1234.56", "-123,45")
                $valor = $this->parseMoneyPtBr($valorStr);
                $saldo = $saldoStr !== null && $saldoStr !== '' ? $this->parseMoneyPtBr($saldoStr) : null;

                // Insere

                $stmt->execute([
                    $IdTipoBanco,
                    $IdArquivoExtrato,
                    $dataSql,
                    $linha,
                    $lancamento,
                    $razaoSocial,
                    $documento,
                    $valor,
                ]);

                $linha++;
                $linhasInseridas++;

            }

            fclose($handle);
            $this->pdo->commit();
            $result = ['status' => 'ok', 'linhas_arquivo' => $linha, 'linhas_inseridas' => $linhasInseridas, 'mensagem' => 'Arquivo Importado com sucesso.'];
            return $result;
        } catch (Throwble $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            fclose($handle);

            $result = ['status' => 'no', 'linhas_arquivo' => $linha, 'linhas_inseridas' => $linhasInseridas, 'mensagem' => $e->getMessage()];
            throw $e;
        }
    }

    public function processarLayout341III(string $csvPath, int $Id, int $layoutBanco): array
    {
        $handle = fopen($csvPath, 'r');
        if (! $handle) {
            throw new RuntimeException("Falha ao abrir: {$csvPath}");
        }

        $this->pdo->beginTransaction();

        $stmt = $this->pdo->prepare("
            INSERT INTO Extratos
              (IdTipoBanco, IdArquivoExtrato, Data, Linha, Lancamento, Nome, CPFCNPJ, Valor)
            VALUES
              (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $linhasInseridas = 0;
        $started         = false;
        $linha           = 0;

        try {
            while (($row = fgetcsv($handle, 0, ';')) !== false) {

                if ($row === [null] || $row === false) {
                    $linha++;
                    continue; // linha vazia
                }

                $row = array_map([$this, 'normalizeCell'], $row);

                // Se ainda não começou, procura a primeira linha cuja coluna 0 é uma data dd/mm/yyyy
                if (! $started) {
                    if (isset($row[6]) && $this->isDatePtBr($row[6])) {
                        $started = true;
                    } else {
                        $linha++;
                        continue; // ainda no “lixo” de cabeçalho
                    }
                }

                // Daqui para frente, tentamos processar os registros que tenham ao menos 5 colunas
                if (count($row) < 6) {
                    $linha++;
                    continue;
                }

                // Mapeamento esperado:
                //  1: Pagador
                //  2: CPF/CNPJ Pagador (pode vir com máscara)
                //  3: Tipo
                //  8: Data Pagamento (dd/mm/yyyy)
                // 11: Valor Pago (R$)
                // 12: Status  -> paga
                $IdTipoBanco      = $layoutBanco;
                $IdArquivoExtrato = $Id;
                $razaoSocial      = $row[1] ?? '';
                $documentoRaw     = $row[2] ?? '';
                $lancamento       = $row[3] ?? '';
                $dataStr          = $row[8] ?? '';
                $valorStr         = $row[11] ?? '';
                $saldoStr         = $row[12] ?? null;

                if (trim(strtoupper($saldoStr)) !== "PAGA") {
                    continue;
                }

                // Valida data
                if (! $this->isDatePtBr($dataStr)) {
                    // Se depois de começar aparecer linha sem data válida, apenas ignora
                    $linha++;
                    continue;
                }

                $dataSql = $this->datePtBrToSql($dataStr);

                // Documento só dígitos (CPF/CNPJ)
                $documento = preg_replace('/\D+/', '', $documentoRaw) ?: "";

                // Converte valores monetários (aceita "1.234,56", "1234,56", "1234.56", "-123,45")
                $valor = $this->parseMoneyPtBr($valorStr);
                //$saldo = $saldoStr !== null && $saldoStr !== '' ? $this->parseMoneyPtBr($saldoStr) : null;
                $saldo = $saldoStr !== null && trim(strtoupper($saldoStr)) !== "PAGA" ? $this->parseMoneyPtBr($saldoStr) : null;

                // Insere

                $stmt->execute([
                    $IdTipoBanco,
                    $IdArquivoExtrato,
                    $dataSql,
                    $linha,
                    $lancamento,
                    $razaoSocial,
                    $documento,
                    $valor,
                ]);

                $linha++;
                $linhasInseridas++;

            }

            fclose($handle);
            $this->pdo->commit();
            $result = ['status' => 'ok', 'linhas_arquivo' => $linha, 'linhas_inseridas' => $linhasInseridas, 'mensagem' => 'Arquivo Importado com sucesso.'];
            return $result;
        } catch (Throwble $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            fclose($handle);

            $result = ['status' => 'no', 'linhas_arquivo' => $linha, 'linhas_inseridas' => $linhasInseridas, 'mensagem' => $e->getMessage()];
            throw $e;
        }
    }

    /** Normaliza encoding (Windows-1252/ISO-8859-1 -> UTF-8) e faz trim */
    private function normalizeCell(?string $s): string
    {
        if ($s === null) {
            return '';
        }

        // Converte para UTF-8 se necessário (mojibake típico: "Atualiza‡Æo")
        $s = @mb_convert_encoding($s, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
        return trim($s);
    }

    /** Confere se string é uma data dd/mm/yyyy válida */
    private function isDatePtBr(string $s): bool
    {
        if (! preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $s)) {
            return false;
        }
        [$d, $m, $y] = array_map('intval', explode('/', $s));
        return checkdate($m, $d, $y);
    }

    /** Converte dd/mm/yyyy -> yyyy-mm-dd */
    private function datePtBrToSql(string $s): string
    {
        [$d, $m, $y] = explode('/', $s);
        return sprintf('%04d-%02d-%02d', (int) $y, (int) $m, (int) $d);
    }

    /**
     * Converte dinheiro pt-BR para float/decimal string
     * Aceita: "1.234,56", "1234,56", "1234.56", "-1.234,56", "(1.234,56)"
     */
    private function parseMoneyPtBr(string $s): float
    {
        $s   = trim($s);
        $neg = false;

        // Formato contas pode trazer negativo com parênteses
        if (preg_match('/^\((.*)\)$/', $s, $m)) {
            $s   = $m[1];
            $neg = true;
        }

        // Remove caracteres que não sejam dígitos, ponto, vírgula ou sinal
        $s = preg_replace('/[^\d\.,\-]/', '', $s) ?? '0';

        // Se houver vírgula, considera vírgula como separador decimal e remove pontos (milhar)
        if (str_contains($s, ',')) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        }

        $v = (float) $s;
        return $neg ? -$v : $v;
    }

    private function processarExtrato(?int $id = null): array
    {
        $sql = "SELECT
                    Id,
                    IdTipoBanco,
                    IdArquivoExtrato,
                    Data,
                    Linha,
                    Lancamento,
                    Nome,
                    CPFCNPJ,
                    Valor,
                    Conciliado
                FROM
                    Extratos
                WHERE
                    Conciliado = 0 AND
                    (:Id IS NULL OR IdArquivoExtrato = :Id)";

        $stmt = $this->pdo->prepare($sql);

        $param = [
            ':Id' => $id,
        ];

        $stmt->execute($param);

        $cliente      = 0;
        $contaReceber = 0;

        $this->pdo->beginTransaction();

        try {
            while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cliente = $this->acharCliente($linha['CPFCNPJ']);

                if ($cliente == 0) {
                    $cliente = $this->acharClienteCC($linha['CPFCNPJ']);
                }

                if ($cliente != 0) {

                    $contaReceber = $this->acharContaReceber($cliente, $linha['Data']);

                    if ($contaReceber != 0) {
                        $updateCR = $this->updateContaReceber($contaReceber, (float) $linha['Valor']);

                        if ($updateCR != 0) {
                            $insertExtatoCliente = $this->insertExtratoCliente($cliente, $linha['Data'], (float) $linha['Valor']);
                            $check               = $this->checkExtrato($linha['Id']);
                        }
                    }
                }
            }

            $this->pdo->commit();

            $result = ['status' => 'ok', 'mensagem' => 'Registro Conciliados'];
            return $result;
        } catch (Throwble $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            $result = ['status' => 'no', 'mensagem' => $e->getMessage()];
            return $result;

        }
    }

    private function insertExtratoCliente(int $cliente, string $Data, float $Valor): int
    {
        // 1. CORREÇÃO: Chama o método com $this->
        // Obs: Se getLastSaldo não estiver corrigido (faltando execute()), ele retornará 0.0.
        $SaldoAnterior = $this->getLastSaldo($cliente);

        // Calcula o novo saldo (Credito + Saldo Anterior)
        // Assumimos que Credito é $Valor e Debito é 0.0, e Saldo é o novo saldo
        $NovoSaldo = $SaldoAnterior + $Valor;

        $sql = "INSERT INTO ExtratoCliente(
                    IdCliente,
                    Data,
                    Historico,
                    Credito,
                    Debito,
                    Saldo
                ) VALUES (
                    :IdCliente,
                    :Data,
                    :Historico,
                    :Credito,
                    :Debito,
                    :NovoSaldo  -- Usamos o novo saldo calculado
                )";

        $stmt = $this->pdo->prepare($sql);

        $params = [
            ':IdCliente' => $cliente,
            ':Data'      => $Data,
            ':Historico' => 'Conciliado',
            ':Credito'   => $Valor,
            ':Debito'    => 0.0,
            ':NovoSaldo' => $NovoSaldo,
        ];

        $stmt->execute($params);

        $idExtrato = $this->pdo->lastInsertId();

        if ($idExtrato !== false && $idExtrato !== 0) {
            return (int) $idExtrato;
        } else {
            return 0;
        }
    }
    private function acharCliente(string $CPFCNPJ): int
    {
        $sql = "select id from cliente where CPF = :CPF";

        $stmt = $this->pdo->prepare($sql);

        $params = [
            ':CPF' => $CPFCNPJ,
        ];

        $stmt->execute($params);

        $id_cliente = $stmt->fetchColumn();

        if ($id_cliente !== false) {
            return (int) $id_cliente;
        } else {
            return 0;
        }
    }

    private function getLastSaldo(int $cliente): float
    {
        $sql = "
            SELECT
                Saldo
            FROM
                ExtratoCliente
            WHERE
                IdCliente = :IdCliente
            ORDER BY
                Id DESC
            LIMIT 1;
        ";

        $stmt = $this->pdo->prepare($sql);

        $params = [
            ':IdCliente' => $cliente,
        ];

        $stmt->execute($params);

        $Saldo = $stmt->fetchColumn();

        if ($Saldo !== false) {
            return (float) $Saldo;
        } else {
            return 0.0;
        }
    }

    private function acharClienteCC(string $CPFCNPJ): int
    {
        $sql = "SELECT IdCliente FROM ClienteCC WHERE CPFCNPJ = :CPF";

        $stmt = $this->pdo->prepare($sql);

        $params = [
            ':CPF' => $CPFCNPJ,
        ];

        // PASSO CRÍTICO: Executa a consulta no banco de dados,
        // substituindo o placeholder.
        $stmt->execute($params);

        // Busca o valor da primeira coluna (IdCliente) do primeiro registro.
        $id_cliente = $stmt->fetchColumn();

        if ($id_cliente !== false) {
            // Se encontrou (não é false), retorna o ID.
            return (int) $id_cliente;
        } else {
            // Se não encontrou, retorna 0.
            return 0;
        }
    }

    private function acharContaReceber(int $cliente, string $Data): int
    {
        $sql = "SELECT
                    id
                FROM
                    ContasReceber
                WHERE
                    DtInicio <= :Data
                    AND DtFinal >= :Data
                    AND IdCliente  = :Id";

        $stmt = $this->pdo->prepare($sql);

        $params = [
            ':Data' => $Data,
            ':Id'   => $cliente,
        ];

        // Tenta executar
        $executed = $stmt->execute($params);

        $id = $stmt->fetchColumn();

        //var_dump("DEBUG: Cliente: {$cliente}, Data: {$Data}, ID Encontrado: " . var_export($id, true));

        if ($id !== false) {
            return (int) $id;
        } else {
            return 0;
        }
    }

    private function updateContaReceber(int $Id, float $Valor): int
    {
        $sql = "Update
                    ContasReceber
                SET
                    VlConciliado = VlConciliado + :Valor
                    WHERE
                    Id  = :Id";

        $stmt = $this->pdo->prepare($sql);

        $params = [
            ':Id'    => $Id,
            ':Valor' => $Valor,
        ];

        $stmt->execute($params);

        return $stmt->rowCount();
    }

    private function checkExtrato(int $Id): int
    {
        $sql = "UPDATE
                    Extratos
                SET
                    Conciliado = 1
                WHERE
                    Id  = :Id";

        $stmt = $this->pdo->prepare($sql);

        $params = [
            ':Id' => $Id,
        ];

        $stmt->execute($params);

        return $stmt->rowCount();
    }

}
