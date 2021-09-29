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

    private function allNeighbours($x, $y) {
        return [
            [$x, $y + 1],
            [$x + 1, $y],
            [$x + 1, $y - 1],
            [$x, $y - 1],
            [$x - 1, $y],
            [$x - 1, $y + 1]
        ];
    }

    private function getNeighbours($x, $y, $onMap = true) {
        $possibleNeighbours = $this->allNeighbours($x, $y);

        return array_filter($possibleNeighbours, function($coords) use ($onMap) {
            [$x, $y] = $coords;
            $result = $this->lookupCoords($x, $y);
            return $onMap ? ($result !== null) : ($result === null);
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

    /***ALL BELOW CODE IS NOT USED YET (ONLY FOR "RANDOM" - NON-COMPACT - MAPS) AND WILL
     NEED TO BE TOTALLY REWRITTEN WHEN THE TIME COMES. ONLY KEPT FOR NOW BECAUSE SOME OF IT
     MAY BE USEFUL! ***/

    // simply returns a list - in no particular order - of all locations on the "edge" of the map.
    // That is, these are locations which are not actually part of the map but which are adjacent to it.
    // Note that this simply tests whether hexes are lacking all 6 neighbours - therefore it counts
    // shallows as well. These will be weeded out later when finding connected areas!
    private function getEdgeAndShallows() {
        $map = $this->tileMap;
        $edge = [];

        foreach($map as $x => $yMap) {
            foreach($yMap as $y => $info) {
                $neighboursOffMap = $this->getNeighbours($x, $y, false);
                foreach($neighboursOffMap as $neighbour) {
                    if (!in_array($neighbour, $edge)) {
                        $edge[] = $neighbour;
                    }
                }
            }
        }

        return $edge;
    }

    // refines the result of the getEdgeAndShallows method by returning just the edge (not shallows).
    // NOTE: in order to identify the edge v. shallows, we take the largest connected set. This feels
    // like it should always be the case and I can't imagine it not being, but that's not exactly a
    // mathematical proof!
    //TODO: these approaches don't work at all - connectedEdge fails because we can process disconnected
    //locations that are later "revealed" as connected by other locations being processed, and we don't
    //check for this.
    //Think best thing to do is write a completely different approach, where we apply the "go in order"
    //functionality to the whole set
    private function connectedEdge() {
        $edgeAndShallows = $this->getEdgeAndShallows();
        $connectedSets = [];
        foreach ($edgeAndShallows as $edgeHex) {
            [$x, $y] = $edgeHex;
            $neighbours = $this->allNeighbours($x, $y);
            $found = false;
            foreach ($neighbours as $neighbour) {
                if (in_array($neighbour, $edgeAndShallows)) {
                    foreach ($connectedSets as &$soFar) {
                        if (in_array($neighbour, $soFar)) {
                            $soFar[] = $edgeHex;
                            $found = true;
                            break;
                        }
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

    // uses the result of the above but sorts into order, as one would "walk" along the edge
    private function getEdgeInOrder($edge, $soFar = []) {
        if (!isset($edge)) {
            $edge = $this->connectedEdge();
        }

        // if we've got everything, exit the function
        if (count($soFar) === count($edge)) {
            return $soFar;
        }

        if (count($soFar) === 0) {
            $current = $edge[0];
            $soFar[] = $current;
        } else {
            $current = $soFar[count($soFar) - 1];
        }

        [$x, $y] = $current;

        // find all neighbours of the current point that are in the set of edge tiles
        $neighbours = $this->allNeighbours($x, $y);
        $neighboursIndicesFound = [];
        $i = 0;
        foreach($neighbours as $neighbour) {
            if (in_array($neighbour, $edge) && !in_array($neighbour, $soFar)) {
                $neighboursIndicesFound[] = $i;
            }
            $i++;
        }

        switch (count($neighboursIndicesFound)) {
            case 0:
                // no neighbours - means we've picked a wrong path somewhere to lead to a dead end.
                // Return null to signal this
                //now realise this is WRONG, even the "standard" "compact" map shape will inevitably
                //run into this (towards the South West there is a narrow passage 2 tiles deep)
                //Probably need a COMPLETELY different way of deciding "equidistance"
                return null;
            case 1:
                // only one neighbour, so we know the next location. Push to array and recurse
                $soFar[] = $neighbours[$i];
                return $this->getEdgeInOrder($edge, $soFar);
            default:
                // this is the tricky case that we need to concentrate on. First we need to order the multiple
                // candidate locations in clockwise order. (Could have been anticlockwise, choice is arbitrary
                // - but it is important that it's consistent. As $neighbours is clockwise anyway we do it that
                // way - and this is why we have kept a record of the indices we found in that array.)
                while (!in_array(0, $neighboursIndicesFound) || in_array(5, $neighboursIndicesFound)) {
                    // we keep cycling the array until we find a "canonical form" where 0 is included but
                    // not 5.
                    $neighboursIndicesFound[] = array_shift($neighboursIndicesFound);
                }
                // there are only 2 possibilities - we take the neighbours in the order of
                // $neighboursIndicesFound, or in REVERSE order. So this is a very simple recursion to write.
                $firstTry = array_merge($soFar, array_map(function($i) use ($neighbours) {
                    return $neighbours[$i];
                }), $neighboursIndicesFound);
                $firstResult = $this->getEdgeInOrder($firstTry);
                if ($firstResult === null) {
                    $reversed = array_reverse($neighboursIndicesFound);
                    $secondTry = array_merge($soFar, array_map(function($i) use ($neighbours) {
                        return $neighbours[$i];
                    }), $reversed);
                    // it is assumed this will work and won't be null!
                    return $secondTry;
                } else {
                    // we succeeded, so return that
                    return $firstResult;
                }
        }
    }

    // given two locations to use as the "base" of a city tile (assumed to be connected locations along
    // the edge of the map), determines the location of the third hex. It returns that (there is at most 1),
    // or null if it's not possible due to the shape of the map in that area
    private function getCityThirdLocation($base1, $base2) {
        // work out the 2 possibilities for the third location, depending on orientation of the tile
        [$x, $y] = $base1;
        $neighbourIndex = array_search($this->allNeighbours($x, $y), $base2);
        // note that by design $neighbourIndex must be found
        switch ($neighbourIndex) {
            case 0:
                // [$x, $y + 1] - North-East direction. 3rd location is either North-West or East
                // of first
                $possibilities = [[$x - 1, $y + 1], [$x + 1, $y]];
                break;
            case 1:
                // [$x + 1, $y] - East direction. 3rd location is either North-East or South-East
                // of first
                $possibilities = [[$x, $y + 1], [$x + 1, $y - 1]];
                break;
            case 2:
                // [$x + 1, $y - 1] - South-East direction. 3rd location is either East or South-West
                // of first
                $possibilities = [[$x + 1, $y], [$x, $y - 1]];
                break;
            case 3:
                // [$x, $y - 1] - South-West direction. 3rd location is either South-East or West
                // of first
                $possibilities = [[$x + 1, $y - 1], [$x - 1, $y]];
                break;
            case 4:
                // [$x - 1, $y - West direction. 3rd location is either South-West or North-West
                // of first
                $possibilities = [[$x, $y - 1], [$x - 1, $y + 1]];
                break;
            case 5:
                // [$x - 1, $y + 1] - North-West direction. 3rd location is either West or North-East
                // of first
                $possibilities = [[$x - 1, $y], [$x, $y + 1]];
                break;
        }
        $onMap = array_filter($possibilities, function($location) {
            [$x, $y] = $location;
            return $this->lookupCoords($x, $y) !== null;
        });
        if (count($onMap) === 0) {
            return null;
        } else {
            return $onMap[0];
        }
    }

    // returns a list of triples of hex coordinates to use as city locations. This means
    // (note, not strictly defined in the rules, but seems to be the "spirit" of what is intended) that
    // 2 of the 3 hexes have to touch the edge of the map, and (of course) none of the 3 overlap the map.
    public function getCityAttachments() {
        $edge = $this->getEdgeInOrder(null);
        $edgeLength = count($edge);
        $gaps = array_map(function($i) use ($edgeLength) {
            return round($edgeLength * $i / 6);
        }, [0, 1, 2, 3, 4, 5]);
        $foundOne = false;
        while (!$foundOne) {
            //TODO: theoretiaclly possible that no "start" values work, and we need to jig the $gaps
            //a little. Don't worry about this yet though.
            $start = bga_rand(0, $edgeLength - 1);
            $candidates = array_map(function($gap) use ($start) {
                $current = ($gap + $start) % $edgeLength;
                $next = ($current + 1) % $edgeLength;
                return [$edge[$current], $edge[$next]];
            }, $gaps);
            $ok = [];
            foreach($candidates as [$base1, $base2]) {
                $third = $this->getCityThirdLocation($base1, $base2);
                if ($third === null) {
                    break;
                }
                $ok[] = [$base1, $base2, $third];
            }
            if (count($ok) === 6) {
                $foundOne = true;
            }
        }
        return $ok;
    }
}
