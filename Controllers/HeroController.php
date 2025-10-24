<?php
require_once 'Model/Hero.php';

class HeroController
{
    private $dataFile;

    public function __construct(){
        $this->dataFile = 'data/heroes.json';
        $this->ensureDataFileExists();
    }

    private function ensureDataFileExists(){
        if(!file_exists())
    }
}