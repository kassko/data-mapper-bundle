<?php

namespace Solfa\Bundle\ScalesBundle\Scales;

use Kassko\DataAccess\Annotation as DA;
use Kassko\DataAccess\Hydrator\HydrationContextInterface;
use Kassko\DataAccess\Hydrator\Value;
use Kassko\DataAccess\ObjectExtension\LazyLoadableTrait;
use Kassko\DataAccess\ObjectExtension\LoggableTrait;

class Keyboard
{
    use LazyLoadableTrait;
    //use LoggableTrait;

    /**
     * @DA\Id
     * @DA\Field(readStrategy="readBrand")
     */
    private $brand;

    /**
     * @DA\Field
     */
    private $color;

    /**
     * @DA\Provider(lazyLoading=true, class="Solfa\Bundle\ScalesBundle\Scales\ShopManager", method="loadShops")
     * @DA\Field
     */
    private $shops;

    /**
     * Gets the value of brand.
     *
     * @return mixed
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Sets the value of brand.
     *
     * @param mixed $brand the brand
     *
     * @return self
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Gets the value of color.
     *
     * @return mixed
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Sets the value of color.
     *
     * @param mixed $color the color
     *
     * @return self
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Gets the value of shops.
     *
     * @return mixed
     */
    public function getShops()
    {
        /*
        if ($logger = $this->getLogger()) {
            var_dump($logger);
        }
        */
        $this->loadProperty('shops');

        return $this->shops;
    }

    /**
     * Sets the value of shops.
     *
     * @param mixed $shops the shops
     *
     * @return self
     */
    public function setShops(array $shops)
    {
        $this->shops = $shops;

        return $this;
    }

    public function readBrand(Value $value, HydrationContextInterface $context)
    {
        $value->value = '===>'.$value->value;
    }
}