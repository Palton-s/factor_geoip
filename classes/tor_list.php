<?php
// This file is part of Moodle - http://moodle.org/

namespace factor_geoip;

/**
 * Lista local de nós de saída do Tor, baixada da lista oficial do Tor Project
 * por uma tarefa agendada e guardada em $CFG->dataroot/factor_geoip/.
 *
 * A consulta é local (sem chamada de rede no caminho do login).
 */
class tor_list {

    /** @var string lista oficial de nós de saída do Tor. */
    const SOURCE_URL = 'https://check.torproject.org/torbulkexitlist';

    /** @var array|null IPs da lista (como chaves), carregados uma vez por requisição. */
    private static $ips = null;

    /**
     * @return bool true se o IP é um nó de saída do Tor conhecido.
     */
    public static function contains(string $ip): bool {
        if (self::$ips === null) {
            self::$ips = [];
            $path = self::get_path();
            if (is_readable($path)) {
                $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
                self::$ips = array_flip($lines);
            }
        }
        return isset(self::$ips[$ip]);
    }

    /**
     * Baixa a lista oficial e substitui o arquivo local.
     *
     * @return int quantidade de IPs gravados.
     * @throws \moodle_exception se o download falhar ou vier vazio.
     */
    public static function update(): int {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $curl = new \curl();
        $content = $curl->get(self::SOURCE_URL, [], ['CURLOPT_TIMEOUT' => 30]);
        $info = $curl->get_info();
        if ($curl->get_errno() || ($info['http_code'] ?? 0) != 200) {
            throw new \moodle_exception('error:torupdate', 'factor_geoip', '', $curl->error ?: ($info['http_code'] ?? ''));
        }

        $ips = [];
        foreach (preg_split('/\R/', (string) $content) as $line) {
            $line = trim($line);
            if (filter_var($line, FILTER_VALIDATE_IP)) {
                $ips[] = $line;
            }
        }
        if (!$ips) {
            throw new \moodle_exception('error:torupdate', 'factor_geoip', '', 'lista vazia');
        }

        $path = self::get_path();
        make_writable_directory(dirname($path));
        $tmp = $path . '.tmp';
        file_put_contents($tmp, implode("\n", $ips) . "\n");
        rename($tmp, $path);
        self::$ips = null;

        return count($ips);
    }

    public static function get_path(): string {
        global $CFG;
        return $CFG->dataroot . '/factor_geoip/torexits.txt';
    }
}
