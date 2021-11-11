/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * TheOracleOfDelphi implementation : © Robin Zigmond <robinzig@hotmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * theoracleofdelphi.js
 *
 * TheOracleOfDelphi user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

// constants sent from backend
const OFFERING = "offering";
const MONSTER = "monster";
const STATUE = "statue";
const ISLAND = "island";

const RED = "red";
const YELLOW = "yellow";
const GREEN = "green";
const BLUE = "blue";
const PINK = "pink";
const BLACK = "black";

const ALL_COLORS = [RED, YELLOW, GREEN, BLUE, PINK, BLACK];

define([
    "dojo", "dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter"
],
    function (dojo, declare) {
        return declare("bgagame.theoracleofdelphi", ebg.core.gamegui, {
            constructor: function () {
                console.log('theoracleofdelphi constructor');

                // Here, you can init the global variables of your user interface
                // Example:
                // this.myGlobalValue = 0;

                //TODO: will need to come from backend
                const tilesInMapWidth = 14;
                let screenWidth = document.getElementById("game_play_area").clientWidth;
                // take BGA zoom into account for small screens
                if (screenWidth < 740) {
                    screenWidth = 740;
                }
                //TODO: allow user adjustment, and use local storage to recall value
                this.tileWidth = screenWidth / tilesInMapWidth;
            },

            /*
                setup:
                
                This method must set up the game user interface according to current game situation specified
                in parameters.
                
                The method is called each time the game interface is displayed to a player, ie:
                _ when the game starts
                _ when a player refreshes the game page (F5)
                
                "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
            */

            setup: function (gamedatas) {
                // adjust map tile size to fit screen
                document.documentElement.style.setProperty("--tile-width", `${this.tileWidth}px`)

                // Setting up player boards
                for (var player_id in gamedatas.players) {
                    var player = gamedatas.players[player_id];

                    // TODO: Setting up players boards if needed
                }

                // place Zeus in appropriate spot of the map. (Never changes so not part of gamedatas.)
                const zeusHex = document.querySelector(".ood_maphex_zeus");
                const zeusFigure = document.createElement("div");
                zeusFigure.classList.add("ood_zeus_figure", "ood_hex_center");
                zeusHex.appendChild(zeusFigure);

                // also place temples (in fixed positions depending only on the map)
                ALL_COLORS.forEach((color) => {
                    const templeHex = document.querySelector(`.ood_maphex_temple.ood_maphex_color_${color}`);
                    const templeDiv = document.createElement("div");
                    templeDiv.classList.add(
                        "ood_wooden_piece",
                        "ood_temple",
                        `ood_temple_${color}`
                    );
                    templeHex.appendChild(templeDiv);
                });

                // place all tokens of all types on the map
                const { tokensOnMap } = gamedatas;
                // keep track of counts of each "type" of token in each location
                const counts = {};
                const addToCounts = (x, y, type) => {
                    if (counts[x]) {
                        if (counts[x][y]) {
                            if (counts[x][y][type]) {
                                counts[x][y][type]++;
                            } else {
                                counts[x][y][type] = 1;
                            }
                        } else {
                            counts[x][y] = { [type]: 1 };
                        }
                    } else {
                        counts[x] = { [y]: { [type]: 1 } };
                    }
                };
                const getCount = (x, y, type) => {
                    let result = 0;
                    if (counts[x] && counts[x][y] && counts[x][y][type]) {
                        result = counts[x][y][type];
                    }
                    return result;
                };
                const getTotal = (x, y, type_) => {
                    return tokensOnMap.filter(({ location_x, location_y, type }) => (
                        location_x === x
                        && location_y === y
                        && type === type_
                    )).length;
                }

                for (const { location_x, location_y, type, color, player_id, status } of tokensOnMap) {
                    const position = getCount(location_x, location_y, type);
                    const total = getTotal(location_x, location_y, type);
                    switch (type) {
                        case OFFERING:
                            this.placeOffering(location_x, location_y, color, position, total);
                            break;
                        case MONSTER:
                            this.placeMonster(location_x, location_y, color, position, total);
                            break;
                        case STATUE:
                            this.placeStatue(location_x, location_y, color, position);
                            break;
                        case ISLAND: {
                            this.placeIsland(location_x, location_y, color, status, gamedatas.greekLetters);
                            break;
                        }
                        default:
                            break;
                    }
                    addToCounts(location_x, location_y, type);
                }

                // Setup game notifications to handle (see "setupNotifications" method below)
                this.setupNotifications();

                console.log("Ending game setup");
            },


            ///////////////////////////////////////////////////
            //// Game & client states

            // onEnteringState: this method is called each time we are entering into a new game state.
            //                  You can use this method to perform some user interface changes at this moment.
            //
            onEnteringState: function (stateName, args) {
                console.log('Entering state: ' + stateName);

                switch (stateName) {

                    /* Example:
                    
                    case 'myGameState':
                    
                        // Show some HTML block at this game state
                        dojo.style( 'my_html_block_id', 'display', 'block' );
                        
                        break;
                   */


                    case 'dummmy':
                        break;
                }
            },

            // onLeavingState: this method is called each time we are leaving a game state.
            //                 You can use this method to perform some user interface changes at this moment.
            //
            onLeavingState: function (stateName) {
                console.log('Leaving state: ' + stateName);

                switch (stateName) {

                    /* Example:
                    
                    case 'myGameState':
                    
                        // Hide the HTML block we are displaying only during this game state
                        dojo.style( 'my_html_block_id', 'display', 'none' );
                        
                        break;
                   */


                    case 'dummmy':
                        break;
                }
            },

            // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
            //                        action status bar (ie: the HTML links in the status bar).
            //        
            onUpdateActionButtons: function (stateName, args) {
                console.log('onUpdateActionButtons: ' + stateName);

                if (this.isCurrentPlayerActive()) {
                    switch (stateName) {
                        /*               
                                         Example:
                         
                                         case 'myGameState':
                                            
                                            // Add 3 action buttons in the action status bar:
                                            
                                            this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' ); 
                                            this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' ); 
                                            this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' ); 
                                            break;
                        */
                    }
                }
            },

            ///////////////////////////////////////////////////
            //// Utility methods

            /*
            
                Here, you can defines some utility methods that you can use everywhere in your javascript
                script.
            
            */

            // utilities for placing tokens on the map
            placeOffering: function (x, y, color, position, total) {
                const hex = document.getElementById(`ood_maphex_${x}_${y}`);
                const tokenDiv = document.createElement("div");
                tokenDiv.classList.add(
                    "ood_wooden_piece",
                    "ood_offering",
                    `ood_offering_${color}`
                );
                // subtract pi/6 from the angle to compensate for the tile rotation
                const angle = 2 * Math.PI * position / total - Math.PI / 6;
                const top = 50 + Math.sin(angle) * 30;
                const left = 50 + Math.cos(angle) * 30;
                tokenDiv.style.top = `${top}%`;
                tokenDiv.style.left = `${left}%`;
                hex.appendChild(tokenDiv);
            },

            placeMonster: function (x, y, color, position, total) {
                const hex = document.getElementById(`ood_maphex_${x}_${y}`);
                const tokenDiv = document.createElement("div");
                tokenDiv.classList.add(
                    "ood_wooden_piece",
                    "ood_monster",
                    `ood_monster_${color}`
                );
                const midPoint = (total + 1) / 2;
                const offset = 1 + position - midPoint;
                const top = (total === 1) ? 50 : 50 + 20 * (offset / (total - 1));
                const left = top;
                tokenDiv.style.top = `${top}%`;
                tokenDiv.style.left = `${left}%`;
                hex.appendChild(tokenDiv);
            },

            placeStatue: function (x, y, color, position) {
                const hex = document.getElementById(`ood_maphex_${x}_${y}`);
                const tokenDiv = document.createElement("div");
                tokenDiv.classList.add(
                    "ood_wooden_piece",
                    "ood_statue",
                    `ood_statue_${color}`
                );
                let top, left;
                switch (position) {
                    case 0:
                        [top, left] = [40, 80];
                        break;
                    case 1:
                        [top, left] = [15, 50];
                        break;
                    case 2:
                        [top, left] = [40, 20];
                        break;
                    default:
                        console.error(`unexpected position value for statue: ${position}`);
                        break;
                }
                // compensate for tile rotation - rotate clockwise by 30 degrees
                const cos = Math.cos(-Math.PI / 6);
                const sin = Math.sin(-Math.PI / 6);
                let xPos = (left - 50) / 50;
                let yPos = (50 - top) / 50;
                [xPos, yPos] = [xPos * cos - yPos * sin, yPos * cos + xPos * sin];
                left = 50 * (xPos + 1);
                top = 50 * (1 - yPos);
                tokenDiv.style.top = `${top}%`;
                tokenDiv.style.left = `${left}%`;
                hex.appendChild(tokenDiv);
            },

            placeIsland: function (x, y, color, status, greekLetterArray) {
                const hex = document.getElementById(`ood_maphex_${x}_${y}`);
                const tokenDiv = document.createElement("div");
                let islandDetails;
                if (Number(status) === -1) {
                    islandDetails = "back";
                } else {
                    islandDetails = `${color}_${greekLetterArray[status - 4]}`;
                }
                tokenDiv.classList.add(
                    "ood_island_tile",
                    `ood_island_${islandDetails}`,
                    "ood_hex_center"
                );
                hex.appendChild(tokenDiv);
            },

            ///////////////////////////////////////////////////
            //// Player's action

            /*
            
                Here, you are defining methods to handle player's action (ex: results of mouse click on 
                game objects).
                
                Most of the time, these methods:
                _ check the action is possible at this game state.
                _ make a call to the game server
            
            */

            /* Example:
            
            onMyMethodToCall1: function( evt )
            {
                console.log( 'onMyMethodToCall1' );
                
                // Preventing default browser reaction
                dojo.stopEvent( evt );
    
                // Check that this action is possible (see "possibleactions" in states.inc.php)
                if( ! this.checkAction( 'myAction' ) )
                {   return; }
    
                this.ajaxcall( "/theoracleofdelphi/theoracleofdelphi/myAction.html", { 
                                                                        lock: true, 
                                                                        myArgument1: arg1, 
                                                                        myArgument2: arg2,
                                                                        ...
                                                                     }, 
                             this, function( result ) {
                                
                                // What to do after the server call if it succeeded
                                // (most of the time: nothing)
                                
                             }, function( is_error) {
    
                                // What to do after the server call in anyway (success or failure)
                                // (most of the time: nothing)
    
                             } );        
            },        
            
            */


            ///////////////////////////////////////////////////
            //// Reaction to cometD notifications

            /*
                setupNotifications:
                
                In this method, you associate each of your game notifications with your local method to handle it.
                
                Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                      your theoracleofdelphi.game.php file.
            
            */
            setupNotifications: function () {
                console.log('notifications subscriptions setup');

                // TODO: here, associate your game notifications with local methods

                // Example 1: standard notification handling
                // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );

                // Example 2: standard notification handling + tell the user interface to wait
                //            during 3 seconds after calling the method in order to let the players
                //            see what is happening in the game.
                // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
                // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
                // 
            },

            // TODO: from this point and below, you can write your game notifications handling methods

            /*
            Example:
            
            notif_cardPlayed: function( notif )
            {
                console.log( 'notif_cardPlayed' );
                console.log( notif );
                
                // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
                
                // TODO: play the card in the user interface.
            },    
            
            */
        });
    });
