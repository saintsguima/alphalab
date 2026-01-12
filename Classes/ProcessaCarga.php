<?php
declare (strict_types = 1);

class ProcessaCarga
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
    public function processCarga(string $csvPath): array
    {
        $ok       = 0;
        $fail     = 0;
        $errors   = [];
        $linhaNum = 0; // Usado para referenciar a linha original no arquivo

        $csv = new SplFileObject($csvPath, 'r');
        // Mantido o READ_CSV para ler como array, removido o SKIP_EMPTY
        // para tratar linhas vazias manualmente (mais seguro)
        $csv->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD);
        $csv->setCsvControl(';');

        // 1. Defina o cabeçalho esperado (com trim aplicado para corresponder)
        $cabecalhoEsperado = [
            'Titulo',
            'Cliente',
            'CnpjCpf',
            'E_mailFinanceiro',
            'Celular',
            'TotalExame',
            '',
        ];

        // 2. Flag para controlar o início do processamento de dados
        $cabecalhoEncontrado = false;

        foreach ($csv as $row) {
            $linhaNum++;

            // Pula linhas vazias (array com apenas nulls ou vazio após trim)
            $linhaSemVazios = array_filter($row, fn($cell) => $cell !== null && trim((string) $cell) !== '');
            if (empty($linhaSemVazios)) {
                //var_dump("L$linhaNum: Linha vazia/lixo ignorada.");
                continue;
            }

            // 3. Lógica para encontrar o cabeçalho
            if (! $cabecalhoEncontrado) {
                // Mapeia a linha atual limpando os espaços em branco de cada célula para a comparação
                $linhaAtualLimpa = array_map('trim', $row);
                // Compara a linha atual (limpa) com o cabeçalho esperado
                if ($linhaAtualLimpa === $cabecalhoEsperado) {
                    //var_dump("L$linhaNum: CABEÇALHO ENCONTRADO. Próximas linhas são dados.");
                    $cabecalhoEncontrado = true;
                    continue; // Pula a linha do cabeçalho
                }

                // Se não for o cabeçalho e a flag estiver como false, é uma linha lixo, ignorar.
                //var_dump("L$linhaNum: Linha lixo ignorada: " . implode(';', $row));
                continue;
            }

            // =========================================================================================
            // 4. Se a flag for TRUE, esta é uma linha de DADOS para processar
            // =========================================================================================

            // Garante que o array tem pelo menos o tamanho esperado (ou a lógica abaixo falharia)
            // if (count($row) < 6) {
            //     $fail++;
            //     $errors[] = "L$linhaNum: Linha de dados incompleta/mal formada.";
            //     continue;
            // }

                                                           // Extração dos dados
            $clienteRaw  = trim((string) ($row[1] ?? '')); // Coluna Cliente (índice 1)
            $docRaw      = trim((string) ($row[2] ?? '')); // Coluna CnpjCpf (índice 2)
            $emailRaw    = trim((string) ($row[3] ?? '')); // Coluna E_mailFinanceiro (índice 3)
            $telefoneRaw = trim((string) ($row[4] ?? '')); // Coluna Celular (índice 4)
            $valRaw      = trim((string) ($row[5] ?? '')); // Coluna TotalExame (índice 5)

            $clienteRaw = mb_convert_encoding($clienteRaw, 'UTF-8', 'ISO-8859-1');

            // O código original tinha:
            // $cabecalho = $csv->fgetcsv();
            // Esse comando DEVE SER REMOVIDO! Ele avança o ponteiro do arquivo
            // de forma indesejada dentro do loop `foreach`.

            $valor = $this->parseMoney($valRaw);
            if ($valor === null) {
                $fail++;
                $errors[] = "L$linhaNum: valor ('$valRaw') inválido.";
                continue;
            }

            if ($docRaw === '' && $emailRaw === '' && $telefoneRaw === '') {
                // A linha pode ser de lixo/totalizador no fim do arquivo.
                // Por exemplo: ";159,00;;;;22.465,90;" - pode ser ignorado ou tratado.
                if (strtoupper($clienteRaw) === '' && $valor > 0) {
                    // var_dump("L$linhaNum: Ignorando totalizador ou linha incompleta/sem identificador.");
                    continue;
                }

                $fail++;
                $errors[] = "L$linhaNum: linha em branco (sem CPF/CNPJ, Email ou Telefone).";
                continue;
            }

            $digits = $this->onlyDigits($docRaw);
            $isDoc  = $this->isCpfCnpj($docRaw); // 11 ou 14 dígitos após limpar

            try {
                // Busca cliente conforme tipo
                $clienteId = $isDoc
                    ? $this->findUserIdByCpfCnpj($digits)
                    : $this->findUserIdByName(onlyABC(strtoupper($clienteRaw)));

                // O seu código original tem um bug aqui: a variável local é $clienteId, mas
                // você usa $clientId na linha seguinte. Corrigido para $clienteId.

                if (! $clienteId) {
                    $this->insertCliente($clienteRaw, $digits, $emailRaw, $telefoneRaw);
                    // $ok++; // Se a inserção for considerada sucesso
                    continue;
                }
                $this->updateCliente($clienteId, $clienteRaw, $digits, $emailRaw, $telefoneRaw);
                $ok++;
            } catch (Throwable $t) {
                $fail++;
                $errors[] = "L$linhaNum: exceção - " . $t->getMessage();
            }
        }

        if (! $cabecalhoEncontrado) {
            $errors[] = "ERRO FATAL: Cabeçalho ('" . implode(';', $cabecalhoEsperado) . "') não foi encontrado no arquivo.";
            $fail     = -1; // Sinalizar erro grave na leitura
        }

        return ['ok' => $ok, 'fail' => $fail, 'errors' => $errors];
    }

    /* ========================= AUXILIARES ========================= */

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

        // Formato 1234.56 ou inteiro
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
     * Procura cliente por CPF ou CNPJ (colunas CPF e/ou CNPJ).
     * Ajuste os nomes das colunas conforme seu schema.
     */
    private function findUserIdByCpfCnpj(string $digits): ?int
    {
        // Se sua tabela tiver apenas CPF, use "WHERE CPF = :doc".
        // Mantido OR para cobrir ambos os casos.
        $sql = "SELECT Id FROM cliente
                WHERE CPF = :doc
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':doc' => $digits]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }

    /**
     * Procura cliente por Nome (match exato; adapte para LIKE se desejar).
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

    private function updateCliente(int $clienteId, string $clienteRaw, string $digits, string $emailRaw, string $telefoneRaw): void
    {

        $sql = "UPDATE
                    cliente
                SET
                    Nome = :Nome,
                    CPF = :CPF,
                    Telefone = :Telefone,
                    Email = :Email
                WHERE
                    Id = :Id;";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':Nome'     => $this->removerCaracteresEspeciais(strtoupper($clienteRaw)),
            ':CPF'      => $this->onlyDigits($digits),
            ':Telefone' => $this->onlyDigits($telefoneRaw),
            ':Email'    => explode(';', $emailRaw)[0],
            ':Id'       => $clienteId,
        ]);

    }

    private function insertCliente(string $clienteRaw, string $digits, string $emailRaw, string $telefoneRaw): void
    {

        $sql = "INSERT INTO cliente(
                    Nome,
                    CPF,
                    Telefone,
                    Email,
                    Ativo
               ) VALUES (
                    :Nome,
                    :CPF,
                    :Telefone,
                    :Email,
                    :Ativo
               )";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':Nome'     => $this->removerCaracteresEspeciais(strtoupper($clienteRaw)),
            ':CPF'      => $this->onlyDigits($digits), // aqui vai o documento sem máscara OU o nome, conforme seu layout atual
            ':Telefone' => $this->onlyDigits($telefoneRaw),
            ':Email'    => explode(';', $emailRaw)[0],
            ':Ativo'    => 1,
        ]);
    }

    private function removerCaracteresEspeciais(string $texto): string
    {
        // Normaliza caracteres acentuados para ASCII
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);

        // Remove caracteres não alfanuméricos (mantém letras, números e espaço)
        $texto = preg_replace('/[^a-zA-Z0-9\s]/', '', $texto);

        // Remove espaços extras e trim final
        return trim($texto);
    }
}
