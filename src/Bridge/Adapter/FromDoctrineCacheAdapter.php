<?php

namespace Kassko\Bundle\DataAccessBundle\Bridge\Adapter;

use Kassko\DataAccess\Cache\CacheAdapter as ToDataAccessCacheAdapter;
use Doctrine\Common\Cache\Cache as DoctrineCacheInterface;

/**
 * A cache adapter to use DataAccess cache interface instead of Doctrine cache interface.
 *
 * @author kko
 */
class FromDoctrineCacheAdapter extends ToDataAccessCacheAdapter
{
    public function __construct(DoctrineCacheInterface $doctrineCache = null)
    {
        $this->wrappedCache = $doctrineCache;
    }

    public function setDoctrineCache(DoctrineCacheInterface $doctrineCache)
    {
        parent::setWrappedCache($doctrineCache);
    }

    public function fetch($id)
    {
        return $this->wrappedCache->fetch($id);
    }

    /**
     * {@inheritdoc}
     */
    public function contains($id)
    {
        return $this->wrappedCache->contains($id);
    }

    /**
     * {@inheritdoc}
     */
    public function save($id, $data, $lifeTime = 0)
    {
        return $this->wrappedCache->save($id, $data, $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return $this->wrappedCache->delete($id);
    }
}
