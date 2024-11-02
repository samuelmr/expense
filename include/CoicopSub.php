<?php

class CoicopSub{

  public $id;
  public $parent;
  public $nameFi;
  public $nameEn;
  public $helpFi;
  public $helpEn;

  function __construct($id, $parent, $nameFi, $nameEn) {
    $this->id = $id;
    $this->parent = $parent;
    $this->nameFi = $nameFi;
    $this->nameEn = $nameEn;
    $this->helpFi = array();
    $this->helpEn = array();
  }

  function addHelp($textFi, $textEn) {
    $this->helpFi[] = $textFi;
    $this->helpEn[] = $textEn;
  }

  function getHelp($lang='en') {
    return (($lang == 'fi') ? $this->helpFi : $this->helpEn);
  }

}