<?php

/* Utility class for storing map information and manipulating/querying it.
 * Data is passed in as a list of objects with keys representing x and y coords, type and color.
 * In the constructor this is converted to a map to look up by locations by co-ordinates.
 * Sadly in PHP "tuples" (for which we'd have to use arrays) can't be used as keys in an associative array,
 * so we instead store it internally as a double-keyed map: x => [y1 => [type, color], y2 => [type, color] etc.]
*/

class delphi_maputils {
    function __construct($tilesList) {
        $this->tileMap = $this->listToMap($tilesList);
    }

    private function listToMap($tiles) {
        $map = [];

        foreach($tiles as [
            "x_coord" => $x,
            "y_coord" => $y,
            "type" => $type,
            "color" => $color
        ]) {
            if (array_key_exists($x, $map)) {
                $map[$x][$y] = ["type" => $type, "color" => $color];
            } else {
                $map[$x] = [$y => ["type" => $type, "color" => $color]];
            }
        }

        return $map;
    }

    private function lookupCoords($x, $y) {
        $map = $this->tileMap;
        if (array_key_exists($x, $map)) {
            if (array_key_exists($y, $map[$x])) {
                return $this->tileMap[$x][$y];
            }
        }
        return null;
    }

    private function getNeighbours($x, $y) {
        $possibleNeighbours = [
            [$x, $y + 1],
            [$x + 1, $y],
            [$x + 1, $y - 1],
            [$x, $y - 1],
            [$x - 1, $y],
            [$x - 1, $y + 1]
        ];

        return array_filter($possibleNeighbours, function($coords) {
            [$x, $y] = $coords;
            return $this->lookupCoords($x, $y) !== null;
        });
    }

    private function countWater() {
        $waterCount = 0;

        foreach($this->tileMap as $x => $yMap) {
            foreach($yMap as $y => $hexInfo) {
                if ($hexInfo["type"] === MAP_TYPE_WATER) {
                    $waterCount += 1;
                }
            }
        }

        return $waterCount;
    }

    private function findWater() {
        $map = $this->tileMap;

        foreach($map as $x => $yMap) {
            foreach($yMap as $y => $hexInfo) {
                if ($hexInfo["type"] === MAP_TYPE_WATER) {
                    return [$x, $y];
                }
            }
        }

        // won't ever get here!
    }

    // mutates the array that's passed to it, and returns true if any neighbours
    // were added, otherwise false. Used recursively to build up a connected area
    // of water.
    private function addNeighbours(&$hexes) {
        $added = false;
        $new = [];
        foreach($hexes as [$x, $y]) {
            $neighbours = $this->getNeighbours($x, $y);
            foreach($neighbours as $neighbour) {
                [$neighbourX, $neighbourY] = $neighbour;
                if ($this->lookupCoords($neighbourX, $neighbourY)["type"] === MAP_TYPE_WATER) {
                    if (!in_array($neighbour, $hexes) && !in_array($neighbour, $new)) {
                        $new[] = $neighbour;
                        $added = true;
                    }
                }
            }
        }
        $hexes = array_merge($hexes, $new);
        return $added;
    }

    private function connectedWater($start) {
        $hexes = [$start];
        $moreToAdd = true;
        while ($moreToAdd) {
            $moreToAdd = $this->addNeighbours($hexes);
        }
        return $hexes;
    }

    public function isWaterConnected() {
        $start = $this->findWater();
        $connectedArea = $this->connectedWater($start);
        $totalWater = $this->countWater();
        return $totalWater === count($connectedArea);
    }
}
