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
require_once('modules/delphi_maputils.php');

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

    define("TOKEN_STATUE", "statue");
    
    define("MAX_GOD_VALUE", 6);
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

        // ordered version of the above, needed for calculating die recoloring
        $this->colorsOrdered = [
            MAP_COLOR_RED,
            MAP_COLOR_BLACK,
            MAP_COLOR_PINK,
            MAP_COLOR_BLUE,
            MAP_COLOR_YELLOW,
            MAP_COLOR_GREEN
        ];

        // mapping of player colors (color hex code) with color strings ("red" etc)
        $this->colorMapping = [
            PLAYER_COLOR_RED => MAP_COLOR_RED,
            PLAYER_COLOR_YELLOW => MAP_COLOR_YELLOW,
            PLAYER_COLOR_GREEN => MAP_COLOR_GREEN,
            PLAYER_COLOR_BLUE => MAP_COLOR_BLUE
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

        $this->allGods = array_values($this->godColors);

        // statuses of island tiles, based on greek letters
        $this->greekLetterStatus = [
            GREEK_LETTER_SIGMA => 0,
            GREEK_LETTER_PHI => 1,
            GREEK_LETTER_PSI => 2,
            GREEK_LETTER_OMEGA => 3
        ];

        // set up cards
        $this->allCards = self::getNew("module.common.deck");
        $this->allCards->init("card");

        self::initGameStateLabels( array( 
            //    "my_first_global_variable" => 10,
            //    "my_second_global_variable" => 11,
            //      ...
            //    "my_first_game_variant" => 100,
            //    "my_second_game_variant" => 101,
            //      ...
        ) );        
	}

    private function setupCards() {
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
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
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
        //TODO: this won't store the instance var when reloading a game after setup, need to get
        //it from database!
        $this->map = $this->setupMap();

        // temples - randomly place the 6 colors of temples on each of the corresponding map spaces.
        // Note that templs are not in the "tokens" table as they never move after being placed - we
        // simply update the color field in the map_hex table
        $templeIds = self::getObjectListFromDb("SELECT id FROM map_hex WHERE type='" . MAP_TYPE_TEMPLE . "'", true);
        $colors = $this->allColors;
        shuffle($colors);
        foreach($templeIds as $id) {
            $color = array_shift($colors);
            $templeColorUpdateSql = "UPDATE map_hex SET color = '$color' WHERE id=$id";
            self::DbQuery($templeColorUpdateSql);
        }

        // Step 2 - "setup general supply"
        // mostly various decks of cards (Oracle/Injury/Companion/Equipment). We will store all these
        // in a single "deck" component - initialised in constructor.
        $this->setupCards();

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
        // the player's ship starts on the space with Zeus - also do here
        $zeusSpace = $this->map->findLocationsOfType(MAP_TYPE_ZEUS)[0];
        ["x" => $zeusX, "y" => $zeusY] = $zeusSpace;
        foreach($players as $player_id => $player) {
            $drawnInjury = $this->allCards->pickCard(CARD_LOCATION_INJURY_DECK, $player_id);
            // each player puts their Gods on the lowest row of the God track - except the God of the colour
            // of the Injury card drawn, which advances once.
            // Note that the God positions default to 0 when the player rows are created, so we
            // just have to advance that 1 God for each player.
            $cardColor = $this->allColors[$drawnInjury["type_arg"]];
            $godToAdvance = $this->godColors[$cardColor];
            // actual position to advance to depends on number of players!
            $updateSql = "UPDATE player
                          SET $godToAdvance={$this->godFirstStep},
                          ship_location_x=$zeusX,
                          ship_location_y=$zeusY
                          WHERE player_id=$player_id";

            self::DbQuery($updateSql);
        }

        // shield gets set to 0 - done automatically because 0 is the default column value

        // Add all token types:
        $tokensToAdd = [];

        // - zeus tiles
        // of the 4 "variable" Zeus tiles, randomly choose 2 to be offerings and 2 to be monsters. Each
        // player gets the same. (Later introduce "first game" variant to only use 8 due to random discards).
        // Status column is coded as follows: tile id (1 - 48), plus 100 if "second side" used.
        // Another 200 will be added if the tile has been completed by the player.
        // (later, for variant with fewer tiles, status 0 will be used for not in game)
        $variableIndices = [0, 0, 1, 1];
        shuffle($variableIndices);
        $zeusTileSides = array_merge(array_fill(0, 8, 0), $variableIndices);
        $idsByColor = [];
        foreach(self::loadPlayersBasicInfos() as $player_id => $player) {
            $idsByColor[$player["player_color"]] = $player_id;
            // also add starting favors to player table here. For some reason player_no isn't available
            // in the initial loop above where player info is inserted to the database!
            $favors = (int)$player["player_no"] + 2;
            self::DbQuery("UPDATE player SET favors=$favors WHERE player_id=$player_id");
        }
        foreach($this->zeusTiles as $id => ["player" => $playerColor, "tile" => $sides]) {
            // leave out any Zeus tiles belonging to player colors not in the game
            if (array_key_exists($playerColor, $idsByColor)) {
                $side = $zeusTileSides[($id - 1) % 12];
                $tileStatus = $id + ($side > 0 ? 100 : 0);
                $playerId = $idsByColor[$playerColor];
                $tokensToAdd[] = "('zeus', '$playerColor', null, null, $playerId, $tileStatus)";
            }
        }

        // - shrines (all start in player supply)
        foreach($players as $player_id => $player) {
            for($i = 0; $i < 3; $i++) {
                $tokensToAdd[] = "('shrine', null, null, null, $player_id, 0)";
            }
        }

        // - statue (start in cities, so based on map)
        $cityTiles = $this->map->findLocationsOfType(MAP_TYPE_CITY);
        foreach($cityTiles as $city) {
            ["x" => $x, "y" => $y, "color" => $color] = $city;
            for ($i = 0; $i < 3; $i++) {
                $tokensToAdd[] = "('" .  TOKEN_STATUE . "', '$color', $x, $y, null, 0)";
            }
        }

        // - offering (distribute evenly among the 6 offering spaces, from a pool of 1 per player
        // of each of the 6 colors, and without 2 of the same color on any one island)
        $offeringCubes = $this->getSetupOfferingCubes(count($players));
        $offeringSpaces = $this->map->findLocationsOfType(MAP_TYPE_OFFERING);
        foreach($offeringSpaces as $offeringSpace) {
            ["x" => $x, "y" => $y] = $offeringSpace;
            $cubeSet = array_shift($offeringCubes);
            foreach($cubeSet as $cubeColor ) {
                $tokensToAdd[] = "('" . MAP_TYPE_OFFERING . "', '$cubeColor', $x, $y, null, 0)";
            }
        }

        // - monsters: 2 on each "monster" type map tile, which must be of different colors on each tile.
        // Then distribute the rest evenly among the 6 "land" tiles, again with no repeat colors.
        // Again the pool is 6 (1 of each color) per player in the game.
        $bothMonsterSets = $this->getMonsterSets(count($players));
        $isFirstSet = true;
        foreach($bothMonsterSets as $monsterSet) {
            $locationType = $isFirstSet ? MAP_TYPE_MONSTER : MAP_TYPE_LAND;
            $monsterSpaces = $this->map->findLocationsOfType($locationType);
            foreach($monsterSpaces as $monsterSpace) {
                ["x" => $x, "y" => $y] = $monsterSpace;
                $currentSet = array_shift($monsterSet);
                foreach($currentSet as $monsterColor ) {
                    $tokensToAdd[] = "('" . MAP_TYPE_MONSTER . "', '$monsterColor', $x, $y, null, 0)";
                }
            }
            $isFirstSet = false;
        }

        // - ship (ship tiles). Status for tile ID (1 - 8)
        //TODO: alter for other variants for handing out. If using draft will add new statuses to
        //handle this
        $shipTileIds = array_keys($this->shipTiles);
        shuffle($shipTileIds);
        foreach($players as $player_id => $player) {
            $id = array_shift($shipTileIds);
            $tokensToAdd[] = "('ship', null, null, null, $player_id, $id)";
        }

        // - island tiles. Shuffle all 12 and put face-down on the 12 corresponding island spaces.
        // Note: status of tile is 0-3 when facedown and (later) 4-7 when faceup, with precise
        // value depending on greek letter (turning faceup always adds 4 to value).
        // Also the color/greek-letter combos come from the zeus tiles array.
        $islandTiles = [];
        foreach($this->islandTileLetters as $color => $letters) {
            foreach($letters as $letter) {
                $islandTiles[] = ["color" => $color, "status" => $this->greekLetterStatus[$letter]];
            }
        }
        shuffle($islandTiles);
        $islandSpaces = $this->map->findLocationsOfType(MAP_TYPE_ISLAND);
        foreach($islandSpaces as $islandSpace) {
            ["x" => $x, "y" => $y] = $islandSpace;
            $islandTile = array_shift($islandTiles);
            ["color" => $color, "status" => $status] = $islandTile;
            $sqlColor = $this->colorMapping[$color];
            $tokensToAdd[] = "('" . MAP_TYPE_ISLAND . "', '$sqlColor', $x, $y, null, '$status')";
        }

        // now insert all tokens into the database
        $tokenSql = "INSERT INTO token (type, color, location_x, location_y, player_id, status) VALUES ";
        $tokenSql .= implode(", ", $tokensToAdd);
        self::DbQuery($tokenSql);

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
        return new delphi_maputils($map);
    }

    // utility function which takes a "pool" of items of different colors, in the form
    // of an associative array (eg ["red" => 2, "yellow" => 3, etc]), and a number of sets to
    // divide into, and makes a set of equal-size groups that uses the entire pool and with no
    // repetition of colour in any one set.
    // NOTE: this assumes that $numSets divides exactly the total number in the pool, and that
    // this is always possible (eg. that there aren't more of any one color in the pool than there
    // are sets to fill) - these will always be the case when this is used
    private function makeNonRepeatingSets($pool, $numSets) {
        $needRestart = true;
        $emptySets = array_fill(0, $numSets, []);
        $total = 0;
        foreach ($pool as $color => $num) {
            $total += $num;
        }
        $target = $total / $numSets;

        while ($needRestart) {
            $needRestart = false;
            $sets = $emptySets;
            foreach ($pool as $color => $num) {
                for ($j = 0; $j < $num; $j++) {
                    // get indices of all cube sets which don't have that color so far,
                    // and which aren't yet full
                    $possibleIndices = [];
                    $k = 0;
                    foreach($sets as $set) {
                        if (!in_array($color, $set) && count($set) < $target) {
                            $possibleIndices[] = $k;
                        }
                        $k++;
                    }
                    // if none are possible, something has gone wrong and we need to start again
                    if (count($possibleIndices) === 0) {
                        $needRestart = true;
                        break;
                    }
                    // otherwise, assign one randomly and keep going
                    shuffle($possibleIndices);
                    $sets[$possibleIndices[0]][] = $color;
                }
                if ($needRestart) {
                    break;
                }
            }
            // if we get here without needing a restart, we're done
        }
        return $sets;
    }

    private function getSetupOfferingCubes($numPlayers) {
        // takes a pool of cubes, consisting of one of each color per player,
        // and divides them evenly into 6 sets such that there are no repeat colors
        // in each set
        $pool = array_fill_keys($this->allColors, $numPlayers);
        return $this->makeNonRepeatingSets($pool, 6);
    }

    private function getMonsterSets($numPlayers) {
        // places all monsters, according to the rules described in setupNewGame. Returns an array
        // of 2 arrays, one for the "monster tiles" the other for the "land" tiles.

        // First need to randomly generate a subset of 6 monsters.
        $allMonsters = array_fill_keys($this->allColors, $numPlayers);
        $monstersArray = [];
        foreach($allMonsters as $color => $num) {
            for($i = 0; $i < $num; $i++) {
                $monstersArray[] = $color;
            }
        }
        shuffle($monstersArray);
        $firstSixArray = array_slice($monstersArray, 0, 6);
        $chosenMonsters = [];
        foreach($firstSixArray as $monsterColor) {
            if (!array_key_exists($monsterColor, $chosenMonsters)) {
                $chosenMonsters[$monsterColor] = 0;
            }
            $chosenMonsters[$monsterColor] += 1;
        }
        $firstSet = $this->makeNonRepeatingSets($chosenMonsters, 3);

        $remainder = $allMonsters;
        foreach($chosenMonsters as $color => $alreadyPlaced) {
            $remainder[$color] -= $alreadyPlaced;
        }
        $secondSet = $this->makeNonRepeatingSets($remainder, 6);

        return [$firstSet, $secondSet];
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
    
        // Gather all information about current game situation (visible by player $current_player_id).

        // Static map information. Already done in .view.php but also need in the JS.
        $result["mapData"] = self::getObjectListFromDb(
            "SELECT x_coord, y_coord, type, color FROM map_hex"
        );

        // Tokens on the map.
        // Note that for island tiles we send the status as -1 for any facedown tile, to avoid leaking
        // hidden information (the greek letter on face-down tiles)
        $result["tokensOnMap"] = self::getObjectListFromDb(
            "SELECT id, location_x, location_y, type, color, player_id,
            CASE
                WHEN type = '" . MAP_TYPE_ISLAND . "' AND STATUS < 5 THEN -1
                ELSE status
            END AS status
            FROM token
            WHERE location_x IS NOT NULL"
        );

        // also send greek letter statuses for islands so it can be "decoded" easily on the frontend
        // - ensures it's consistent and doesn't have to be in both PHP and JS code!
        $result["greekLetters"] = array_flip($this->greekLetterStatus);

        // send info about cards, as follows:
        // for oracle cards, those held by players, the number in deck and discard, and the top discard
        // for injury cards, same as for oracle
        // for companion cards, ALL such cards along with their locations (player or deck)
        // for equipment cards, those held by players, the 6 on display, the number in deck and discard,
        // and the top discard
        $cardInfo = [
            CARD_TYPE_ORACLE => [],
            CARD_TYPE_INJURY => [],
            CARD_TYPE_COMPANION => [],
            CARD_TYPE_EQUIPMENT => []
        ];
        // counts in decks/discard
        $deckDiscardCounts = self::getCollectionFromDb(
            "SELECT card_location, COUNT(*) AS count
             FROM card
             WHERE card_location LIKE '%_deck' OR card_location LIKE '%_discard'
             GROUP BY card_location", true
        );
        $inHand = self::getObjectListFromDb(
            "SELECT card_id, card_type, card_location_arg, card_type_arg
             FROM card
             WHERE card_location = 'hand'"
        );
        $getCardIdentifier = function($cardType, $cardNum) {
            return in_array($cardType, [CARD_TYPE_INJURY, CARD_TYPE_ORACLE])
                ? $this->allColors[(int)$cardNum]
                : (int)$cardNum;
        };
        foreach([CARD_TYPE_ORACLE, CARD_TYPE_INJURY, CARD_TYPE_COMPANION, CARD_TYPE_EQUIPMENT] as $cardType) {
            $cardInfo[$cardType]["deck_size"] = array_key_exists("${cardType}_deck", $deckDiscardCounts)
                ? (int)$deckDiscardCounts["${cardType}_deck"]
                : 0;
            $cardInfo[$cardType]["discard_size"] = array_key_exists("${cardType}_discard", $deckDiscardCounts)
                ? (int)$deckDiscardCounts["${cardType}_discard"]
                : 0;
            $topDiscard = $this->allCards->getCardOnTop("${cardType}_discard");
            $cardInfo[$cardType]["top_discard"] = isset($topDiscard)
                ? $getCardIdentifier($cardType, $topDiscard["type_arg"])
                : null;
            $inHandOfType = array_filter($inHand, function($card) use ($cardType) {
                return $card["card_type"] === $cardType;
            });
            $cardInfo[$cardType]["hands"] = [];
            foreach ($inHandOfType as ["card_id" => $id, "card_location_arg" => $player, "card_type_arg" => $info]) {
                if (!array_key_exists($player, $cardInfo[$cardType]["hands"])) {
                    $cardInfo[$cardType]["hands"][$player] = [];
                }
                $cardInfo[$cardType]["hands"][$player][] = [
                    "card" => $getCardIdentifier($cardType, $info),
                    "id" => $id
                ];
            }
        }

        $cardInfo["equipment"]["display"] = array_map(function($card) use ($getCardIdentifier) {
            return $getCardIdentifier("equipment", (int)$card["type_arg"]);
        }, array_values($this->allCards->getCardsInLocation("equipment_display")));

        $result["cards"] = $cardInfo;

        // Get information about players
        $sql = "SELECT player_id id, player_score score, favors, shields, ship_location_x, ship_location_y, oracle_used, "
                . implode(", ", $this->allGods) . "
                FROM player";
        $playerInfo = self::getCollectionFromDb( $sql );

        // also fetch ship tile/shrines/zeus tiles (from tokens table)
        // We ensure location_x is null to avoid counting shrines which are placed on the map
        $playerTokens = self::getObjectListFromDb(
            "SELECT id, type, status, player_id FROM token
             WHERE type IN ('" . implode("', '", ["ship", "shrine", "zeus"]) ."')
             AND location_x IS NULL"
        );
        $tokenArray = [];
        foreach($playerTokens as ["id" => $id, "player_id" => $playerId, "type" => $type, "status" => $status]) {
            if (!array_key_exists($playerId, $tokenArray)) {
                $tokenArray[$playerId] = ["zeus" => [], "shrines" => 0];
            }
            //TODO: send id in gamedata for ships and shrines (if it turns out to be necessary/helpful for frontend!)
            //Will have to change data structure sent so not doing until I know it's needed.
            switch($type) {
                case "ship":
                    $tokenArray[$playerId]["shipTile"] = (int)$status;
                    break;
                case "shrine":
                    $tokenArray[$playerId]["shrines"] += 1;
                    break;
                case "zeus":
                    // "decode" the Status into info about the tile to show on the frontend.
                    // (Note: we don't bother getting the tile color, as the tiles are already in
                    // the array of player info, and the front end can workout the player color!)
                    $status = (int)$status;
                    $tileId = $status % 100;
                    $reverse = ($status % 200) > 100;
                    $complete = $status > 200;
                    $tileInfo = $this->zeusTiles[$tileId]["tile"][$reverse ? 1 : 0];
                    $tileType = substr($tileInfo["type"], 5); // the type string starts zeus_, which we remove
                    $tileDetails =$tileInfo[array_key_exists("color", $tileInfo) ? "color" : "letter"];
                    $tokenArray[$playerId]["zeus"][] = [
                        "originalId" => $tileId % 12 == 0 ? 12 : $tileId % 12,
                        "type" => $tileType,
                        "details" => $tileDetails,
                        "complete" => $complete,
                        "id" => $id
                    ];
                    break;
            }
        }

        // and dice (from player_dice table)
        $diceDb = self::getObjectListFromDb("SELECT id, player_id, color, used FROM player_dice");
        $dice = [];
        foreach($diceDb as ["id" => $id, "player_id" => $playerId, "color" => $color, "used" => $used]) {
            if (!array_key_exists($playerId, $dice)) {
                $dice[$playerId] = [];
            }
            $dice[$playerId][] = ["id" => $id, "color" => $color, "used" => $used != 0];
        }

        foreach($playerInfo as $playerId => &$info) {
            $info["dice"] = $dice[$playerId];
            ["zeus" => $zeus, "shrines" => $shrines, "shipTile" => $shipTile] = $tokenArray[$playerId];
            $info["zeus"] = $zeus;
            $info["shrines"] = $shrines;
            $info["shipTile"] = $shipTile;
        }

        $result["players"] = $playerInfo;

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

    // utility function to check it is legal for the player to use a given oracle die or card.
    // Returns the ID of the die/card if it is found.
    private function checkDie($playerId, $color, $isCard) {
        if ($isCard) {
            // check the player hasn't yet used an Oracle card this turn
            $checkUsedOracleSql = "SELECT COUNT(*) FROM player WHERE player_id = $playerId AND ORACLE_USED IS NOT NULL";
            $hasUsedOracle = self::getUniqueValueFromDb($checkUsedOracleSql);
            if ($hasUsedOracle != 0) {
                throw new BgaUserException("You have already used an Oracle card this turn!");
            }
            // make sure they actually have a card of that color
            $oraclesOfColorInHand = $this->allCards->getCardsOfTypeInLocation(
                CARD_TYPE_ORACLE, array_search($color, $this->allColors), "hand", $playerId
            );
            $oracleIds = array_keys($oraclesOfColorInHand);
            if (count($oracleIds) == 0) {
                throw new BgaUserException("You do not have an oracle card of that color!");
            }
            return $oracleIds[0];
        } else {
            // we only have to check that the player has an unused die of the specified color
            $checkDieSql = "SELECT id FROM player_dice
                            WHERE player_id = $playerId AND color = '$color' AND used=0
                            LIMIT 1";
            $dieIdArray = self::getObjectListFromDb($checkDieSql, true);
            if (count($dieIdArray) === 0) {
                throw new BgaUserException("You do not have an unused die of that color!");
            }
            return $dieIdArray[0];
        }
    }

    public function handleActions($actions) {
        self::checkAction("submitActions");

        $notifs = [];
        foreach ($actions as $action) {
            $methodName = "handleAction_" . $action["type"];
            $notifs[] = $this->{$methodName}($action);
        }

        $specialTransition = null;

        foreach ($notifs as $notif) {
            ["notif_name" => $notifName, "notif_string" => $notifString, "notif_args" => $notifArgs] = $notif;
            self::notifyAllPlayers($notifName, $notifString, $notifArgs);
            if (isset($notif["special_transition"])) {
                if ($specialTransition) {
                    throw new feException("This shouldn't happen - two special transitions called for in one submitted set of actions!");
                } else {
                    $specialTransition = $notif["special_transition"];
                }
            }
        }

        if ($specialTransition) {
            $this->gamestate->nextstate($specialTransition);
        }
    }

    // individual action handling methods
    private function handleAction_draw_oracle($action) {
        $dieColor = $action["die"];
        $isCard = $action["isCard"];
        // [note: die color necessary because needs to be removed from player's active dice,
        // even though it doesn't affect what happens]

        $activePlayerId = self::getActivePlayerId();

        // check move is legal
        $usedId = $this->checkDie($activePlayerId, $dieColor, $isCard);

        // update database:
        // draw new oracle card from deck into player's hand
        $drawn = $this->allCards->pickCard(CARD_LOCATION_ORACLE_DECK, $activePlayerId);

        if ($isCard) {
            // mark that an oracle card has been used this turn
            self::DbQuery("UPDATE player SET oracle_used={$usedId} WHERE player_id=$activePlayerId");
        } else {
            // mark die as used
            self::DbQuery("UPDATE player_dice SET used=1 WHERE id=$usedId");
        }

        $isCardText = $isCard ? "card" : "die";
        $oracleColor = $this->allColors[$drawn["type_arg"]];

        // return notification to send to client
        return [
            "notif_name" => "draw_oracle",
            "notif_string" => clienttranslate('${player_name} uses ${token_used} to draw an Oracle card, and gets ${card_gained}'),
            "notif_args" => [
                "player_id" => $activePlayerId,
                "player_name" => self::getActivePlayerName(),
                "die_color" => $dieColor,
                "used_id" => $usedId,
                "oracle_color" => $oracleColor,
                "card_id" => $drawn["id"],
                "is_card" => $isCard,
                "token_used" => "$dieColor $isCardText",
                "card_gained" => "$oracleColor card"
            ]
        ];
    }

    private function handleAction_recolor_die($action) {
        $original = $action["original"];
        $favorsSpent = $action["favorsSpent"];
        $isCard = $action["isCard"];
        
        // check move is legal

        // check card/die is legal, and get ID if so
        $activePlayerId = self::getActivePlayerId();

        $usedId = $this->checkDie($activePlayerId, $original, $isCard);

        // does player have the number of favors specified?
        $favorsHeld = (int) self::getUniqueValueFromDb("SELECT favors FROM player WHERE player_id=$activePlayerId");
        if ($favorsHeld < $favorsSpent) {
            throw new BgaUserException("You do not have enough favors to recolor by this many steps");
        }

        // update database

        $newColor = $this->colorsOrdered[
            (array_search($original, $this->colorsOrdered) + $favorsSpent) % count($this->allColors)
        ];

        // remove favors
        $favorsLeft = $favorsHeld - $favorsSpent;
        self::DbQuery("UPDATE player SET favors = $favorsLeft WHERE player_id=$activePlayerId");
        $newId = null;

        if ($isCard) {
            // if card, mark used, and add additional die of appropriate color
            self::DbQuery("UPDATE player SET oracle_used=$usedId WHERE player_id=$activePlayerId");
            // get the ID of the new entry and return it as part of the notification, so that the temp ID
            // on the client side can be correctly updated
            self::DbQuery("INSERT INTO player_dice (player_id, color, used) VALUES ($activePlayerId, '$newColor', 0)");
            $newId = self::DbGetLastId();
        } else {
            // if die, change color of die
            self::DbQuery("UPDATE player_dice SET color='$newColor' WHERE id=$usedId");
        }

        // return notification info
        $isCardText = $isCard ? "card" : "die";

        return [
            "notif_name" => "recolor_die",
            "notif_string" => clienttranslate('${player_name} spends ${favorsSpent} favor(s) to transform ${token_used} to ${new_die}'),
            "notif_args" => [
                "favorsSpent" => $favorsSpent,
                "isCard" => $isCard,
                "original" => $original,
                "playerId" => $activePlayerId,
                "player_name" => self::getActivePlayerName(),
                "token_used" => "$original $isCardText",
                "new_die" => "$newColor die",
                "new_id" => $newId,
            ]
        ];
    }
    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
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
