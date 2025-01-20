<?php

namespace App\Service;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;


class CacheManager
{
    private FilesystemAdapter $cacheAdator;

    public function __construct()
    {
        $this->cacheAdator = new FilesystemAdapter();
    }

    public function get(string $key): ?string
    {
        $item = $this->cacheAdator->getItem($key);
        if (!$item->isHit()) {
            return null;
        }

        return  $item->get();
    }

    public function set(string $key, string $value): void
    {
        $item =  $this->cacheAdator->getItem($key);

        $item->set($value);
        $this->cacheAdator->save($item);
    }

    public function delete(string $key): void
    {
        $this->cacheAdator->deleteItem($key);
    }
}