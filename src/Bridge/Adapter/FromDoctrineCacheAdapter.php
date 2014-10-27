<?php

namespace Kassko\DataAccessBundle\Bridge\Adapter;

use Kassko\DataAccess\Cache\CacheAdapterInterface as ToDataAccessCacheAdapterInterface;
use Doctrine\Common\Cache\Cache as DoctrineCacheInterface;

/**
 * A cache adapter to use class resolver container interface instead of Doctrine cache interface.
 *
 * @author kko
 */
class FromDoctrineCacheAdapter implements ToDataAccessCacheAdapterInterface
{
    private $doctrineCache;

    public function __construct(DoctrineCacheInterface $doctrineCache = null)
    {
        $this->doctrineCache = $doctrineCache;
    }

    public function setWrappedCache(DoctrineCacheInterface $doctrineCache)
    {
        $this->doctrineCache = $doctrineCache;
    }

    function fetch($id)
    {
        return $this->doctrineCache->fetch($id);
    }

    /**
     * {@inheritdoc}
     */
    function contains($id)
    {
        return $this->doctrineCache->contains($id);
    }

    /**
     * {@inheritdoc}
     */
    function save($id, $data, $lifeTime = 0)
    {
        return $this->doctrineCache->save($id, $data, $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    function delete($id)
    {
        return $this->doctrineCache->delete($id);
    }
}
