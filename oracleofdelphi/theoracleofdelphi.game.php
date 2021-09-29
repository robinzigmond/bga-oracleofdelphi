<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * TheOracleOfDelphi implementation : © Robin Zigmond <robinzig@hotmail.com>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * theoracleofdelphi.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );
require_once('modules/delphi_mapgenerator.php');

if (!defined("CARD_TYPE_INJURY")) {
    define("CARD_TYPE_INJURY", "injury");
    define("CARD_TYPE_ORACLE", "oracle");
    define("CARD_TYPE_COMPANION", "companion");
    define("CARD_TYPE_EQUIPMENT", "equipment");

    define("CARD_LOCATION_PLAYER", "player");
    define("CARD_LOCATION_INJURY_DECK", "injury_deck");
    define("CARD_LOCATION_INJURY_DISCARD", "injury_discard");
    define("CARD_LOCATION_ORACLE_DECK", "oracle_deck");
    define("CARD_LOCATION_ORACLE_DISCARD", "oracle_discard");
    define("CARD_LOCATION_EQUIPMENT_DECK", "equipment_deck");
    define("CARD_LOCATION_EQUIPMENT_DISCARD", "equipment_discard");
    define("CARD_LOCATION_EQUIPMENT_DISPLAY", "equipment_display");
    define("CARD_LOCATION_COMPANION_DECK", "companion_deck");

    define("GOD_POSEIDON", "poseidon");
    define("GOD_ARTEMIS", "artemis");
    define("GOD_ARES", "ares");
    define("GOD_APOLLON", "apollon");
    define("GOD_APHRODITE", "aphrodite");
    define("GOD_HERMES", "hermes");
}


class TheOracleOfDelphi extends Table
{
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();

        // keep useful "globals" here.
        // Array of all 6 game color strings (defined in material file):
        $this->allColors = [
            MAP_COLOR_RED,
            MAP_COLOR_YELLOW,
            MAP_COLOR_GREEN,
            MAP_COLOR_BLUE,
            MAP_COLOR_BLACK,
            MAP_COLOR_PINK
        ];

        // correspondence of colors with gods
        $this->godColors = [
            MAP_COLOR_RED => GOD_APHRODITE,
            MAP_COLOR_YELLOW => GOD_APOLLON,
            MAP_COLOR_GREEN => GOD_ARTEMIS,
            MAP_COLOR_BLUE => GOD_POSEIDON,
            MAP_COLOR_BLACK => GOD_ARES,
            MAP_COLOR_PINK => GOD_HERMES
        ];
        
        self::initGameStateLabels( array( 
            //    "my_first_global_variable" => 10,
            //    "my_second_global_variable" => 11,
            //      ...
            //    "my_first_game_variant" => 100,
            //    "my_second_game_variant" => 101,
            //      ...
        ) );        
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "theoracleofdelphi";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
 
        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, favors) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $favors = (int)$player["player_table_order"] + 2;
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."','".$favors."')";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/
        // the "first step" of the God tracks varies depending on the playercount. Although it's a very
        // simple calculation we save it here as an instance property for ease of access.
        $this->godFirstStep = 5 - count($players);

        // Init global values with their initial values
        //self::setGameStateInitialValue( 'my_first_global_variable', 0 );
        
        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

        // running through game setup in the rulebook.
        // Step 1 - "setup variable game board" (ie the map)
        $map = $this->setupMap();

        // Step 2 - "setup general supply"
        // mostly various decks of cards (Oracle/Injury/Companion/Equipment). We will store all these
        // in a single "deck" component - initialised below from the material file and other "static"
        // information.
        $this->allCards = self::getNew("module.common.deck");
        $this->allCards->init("card");
        // first create all in single "deck" location (even though this won't be used in the game!)
        $allCards = [];
        // injury and oracle cards: 7 (resp 5) of each of the 6 colours
        $index = 0;
        foreach ($this->allColors as $color) {
            $allCards[] = [
                "type" => CARD_TYPE_INJURY,
                "type_arg" => $index,
                "nbr" => 7
            ];
            $allCards[] = [
                "type" => CARD_TYPE_ORACLE,
                "type_arg" => $index,
                "nbr" => 5
            ];
            $index++;
        }
        // companion and equipment cards, defined in material file (although the only information we are using
        // here is the number of cards of each type!)
        foreach($this->equipmentCards as $cardId => $card) {
            $allCards[] = [
                "type" => CARD_TYPE_EQUIPMENT,
                "type_arg" => $cardId,
                "nbr" => 1
            ];
        }
        foreach($this->companionCards as $cardId => $card) {
            $allCards[] = [
                "type" => CARD_TYPE_COMPANION,
                "type_arg" => $cardId,
                "nbr" => 1
            ];
        }
        $this->allCards->createCards($allCards);

        // then find the cards of each apppropriate "type" and move to the appropriate locations
        $cardsWithIds = $this->allCards->getCardsInLocation("deck"); // that's where they'll be at this stage
        $oracleCardIds = [];
        $injuryCardIds = [];
        $equipmentCardIds = [];
        $companionCardIds = [];
        foreach($cardsWithIds as $id => $card) {
            switch ($card["type"]) {
                case CARD_TYPE_ORACLE:
                    $oracleCardIds[] = $id;
                    break;
                case CARD_TYPE_INJURY:
                    $injuryCardIds[] = $id;
                    break;
                case CARD_TYPE_EQUIPMENT:
                    $equipmentCardIds[] = $id;
                    break;
                case CARD_TYPE_COMPANION:
                    $companionCardIds[] = $id;
                    break;
            }
        }
        $this->allCards->moveCards($oracleCardIds, CARD_LOCATION_ORACLE_DECK);
        $this->allCards->moveCards($injuryCardIds, CARD_LOCATION_INJURY_DECK);
        $this->allCards->moveCards($equipmentCardIds, CARD_LOCATION_EQUIPMENT_DECK);
        $this->allCards->moveCards($companionCardIds, CARD_LOCATION_COMPANION_DECK);

        // also set up "autoreshuffle" for oracle, injury and equipment cards
        $this->allCards->autoreshuffle_custom = [
            CARD_LOCATION_ORACLE_DECK => CARD_LOCATION_ORACLE_DISCARD,
            CARD_LOCATION_INJURY_DECK => CARD_LOCATION_INJURY_DISCARD,
            CARD_LOCATION_EQUIPMENT_DECK => CARD_LOCATION_EQUIPMENT_DISCARD
        ];
        $this->allCards->autoreshuffle = true;

        // Then shuffle each of the Oracle/Injury/Equipment decks (not Companions)
        foreach ([
            CARD_LOCATION_ORACLE_DECK,
            CARD_LOCATION_INJURY_DECK,
            CARD_LOCATION_EQUIPMENT_DECK
        ] as $location) {
            $this->allCards->shuffle($location);
        }
        // deal 6 equipment cards to the display.
        $this->allCards->pickCardsForLocation(6, CARD_LOCATION_EQUIPMENT_DECK, CARD_LOCATION_EQUIPMENT_DISPLAY);

        // Step 3 - "setup individual play area"
        // favor tokens - starting player gets 3, then everyone else gets 1 more in turn order
        // (done above, in player loop!)

        // NOTE: ship tiles and Zeus tiles are part of this step in the rules, but are dealt with below

        // roll all player dice
        $sql = "INSERT INTO player_dice (player_id, color, used) VALUES ";
        $diceValues = [];
        foreach($players as $player_id => $player) {
            for ($i = 0; $i < 3; $i++) {
                $dieRoll = bga_rand(0, 5);
                $color = $this->allColors[$dieRoll];
                $diceValues[] = "($player_id, '$color', 0)";
            }
        }
        $sql .= implode(", ", $diceValues);
        self::DbQuery($sql);

        // each player draws 1 injury card
        foreach($players as $player_id => $player) {
            $drawnInjury = $this->allCards->pickCard(CARD_LOCATION_INJURY_DECK, $player_id);
            // each player puts their Gods on the lowest row of the God track - except the God of the colour
            // of the Injury card drawn, which advances once.
            // Note that the God positions default to 0 when the player rows are created, so we
            // just have to advance that 1 God for each player.
            $cardColor = $this->allColors[$drawnInjury["type_arg"]];
            $godToAdvance = $this->godColors[$cardColor];
            // actual position to advance to depends on number of players!
            $updateSql = "UPDATE player SET $godToAdvance={$this->godFirstStep} WHERE player_id=$player_id";
            self::DbQuery($updateSql);
        }

        // shield gets set to 0 - done automatically because 0 is the default column value

        // the player's ship starts on the space with Zeus
        // TODO - needs map!

        // Add all other token types (all TODO):
        // - zeus tiles
        // of the 4 "variable" Zeus tiles, randomly choose 2 to be offerings and 2 to be monsters. Each
        // player gets the same. (Later introduce "first game" variant to only use 8 due to random discards)
        // - monster (appropriate locations on map)
        // - shrine (all start in player supply)
        // - statue (start in cities, so based on map)
        // - offering (randomly determined based on map)
        // - ship (ship tiles)
        //   Just deal randomly for now - later introduce option for draft, or "first game"
        //   option to randomly distribute 4 particular ones
        // - island (random but based on map)

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    private function setupMap() {
        // delegate work to separate class, as it's not needed again
        $mapGenerator = new delphi_mapgenerator($this->mapTiles, $this->cityTiles);
        // generate compact map for now, later add game option for "full random" generation
        $map = $mapGenerator->generateCompactWithCities();
        $mapSql = $mapGenerator->generateSql($map);
        self::DbQuery($mapSql);
        return $map;
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
    
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
  
        // TODO: Gather all information about current game situation (visible by player $current_player_id).
  
        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        // TODO: compute and return the game progression

        return 0;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */



//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in theoracleofdelphi.action.php)
    */

    /*
    
    Example:

    function playCard( $card_id )
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'playCard' ); 
        
        $player_id = self::getActivePlayerId();
        
        // Add your game logic to play a card there 
        ...
        
        // Notify all players about the card played
        self::notifyAllPlayers( "cardPlayed", clienttranslate( '${player_name} plays ${card_name}' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_name' => $card_name,
            'card_id' => $card_id
        ) );
          
    }
    
    */

    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    /*
    
    Example for game state "MyGameState":
    
    function argMyGameState()
    {
        // Get some values from the current game situation in database...
    
        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
        );
    }    
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    
    /*
    
    Example for game state "MyGameState":

    function stMyGameState()
    {
        // Do some stuff ...
        
        // (very often) go to another gamestate
        $this->gamestate->nextState( 'some_gamestate_transition' );
    }    
    */

    function stTurnEnd() {

    }

    function stConsultOracle() {

    }

    function stOracleChooseGod() {

    }

    function stFightMonster() {
        
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];
    	
        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );
            
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
}
