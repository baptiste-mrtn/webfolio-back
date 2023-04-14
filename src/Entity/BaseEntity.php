<?php

namespace App\Entity;

use App\Utils\CryptUtils;
use Symfony\Component\Serializer\Annotation\Groups;

abstract class BaseEntity
{
    /** 
     *  
     * @var string|null
     * @Groups({"list","idcrypt","edit"})
     */
    protected $idcrypt;

    /** @var  ?string */
    protected $parentId;


    /**
     * Get the value of idcrypt
     *
     * @return  string|null
     */
    public function getIdCrypt()
    {
        return $this->idcrypt;
    }

    /**
     * Set the value of idcrypt
     *
     * @param  string|null  $idcrypt
     *
     * @return  self
     */
    public function setIdcrypt($idcrypt)
    {
        $this->idcrypt = $idcrypt;

        return $this;
    }

    /**
     * getId
     *
     * @return int
     */
    public  function getId()
    {
    }

    public function cryptId()
    {
        $this->idcrypt = CryptUtils::cryptId($this->getId());
    }
}
