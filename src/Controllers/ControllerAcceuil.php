<?php

namespace Template\Controllers;

use Template\Controllers\Controller;
use Template\Models\ManagerImage;
use Template\Models\ManagerUsers;

class ControllerAcceuil extends Controller
{
    private $managerImage;

    public function __construct()
    {
        parent::__construct(new ManagerUsers());
        $this->managerImage = new ManagerImage();
    }

    public function getAcceuil()
    {
        if (!isset($_COOKIE['id'])) {
            header("Location: /login");
        } else {
            $Users = $this->managerImage->find();
            $user = $this->manager->getUser($_COOKIE["id"]);
            $name = $user["name"];
            require VIEWS . "Acceuil.php";
        }
    }

    public function postMethod()
    {
        $this->manager->post();
        header("Location: /");
    }

    public function postImg()
    {
        $this->managerImage->newImage($_COOKIE['id']);
    }

    public function sup()
    {
        $this->managerImage->sup($_POST["id"]);
        header("Location: /");
    }
}
