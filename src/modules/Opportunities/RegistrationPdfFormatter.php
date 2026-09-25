<?php

namespace MapasCulturais\Modules\Opportunities;

use MapasCulturais\App;

class RegistrationPdfFormatter
{
    public static function formatFieldValue(object $fieldConfig, mixed $value): string
    {
        if (self::isLocationField($fieldConfig) && (is_array($value) || is_object($value))) {
            return self::formatLocationValue($value);
        }

        if (is_array($value)) {
            return implode(', ', array_map([self::class, 'stringifyValue'], $value));
        }

        return self::stringifyValue($value);
    }

    public static function formatFieldValueAsHtml(object $fieldConfig, mixed $value): string
    {
        if (self::isAddressesField($fieldConfig) && is_array($value)) {
            return self::formatAddressesValueAsHtml($value);
        }

        if (self::isCustomTableField($fieldConfig) && is_array($value)) {
            return self::formatCustomTableValueAsHtml($fieldConfig, $value);
        }

        return nl2br(self::escape(self::formatFieldValue($fieldConfig, $value)));
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

    private static function isCustomTableField(object $fieldConfig): bool
    {
        return ($fieldConfig->fieldType ?? null) === 'custom-table';
    }

    private static function isAddressesField(object $fieldConfig): bool
    {
        return ($fieldConfig->fieldType ?? null) === 'addresses';
    }

    private static function formatAddressesValueAsHtml(array $addresses): string
    {
        if (!$addresses) {
            return '';
        }

        $html = '<ul class="address-list">';

        foreach ($addresses as $address) {
            $line = self::formatAddressListItem($address);

            if ($line === '') {
                continue;
            }

            $html .= '<li>' . self::escape($line) . '</li>';
        }

        return $html === '<ul class="address-list">' ? '' : $html . '</ul>';
    }

    private static function formatAddressListItem(mixed $address): string
    {
        if (!is_array($address) && !is_object($address)) {
            return self::cleanScalar($address);
        }

        $logradouro = self::cleanScalar(self::getConfigValue($address, 'logradouro'));
        $numero = self::cleanScalar(self::getConfigValue($address, 'numero'));
        $bairro = self::cleanScalar(self::getConfigValue($address, 'bairro'));
        $cidade = self::cleanScalar(self::getConfigValue($address, 'cidade'));
        $estado = self::cleanScalar(self::getConfigValue($address, 'estado'));
        $cep = self::cleanScalar(self::getConfigValue($address, 'cep'));
        $complemento = self::cleanScalar(self::getConfigValue($address, 'complemento'));

        if (!$logradouro && !$numero && !$bairro && !$cidade && !$estado && !$cep && !$complemento) {
            return '';
        }

        $line = sprintf('%s, nº %s - %s, %s/%s, %s', $logradouro, $numero, $bairro, $cidade, $estado, $cep);

        if ($complemento !== '') {
            $line .= sprintf(' (%s)', $complemento);
        }

        return trim($line, " \t\n\r\0\x0B,-/");
    }

    private static function formatCustomTableValueAsHtml(object $fieldConfig, array $rows): string
    {
        $columns = self::getConfigValue($fieldConfig->config ?? [], 'columns', []);

        if (!$columns || !$rows) {
            return '';
        }

        $html = '<table class="custom-table-view"><thead><tr>';

        foreach ($columns as $column) {
            $html .= '<th>' . self::escape(self::getConfigValue($column, 'name', '')) . '</th>';
        }

        $html .= '</tr></thead><tbody>';

        foreach ($rows as $row) {
            $html .= '<tr>';

            foreach ($columns as $index => $column) {
                $cellValue = self::getConfigValue($row, "col{$index}", '');
                $html .= '<td>' . self::escape(self::formatCustomTableCell($column, $cellValue)) . '</td>';
            }

            $html .= '</tr>';
        }

        return $html . '</tbody></table>';
    }

    private static function formatCustomTableCell(mixed $column, mixed $value): string
    {
        $type = self::getConfigValue($column, 'type', 'text');
        $value = self::stringifyValue($value);

        if ($type === 'date') {
            return self::formatDate($value);
        }

        return $value;
    }

    private static function formatDate(string $value): string
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', substr($value, 0, 10));

        if ($date) {
            return $date->format('d/m/Y');
        }

        return $value;
    }

    private static function formatLocationValue(array|object $value): string
    {
        $country = self::cleanScalar(self::getConfigValue($value, 'address_level0', self::getConfigValue($value, 'En_Pais')));
        $lines = [];

        foreach (self::locationDisplayKeys() as $key) {
            $item = self::cleanScalar(self::getConfigValue($value, $key));
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
            return self::stringifyStructuredValue($value);
        }

        return (string) $value;
    }

    private static function stringifyStructuredValue(array|object $value): string
    {
        $title = self::cleanScalar(self::getConfigValue($value, 'title'));
        $link = self::cleanScalar(self::getConfigValue($value, 'value'));
        if ($title !== '' && $link !== '') {
            return $title . ': ' . $link;
        }

        $name = self::cleanScalar(self::getConfigValue($value, 'name'));
        $url = self::cleanScalar(self::getConfigValue($value, 'url'));
        if ($name !== '' && $url !== '') {
            return $name . ': ' . $url;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
    }

    private static function cleanScalar(mixed $value): string
    {
        if ($value === null || is_array($value) || is_object($value)) {
            return '';
        }

        return trim((string) $value);
    }

    private static function getConfigValue(mixed $source, string|int $key, mixed $default = null): mixed
    {
        if (is_array($source)) {
            return $source[$key] ?? $default;
        }

        if (is_object($source)) {
            return $source->{$key} ?? $default;
        }

        return $default;
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
