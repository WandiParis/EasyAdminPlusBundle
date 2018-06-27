<?php

namespace Lle\EasyAdminPlusBundle\Filter;

/**
 * Description of HiddenEntity
 *
 * @author Jérôme PERAT <jerome@2le.net>
 */
class HiddenEntity
{

    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return (string) $this->value;
    }

    public function getId()
    {
        return $this->value;
    }

}
