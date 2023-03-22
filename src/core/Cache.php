<?php
declare(strict_types=1);

namespace MapasCulturais;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;

/**
 * @property-read AdapterInterface $adapter
 * @package MapasCulturais
 */
class Cache {
    use Traits\MagicGetter;

    protected AdapterInterface $adapter;

    protected array $items = [];

    function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    protected function getCacheItem(string $key):CacheItem {
        if (!isset($this->items[$key])) {
            $this->items[$key] = $this->adapter->getItem($key);
        } 
        return $this->items[$key];
    }

    function save(string $key, $value, int $cache_ttl = null) {
        $item = $this->getCacheItem($key);
        $item->expiresAfter($cache_ttl);
        $item->set($value);

    }

    function contains(string $key):bool {
        $item = $this->getCacheItem($key);
        return $item->isHit();
    }

    function delete(string $key) {
        $this->adapter->deleteItem($key);
        unset($this->items[$key]);
    }

    function flushAll() {
        $this->adapter->clear();
    }
}