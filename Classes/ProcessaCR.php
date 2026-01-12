<?php
declare (strict_types = 1);

class ProcessaCR
{
    private PDO $pdo;
    private string $dtInicio;
    private string $dtFinal;
    private string $apiUrl;

    public function __construct(PDO $pdo, string $dtInicio, string $dtFinal, string $apiUrl)
    {
        $this->pdo      = $pdo;
        $this->dtInicio = $dtInicio;
        $this->dtFinal  = $dtFinal;
        $this->apiUrl   = $apiUrl; // mantido por compatibilidade (mesmo que hoje o método faça UPSERT no banco)
    }

    /**
     * Processa o CSV e grava ContasReceber (via UPSERT), registrando erros em dlCargaCliente.
     *
     * @return array{ok:int, fail:int, errors:array<int,string>}
     */
    public function process(string $csvPath, string $arquivoBase, string $origName): array
    {
        $ok       = 0;
        $fail     = 0;
        $errors   = [];
        $linhaNum = 0;

        $csv = new SplFileObject($csvPath, 'r');
        $csv->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
        $csv->setCsvControl(';');

        // Seu layout tem ; no final, então existe uma coluna vazia no fim do cabeçalho
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

        foreach ($csv as $row) {
            $linhaNum++;

            // Em alguns casos o SplFileObject pode retornar false/null
            if (! is_array($row) || $row === [null] || $row === false) {
                continue;
            }

            // Normaliza: remove nulls e garante array de strings
            $row = array_map(
                static fn($v) => is_string($v) ? $v : (string) ($v ?? ''),
                $row
            );

            // 1) Encontrar o cabeçalho (ignora "lixo" antes)
            if (! $cabecalhoEncontrado) {
                $linhaAtualLimpa = array_map('trim', $row);

                if ($linhaAtualLimpa === $cabecalhoEsperado) {
                    $cabecalhoEncontrado = true;
                }

                // Seja cabeçalho ou lixo, não processa como dado
                continue;
            }

                                                           // 2) Linha de dado (após cabeçalho)
            $clienteRaw  = trim((string) ($row[1] ?? '')); // Cliente
            $docRaw      = trim((string) ($row[2] ?? '')); // CnpjCpf
            $emailRaw    = trim((string) ($row[3] ?? '')); // E_mailFinanceiro (não usado no upsert, mas pode ser útil para debug)
            $telefoneRaw = trim((string) ($row[4] ?? '')); // Celular (não usado no upsert)
            $valRaw      = trim((string) ($row[5] ?? '')); // TotalExame

            /**
             * ✅ CORREÇÃO PRINCIPAL:
             * $digits e $isDoc DEVEM ser calculados ANTES de QUALQUER continue/registro de erro.
             * Assim eles nunca "herdam" valor da linha anterior.
             */
            $digits = $this->onlyDigits($docRaw);
            $isDoc  = $this->isCpfCnpj($docRaw);

            // Identificador para logging/erros (prioriza documento válido; senão usa nome)
            $identificadorErro = ($isDoc && $digits !== '') ? $digits : $clienteRaw;

            // 3) Validações coerentes com sua regra: achar por CPF/CNPJ OU por Nome
            if ($valRaw === '' || ($digits === '' && $clienteRaw === '')) {
                $fail++;
                $msg      = 'linha incompleta.';
                $errors[] = "{$linhaNum}: {$msg}";
                $this->insertDLCR($origName, $msg, $linhaNum, $identificadorErro, 0.0, $this->dtInicio, $this->dtFinal);
                continue;
            }

            $valor = $this->parseMoney($valRaw);
            if ($valor === null) {
                $fail++;
                $msg      = 'valor inválido.';
                $errors[] = "{$linhaNum}: {$msg}";
                $this->insertDLCR($origName, $msg, $linhaNum, $identificadorErro, 0.0, $this->dtInicio, $this->dtFinal);
                continue;
            }

            if ($valor <= 0.0) {
                $fail++;
                $msg      = 'valor menor ou igual a zero.';
                $errors[] = "{$linhaNum}: {$msg}";
                $this->insertDLCR($origName, $msg, $linhaNum, $identificadorErro, (float) $valor, $this->dtInicio, $this->dtFinal);
                continue;
            }

            // 4) Encontrar cliente
            $clienteId = null;

            if ($isDoc && $digits !== '') {
                $clienteId = $this->findUserIdByCpfCnpj($digits);
            } else {
                // Normaliza o nome para bater com o que está gravado no seu banco
                $nomeNormalizado = strtoupper($this->removerCaracteresEspeciais($clienteRaw));
                $clienteId       = $this->findUserIdByName($nomeNormalizado);
            }

            if ($clienteId === null) {
                $fail++;
                $msg      = 'Cliente não encontrado.';
                $errors[] = "{$linhaNum}: {$msg}";
                $this->insertDLCR($origName, $msg, $linhaNum, $identificadorErro, (float) $valor, $this->dtInicio, $this->dtFinal);
                continue;
            }

            // 5) Gravar (UPSERT) em ContasReceber
            try {
                $resp = $this->callExternalApi(
                    identificador: $identificadorErro, // mantido
                    valor: (float) $valor,
                    dtInicio: $this->dtInicio,
                    dtFinal: $this->dtFinal,
                    clienteId: $clienteId,
                    isDocumento: ($isDoc && $digits !== '')
                );

                if (($resp['ok'] ?? false) === true) {
                    $ok++;
                } else {
                    $fail++;
                    $status   = (int) ($resp['status'] ?? 500);
                    $err      = (string) ($resp['error'] ?? 'erro');
                    $errors[] = "{$linhaNum}: API status {$status} - {$err}";
                    $this->insertDLCR($origName, "falha ao gravar: {$err}", $linhaNum, $identificadorErro, (float) $valor, $this->dtInicio, $this->dtFinal);
                }
            } catch (Throwable $t) {
                $fail++;
                $errors[] = "{$linhaNum}: exceção - " . $t->getMessage();
                $this->insertDLCR($origName, "exceção: " . $t->getMessage(), $linhaNum, $identificadorErro, (float) $valor, $this->dtInicio, $this->dtFinal);
            }
        }

        return [
            'ok'     => $ok,
            'fail'   => $fail,
            'errors' => $errors,
        ];
    }

    /**
     * Registra linha não processada na dlCargaCliente.
     */
    private function insertDLCR(
        string $origName,
        string $historico,
        int $linhaNum,
        string $rawField,
        float $valor,
        string $dtInicio,
        string $dtFinal
    ): void {
        $sql = "INSERT INTO dlCargaCliente(
                    NomeArquivo,
                    Historico,
                    Linha,
                    CPFCNPJ,
                    Valor,
                    DtInicio,
                    DtFinal
               ) VALUES (
                    :NomeArquivo,
                    :Historico,
                    :Linha,
                    :CPFCNPJ,
                    :Valor,
                    :DtInicio,
                    :DtFinal
               )";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':NomeArquivo' => $origName,
            ':Historico'   => $historico,
            ':Linha'       => $linhaNum,
            ':CPFCNPJ'     => $rawField,
            ':Valor'       => $valor,
            ':DtInicio'    => $dtInicio,
            ':DtFinal'     => $dtFinal,
        ]);
    }

    /**
     * Mantive o nome "callExternalApi" para não quebrar nada,
     * mas hoje ele faz UPSERT na tabela ContasReceber (UPDATE se existe, INSERT se não).
     *
     * @return array{ok:bool,status:int,error:?string,data:mixed}
     */
    private function callExternalApi(
        string $identificador,
        float $valor,
        string $dtInicio,
        string $dtFinal,
        int $clienteId,
        bool $isDocumento
    ): array {
        try {
            // 1) tenta atualizar
            $sqlUpdate = "
                UPDATE ContasReceber
                SET
                    VlTotal = :VlTotal,
                    IdUsuarioInclusao = :IdUsuarioInclusao
                WHERE
                    IdCliente = :IdCliente
                    AND DtInicio = :DtInicio
                    AND DtFinal  = :DtFinal
            ";

            $stmt = $this->pdo->prepare($sqlUpdate);
            $stmt->execute([
                ':VlTotal'           => $valor,
                ':IdUsuarioInclusao' => 1,
                ':IdCliente'         => $clienteId,
                ':DtInicio'          => $dtInicio,
                ':DtFinal'           => $dtFinal,
            ]);

            // 2) se não atualizou, insere
            if ($stmt->rowCount() === 0) {
                $sqlInsert = "
                    INSERT INTO ContasReceber (
                        IdCliente,
                        DtInicio,
                        DtFinal,
                        VlTotal,
                        IdUsuarioInclusao
                    ) VALUES (
                        :IdCliente,
                        :DtInicio,
                        :DtFinal,
                        :VlTotal,
                        :IdUsuarioInclusao
                    )
                ";

                $stmtIns = $this->pdo->prepare($sqlInsert);
                $stmtIns->execute([
                    ':IdCliente'         => $clienteId,
                    ':DtInicio'          => $dtInicio,
                    ':DtFinal'           => $dtFinal,
                    ':VlTotal'           => $valor,
                    ':IdUsuarioInclusao' => 1,
                ]);
            }

            return [
                'ok'     => true,
                'status' => 200,
                'error'  => null,
                'data'   => [
                    'clienteId' => $clienteId,
                    'dtInicio'  => $dtInicio,
                    'dtFinal'   => $dtFinal,
                    'valor'     => $valor,
                ],
            ];
        } catch (Throwable $t) {
            return [
                'ok'     => false,
                'status' => 500,
                'error'  => $t->getMessage(),
                'data'   => null,
            ];
        }
    }

    /**
     * Retorna apenas dígitos de uma string.
     */
    private function onlyDigits(string $s): string
    {
        return preg_replace('/\D+/', '', $s) ?? '';
    }

    /**
     * Aceita formatos "1.234,56" (pt-BR) e "1234.56" (padrão).
     * Retorna float ou null se inválido.
     */
    private function parseMoney(string $raw): ?float
    {
        $raw = trim($raw);

        // Formato pt-BR: 1.234,56
        if (preg_match('/^\d{1,3}(\.\d{3})*,\d{2}$/', $raw)) {
            $norm = str_replace('.', '', $raw);
            $norm = str_replace(',', '.', $norm);
            return is_numeric($norm) ? (float) $norm : null;
        }

        // Formato 1234.56 ou inteiro ou "1234,56"
        $norm = str_replace(',', '.', $raw);
        return is_numeric($norm) ? (float) $norm : null;
    }

    /**
     * Decide se a entrada representa CPF (11 dígitos) ou CNPJ (14 dígitos), ignorando máscara.
     */
    private function isCpfCnpj(string $raw): bool
    {
        $d   = $this->onlyDigits($raw);
        $len = strlen($d);
        return $len === 11 || $len === 14;
    }

    /**
     * Procura cliente por CPF/CNPJ.
     * Ajuste o SQL caso seu schema seja diferente.
     */
    private function findUserIdByCpfCnpj(string $digits): ?int
    {
        $sql = "SELECT Id FROM cliente
                WHERE CPF = :doc
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':doc' => $digits]);
        $id = $stmt->fetchColumn();

        return $id ? (int) $id : null;
    }

    /**
     * Procura cliente por Nome (match exato).
     * Se quiser tolerância, troque por LIKE e normalize de forma consistente.
     */
    private function findUserIdByName(string $name): ?int
    {
        $sql = "SELECT Id FROM cliente
                WHERE Nome = :name
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':name' => $name]);
        $id = $stmt->fetchColumn();

        return $id ? (int) $id : null;
    }

    private function removerCaracteresEspeciais($texto): string
    {
        // Garante string (evita bool/null/array)
        if (! is_string($texto)) {
            $texto = (string) ($texto ?? '');
        }

        $texto = trim($texto);
        if ($texto === '') {
            return '';
        }

        // Tenta normalizar encoding para UTF-8 (se mbstring estiver disponível)
        if (function_exists('mb_detect_encoding') && function_exists('mb_convert_encoding')) {
            $enc = mb_detect_encoding($texto, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
            if ($enc && $enc !== 'UTF-8') {
                $texto = mb_convert_encoding($texto, 'UTF-8', $enc);
            }
        }

        // Remove acentos: tenta transliterator (intl), depois iconv, senão deixa como está
        if (function_exists('transliterator_transliterate')) {
            $texto2 = transliterator_transliterate('Any-Latin; Latin-ASCII', $texto);
            if (is_string($texto2) && $texto2 !== '') {
                $texto = $texto2;
            }
        } else {
            $texto2 = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
            if (is_string($texto2) && $texto2 !== '') {
                $texto = $texto2;
            }
            // se iconv falhar, mantém $texto original (não deixa virar false)
        }

        // Agora sempre garantimos que é string
        $texto = (string) $texto;

        // Remove caracteres especiais (mantém letras, números e espaço)
        $texto = preg_replace('/[^a-zA-Z0-9\s]/', '', $texto);
        if (! is_string($texto)) {
            $texto = '';
        }

        // Normaliza espaços
        $texto = preg_replace('/\s+/', ' ', $texto);
        if (! is_string($texto)) {
            $texto = '';
        }

        return trim($texto);
    }

}
