<?php

class ProcessaCR
{
    /** @var PDO */
    private $pdo;

    /** @var string */
    private $dtInicio;

    /** @var string */
    private $dtFinal;

    /** @var string */
    private $apiUrl;

    /** @var array */
    private $termosExcecao = [];

    /** @var array */
    private $termosPlano = [];

    public function __construct(PDO $pdo, $dtInicio, $dtFinal, $apiUrl)
    {
        $this->pdo      = $pdo;
        $this->dtInicio = (string) $dtInicio;
        $this->dtFinal  = (string) $dtFinal;
        $this->apiUrl   = (string) $apiUrl;

        // Ajuda a evitar HY093 em alguns ambientes antigos
        try {
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        } catch (Exception $e) {
            // ignora se não suportar
        }

        $this->termosExcecao = $this->loadTermsFromTable('Excecao');
        $this->termosPlano   = $this->loadTermsFromTable('Plano');
    }

    public function process($csvPath, $arquivoBase, $origName)
    {
        $ok       = 0;
        $fail     = 0;
        $errors   = [];
        $linhaNum = 0;

        $csv = new SplFileObject($csvPath, 'r');
        $csv->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
        $csv->setCsvControl(';');

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

        // Buffer para ignorar a última linha (somatório)
        $prevRow      = null;
        $prevLinhaNum = 0;

        foreach ($csv as $row) {
            $linhaNum++;

            if (! is_array($row) || $row === [null] || $row === false) {
                continue;
            }

            // Normaliza para strings
            $row2 = [];
            foreach ($row as $v) {
                $row2[] = is_string($v) ? $v : (string) ($v === null ? '' : $v);
            }
            $row = $row2;

            if (! $cabecalhoEncontrado) {
                $linhaAtualLimpa = [];
                foreach ($row as $c) {
                    $linhaAtualLimpa[] = trim((string) $c);
                }

                if ($linhaAtualLimpa === $cabecalhoEsperado) {
                    $cabecalhoEncontrado = true;
                }
                continue;
            }

            // processa sempre a linha anterior; a última fica sem processar
            if ($prevRow !== null) {
                $this->processDataRow($prevRow, $prevLinhaNum, $origName, $ok, $fail, $errors);
            }

            $prevRow      = $row;
            $prevLinhaNum = $linhaNum;
        }

        // NÃO processa $prevRow (última linha = somatório)

        return [
            'ok'     => $ok,
            'fail'   => $fail,
            'errors' => $errors,
        ];
    }

    private function processDataRow(array $row, $linhaNum, $origName, &$ok, &$fail, array &$errors)
    {
        $clienteRaw = trim(isset($row[1]) ? (string) $row[1] : '');
        $docRaw     = trim(isset($row[2]) ? (string) $row[2] : '');
        $totalRaw   = trim(isset($row[5]) ? (string) $row[5] : '');

        // (3) Cliente vazio -> descarta sem log
        if ($clienteRaw === '') {
            return;
        }

        // proteção extra
        if ($this->looksLikeSummaryRow($row)) {
            return;
        }

        // (4.2) Descartar se Cliente contém termo de Plano ou Excecao
        $clienteNorm = $this->normalizeForMatch($clienteRaw);

        if ($this->containsAnyTerm($clienteNorm, $this->termosPlano) ||
            $this->containsAnyTerm($clienteNorm, $this->termosExcecao)) {
            return;
        }

        $valor = $this->parseMoney($totalRaw);
        if ($valor === null) {
            $fail++;
            $msg      = 'Valor inválido em TotalExame.';
            $errors[] = $linhaNum . ': ' . $msg . ' (TotalExame="' . $totalRaw . '")';
            $this->insertDLCR($origName, $msg, (int) $linhaNum, $clienteRaw, 0.0, $this->dtInicio, $this->dtFinal);
            return;
        }

        $identificadorErro = ($docRaw !== '') ? $docRaw : $clienteRaw;

        // Busca cliente por CPF/CNPJ e fallback por Nome
        $clienteId = null;

        $digits = $this->onlyDigits($docRaw);
        $isDoc  = ($digits !== '' && $this->isCpfCnpjDigits($digits));

        if ($isDoc) {
            $clienteId = $this->findUserIdByCpfCnpj($digits);
        }

        if ($clienteId === null) {
            $clienteId = $this->findUserIdByName($clienteNorm);
            // var_dump($clienteRaw);
            // echo '<br/>';
            // var_dump($clienteNorm);
            // echo '<br/>';
            // var_dump('Fallback por nome: ' . $clienteNorm . ' -> ' . ($clienteId === null ? 'NÃO ENCONTRADO' : 'ID ' . $clienteId));
            // echo '<br/>';
        }

        if ($clienteId === null) {
            $fail++;
            $msg      = 'Cliente não encontrado.';
            $errors[] = $linhaNum . ': ' . $msg . ' (Identificador="' . $identificadorErro . '")';
            $this->insertDLCR($origName, $msg, (int) $linhaNum, $identificadorErro, (float) $valor, $this->dtInicio, $this->dtFinal);
            return;
        }

        // (4.1) UPSERT por período
        try {
            $resp = $this->upsertContasReceber((float) $valor, $this->dtInicio, $this->dtFinal, (int) $clienteId);

            if (isset($resp['ok']) && $resp['ok'] === true) {
                $ok++;
            } else {
                $fail++;
                $err      = isset($resp['error']) ? (string) $resp['error'] : 'erro';
                $errors[] = $linhaNum . ': falha ao gravar: ' . $err;
                $this->insertDLCR($origName, 'falha ao gravar: ' . $err, (int) $linhaNum, $identificadorErro, (float) $valor, $this->dtInicio, $this->dtFinal);
            }
        } catch (Exception $e) {
            $fail++;
            $errors[] = $linhaNum . ': exceção - ' . $e->getMessage();
            $this->insertDLCR($origName, 'exceção: ' . $e->getMessage(), (int) $linhaNum, $identificadorErro, (float) $valor, $this->dtInicio, $this->dtFinal);
        }
    }

    private function upsertContasReceber($valor, $dtInicio, $dtFinal, $clienteId)
    {
        try {
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
            // ✅ sem ":" nas chaves
            $stmt->execute([
                'VlTotal'           => (float) $valor,
                'IdUsuarioInclusao' => 1,
                'IdCliente'         => (int) $clienteId,
                'DtInicio'          => (string) $dtInicio,
                'DtFinal'           => (string) $dtFinal,
            ]);

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
                // ✅ sem ":" nas chaves
                $stmtIns->execute([
                    'IdCliente'         => (int) $clienteId,
                    'DtInicio'          => (string) $dtInicio,
                    'DtFinal'           => (string) $dtFinal,
                    'VlTotal'           => (float) $valor,
                    'IdUsuarioInclusao' => 1,
                ]);
            }

            return ['ok' => true, 'error' => null];
        } catch (Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function insertDLCR($origName, $historico, $linhaNum, $rawField, $valor, $dtInicio, $dtFinal)
    {
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

        // ✅ sem ":" nas chaves
        $stmt->execute([
            'NomeArquivo' => (string) $origName,
            'Historico'   => (string) $historico,
            'Linha'       => (int) $linhaNum,
            'CPFCNPJ'     => (string) $rawField,
            'Valor'       => (float) $valor,
            'DtInicio'    => (string) $dtInicio,
            'DtFinal'     => (string) $dtFinal,
        ]);
    }

    private function loadTermsFromTable($tableName)
    {
        try {
            $sql  = "SELECT Nome FROM " . $tableName;
            $stmt = $this->pdo->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $out = [];
            foreach ($rows as $r) {
                $nome = trim(isset($r['Nome']) ? (string) $r['Nome'] : '');
                if ($nome === '') {
                    continue;
                }

                $out[] = $this->normalizeForMatch($nome);
            }

            // remove duplicados
            $uniq = [];
            foreach ($out as $v) {
                if ($v !== '' && ! in_array($v, $uniq, true)) {
                    $uniq[] = $v;
                }
            }

            return array_values($uniq);
        } catch (Exception $e) {
            return [];
        }
    }

    private function containsAnyTerm($haystackNorm, array $terms)
    {
        if ($haystackNorm === '' || empty($terms)) {
            return false;
        }

        foreach ($terms as $term) {
            if ($term === '') {
                continue;
            }

            if (strpos($haystackNorm, $term) !== false) {
                return true;
            }

        }
        return false;
    }

    private function looksLikeSummaryRow(array $row)
    {
        $cliente = trim(isset($row[1]) ? (string) $row[1] : '');
        $doc     = trim(isset($row[2]) ? (string) $row[2] : '');
        $valor   = trim(isset($row[5]) ? (string) $row[5] : '');

        if ($cliente !== '' && $doc === '' && ctype_digit($cliente)) {
            return (bool) preg_match('/[\d\.]+,\d{2}|\d+\.\d{2}/', $valor);
        }
        return false;
    }

    private function parseMoney($raw)
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/^\d{1,3}(\.\d{3})*,\d{2}$/', $raw)) {
            $norm = str_replace('.', '', $raw);
            $norm = str_replace(',', '.', $norm);
            return is_numeric($norm) ? (float) $norm : null;
        }

        if (preg_match('/^\d+(,\d{2})$/', $raw)) {
            $norm = str_replace(',', '.', $raw);
            return is_numeric($norm) ? (float) $norm : null;
        }

        if (preg_match('/^\d+(\.\d{2})$/', $raw)) {
            return is_numeric($raw) ? (float) $raw : null;
        }

        if (ctype_digit($raw)) {
            return (float) $raw;
        }

        return null;
    }

    private function onlyDigits($raw)
    {
        $d = preg_replace('/\D+/', '', (string) $raw);
        return is_string($d) ? $d : '';
    }

    private function isCpfCnpjDigits($digits)
    {
        $len = strlen((string) $digits);
        return ($len === 11 || $len === 14);
    }

    private function findUserIdByCpfCnpj($digits)
    {
        $digits = (string) $digits;

        $candidatesSql = [
            "SELECT Id FROM cliente WHERE CPF = :doc LIMIT 1",
            "SELECT Id FROM cliente WHERE CNPJ = :doc LIMIT 1",
            "SELECT Id FROM cliente WHERE CnpjCpf = :doc LIMIT 1",
            "SELECT Id FROM cliente WHERE REPLACE(REPLACE(REPLACE(CnpjCpf,'.',''),'-',''),'/','') = :doc LIMIT 1",
            "SELECT Id FROM cliente WHERE REPLACE(REPLACE(REPLACE(CNPJ,'.',''),'-',''),'/','') = :doc LIMIT 1",
            "SELECT Id FROM cliente WHERE REPLACE(REPLACE(CPF,'.',''),'-','') = :doc LIMIT 1",
        ];

        foreach ($candidatesSql as $sql) {
            try {
                $stmt = $this->pdo->prepare($sql);
                // ✅ sem ":" nas chaves
                $stmt->execute(['doc' => $digits]);
                $id = $stmt->fetchColumn();
                if ($id !== false && $id !== null) {
                    return (int) $id;
                }

            } catch (Exception $e) {
                continue;
            }
        }

        return null;
    }

    private function findUserIdByName($nomeNormalizado)
    {
        $nomeNormalizado = (string) $nomeNormalizado;
        if ($nomeNormalizado === '') {
            return null;
        }

        $candidates = [
            ['sql' => "SELECT Id FROM cliente WHERE UPPER(Nome) = :nome LIMIT 1", 'param' => $nomeNormalizado],
            ['sql' => "SELECT Id FROM cliente WHERE UPPER(RazaoSocial) = :nome LIMIT 1", 'param' => $nomeNormalizado],
            ['sql' => "SELECT Id FROM cliente WHERE UPPER(Nome) LIKE :nome LIMIT 1", 'param' => '%' . $nomeNormalizado . '%'],
            ['sql' => "SELECT Id FROM cliente WHERE UPPER(RazaoSocial) LIKE :nome LIMIT 1", 'param' => '%' . $nomeNormalizado . '%'],
        ];

        foreach ($candidates as $c) {
            try {
                $stmt = $this->pdo->prepare($c['sql']);
                // ✅ sem ":" nas chaves
                $stmt->execute(['nome' => $c['param']]);
                $id = $stmt->fetchColumn();
                if ($id !== false && $id !== null) {
                    return (int) $id;
                }

            } catch (Exception $e) {
                continue;
            }
        }

        return null;
    }

    private function normalizeForMatch($texto)
    {

        if ($texto === null) {
            return '';
        }

        $texto = trim((string) $texto);
        if ($texto === '') {
            return '';
        }

        // 1) Detecta encoding provável do CSV e converte pra UTF-8
        if (function_exists('mb_detect_encoding') && function_exists('mb_convert_encoding')) {
            $enc = mb_detect_encoding($texto, ['UTF-8', 'Windows-1252', 'ISO-8859-1'], true);
            if ($enc === false) {
                // fallback: Excel costuma ser Windows-1252
                $enc = 'Windows-1252';
            }
            $texto = mb_convert_encoding($texto, 'UTF-8', $enc);
        }

        // 2) Padroniza hífens diferentes para "-"
        $texto = str_replace(["–", "—", "-"], "-", $texto);

        // 3) Remove acentos / caracteres especiais de forma confiável
        // Preferência: intl (melhor). Fallback: iconv.
        if (function_exists('transliterator_transliterate')) {
            $texto = transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $texto);
        } elseif (function_exists('iconv')) {
            $tmp = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
            if ($tmp !== false && $tmp !== '') {
                $texto = $tmp;
            }
        }

        // 4) Uppercase
        $texto = strtoupper($texto);

        // 5) Mantém letras, números, espaço e hífen
        $texto = preg_replace('/[^A-Z0-9\s\-\%]/', '', $texto);

        // 6) Normaliza espaços
        $texto = preg_replace('/\s+/', ' ', $texto);

        return trim($texto);
    }

}
