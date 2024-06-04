<?php
declare(strict_types=1);

namespace MapasCulturais;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;

/**
 * Classe adaptadora para o Synfony Cache mantendo a interface do Doctrine Cache
 * 
 * @property-read AdapterInterface $adapter
 * @package MapasCulturais
 */
class Cache {
    use Traits\MagicGetter,
        Traits\MagicCallers;

    protected AdapterInterface $adapter;

    protected array $items = [];

    protected string $namespace = 'NAMESPACE#';

    private static $parseFrom = ['{', '}', '(', ')', '/', '\\', '@', ':'];
    private static $parseTo = ['<', '>', '[', ']', '_', '|',  '%', '#'];

    function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    private function parseKey(string $key): string {
        // caracteres reservados: {}()/\@:
        return $this->namespace . str_replace(self::$parseFrom, self::$parseTo, $key);
    }

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

    function save(string $key, $value, int $cache_ttl = DAY_IN_SECONDS) {
        $item = $this->getCacheItem($key);
        $item->expiresAfter($cache_ttl);
        $item->set($value);
        $this->adapter->save($item);
    }

    function contains(string $key):bool {
        $item = $this->getCacheItem($key);
        return $item->isHit();
    }

    function delete(string $key) {
        $key = $this->parseKey($key);
        $this->adapter->deleteItem($key);
        unset($this->items[$key]);
    }

    function flushAll() {
        $this->adapter->clear($this->namespace);
    }

    function deleteAll() {
        $this->flushAll();
    }

    function setNamespace(string $namespace = null) {
        // caracteres reservados: {}()/\@:
        $namespace = str_replace(self::$parseFrom, self::$parseTo, $namespace);
        $this->namespace = "NAMESPACE<{$namespace}>#";
    }

    function fetch(string $key) {
        if ($this->contains($key)) {
            $item = $this->getCacheItem($key);
            return $item->get();
        }
    }
}