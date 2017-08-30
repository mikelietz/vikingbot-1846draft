<?php
class company {
  var $r;
  var $name;
  var $price;
  var $income;
  var $debt;
  var $property;
  var $blank;

  function __construct( $root, $name = null, $price = null, $income = null, $debt = null, $property = null ) {
    $this->r = $root;
    $this->name = $name == null ? 'blank' : $name;
    $this->price = $price;
    $this->income = $income;
    $this->debt = $debt;
    $this->property = $property;
    $this->blank = $name == null ? true : false;
  }
  function full_display() {
    return $this->blank ? "blank" : sprintf( "%s - Price: $%d %s: $%d - %s",
	$this->name, $this->price, $this->debt != null ? "Debt" : "Income", $this->debt != null ? $this->debt : $this->income, $this->property );
  }
  function display() {
    return $this->blank ? "blank" : sprintf( "%s (P:$%d %s:$%d)",
	$this->name, $this->price, $this->debt != null ? "D" : "I", $this->debt != null ? $this->debt : $this->income );
  }
}
class companyDeck extends deck {
  var $lastCompany;

  function count() {
    return count( $this->deck ) + count( $this->discard );
  }

  function take() {
    if(count($this->deck) == 0) {
      if(count($this->discard) == 0) return null;
      $this->deck = $this->discard;
      $this->discard = array();
    }
    $card = array_shift( $this->deck );
    return $card;
  }

  function __construct($root) {
    $this->r = $root;
    $this->deck = array();
    $this->discard = array();

    $this->lastCompany = null;

    $this->cards[] = new company( $root, 'Big 4 Railroad', 40, 0, 60,
	"Operates as a miniature corporation with a 2T, no stock, and no extra tokens, until purchased or removed." );
    $this->cards[] = new company( $root, 'Chicago & W. Indiana', 60, 10, 0,
	"Reserves a token placement in Chicago for its purchasing corporation." );
    $this->cards[] = new company( $root, 'Mail Contract', 80, 0, 0,
	"Once purchased by a corporation, adds $10 per location visited by one of its trains to that route's value. Unlike other private companies, this company is never removed from play." );
    $this->cards[] = new company( $root, 'Michigan Southern', 60, 0, 80,
	"Operates as a miniature corporation with a 2T, no stock, and no extra tokens, until purchased or removed." );

    $playerCount = count( $this->r->players );

    $group = array();
    $group[] = new company( $root, 'Lake Shore Line', 40, 15, 0,
	"Allows its purchasing corporation to upgrade a yellow tile in Cleveland or Toledo at no cost." );
    $group[] = new company( $root, 'Michigan Central Railroad', 40, 15, 0,
	"Allows its purchasing corporation to lay one or two extra yellow track tiles in either or both of its hexes (see board) at no cost." );
    $group[] = new company( $root, 'Ohio & Indiana Railroad', 40, 15, 0,
	"Allows its purchasing corporation to lay one or two extra yellow track tiles in either or both of its hexes (see board) at no cost." );

    shuffle( $group );
    $group = array_slice( $group, 0, $playerCount - 2 );
    $this->cards = array_merge( $this->cards, $group );

    $group = array();
    $group[] = new company( $root, 'Tunnel Blasting Company', 60, 20, 0,
	"Reduces, for the owning corporation, the cost of laying all mountain tiles and tunnel/pass hexsides by $20." );
    $group[] = new company( $root, 'Meat Packing Company', 60, 15, 0,
	"Allows its purchasing corporation to place the $30 meat packing token in either Chicago or St. Louis. This token adds $30 to all routes including that city or location run by the purchasing corporation." );
    $group[] = new company( $root, 'Steamboat Company', 40, 10, 0,
	"Allows its purchasing corporation, before running its routes, to place or shift the $20 port token to any port space, adding $20 or $40 to all routes including that location for the purchasing corporation." );

    shuffle( $group );
    $group = array_slice( $group, 0, $playerCount - 2 );
    $this->cards = array_merge( $this->cards, $group );

    $this->r->mChan( "The Private Companies in this draft: " );

    foreach( $this->cards as $card ) {
	$this->r->mChan( $card->full_display() );
    }

    // add in the blank cards;
    for($i=0; $i<$playerCount; $i++) $this->cards[] = new company( $root );

    shuffle( $this->cards );

    $this->deck = $this->cards;
  }

  function done( /*$root*/ ) {
    $blanks = 0;
    $companies = 0;

// $this->r->mChan( sprintf( "Checking for doneness. Deck: %d Discard %d Total %d", count( $this->deck ), count( $this->discard ), $this->r->deck->count()));
$this->r->mChan( sprintf( "There are %d cards in the deck.", $this->r->deck->count()));
    foreach( $this->deck as $card ) {
      if( $card->blank == true ) {
	$blanks++;
      } else {
        $companies++;
	// set the company to the last one, only matters when there's only one.
	$this->lastCompany = $card;
      }
    }
    foreach( $this->discard as $card ) {
      if( $card->blank == true ) {
	$blanks++;
      } else {
        $companies++;
	// set the company to the last one, only matters when there's only one.
	$this->lastCompany = $card;
      }
    }
// $this->r->mChan( "Companies: {$companies} Blanks: {$blanks} " );
    return $companies == 1 && $blanks == 0;
  }
}
?>
