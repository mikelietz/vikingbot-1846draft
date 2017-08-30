<?php
require_once('draft1846/player.php');

require_once('draft1846/phase.nogame.php');
require_once('draft1846/phase.setup.php');
require_once('draft1846/phase.draft.php');
require_once('draft1846/phase.selloff.php');
require_once('draft1846/phase.end.php');

require_once('generic/deck.base.php');
require_once('draft1846/deck.companies.php');

class draft1846 implements pluginInterface {
  var $config;
  var $socket;
  var $channel;
  var $game;
  var $started;

  var $players;
  var $playerMap;
  var $phases;
  var $phase;
  var $currentPlayer;

  var $deck;
  var $hand;
  var $wild;
  var $autoScore;

  function init($config, $socket) {
    $this->config = $config;
    $this->socket = $socket;
    $this->channel = '#Setup1846';
    $this->game = 'Set up 1846';
    $this->started = false;
    $this->autoScore = null;

    $this->phases = array();
    $this->phases['nogame'] = new phaseDraft1846NoGame($this);
    $this->phases['setup'] = new phaseDraft1846Setup($this);
    $this->phases['draft'] = new phaseDraft1846Draft($this);
    $this->phases['selloff'] = new phaseDraft1846Selloff($this);
    $this->phases['end'] = new phaseDraft1846End($this);

    $this->setPhase('nogame');
  }

  function tick() {

  }

  function onMessage($from, $channel, $msg) {
    if($channel != $this->channel) return;
    if($msg{0} != '!') return;
    $args = explode(" ", $msg);
    $cmdRaw = array_shift($args);
    $cmd = 'cmd'.strtolower(substr($cmdRaw, 1));
    if(trim($cmd) == 'cmd') return;
    if(method_exists($this, $cmd)) {
      $this->$cmd($from, $args);
    } else if(method_exists($this->phase, $cmd)) {
      $this->phase->$cmd($from, $args);
    } else {
      $this->mChan("$from: $cmdRaw does not exist in the phase '{$this->phase->desc}'.");
    }

  }
  function onNick($from, $to) {
    if(isset($this->players[$from])) {
      $this->players[$to] = $this->players[$from];
      $this->players[$to]->nick = $to;
      unset($this->players[$from]);
    }
  }
  function onQuit($from) {

  }

  function destroy() {

  }
  function onData($data) {
    $tmp = explode(" ", trim($data));
    $from = getNick($tmp[0]);
    if(!(isset($tmp[1]))) continue;
    if($tmp[1] == 'NICK') $this->onNick($from, str_replace(":", "", $tmp[2]));
    else if($tmp[1] == 'PART' && trim(strtolower($this->channel)) == trim(strtolower($tmp[2]))) $this->onQuit($from);
    else if($tmp[1] == 'QUIT') $this->onQuit($from);
  }
  function mChan($message) {
    sendMessage($this->socket, $this->channel, $message);
  }
  function nUser($nick, $message) {
    $player = $this->findPlayer($nick);
    if($player == null) sendNotice($this->socket, $nick, $message);
    else {
      if($player->msgType == 'msg') sendMessage($this->socket, $nick, $message);
      else sendNotice($this->socket, $nick, $message);
    }
  }
  function playerList() {
    $players = array_keys($this->players);
    return implode(", ", $players);
  }
  function setPhase($phase) {
    $this->phase = $this->phases[$phase];
    $this->phase->init();
  }
  function checkCurrentPlayer($from, $cmd) {
    if($this->currentPlayer->nick != $from) {
      $this->mChan("$from: Please wait your turn to $cmd.");
      return false;
    }
    return true;
  }
  function checkArgs($from, $args, $min, $max = -1) {
    if($max == -1) $max = $min;
    $argc = count($args);
    if($argc < $min || $argc > $max) {
      if($min == $max) $this->mChan("$from: That command only takes $min argument(s). Please try again.");
      else $this->mChan("$from: That command requires $min-$max arguments. Please try again.");
      return false;
    }
    return true;
  }
  function findPlayer($nick) {
    if(!(isset($this->playerMap[strtolower($nick)]))) return null;
    return $this->playerMap[strtolower($nick)];
  }
  function cmdhelp($from, $args) {
    $this->nUser( $from, "This is an IRC implementation of the beginning setup for 1846." );
    $this->nUser( $from, "The rules can be found online at http://www.deepthoughtgames.com/games/1846/rules.pdf" );
    $this->nUser( $from, "!start - Start the draft." );
    $this->nUser( $from, "!join - Join." );
    $this->nUser( $from, "!part - Part." );
    $this->nUser( $from, "!notice - Bot will send notices for your hand." );
    $this->nUser( $from, "!msg - Bot will send messages for your hand." );
    $this->nUser( $from, "!pick <card> | !p <card> - Pick a card." );
    $this->nUser( $from, "!buy | !b - Buy the last company." );
    $this->nUser( $from, "!pass | !p - Do not buy the last company." );
  }
  function displayHand($drawn = '') {
    $display = array();
    foreach($this->hand as $letter => $card) {
      $display[] = "$letter. ".$card->display();
    }
    return implode(', ', $display);
  }
  function score() {
    $playerCount = count( $this->players );
    $bank = 9000 - ($playerCount < 5)*1500 - ($playerCount < 4)*1000 - $playerCount * 400;

    $scores = array();
    foreach($this->players as $nick => $player) {
      $last = null;
      $run = 0;
      $bestRun = 0;
      $score = 0;
      foreach($player->purchases as $card) {
        $bank += $card->price + ($card->debt - $card->price) * ($card->debt != null);
	// here's how it actually works for MC and B4 - the player pays the price into the corp treasury, and the debt to the bank.
        $player->money -= $card->price + $card->debt;
      }
    }

    foreach( array_reverse( $this->players ) as $nick => $player) {
      $this->mChan("$nick ({$player->money}) ".$player->displayPurchases().".");
    }
    $this->mChan( "Bank has {$bank}." );
  }

  function cmdnotice($from, $args) {
    $player = $this->findPlayer($from);
    if($player == null) return;
    $player->msgType = 'notice';
    $this->mChan("Messages will now be sent to you as a notice.");
  }
  function cmdmsg($from, $args) {
    $player = $this->findPlayer($from);
    if($player == null) return;
    $player->msgType = 'msg';
    $this->mChan("Messages will now be sent to you as private message.");
  }
}
?>
