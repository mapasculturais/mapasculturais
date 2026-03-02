<?php
namespace MapasCulturais;

use Respect\Validation\Validator as v;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;

/**
 * Classe base para todas as entidades usadas no MapasCulturais.
 *
 * @property-read array $validationErrors Erros de validação das propriedades e metadados da entidade.
 * @property-read array $propertiesMetadata Metadados das propriedades
 * @property-read Controller $controller Controlador com a classe de mesmo nome desta classe de entidade no namespace pai.
 * @property-read string $controllerId ID do controlador para esta entidade
 * @property-read string $className Nome da classe da entidade
 * @property-read Entities\User $ownerUser Usuário proprietário desta entidade
 * @property-read array $userPermissions Retorna a lista de permissões do usuário. Se nenhum usuário for especificado, retorna o usuário autenticado.
 * @property-read string $hookClassPath Caminho da classe para hooks
 * @property-read string $hookPrefix Prefixo para hooks
 * @property-read array $currentUserPermissions Permissões do usuário atual sobre a entidade
 * @property-read string $permissionCacheKeyPrefix Prefixo para chaves de cache de permissão
 * @property-read array $entity Esta entidade como um array
 * @property-read string $singleUrl URL para visualização individual da entidade
 * @property-read string $editUrl URL para edição da entidade
 * @property-read string $deleteUrl URL para exclusão da entidade
 * @property-read string $entityType Tipo da entidade (nome da classe sem o namespace)
 * @property-read int $entityState Estado da entidade no UnitOfWork do Doctrine
 *
 * @hook **entity.new** - Executado quando o método __construct de qualquer entidade é chamado.
 * @hook **entity({$entity_class}).new** - Executado quando o método __construct da $entity_class é chamado.
 *
 * @hook **entity.load** - Executado após qualquer entidade ser carregada.
 * @hook **entity({$entity_class}).load** - Executado após uma entidade da classe $entity_class ser carregada.
 *
 * @hook **entity.save:before** - Executado antes de qualquer entidade ser inserida ou atualizada.
 * @hook **entity({$entity_class}).save:before** - Executado antes de uma entidade da classe $entity_class ser inserida ou atualizada.
 * @hook **entity.save:after**  - Executado após qualquer entidade ser inserida ou atualizada.
 * @hook **entity({$entity_class}).save:after** - Executado antes de uma entidade da classe $entity_class ser inserida ou atualizada.
 *
 * @hook **entity.insert:before** - Executado antes de qualquer entidade ser inserida.
 * @hook **entity({$entity_class}).insert:before** - Executado antes de uma entidade da classe $entity_class ser inserida.
 *
 * @hook **entity.insert:after** - Executado após qualquer entidade ser inserida.
 * @hook **entity({$entity_class}).insert:after** - Executado após uma entidade da classe $entity_class ser inserida.
 *
 * @hook **entity.remove:before** - Executado antes de qualquer entidade ser inserida.
 * @hook **entity({$entity_class}).remove:before** - Executado antes de uma entidade da classe $entity_class ser removida.
 *
 * @hook **entity.remove:after** - Executado após qualquer entidade ser inserida.
 * @hook **entity({$entity_class}).remove:after** - Executado após uma entidade da classe $entity_class ser removida.
 *
 * @hook **entity.update:before** - Executado antes de qualquer entidade ser atualizada.
 * @hook **entity({$entity_class}).update:before** - Executado antes de uma entidade da classe $entity_class ser atualizada.
 *
 * @hook **entity.update:after** - Executado após qualquer entidade ser atualizada.
 * @hook **entity({$entity_class}).update:after** - Executado após uma entidade da classe $entity_class ser atualizada.
 *
 */
abstract class Entity implements \JsonSerializable{
    use Traits\MagicGetter,
        Traits\MagicSetter,
        Traits\MagicCallers;

    /**
     * @var int STATUS_ENABLED Status ativado
     */
    const STATUS_ENABLED = 1;
    
    /**
     * @var int STATUS_DRAFT Status rascunho
     */
    const STATUS_DRAFT = 0;
    
    /**
     * @var int STATUS_DISABLED Status desabilitado
     */
    const STATUS_DISABLED = -9;
    
    /**
     * @var int STATUS_TRASH Status lixeira
     */
    const STATUS_TRASH = -10;
    
    /**
     * @var int STATUS_ARCHIVED Status arquivado
     */
    const STATUS_ARCHIVED = -2;

    /**
     * Array de definições de validação
     * @var array
     */
    protected static $validations = [];

    /**
     * @var array Erros de validação da entidade
     */
    protected $_validationErrors = [];

    /**
     * @var array Objetos aninhados para serialização JSON
     */
    private static $_jsonSerializeNestedObjects = [];

    /**
     * @var array Mudanças na entidade
     */
    public $_changes = [];
    
    /**
     * Habilita ou desabilita o uso do hook magic getter para filtrar valores de propriedades
     * @var bool
     */
    protected $__enableMagicGetterHook = false;
    
    /**
     * Habilita ou desabilita o uso do hook magic setter para filtrar valores de propriedades
     * @var bool
     */
    protected $__enableMagicSetterHook = false;
    
    /**
     * Flag para desabilitar a atualização do updateTimestamp no save
     * 
     * A flag está ativa se o valor for igual ou maior que 1
     * @var int
     */
    private int $__updateTimestampEnabled = 1;

    /**
     * Cria o novo objeto de entidade vazio adicionando um ponto vazio para propriedades do tipo 'point' e,
     * se a propriedade createTimestamp existir, um objeto DateTime com a data e hora atuais.
     *
     * @hook **entity(<<Entity>>).new** - Executado quando o método __construct da $entity_class é chamado.
     */
    public function __construct() {
        $app = App::i();

        foreach($app->em->getClassMetadata($this->getClassName())->associationMappings as $field => $conf){
            if($conf['type'] === 4){
                $this->$field = new \Doctrine\Common\Collections\ArrayCollection;
            }
        }

        foreach($app->em->getClassMetadata($this->getClassName())->fieldMappings as $field => $conf){

            if($conf['type'] == 'point'){
                $this->$field = new Types\GeoPoint(0,0);
            }
        }

        if(property_exists($this, 'createTimestamp')) {
            $this->createTimestamp = new \DateTime;
        }

        if(property_exists($this, 'updateTimestamp')) {
            $this->updateTimestamp = new \DateTime;
        }

        if($this->usesOwnerAgent() && !$app->user->is('guest')){
            $this->setOwner($app->user->profile);
        }

    }

    /**
     * Retorna a representação em string da entidade (classe:ID)
     * 
     * @return string
     */
    function __toString() {
        $pk = $this->getPKPropertyName();
        return $this->getClassName() . ':' . $this->$pk;
    }

    /**
     * Retorna o nome da propriedade que é a chave primária da entidade
     * 
     * @return string Nome da propriedade da chave primária
     */
    static function getPKPropertyName(): string
    {
        $app = App::i();
        $metadata = $app->em->getClassMetadata(static::class);
        return $metadata->identifier[0];
    }

    /**
     * Indica se a entidade é privada
     * 
     * @return bool Sempre retorna false
     */
    static function isPrivateEntity(){
        return false;
    }

    /**
     * Atualiza a entidade com os dados do banco de dados
     * 
     * @return void
     */
    function refresh(){
        $app = App::i();

        if($app->em->contains($this)) {
            $app->em->refresh($this);
        }
    }

    /** 
     * Retorna uma versão trazida do banco novamente
     * 
     * @return self
     */
    function refreshed() {
        if ($this->isNew()) {
            return $this;
        }

        $this->refresh();
        
        $pk = $this->getPKPropertyName();

        return $this->repo()->find($this->$pk);
    }

    /**
     * Verifica se esta entidade é igual a outra entidade
     * 
     * @param mixed $entity Entidade para comparar
     * @return bool True se as entidades forem iguais
     */
    function equals($entity){
        $pk = $this->getPKPropertyName();
        return is_object($entity) && $entity instanceof Entity && $entity->getClassName() === $this->getClassName() && $entity->$pk === $this->$pk;
    }

    /**
     * Verifica se a entidade é nova (não persistida no banco)
     * 
     * @return bool True se a entidade for nova
     */
    function isNew(){
        return App::i()->em->getUnitOfWork()->getEntityState($this) === \Doctrine\ORM\UnitOfWork::STATE_NEW;
    }

    /**
     * Verifica se a entidade está arquivada
     * 
     * @return bool True se a entidade estiver arquivada
     */
    function isArchived(){
        return $this->status === self::STATUS_ARCHIVED;
    }

    /**
     * Retorna uma versão simplificada da entidade com as propriedades especificadas
     * 
     * @param string|array $properties Propriedades a serem incluídas (string separada por vírgulas ou array)
     * @return \stdClass Objeto com as propriedades simplificadas
     */
    function simplify($properties = 'id,name'){
        $e = new \stdClass;
        $e->{'@entityType'} = $this->getControllerId();

        $properties = is_string($properties) ? explode(',',$properties) : $properties;
        if(is_array($properties)){
            foreach($properties as $prop){
                switch ($prop){
                    case 'className':
                        $e->className = $this->getClassName();
                    break;

                    case 'avatar':
                        if($this->usesAvatar()){
                            $e->avatar = [];
                            if($avatar = $this->avatar){
                                foreach($avatar->files as $transformation => $f){
                                    $e->avatar[$transformation] = $f->simplify('id,url');
                                }
                                $e->files = $e->files ?? [];
                                $e->files['avatar'] = $this->avatar->simplify('id,name,description,url,files');
                            }
                        }
                    break;

                    case 'terms':
                        if($this->usesTaxonomies())
                            $e->terms = $this->getTerms();

                    break;

                    default:
                        $e->$prop = $this->__get($prop);
                    break;
                }
            }
        }

        return $e;
    }

    /**
     * Exibe um dump da entidade para debug
     * 
     * @return void
     */
    function dump(){
        echo '<pre>';
        \Doctrine\Common\Util\Debug::dump($this);
        echo '</pre>';
    }

    /**
     * Retorna o nome da classe da entidade
     * 
     * @return string Nome da classe
     */
    static function getClassName(){
        return App::i()->em->getClassMetadata(get_called_class())->name;
    }

    /**
     * Retorna o usuário proprietário desta entidade
     *
     * @return \MapasCulturais\Entities\User
     */
    function getOwnerUser(){
        $app = App::i();

        if(isset($this->user)){
            return $this->user;
        }

        $owner = $this->owner;

        if(!$owner){
            return $app->user;
        }

        if(!($owner instanceof Entity)){
            return $app->user;
        }

        $user = $owner->getOwnerUser();

        return $user;
    }

    /**
     * Retorna array com os nomes dos status
     * 
     * @return array
     */

    static function getStatusesNames() {
        $app = App::i();
        $class = get_called_class();
        
        $statuses = $class::_getStatusesNames();
        
        // hook: entity(EntityName).statusesNames
        $hook_prefix = $class::getHookPrefix();
        $app->applyHook("{$hook_prefix}.statusesNames", [&$statuses]);

        return $statuses;
    }

    /**
     * Retorna array com os nomes dos status
     * 
     * @return array
     */
    protected static function _getStatusesNames() {
        return [
            self::STATUS_ARCHIVED => i::__('Arquivado'),
            self::STATUS_DISABLED => i::__('Desabilitado'),
            self::STATUS_DRAFT => i::__('Rascunho'),
            self::STATUS_ENABLED => i::__('Ativado'),
            self::STATUS_TRASH => i::__('Lixeira'),
        ];
    }

    /**
     * Retorna o nome do número de status informado, ou null se não existir
     * 
     * @return string|null
     */
    static function getStatusNameById($status) {
        $class = get_called_class();
        $statuses = $class::getStatusesNames();

        return $statuses[$status] ?? null;
    }

    /**
     * Define o status da entidade
     * 
     * @param int $status Novo status da entidade
     * @return void 
     */    
    function setStatus(int $status){
        $app = App::i();

        
        if($status != $this->status){
            
            switch($status){
                case self::STATUS_ARCHIVED:
                    if($this->usesArchive()){
                        $this->checkPermission('archive');
                    }
                    break;
                
                case self::STATUS_TRASH:
                    if($this->usesSoftDelete()){
                        $this->checkPermission('remove');
                    }
                    break;

                case self::STATUS_DRAFT:
                    if ($this->usesSoftDelete() && $this->status == self::STATUS_TRASH) {
                        $this->checkPermission('undelete');
                    } else if ($this->usesArchive() && $this->status == self::STATUS_ARCHIVED) {
                        $this->checkPermission('unarchive');
                    }
                    break;

                case self::STATUS_ENABLED:
                    if ($this->usesSoftDelete() && $this->status == self::STATUS_TRASH) {
                        $this->checkPermission('undelete');
                    } else if ($this->usesArchive() && $this->status == self::STATUS_ARCHIVED) {
                        $this->checkPermission('unarchive');
                    }
                    break;
            }
        }
        $hook_prefix = $this->getHookPrefix();

        $app->applyHookBoundTo($this, "{$hook_prefix}.setStatus({$status})", [&$status]);

        if($this->usesPermissionCache() && !$this->__skipQueuingPCacheRecreation) {
            $this->enqueueToPCacheRecreation();
        }
        $this->status = $status;
    }

    /**
     * Filtra uma coleção de entidades por status
     * 
     * @param iterable $collection Coleção de entidades para filtrar
     * @param int $status Status para filtrar
     * @param mixed $order Ordenação (não utilizado no momento)
     * @return array Entidades filtradas pelo status
     */
    protected function fetchByStatus($collection, $status, $order = null){
        $collection = is_iterable($collection) ? $collection : [];
        $result = [];

        foreach($collection as $entity) {
            if($entity->status == $status) {
                $result[] = $entity;
            }
        }
        
        return $result;
    }

    /**
     * Verificação genérica de permissão para um usuário
     * 
     * @param UserInterface $user Usuário para verificar permissões
     * @return bool True se o usuário tiver permissão genérica
     */
    protected function genericPermissionVerification($user){
        
        if($user->is('guest'))
            return false;

        if($this->isUserAdmin($user)){
            return true;
        }

        if($this->getOwnerUser()->id == $user->id)
            return true;

        if($this->usesAgentRelation() && $this->userHasControl($user))
            return true;


        $class_parts = explode('\\', $this->getClassName());
        $permission = end($class_parts);

        $entity_user = $this->getOwnerUser();
        if($user->isAttorney("manage{$permission}", $entity_user)){
            return true;
        }

        return false;
    }

    /**
     * Verifica se um usuário pode visualizar esta entidade
     * 
     * @param UserInterface $user Usuário para verificar permissão
     * @return bool True se o usuário pode visualizar a entidade
     */
    protected function canUserView($user){
        if($this->status > 0){
            return true;
        }else{
            return $this->canUser('@control', $user);
        }
    }

    /**
     * Verifica se um usuário pode criar esta entidade
     * 
     * @param UserInterface $user Usuário para verificar permissão
     * @return bool True se o usuário pode criar a entidade
     */
    protected function canUserCreate($user){
        $result = $this->genericPermissionVerification($user);
        if($result && $this->usesOwnerAgent()){
            $owner = $this->getOwner();
            if(!$owner || $owner->status < 1){
                $result = false;
            }
        }

        if($result && $this->usesNested()){
            $parent = $this->getParent();
            if($parent && $parent->status < 1){
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Verifica se um usuário pode modificar esta entidade
     * 
     * @param UserInterface $user Usuário para verificar permissão
     * @return bool True se o usuário pode modificar a entidade
     */
    protected function canUserModify($user) {
        return $this->genericPermissionVerification($user);
    }

    /**
     * Verifica se um usuário pode remover esta entidade
     * 
     * @param UserInterface $user Usuário para verificar permissão
     * @return bool True se o usuário pode remover a entidade
     */
    protected function canUserRemove($user){
        if($user->is('guest'))
            return false;

        if($this->isUserAdmin($user) || $this->getOwnerUser()->id == $user->id) {
            return true;

        }

        return false;
    }

    /**
     * Verifica se um usuário tem controle sobre esta entidade
     * 
     * @param UserInterface $user Usuário para verificar permissão
     * @return bool True se o usuário tem controle sobre a entidade
     */
    protected function canUser_control($user) {
        if ($this->usesAgentRelation() && $this->userHasControl($user)){
            return true;
        }
        
        if ($this->isUserAdmin($user)) {
            return true;
        } 
        
        if ($this->usesNested() && $this->parent && $this->parent->canUser('@control', $user)) {
            return true;
        } 
        
        if (isset($this->owner) && $this->owner->canUser('@control', $user)) {
            return true;
        }

        return false;
    }

    /** 
     * Retorna o prefixo para as chaves de cache de permissão
     * 
     * @return string Prefixo para chaves de cache de permissão
     */
    protected function getPermissionCacheKeyPrefix(): string {
        $app = App::i();
        $key = "{$this}:permissionCachePrefix";
        if($app->cache->contains($key)){
            return $app->cache->fetch($key);
        } else {
            $prefix = "$this" . uniqid(more_entropy: true) . ":";
            $app->cache->save($key, $prefix, DAY_IN_SECONDS);
            return $prefix;
        }
    }

    /** 
     * Limpa o cache de permissão
     * 
     * @return void
     */
    public function clearPermissionCache(){
        $app = App::i();
        $key = "{$this}:permissionCachePrefix";
        $app->cache->delete($key); 
    }

    /**
     * Verifica se um usuário pode executar uma ação sobre esta entidade
     * 
     * @param string $action Ação a ser verificada
     * @param UserInterface|Entities\Agent|null $userOrAgent Usuário ou agente para verificar permissão
     * @return bool True se o usuário pode executar a ação
     */
    public function canUser($action, $userOrAgent = null){
        $app = App::i();
        if(!$app->isAccessControlEnabled()){
            return true;
        }

        if(is_null($userOrAgent)){
            $user = $app->user;
        } else if($userOrAgent instanceof UserInterface) {
            $user = $userOrAgent;
        } else {
            $user = $userOrAgent->getOwnerUser();
        }

        $result = false;

        if (!empty($user)) {
            $cache_key = "{$this->permissionCacheKeyPrefix}:canUser({$user->id}):{$action}";
            if($app->config['app.usePermissionsCache'] && $app->cache->contains($cache_key)){
                return $app->cache->fetch($cache_key);
            }
            $class_parts = explode('\\', $this->getClassName());
            $permission = end($class_parts);

            $entity_user = $this->getOwnerUser();
            if($user->isAttorney("{$action}{$permission}", $entity_user) || $user->isAttorney("manage{$permission}", $entity_user)){
                $result = true;
            } else {
                if (strtolower($action) === '@control') {
                    $result = $this->canUser_control($user);
                } elseif (method_exists($this, 'canUser' . $action)) {
                    $method = 'canUser' . $action;
                    $result = $this->$method($user);
                } else {
                    $result = $this->genericPermissionVerification($user);
                }
            }

            $app->applyHookBoundTo($this, 'can(' . $this->getHookClassPath() . '.' . $action . ')', ['user' => $user, 'result' => &$result]);
            $app->applyHookBoundTo($this, $this->getHookPrefix() . '.canUser(' . $action . ')', ['user' => $user, 'result' => &$result]);

            if($app->config['app.usePermissionsCache']){
                $app->cache->save($cache_key, $result, $app->config['app.permissionsCache.lifetime']);
            }
        }

        return $result;
    }
    
    /**
     * Verifica se um usuário pode acessar os arquivos privados pertencentes a esta entidade
     * 
     * O padrão é 'view', pois é usado para proteger os arquivos anexados às inscrições
     * 
     * Outras entidades podem estender este método e alterar a verificação
     * 
     * @param UserInterface $user Usuário para verificar permissão
     * @return bool True se o usuário pode visualizar arquivos privados
     */ 
    protected function canUserViewPrivateFiles($user) {
        if($this->isPrivateEntity()) {
            return $this->canUser('view', $user);
        }else {
            return $this->canUser('@control', $user);
        }
    }

    /**
     * Retorna a lista de permissões do usuário. Caso nenhum usuário tenha sido informado, retorna o usuario autenticado
     * @param Entities\User|GuestUser|null $user
     * @return array
     */
    public function getUserPermissions(Entities\User|GuestUser|null $user = null): array
    {
        $app = App::i();
        $user = $user ?: $app->user;
        
        $entity_class_name = $this->className;
        $permissions_list = $entity_class_name::getPermissionsList();
        $permissions = [];
        foreach($permissions_list as $action) {
            $permissions[$action] = $this->canUser($action, $user);
        }

        return $permissions;
    }
    

    /**
     * Verifica se um usuário é administrador para esta entidade
     * 
     * @param UserInterface $user Usuário para verificar
     * @param string $role Papel do administrador (padrão: 'admin')
     * @return bool True se o usuário for administrador
     */
    public function isUserAdmin(UserInterface $user, $role = 'admin'){
        if($user->is('guest')) {
            return false;
        }
        
        $result = false;
        if($this->usesOriginSubsite()){
            if($user->is($role, $this->_subsiteId)){
                $result = true;
            }
        } else if($user->is($role)) {
            $result = true;
        }

        $app = App::i();

        $hook_prefix = $this->getHookPrefix();
        $app->applyHookBoundTo($this, "{$hook_prefix}.isUserAdmin({$role})", ['user' => $user, 'role' => $role, 'result' => &$result]);

        return $result;
    }

    /**
     * Verifica se o usuário atual tem permissão para executar uma ação e lança exceção se não tiver
     * 
     * @param string $action Ação a ser verificada
     * @throws Exceptions\PermissionDenied Se o usuário não tiver permissão
     * @return void
     */
    public function checkPermission($action){
        if(!$this->canUser($action))
            throw new Exceptions\PermissionDenied(App::i()->user, $this, $action);
    }

    /**
     * Retorna os rótulos das propriedades da entidade
     * 
     * @return array Array associativo com os rótulos das propriedades
     */
    public static function getPropertiesLabels(){
        $result = [];
        foreach(self::getPropertiesMetadata() as $key => $metadata){
            if(isset($metadata['@select'])){
                $key = $metadata['@select'];
            }
            $result[$key] = $metadata['label'];
        }
        return $result;
    }

    /**
     * Retorna o rótulo de uma propriedade específica
     * 
     * @param string $property_name Nome da propriedade
     * @return string Rótulo da propriedade ou string vazia se não encontrado
     */
    public static function getPropertyLabel($property_name){
        $labels = self::getPropertiesLabels();

        return isset($labels[$property_name]) ? $labels[$property_name] : '';
    }

    /**
     * Retorna o rótulo configurado para uma propriedade específica
     * 
     * Verifica primeiro se há um rótulo específico para a classe da entidade,
     * caso contrário usa o rótulo padrão configurado.
     * 
     * @param string $property_name Nome da propriedade
     * @return string Rótulo da propriedade ou string vazia se não encontrado
     */
    public static function _getConfiguredPropertyLabel($property_name){
        $app = App::i();
        $label = '';

        $prop_labels = $app->config['app.entityPropertiesLabels'];

        if(isset($prop_labels [self::getClassName()][$property_name])){
            $label = $prop_labels[self::getClassName()][$property_name];
        }elseif(isset($prop_labels ['@default'][$property_name])){
            $label = $prop_labels ['@default'][$property_name];
        }

        return $label;
    }

    /**
     * Cache de permissões por classe de entidade
     * 
     * @var array
     */
    private static $__permissions = [];

    static function getPermissionsList() {
        $class_name = self::getClassName();
        if (!isset(self::$__permissions[$class_name])) {
            $permissions = ['@control'];
            foreach (get_class_methods($class_name) as $method) {
                if (strpos($method, 'canUser') === 0 && $method != 'canUser') {
                    $permissions[] = lcfirst(substr($method, 7));
                }
            }

            $app = App::i();
            $prefix = self::getHookPrefix();
            $app->applyHook("{$prefix}.permissionsList", [&$permissions]);

            self::$__permissions[$class_name] = $permissions;
        }
        
        return self::$__permissions[$class_name];
    }

    /** 
     * Retorna a lista de permissões que devem ser salvas na tabela de cache de permissões
     * 
     * @return array
     */
    static function getPCachePermissionsList() {
        $app = App::i();
        $prefix = self::getHookPrefix();

        $permissions = [
            '@control',
            'view',
            'modify'
        ];

        $app->applyHook("{$prefix}.pcachePermissionsList", [&$permissions]);

        return $permissions;
    }


    /**
     * Retorna os metadados das propriedades desta entidade.
     *
     * Os metadados são compostos por uma chave required, uma chave type (tipo de mapeamento Doctrine) e uma chave length.
     *
     * <code>
     * /**
     *  * Exemplo
     *  array(
     *     'name' => array(
     *         'required' => true,
     *         'type' => 'string',
     *         'length' => 255
     *      )
     *      ...
     * </code>
     *
     * Se a entidade usa metadados, os metadados dos metadados (metameta??) serão incluídos no resultado.
     *
     * @see \MapasCulturais\Definitions\Metadata::getMetadata()
     * @see \MapasCulturais\Traits\EntityMetadata
     *
     * @param bool $include_column_name Incluir o nome da coluna no resultado
     * @return array Os metadados das propriedades desta entidade.
     */
    public static function getPropertiesMetadata($include_column_name = false){
        $app = App::i();

        $__class = get_called_class();
        $class = $__class::getClassName();

        $class_metadata = $app->em->getClassMetadata($class)->fieldMappings;
        $class_relations = $app->em->getClassMetadata($class)->getAssociationMappings();

        $result = [];

        $validations = $__class::getValidations();

        foreach ($class_metadata as $key => $value){
            $metadata = [
                'isMetadata' => false,
                'isEntityRelation' => false,

                'required'  => (bool) ($validations[$key]['required'] ?? !$value['nullable']),
                'type' => $value['type'],
                'length' => $value['length'],
                'label' => $class::_getConfiguredPropertyLabel($key),
            ];

            $metadata['isPK'] = $value['id'] ?? false;
            
            if ($include_column_name && isset($value['columnName'])) {
                $metadata['columnName'] = $value['columnName'];
            }

            if($key[0] == '_'){
                $prop = substr($key, 1);
                if(method_exists($class, 'get' . $prop)){
                     $metadata['@select'] = $prop;
                }else{
                    continue;
                }
            }

            if ($key == 'status') {
                $options = [
                    'draft' => self::STATUS_DRAFT, 
                    'enabled' => self::STATUS_ENABLED,
                ];
                if ($class::usesSoftDelete()) {
                    $options['trash'] = self::STATUS_TRASH;
                }
                if ($class::usesArchive()) {
                    $options['archived'] = self::STATUS_ARCHIVED;
                }
                $metadata['options'] = $options;
            }

            $result[$key] = $metadata;
        }

        foreach ($class_relations as $key => $value){
            $result[$key] = [
                'isMetadata' => false,
                'isEntityRelation' => true,

                'targetEntity' => str_replace('MapasCulturais\Entities\\','',$value['targetEntity']),
                'isOwningSide' => $value['isOwningSide'],
                'label' => $class::_getConfiguredPropertyLabel($key)
            ];
        }

        if($class::usesMetadata()){
            $result = $result + $class::getMetadataMetadata();
        }
        
        if($class::usesTypes()){
            $types = [];
            $types_order = [];
            foreach($app->getRegisteredEntityTypes($class) as $type) {
                $types[$type->id] = $type->name;
                $types_order[] = $type->id;
            }

            $result['type'] = [
                'type' => 'select',
                'options' => $types,
                'optionsOrder' => $types_order,

            ] + $result['_type'];
        }

        if(isset($result['location']) && isset($result['publicLocation'])){
            $result['location']['private'] = function(){ return (bool) ! $this->publicLocation; };
        }

        $kook_prefix = $class::getHookPrefix();
        $app->applyHook("{$kook_prefix}.propertiesMetadata", [&$result]);

        return $result;
    }

    static public function getValidations(){
        return [];
    }

    /**
     * Verifica se uma propriedade é obrigatória para a entidade
     * 
     * Verifica primeiro nos metadados das propriedades e depois nas validações
     * configuradas para a classe da entidade.
     * 
     * @param mixed $entity Entidade (parâmetro não utilizado, mantido para compatibilidade)
     * @param string $property Nome da propriedade
     * @return bool True se a propriedade for obrigatória
     */
    public function isPropertyRequired($entity,$property) {
        $app = App::i();
        $return = false;

        $__class = get_called_class();
        $class = $__class::getClassName();

        $metadata = $class::getPropertiesMetadata();
        if(array_key_exists($property,$metadata) && array_key_exists('required',$metadata[$property])) {
            $return = $metadata[$property]['required'];
        }

        $v = $class::getValidations();
        if(!$return && array_key_exists($property,$v) && array_key_exists('required',$v[$property])) {
            $return = true;
        }

        return $return;
    }

    /**
     * Returns this entity as an array.
     *
     * @return \MapasCulturais\Entity
     */
    public function getEntity(){
        $data = [];
        foreach ($this as $key => $value){
            if($key[0] == '_')
                continue;
            $data[$key] = $value;
        }
        return $data;
    }

    /**
     * Retorna a URL para visualização individual da entidade
     * 
     * @return string URL para a página de visualização da entidade
     */
    public function getSingleUrl(){
        $pk = $this->getPKPropertyName();
        return App::i()->createUrl($this->controllerId, 'single', [$this->$pk]);
    }

    /**
     * Retorna a URL para edição da entidade
     * 
     * @return string URL para a página de edição da entidade
     */
    public function getEditUrl(){
        $pk = $this->getPKPropertyName();
        return App::i()->createUrl($this->controllerId, 'edit', [$this->$pk]);
    }

    /**
     * Retorna a URL para exclusão da entidade
     * 
     * @return string URL para a ação de exclusão da entidade
     */
    public function getDeleteUrl(){
        $pk = $this->getPKPropertyName();
        return App::i()->createUrl($this->controllerId, 'delete', [$this->$pk]);
    }

    /**
     * Retorna o nome da classe do controlador associado a esta entidade
     * 
     * Substitui o namespace 'Entities' por 'Controllers' mantendo o nome da classe.
     * 
     * @return string Nome da classe do controlador
     */
    static function getControllerClassName() {
        $class = get_called_class();
        
        return preg_replace('#\\\Entities\\\([^\\\]+)$#', '\\Controllers\\\$1', $class::getClassName());
    }

    /**
     * Returns the controller with the same name in the parent namespace if it exists.
     *
     * @return \MapasCulturais\Controller The controller
     */
    public function getController(){
        return App::i()->getControllerByEntity($this);
    }

    /**
     * Returns the entity controller id
     *
     * @return string
     */
    public static function getControllerId() {
        $called_class = get_called_class();
        $class = $called_class::getClassName();
        return App::i()->getControllerIdByEntity($class) ?? '';
    }


    /**
     * Return the class path to be used in hook names.
     *
     * If the Entity is in the MapasCulturais\Entities namespace, the namespace will be removed.
     *
     * @example for the entity MapasCulturais\Entities\Agent, this method returns "Agent".
     * @example for the entity Foo\Boo\SomeEntity, this method returns "Foo.Boo.SomeEntity".
     *
     * @return string
     */
    public static function getHookClassPath($class = null){
        if(!$class){
            $called_class = get_called_class();
            $class = $called_class::getClassName();
        }
        return preg_replace('#^MapasCulturais\.Entities\.#','',str_replace('\\','.',$class));
    }

    public static function getHookPrefix($class = null) {
        $hook_class_path = self::getHookClassPath($class);
        return "entity({$hook_class_path})";
    }

    /**
     * Retorna o tipo da entidade (nome da classe sem o namespace)
     * 
     * @return string Tipo da entidade
     */
    public function getEntityType(){
        return str_replace('MapasCulturais\Entities\\','',$this->getClassName());
    }

    /**
     * Retorna o rótulo do tipo da entidade
     * 
     * Método a ser sobrescrito pelas classes filhas para fornecer
     * um rótulo adequado para o tipo de entidade.
     * 
     * @param bool $plural Se true, retorna o rótulo no plural
     * @return string Rótulo do tipo da entidade
     */
    public static function getEntityTypeLabel($plural = false): string {
        return '';
    }

    /**
     * Retorna o estado da entidade no UnitOfWork do Doctrine
     * 
     * @return int Estado da entidade (STATE_MANAGED, STATE_NEW, STATE_DETACHED, STATE_REMOVED)
     */
    function getEntityState() {
        return App::i()->em->getUnitOfWork()->getEntityState($this);
    }

    /**
     * Desabilita a atualização automática do timestamp de atualização
     * 
     * Decrementa o contador interno. A atualização só é habilitada
     * quando o contador for maior que zero.
     * 
     * @return void
     */
    function disableUpdateTimestamp(): void
    {
        $this->__updateTimestampEnabled--;
    }

    /**
     * Habilita a atualização automática do timestamp de atualização
     * 
     * Incrementa o contador interno. A atualização só é habilitada
     * quando o contador for maior que zero.
     * 
     * @return void
     */
    function enableUpdateTimestamp(): void
    {
        $this->__updateTimestampEnabled++;
    }

    /**
     * Verifica se a atualização automática do timestamp está habilitada
     * 
     * @return bool True se a atualização do timestamp estiver habilitada
     */
    function isUpdateTimestampEnabled(): bool
    {
        return $this->__updateTimestampEnabled > 0;
    }

    /**
     * Persist the Entity optionally flushing
     *
     * @param boolean $flush Flushes to the Database
     */
    public function save($flush = false){
        $app = App::i();

        $requests = [];

        $hook_prefix = $this->getHookPrefix();

        if(!$app->user->is('admin') && $this->usesLock() && $this->isLocked()) {
            $lock_info = $this->isLocked();

            if($lock_info['userId'] != $app->user->id) {
                throw new Exceptions\PermissionDenied($app->user, message: i::__('A entidade está bloqueada por outro usuário.'), code: Exceptions\PermissionDenied::CODE_ENTITY_LOCKED);
            }
        }

        try {
            $app->applyHookBoundTo($this, "{$hook_prefix}.save:requests", [&$requests]);
            $app->applyHookBoundTo($this, "entity({$this}).save:requests", [&$requests]);
        } catch (Exceptions\WorkflowRequestTransport $e) {
            $requests[] = $e->request;
        }
        
        if (method_exists($this, '_saveNested')) {
           try {
                $this->_saveNested();
            } catch (Exceptions\WorkflowRequestTransport $e) {
                $requests[] = $e->request;
            }
        }

        if (method_exists($this, '_saveOwnerAgent')) {
            try {
               $this->_saveOwnerAgent();                
            } catch (Exceptions\WorkflowRequestTransport $e) {
                $requests[] = $e->request;
            }
        }
        try{
            if($this->isNew()){
                $this->checkPermission('create');
                $is_new = true;

                if($this->usesOriginSubsite() && $app->getCurrentSubsiteId()){
                    $subsite = $app->repo('Subsite')->find($app->getCurrentSubsiteId());
                    $this->setSubsite($subsite);
                }

            }else{
                $this->checkPermission('modify');
                $is_new = false;
            }
            
            $app->applyHookBoundTo($this, "{$hook_prefix}.save:before");
            $app->em->persist($this);
            $app->applyHookBoundTo($this, "{$hook_prefix}.save:after");

            if($this->usesMetadata()){
                $this->saveMetadata($flush);
            }

            if($this->usesTaxonomies()){
                $this->saveTerms($flush);
            }

            if($this->usesRevision()) {
                if($is_new){
                    $this->_newCreatedRevision(flush: $flush);
                } else {
                    $this->_newModifiedRevision(flush: $flush);
                }
            }

            if($flush){
                $app->em->flush($this);
            }

        }catch(Exceptions\PermissionDenied $e){
            if(!$requests)
                throw $e;
        }
        
        if($requests){
            foreach($requests as $request)
                $request->save($flush);
            $e = new Exceptions\WorkflowRequest($requests);
            throw $e;
        }

        if ($is_new) {
            $app->applyHookBoundTo($this, "{$hook_prefix}.insert:finish", [$flush]);
        } else {
            $app->applyHookBoundTo($this, "{$hook_prefix}.update:finish", [$flush]);
        }

        $app->applyHookBoundTo($this, "{$hook_prefix}.save:finish", [$flush]);
        
    }

    /**
     * Remove this entity.
     *
     * @param boolean $flush Flushes to the database
     */
    public function delete($flush = false){
        $this->checkPermission('remove');

        if($this->usesRevision()) {
            $this->_newDeletedRevision(true);
        }

        App::i()->em->remove($this);
        if($flush)
            App::i()->em->flush();
    }

    /**
     * Verifica se um valor é serializável para JSON
     * 
     * Método recursivo que verifica se um valor (array, objeto ou primitivo)
     * pode ser serializado para JSON, considerando as classes permitidas
     * e evitando referências circulares.
     * 
     * @param mixed $val Valor a ser verificado
     * @param array $allowed_classes Lista de classes permitidas para serialização
     * @return mixed Valor serializável
     * @throws \Exception Se o valor não for serializável
     */
    private function _isPropertySerializable($val, array $allowed_classes){
        if(is_array($val)){
            $nval = [];
            foreach($val as $k => $v){
                try{
                    $nval[$k] = $this->_isPropertySerializable($v, $allowed_classes);
                }  catch (\Exception $e) {}
            }
            $val = $nval;
        }elseif(is_object($val) && !is_subclass_of($val, __CLASS__) && !in_array(\get_class($val), $allowed_classes)){
            throw new \Exception();
        }elseif(is_object($val)){

            if(in_array($val, Entity::$_jsonSerializeNestedObjects))
                throw new \Exception();
        }

        return $val;
    }

    /**
     * Retorna um array com a estrutura que será serializada pela função json_encode
     * 
     * @return array
     */
    public function jsonSerialize(): array {
        $app = App::i();
        
        $result = [
            '@entityType' => $this->getControllerId()
        ];
        $allowed_classes = [
            'DateTime',
            'MapasCulturais\Types\GeoPoint',
            'stdClass'
        ];
        $_uid = uniqid();

        Entity::$_jsonSerializeNestedObjects[$_uid] = $this;

        foreach($this as $prop => $val){
            if($prop[0] == '_')
                continue;

            if($prop[0] == '_' && method_exists($this, 'get' . substr($prop, 1)))
                $prop = substr ($prop, 1);

            try{
                $val = $this->__get($prop);
                $val = $this->_isPropertySerializable($val, $allowed_classes);
                $result[$prop] = $val;
            }  catch (\Exception $e){}
        }

        if($this->usesMetadata()){
            $registered_metadata = $this->getRegisteredMetadata();
            foreach($registered_metadata as $def){
                $meta_key = $def->key;
                $meta_value = $this->$meta_key;
                if ($meta_value instanceof Entity) {
                    $result[$meta_key] = $meta_value->id;
                } else {
                    $result[$meta_key] = $meta_value;
                }
            }
        }

        if ($this->usesTypes()) {
            $result['type'] = $this->_type;
        }

        if ($this->usesTaxonomies()) {
            $result['terms'] = $this->terms;
        }

        if($controller_id = $this->getControllerId()){
            $result['controllerId'] = $controller_id;
            $result['deleteUrl'] = $this->getDeleteUrl();
            $result['editUrl'] = $this->getEditUrl();
            $result['singleUrl'] = $this->getSingleUrl();
        }

        if($this->usesSealRelation()) {
            $result['lockedFields'] = $this->lockedFields;
        }
        
        unset(Entity::$_jsonSerializeNestedObjects[$_uid]);

        // adiciona as permissões do usuário sobre a entidade:
        if ($this->usesPermissionCache()) {
            $result['currentUserPermissions'] = $this->currentUserPermissions;
        }

        $app->applyHookBoundTo($this, "{$this->hookPrefix}.jsonSerialize", [&$result]);

        return $result;
    }


    /**
     * Validate that this property is unique in database
     *
     * @param string $property_name
     *
     * @return boolean
     */
    protected function validateUniquePropertyValue($property_name){
        $class = get_called_class();
        $dql = "SELECT COUNT(e.$property_name) FROM $class e WHERE e.$property_name = :val";
        $params = ['val' => $this->$property_name];
        $pk = $this->getPKPropertyName();
        if($this->$pk){
            $dql .= " AND e.{$pk} != :pk";
            $params['pk'] = $this->$pk;
        }

        $ok = App::i()->em->createQuery($dql)->setParameters($params)->getSingleScalarResult() == 0;
        return $ok;
    }

    /**
     * Validates the entity properties and returns the errors messages.
     *
     * The entity errors messages uses php gettext.
     *
     * If this entity uses metadata, this method will call getMetadataValidationErrors() method
     *
     * <code>
     * /**
     *  * Example of the array of errors:
     * array(
     *     'name' => [ 'The name is required' ],
     *     'email' => [ 'The first error message', 'The second error message' ]
     * )
     * </code>
     *
     * @see \MapasCulturais\Traits\Metadata::getMetadataValidationErrors() Metadata Validation Errors
     *
     * @return array
     */
    public function getValidationErrors(){
        $app = App::i();

        $errors = $this->_validationErrors;
        $class = get_called_class();

        if(!method_exists($class, 'getValidations')) {
            return $errors;
        }

        $properties_validations = $class::getValidations();

        $tags = ['style','script','embed','object','iframe','img','link'];
        $validation = implode(',', array_map(function($tag){
            return "v::contains('<{$tag}')";
        }, $tags));
        $htmltags_validation = "v::noneOf($validation)";

        foreach($this->getPropertiesMetadata() as $prop => $metadata){
            if(!$metadata['isEntityRelation']){
                if(!isset($properties_validations[$prop])){
                    $properties_validations[$prop] = [];
                }

                $properties_validations[$prop]['htmlentities'] = i::__('Não é permitido a inclusão de scripts');
            }
        }

        $hook_prefix = $this->getHookPrefix();
        
        $app->applyHookBoundTo($this, "{$hook_prefix}.validations", [&$properties_validations]);

        foreach($properties_validations as $property => $validations){

            if(!$this->$property && !key_exists('required', $validations))
                continue;



            foreach($validations as $validation => $error_message){
                $validation = trim($validation);

                $ok = true;

                if($validation == 'htmlentities'){
                    if(!is_string($this->$property)){
                        continue;
                    }
                    
                    $validation = $htmltags_validation;
                }

                if($validation == 'required'){
                    if (is_string($this->$property)) {
                        $ok = trim($this->$property) !== '';
                    } else {
                        $ok = (bool) $this->$property || $this->$property === 0;
                    }

                }elseif($validation == 'unique'){
                    $ok = $this->validateUniquePropertyValue($property);

                }elseif(strpos($validation,'v::') === 0){
                    $validation = str_replace('v::', 'Respect\Validation\Validator::', $validation);
                    eval('$ok = ' . $validation . '->validate($this->' . $property . ');');
                }else{
                    $value = $this->$property;
                    eval('$ok = ' . $validation . ';');
                }
                if(!$ok){
                    if (!key_exists($property, $errors))
                        $errors[$property] = [];

                    $errors[$property][] = $error_message;

                }
            }
        }

        if($this->usesTypes() && !$this->_type)
            $errors['type'] = [\MapasCulturais\i::__('O Tipo é obrigatório')];
        elseif($this->usesTypes() && !$this->validateType())
            $errors['type'] = [\MapasCulturais\i::__('Tipo inválido')];

        if($this->usesMetadata()) {
            $errors = $errors + $this->getMetadataValidationErrors();
        }

        if($this->usesTaxonomies())
            $errors = $errors + $this->getTaxonomiesValidationErrors();

        $app->applyHookBoundTo($this, "{$this->hookPrefix}.validationErrors", [&$errors]);
        
        return $errors;
    }


    /**
     * Returns the Doctrine Repository for this entity.
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function repo(){
        return App::i()->repo($this->getClassName());
    }

    /**
     * computed changes in entity
     *
     * @return void
     */
    public function computeChangeSets()
    {
        $app = App::i();

        $uow = $app->em->getUnitOfWork();
        $uow->computeChangeSets();
        $this->_changes = $uow->getEntityChangeSet($this);
    }

    /**
     * Executed before the entity is inserted.
     *
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#lifecycle-events
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#prepersist
     *
     * @hook **entity.insert:before**
     * @hook **entity({$entity_class}).insert:before**
     * @hook **entity.save:before**
     * @hook **entity({$entity_class}).save:before**
     */
    public function prePersist($args = null){
        $app = App::i();
        
        $this->computeChangeSets();

        $hook_prefix = $this->getHookPrefix();

        $app->applyHookBoundTo($this, "{$hook_prefix}.insert:before");

        $this->computeChangeSets();
    }

    /**
     * Executed after the entity is inserted.
     *
     * If the entity uses Metadata, saves the entity metadatas.
     *
     * If the entity uses Taxonomies, saves the terms.
     *
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#lifecycle-events
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#postupdate-postremove-postpersist
     * @see \MapasCulturais\Traits\EntityMetadata::saveMetadata()
     * @see \MapasCulturais\Traits\EntityTaxonomies::saveTerms()
     *
     * @hook **entity.insert:after**
     * @hook **entity({$entity_class}).insert:after**
     * @hook **entity.save:after**
     * @hook **entity({$entity_class}).save:after**
     */
    public function postPersist($args = null){
        $app = App::i();
        
        $hook_prefix = $this->getHookPrefix();

        $app->applyHookBoundTo($this, "{$hook_prefix}.insert:after");

        if ($this->usesPermissionCache()) {
            $this->createPermissionsCacheForUsers([$this->ownerUser]);
            $app->enqueueEntityToPCacheRecreation($this);
        }
    }

    /**
     * Executed before the entity is removed.
     *
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#lifecycle-events
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#preremove
     *
     * @hook **entity.remove:before**
     * @hook **entity({$entity_class}).remove:before**
     */
    public function preRemove($args = null){
        $app = App::i();
        
        $hook_prefix = $this->getHookPrefix();

        $app->applyHookBoundTo($this, "{$hook_prefix}.remove:before");
    }

    /**
     * Executed after the entity is removed.
     *
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#lifecycle-events
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#postupdate-postremove-postpersist
     *
     * @hook **entity.remove:after**
     * @hook **entity({$entity_class}).remove:after**
     */
    public function postRemove($args = null){
        $app = App::i();
        
        $hook_prefix = $this->getHookPrefix();

        $app->applyHookBoundTo($this, "{$hook_prefix}.remove:after");

        if($this->usesPermissionCache()){
            $this->deletePermissionsCache();
        }

        if($this->usesRevision()) {
            //$this->_newDeletedRevision();
        }
    }

    /**
     * Executed before the entity is updated.
     *
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#lifecycle-events
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#preupdate
     *
     * @hook **entity.update:before**
     * @hook **entity({$entity_class}).update:before**
     * @hook **entity.save:before**
     * @hook **entity({$entity_class}).save:before**
     */
    public function preUpdate($args = null){
        $app = App::i();
        $this->computeChangeSets();
        $hook_prefix = $this->getHookPrefix();
        $app->applyHookBoundTo($this, "{$hook_prefix}.update:before");

        $this->computeChangeSets();
        
        if (property_exists($this, 'updateTimestamp') && $this->isUpdateTimestampEnabled()) {
            $this->updateTimestamp = new \DateTime;
        }
    }

    /**
     * Executed after the entity is updated.
     *
     * If the entity uses Metadata, saves the entity metadatas.
     *
     * If the entity uses Taxonomies, saves the terms.
     *
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#lifecycle-events
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#postupdate-postremove-postpersist
     * @see \MapasCulturais\Traits\EntityMetadata::saveMetadata()
     * @see \MapasCulturais\Traits\EntityTaxonomies::saveTerms()
     *
     * @hook **entity.update:after**
     * @hook **entity({$entity_class}).update:after**
     * @hook **entity.save:after**
     * @hook **entity({$entity_class}).save:after**
     *
     * @ORM\PostUpdate
     */
    public function postUpdate($args = null){
        $app = App::i();
        $hook_prefix = $this->getHookPrefix();

        $app->applyHookBoundTo($this, "{$hook_prefix}.update:after");

        $this->clearPermissionCache();
    }
}
