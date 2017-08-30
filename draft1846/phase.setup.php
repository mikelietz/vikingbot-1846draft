<?php
class phaseDraft1846Setup {
  var $r;
  var $desc;

  function __construct($root) {
    $this->r = $root;
    $this->desc = 'Setting up Draft';
  }
  function init() {
    $this->setupBase();
    $this->setupPlayers();
    $this->r->mChan("The Private Companies will now be distributed. The (reversed) player order is: " . $this->r->playerList());
    $this->r->setPhase('draft');
  }
  function setupBase() {
    $playerCount = count($this->r->players);
    $this->r->deck = new companyDeck( $this->r, $playerCount );

    $this->pickCorporations( $playerCount );

    $this->r->hand = array();

    $this->r->started = true;
  }

  function setupPlayers() {
    $first = null;
    $last = null;
    $nicks = array_keys($this->r->players);
    shuffle($nicks);
    $new = array();
    foreach($nicks as $nick) $new[$nick] = $this->r->players[$nick];
    $this->r->players = $new;
    foreach($this->r->players as $nick => $player) {
      $player->purchases = array();
      if($last == null) {
        $first = $player;
        $last = $player;
        continue;
      }
      $player->right = $last;
      $last->left = $player;
      $last = $player;
    }
    $first->right = $last;
    $last->left = $first;
    $this->r->currentPlayer = $first;
  }

  function pickCorporations( $playerCount ) {
    $corporations = array( 'C&O', 'Erie', 'PA' );

    shuffle( $corporations );

    $corporations = array_slice( $corporations, 0, $playerCount - 2 );

    $corporations = array_merge( array( 'GT', 'IC', 'NYC', 'B&O' ), $corporations );

    $this->r->mChan("The Corporations for this game are: " . implode( ', ', $corporations ) );
  }

}
?>
