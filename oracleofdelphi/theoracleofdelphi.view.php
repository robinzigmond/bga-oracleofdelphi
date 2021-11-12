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
 * theoracleofdelphi.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in theoracleofdelphi_theoracleofdelphi.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
  require_once( APP_BASE_PATH."view/common/game.view.php" );
  
  class view_theoracleofdelphi_theoracleofdelphi extends game_view
  {
    function getGameName() {
        return "theoracleofdelphi";
    }    
  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        // translated strings for main interface
        $this->tpl["ORACLE_CARDS"] = self::_("Oracle cards");
        $this->tpl["INJURY_CARDS"] = self::_("Injury cards");
        $this->tpl["EQUIPMENT_CARDS"] = self::_("Equipment cards");
        $this->tpl["DECK"] = self::_("Deck");
        $this->tpl["DISCARD"] = self::_("Discard");
        $this->tpl["DISPLAY"] = self::_("Display");

        $tiles = $this->game->getObjectListFromDb(
          "SELECT x_coord, y_coord, type, color, orientation FROM map_hex"
        );
        $MAP_WIDTH_IN_TILES = 14; //TODO: make dynamic when random maps used
        $MAP_HEIGHT_IN_TILES = 14; //TODO: as above

        $this->page->begin_block("theoracleofdelphi_theoracleofdelphi", "maptile");

        foreach($tiles as $tile) {
          [
            "x_coord" => $x,
            "y_coord" => $y,
            "color" => $color,
            "type" => $type,
            "orientation" => $orientation
          ] = $tile;

          $this->page->insert_block("maptile", [
            "X" => $x,
            "Y" => $y,
            "TYPE" => $type,
            "COLOR_CLASS" => isset($color) ? " ood_maphex_color_$color" : "",
            "ROTATION_CLASS" => isset($orientation) ? " ood_maphex_cityrotation_$orientation" : "",
            //TODO: (0,0) being 45% of the way from the left (approx!) is just a feature of the "compact" map
            "LEFTPERCENT" => 45 + 100 * ($x + $y / 2) / $MAP_WIDTH_IN_TILES,
            "BOTTOMPERCENT" => 50 + 86 * $y / $MAP_HEIGHT_IN_TILES
          ]);
        }

        /*********** Do not change anything below this line  ************/
  	}
  }
  

