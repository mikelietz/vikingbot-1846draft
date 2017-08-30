<?php
class phaseDraft1846Draft {
  var $r;
  var $desc;

  function __construct($root) {
    $this->r = $root;
    $this->desc = 'Choosing a card';
  }
  function init() {
    $all_blanks = true;
    $top_card = null;
//$this->r->mChan( sprintf( "Deck has %d cards", $this->r->deck->count() ) );
    if( $this->r->deck->done() ) {
      $this->r->mChan( "Only {$this->r->deck->lastCompany->name} remains." );
      $this->r->setPhase('selloff');
      return;
    }

    $playerCount = count($this->r->players);
    for( $i = 0; $i < $playerCount + 2; $i++ ) {
      $top_card = $this->r->deck->take();
      if ( $top_card == null ) break;
      if ( ! $top_card->blank ) {
        $all_blanks = false;
      }
      $this->r->hand[] = $top_card;
    }

    if ( $all_blanks ) {
$this->r->mChan( "Player has drawn all blanks." );
      $this->r->setPhase('end');
      return;
    }

    shuffle( $this->r->hand );

    $letters = array( 'A', 'B', 'C', 'D', 'E', 'F', 'G' );
    $newHand = array();
    $l = 0;
    $drawn = '';
    foreach($this->r->hand as $card) {
      $letter = $letters[$l++];
      $newHand[$letter] = $card;
      if($card == $drawn) $drawn = $letter;
    }
    $this->r->hand = $newHand;

    $this->r->nUser($this->r->currentPlayer->nick, "You have \${$this->r->currentPlayer->calculateMoney()}. " . (count($this->r->currentPlayer->holdings) > 0 ? "You hold these cards already:" : "" ) );
      foreach( $this->r->currentPlayer->listHoldings() as $card ) {
        $this->r->nUser( $this->r->currentPlayer->nick, $card );
      }
    $this->r->mChan($this->r->currentPlayer->nick.", you're up. Please !pick a card.");
    $this->r->nUser($this->r->currentPlayer->nick, "You drew {$this->r->displayHand($drawn)}." );
  }
  function cmdpa($from, $args) {
    $this->cmdpick($from, array('a'));
  }
  function cmdpb($from, $args) {
    $this->cmdpick($from, array('b'));
  }
  function cmdpc($from, $args) {
    $this->cmdpick($from, array('c'));
  }
  function cmdpd($from, $args) {
    $this->cmdpick($from, array('d'));
  }
  function cmdpe($from, $args) {
    $this->cmdpick($from, array('e'));
  }
  function cmdpf($from, $args) {
    $this->cmdpick($from, array('f'));
  }
  function cmdpg($from, $args) {
    $this->cmdpick($from, array('g'));
  }
  function cmdp($from, $args) {
    $this->cmdpick($from, $args);
  }
  function cmdpick($from, $args) {
    if(!($this->r->checkCurrentPlayer($from, 'pick a card'))) return;
    if(!($this->r->checkArgs($from, $args, 1))) return;
    $card = strtoupper($args[0]);
    if( ! ( isset( $this->r->hand[$card] ) ) ) {
     $this->r->mChan($this->r->currentPlayer->nick.": Please specify a valid card to take.");
      return;
    }

//    if( ! $this->r->hand[$card]->blank ) {
        $this->r->currentPlayer->purchases[] = $this->r->hand[$card];
//    }
//    $this->r->mChan($this->r->currentPlayer->nick." has added to their train: ".$this->r->currentPlayer->displayTrain().".");
    unset( $this->r->hand[$card] );
    shuffle( $this->r->hand );

    while( count($this->r->hand) > 0 ) {
       $this->r->deck->discard( array_pop( $this->r->hand ) );
    }
    $this->r->currentPlayer = $this->r->currentPlayer->left;
    $this->r->setPhase('draft');
  }
}
?>
