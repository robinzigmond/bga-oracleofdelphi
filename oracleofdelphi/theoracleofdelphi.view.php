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

        $tiles = $this->game->getObjectListFromDb(
          "SELECT x_coord, y_coord, type, color, orientation FROM map_hex"
        );
        $TILE_WIDTH = 75; //TODO: will later have to be made dynamic. Unfortunately tied to value of CSS variable.
        $TILE_HEIGHT = $TILE_WIDTH * 171 / 149;
        $MAP_HEIGHT = $TILE_WIDTH * 14;

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
            //TODO: determine these dynamically based on map, at the moment just set up to look OK
            //with standard "compact" map layout
            "LEFT" => ($MAP_HEIGHT * 0.4) + ($x + $y / 2) * $TILE_WIDTH,
            // multiplier of 0.75 to make sure the hexes correctly interlock vertically
            "BOTTOM" => ($MAP_HEIGHT / 2) + $y * $TILE_HEIGHT * 0.75
          ]);
        }

        /*********** Do not change anything below this line  ************/
  	}
  }
  

