<?php

namespace Template\Controllers;
use Template\Controllers\Controller;
use Template\Models\ManagerTemplate;

class ControllerTemplate extends Controller {
    public function __construct() {
        return parent::__construct(new ManagerTemplate());
    }

    public function getMethod() {
        require VIEWS . "get.php";
    }

    public function postMethod() {
        $this->manager->post();
        header("Location: /");
    }
}