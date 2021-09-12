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

    // simply returns a list - in no particular order - of all locations on the "edge" of the map.
    // Note that this simply tests whether hexes are lacking all 6 neighbours - therefore it counts
    // shallows as well. These will be weeded out later when finding connected areas!
    private function getEdgeAndShallows() {
        $map = $this->tileMap;
        $edge = [];

        foreach($map as $x => $yMap) {
            foreach($yMap as $y => $info) {
                $neighbours = $this->getNeighbours($x, $y);
                if (count($neighbours) < 6) {
                    $edge[] = [$x, $y];
                }
            }
        }

        return $edge;
    }

    // refines the result of the getEdgeAndShallows method by returning just the edge (not shallows)
    //TODO: revisit this, it's just looking at physical connection of the map pieces, but it's possible for
    //the edge to itself be connected to shallows, in which case this will  give the wrong result!
    //Need to revise by getting locations of the "actual edge" - ie the hexes which are NOT part of the map,
    //but just adjacent to it.
    private function connectedEdge() {
        $edgeAndShallows = $this->getEdgeAndShallows();
        $connectedSets = [];
        foreach ($edgeAndShallows as $edgeHex) {
            $neighbours = $this->getNeighbours($x, $y);
            $found = false;
            foreach ($neighbours as $neighbour) {
                foreach ($connectedSets as $soFar) {
                    if (in_array($neighbour, $soFar)) {
                        $soFar[] = $edgeHex;
                        $found = true;
                        break;
                    }
                }
                if ($found) {
                    break;
                }
            }
            if (!$found) {
                $connectedSets[] = [$edgeHex];
            }
        }

        // the "real edge" will be the one of these connected sets which is longest
        $realEdge = [];
        foreach ($connectedSets as $connected) {
            if (count($connected) > count($realEdge)) {
                $realEdge = $connected;
            }
        }
        return $realEdge;
    }

    //TODO: continue building up to below function. Next we need to get the edge in the correct order.
    //Might be better to do it as part of the above method to avoid having to loop over edges repeatedly?

    // returns a list of all triples of hex coordinates that can be used as a city location. This means
    // (note, not strictly defined in the rules, but seems to be the "spirit" of what is intended) that
    // 2 of the 3 hexes have to touch the edge of the map, and (of course) none of the 3 overlap the map
    //TODO: also need some sort of "distance indication" so that we can make the city tiles equidistant around
    //the edge
    public function getCityAttachments() {
        //TODO
    }
}
