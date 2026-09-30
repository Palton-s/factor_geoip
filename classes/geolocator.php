<?php
// This file is part of Moodle - http://moodle.org/

namespace factor_geoip;

defined('MOODLE_INTERNAL') || die();

/**
 * Resolve um IP para coordenadas geográficas usando uma base local MaxMind
 * GeoLite2-City (.mmdb), e calcula distância entre dois pontos (Haversine).
 *
 * Usar uma base local (em vez de uma API HTTP de geolocalização) evita
 * vazar o IP dos usuários para um terceiro a cada login e evita limites
 * de requisição/latência de rede no caminho crítico do login.
 */
class geolocator {

    /** @var \MaxMind\Db\Reader[] leitores abertos, por caminho do arquivo. */
    private static $readers = [];

    /** @var string[] campos da base GeoIP2 Anonymous IP que indicam VPN/proxy/Tor. */
    const ANONYMOUS_FLAGS = [
        'is_anonymous', 'is_anonymous_vpn', 'is_hosting_provider',
        'is_public_proxy', 'is_residential_proxy', 'is_tor_exit_node',
    ];

    /**
     * Consulta a base opcional MaxMind GeoIP2 Anonymous IP.
     *
     * @return bool true se o IP é conhecido como VPN, proxy, Tor ou hospedagem.
     */
    public static function is_anonymous(string $ip): bool {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }

        $reader = self::get_reader((string) get_config('factor_geoip', 'anonmmdbpath'));
        if ($reader === null) {
            return false;
        }

        try {
            $record = $reader->get($ip);
        } catch (\Throwable $e) {
            debugging('factor_geoip: falha ao consultar a base de IP anônimo para ' . $ip . ': ' . $e->getMessage());
            return false;
        }

        foreach (self::ANONYMOUS_FLAGS as $flag) {
            if (!empty($record[$flag])) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $ip
     * @return array|null ['lat' => float, 'lon' => float, 'country' => string, 'city' => string] ou null se não resolver
     */
    public static function lookup(string $ip): ?array {
        // Não tenta geolocalizar IPs privados/loopback (comum em dev/testes atrás de proxy).
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }

        $reader = self::get_reader((string) get_config('factor_geoip', 'mmdbpath'));
        if ($reader === null) {
            return null;
        }

        try {
            $record = $reader->get($ip);
        } catch (\Throwable $e) {
            debugging('factor_geoip: falha ao consultar mmdb para ' . $ip . ': ' . $e->getMessage());
            return null;
        }

        if (empty($record['location']['latitude']) || empty($record['location']['longitude'])) {
            return null;
        }

        return [
            'lat' => (float) $record['location']['latitude'],
            'lon' => (float) $record['location']['longitude'],
            'country' => $record['country']['iso_code'] ?? '',
            'city' => $record['city']['names']['en'] ?? '',
        ];
    }

    /**
     * Distância em km entre dois pontos (fórmula de Haversine).
     */
    public static function distance_km(float $lat1, float $lon1, float $lat2, float $lon2): float {
        $earthradiuskm = 6371.0;

        $dlat = deg2rad($lat2 - $lat1);
        $dlon = deg2rad($lon2 - $lon1);

        $a = sin($dlat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dlon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthradiuskm * $c;
    }

    private static function get_reader(string $path): ?\MaxMind\Db\Reader {
        if ($path === '') {
            return null;
        }
        if (isset(self::$readers[$path])) {
            return self::$readers[$path];
        }

        if (!is_readable($path)) {
            debugging('factor_geoip: arquivo .mmdb ilegível em: ' . $path);
            return null;
        }

        $autoload = __DIR__ . '/../vendor/autoload.php';
        if (!class_exists('\\MaxMind\\Db\\Reader') && is_readable($autoload)) {
            require_once($autoload);
        }

        if (!class_exists('\\MaxMind\\Db\\Reader')) {
            debugging('factor_geoip: biblioteca MaxMind\\Db\\Reader não encontrada. '
                . 'Rode "composer require geoip2/geoip2" dentro da pasta do plugin.');
            return null;
        }

        self::$readers[$path] = new \MaxMind\Db\Reader($path);
        return self::$readers[$path];
    }
}
