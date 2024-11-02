<?php

class CoicopCat {

  public $id;
  public $color;
  public $nameFi;
  public $nameEn;
  public $subs;
  
  public function __construct($id, $color, $nameFi, $nameEn) {
    $this->id = $id;
    $this->color = $color;
    $this->nameFi = $nameFi;
    $this->nameEn = $nameEn;
    $this->subs = array();
  }

  function addSub($id, $nameFi, $nameEn) {
    $new = new CoicopSub($id, $this->id, $nameFi, $nameEn);
    $this->subs[] = &$new;
    return count($this->subs);
  }

  function addHelp($id, $textFi, $textEn) {
    $sub = $this->getSub($id);
    $sub->addHelp($textFi, $textEn);
    return TRUE;
  }

  function getSub(&$id) {
    for ($i=0; $i<count($this->subs); $i++) {
      $sub = $this->subs[$i];
      # if ($sub->id == $id) {
      if (strpos($id, $sub->id) === 0) {
        return $sub;
      }
      # trigger_error("No sub found with id $id", E_USER_WARNING);
      # print("<pre>No sub found with id $id</pre>\n");
    }
  }

  function getSubs() {
    return $this->subs;
  }

}

?>
