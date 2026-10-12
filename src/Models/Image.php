<?php

namespace Template\Models;

use JsonSerializable;

class Image implements JsonSerializable
{
    private $id;
    private $src;
    private $id_user;

    public function jsonSerialize(): mixed
    {
        return [
            "id" => $this->getId(),
            "src" => $this->getSRC(),
            "id_user" => $this->getId_user(),
        ];
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getSRC()
    {
        return $this->src;
    }

    /**
     * @param mixed $src
     */
    public function setSRC($src): void
    {
        $this->src = $src;
    }

    /**
     * @return mixed
     */
    public function getId_user()
    {
        return $this->id_user;
    }

    /**
     * @param mixed $id_user
     */
    public function setId_user($id_user): void
    {
        $this->id_user = $id_user;
    }
}
