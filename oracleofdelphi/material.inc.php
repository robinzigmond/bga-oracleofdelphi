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
 * material.inc.php
 *
 * TheOracleOfDelphi game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */

  // constants used in this file, and elsewhere, for colours and map location types.
  // Also a few other consts which seemed appropriate.
  if (!defined("MAP_COLOR_PINK")) {
    define("MAP_COLOR_PINK", "pink");
    define("MAP_COLOR_BLUE", "blue");
    define("MAP_COLOR_YELLOW", "yellow");
    define("MAP_COLOR_GREEN", "green");
    define("MAP_COLOR_RED", "red");
    define("MAP_COLOR_BLACK", "black");

    define("MAP_TYPE_WATER", "water");
    define("MAP_TYPE_ZEUS", "zeus");
    define("MAP_TYPE_ISLAND", "island");
    define("MAP_TYPE_TEMPLE", "temple");
    define("MAP_TYPE_MONSTER", "monster");
    define("MAP_TYPE_LAND", "land");
    define("MAP_TYPE_OFFERING", "offering");
    define("MAP_TYPE_CITY", "city");
    define("MAP_TYPE_STATUE_RED_BLUE_PINK", "statue_red_blue_pink");
    define("MAP_TYPE_STATUE_GREEN_YELLOW_PINK", "statue_green_yellow_pink");
    define("MAP_TYPE_STATUE_BLACK_BLUE_YELLOW", "statue_black_blue_yellow");
    define("MAP_TYPE_STATUE_RED_PINK_BLACK", "statue_red_pink_black");
    define("MAP_TYPE_STATUE_GREEN_BLUE_BLACK", "statue_green_blue_black");
    define("MAP_TYPE_STATUE_RED_GREEN_YELLOW", "statue_red_green_yellow");

    define("COMPANION_TYPE_HERO", "hero");
    define("COMPANION_TYPE_DEMIGOD", "demigod");
    define("COMPANION_TYPE_CREATURE", "creature");
    define("COMPANION_HERO_TOOLTIP", clienttranslate("When acquiring a Hero, increase your Shield's strength by 2. From now on, you may discard any Injury Cards of the Hero's color."));
    define("COMPANION_DEMIGOD_TOOLTIP", clienttranslate("When acquiring a Demigod, draw 1 Oracle card. You may use Oracle Dice in the Demigod's color as if it was a color of your choice."));
    define("COMPANION_CREATURE_TOOLTIP", clienttranslate("When Moving your Ship with an Oracle Die of the Creature's color, your Ship's range is increased by 3. You may end your movement on a water space of any color."));

    define("ZEUS_TILE_SHRINE", "zeus_shrine");
    define("ZEUS_TILE_STATUE", "zeus_statue");
    define("ZEUS_TILE_OFFERING", "zeus_offering");
    define("ZEUS_TILE_MONSTER", "zeus_monster");

    define("GREEK_LETTER_SIGMA", "sigma");
    define("GREEK_LETTER_PHI", "phi");
    define("GREEK_LETTER_PSI", "psi");
    define("GREEK_LETTER_OMEGA", "omega");

    define("PLAYER_COLOR_RED", "ff0000");
    define("PLAYER_COLOR_YELLOW", "ffa500");
    define("PLAYER_COLOR_GREEN", "008000");
    define("PLAYER_COLOR_BLUE", "0000ff");
    define("TASK_COLOR_WILD", "wild");
  }

 /*
  * Static information about the 12 map tiles and 6 city tiles in the game. Used to construct the map
  * randomly, in either a pre-determined shape (choosing only which tile goes where and which rotation to use
  * for the 3 "circular" tiles), or in a completely "freeform" layout.
  * 
  * The co-ordinates here are purely local to each tile. An arbitrary point is chosen as (0,0) and other given
  * co-ordinates relative to it. The hex co-ordinates use a format where the 6 neighbours of (x, y), clockwise
  * from the top-right, are:
  * (x, y+1)
  * (x+1, y)
  * (x+1, y-1)
  * (x, y-1)
  * (x-1, y),
  * (x-1, y+1)
  *
  * That is, an increase in X moves to the right ("East"), while an increase in y moves to the "north-east".
  *
  * We also record "type" and "color" for each hex (color can be null), using the same enums as the map_hex
  * database table. This means that, after deciding a layout and transforming co-ordinates, the values
  * can be directly inserted into the database.
  *
  * Finally, there is a "rotation_amounts" key for each TILE (not hex!) which is only used for the
  * standard "compact" layout, and indicates the amounts by which each tile in a set can be rotated
  * to make that map. In addition there is an "all" key which, if set (true), says that each of the
  * rotation amounts must be used once in the set of tiles of that shape.
  */

$this->mapTiles = [
  [
    "rotation_amounts" => [0, 1, 2, 3, 4, 5, 6],
    "hexes" => [
      [
        "x_coord" => 0,
        "y_coord" => 0,
        "type" => MAP_TYPE_ZEUS,
        "color" => null
      ],
      [
        "x_coord" => -1,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_PINK
      ],
      [
        "x_coord" => 0,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_GREEN
      ],
      [
        "x_coord" => -1,
        "y_coord" => 0,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLUE
      ],
      [
        "x_coord" => 1,
        "y_coord" => 0,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLACK
      ],
      [
        "x_coord" => 0,
        "y_coord" => -1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_RED
      ],
      [
        "x_coord" => 1,
        "y_coord" => -1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_YELLOW
      ]
    ]
  ],
  [
    "rotation_amounts" => [0, 1, 2, 3, 4, 5, 6],
    "hexes" => [
      [
        "x_coord" => -1,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_YELLOW
      ],
      [
        "x_coord" => 0,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_GREEN
      ],
      [
        "x_coord" => -1,
        "y_coord" => 0,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_RED
      ],
      [
        "x_coord" => 1,
        "y_coord" => 0,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLACK
      ],
      [
        "x_coord" => 0,
        "y_coord" => -1,
        "type" => MAP_TYPE_OFFERING,
        "color" => null
      ],
      [
        "x_coord" => 1,
        "y_coord" => -1,
        "type" => MAP_TYPE_ISLAND,
        "color" => MAP_COLOR_GREEN
      ]
    ]
  ],
  [
    "rotation_amounts" => [0, 1, 2, 3, 4, 5, 6],
    "hexes" => [
      [
        "x_coord" => -1,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLUE
      ],
      [
        "x_coord" => 0,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_RED
      ],
      [
        "x_coord" => -1,
        "y_coord" => 0,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_YELLOW
      ],
      [
        "x_coord" => 1,
        "y_coord" => 0,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_PINK
      ],
      [
        "x_coord" => 0,
        "y_coord" => -1,
        "type" => MAP_TYPE_LAND,
        "color" => null
      ],
      [
        "x_coord" => 1,
        "y_coord" => -1,
        "type" => MAP_TYPE_ISLAND,
        "color" => MAP_COLOR_GREEN
      ]
    ]
  ],
  [
    "rotation_amounts" => [0, 2, 4],
    "all" => true,
    "hexes" => [
      [
        "x_coord" => 0,
        "y_coord" => 0,
        "type" => MAP_TYPE_STATUE_GREEN_BLUE_BLACK,
        "color" => null
      ],
      [
        "x_coord" => 1,
        "y_coord" => 0,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_GREEN
      ],
      [
        "x_coord" => 0,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLUE
      ],
      [
        "x_coord" => 1,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLACK
      ],
      [
        "x_coord" => -1,
        "y_coord" => 2,
        "type" => MAP_TYPE_LAND,
        "color" => null
      ],
      [
        "x_coord" => 0,
        "y_coord" => 2,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_RED
      ],
      [
        "x_coord" => -2,
        "y_coord" => 3,
        "type" => MAP_TYPE_ISLAND,
        "color" => MAP_COLOR_YELLOW
      ],
      [
        "x_coord" => -1,
        "y_coord" => 3,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_PINK
      ],
      [
        "x_coord" => 0,
        "y_coord" => 3,
        "type" => MAP_TYPE_OFFERING,
        "color" => null
      ],
      [
        "x_coord" => -2,
        "y_coord" => 4,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_GREEN
      ],
      [
        "x_coord" => -1,
        "y_coord" => 4,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLACK
      ]
    ]
  ],
  [
    "rotation_amounts" => [0, 2, 4],
    "all" => true,
    "hexes" => [
      [
        "x_coord" => 0,
        "y_coord" => 0,
        "type" => MAP_TYPE_LAND,
        "color" => null
      ],
      [
        "x_coord" => 1,
        "y_coord" => 0,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_PINK
      ],
      [
        "x_coord" => 0,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_GREEN
      ],
      [
        "x_coord" => 1,
        "y_coord" => 1,
        "type" => MAP_TYPE_ISLAND,
        "color" => MAP_COLOR_PINK
      ],
      [
        "x_coord" => -1,
        "y_coord" => 2,
        "type" => MAP_TYPE_TEMPLE,
        "color" => null
      ],
      [
        "x_coord" => 0,
        "y_coord" => 2,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_YELLOW
      ],
      [
        "x_coord" => -2,
        "y_coord" => 3,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_GREEN
      ],
      [
        "x_coord" => -1,
        "y_coord" => 3,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_RED
      ],
      [
        "x_coord" => 0,
        "y_coord" => 3,
        "type" => MAP_TYPE_STATUE_RED_PINK_BLACK,
        "color" => null
      ],
      [
        "x_coord" => -2,
        "y_coord" => 4,
        "type" => MAP_TYPE_OFFERING,
        "color" => null
      ],
      [
        "x_coord" => -1,
        "y_coord" => 4,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_PINK
      ]
    ]
  ],
  [
    "rotation_amounts" => [0, 2, 4],
    "all" => true,
    "hexes" => [
      [
        "x_coord" => 0,
        "y_coord" => 0,
        "type" => MAP_TYPE_ISLAND,
        "color" => MAP_COLOR_PINK
      ],
      [
        "x_coord" => 1,
        "y_coord" => 0,
        "type" => MAP_TYPE_TEMPLE,
        "color" => null
      ],
      [
        "x_coord" => 0,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLUE
      ],
      [
        "x_coord" => 1,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_RED
      ],
      [
        "x_coord" => -1,
        "y_coord" => 2,
        "type" => MAP_TYPE_LAND,
        "color" => null
      ],
      [
        "x_coord" => 0,
        "y_coord" => 2,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLACK
      ],
      [
        "x_coord" => -2,
        "y_coord" => 3,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_RED
      ],
      [
        "x_coord" => -1,
        "y_coord" => 3,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_YELLOW
      ],
      [
        "x_coord" => 0,
        "y_coord" => 3,
        "type" => MAP_TYPE_ISLAND,
        "color" => MAP_COLOR_RED
      ],
      [
        "x_coord" => -2,
        "y_coord" => 4,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLACK
      ],
      [
        "x_coord" => -1,
        "y_coord" => 4,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLUE
      ]
    ]
  ],
  [
    "rotation_amounts" => [1, 3, 5],
    "all" => true,
    "hexes" => [
      [
        "x_coord" => 0,
        "y_coord" => 0,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_PINK
      ],
      [
        "x_coord" => 1,
        "y_coord" => 0,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_RED
      ],
      [
        "x_coord" => 2,
        "y_coord" => 0,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_PINK
      ],
      [
        "x_coord" => -1,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_GREEN
      ],
      [
        "x_coord" => 0,
        "y_coord" => 1,
        "type" => MAP_TYPE_ISLAND,
        "color" => MAP_COLOR_YELLOW
      ],
      [
        "x_coord" => 1,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLUE
      ],
      [
        "x_coord" => -1,
        "y_coord" => 2,
        "type" => MAP_TYPE_STATUE_RED_GREEN_YELLOW,
        "color" => null
      ],
      [
        "x_coord" => 0,
        "y_coord" => 2,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_YELLOW
      ],
      [
        "x_coord" => 1,
        "y_coord" => 2,
        "type" => MAP_TYPE_OFFERING,
        "color" => null
      ]
    ]
  ],
  [
    "rotation_amounts" => [1, 3, 5],
    "all" => true,
    "hexes" => [
      [
        "x_coord" => 0,
        "y_coord" => 0,
        "type" => MAP_TYPE_OFFERING,
        "color" => null
      ],
      [
        "x_coord" => 1,
        "y_coord" => 0,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_GREEN
      ],
      [
        "x_coord" => 2,
        "y_coord" => 0,
        "type" => MAP_TYPE_STATUE_BLACK_BLUE_YELLOW,
        "color" => null
      ],
      [
        "x_coord" => -1,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_RED
      ],
      [
        "x_coord" => 0,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_PINK
      ],
      [
        "x_coord" => 1,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_YELLOW
      ],
      [
        "x_coord" => -1,
        "y_coord" => 2,
        "type" => MAP_TYPE_MONSTER,
        "color" => null
      ],
      [
        "x_coord" => 0,
        "y_coord" => 2,
        "type" => MAP_TYPE_ISLAND,
        "color" => MAP_COLOR_BLACK
      ],
      [
        "x_coord" => 1,
        "y_coord" => 2,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_PINK
      ]
    ]
  ],
  [
    "rotation_amounts" => [1, 3, 5],
    "all" => true,
    "hexes" => [
      [
        "x_coord" => 0,
        "y_coord" => 0,
        "type" => MAP_TYPE_LAND,
        "color" => null
      ],
      [
        "x_coord" => 1,
        "y_coord" => 0,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLUE
      ],
      [
        "x_coord" => 2,
        "y_coord" => 0,
        "type" => MAP_TYPE_TEMPLE,
        "color" => null
      ],
      [
        "x_coord" => -1,
        "y_coord" => 1,
        "type" => MAP_TYPE_OFFERING,
        "color" => null
      ],
      [
        "x_coord" => 0,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_GREEN
      ],
      [
        "x_coord" => 1,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLACK
      ],
      [
        "x_coord" => -1,
        "y_coord" => 2,
        "type" => MAP_TYPE_ISLAND,
        "color" => MAP_COLOR_RED
      ],
      [
        "x_coord" => 0,
        "y_coord" => 2,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_PINK
      ],
      [
        "x_coord" => 1,
        "y_coord" => 2,
        "type" => MAP_TYPE_STATUE_GREEN_YELLOW_PINK,
        "color" => null
      ]
    ]
  ],
  [
    "rotation_amounts" => [2, 3, 4],
    "all" => true,
    "hexes" => [
      [
        "x_coord" => 0,
        "y_coord" => 0,
        "type" => MAP_TYPE_MONSTER,
        "color" => null
      ],
      [
        "x_coord" => 1,
        "y_coord" => 0,
        "type" => MAP_TYPE_TEMPLE,
        "color" => null
      ],
      [
        "x_coord" => 0,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLUE
      ],
      [
        "x_coord" => 1,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_YELLOW
      ],
      [
        "x_coord" => 2,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLACK
      ],
      [
        "x_coord" => 0,
        "y_coord" => 2,
        "type" => MAP_TYPE_ISLAND,
        "color" => MAP_COLOR_BLUE
      ],
      [
        "x_coord" => 1,
        "y_coord" => 2,
        "type" => MAP_TYPE_STATUE_RED_BLUE_PINK,
        "color" => null
      ]
    ]
  ],
  [
    "rotation_amounts" => [2, 3, 4],
    "all" => true,
    "hexes" => [
      [
        "x_coord" => 0,
        "y_coord" => 0,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLACK
      ],
      [
        "x_coord" => 1,
        "y_coord" => 0,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLUE
      ],
      [
        "x_coord" => 0,
        "y_coord" => 1,
        "type" => MAP_TYPE_TEMPLE,
        "color" => null
      ],
      [
        "x_coord" => 1,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_YELLOW
      ],
      [
        "x_coord" => 2,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_RED
      ],
      [
        "x_coord" => 0,
        "y_coord" => 2,
        "type" => MAP_TYPE_ISLAND,
        "color" => MAP_COLOR_BLUE
      ],
      [
        "x_coord" => 1,
        "y_coord" => 2,
        "type" => MAP_TYPE_LAND,
        "color" => null
      ]
    ]
  ],
  [
    "rotation_amounts" => [2, 3, 4],
    "all" => true,
    "hexes" => [
      [
        "x_coord" => 0,
        "y_coord" => 0,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLUE
      ],
      [
        "x_coord" => 1,
        "y_coord" => 0,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLACK
      ],
      [
        "x_coord" => 0,
        "y_coord" => 1,
        "type" => MAP_TYPE_ISLAND,
        "color" => MAP_COLOR_BLACK
      ],
      [
        "x_coord" => 1,
        "y_coord" => 1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_GREEN
      ],
      [
        "x_coord" => 2,
        "y_coord" => 1,
        "type" => MAP_TYPE_TEMPLE,
        "color" => null
      ],
      [
        "x_coord" => 0,
        "y_coord" => 2,
        "type" => MAP_TYPE_MONSTER,
        "color" => null
      ],
      [
        "x_coord" => 1,
        "y_coord" => 2,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_YELLOW
      ]
    ]
  ]
];

$this->cityTiles = [
  [
    "rotation_amounts" => [0, 2, 4],
    "hexes" => [
      [
        "x_coord" => 0,
        "y_coord" => 0,
        "type" => MAP_TYPE_CITY,
        "color" => MAP_COLOR_YELLOW
      ],
      [
        "x_coord" => 0,
        "y_coord" => -1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_GREEN
      ],
      [
        "x_coord" => 1,
        "y_coord" => -1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_RED
      ]
    ]
  ],
  [
    "rotation_amounts" => [0, 2, 4],
    "hexes" => [
      [
        "x_coord" => 0,
        "y_coord" => 0,
        "type" => MAP_TYPE_CITY,
        "color" => MAP_COLOR_RED
      ],
      [
        "x_coord" => 0,
        "y_coord" => -1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_PINK
      ],
      [
        "x_coord" => 1,
        "y_coord" => -1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLACK
      ]
    ]
  ],
  [
    "rotation_amounts" => [0, 2, 4],
    "hexes" => [
      [
        "x_coord" => 0,
        "y_coord" => 0,
        "type" => MAP_TYPE_CITY,
        "color" => MAP_COLOR_BLACK
      ],
      [
        "x_coord" => 0,
        "y_coord" => -1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_PINK
      ],
      [
        "x_coord" => 1,
        "y_coord" => -1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_YELLOW
      ]
    ]
  ],
  [
    "rotation_amounts" => [0, 2, 4],
    "hexes" => [
      [
        "x_coord" => 0,
        "y_coord" => 0,
        "type" => MAP_TYPE_CITY,
        "color" => MAP_COLOR_GREEN
      ],
      [
        "x_coord" => 0,
        "y_coord" => -1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLUE
      ],
      [
        "x_coord" => 1,
        "y_coord" => -1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLACK
      ]
    ]
  ],
  [
    "rotation_amounts" => [0, 2, 4],
    "hexes" => [
      [
        "x_coord" => 0,
        "y_coord" => 0,
        "type" => MAP_TYPE_CITY,
        "color" => MAP_COLOR_PINK
      ],
      [
        "x_coord" => 0,
        "y_coord" => -1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_BLUE
      ],
      [
        "x_coord" => 1,
        "y_coord" => -1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_RED
      ]
    ]
  ],
  [
    "rotation_amounts" => [0, 2, 4],
    "hexes" => [
      [
        "x_coord" => 0,
        "y_coord" => 0,
        "type" => MAP_TYPE_CITY,
        "color" => MAP_COLOR_BLUE
      ],
      [
        "x_coord" => 0,
        "y_coord" => -1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_GREEN
      ],
      [
        "x_coord" => 1,
        "y_coord" => -1,
        "type" => MAP_TYPE_WATER,
        "color" => MAP_COLOR_YELLOW
      ]
    ]
  ]
];

// equipment cards (held as part of "cards" table)
$this->equipmentCards = [
  1 => [
    "tooltip" => clienttranslate("Your Ship's range is increased by 1.")
  ],
  2 => [
    "tooltip" => clienttranslate("Whenever you receive a reward for Making an Offering, Raising a Statue or Fighting a Monster, advance 1 God by 1 step.")
  ],
  3 => [
    "tooltip" => clienttranslate("You may Fight a Monster, Explore an Island and Build a Shrine from a distance of 1 water space from the respective Island Tiles.")
  ],
  4 => [
    "tooltip" => clienttranslate("You may use an Oracle Die of the depicted color as an action to take 1 Favor Token, draw 1 Oracle Card, and advance the God of the respective color by 1 step."),
    "color" => MAP_COLOR_BLUE
  ],
  5 => [
    "tooltip" => clienttranslate("You may use an Oracle Die of the depicted color as an action to take 1 Favor Token, draw 1 Oracle Card, and advance the God of the respective color by 1 step."),
    "color" => MAP_COLOR_GREEN
  ],
  6 => [
    "tooltip" => clienttranslate("You may use an Oracle Die of the depicted color as an action to take 1 Favor Token, draw 1 Oracle Card, and advance the God of the respective color by 1 step."),
    "color" => MAP_COLOR_PINK
  ],
  7 => [
    "tooltip" => clienttranslate("When checking your Injury Cards, you have to Recover due to 4 equally colored Injury Cards or 8 Injury Cards, in total instead of 3 and 6, respectively.")
  ],
  8 => [
    "tooltip" => clienttranslate("When you Consult the Oracle and at least 1 of the dice shows the depicted color, take 2 Favor Tokens."),
    "color" => MAP_COLOR_YELLOW
  ],
  9 => [
    "tooltip" => clienttranslate("When you Consult the Oracle and at least 1 of the dice shows the depicted color, take 2 Favor Tokens."),
    "color" => MAP_COLOR_BLACK
  ],
  10 => [
    "tooltip" => clienttranslate("When you Consult the Oracle and at least 1 of the dice shows the depicted color, take 2 Favor Tokens."),
    "color" => MAP_COLOR_RED
  ],
  11 => [
    "tooltip" => clienttranslate("One-time: Advance 1 of the depicted Gods to the topmost row of the God Track.")
  ],
  12 => [
    "tooltip" => clienttranslate("One-time: Look at 2 facedown Island Tiles and put 1 back. Uncover the other and take the corresponding reward. If there are less than 2 face down Island Tiles, this card cannot be used.")
  ],
  13 => [
    "tooltip" => clienttranslate("Your Ship may cross shallows. A shallow does not count as a space!")
  ],
  14 => [
    "tooltip" => clienttranslate("Your storage capcity is increased by 1. One-time: Increase your Shield's strength by 1.")
  ],
  15 => [
    "tooltip" => clienttranslate("You may Load a Statue and Raise a Statue from a distance of 1 water space from the respective City Tile or Island Tile.")
  ],
  16 => [
    "tooltip" => clienttranslate("You may Load an Offering and Make an Offering from a distance of 1 water space from the respective Island Tile.")
  ],
  17 => [
    "tooltip" => clienttranslate("Once per turn, you may spend 3 Favor Tokens to perform an additional action of any color.")
  ],
  18 => [
    "tooltip" => clienttranslate("One-time: Take 1 of the depicted Statues from the corresponding City Tile and store it in your Ship."),
    "colors" => [MAP_COLOR_PINK, MAP_COLOR_BLUE, MAP_COLOR_BLACK]
  ],
  19 => [
    "tooltip" => clienttranslate("One-time: Take 1 of the depicted Statues from the corresponding City Tile and store it in your Ship."),
    "colors" => [MAP_COLOR_RED, MAP_COLOR_GREEN, MAP_COLOR_YELLOW]
  ],
  20 => [
    "tooltip" => clienttranslate("One-time: Take 1 of the depicted Offerings from any Island Tile and store it in your Ship."),
    "colors" => [MAP_COLOR_PINK, MAP_COLOR_BLUE, MAP_COLOR_BLACK]
  ],
  21 => [
    "tooltip" => clienttranslate("One-time: Take 1 of the depicted Offerings from any Island Tile and store it in your Ship."),
    "colors" => [MAP_COLOR_RED, MAP_COLOR_GREEN, MAP_COLOR_YELLOW]
  ],
  22 => [
    "tooltip" => clienttranslate("One-time: Take 3 Favor Tokens, draw 1 Oracle Card, and advance 1 or 2 Gods by a total of 2 steps combined.")
  ]
];

// companion cards (also held in "cards" table
$this->companionCards = [
  1 => [
    "name" => "Aias",
    "color" => MAP_COLOR_PINK,
    "type" => COMPANION_TYPE_HERO,
    "tooltip" => COMPANION_HERO_TOOLTIP
  ],
  2 => [
    "name" => "Helena",
    "color" => MAP_COLOR_PINK,
    "type" => COMPANION_TYPE_DEMIGOD,
    "tooltip" => COMPANION_DEMIGOD_TOOLTIP
  ],
  3 => [
    "name" => "Pan",
    "color" => MAP_COLOR_PINK,
    "type" => COMPANION_TYPE_CREATURE,
    "tooltip" => COMPANION_CREATURE_TOOLTIP
  ],
  4 => [
    "name" => "Bellerophon",
    "color" => MAP_COLOR_YELLOW,
    "type" => COMPANION_TYPE_HERO,
    "tooltip" => COMPANION_HERO_TOOLTIP
  ],
  5 => [
    "name" => "Minos",
    "color" => MAP_COLOR_YELLOW,
    "type" => COMPANION_TYPE_DEMIGOD,
    "tooltip" => COMPANION_DEMIGOD_TOOLTIP
  ],
  6 => [
    "name" => "Gryphos",
    "color" => MAP_COLOR_YELLOW,
    "type" => COMPANION_TYPE_CREATURE,
    "tooltip" => COMPANION_CREATURE_TOOLTIP
  ],
  7 => [
    "name" => "Theseus",
    "color" => MAP_COLOR_BLACK,
    "type" => COMPANION_TYPE_HERO,
    "tooltip" => COMPANION_HERO_TOOLTIP
  ],
  8 => [
    "name" => "Kirke",
    "color" => MAP_COLOR_BLACK,
    "type" => COMPANION_TYPE_DEMIGOD,
    "tooltip" => COMPANION_DEMIGOD_TOOLTIP
  ],
  9 => [
    "name" => "Cheiron",
    "color" => MAP_COLOR_BLACK,
    "type" => COMPANION_TYPE_CREATURE,
    "tooltip" => COMPANION_CREATURE_TOOLTIP
  ],
  10 => [
    "name" => "Hektor",
    "color" => MAP_COLOR_GREEN,
    "type" => COMPANION_TYPE_HERO,
    "tooltip" => COMPANION_HERO_TOOLTIP
  ],
  11 => [
    "name" => "Perseus",
    "color" => MAP_COLOR_GREEN,
    "type" => COMPANION_TYPE_DEMIGOD,
    "tooltip" => COMPANION_DEMIGOD_TOOLTIP
  ],
  12 => [
    "name" => "Pegasus",
    "color" => MAP_COLOR_GREEN,
    "type" => COMPANION_TYPE_CREATURE,
    "tooltip" => COMPANION_CREATURE_TOOLTIP
  ],
  13 => [
    "name" => "Achilles",
    "color" => MAP_COLOR_BLUE,
    "type" => COMPANION_TYPE_HERO,
    "tooltip" => COMPANION_HERO_TOOLTIP
  ],
  14 => [
    "name" => "Herakles",
    "color" => MAP_COLOR_BLUE,
    "type" => COMPANION_TYPE_DEMIGOD,
    "tooltip" => COMPANION_DEMIGOD_TOOLTIP
  ],
  15 => [
    "name" => "Nereide",
    "color" => MAP_COLOR_BLUE,
    "type" => COMPANION_TYPE_CREATURE,
    "tooltip" => COMPANION_CREATURE_TOOLTIP
  ],
  16 => [
    "name" => "Odysseus",
    "color" => MAP_COLOR_RED,
    "type" => COMPANION_TYPE_HERO,
    "tooltip" => COMPANION_HERO_TOOLTIP
  ],
  17 => [
    "name" => "Penthesilea",
    "color" => MAP_COLOR_RED,
    "type" => COMPANION_TYPE_DEMIGOD,
    "tooltip" => COMPANION_DEMIGOD_TOOLTIP
  ],
  18 => [
    "name" => "Phoenix",
    "color" => MAP_COLOR_RED,
    "type" => COMPANION_TYPE_CREATURE,
    "tooltip" => COMPANION_CREATURE_TOOLTIP
  ]
];

// ship tiles
$this->shipTiles = [
  1 => [
    "tooltip" => clienttranslate("At the start of the game, move your Shield 2 steps to the right."),
    "storage" => 2,
    "firstGame" => true
  ],
  2 => [
    "tooltip" => clienttranslate("Advance all your Gods on the God Track to the row showing the number of players participating in the game. After using a Special Action of a God, return it to that row instead of the lowest row."),
    "storage" => 2,
    "firstGame" => true
  ],
  3 => [
    "tooltip" => clienttranslate("Return a Zeus Tile of your choice to the box. You do not receive its reward. You require 11 completed tasks to win the game instead of 12."),
    "storage" => 2,
    "firstGame" => false
  ],
  4 => [
    "tooltip" => clienttranslate("Your cost for \"recoloring\" Oracle Dice is reduced by 1."),
    "storage" => 2,
    "firstGame" => false
  ],
  5 => [
    "tooltip" => clienttranslate("Your Ship's range is increased by 2."),
    "storage" => 2,
    "firstGame" => true
  ],
  6 => [
    "tooltip" => clienttranslate("Whenever you take 1 or more Favor Tokens, take 1 more. This also applies to the starting Favor Tokens."),
    "storage" => 2,
    "firstGame" => false
  ],
  7 => [
    "tooltip" => clienttranslate("At the start of the game, take 1 Equipment Card from the display and draw 1 Oracle Card."),
    "storage" => 2,
    "firstGame" => false
  ],
  8 => [
    "tooltip" => "You can also \"recolor\" Oracle Dice in counterclockwise direction. Additionally, your storage capacity is increased by 2",
    "storage" => 4,
    "firstGame" => true
  ]
];

$this->islandTileLetters = [
  PLAYER_COLOR_RED => [
    GREEK_LETTER_PHI,
    GREEK_LETTER_PSI,
    GREEK_LETTER_OMEGA
  ],
  PLAYER_COLOR_YELLOW => [
    GREEK_LETTER_SIGMA,
    GREEK_LETTER_PSI,
    GREEK_LETTER_OMEGA
  ],
  PLAYER_COLOR_GREEN => [
    GREEK_LETTER_SIGMA,
    GREEK_LETTER_PHI,
    GREEK_LETTER_PSI
  ],
  PLAYER_COLOR_BLUE => [
    GREEK_LETTER_SIGMA,
    GREEK_LETTER_PHI,
    GREEK_LETTER_OMEGA
  ]
];

$this->zeusTiles = [
  1 => [
    "player" => PLAYER_COLOR_RED,
    "tile" => [
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_RED][0]
      ],
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_RED][0]
      ]
    ]
  ],
  2 => [
    "player" => PLAYER_COLOR_RED,
    "tile" => [
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_RED][1]
      ],
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_RED][1]
      ]
    ]
  ],
  3 => [
    "player" => PLAYER_COLOR_RED,
    "tile" => [
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_RED][2]
      ],
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_RED][2]
      ]
    ]
  ],
  4 => [
    "player" => PLAYER_COLOR_RED,
    "tile" => [
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ],
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ]
    ]
  ],
  5 => [
    "player" => PLAYER_COLOR_RED,
    "tile" => [
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ],
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ]
    ]
  ],
  6 => [
    "player" => PLAYER_COLOR_RED,
    "tile" => [
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ],
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ]
    ]
  ],
  7 => [
    "player" => PLAYER_COLOR_RED,
    "tile" => [
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => TASK_COLOR_WILD
      ],
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => TASK_COLOR_WILD
      ]
    ]
  ],
  8 => [
    "player" => PLAYER_COLOR_RED,
    "tile" => [
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => TASK_COLOR_WILD
      ],
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => TASK_COLOR_WILD
      ]
    ]
  ],
  9 => [
    "player" => PLAYER_COLOR_RED,
    "tile" => [
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => MAP_COLOR_YELLOW
      ],
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => MAP_COLOR_YELLOW
      ]
    ]
  ],
  10 => [
    "player" => PLAYER_COLOR_RED,
    "tile" => [
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => MAP_COLOR_BLUE
      ],
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => MAP_COLOR_BLUE
      ]
    ]
  ],
  11 => [
    "player" => PLAYER_COLOR_RED,
    "tile" => [
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => MAP_COLOR_PINK
      ],
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => MAP_COLOR_RED
      ]
    ]
  ],
  12 => [
    "player" => PLAYER_COLOR_RED,
    "tile" => [
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => MAP_COLOR_GREEN
      ],
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => MAP_COLOR_BLACK
      ]
    ]
  ],
  13 => [
    "player" => PLAYER_COLOR_YELLOW,
    "tile" => [
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_YELLOW][0]
      ],
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_YELLOW][0]
      ]
    ]
  ],
  14 => [
    "player" => PLAYER_COLOR_YELLOW,
    "tile" => [
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_YELLOW][1]
      ],
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_YELLOW][1]
      ]
    ]
  ],
  15 => [
    "player" => PLAYER_COLOR_YELLOW,
    "tile" => [
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_YELLOW][2]
      ],
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_YELLOW][2]
      ]
    ]
  ],
  16 => [
    "player" => PLAYER_COLOR_YELLOW,
    "tile" => [
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ],
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ]
    ]
  ],
  17 => [
    "player" => PLAYER_COLOR_YELLOW,
    "tile" => [
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ],
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ]
    ]
  ],
  18 => [
    "player" => PLAYER_COLOR_YELLOW,
    "tile" => [
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ],
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ]
    ]
  ],
  19 => [
    "player" => PLAYER_COLOR_YELLOW,
    "tile" => [
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => TASK_COLOR_WILD
      ],
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => TASK_COLOR_WILD
      ]
    ]
  ],
  20 => [
    "player" => PLAYER_COLOR_YELLOW,
    "tile" => [
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => TASK_COLOR_WILD
      ],
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => TASK_COLOR_WILD
      ]
    ]
  ],
  21 => [
    "player" => PLAYER_COLOR_YELLOW,
    "tile" => [
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => MAP_COLOR_YELLOW
      ],
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => MAP_COLOR_YELLOW
      ]
    ]
  ],
  22 => [
    "player" => PLAYER_COLOR_YELLOW,
    "tile" => [
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => MAP_COLOR_BLUE
      ],
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => MAP_COLOR_BLUE
      ]
    ]
  ],
  23 => [
    "player" => PLAYER_COLOR_YELLOW,
    "tile" => [
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => MAP_COLOR_PINK
      ],
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => MAP_COLOR_RED
      ]
    ]
  ],
  24 => [
    "player" => PLAYER_COLOR_YELLOW,
    "tile" => [
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => MAP_COLOR_GREEN
      ],
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => MAP_COLOR_BLACK
      ]
    ]
  ],
  25 => [
    "player" => PLAYER_COLOR_GREEN,
    "tile" => [
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_GREEN][0]
      ],
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_GREEN][0]
      ]
    ]
  ],
  26 => [
    "player" => PLAYER_COLOR_GREEN,
    "tile" => [
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_GREEN][1]
      ],
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_GREEN][1]
      ]
    ]
  ],
  27 => [
    "player" => PLAYER_COLOR_GREEN,
    "tile" => [
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_GREEN][2]
      ],
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_GREEN][2]
      ]
    ]
  ],
  28 => [
    "player" => PLAYER_COLOR_GREEN,
    "tile" => [
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ],
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ]
    ]
  ],
  29 => [
    "player" => PLAYER_COLOR_GREEN,
    "tile" => [
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ],
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ]
    ]
  ],
  30 => [
    "player" => PLAYER_COLOR_GREEN,
    "tile" => [
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ],
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ]
    ]
  ],
  31 => [
    "player" => PLAYER_COLOR_GREEN,
    "tile" => [
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => TASK_COLOR_WILD
      ],
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => TASK_COLOR_WILD
      ]
    ]
  ],
  32 => [
    "player" => PLAYER_COLOR_GREEN,
    "tile" => [
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => TASK_COLOR_WILD
      ],
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => TASK_COLOR_WILD
      ]
    ]
  ],
  33 => [
    "player" => PLAYER_COLOR_GREEN,
    "tile" => [
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => MAP_COLOR_YELLOW
      ],
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => MAP_COLOR_YELLOW
      ]
    ]
  ],
  34 => [
    "player" => PLAYER_COLOR_GREEN,
    "tile" => [
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => MAP_COLOR_BLUE
      ],
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => MAP_COLOR_BLUE
      ]
    ]
  ],
  35 => [
    "player" => PLAYER_COLOR_GREEN,
    "tile" => [
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => MAP_COLOR_PINK
      ],
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => MAP_COLOR_RED
      ]
    ]
  ],
  36 => [
    "player" => PLAYER_COLOR_GREEN,
    "tile" => [
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => MAP_COLOR_GREEN
      ],
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => MAP_COLOR_BLACK
      ]
    ]
  ],
  37 => [
    "player" => PLAYER_COLOR_BLUE,
    "tile" => [
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_BLUE][0]
      ],
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_BLUE][0]
      ]
    ]
  ],
  38 => [
    "player" => PLAYER_COLOR_BLUE,
    "tile" => [
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_BLUE][1]
      ],
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_BLUE][1]
      ]
    ]
  ],
  39 => [
    "player" => PLAYER_COLOR_BLUE,
    "tile" => [
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_BLUE][2]
      ],
      [
        "type" => ZEUS_TILE_SHRINE,
        "letter" => $this->islandTileLetters[PLAYER_COLOR_BLUE][2]
      ]
    ]
  ],
  40 => [
    "player" => PLAYER_COLOR_BLUE,
    "tile" => [
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ],
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ]
    ]
  ],
  41 => [
    "player" => PLAYER_COLOR_BLUE,
    "tile" => [
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ],
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ]
    ]
  ],
  42 => [
    "player" => PLAYER_COLOR_BLUE,
    "tile" => [
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ],
      [
        "type" => ZEUS_TILE_STATUE,
        "color" => TASK_COLOR_WILD
      ]
    ]
  ],
  43 => [
    "player" => PLAYER_COLOR_BLUE,
    "tile" => [
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => TASK_COLOR_WILD
      ],
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => TASK_COLOR_WILD
      ]
    ]
  ],
  44 => [
    "player" => PLAYER_COLOR_BLUE,
    "tile" => [
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => TASK_COLOR_WILD
      ],
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => TASK_COLOR_WILD
      ]
    ]
  ],
  45 => [
    "player" => PLAYER_COLOR_BLUE,
    "tile" => [
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => MAP_COLOR_YELLOW
      ],
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => MAP_COLOR_YELLOW
      ]
    ]
  ],
  46 => [
    "player" => PLAYER_COLOR_BLUE,
    "tile" => [
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => MAP_COLOR_BLUE
      ],
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => MAP_COLOR_BLUE
      ]
    ]
  ],
  47 => [
    "player" => PLAYER_COLOR_BLUE,
    "tile" => [
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => MAP_COLOR_PINK
      ],
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => MAP_COLOR_RED
      ]
    ]
  ],
  48 => [
    "player" => PLAYER_COLOR_BLUE,
    "tile" => [
      [
        "type" => ZEUS_TILE_OFFERING,
        "color" => MAP_COLOR_GREEN
      ],
      [
        "type" => ZEUS_TILE_MONSTER,
        "color" => MAP_COLOR_BLACK
      ]
    ]
  ]
];
