<?php
namespace CountryLocalizations;

use MapasCulturais\App;
use MapasCulturais\Entity;
use MapasCulturais\i;

class BrazilLocalization extends CountryLocalizationDefinition
{
    function register() {}

    // =================== GETTERS ================== //

    protected function getCountryCode(): ?string
    {
        return 'BR';
    }

    protected function getCountryName(): ?string
    {
        return i::__('Brasil');
    }

    protected function getActiveLevels(): ?array
    {
        $app = App::i();
        
        return $app->config['address.defaultActiveLevels'];
    }

    protected function getPostalCode(Entity $entity): ?string
    {
        return $entity->En_CEP ?? null;
    }

    protected function getLevel1(Entity $entity): ?string
    {
        return null;
    }

    protected function getLevel2(Entity $entity): ?string
    {
        return $entity->En_Estado ?? null;
    }

    protected function getLevel3(Entity $entity): ?string
    {
        return null;
    }

    protected function getLevel4(Entity $entity): ?string
    {
        return $entity->En_Municipio ?? null;
    }

    protected function getLevel5(Entity $entity): ?string
    {
        return null;
    }

    protected function getLevel6(Entity $entity): ?string
    {
        return $entity->En_Bairro ?? null;
    }                                                                                                                       

    protected function getLine1(Entity $entity): ?string
    {
        $address = "{$entity->En_Logradouro}, {$entity->En_Numero}";
        return $address;
    }

    protected function getLine2(Entity $entity): ?string
    {
        return $entity->En_Complemento ?? null;
    }

    protected function getFullAddress(Entity $entity): ?string
    {
        $line_1 = $this->getLine1($entity); // Logradouro e Número
        $line_2 = $this->getLine2($entity); // Complemento
        $postal_code = $this->getPostalCode($entity); // CEP
        $level_2 = $this->getLevel2($entity); // Estado
        $level_4 = $this->getLevel4($entity); // Cidade
        $level_6 = $this->getLevel6($entity); // Bairro

        $parts = [];

        $parts[] = $line_1;

        if($line_2) {
            $parts[] = $line_2;
        }

        if($level_6) {
            $parts[] = $level_6;
        }

        if($level_4 && $level_2) {
            $parts[] = "{$level_4} - {$level_2}";
        } elseif($level_4) {
            $parts[] = $level_4;
        } elseif($level_2) {
            $parts[] = $level_2;
        }

        if($postal_code) {
            $parts[] = "CEP: {$postal_code}";
        }

        return implode(' - ', $parts);
    }

    protected function getLevelHierarchy(Entity $entity): array
    {
        $file = __DIR__ . '/levels-hierarchies/br.php';

        if (file_exists($file)) {
            return include $file;
        }

        return [];
    }

    // =================== SETTERS ===================== //

    protected function setPostalCode(Entity $entity, string $value): void
    {
        $entity->En_CEP = $value;
    }

    protected function setLevel1(Entity $entity, string $value): void{}

    protected function setLevel2(Entity $entity, string $value): void
    {
        $entity->En_Estado = $value;
    }

    protected function setLevel3(Entity $entity, string $value): void{}

    protected function setLevel4(Entity $entity, string $value): void
    {
        $entity->En_Municipio = $value;
    }

    protected function setLevel5(Entity $entity, string $value): void{}

    protected function setLevel6(Entity $entity, string $value): void
    {
        $entity->En_Bairro = $value;
    }

    protected function setLine1(Entity $entity, string $value): void
    {
        $parts = explode(',', $value);
        $entity->En_Logradouro = trim($parts[0] ?? '');
        $entity->En_Numero = trim($parts[1] ?? '');
    }

    protected function setLine2(Entity $entity, string $value): void
    {
        $entity->En_Complemento = $value;
    }
}