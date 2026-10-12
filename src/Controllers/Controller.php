<?php

namespace Template\Controllers;

abstract class Controller {
    protected $manager;

    public function __construct($manager) {
        $this->manager = $manager;
    }
}   