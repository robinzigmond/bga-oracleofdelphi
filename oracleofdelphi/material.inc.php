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

 // constants for colours and map location types
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
  * Finally, there is a "can_rotate" key for each TILE (not hex!) which identifies the 3 circular tiles
  * - ie those which, for the standard "compact" layout, can be freely rotated without changing the shape
  * of the map.
  */

$this->mapTiles = [
  [
    "can_rotate" => true,
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
    "can_rotate" => true,
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
    "can_rotate" => true,
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
    "can_rotate" => false,
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
    "can_rotate" => false,
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
    "can_rotate" => false,
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
    "can_rotate" => false,
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
    "can_rotate" => false,
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
    "can_rotate" => false,
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
    "can_rotate" => false,
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
    "can_rotate" => false,
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
    "can_rotate" => false,
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
    "can_rotate" => true,
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
    "can_rotate" => true,
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
    "can_rotate" => true,
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
    "can_rotate" => true,
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
    "can_rotate" => true,
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
    "can_rotate" => true,
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
