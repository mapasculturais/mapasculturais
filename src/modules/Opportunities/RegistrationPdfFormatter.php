<?php

namespace MapasCulturais\Modules\Opportunities;

use MapasCulturais\App;

class RegistrationPdfFormatter
{
    public static function formatFieldValue(object $fieldConfig, mixed $value): string
    {
        if (self::isLocationField($fieldConfig) && is_array($value)) {
            return self::formatLocationValue($value);
        }

        if (is_array($value)) {
            return implode(', ', array_map([self::class, 'stringifyValue'], $value));
        }

        return self::stringifyValue($value);
    }

    private static function isLocationField(object $fieldConfig): bool
    {
        $config = $fieldConfig->config ?? [];

        if (is_array($config)) {
            return ($config['entityField'] ?? null) === '@location';
        }

        if (is_object($config)) {
            return ($config->entityField ?? null) === '@location';
        }

        return false;
    }

    private static function formatLocationValue(array $value): string
    {
        $country = self::cleanScalar($value['address_level0'] ?? $value['En_Pais'] ?? null);
        $lines = [];

        foreach (self::locationDisplayKeys() as $key) {
            if (!array_key_exists($key, $value)) {
                continue;
            }

            $item = self::cleanScalar($value[$key]);
            if ($item === '') {
                continue;
            }

            if ($key === 'address_postalCode') {
                $item = self::formatPostalCode($item, $country);
            }

            $lines[] = self::getAddressLabel($key, $country) . ': ' . $item;
        }

        return implode("\n", $lines);
    }

    private static function locationDisplayKeys(): array
    {
        return [
            'address_postalCode',
            'address_level0',
            'address_level1',
            'address_level2',
            'address_level3',
            'address_level4',
            'address_level5',
            'address_level6',
            'address_line1',
            'address_line2',
            'endereco',
        ];
    }

    private static function getAddressLabel(string $key, string $country): string
    {
        $labels = self::addressLabels();

        if (isset($labels[$key])) {
            return $labels[$key];
        }

        $separatorPosition = strrpos($key, '_');

        return $separatorPosition === false ? $key : substr($key, $separatorPosition + 1);
    }

    private static function addressLabels(): array
    {
        $defaultLabels = [
            'address_postalCode' => 'Código postal',
            'address_level0' => 'País',
            'address_level1' => 'Região',
            'address_level2' => 'Estado/Província',
            'address_level3' => 'Mesorregião/Subdivisão',
            'address_level4' => 'Município/Cidade/Comune',
            'address_level5' => 'Distrito/Setor',
            'address_level6' => 'Bairro',
            'address_line1' => 'Endereço',
            'address_line2' => 'Complemento',
            'endereco' => 'endereco',
        ];

        $levelLabels = App::i()->config['address.defaultLevelsLabels'] ?? [];

        foreach ($levelLabels as $level => $label) {
            $defaultLabels["address_level{$level}"] = $label;
        }

        return $defaultLabels;
    }

    private static function formatPostalCode(string $postalCode, string $country): string
    {
        $digits = preg_replace('/\D+/', '', $postalCode);

        if ($country === 'BR' && strlen($digits) === 8) {
            return substr($digits, 0, 5) . '-' . substr($digits, 5);
        }

        return $postalCode;
    }

    private static function stringifyValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'Sim' : 'Não';
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE) ?: '';
        }

        return (string) $value;
    }

    private static function cleanScalar(mixed $value): string
    {
        if ($value === null || is_array($value) || is_object($value)) {
            return '';
        }

        return trim((string) $value);
    }
}
