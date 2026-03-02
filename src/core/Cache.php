<?php
declare(strict_types=1);

namespace MapasCulturais;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;

/**
 * Classe adaptadora para o Symfony Cache mantendo uma interface similar ao Doctrine Cache
 * 
 * @property-read AdapterInterface $adapter O adaptador de cache do Symfony
 * @property-read array $items Itens de cache carregados em memória
 * @property-read string $namespace Namespace atual do cache
 * 
 * @package MapasCulturais
 */
class Cache {
    use Traits\MagicGetter,
        Traits\MagicCallers;

    /**
     * O adaptador de cache do Symfony
     * @var AdapterInterface
     */
    protected AdapterInterface $adapter;

    /**
     * Itens de cache carregados em memória
     * @var array
     */
    protected array $items = [];

    /**
     * Namespace atual do cache
     * @var string
     */
    protected string $namespace = 'NAMESPACE#';

    /**
     * Caracteres a serem substituídos nas chaves de cache
     * @var array
     */
    private static $parseFrom = ['{', '}', '(', ')', '/', '\\', '@', ':'];

    /**
     * Substitutos para os caracteres reservados
     * @var array
     */
    private static $parseTo = ['<', '>', '[', ']', '_', '|',  '%', '#'];

    /**
     * Construtor da classe
     * 
     * @param AdapterInterface $adapter
     */
    function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Higieniza a chave de cache e adiciona o namespace
     * 
     * @param string $key
     * @return string
     */
    private function parseKey(string $key): string {
        // caracteres reservados: {}()/\@:
        return $this->namespace . str_replace(self::$parseFrom, self::$parseTo, $key);
    }

    /**
     * Obtém um item de cache do adaptador
     * 
     * @param string $key
     * @return CacheItem
     */
    protected function getCacheItem(string $key): CacheItem {
        $key = $this->parseKey($key);
        $item = $this->items[$key] ?? null;
        if (!$item) {
            $item = $this->adapter->getItem($key);
            if($item->isHit()) {
                $this->items[$key] = $item;
            }
        } 
        return $item;
    }

    /**
     * Salva um valor no cache
     * 
     * @param string $key
     * @param mixed $value
     * @param int $cache_ttl tempo de vida em segundos (padrão: 1 dia)
     * @return void
     */
    function save(string $key, $value, int $cache_ttl = DAY_IN_SECONDS) {
        $item = $this->getCacheItem($key);
        $item->expiresAfter($cache_ttl);
        $item->set($value);
        $this->adapter->save($item);
    }

    /**
     * Verifica se o cache contém o item solicitado
     * 
     * @param string $key
     * @return bool
     */
    function contains(string $key):bool {
        $item = $this->getCacheItem($key);
        return $item->isHit();
    }

    /**
     * Remove um item do cache
     * 
     * @param string $key
     * @return void
     */
    function delete(string $key) {
        $key = $this->parseKey($key);
        $this->adapter->deleteItem($key);
        unset($this->items[$key]);
    }

    /**
     * Limpa todo o cache do namespace atual
     * 
     * @return void
     */
    function flushAll() {
        $this->adapter->clear($this->namespace);
    }

    /**
     * Limpa todo o cache do namespace atual (sinônimo de flushAll)
     * 
     * @return void
     */
    function deleteAll() {
        $this->flushAll();
    }

    /**
     * Define o namespace do cache
     * 
     * @param string|null $namespace
     * @return void
     */
    function setNamespace(?string $namespace = null) {
        // caracteres reservados: {}()/\@:
        $namespace = str_replace(self::$parseFrom, self::$parseTo, $namespace);
        $this->namespace = "NAMESPACE<{$namespace}>#";
    }

    /**
     * Busca um valor no cache
     * 
     * @param string $key
     * @return mixed|null
     */
    function fetch(string $key) {
        if ($this->contains($key)) {
            $item = $this->getCacheItem($key);
            return $item->get();
        }
    }
}