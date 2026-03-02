<?php
namespace CountryLocalizations;

use MapasCulturais\App;
use MapasCulturais\Entity;
use MapasCulturais\i;

class BrasilLocalization extends CountryLocalizationDefinition
{
    function register() {}

    // =================== GETTERS ================== //

    public function getCountryCode(): ?string
    {
        return 'BR';
    }

    public function getCountryName(): ?string
    {
        return i::__('Brasil');
    }

    public function getActiveLevels(): ?array
    {
        $app = App::i();
        
        return $app->config['address.defaultActiveLevels'];
    }

    public function getPostalCode(Entity $entity): ?string
    {
        return $entity->En_CEP;
    }

    public function getLevel0(Entity $entity): ?string
    {
        return $entity->En_Pais;
    }

    public function getLevel1(Entity $entity): ?string
    {
        return null;
    }

    public function getLevel2(Entity $entity): ?string
    {
        return $entity->En_Estado;
    }

    public function getLevel3(Entity $entity): ?string
    {
        return null;
    }

    public function getLevel4(Entity $entity): ?string
    {
        return $entity->En_Municipio;
    }

    public function getLevel5(Entity $entity): ?string
    {
        return null;
    }

    public function getLevel6(Entity $entity): ?string
    {
        return $entity->En_Bairro;
    }                                                                                                                       

    public function getLine1(Entity $entity): ?string
    {
        $address = "{$entity->En_Nome_Logradouro}, {$entity->En_Num}";
        return $address;
    }

    public function getLine2(Entity $entity): ?string
    {
        return $entity->En_Complemento;
    }

    public function getFullAddress(Entity $entity): ?string
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

    public function getLevelHierarchy(): array
    {
        $file = __DIR__ . '/levels-hierarchies/br.php';

        if (file_exists($file)) {
            return include $file;
        }

        return [];
    }

    // =================== SETTERS ===================== //

    public function setPostalCode(Entity $entity, ?string $value): void
    {
        $entity->En_CEP = $value;
    }

    public function setLevel0(Entity $entity, ?string $value): void 
    {
        $entity->En_Pais = $value;
    }

    public function setLevel1(Entity $entity, ?string $value): void{}

    public function setLevel2(Entity $entity, ?string $value): void
    {
        $entity->En_Estado = $value;
    }

    public function setLevel3(Entity $entity, ?string $value): void{}

    public function setLevel4(Entity $entity, ?string $value): void
    {
        $entity->En_Municipio = $value;
    }

    public function setLevel5(Entity $entity, ?string $value): void{}

    public function setLevel6(Entity $entity, ?string $value): void
    {
        $entity->En_Bairro = $value;
    }

    public function setLine1(Entity $entity, ?string $value): void
    {
        if ($value === null || $value === '') {
            $entity->En_Nome_Logradouro = '';
            $entity->En_Num = '';
            return;
        }

        $parts = explode(',', (string) $value);
        $entity->En_Nome_Logradouro = trim($parts[0] ?? '');
        $entity->En_Num = trim($parts[1] ?? '');
    }

    public function setLine2(Entity $entity, ?string $value): void
    {
        $entity->En_Complemento = $value;
    }

    public function setFullAddress(Entity $entity, ?string $value): void
    {
        $entity->endereco = $value;
    }
}