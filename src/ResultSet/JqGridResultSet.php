<?php

namespace Lemo\Grid\ResultSet;

class JqGridResultSet extends AbstractResultSet
{
    /**
     * @var array
     */
    protected $dataUser;

    /**
     * @param  array $dataUser
     * @return JqGridResultSet
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
