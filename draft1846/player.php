<?php
class draft1846Player {
  var $r;
  var $nick;
  var $money;
  var $msgType;

  var $purchases;

  function __construct($root, $nick) {
    $this->r = $root;
    $this->nick = $nick;
    $this->msgType = 'notice';
    $this->money = 400;
  }
  function init() {
  }
  function displayPurchases() {
    $display = array();
    if(count($this->purchases) == 0) return '<None>';
    foreach($this->purchases as $card) {
      $display[] = $card->name;
    }
    return implode( ", ", $display );
  }
  function listHoldings() {
    $display = array();
    if(count($this->purchases) == 0) return array('<None>');
    foreach($this->purchases as $card) {
      $display[] = $card->display();
    }
    return display;
  }
  function calculateMoney() {
    $money = $this->money;
    foreach( $this->purchases as $card ) {
      $money -= $card->price + $card->debt;
    }
    return $money;
  }
}
?>
