<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App,
    MapasCulturais\Entities\Seal;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Exceptions\PermissionDenied;

/**
 * Defines that this entity has seals related to it.
 *
 * @property-read Seal[] $relatedSeals The seals related to this entity
 * @property-read SealRelation[] $sealRelations
 * @property-read string $sealRelationEntityClassNamerelatedSeals
 * 
 */
trait EntitySealRelation {

    public static function usesSealRelation(){
        return true;
    }

    static function getSealRelationEntityClassName(){
        return self::getClassName() . 'SealRelation';
    }

    /**
     * Verifica se um campo específico está bloqueado por algum selo ativo.
     * NÃO usa cache — consulta diretamente seal_relation_field.
     *
     * @param string $fieldName Nome do campo sem prefixo (ex: 'name')
     * @return bool
     */
    public function isFieldLocked(string $fieldName): bool
    {
        $app = App::i();

        // Admin bypass
        if ($app->user->is('admin')) {
            return false;
        }

        $fullFieldName = $this->controllerId . '.' . $fieldName;

        foreach ($this->getSealRelations() as $seal_relation) {
            $seal = $seal_relation->seal;
            $config = (array) $seal->lockedFieldsConfig;

            // Verifica se o campo está na configuração do selo
            if (!isset($config[$fullFieldName])) {
                continue;
            }

            // Se há registros seal_relation_field, usa a lógica granular
            $fields = $seal_relation->getSealRelationFields();
            if (!empty($fields)) {
                foreach ($fields as $field) {
                    if ($field->fieldName === $fullFieldName) {
                        $field_status = $field->getFieldStatus();
                        // Campo está bloqueado apenas se status == 'valid' ou 'no_expiration'
                        if ($field_status === 'valid' || $field_status === 'no_expiration') {
                            return true;
                        }
                        // Se expirado ou about_to_expire, não bloqueia este selo para este campo
                        break 2;
                    }
                }
            } else {
                // Fallback para comportamento legado: campo está bloqueado
                return true;
            }
        }

        return false;
    }

    /**
     * Retorna a lista dos campos verificados e os selos que verificam cada campo.
     * Considera expiração por campo. NÃO usa cache.
     *
     * @return object Um objeto contendo os selos bloqueados de cada campo.
     */
    function getLockedFieldSeals() {
        /** @var \MapasCulturais\Entity $this */

        $app = App::i();
        $locked_field_seals = [];

        foreach ($this->getSealRelations() as $seal_relation) {
            $seal = $seal_relation->seal;
            $config = (array) $seal->lockedFieldsConfig;

            if (!empty($config)) {
                // Lógica granular
                foreach ($config as $field_name => $field_config) {
                    if (preg_match("#{$this->controllerId}\.(.*)#", $field_name, $match)) {
                        $field = $match[1];

                        // Verifica se o campo está bloqueado
                        $is_locked = false;
                        $fields = $seal_relation->getSealRelationFields();
                        if (!empty($fields)) {
                            foreach ($fields as $srf) {
                                if ($srf->fieldName === $field_name) {
                                    $status = $srf->getFieldStatus();
                                    $is_locked = ($status === 'valid' || $status === 'no_expiration');
                                    break;
                                }
                            }
                        } else {
                            $is_locked = true; // fallback legado
                        }

                        if ($is_locked) {
                            $locked_field_seals[$field] = $locked_field_seals[$field] ?? [];
                            $locked_field_seals[$field][] = $seal->id;
                        }
                    }
                }
            } else {
                // Comportamento legado
                foreach ($seal->lockedFields ?: [] as $entity_field) {
                    if (preg_match("#{$this->controllerId}\.(.*)#", $entity_field, $match)) {
                        $field = $match[1];
                        $locked_field_seals[$field] = $locked_field_seals[$field] ?? [];
                        $locked_field_seals[$field][] = $seal->id;
                    }
                }
            }
        }

        $app->applyHookBoundTo($this, "{$this->hookPrefix}.lockedFieldSeals", [&$locked_field_seals]);

        return (object) $locked_field_seals;
    }

    /**
     * Retorna os selos que validam cada campo com o status granular atual.
     *
     * Este payload é visual: inclui campos válidos, prestes a expirar e expirados.
     * O bloqueio continua sendo decidido por getLockedFieldSeals()/getLockedFields,
     * que só considera valid/no_expiration como bloqueados.
     *
     * @return object
     */
    function getFieldSealStatuses() {
        /** @var \MapasCulturais\Entity $this */

        $app = App::i();
        $field_seal_statuses = [];

        foreach ($this->getSealRelations() as $seal_relation) {
            $seal = $seal_relation->seal;
            $config = (array) $seal->lockedFieldsConfig;

            if (!empty($config)) {
                $fields_by_name = [];
                foreach ($seal_relation->getSealRelationFields() as $srf) {
                    $fields_by_name[$srf->fieldName] = $srf;
                }

                foreach ($config as $field_name => $field_config) {
                    if (preg_match("#{$this->controllerId}\.(.*)#", $field_name, $match)) {
                        $field = $match[1];
                        $srf = $fields_by_name[$field_name] ?? null;
                        $status = $srf ? $srf->getFieldStatus() : 'no_expiration';

                        $field_seal_statuses[$field] = $field_seal_statuses[$field] ?? [];
                        $field_seal_statuses[$field][] = [
                            'sealId' => $seal->id,
                            'fieldName' => $field_name,
                            'fieldStatus' => $status,
                            'expiryDate' => $srf && $srf->expiryDate ? $srf->expiryDate->format(\MapasCulturais\i::__('d/m/Y')) : null,
                            'isInvalidator' => $srf ? $srf->isInvalidator : !empty(((array) $field_config)['isInvalidator']),
                            'isUnlocked' => in_array($status, ['about_to_expire', 'expired'], true),
                            'isLocked' => in_array($status, ['valid', 'no_expiration'], true),
                        ];
                    }
                }
            } else {
                // Comportamento legado: selos sem configuração granular continuam
                // sendo exibidos como válidos e bloqueados.
                foreach ($seal->lockedFields ?: [] as $entity_field) {
                    if (preg_match("#{$this->controllerId}\.(.*)#", $entity_field, $match)) {
                        $field = $match[1];
                        $field_seal_statuses[$field] = $field_seal_statuses[$field] ?? [];
                        $field_seal_statuses[$field][] = [
                            'sealId' => $seal->id,
                            'fieldName' => $entity_field,
                            'fieldStatus' => 'no_expiration',
                            'expiryDate' => null,
                            'isInvalidator' => false,
                            'isUnlocked' => false,
                            'isLocked' => true,
                        ];
                    }
                }
            }
        }

        $app->applyHookBoundTo($this, "{$this->hookPrefix}.fieldSealStatuses", [&$field_seal_statuses]);

        return (object) $field_seal_statuses;
    }

    /**
     * Retorna a lista dos campos bloqueados.
     * NÃO usa cache.
     *
     * @return array Um array contendo os nomes dos campos bloqueados.
     */
    function getLockedFields() {
        /** @var \MapasCulturais\Entity $this */

        $app = App::i();
        $locked_field_seals = (array) $this->lockedFieldSeals;

        $lockedFields = [];

        if (!empty($locked_field_seals)) {
            $lockedFields = array_keys($locked_field_seals);
        }

        $app->applyHookBoundTo($this, "{$this->hookPrefix}.lockedFields", [&$lockedFields]);

        return $lockedFields;
    }

    function getSealRelations($include_pending_relations = false){
        if(!$this->id)
            return [];

        $relation_class = $this->getSealRelationEntityClassName();
        if(!class_exists($relation_class))
            return [];

        $statuses = $include_pending_relations ? [$relation_class::STATUS_ENABLED, $relation_class::STATUS_PENDING] : [$relation_class::STATUS_ENABLED];
        $seal_statuses = [Seal::STATUS_ENABLED, Seal::STATUS_RELATED];
        $relations = [];

        $__relations = $this->_sealRelations;

        if(is_null($__relations)){
            $__relations = App::i()->repo($this->getSealRelationEntityClassName())->findBy(['owner' => $this]);
        }

        foreach($__relations as $ar){
            if(in_array($ar->status, $statuses) && in_array($ar->seal->status, $seal_statuses)){
                $relations[] = $ar;
            }
        }

        return $relations;
    }

    /**
     * Returns the seals related to this entity.
     *
     * @return Seal[]|SealRelation[] The Seals related to this entity.
     */
    function getRelatedSeals($return_relations = false, $include_pending_relations = false){
        $app = App::i();

        if(!$this->id)
            return [];

        $relation_class = $this->getSealRelationEntityClassName();
        if(!class_exists($relation_class))
            return [];

        $result = [];
       
        foreach ($this->getSealRelations($include_pending_relations) as $sealRelation) {
            $result[$sealRelation->id] = $return_relations ? $sealRelation : $sealRelation->seal;
        }
        
        rsort($result);
        return $result;
    }

    function createSealRelation(Seal $seal, $save = true, $flush = true, ?Agent $agent = null){
        $app = App::i();
        
        $seal->checkPermission('@control');
        
        $relation_class = $this->getSealRelationEntityClassName();

        $existing_relation = $app->repo($relation_class)->findOneBy(['seal' => $seal, 'owner' => $this]);
        if($existing_relation) {
            return $existing_relation;
        }

        $relation = new $relation_class;
        $relation->seal = $seal;
        $relation->owner = $this;
        $relation->agent = $agent ?: $app->user->profile->refreshed();

        if($save){
            $relation->save($flush);
        }
        
        $this->refresh();
        return $relation;
    }

    function removeSealRelation(Seal $seal, $flush = true){
        $relation_class = $this->getSealRelationEntityClassName();
        $repo = App::i()->repo($relation_class);
        $relation = $repo->findOneBy(['seal' => $seal, 'owner' => $this]);
        if($relation){
            $relation->delete($flush);
        }

        $this->refresh();
    }

    protected function canUserCreateSealRelation($user){
        $result = $this->canUser('@control', $user);
        try {
            $user_seals = $user->hasControlSeals;
        } catch (PermissionDenied $th) {
            $user_seals = [];
        }

        return $user->is('admin') || $result && $user_seals;
    }

    function canUserRemoveSealRelation($user){
        if ($user->is('admin')) {
            return true;
        }
        
        $result = false;
        if($this->canUser('@control', $user)){
            if($entity_seals = $this->relatedSeals){

                try {
                    $user_seals = $user->hasControlSeals;
                } catch (PermissionDenied $th) {
                    $user_seals = [];
                }

                foreach($user_seals as $seal) {
                    if(array_search($seal, $entity_seals) !== false) {
                        $result = true;
                    }
                }
            }
        }
        return $result;
    }

    function getRequestSealRelationUrl($idRelation){
        return App::i()->createUrl($this->controllerId, 'requestsealrelation', [$idRelation]);
    }

    function getRenewSealRelationUrl($idRelation){
        return App::i()->createUrl($this->controllerId, 'renewsealrelation', [$idRelation]);
    }
}
