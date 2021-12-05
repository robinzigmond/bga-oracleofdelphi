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
 * states.inc.php
 *
 * TheOracleOfDelphi game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!

// define contants for state ids
if (!defined('STATE_END_GAME')) { // ensure this block is only invoked once, since it is included multiple times
    define("STATE_TYPE_ACTIVEPLAYER", "activeplayer");
    define("STATE_TYPE_MULTIPLEACTIVEPLAYER", "multipleactiveplayer");
    define("STATE_TYPE_GAME", "game");

    define("STATE_PLAYER_TURN", 2);
    define("STATE_TURN_END", 3);
    define("STATE_CONSULT_ORACLE", 4);
    define("STATE_ORACLE_CHOOSE_GOD", 5);
    define("STATE_FIGHT_MONSTER", 6);
    define("STATE_CONTINUE_FIGHT", 7);
    define("STATE_NO_INJURY_REWARD", 8);
    define("STATE_DISCARD_EXCESS_INJURIES", 9);
    define("STATE_END_GAME", 99);
 }

//TODO: rethink state machine, need to handle full "turn framework" as well as
//all possible outcomes of injury card check phase (most aren't in as states, just as
//"possibleactions"!) 
$machinestates = array(

    // The initial state. Please do not modify.
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array( "" => STATE_PLAYER_TURN )
    ),
    
    // Note: ID=2 => your first state

    STATE_PLAYER_TURN => [
        "name" => "playerTurn",
        "description" => clienttranslate('${actplayer} is taking their turn'),
        "descriptionmyturn" => clienttranslate('${you} can select an oracle die or card, or god, to perform an action'),
        "args" => "argPlayerTurn",
        "type" => STATE_TYPE_ACTIVEPLAYER,
        "possibleactions" => ["takeActions"],
        "transitions" => ["fightMonster" => STATE_FIGHT_MONSTER, "endTurn" => STATE_CONSULT_ORACLE
        ]
    ],

    STATE_TURN_END => [
        "name" => "turnEnd",
        "description" => "",
        "descriptionmyturn" => "",
        "type" => STATE_TYPE_GAME,
        "action" => "stTurnEnd",
        "transitions" => [
            "gameEnd" => STATE_END_GAME,
            "noInjuries" => STATE_NO_INJURY_REWARD,
            "recovery" => STATE_DISCARD_EXCESS_INJURIES,
            "nextTurn" => STATE_PLAYER_TURN
        ]
    ],

    STATE_CONSULT_ORACLE => [
        "name" => "consultOracle",
        "description" => "",
        "descriptionmyturn" => "",
        "type" => STATE_TYPE_GAME,
        "action" => "stConsultOracle",
        "transitions" => ["otherPlayersChoice" => STATE_ORACLE_CHOOSE_GOD, "noChoice" => STATE_TURN_END]
    ],

    STATE_ORACLE_CHOOSE_GOD => [
        "name" => "oracleChooseGod",
        "description" => "other players must choose which God to advance",
        "descriptionmyturn" => '${you} must choose which God to advance',
        "type" => STATE_TYPE_MULTIPLEACTIVEPLAYER,
        "possibleactions" => ["chooseGod"],
        "action" => "stOracleChooseGod",
        "transitions" => ["allChosen" => STATE_TURN_END]
    ],

    STATE_FIGHT_MONSTER => [
        "name" => "fightMonster",
        "description" => "",
        "descriptionmyturn" => "",
        "type" => STATE_TYPE_GAME,
        "action" => "stFightMonster",
        "transitions" => ["win" => STATE_PLAYER_TURN, "lose" => STATE_CONTINUE_FIGHT]
    ],

    STATE_CONTINUE_FIGHT => [
        "name" => "continueFight",
        "description" => '${actplayer} must decide whether to continue the fight',
        "descriptionmyturn" => '${you} must decide whether to continue the fight',
        "type" => STATE_TYPE_ACTIVEPLAYER,
        "possibleactions" => ["continueOrNot"],
        "transitions" => [
            "end" => STATE_PLAYER_TURN,
            "continueAndWin" => STATE_PLAYER_TURN,
            "continueAndLose" => STATE_CONTINUE_FIGHT
        ]
    ],

    STATE_NO_INJURY_REWARD => [
        "name" => "noInjuryReward",
        "description" => '${actplayer} can gain a favor token or advance a God by one step',
        "descriptionmyturn" => '${you} can gain a favor token or advance a God by one step',
        "type" => STATE_TYPE_ACTIVEPLAYER,
        "possibleactions" => ["gainFavor", "advanceGod"],
        "transitions" => ["" => STATE_PLAYER_TURN]
    ],

    STATE_DISCARD_EXCESS_INJURIES => [
        "name" => "discardExessInjuries",
        "description" => '${actplayer} must recover by discarding 3 injury cards',
        "descriptionmyturn" => '${you} must recover by discarding 3 injury cards',
        "type" => STATE_TYPE_ACTIVEPLAYER,
        "possibleactions" => ["discard"],
        "transitions" => ["" => STATE_TURN_END]
    ],

    // Final state.
    // Please do not modify (and do not overload action/args methods).
    STATE_END_GAME => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);



