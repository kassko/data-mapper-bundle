<?php

namespace Solfa\Bundle\ScalesBundle\Scales;

use Kassko\DataAccess\Annotation as DA;

class Behaviour
{
    /**
     * @DA\Field
     */
    private $fluoInTheNight;
    /**
     * @DA\Field
     */
    private $invisibleInTheWoods;

    /**
     * Gets the value of fluoTheNight.
     *
     * @return mixed
     */
    public function getFluoInTheNight()
    {
        return $this->fluoInTheNight;
    }

    /**
     * Sets the value of fluoInTheNight.
     *
     * @param mixed $fluoInTheNight the fluo the night
     *
     * @return self
     */
    public function setFluoInTheNight($fluoInTheNight)
    {
        $this->fluoInTheNight = $fluoInTheNight;

        return $this;
    }

    /**
     * Gets the value of invisibleInTheWoods.
     *
     * @return mixed
     */
    public function getInvisibleInTheWoods()
    {
        return $this->invisibleInTheWoods;
    }

    /**
     * Sets the value of invisibleInTheWoods.
     *
     * @param mixed $invisibleInTheWoods the invisible in the woods
     *
     * @return self
     */
    public function setInvisibleInTheWoods($invisibleInTheWoods)
    {
        $this->invisibleInTheWoods = $invisibleInTheWoods;

        return $this;
    }
}
