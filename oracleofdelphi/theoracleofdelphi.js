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

const WILD = "wild";

const ALL_COLORS = [RED, YELLOW, GREEN, BLUE, PINK, BLACK];

const POSEIDON = "poseidon";
const APOLLON = "apollon";
const ARTEMIS = "artemis";
const APHRODITE = "aphrodite";
const ARES = "ares";
const HERMES = "hermes";

const ALL_GODS = [POSEIDON, APOLLON, ARTEMIS, APHRODITE, ARES, HERMES];

const ORACLE = "oracle";
const INJURY = "injury";
const COMPANION = "companion";
const EQUIPMENT = "equipment";

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

                // add cards to both player areas and decks/piles below. Also setup counters for deck/discard size.
                this.counters = {};
                for (const cardType of [ORACLE, INJURY, EQUIPMENT]) {
                    const deckCounter = new ebg.counter();
                    deckCounter.create(`ood_${cardType}_deck_count`);
                    deckCounter.toValue(gamedatas.cards[cardType].deck_size);
                    const deckContainer = document.getElementById(`ood_${cardType}_deck`);
                    if (gamedatas.cards[cardType].deck_size > 0) {
                        const cardBack = document.createElement("div");
                        cardBack.classList.add("ood_card", `ood_card_${cardType}`, `ood_card_${cardType}_back`);
                        deckContainer.appendChild(cardBack);
                    } else {
                        const spacer = document.createElement("div");
                        spacer.classList.add("ood_card_spacer");
                        deckContainer.appendChild(spacer);
                    }

                    const discardCounter = new ebg.counter();
                    discardCounter.create(`ood_${cardType}_discard_count`);
                    discardCounter.toValue(gamedatas.cards[cardType].discard_size);
                    const { top_discard } = gamedatas.cards[cardType];
                    const discardContainer = document.getElementById(`ood_${cardType}_discard`);
                    if (top_discard) {
                        const card = document.createElement("div");
                        card.classList.add("ood_card", `ood_card_${cardType}`, `ood_card_${cardType}_${top_discard}`);
                        discardContainer.appendChild(card);
                    } else {
                        const spacer = document.createElement("div");
                        spacer.classList.add("ood_card_spacer");
                        discardContainer.appendChild(spacer);
                    }

                    this.counters[cardType] = { deck: deckCounter, discard: discardCounter };
                }
                const equipmentDisplay = document.getElementById("ood_equipment_display");
                for (const equipmentCard in gamedatas.cards.equipment.display) {
                    const cardDiv = document.createElement("div");
                    cardDiv.classList.add("ood_card", "ood_card_equipment", `ood_card_equipment_${equipmentCard}`);
                    equipmentDisplay.appendChild(cardDiv);
                }

                // set up player-related info
                const { players } = gamedatas;
                this.counters.favors = {};
                for (const [playerId, info] of Object.entries(players)) {
                    const favorCounter = new ebg.counter();
                    favorCounter.create(`ood_favor_count_${playerId}`);
                    favorCounter.toValue(info.favors);
                    this.counters.favors[playerId] = favorCounter;

                    const shiptileWrapper = document.getElementById(`ood_shiptile_${playerId}`);
                    const shiptile = document.createElement("div");
                    shiptile.classList.add("ood_ship_tile", `ood_ship_tile_${info.shipTile}`);
                    shiptileWrapper.appendChild(shiptile);

                    const { dice } = info;
                    for (const die of dice) {
                        const { color, used } = die;
                        const diceDiv = document.createElement("div");
                        diceDiv.classList.add("ood_die_result", "ood_oracle_die", `ood_oracle_die_${color}`);
                        const position = document.getElementById(`ood_dice_spot_${used ? "center" : color}_${playerId}`);
                        position.appendChild(diceDiv);
                    }
                    // reposition dice if any are duplicates in the same section, so they're offset nicely
                    for (const pos of [...ALL_COLORS, "center"]) {
                        const diceSpot = document.getElementById(`ood_dice_spot_${pos}_${playerId}`);
                        const diceInSpot = diceSpot.childNodes;
                        const numDice = diceInSpot.length;
                        if (numDice === 2) {
                            diceInSpot[0].classList.add("ood_die_in_spot_1_of_2");
                            diceInSpot[1].classList.add("ood_die_in_spot_2_of_2");
                        } else if (numDice === 3) {
                            diceInSpot[0].classList.add("ood_die_in_spot_1_of_3");
                            diceInSpot[1].classList.add("ood_die_in_spot_2_of_3");
                            diceInSpot[2].classList.add("ood_die_in_spot_3_of_3");
                        }
                    }

                    for (let i = 1; i <= info.shrines; i++) {
                        const shrineContainer = document.getElementById(`ood_shrine_spot_${i}_${playerId}`);
                        const shrineDiv = document.createElement("div");
                        shrineDiv.classList.add("ood_wooden_piece", "ood_shrine");;
                        shrineContainer.appendChild(shrineDiv);
                    }

                    for (const god of ALL_GODS) {
                        const godValue = Number(info[god]);
                        const godColumn = document.getElementById(`ood_god_column_${god}_${playerId}`);
                        const godDisc = document.createElement("div");
                        godDisc.classList.add(
                            "ood_wooden_piece",
                            "ood_god",
                            `ood_god_${god}`,
                            `ood_god_position_${godValue}`
                        );
                        godColumn.appendChild(godDisc);
                    }

                    const shieldSpot = document.getElementById(`ood_shield_spot_${playerId}`);
                    shieldSpot.classList.add(`ood_shield_pos_${info.shields}`);
                    const shield = document.createElement("div");
                    shield.classList.add("ood_shield", `ood_shield_${info.color}`);
                    shieldSpot.appendChild(shield);

                    const shipHex = document.getElementById(`ood_maphex_${info.ship_location_x}_${info.ship_location_y}`);
                    const shipDiv = document.createElement("div");
                    shipDiv.classList.add("ood_wooden_piece", "ood_ship", `ood_ship_${info.color}`);
                    shipHex.appendChild(shipDiv);

                    let nonWildOfferings = 0;
                    let nonWildMonsters = 0;
                    info.zeus.filter(({ completed }) => !completed)
                        .forEach(({ originalId, type, details, complete }) => {
                            // compute proper position (from 1 - 12), depending on tile details.
                            // Generally will be same as originalId, but not for monsters and offerings,
                            // due to the wild tiles (ids 7 and 8).
                            // Also 9-12 can each be either offering or monster (but always exactly 2 of each).
                            // This is why we could how many of each we've had
                            let position = originalId;
                            if (type === OFFERING) {
                                if (details === WILD) {
                                    position = 9;
                                } else {
                                    position = 7 + nonWildOfferings;
                                    nonWildOfferings++;
                                }
                            } else if (type === MONSTER) {
                                if (details === WILD) {
                                    position = 12;
                                } else {
                                    position = 10 + nonWildMonsters;
                                    nonWildMonsters++;
                                }
                            }
                            const tileSlot = document.getElementById(`ood_zeus_tile_spot_${position}_${playerId}`);
                            const tileDiv = document.createElement("div");
                            tileDiv.classList.add("ood_zeus_tile", `ood_zeus_${info.color}_${type}_${details}`);
                            tileSlot.appendChild(tileDiv);
                        });

                    // cards:
                    // used oracle card
                    if (info.oracle_used) {
                        const container = document.getElementById(`ood_oracle_used_section_${playerId}`);
                        const card = document.createElement("div");
                        card.classList.add("ood_card", "ood_card_oracle", `ood_card_oracle_${info.oracle_used}`);
                        container.appendChild(card);
                    }

                    // unused oracle cards (remember to remove used one!)
                    if (gamedatas.cards.oracle.hands[playerId]) {
                        let oracleCards = [...gamedatas.cards.oracle.hands[playerId]];
                        if (info.oracle_used) {
                            const toRemove = oracleCards.indexOf(info.oracle_used);
                            oracleCards = oracleCards.filter((card, index) => index !== toRemove);
                        }
                        oracleCards.forEach((color, index) => {
                            const container = document.getElementById(`ood_oracle_section_${playerId}`);
                            const card = document.createElement("div");
                            card.classList.add("ood_card", "ood_card_oracle", `ood_card_oracle_${color}`);
                            card.style.right = `${30 * index - 110}px`;
                            container.appendChild(card);
                        });
                    }

                    // injury cards
                    if (gamedatas.cards.injury.hands[playerId]) {
                        gamedatas.cards.injury.hands[playerId].forEach((color, index) => {
                            const container = document.getElementById(`ood_injury_section_${playerId}`);
                            const card = document.createElement("div");
                            card.classList.add("ood_card", "ood_card_injury", `ood_card_injury_${color}`);
                            card.style.right = `${37 * index - 110}px`;
                            container.appendChild(card);
                        });
                    }

                    // companion cards
                    if (gamedatas.cards.companion.hands[playerId]) {
                        gamedatas.cards.companion.hands[playerId].forEach((cardId, index) => {
                            const container = document.getElementById(`ood_companion_section_${playerId}`);
                            const card = document.createElement("div");
                            card.classList.add("ood_card", "ood_card_companion", `ood_card_companion_${cardId}`);
                            card.style.left = `${37 * index}px`;
                            container.appendChild(card);
                        });
                    }

                    // equipment cards
                    if (gamedatas.cards.equipment.hands[playerId]) {
                        gamedatas.cards.equipment.hands[playerId].forEach((cardId, index) => {
                            const container = document.getElementById(`ood_equipment_section_${playerId}`);
                            const card = document.createElement("div");
                            card.classList.add("ood_card", "ood_card_equipment", `ood_card_equipment_${cardId}`);
                            card.style.left = `${50 * index}px`;
                            container.appendChild(card);
                        });
                    }
                }
                // Add CSS classes to position ships nicely in their hex according to the total number.
                // (Note that the Zeus space gets different positioning, but that is all handled in CSS.)
                document.querySelectorAll(".ood_maphex").forEach((hex) => {
                    const ships = hex.querySelectorAll(".ood_ship");
                    const numShips = ships.length;
                    let counter = 1;
                    ships.forEach((ship) => {
                        ship.classList.add(`ood_ship_${counter}_of_${numShips}`);
                        counter++;
                    });
                });

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
