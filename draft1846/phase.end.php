<?php
class phaseDraft1846End {
  var $r;
  var $desc;

  function __construct($root) {
    $this->r = $root;
    $this->desc = 'End Game';
  }
  function init() {
    $this->r->mChan( "Setup is now complete." );
    $this->r->score();
    $this->r->setPhase( 'nogame' );
    return;
  }
}
?>
