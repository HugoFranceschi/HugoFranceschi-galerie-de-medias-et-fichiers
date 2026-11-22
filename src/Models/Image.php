<?php

namespace Template\Models;

use JsonSerializable;

class Image implements JsonSerializable
{
    private $id;
    private $src;
    private $id_user;
    private $id_category;
    private $category_name;
    private $srcName;

    public function jsonSerialize(): mixed
    {
        return [
            "id" => $this->getId(),
            "src" => $this->getSRC(),
            "id_user" => $this->getId_user(),
            "id_category" => $this->getId_category(),
            "category_name" => $this->getCategoryName(),
            "srcName" => $this->getSrcName(),
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

    /**
     * @return mixed
     */
    public function getId_category()
    {
        return $this->id_category;
    }

    /**
     * @param mixed $id_category
     */
    public function setId_category($id_category): void
    {
        $this->id_category = $id_category;
    }

    /**
     * @return mixed
     */
    public function getCategoryName()
    {
        return $this->category_name;
    }

    /**
     * @param mixed $category_name
     */
    public function setCategoryName($category_name): void
    {
        $this->category_name = $category_name;
    }

    /**
     * @return mixed
     */
    public function getSrcName()
    {
        return $this->srcName;
    }

    /**
     * @param mixed $srcName
     */
    public function setSrcName($srcName): void
    {
        $this->srcName = $srcName;
    }
}
