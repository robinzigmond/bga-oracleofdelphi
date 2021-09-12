<?php

require_once('delphi_maputils.php');

class delphi_mapgenerator {
    function __construct($mapTiles, $cityTiles) {
        $this->mapTiles = $mapTiles;
        $this->cityTiles = $cityTiles;

        // config for "compact" map - this array maps the size of a tile (number of hexes)
        // to the translation offsets that are needed for each rotation number.
        // For the "circular" tiles it is different - we use the key "circle", and don't key
        // by rotation amount as ANY combination of rotations is allowed, we simply need all 3
        // of the translation offsets used.
        // Note that we arbitrarily pick (0, 0) to be the central shallow of the "topmost" of
        // the 3 "circular" tiles
        $this->compactConfig = [
            "circle" => [
                [
                    "x" => 0,
                    "y" => 0
                ],
                [
                    "x" => 3,
                    "y" => -1
                ],
                [
                    "x" => 2,
                    "y" => -3
                ]
            ],
            11 => [
                0 => [
                    "x" => 0,
                    "y" => -5
                ],
                2 => [
                    "x" => -2,
                    "y" => 4
                ],
                4 => [
                    "x" => 7,
                    "y" => -3
                ]
            ],
            9 => [
                1 => [
                    "x" => 3,
                    "y" => 1
                ],
                3 => [
                    "x" => 4,
                    "y" => -5
                ],
                5 => [
                    "x" => -2,
                    "y" => 0
                ]
            ],
            7 => [
                2 => [
                    "x" => -3,
                    "y" => -2
                ],
                3 => [
                    "x" => 2,
                    "y" => -6
                ],
                4 => [
                    "x" => 1,
                    "y" => 4
                ]
            ]
        ];
    }

    // "locally" rotates a set of tiles one clockwise 60 degree rotation about 0,0.
    // Leaves everything untouched apart from the x and y co-ordinates.
    // Recall that an "x" increase moves "East" while a "y" increase moves "North East".
    private function rotateOnce($hexes) {
        return array_map(function($hexInfo) {
            [ "x_coord" => $x, "y_coord" => $y, "type" =>$type, "color" => $color ] = $hexInfo;
            return [
                "x_coord" => $x + $y,
                "y_coord" => -$x,
                "type" => $type,
                "color" => $color
            ];
        }, $hexes);
    }

    // the same, but rotates $numRotations steps, where $numRotations is between 0 and 5 (inclusive)
    private function rotate($hexes, $numRotations) {
        $result = $hexes;

        for ($i = 0; $i < $numRotations; $i++) {
            $result = $this->rotateOnce($result);
        }

        return $result;
    }

    // translates a set of hex by given amounts in both "x" and "y" directions 
    private function translate($hexes, $xOffset, $yOffset) {
        return array_map(function($hexInfo) use ($xOffset, $yOffset) {
            [ "x_coord" => $x, "y_coord" => $y, "type" =>$type, "color" => $color ] = $hexInfo;
            return [
                "x_coord" => $x + $xOffset,
                "y_coord" => $y + $yOffset,
                "type" => $type,
                "color" => $color
            ];
        }, $hexes);
        }

    // builds a randomised "compact layout" - as defined in the rulebook for a first game.
    // That is, it randomises the arrangement of the various tile types in a fixed layout, as well
    // as the rotation amount where possible.
    // This function returns the final layout in the form of an array of associative arrays (like the
    // $hexes array taken by rotate as an argument, but a much bigger one consisting of every map tile)
    // NOTE: this does NOT check that the final arrangement is legal, in the sense of all water tiles
    // forming a connected area. That is checked in other methods!
    private function generateCompactRandom() {
        $result = [];

        $tileSets = array_chunk($this->mapTiles, 3);
        foreach ($tileSets as $tileSet) {
            // determine how to rotate and translate the tiles
            $representative = $tileSet[0]; // to figure out info which is common to each tile in the set

            if (array_key_exists("all", $representative) && $representative["all"]) {
                $config = $this->compactConfig[count($representative["hexes"])];
                $rotations = $representative["rotation_amounts"];
                shuffle($rotations);
                foreach($tileSet as $tile) {
                    $rotation = array_shift($rotations);
                    $translation = $config[$rotation];
                    $rotatedTile = $this->rotate($tile["hexes"], $rotation);
                    $rotatedAndTranslated = $this->translate($rotatedTile, $translation["x"], $translation["y"]);
                    $result = array_merge($result, $rotatedAndTranslated);
                }
            } else {
                $config = $this->compactConfig["circle"];
                shuffle($config);
                $rotationOptions = $representative["rotation_amounts"];
                $numOptions = count($rotationOptions);
                foreach($tileSet as $tile) {
                    // could have just been $rotation = bga_rand(0, 5), but it's nice to not
                    // rely on the fact that the array of options has consecutive integers!
                    $rotation = $rotationOptions[bga_rand(0, $numOptions - 1)];
                    $translation = array_shift($config);
                    $rotatedTile = $this->rotate($tile["hexes"], $rotation);
                    $rotatedAndTranslated = $this->translate($rotatedTile, $translation["x"], $translation["y"]);
                    $result = array_merge($result, $rotatedAndTranslated);
                }
            }
        }

        return $result;
    }

    // check all water areas are connected, using maputils class
    private function isLegal($tiles) {
        $mapUtils = new delphi_maputils($tiles);
        return $mapUtils->isWaterConnected();
    }

    public function generateCompact() {
        $ok = false;

        //need to debug isLegal and everything that goes into it - it gave me a map with disconnected
        //water hexes, even though I saw it DOES return false for at least some maps!
        while (!$ok) {
            $map = $this->generateCompactRandom();
            $ok = $this->isLegal($map);
        }
        // save as instance variable
        $this->map = new delphi_maputils($map);
        return $map;
    }

    private function getCityAttachments() {
        $map = $this->map;
        return $map->getCityAttachments();
    }

    //TODO: repeat connected check after all city tiles added. If wrong, save map arrangement and replace
    //city tiles.

    public function generateSql($tiles) {
        $sql = "INSERT INTO map_hex (x_coord, y_coord, type, color) VALUES ";
        $values = [];
        foreach($tiles as $tile) {
            [ "x_coord" => $x, "y_coord" => $y, "type" =>$type, "color" => $color ] = $tile;
            if ($type == null) {
                $type = "null";
            } else {
                $type = "'$type'";
            }
            if ($color == null) {
                $color = "null";
            } else {
                $color = "'$color'";
            }
            $values[] = "($x, $y, $type, $color)";
        }
        $sql .= implode(", ", $values);
        return $sql;
    }
}
