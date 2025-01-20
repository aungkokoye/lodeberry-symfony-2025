<?php

namespace App\Tests\Integration\Service;

use App\Service\CacheManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CacheManagerTest extends KernelTestCase
{
    const VALUE = "test_cache_value_string";
    const KEY = "test_cache_key_string";

    public function testGetAndSetCatchItem()
    {
        self::bootKernel();

        $cacheManager = static::getContainer()->get(CacheManager::class);
        $this->assertInstanceOf(CacheManager::class, $cacheManager );

          /** @var CacheManager $cacheManager */
        $cacheManager->set(self::KEY, SELF::VALUE);

        $this->assertEquals(self::VALUE, $cacheManager->get(self::KEY));
    }

    public function testReturnNullWhileUsingInvalidKey()
    {
        self::bootKernel();
        
        $cacheManager = static::getContainer()->get(CacheManager::class);
        $this->assertInstanceOf(CacheManager::class, $cacheManager );
        $this->assertEquals(null, $cacheManager->get(self::KEY . "_invalid"));
    }
}
