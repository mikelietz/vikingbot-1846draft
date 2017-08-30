<?php
class phaseDraft1846Selloff {
  var $r;
  var $desc;

  function __construct($root) {
    $this->r = $root;
    $this->desc = 'buy the last company';
  }
  function init() {
    $company = $this->r->deck->lastCompany;
    if( $company->price == 0 ) {
      $this->r->mChan( $this->r->currentPlayer->nick." gets {$company->name} for free." );
      $this->r->currentPlayer->purchases[] = $this->r->deck->lastCompany;
      $this->r->setPhase( 'end' );
    } else {
      $this->r->nUser( $this->r->currentPlayer->nick, "You have \${$this->r->currentPlayer->calculateMoney()}");
      foreach( $this->r->currentPlayer->listHoldings() as $card ) {
        $this->r->nUser( $this->r->currentPlayer->nick, $card );
      }
      $this->r->mChan($this->r->currentPlayer->nick.", you may !buy {$company->name} for \${$company->price}, or !pass.");
    }
  }
  function cmdp($from, $args) {
    $this->cmdpass($from, $args);
  }
  function cmdpass($from, $args) {
    if(!($this->r->checkCurrentPlayer($from, 'buy the last company'))) return;
    $this->r->mChan($this->r->currentPlayer->nick." passes." );
    $this->r->deck->lastCompany->price -= 10;

    $this->r->currentPlayer = $this->r->currentPlayer->left;
    $this->r->setPhase('selloff');
  }
  function cmdb($from, $args) {
    $this->cmdbuy($from, $args);
  }
  function cmdbuy($from, $args) {
    if(!($this->r->checkCurrentPlayer($from, 'buy the last company'))) return;
    if(!($this->r->checkArgs($from, $args, 0))) return;

    $this->r->currentPlayer->purchases[] = $this->r->deck->lastCompany;
    $this->r->setPhase('end');
  }
}
?>
