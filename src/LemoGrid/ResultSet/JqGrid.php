<?php

namespace LemoGrid\ResultSet;

use Zend\Stdlib\ArrayObject;

class JqGrid extends ArrayObject implements ResultSetInterface
{
    /**
     * @var array
     */
    protected $userData;

    /**
     * @param  array $userData
     * @return JqGrid
     */
    public function setUserData(array $userData)
    {
        $this->userData = $userData;

        return $this;
    }

    /**
     * @return array
     */
    public function getUserData()
    {
        return $this->userData;
    }
}
