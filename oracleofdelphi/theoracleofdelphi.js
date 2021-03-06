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
const TEMPLE = "temple";

const RED = "red";
const YELLOW = "yellow";
const GREEN = "green";
const BLUE = "blue";
const PINK = "pink";
const BLACK = "black";

const WILD = "wild";

const ALL_COLORS = [RED, YELLOW, GREEN, BLUE, PINK, BLACK];

const FRIENDLY_COLORS = {
    ["ff0000"]: RED,
    ["008000"]: GREEN,
    ["0000ff"]: BLUE,
    ["ffa500"]: YELLOW
};

const COLOR_STATUE_COMBINATIONS = {
    [RED]: [`${STATUE}_${RED}_${BLUE}_${PINK}`, `${STATUE}_${RED}_${PINK}_${BLACK}`, `${STATUE}_${RED}_${GREEN}_${YELLOW}`],
    [YELLOW]: [`${STATUE}_${GREEN}_${YELLOW}_${PINK}`, `${STATUE}_${BLACK}_${BLUE}_${YELLOW}`, `${STATUE}_${RED}_${GREEN}_${YELLOW}`],
    [GREEN]: [`${STATUE}_${GREEN}_${YELLOW}_${PINK}`, `${STATUE}_${GREEN}_${BLUE}_${BLACK}`, `${STATUE}_${RED}_${GREEN}_${YELLOW}`],
    [BLUE]: [`${STATUE}_${RED}_${BLUE}_${PINK}`, `${STATUE}_${BLACK}_${BLUE}_${YELLOW}`, `${STATUE}_${GREEN}_${BLUE}_${BLACK}`],
    [PINK]: [`${STATUE}_${RED}_${BLUE}_${PINK}`, `${STATUE}_${GREEN}_${YELLOW}_${PINK}`, `${STATUE}_${RED}_${PINK}_${BLACK}`],
    [BLACK]: [`${STATUE}_${BLACK}_${BLUE}_${YELLOW}`, `${STATUE}_${RED}_${PINK}_${BLACK}`, `${STATUE}_${GREEN}_${BLUE}_${BLACK}`]
};

const POSEIDON = "poseidon";
const APOLLON = "apollon";
const ARTEMIS = "artemis";
const APHRODITE = "aphrodite";
const ARES = "ares";
const HERMES = "hermes";

const GOD_COLORS = {
    [RED]: APHRODITE,
    [YELLOW]: APOLLON,
    [GREEN]: ARTEMIS,
    [BLUE]: POSEIDON,
    [PINK]: HERMES,
    [BLACK]: ARES
};

const ALL_GODS = [POSEIDON, APOLLON, ARTEMIS, APHRODITE, ARES, HERMES];

const SIGMA = "sigma";
const PHI = "phi";
const PSI = "psi";
const OMEGA = "omega";

const ALL_GREEK_LETTERS = [SIGMA, PHI, PSI, OMEGA];

const ORACLE = "oracle";
const INJURY = "injury";
const COMPANION = "companion";
const EQUIPMENT = "equipment";

const OOD_ACTION_QUEUE = "bga_ood_actions";

// state names. Note: must match "name" attribute of state in states.inc.php
const STATE_PLAYER_TURN = "playerTurn";

// client states
const STATE_DIE_CHOSEN = "client_dieChosen";
const STATE_RECOLOR_DIE = "client_recolorDie";

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
                this.tilesInMapWidth = 14;

                window.addEventListener("resize", this.onScreenChange.bind(this));
                window.addEventListener("orientationchange", this.onScreenChange.bind(this));

                // object for all counter components
                this.counters = {};

                // used for keeping references to various click handlers that need to
                // be selectively removed
                this.handlers = {
                    dice: {},
                    oracles: {},
                    gods: {}
                };

                // client state arguments, which will be used extensively!
                this.clientStateArgs = {};
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
                this.placeZeus();

                this.placeTemples();

                this.placeMapTokens(gamedatas.tokensOnMap, gamedatas.greekLetters);

                this.setupCardDisplay(gamedatas.cards);

                // set up player-related info
                const { players } = gamedatas;
                this.counters.favors = {};
                for (const [playerId, info] of Object.entries(players)) {
                    const favorCounter = new ebg.counter();
                    favorCounter.create(`ood_favor_count_${playerId}`);
                    favorCounter.toValue(info.favors);
                    this.counters.favors[playerId] = favorCounter;

                    // place shiptile
                    this.placeElement(
                        `ood_shiptile_${playerId}`, "ood_ship_tile", `ood_ship_tile_${info.shipTile}`
                    );

                    this.placePlayerDice(playerId, info.dice);
                    this.placeShrinesOnPlayerboard(playerId, info.shrines);
                    this.placeGodTokens(playerId, info);
                    this.placeShield(playerId, info.color, info.shields);

                    // place ship token
                    this.placeElement(
                        `ood_maphex_${info.ship_location_x}_${info.ship_location_y}`,
                        "ood_wooden_piece",
                        "ood_ship",
                        `ood_ship_${info.color}`
                    );

                    this.setupZeusTiles(
                        playerId, info.color, info.zeus.filter(({ completed }) => !completed)
                    );

                    // cards:
                    // used oracle card
                    if (info.oracle_used) {
                        this.placeElement(
                            `ood_oracle_used_section_${playerId}`,
                            "ood_card",
                            "ood_card_oracle",
                            `ood_card_oracle_${info.oracle_used}`
                        );
                    }

                    this.placeAvailableOracleCards(
                        playerId, gamedatas.cards.oracle.hands[playerId], info.oracle_used
                    );
                    this.placeInjuryCards(playerId, gamedatas.cards.injury.hands[playerId]);
                    this.placeCompanionCards(playerId, gamedatas.cards.companion.hands[playerId]);
                    this.placeEquipmentCards(playerId, gamedatas.cards.equipment.hands[playerId]);
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

                // ensure layout is correct for screen resolution
                this.onScreenChange();

                // fetch action queue from local storage, if it exists
                const savedActions = localStorage.getItem(OOD_ACTION_QUEUE);
                this.actionQueue = savedActions ? JSON.parse(savedActions) : [];
                //TODO: need to "play through" actions in the just-fetched queue (if non-empty),
                //so that the UI matches the partially-played turn
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
                    case STATE_PLAYER_TURN: {
                        // remove all ood_action_trigger classes, and event listeners.
                        // Remove class last as it's used to determine the elements with listeners
                        // to remove.
                        const { dice, oracles, gods } = this.handlers;

                        const actualDice = document.querySelectorAll(
                            ".ood_playerboard .ood_oracle_die.ood_action_trigger"
                        );

                        actualDice.forEach((die) => {
                            const color = Array.from(die.classList)
                                .find(className => className.startsWith("ood_oracle_die_"))
                                .slice("ood_oracle_die_".length);
                            die.removeEventListener("click", dice[color]);
                        });

                        const actualOracles = document.querySelectorAll(
                            ".ood_playerboard_left_side .ood_card_oracle.ood_action_trigger"
                        );
                        actualOracles.forEach((card) => {
                            const color = Array.from(card.classList)
                                .find(className => className.startsWith("ood_card_oracle_"))
                                .slice("ood_card_oracle_".length);
                            card.removeEventListener("click", oracles[color]);
                        });

                        const actualGods = document.querySelectorAll(
                            ".ood_playerboard .ood_god.ood_action_trigger"
                        );
                        actualGods.forEach((god) => {
                            const color = Array.from(god.classList)
                                .find(className => className.startsWith("ood_god_"))
                                .slice("ood_god_".length);
                            god.removeEventListener("click", gods[color]);
                        });

                        Array.from(document.getElementsByClassName("ood_action_trigger")).forEach((element) => {
                            element.classList.remove("ood_action_trigger");
                        });

                        this.handlers = {
                            dice: {},
                            oracles: {},
                            gods: {}
                        };
                        break;
                    }
                    default:
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
                        case STATE_PLAYER_TURN: {
                            const parentSelector = `#ood_playerboard_${this.player_id}`;
                            // TODO: use client state to track info as well! Needs to be updated
                            // in client-side action handlers
                            let colorIndex = 0;
                            const { dice, oracles, gods } = this.handlers;
                            for (const dieColor of args.unusedDice) {
                                const handler = () => {
                                    this.clientStateArgs.dieChosen = dieColor;
                                    this.setClientState(STATE_DIE_CHOSEN, {
                                        descriptionmyturn: _("${you} must choose an action with this die")
                                    });
                                };
                                if (!dice[dieColor]) {
                                    dice[dieColor] = handler;
                                }
                                this.addActionButton(
                                    `ood_actionbutton_die_${dieColor}_${colorIndex}`,
                                    `<div class="ood_die_result ood_oracle_die ood_oracle_die_${dieColor}"></div>`,
                                    handler
                                );
                                const actualDice = document.querySelectorAll(
                                    `${parentSelector} .ood_oracle_die_${dieColor}`
                                );
                                // ugly, but effective, way to ensure each die of the appropriate color
                                // has exactly 1 handler: remove them all then re-add, each time through
                                // the loop
                                actualDice.forEach((die) => {
                                    die.classList.add("ood_action_trigger");
                                    die.removeEventListener("click", dice[dieColor]);
                                    // can't use handler for listener in line below, as have to ensure
                                    // it's the same reference that will later be removed
                                    die.addEventListener("click", dice[dieColor]);
                                });
                                colorIndex++;
                            }
                            for (const oracleColor of args.unusedOracleColors) {
                                const handler = () => { console.log(`${oracleColor} oracle action`); }; //TODO
                                if (!oracles[oracleColor]) {
                                    oracles[oracleColor] = handler;
                                }
                                this.addActionButton(
                                    `ood_actionbutton_oracle_${oracleColor}`,
                                    `<div class="ood_card ood_card_oracle ood_card_oracle_${oracleColor}"></div>`,
                                    handler
                                );
                                const actualCards = document.querySelectorAll(
                                    `${parentSelector} .ood_oracle_section .ood_card_oracle_${oracleColor}`
                                );
                                actualCards.forEach((card) => {
                                    card.classList.add("ood_action_trigger");
                                    card.addEventListener("click", handler);
                                });
                            }
                            for (const god of args.godsOnTop) {
                                const handler = () => { console.log(`${god} god action`); }; //TODO
                                if (!gods[god]) {
                                    gods[god] = handler;
                                }
                                this.addActionButton(
                                    `ood_actionbutton_god_${god}`,
                                    `<div class="ood_wooden_piece ood_god ood_god_${god}"></div>`,
                                    handler
                                );
                                const pieceOnBoard = document.querySelector(
                                    `${parentSelector} .ood_god_${god}`
                                );
                                pieceOnBoard.classList.add("ood_action_trigger");
                                pieceOnBoard.addEventListener("click", handler);
                            }
                            //TODO: submit and undo buttons (x2) where appropriate. Probably put
                            //in separate methods as won't just be used here (actually, maybe they
                            //will - but separate methods still better!)
                            break;
                        }
                        case STATE_DIE_CHOSEN: {
                            // add many buttons (eventually):
                            // Want some way to visually show the chosen die - can't really do on a button
                            // as not interactive!
                            // Need (up to, some may not be accessible - deal with individually) one button
                            // for each of the 13 die actions.
                            // Plus (provided at least 1 favor possessed) a button to recolor.
                            this.addActionButton(
                                "ood_actionbutton_recolor",
                                _("Recolor die"),
                                () => {
                                    this.setClientState(STATE_RECOLOR_DIE, {
                                        descriptionmyturn: _("${you} must choose how to recolor this die")
                                    });
                                },
                                null, null, "gray"
                            );

                            this.addActionButton(
                                "ood_actionbutton_draworacle",
                                `<div class="ood_action_graphic ood_action_graphic_draw_oracle"></div>`,
                                () => { /*TODO*/ }
                            );

                            this.addActionButton(
                                "ood_actionbutton_takefavors",
                                `<div class="ood_action_graphic ood_action_graphic_favors"></div>`,
                                () => { /*TODO*/ }
                            );

                            // only possible if there is at least one face-down island tile
                            if (this.getAllCoords(`${ISLAND}_back`).length > 0) {
                                this.addActionButton(
                                    "ood_actionbutton_lookatislands",
                                    `<div class="ood_action_graphic ood_action_graphic_look_islands"></div>`,
                                    () => { /*TODO*/ }
                                );
                            }

                            this.addActionButton(
                                "ood_actionbutton_moveship",
                                `<div class="ood_action_graphic ood_action_graphic_move_ship"></div>`,
                                () => { /*TODO*/ }
                            );

                            // only display this if currently next to a monster
                            if (this.findAdjacent(this.player_id, `${MONSTER}_${this.clientStateArgs.dieChosen}`).length > 0) {
                                this.addActionButton(
                                    "ood_actionbutton_fightmonster",
                                    `<div class="ood_action_graphic ood_action_graphic_fight_monster"></div>`,
                                    () => { /*TODO*/ }
                                );
                            }

                            // only display this if currently next to a face-down island tile whose hex color
                            // matches the die
                            for (const {x, y} of this.findAdjacent(this.player_id, `${ISLAND}_back`)) {
                                const tile = document.getElementById(`ood_maphex_${x}_${y}`);
                                if (tile.classList.contains(`ood_maphex_color_${this.clientStateArgs.dieChosen}`)) {
                                    this.addActionButton(
                                        "ood_actionbutton_exploreisland",
                                        `<div class="ood_action_graphic ood_action_graphic_explore_island"></div>`,
                                        () => { /*TODO*/ }
                                    );
                                    break;                                    
                                }
                            }

                            // build shrine action - don't display if no shrines on the player board!
                            if (document.querySelectorAll(`#ood_playerboard_${this.player_id} .ood_shrine`).length > 0) {
                                // only display build shrine action player's ship is currently next to a face-up island
                                // on a hex whose clor matches the die
                                const faceUpIslands = [];
                                for (const letter of ALL_GREEK_LETTERS) {
                                    faceUpIslands.push(...this.findAdjacent(
                                        this.player_id,
                                        `${ISLAND}_${FRIENDLY_COLORS[this.gamedatas.players[this.player_id].color]}_${letter}`
                                    ))
                                }
                                for (const {x, y} of faceUpIslands) {
                                    const tile = document.getElementById(`ood_maphex_${x}_${y}`);
                                    if (tile.classList.contains(`ood_maphex_color_${this.clientStateArgs.dieChosen}`)) {
                                        // also not if that island already has a shrine on it!
                                        if (!tile.querySelector(".ood_shrine")) {
                                            this.addActionButton(
                                                "ood_actionbutton_buildshrine",
                                                `<div class="ood_action_graphic ood_action_graphic_build_shrine"></div>`,
                                                () => { /*TODO*/ }
                                            );
                                        }
                                    break;
                                    }
                                }
                            }

                            // only display this if currently next to an offering of the correct die color
                            //TODO: don't show if ship's storage is full
                            if (this.findAdjacent(
                                    this.player_id, `${OFFERING}_${this.clientStateArgs.dieChosen}`
                                ).length > 0) {
                                this.addActionButton(
                                    "ood_actionbutton_loadoffering",
                                    `<div class="ood_action_graphic ood_action_graphic_load_offering"></div>`,
                                    () => { /*TODO*/ }
                                );
                            }

                            // only display this if currently next to a temple of the correct die color
                            //TODO: don't show if no offering cube of the correct color is held in storage
                            if (this.findAdjacent(
                                    this.player_id, `${TEMPLE}_${this.clientStateArgs.dieChosen}`
                                ).length > 0) {
                                this.addActionButton(
                                    "ood_actionbutton_makeoffering",
                                    `<div class="ood_action_graphic ood_action_graphic_make_offering"></div>`,
                                    () => { /*TODO*/ }
                                );
                            }

                            // only display this if currently next to a statue of the correct die color
                            //TODO: don't show if ship's storage is full
                            if (this.findAdjacent(
                                    this.player_id, `${STATUE}_${this.clientStateArgs.dieChosen}`
                                ).length > 0) {
                                this.addActionButton(
                                    "ood_actionbutton_loadstatue",
                                    `<div class="ood_action_graphic ood_action_graphic_load_statue"></div>`,
                                    () => { /*TODO*/ }
                                );
                            }

                            // only show if next to a "statue_x_y_z" hex which includes the die color
                            //TODO: only show if carrying a statue of the appropriate color
                            const statueHexes = [];
                            for (const colorCombo of COLOR_STATUE_COMBINATIONS[this.clientStateArgs.dieChosen]) {
                                statueHexes.push(...this.findAdjacent(this.player_id, colorCombo, true))
                            }
                            if (statueHexes.length > 0) {
                                this.addActionButton(
                                    "ood_actionbutton_raisestatue",
                                    `<div class="ood_action_graphic ood_action_graphic_raise_statue"></div>`,
                                    () => { /*TODO*/ }
                                );
                            }

                            // only show if the player has at least one injury card of the die color
                            if (document.querySelector(
                                    `#ood_playerboard_${this.player_id} .ood_card_injury_${this.clientStateArgs.dieChosen}`
                                )) {
                                this.addActionButton(
                                    "ood_actionbutton_discardinjury",
                                    `<div class="ood_action_graphic ood_action_graphic_discard_injury"></div>`,
                                    () => { /*TODO*/ }
                                );
                            }

                            // don't show if the God of the corresponding color is at the top of the track
                            const godCounter = document.querySelector(
                                `#ood_playerboard_${this.player_id} .ood_god_${GOD_COLORS[this.clientStateArgs.dieChosen]}`
                            );
                            if (!godCounter.classList.contains("ood_god_position_6")) {
                                this.addActionButton(
                                    "ood_actionbutton_advancegod",
                                    `<div class="ood_action_graphic ood_action_graphic_advance_god"></div>`,
                                    () => { /*TODO*/ }
                                );
                            }

                            this.addActionButton(
                                "ood_actionbutton_canceldie",
                                _("Cancel die selection"),
                                () => {
                                    this.setClientState(STATE_PLAYER_TURN, {
                                        descriptionmyturn: _("${you} can select an oracle die or card, or god, to perform an action")
                                    });
                                },
                                null, null, "red"
                            );
                            break;
                        }
                        case STATE_RECOLOR_DIE: {
                            //TODO: use die color and number of favors (in original server args
                            //- possibly modified by subsequent client actions, but leave that for now)
                            //to display the color options and costs
                            break;
                        }
                        default:
                            break;
                    }
                }
            },

            ///////////////////////////////////////////////////
            //// Utility methods

            /*
             
                Here, you can defines some utility methods that you can use everywhere in your javascript
                script.
             
            */

            // wrapper for ajaxcall
            ajaxAction: function (action, args, handler) {
                if (!args) {
                    args = {};
                }
                // this allows to avoid rapid action clicking which can cause race condition on server
                args.lock = true;
                if (this.checkAction(action)) {
                    this.ajaxcall(
                        `/${this.game_name}/${this.game_name}/${action}.html`, args, this, () => { }, () => {
                            if (handler) {
                                handler();
                            }
                        }
                    );
                }
            },

            submitActions: function () {
                this.ajaxAction("submitActions", this.actionQueue, () => this.clearActions());
            },

            // shorthand function to update local storage with the latest value of the action queue
            saveActions: function () {
                localStorage.setItem(OOD_ACTION_QUEUE, JSON.stringify(this.actionQueue));
            },

            clearActions: function () {
                this.actionQueue = [];
                this.saveActions();
            },

            undoLast: function () {
                this.actionQueue.pop();
                this.saveActions();
                //TODO: update UI appropriately
            },

            undoAll: function () {
                this.clearActions();
                //TODO: update UI appropriately
            },

            // calculate CSS variables for screen width, to keep the layout responsive
            onScreenChange: function () {
                // need to delay to stop the map extending into the notifications area when shrinking
                // and then returning to full width
                setTimeout(() => {
                    let screenWidth = document.getElementById("game_play_area").clientWidth;
                    // take BGA zoom into account for small screens
                    if (screenWidth < 740) {
                        screenWidth = 740;
                    }
                    //TODO: allow user adjustment, and use local storage to recall value
                    const tileWidth = screenWidth / this.tilesInMapWidth;
                    // adjust map tile size to fit screen
                    document.documentElement.style.setProperty("--tile-width", `${tileWidth}px`);
                    // also adjust width of playerboards and the sections at its sides
                    const playerboardWidthRatio = 0.65 * Math.min(screenWidth, 1200) / 1200
                    document.documentElement.style.setProperty("--playerboard-ratio", playerboardWidthRatio);
                    const playerboardSideWidth = Math.min(110, screenWidth / 12);
                    document.documentElement.style.setProperty("--playerboard-side-width", playerboardSideWidth);
                }, 0);
            },

            // utility for placing a div inside another, with particular classes
            placeElement: function (parentId, ...childClasses) {
                const parent = document.getElementById(parentId);
                const child = document.createElement("div");
                child.classList.add(...childClasses);
                parent.appendChild(child);
                // return the child in case other styles/attributes etc are needed by calling code
                return child;
            },

            // general function for placing all tokens on map. Uses specific functions below
            placeMapTokens: function (tokensOnMap, greekLetters) {
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

                for (const { location_x, location_y, type, color, status } of tokensOnMap) {
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
                            this.placeIsland(location_x, location_y, color, status, greekLetters);
                            break;
                        }
                        default:
                            break;
                    }
                    addToCounts(location_x, location_y, type);
                }
            },

            // utilities for placing components on the map and player areas
            placeOffering: function (x, y, color, position, total) {
                const hexId = `ood_maphex_${x}_${y}`;
                const tokenDiv = this.placeElement(
                    hexId, "ood_wooden_piece", "ood_offering", `ood_offering_${color}`
                );
                // subtract pi/6 from the angle to compensate for the tile rotation
                const angle = 2 * Math.PI * position / total - Math.PI / 6;
                const top = 50 + Math.sin(angle) * 30;
                const left = 50 + Math.cos(angle) * 30;
                tokenDiv.style.top = `${top}%`;
                tokenDiv.style.left = `${left}%`;

            },

            placeMonster: function (x, y, color, position, total) {
                const hexId = `ood_maphex_${x}_${y}`;
                const tokenDiv = this.placeElement(
                    hexId, "ood_wooden_piece", "ood_monster", `ood_monster_${color}`
                );
                const midPoint = (total + 1) / 2;
                const offset = 1 + position - midPoint;
                const top = (total === 1) ? 50 : 50 + 20 * (offset / (total - 1));
                const left = top;
                tokenDiv.style.top = `${top}%`;
                tokenDiv.style.left = `${left}%`;
            },

            placeStatue: function (x, y, color, position) {
                const hexId = `ood_maphex_${x}_${y}`;
                const tokenDiv = this.placeElement(
                    hexId, "ood_wooden_piece", "ood_statue", `ood_statue_${color}`
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
            },

            placeIsland: function (x, y, color, status, greekLetterArray) {
                let islandDetails;
                if (Number(status) === -1) {
                    islandDetails = "back";
                } else {
                    islandDetails = `${color}_${greekLetterArray[status - 4]}`;
                }

                const hexId = `ood_maphex_${x}_${y}`;
                this.placeElement(
                    hexId, "ood_island_tile", `ood_island_${islandDetails}`, "ood_hex_center"
                );
            },

            // place Zeus in appropriate spot of the map. (Never changes so no parameters needed.)
            placeZeus: function () {
                const zeusHex = document.querySelector(".ood_maphex_zeus");
                this.placeElement(zeusHex.id, "ood_zeus_figure", "ood_hex_center");

            },

            // place temples (also in fixed positions depending only on the map)
            placeTemples: function () {
                ALL_COLORS.forEach((color) => {
                    const templeHex = document.querySelector(`.ood_maphex_temple.ood_maphex_color_${color}`);
                    this.placeElement(
                        templeHex.id,
                        "ood_wooden_piece",
                        "ood_temple",
                        `ood_temple_${color}`
                    );
                });
            },

            placePlayerDice(playerId, diceInfo) {
                for (const die of diceInfo) {
                    const { color, used } = die;
                    this.placeElement(
                        `ood_dice_spot_${used ? "center" : color}_${playerId}`,
                        "ood_die_result",
                        "ood_oracle_die",
                        `ood_oracle_die_${color}`
                    );
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
            },

            placeShrinesOnPlayerboard: function (playerId, numShrines) {
                for (let i = 1; i <= numShrines; i++) {
                    this.placeElement(
                        `ood_shrine_spot_${i}_${playerId}`,
                        "ood_wooden_piece",
                        "ood_shrine"
                    );
                }
            },

            placeGodTokens: function (playerId, playerInfo) {
                for (const god of ALL_GODS) {
                    const godValue = Number(playerInfo[god]);
                    this.placeElement(
                        `ood_god_column_${god}_${playerId}`,
                        "ood_wooden_piece",
                        "ood_god",
                        `ood_god_${god}`,
                        `ood_god_position_${godValue}`
                    );
                }
            },

            placeShield: function (playerId, playerColor, numShields) {
                const shieldSpotId = `ood_shield_spot_${playerId}`;
                this.placeElement(shieldSpotId, "ood_shield", `ood_shield_${playerColor}`);
                document.getElementById(shieldSpotId).classList.add(`ood_shield_pos_${numShields}`);
            },

            setupZeusTiles: function (playerId, playerColor, incompleteZeusTiles) {
                let nonWildOfferings = 0;
                let nonWildMonsters = 0;
                incompleteZeusTiles.forEach(({ originalId, type, details, complete }) => {
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
                    this.placeElement(
                        `ood_zeus_tile_spot_${position}_${playerId}`,
                        "ood_zeus_tile",
                        `ood_zeus_${playerColor}_${type}_${details}`
                    );
                });
            },

            placeAvailableOracleCards: function (playerId, playerCards, usedOracleCard) {
                if (playerCards) {
                    let oracleCards = [...playerCards];
                    if (usedOracleCard) {
                        const toRemove = oracleCards.indexOf(usedOracleCard);
                        oracleCards = oracleCards.filter((card, index) => index !== toRemove);
                    }
                    oracleCards.forEach((color, index) => {
                        const card = this.placeElement(
                            `ood_oracle_section_${playerId}`,
                            "ood_card",
                            "ood_card_oracle",
                            `ood_card_oracle_${color}`
                        );
                        card.style.right = `calc(var(--playerboard-side-width) * ${30 * index - 110}px / 110)`;
                    });
                }
            },

            placeInjuryCards: function (playerId, playerCards) {
                if (playerCards) {
                    playerCards.forEach((color, index) => {
                        const card = this.placeElement(
                            `ood_injury_section_${playerId}`,
                            "ood_card",
                            "ood_card_injury",
                            `ood_card_injury_${color}`
                        );
                        card.style.right = `calc(var(--playerboard-side-width) * ${37 * index - 110}px / 110)`;
                    });
                }
            },

            placeCompanionCards: function (playerId, playerCards) {
                if (playerCards) {
                    playerCards.forEach((cardId, index) => {
                        const card = this.placeElement(
                            `ood_companion_section_${playerId}`,
                            "ood_card",
                            "ood_card_companion",
                            `ood_card_companion_${cardId}`
                        );
                        card.style.right = `calc(var(--playerboard-side-width) * ${37 * index}px / 110)`;
                    });
                }
            },

            placeEquipmentCards: function (playerId, playerCards) {
                if (playerCards) {
                    playerCards.forEach((cardId, index) => {
                        const card = this.placeElement(
                            `ood_equipment_section_${playerId}`,
                            "ood_card",
                            "ood_card_equipment",
                            `ood_card_equipment_${cardId}`
                        );
                        card.style.left = `${50 * index}px`;
                        card.style.right = `calc(var(--playerboard-side-width) * ${50 * index}px / 110)`;
                    });
                }
            },

            // add cards to both player areas and decks/piles below. Also setup counters for deck/discard size.
            setupCardDisplay: function (cardData) {
                for (const cardType of [ORACLE, INJURY, EQUIPMENT]) {
                    const deckCounter = new ebg.counter();
                    deckCounter.create(`ood_${cardType}_deck_count`);
                    deckCounter.toValue(cardData[cardType].deck_size);
                    const deckContainerId = `ood_${cardType}_deck`;
                    if (cardData[cardType].deck_size > 0) {
                        this.placeElement(
                            deckContainerId, "ood_card", `ood_card_${cardType}`, `ood_card_${cardType}_back`
                        );
                    } else {
                        this.placeElement(deckContainerId, "ood_card_spacer");
                    }

                    const discardCounter = new ebg.counter();
                    discardCounter.create(`ood_${cardType}_discard_count`);
                    discardCounter.toValue(cardData[cardType].discard_size);
                    const { top_discard } = cardData[cardType];
                    const discardContainerId = `ood_${cardType}_discard`;
                    if (top_discard) {
                        this.placeElement(
                            discardContainerId,
                            "ood_card",
                            `ood_card_${cardType}`,
                            `ood_card_${cardType}_${top_discard}`
                        );
                    } else {
                        this.placeElement(discardContainerId, "ood_card_spacer");
                    }

                    this.counters[cardType] = { deck: deckCounter, discard: discardCounter };
                }
                const equipmentDisplayId = "ood_equipment_display";
                for (const equipmentCard in cardData.equipment.display) {
                    this.placeElement(
                        equipmentDisplayId, "ood_card", "ood_card_equipment", `ood_card_equipment_${equipmentCard}`
                    );
                }
            },

            // general map-related utilies:

            // checks if two given hexes are adjacent, via their coordinates
            areAdjacent: function({ x: x1, y: y1}, { x: x2, y: y2}) {
                switch (y2 - y1) {
                    case -1:
                        return [0, 1].includes(x2 - x1);
                    case 0:
                        return [-1, 1].includes(x2 - x1);
                    case 1:
                        return [-1, 0].includes(x2 - x1);
                    default:
                        return false;
                }
            },

            // gets the hex co-ords of all game items of a given type (eg monster, statue, etc.), as an array of
            // {x, y} co-ord objects
            getAllCoords: function(type) {
                const items = document.querySelectorAll(`.ood_${type}`);
                return [...items].map(item => {
                    const hexId = item.parentElement.id;
                    // id is of the form ood_maphex_x_y
                    const parts = hexId.split("_");
                    return { x: parts[2], y: parts[3] };
                });
            },

            // variant of the above - this time finds coords for map hexes where the hex itself has the
            // specific type
            getCoordsOfAllHexes: function(type) {
                const mapHexes = document.querySelectorAll(`.ood_maphex_${type}`);
                return [...mapHexes].map(mapHex => {
                    const hexId = mapHex.id;
                    // id is of the form ood_maphex_x_y
                    const parts = hexId.split("_");
                    return { x: parts[2], y: parts[3] };
                });
            },

            // gets the ship location of a given player - given by player ID
            getShipLocation: function(playerId) {
                const playerColor = this.gamedatas.players[playerId].color;
                const shipToken = document.querySelector(`.ood_ship_${playerColor}`);
                const idParts = shipToken.parentElement.id.split("_");
                return { x: idParts[2], y: idParts[3] };
            },

            // combines the above functions to get an array - possibly empty - of all coords
            // containing a type of object on the map that are adjacent to the player's ship
            findAdjacent: function(playerId, type, isMapHex = false) {
                const shipLocation = this.getShipLocation(playerId);
                const coords = isMapHex ? this.getCoordsOfAllHexes(type) : this.getAllCoords(type);
                return coords.filter(tokenLocation => this.areAdjacent(shipLocation, tokenLocation));
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
