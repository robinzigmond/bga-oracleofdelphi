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
const SHRINE = "shrine";

const RED = "red";
const YELLOW = "yellow";
const GREEN = "green";
const BLUE = "blue";
const PINK = "pink";
const BLACK = "black";

const WILD = "wild";

// note: the order of this array matters, as they're in the order of the player circle which
// is used for recoloring dice.
const ALL_COLORS = [RED, BLACK, PINK, BLUE, YELLOW, GREEN];

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
const MAX_GOD_VALUE = 6;

const SIGMA = "sigma";
const PHI = "phi";
const PSI = "psi";
const OMEGA = "omega";

//const ALL_GREEK_LETTERS = [SIGMA, PHI, PSI, OMEGA];

const ORACLE = "oracle";
const INJURY = "injury";
const COMPANION = "companion";
const EQUIPMENT = "equipment";

//const OOD_ACTION_QUEUE = "bga_ood_actions";

// state names. Note: must match "name" attribute of state in states.inc.php
const STATE_PLAYER_TURN = "playerTurn";

// client states
const STATE_DIE_CHOSEN = "client_dieChosen";
const STATE_RECOLOR_DIE = "client_recolorDie";

// action types
const RECOLOR_DIE = "recolor_die";
const DRAW_ORACLE = "draw_oracle";

const MOVING_PIECE_CLASS = "ood_moving_piece";
const DIE_CLASS_PREFIX = "ood_die_in_spot_";

const PLAYERBOARD_BASE_WIDTH_RATIO = 0.65;
const DIE_BASE_WIDTH = 44;
const DIE_BASE_HEIGHT = 47;

// miscellaneous consts for the UI
const TEMP_DIE_ID = "temp_for_card";

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

                // current game data, taken from the server in setup method but updated on the client side
                this.currentGameData = {};

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

            setup: function (gamedatas, fromUndo = false) {
                // take local copy of gamedatas to keep in sync with client actions
                // (taking a reference shouldn't matter despite mutating this - I don't believe gamedatas is
                // a reference to anything needed elsewhere?)
                this.currentGameData = gamedatas;

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
                        const usedColor = gamedatas.cards.oracle.hands[playerId].find(({ id }) => id == info.oracle_used).card;

                        const card = this.placeElement(
                            `ood_oracle_used_section_${playerId}`,
                            "ood_card",
                            "ood_card_oracle",
                            `ood_card_oracle_${usedColor}`
                        );
                        card.id = `card_id_${info.oracle_used}`;
                    }

                    this.placeAvailableOracleCards(
                        playerId, gamedatas.cards.oracle.hands[playerId], info.oracle_used
                    );
                    this.placeInjuryCards(playerId, gamedatas.cards.injury.hands[playerId]);
                    this.placeCompanionCards(playerId, gamedatas.cards.companion.hands[playerId]);
                    this.placeEquipmentCards(playerId, gamedatas.cards.equipment.hands[playerId]);
                }

                if (!fromUndo) {
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

                    this.actionQueue = [];
                }
            },

            // removes everything placed in the setup method. Used to clear everything before
            // running setup again when using client-side undo
            undoSetup: function() {
                const zeusFigure = document.querySelector("#ood_map .ood_zeus_figure");
                zeusFigure.remove();

                const templePieces = document.querySelectorAll("#ood_map .ood_temple");
                templePieces.forEach(temple => temple.remove());

                //TODO: will also need to remove offering cubes on the player's ship tile
                const offeringCubes = document.querySelectorAll("#ood_map .ood_offering");
                offeringCubes.forEach(offering => offering.remove());

                //TODO: will also need to remove defeated monsters by a player's board
                const monsterPieces = document.querySelectorAll("#ood_map .ood_monster");
                monsterPieces.forEach(monster => monster.remove());

                //TODO: will also need to remove statues on the player's ship tile
                const statuePieces = document.querySelectorAll("ood_map .ood_statue");
                statuePieces.forEach(statue => statue.remove());

                const islandTiles = document.querySelectorAll(".ood_island_tile");
                islandTiles.forEach(island => island.remove());

                for (const cardType of [ORACLE, INJURY, EQUIPMENT]) {
                    const deckContainerId = `ood_${cardType}_deck`;
                    document.getElementById(deckContainerId)
                            .querySelectorAll(`.ood_card_${cardType}, .ood_card_spacer`)
                            .forEach(elt => elt.remove());


                    const discardContainerId = `ood_${cardType}_discard`;
                    document.getElementById(discardContainerId)
                            .querySelectorAll(`.ood_card_${cardType}, .ood_card_spacer`)
                            .forEach(elt => elt.remove());
                    document.getElementById(discardContainerId)
                            .querySelectorAll(".ood_card_equipment")
                            .forEach(elt => elt.remove());
                }

                const shipTiles = document.querySelectorAll(".ood_ship_tile");
                shipTiles.forEach(shipTile => shipTile.remove());

                const oracleDice = document.querySelectorAll(".ood_playerboard .ood_die_result.ood_oracle_die");
                oracleDice.forEach(die => die.remove());

                const shrinePieces = document.querySelectorAll(".ood_playerboard .ood_shrine");
                shrinePieces.forEach(shrine => shrine.remove());

                const godTokens = document.querySelectorAll(".ood_playerboard .ood_god");
                godTokens.forEach(god => god.remove());

                const shieldTokens = document.querySelectorAll(".ood_playerboard .ood_shield");
                shieldTokens.forEach(shield => shield.remove());

                const shipTokens = document.querySelectorAll("#ood_map .ood_wooden_piece.ood_ship");
                shipTokens.forEach(ship => ship.remove());

                const zeusTiles = document.querySelectorAll(".ood_playerboard .ood_zeus_tile");
                zeusTiles.forEach(tile => tile.remove());

                const oracleCards = document.querySelectorAll(".ood_playerboard .ood_card_oracle");
                oracleCards.forEach(card => card.remove());

                const injuryCards = document.querySelectorAll(".ood_playerboard .ood_card_injury");
                injuryCards.forEach(card => card.remove());

                const companionCards = document.querySelectorAll(".ood_playerboard .ood_card_companion");
                companionCards.forEach(card => card.remove());

                const equipmentCards = document.querySelectorAll(".ood_playerboard .ood_card_equipment");
                equipmentCards.forEach(card => card.remove());
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

                        const actualDice = document.querySelectorAll(".ood_oracle_die.ood_action_trigger");

                        actualDice.forEach((die) => {
                            const color = Array.from(die.classList)
                                .find(className => className.startsWith("ood_oracle_die_"))
                                .slice("ood_oracle_die_".length);
                            die.removeEventListener("click", dice[color]);
                        });

                        const actualOracles = document.querySelectorAll(".ood_card_oracle.ood_action_trigger");

                        actualOracles.forEach((card) => {
                            const color = Array.from(card.classList)
                                .find(className => className.startsWith("ood_card_oracle_"))
                                .slice("ood_card_oracle_".length);
                            card.removeEventListener("click", oracles[color]);
                        });

                        //as in above comment!
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
                            let colorIndex = 0;
                            const { dice, oracles, gods } = this.handlers;
                            const unusedDice = this.currentGameData.players[this.player_id].dice
                                .filter(({ used }) => !used)
                                .map(({ color }) => color);
                    
                            for (const dieColor of unusedDice) {
                                const handler = () => {
                                    // need to reset flag here
                                    this.clientStateArgs.cardNotDie = false;
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

                                // get all dice of the selected color and add the click handler for each
                                const actualDice = this.currentGameData.players[this.player_id].dice
                                    .filter(({ used, color }) => !used && color === dieColor)
                                    .map(({ id }) => document.getElementById(`die_id_${id}`));

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

                            const usedOracle = this.currentGameData.players[this.player_id].oracle_used;
                            const unusedOracleColors = usedOracle
                                ? []
                                : (
                                    this.currentGameData.cards.oracle.hands[this.player_id]
                                    // remove duplicates from array
                                    ? Array.from(new Set(
                                        this.currentGameData.cards.oracle.hands[this.player_id]
                                            .map(({ card }) => card)
                                        ))
                                    : []
                                );

                            for (const oracleColor of unusedOracleColors) {
                                // just use previously setup handler as very little changes.
                                // Repeating the code though as dice[oracleColor] won't exist if there are no
                                // unused dice of that color.
                                const handler = () => {
                                    this.clientStateArgs.dieChosen = oracleColor;
                                    // set flag so we can always know we're dealing with a card rather than a die
                                    this.clientStateArgs.cardNotDie = true;
                                    this.setClientState(STATE_DIE_CHOSEN, {
                                        descriptionmyturn: _("${you} must choose an action with this card")
                                    });
                                };

                                if (!oracles[oracleColor]) {
                                    oracles[oracleColor] = handler;
                                }
                                this.addActionButton(
                                    `ood_actionbutton_oracle_${oracleColor}`,
                                    `<div class="ood_card ood_card_oracle ood_card_oracle_${oracleColor}"></div>`,
                                    handler
                                );

                                const actualCards = this.currentGameData.cards.oracle.hands[this.player_id]
                                    .map(({ id }) => document.getElementById(`card_id_${id}`));

                                actualCards.forEach((card) => {
                                    card.classList.add("ood_action_trigger");
                                    card.addEventListener("click", handler);
                                });
                            }

                            const godsOnTop = ALL_GODS.filter(
                                god => Number(this.currentGameData.players[this.player_id][god]) === MAX_GOD_VALUE
                            );
                            for (const god of godsOnTop) {
                                const handler = () => { console.log(`${god} god action`); }; //TODO
                                if (!gods[god]) {
                                    gods[god] = handler;
                                }
                                this.addActionButton(
                                    `ood_actionbutton_god_${god}`,
                                    `<div class="ood_wooden_piece ood_god ood_god_${god}"></div>`,
                                    handler
                                );
                                // (OK to use a DOM query here - the god pieces don't have database IDs
                                // and the token will never leave the player board. It can be moved without
                                // slide functions, just by changing the CSS class and using transitions.)
                                const pieceOnBoard = document.querySelector(
                                    `#ood_playerboard_${this.player_id} .ood_god_${god}`
                                );
                                pieceOnBoard.classList.add("ood_action_trigger");
                                pieceOnBoard.addEventListener("click", handler);
                            }
                            if (this.actionQueue.length > 0) {
                                this.addActionButton(
                                    "ood_actionbutton_undo_last",
                                    _("Undo last action"),
                                    () => this.undoLast(),
                                    null, null, "red"
                                );
                                if (this.actionQueue.length > 1) {
                                    this.addActionButton(
                                        "ood_actionbutton_undo_all",
                                        _("Undo turn"),
                                        () => this.undoAll(),
                                        null, null, "red"
                                    );
                                }
                            }
                            // TODO: submit button - only if all 3 dice have been used
                            break;
                        }
                        case STATE_DIE_CHOSEN: {
                            const { dieChosen, cardNotDie } = this.clientStateArgs;
                            // add many buttons (eventually):
                            // TODO: Want some way to visually show the chosen die - can't really do on a button
                            // as not interactive!
                            // Some sort of visual of the die?
                            // Particularly important with recoloring (which also applies to cards!), should indicate
                            // in some sort of graphic both the "original piece" (die or card) and (if different) the
                            // die it now represents! [but probably not if we're going to physically transform via a
                            // pseudo-notification]

                            // Need (up to, some may not be accessible - deal with individually) one button
                            // for each of the 13 die actions.
                            // Plus (provided at least 1 favor possessed) a button to recolor.
                            //this isn't working when just recoloring!!
                            if (Number(this.currentGameData.players[this.player_id].favors) > 0) {
                                this.addActionButton(
                                    "ood_actionbutton_recolor",
                                    _("Recolor"),
                                    () => {
                                        this.setClientState(STATE_RECOLOR_DIE, {
                                            descriptionmyturn: _("${you} must choose how to recolor this die")
                                        });
                                    },
                                    null, null, "gray"
                                );
                            }

                            this.addActionButton(
                                "ood_actionbutton_draworacle",
                                `<div class="ood_action_graphic ood_action_graphic_draw_oracle"></div>`,
                                () => {
                                    this.doAction({
                                        type: DRAW_ORACLE,
                                        die: dieChosen,
                                        isCard: !!cardNotDie,
                                        cannotUndo: true
                                    });
                                }
                            );

                            this.addActionButton(
                                "ood_actionbutton_takefavors",
                                `<div class="ood_action_graphic ood_action_graphic_favors"></div>`,
                                () => { /*TODO*/ }
                            );

                            // only possible if there is at least one face-down island tile
                            if (this.getAllCoords({ type: ISLAND, status: -1 }).length > 0) {
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
                            if (this.findAdjacent(this.player_id, { type: MONSTER, color: dieChosen }).length > 0) {
                                this.addActionButton(
                                    "ood_actionbutton_fightmonster",
                                    `<div class="ood_action_graphic ood_action_graphic_fight_monster"></div>`,
                                    () => { /*TODO*/ }
                                );
                            }

                            // only display this if currently next to a face-down island tile whose hex color
                            // matches the die
                            for (const {x, y} of this.findAdjacent(this.player_id, { type: ISLAND, status: -1 })) {
                                const islandTile = this.currentGameData.mapData.find(
                                    ({ x_coord, y_coord }) => Number(x_coord) === Number(x) && Number(y_coord) === Number(y)
                                );
                                if (!islandTile) {
                                    console.error("found an adjacent island token that has no corresponding map tile???");
                                }
                                if (islandTile.type === ISLAND) {
                                    console.error("island token not on island tile?? Investigate!");
                                }
                                if (tile.color === dieChosen) {
                                    this.addActionButton(
                                        "ood_actionbutton_exploreisland",
                                        `<div class="ood_action_graphic ood_action_graphic_explore_island"></div>`,
                                        () => { /*TODO*/ }
                                    );
                                    break;                                    
                                }
                            }

                            // build shrine action - don't display if no shrines on the player board!
                            if (this.currentGameData.players[this.player_id].shrines > 0) {
                                // only display build shrine action player's ship is currently next to a face-up island
                                // on a hex whose color matches the die
                                const faceUpIslands = [];
                                faceUpIslands.push(...this.findAdjacent(
                                    this.player_id,
                                    {
                                        type: ISLAND,
                                        color: FRIENDLY_COLORS[this.gamedatas.players[this.player_id].color]
                                    }
                                ))
                                for (const {x, y} of faceUpIslands) {
                                    const islandMapTile = this.currentGameData.mapData.find(({ x_coord, y_coord}) => {
                                        return Number(x_coord) === Number(x) && Number(y_coord) === Number(y);
                                    });
                                    if (!islandMapTile) {
                                        console.error("no island on map where island token is??");
                                    }
                                    if (islandTile.color === dieChosen) {
                                        // also not if that island already has a shrine on it!
                                        const hasShrine = this.currentGameData.tokensOnMap.find(
                                            ({ type, location_x, location_y }) => {
                                                return location_x === x && location_y === y && type === SHRINE;
                                            }
                                        );
                                        if (!hasShrine) {
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
                                    this.player_id, { type: OFFERING, color: dieChosen }
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
                                    this.player_id, { type: TEMPLE, color: dieChosen }
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
                                    this.player_id, { type: STATUE, color: dieChosen }
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
                            for (const colorCombo of COLOR_STATUE_COMBINATIONS[dieChosen]) {
                                statueHexes.push(...this.findAdjacent(this.player_id, { type: colorCombo }, true))
                            }
                            if (statueHexes.length > 0) {
                                this.addActionButton(
                                    "ood_actionbutton_raisestatue",
                                    `<div class="ood_action_graphic ood_action_graphic_raise_statue"></div>`,
                                    () => { /*TODO*/ }
                                );
                            }

                            // only show if the player has at least one injury card of the die color
                            if (this.currentGameData.cards.injury.hands[this.player_id]
                                    .find(({ card: color }) => color === dieChosen)) {
                                this.addActionButton(
                                    "ood_actionbutton_discardinjury",
                                    `<div class="ood_action_graphic ood_action_graphic_discard_injury"></div>`,
                                    () => { /*TODO*/ }
                                );
                            }

                            // don't show if the God of the corresponding color is at the top of the track
                            if (Number(this.currentGameData.players[this.player_id][GOD_COLORS[dieChosen]]) < MAX_GOD_VALUE) {
                                this.addActionButton(
                                    "ood_actionbutton_advancegod",
                                    `<div class="ood_action_graphic ood_action_graphic_advance_god"></div>`,
                                    () => { /*TODO*/ }
                                );
                            }

                            this.addActionButton(
                                "ood_actionbutton_canceldie",
                                this.clientStateArgs.cardNotDie ? _("Cancel card selection") : _("Cancel die selection"),
                                () => { this.resetClientState(); },
                                null, null, "red"
                            );
                            break;
                        }
                        case STATE_RECOLOR_DIE: {
                            const { dieChosen, cardNotDie } = this.clientStateArgs;
                            const numFavors = Number(this.currentGameData.players[this.player_id].favors);
                            const colorIndex = ALL_COLORS.indexOf(dieChosen);
                            //TODO: this will change if one of various different ships is held.
                            for (let i = 1; i <= Math.min(numFavors, 5); i++) {
                                const newColor = ALL_COLORS[(colorIndex + i) % ALL_COLORS.length];
                                const actionButtonContent = `${i} x <div class="ood_favor"></div> => <div class="ood_die_result ood_oracle_die ood_oracle_die_${newColor}"></div>`;
                                this.addActionButton(
                                    `ood_actionbutton_recolor_${i}`,
                                    actionButtonContent,
                                    () => {
                                        // need to update client-state args
                                        this.clientStateArgs.dieChosen = newColor;
                                        this.clientStateArgs.cardNotDie = false;
                                        // note that this must be before the setClientState call, as doAction sets
                                        // off the client-side notification that updates the client gamestate, and
                                        // that is read when moving to the new state to (eg) decide whether to show
                                        // the button to recolor again
                                        this.doAction({
                                            type: RECOLOR_DIE,
                                            original: dieChosen,
                                            favorsSpent: i,
                                            isCard: !!cardNotDie,
                                            cannotUndo: false
                                        });
                                        this.setClientState(STATE_DIE_CHOSEN, {
                                            descriptionmyturn: _("${you} must choose an action with this die")
                                        });
                                        //TODO: OTHER THINGS TO THINK ABOUT! -
                                        //should probably remove option to recolor again?
                                    }
                                );
                            }
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

            /* override (borrowed from studio cookbook, but overwritten with key parts of the actual function
            in BGA source!) */
            attachToNewParent: function (mobile_in, new_parent_in, relation) {
                const mobile = $(mobile_in);
                const new_parent = $(new_parent_in);
    
                const src = dojo.position(mobile);
                const s = this.getAbsRotationAngle(mobile);
                const o = this.disable3dIfNeeded();
                dojo.place(mobile, new_parent, relation);
                const l = dojo.position(mobile);
                let d = dojo.style(mobile, "left");
                let c = dojo.style(mobile, "top");
                const h = this.getAbsRotationAngle(mobile);
                const u = this.getAbsRotationAngle(new_parent_in);
                const p = {
                  x: src.x - l.x + (src.w - l.w) / 2,
                  y: src.y - l.y + (src.h - l.h) / 2
                };
                const m = this.vector_rotate(p, u);
                d += m.x;
                c += m.y;
                dojo.style(mobile, "top", c + "px");
                dojo.style(mobile, "left", d + "px");
                h != s && this.rotateInstantDelta(mobile, s - h);
                this.enable3dIfNeeded(o);
                return mobile;
            },

            /* @Override */
            format_string_recursive: function format_string_recursive(log, args) {
                if (log && args && !args.processed) {
                    args.processed = true;

                    const { token_used, card_gained, new_die } = args;

                    const getTokenElement = (tokenString) => {
                        const [color, type] = tokenString.split(" ");
                        switch (type) {
                            case "die":
                                return `<div class="ood_log_entry_token ood_die_result ood_oracle_die ood_oracle_die_${color}"></div>`;
                            case "card":
                                return `<div class="ood_log_entry_token ood_card ood_card_oracle ood_card_oracle_${color}"></div>`;
                            default:
                                console.error(`unexpected token type in notification message: ${type}`);
                        }
                    };
                    if (token_used) {
                        args.token_used = getTokenElement(token_used);
                    }
                    if (card_gained) {
                        args.card_gained = getTokenElement(card_gained);
                    }
                    if (new_die) {
                        args.new_die = getTokenElement(new_die);
                    }
                }

                return this.inherited({callee: format_string_recursive}, arguments);
            },

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
                this.ajaxAction("submitActions",
                    { actions: JSON.stringify(this.actionQueue.map(({ action }) => action)) },
                    () => {
                        this.clearActions();
                        this.resetClientState();
                    }
                );
            },

            // called when an action is selected by the player in the UI. Handles on either client
            // or server side, depending on whether it's undoable or not
            doAction: function(action) {
                const { cannotUndo, ...restOfAction } = action;
                this.actionQueue.push({ action: restOfAction, gameState: JSON.parse(JSON.stringify(this.currentGameData)) });
                if (cannotUndo) {
                    //TODO: confirmation/warning to ensure player doesn't submit actions to the server
                    //without being aware of it
                    this.submitActions();
                } else {
                    this.playActionNotification(action);
                }
            },

            clearActions: function () {
                this.actionQueue = [];
            },

            undoToState: function(gameState) {
                this.currentGameData = gameState;
                this.undoSetup();
                this.setup(gameState, true);
                // reset state to basic "player turn" state to choose an action
                // (this should usually be right, deal with any cases later where it may not be!)
                this.resetClientState();
            },

            undoLast: function () {
                const { gameState } = this.actionQueue.pop();
                this.undoToState(gameState);
            },

            undoAll: function () {
                if (this.actionQueue.length) {
                    const { gameState } = this.actionQueue[0];
                    this.clearActions();
                    this.undoToState(gameState);
                }
            },

            playActionNotification: function(action) {
                const { type, cannotUndo, ...otherArgs } = action;
                const notifName = `notif_${type}`;
                const notifArgs = { ...otherArgs, playerId: this.player_id };
                this[notifName]({ args: notifArgs }, true);
            },

            // used after a player action is taken or cancelled, to reset the client state to the default one
            resetClientState: function() {
                this.setClientState(STATE_PLAYER_TURN, {
                    descriptionmyturn: _("${you} can select an oracle die or card, or god, to perform an action")
                });
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
                    const playerboardWidthRatio = PLAYERBOARD_BASE_WIDTH_RATIO * Math.min(screenWidth, 1200) / 1200
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

                for (const { id, location_x, location_y, type, color, status } of tokensOnMap) {
                    const position = getCount(location_x, location_y, type);
                    const total = getTotal(location_x, location_y, type);
                    switch (type) {
                        case OFFERING:
                            this.placeOffering(id, location_x, location_y, color, position, total);
                            break;
                        case MONSTER:
                            this.placeMonster(id, location_x, location_y, color, position, total);
                            break;
                        case STATUE:
                            this.placeStatue(id, location_x, location_y, color, position);
                            break;
                        case ISLAND: {
                            this.placeIsland(id, location_x, location_y, color, status, greekLetters);
                            break;
                        }
                        default:
                            break;
                    }
                    addToCounts(location_x, location_y, type);
                }
            },

            // utilities for placing components on the map and player areas
            placeOffering: function (id, x, y, color, position, total) {
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
                tokenDiv.id = `token_id_${id}`;
            },

            placeMonster: function (id, x, y, color, position, total) {
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
                tokenDiv.id = `token_id_${id}`;
            },

            placeStatue: function (id, x, y, color, position) {
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
                tokenDiv.id = `token_id_${id}`;
            },

            placeIsland: function (id, x, y, color, status, greekLetterArray) {
                let islandDetails;
                if (Number(status) === -1) {
                    islandDetails = "back";
                } else {
                    islandDetails = `${color}_${greekLetterArray[status - 4]}`;
                }

                const hexId = `ood_maphex_${x}_${y}`;
                const tokenDiv = this.placeElement(
                    hexId, "ood_island_tile", `ood_island_${islandDetails}`, "ood_hex_center"
                );
                tokenDiv.id = `token_id_${id}`;
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
                    const { color, used, id } = die;
                    const dieDiv = this.placeElement(
                        `ood_dice_spot_${used ? "center" : color}_${playerId}`,
                        "ood_die_result",
                        "ood_oracle_die",
                        `ood_oracle_die_${color}`
                    );
                    dieDiv.id = `die_id_${id}`;
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
                incompleteZeusTiles.forEach(({ id, originalId, type, details, complete }) => {
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
                    const tile = this.placeElement(
                        `ood_zeus_tile_spot_${position}_${playerId}`,
                        "ood_zeus_tile",
                        `ood_zeus_${playerColor}_${type}_${details}`
                    );
                    tile.id = `token_id_${id}`;
                });
            },

            placeAvailableOracleCards: function (playerId, playerCards, usedOracleCard) {
                if (playerCards) {
                    let oracleCards = [...playerCards];
                    if (usedOracleCard) {
                        const toRemove = oracleCards.findIndex(({ id }) => id == usedOracleCard);
                        oracleCards = oracleCards.filter((card, index) => index !== toRemove);
                    }
                    oracleCards.forEach(({ id, card: color }, index) => {
                        const card = this.placeElement(
                            `ood_oracle_section_${playerId}`,
                            "ood_card",
                            "ood_card_oracle",
                            `ood_card_oracle_${color}`
                        );
                        card.id = `card_id_${id}`;
                        card.style.right = `calc(var(--playerboard-side-width) * ${30 * index - 110}px / 110)`;
                    });
                }
            },

            placeInjuryCards: function (playerId, playerCards) {
                if (playerCards) {
                    playerCards.forEach(({ id, card: color }, index) => {
                        const card = this.placeElement(
                            `ood_injury_section_${playerId}`,
                            "ood_card",
                            "ood_card_injury",
                            `ood_card_injury_${color}`
                        );
                        card.id = `card_id_${id}`;
                        card.style.right = `calc(var(--playerboard-side-width) * ${37 * index - 110}px / 110)`;
                    });
                }
            },

            placeCompanionCards: function (playerId, playerCards) {
                if (playerCards) {
                    playerCards.forEach(({ id, card: cardId }, index) => {
                        const card = this.placeElement(
                            `ood_companion_section_${playerId}`,
                            "ood_card",
                            "ood_card_companion",
                            `ood_card_companion_${cardId}`
                        );
                        card.id = `card_id_${id}`;
                        card.style.right = `calc(var(--playerboard-side-width) * ${37 * index}px / 110)`;
                    });
                }
            },

            placeEquipmentCards: function (playerId, playerCards) {
                if (playerCards) {
                    playerCards.forEach(({ id, card: cardId }, index) => {
                        const card = this.placeElement(
                            `ood_equipment_section_${playerId}`,
                            "ood_card",
                            "ood_card_equipment",
                            `ood_card_equipment_${cardId}`
                        );
                        card.id = `card_id_${id}`;                        
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

            // gets the hex co-ords of all game tokens of a given type (eg monster, statue, etc.), as an array of
            // {x, y} co-ord objects and optionally of a particular type and/or color.
            getAllCoords: function({ type, status, color }) {
                return this.currentGameData.tokensOnMap.filter(
                    ({ type: foundType, status: foundStatus, color: foundColor }) => {
                        let result = type === foundType;
                        if (status !== undefined) {
                            result = result && (Number(status) === Number(foundStatus));
                        }
                        if (color !== undefined) {
                            result = result && (color === foundColor);
                        }
                        return result;
                }).map(({ location_x: x, location_y: y }) => ({ x, y}));
            },

            // like the above, but rather than the tokens currently on the map this finds all hexes on the map
            // where the tile itself is of a particular type
            getCoordsOfAllHexes: function(type) {
                return this.currentGameData.mapData
                        .filter(({ type: actualType }) => actualType === type)
                        .map(({ "x_coord": x, "y_coord": y }) => ({ x: Number(x), y: Number(y) }));
            },

            // gets the ship location of a given player - given by player ID
            getShipLocation: function(playerId) {
                const { ship_location_x: x, ship_location_y: y } = this.currentGameData.players[playerId];
                return { x, y };
            },

            // combines the above functions to get an array - possibly empty - of all coords
            // containing a type of object on the map that are adjacent to the player's ship
            findAdjacent: function(playerId, {type, status, color}, isMapHex = false) {
                const shipLocation = this.getShipLocation(playerId);
                const coords = isMapHex ? this.getCoordsOfAllHexes(type) : this.getAllCoords({type, status, color});
                return coords.filter(tokenLocation => this.areAdjacent(shipLocation, tokenLocation));
            },

            // utils for manipulating CSS classes of dice

            // returns the "count class" ("ood_die_in_spot_x_of_y") from a list of CSS classes
            // (can return undefined if there is no such class)
            getCountClass: function(classList) {
                return Array.from(classList).find(className => className.startsWith(DIE_CLASS_PREFIX));
            },

            removeCountClass: function(dieElement) {
                const { classList } = dieElement;
                const countClass = this.getCountClass(classList);
                if (countClass) {
                    classList.remove(countClass);
                }
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
                //TODO - same pattern will likely be used for many different notifs, make a global list of
                //the and repeat the same subscription logic
                dojo.subscribe("recolor_die", this, "notif_recolor_die");
                dojo.subscribe("draw_oracle", this, "notif_draw_oracle");

                /*const clientOnlyActions = ["recolor_die"];

                clientOnlyActions.forEach(action => {
                    this.notifqueue.setIgnoreNotificationCheck(action, (notif) => (notif.args.playerId == this.player_id));
                });*/
            },

            notif_recolor_die: function(notif, fromClient) {
                const { favorsSpent, isCard, original, playerId, new_id } = notif.args;

                // work out new color
                const colorIndex = ALL_COLORS.indexOf(original);
                const newColor = ALL_COLORS[(colorIndex + favorsSpent) % ALL_COLORS.length];

                // must ensure we update any temporary die IDs before we do an early return, or things break!
                if (new_id) {
                    const tempDie = document.getElementById(`ood_dice_spot_${newColor}_${playerId}`).querySelector(`[id^="die_id_${TEMP_DIE_ID}_"]`);
                    // for non-active players, the temp die doesn't actually exist yet (it's created below!)
                    if (tempDie) {
                        tempDie.id = `die_id_${new_id}`;
                        // now do the same for the internal game data!
                        // Again this doesn't work or happen for the non-active player, so it's in the same if
                        // block.
                        const previousDice = this.currentGameData.players[playerId].dice;
                        const withTempId = previousDice.find(die => die.id.startsWith(TEMP_DIE_ID));
                        withTempId.id = new_id;
                    }
                }

                // client-only action, so don't do animation for the active player (it's already been done!).
                // (Do this instead of setIgnoreNotifactionCheck so that the message still appears in the log.)
                if (!fromClient && playerId == this.player_id) {
                    return;
                }

                const favorDiv = document.querySelector(`#ood_playerboard_${playerId} .ood_favor`).parentElement;
                const playerboardTargetId = `overall_player_board_${playerId}`;
                for (let i = 0; i < favorsSpent; i++) {
                    this.slideTemporaryObject('<div class="ood_favor"></div>', favorDiv, favorDiv, playerboardTargetId, 500, 500 * i);
                }
                // update counter on player board
                this.counters.favors[playerId].incValue(-1 * favorsSpent);

                let cardId;
                let newDieId;

                if (isCard) {
                    // move card to used oracle position as it can't now be used again
                    const card = this.currentGameData.cards.oracle.hands[playerId].find(({ card }) => card === original);
                    if (!card) {
                        console.error("couldn't find oracle card that we know the player has?");
                    }
                    cardId = Number(card.id);
                    this.spend_card_animation(playerId, cardId);
                    
                    // add die to appropriate slot
                    //TODO: make it "slide in" from somewhere, rather than simply appear
                    //[leave for later, as this will need a complete change of approach!]
                    // first need to temporarily remove all existing dice
                    const oracleDice = document.querySelectorAll(`#ood_playerboard_${playerId} .ood_die_result.ood_oracle_die`);
                    oracleDice.forEach(die => die.remove());
                    // then restore them, with the "new" die
                    const previousDice = this.currentGameData.players[playerId].dice;
                    newDieId = new_id || `${TEMP_DIE_ID}_${cardId}`;
                    const newDie = { color: newColor, used: false, id: newDieId };
                    //TODO: this won't work well visually in the VERY rare case where a card is converted to a colour
                    //where the player already has 3 dice. Very unlikely a player will actually want to do this, so
                    //not a priority to fix, but should look at at some point
                    this.placePlayerDice(playerId, [...previousDice, newDie]);
                } else {
                    //TODO: would be better to actually move the die with some sort of animation, also changing the color.
                    //Tricky to do so leave till the game logic is mostly complete.
                    const oracleDice = document.querySelectorAll(`#ood_playerboard_${playerId} .ood_die_result.ood_oracle_die`);
                    oracleDice.forEach(die => die.remove());
                    const newDice = this.currentGameData.players[playerId].dice;
                    // find arbitrary die of the apprioriate color
                    const toChange = newDice.find(({ color }) => color === original);
                    // note: this mutates currentGameData - but that's what we want!
                    toChange.color = newColor;
                    this.placePlayerDice(playerId, newDice);
                }

                // update current game data:
                this.currentGameData.players[playerId].favors =
                    Number(this.currentGameData.players[playerId].favors) - favorsSpent;

                if (isCard) {
                    // - if card, mark card used and add new (non-used) die
                    this.currentGameData.players[playerId].oracle_used = cardId;
                    this.currentGameData.players[playerId].dice.push({ color: newColor, used: false, id: newDieId });
                }
                // no need to change currentGameData if it's a die we've changed - ot was already mutated above
            },

            spend_die_animation: function(player_id, die_id) {
                const targetId = `ood_dice_spot_center_${player_id}`;

                const movingDie = document.getElementById(`die_id_${die_id}`);

                // We need to remove the position class, if it exists, from the just moved die.
                // We also must do this before the slide otherwise the calculations are incorrect due to
                // the die being translated at the start of the move.
                this.removeCountClass(movingDie);

                // need to offset by half of die width/height to centre the die in the center spot
                // - but BGA slideToObjectPos actually measures relative to the CENTER of each element,
                // which here has size 0 for the center spot but obviously half the die offsets for the die.
                // To make the position accurate we have to remove these again, hence the quarters...
                // [NOTE: result not pixel-perfect - but close enough, at least for early development!]
                const playerboardRatio = document.documentElement.style.getPropertyValue("--playerboard-ratio") / PLAYERBOARD_BASE_WIDTH_RATIO;
                const dieWidth = DIE_BASE_WIDTH * playerboardRatio;
                const dieHeight = DIE_BASE_HEIGHT * playerboardRatio;
                const anim = this.slideToObjectPos(movingDie, targetId, dieWidth / 4, dieHeight / 4, 500);

                const usedDiceIds = this.currentGameData.players[player_id].dice
                    .filter(({ used }) => used)
                    .map(({ id }) => id);

                const numUsedDice = usedDiceIds.length;

                usedDiceIds.forEach((usedId) => {
                    const dieElement = document.getElementById(`die_id_${usedId}`);
                    const dieClasses = dieElement.classList;
                    //not ideal as removeCountClass ends up calling getCountClass as well.
                    //it's not expensive as there won't be many classes, but still feels unsatisfactory.
                    //Will know better how to fix once written the other class-updating code, below!
                    const existingCountClass = this.getCountClass(dieClasses);
                    this.removeCountClass(dieElement);
                    const currentCount = existingCountClass
                        ? Number(existingCountClass.slice(DIE_CLASS_PREFIX.length, DIE_CLASS_PREFIX.length + 1))
                        : 1;

                    dieClasses.add(`${DIE_CLASS_PREFIX}${currentCount}_of_${numUsedDice + 1}`);
                });

                if (numUsedDice > 0) {
                    movingDie.classList.add(`${DIE_CLASS_PREFIX}${numUsedDice + 1}_of_${numUsedDice + 1}`);
                }

                // need to update count classes of any dice remaining in the starting spot
                dojo.connect(anim, "onEnd", () => {
                    const { dice } = this.currentGameData.players[player_id];
                    const movedDieColor = dice.find(({ id }) => id == die_id).color;
                    const dieIdsLeft = dice.filter(({ id, color, used }) => !used && id != die_id && color === movedDieColor)
                                            .map(({ id }) => id);

                    if (dieIdsLeft.length > 0) {
                        dieIdsLeft.forEach((die_id, index) => {
                            const element = document.getElementById(`die_id_${die_id}`);
                            this.removeCountClass(element);
                            // no need for a new count class if there is only one remaining
                            if (dieIdsLeft.length > 1) {
                                const newCountClass = `${DIE_CLASS_PREFIX}${index+1}_of_${dieIdsLeft.length}`;
                                element.classList.add(newCountClass);
                            }
                        });
                    }
                });

                anim.play();

                // update data to mark die used
                const dieInfo = this.currentGameData.players[player_id].dice.find(({ id }) => id == die_id);

                if (!dieInfo) {
                    console.error(`can't find die id ${die_id} belonging to player with id ${player_id}!`);
                }

                dieInfo.used = true;
            },

            spend_card_animation: function(player_id, card_id) {
                const cardToMove = document.getElementById(`card_id_${card_id}`);
                const targetId = `ood_oracle_used_section_${player_id}`;
                const anim = this.slideToObject(cardToMove, targetId, 500);
                // need to get this here to ensure currentGameData hasn't yet updated with any newly-draw oracle card,
                // otherwise it appears twice!
                const remainingOracles = this.currentGameData.cards.oracle.hands[player_id].filter
                    (({ id }) => Number(id) !== card_id);
                dojo.connect(anim, "onEnd", () => {
                    // would be nice to create a transition for the rotation - but tricky (TODO)
                    this.attachToNewParent(cardToMove, targetId);
                    // also adjust positions of any remaining oracle cards. Let's just remove the existing
                    // cards and replace them using the existing function!
                    const currentOracleCards = remainingOracles.map(({ id }) => document.getElementById(`card_id_${id}`));
                    currentOracleCards.forEach(cardElement => cardElement.remove());
                    this.placeAvailableOracleCards(
                        player_id,
                        remainingOracles,
                        card_id
                    );
                });

                anim.play();

                // update currentGameData to add played card to oracle_used
                this.currentGameData.players[player_id].oracle_used = card_id;
            },

            notif_draw_oracle: function(notif) {
                const { player_id, used_id, oracle_color, card_id, is_card } = notif.args;

                if (is_card) {
                    this.spend_card_animation(player_id, used_id);
                } else {
                    this.spend_die_animation(player_id, used_id);
                }

                const oracleDeck = document.getElementById("ood_oracle_deck");
                let newCard = document.createElement("div");
                newCard.id = `card_id_${card_id}`;
                newCard.classList.add("ood_card", "ood_card_oracle", `ood_card_oracle_${oracle_color}`);
                newCard.style.position = "absolute";
                oracleDeck.appendChild(newCard);

                const targetId = `ood_oracle_section_${player_id}`;
                const anim = this.slideToObject(newCard, targetId, 500);

                // need to adjust width of card, which is based on DOM parent
                dojo.connect(anim, "onEnd", () => {
                    // for the card to nicely transition its shrink in size, we can't just use attachToNewParent
                    // as this removes and recreates the DOM element, thereby disabling the transition.
                    // So we add a new CSS class which also has the reduced size, and only reattach after
                    // a sufficient delay for the transition to be observed
                    // TODO: would be nice for the position to not suddenly jump either. Not a priority yet though!
                    newCard.classList.add("ood_shrink");
                    setTimeout(() => {
                        const numPreviousOracles = document.getElementById(targetId).childElementCount;
                        newCard = this.attachToNewParent(newCard, targetId);
                        newCard.style.position = "";
                        newCard.style.top = "";
                        newCard.style.left = "";
                        //TODO: put this right calculation into separate function?
                        newCard.style.right = `calc(var(--playerboard-side-width) * ${30 * numPreviousOracles - 110}px / 110)`;
                        // not strictly necessary, but keeps things tidier!
                        newCard.classList.remove("ood_shrink");
                    }, 500);
                });
                
                anim.play();

                // Adjust counters, and also deal with case when a reshuffle of the oracle deck has been triggered.
                const { deck, discard } = this.counters[ORACLE];
                const deckCount = deck.getValue();
                if (deckCount > 0) {
                    deck.incValue(-1)
                    // if the count is now 0 (ie. it was 1), then we need to remove the card back image:
                    if (deckCount === 1) {
                        document.getElementById("ood_oracle_deck").innerHTML = '<div class="ood_card_spacer"></div>';
                    }
                } else {
                    deck.toValue(deckCount - 1);
                    discard.toValue(0);
                    // remove the discard card back and add it in to the deck section
                    document.getElementById("ood_oracle_discard").innerHTML = '<div class="ood_card_spacer"></div>';
                    document.getElementById("ood_oracle_deck").innerHTML =
                        '<div class="ood_card ood_card_oracle ood_card_oracle_back"></div>';
                }


                // adjust currentGameData with the new oracle card
                const cardData = this.currentGameData.cards.oracle;
                cardData.deck_size = deck.getValue();
                cardData.discard_size = discard.getValue();
                if (!cardData.hands[player_id]) {
                    cardData.hands[player_id] = [];
                }
                cardData.hands[player_id].push({ card: oracle_color, id: card_id });
            }
        });
    });
