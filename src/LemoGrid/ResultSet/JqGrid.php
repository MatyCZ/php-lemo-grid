<?php

namespace LemoGrid\ResultSet;

class JqGrid extends AbstractResultSet
{
    /**
     * @var array
     */
    protected $dataUser;

    /**
     * @param  array $dataUser
     * @return JqGrid
     */
    public function setDataUser(array $dataUser)
    {
        $this->dataUser = $dataUser;

        return $this;
    }

    /**
     * @return array
     */
    public function getDataUser()
    {
        return $this->dataUser;
    }
}
