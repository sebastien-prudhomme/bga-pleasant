<?php
/*
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Pleasant Prospect Farm implementation: © Sébastien Prud'homme <sebastien.prudhomme@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 *
 * states.inc.php
 *
 */

$machinestates = array(
    1 => array(
        "name" => "gameSetup",
        "description" => clienttranslate("Game setup"),
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array("" => 2)
    ),
    2 => array(
        "name" => "playCardFaceUp",
        "description" => clienttranslate("Some players must play a card face up"),
        "descriptionmyturn" => clienttranslate("\${you} must play a card face up"),
        "type" => "multipleactiveplayer",
        "possibleactions" => array("playCardFaceUp"),
        "transitions" => array("cardFaceUpPlayed" => 3),
        "args" => "argPlayCard"
    ),
    3 => array(
        "name" => "cardFaceUpPlayed",
        "description" => "",
        "type" => "game",
        "action" => "stCardFaceUpPlayed",
        "transitions" => array("beforePlayCardFaceDown" => 4)
    ),
    4 => array(
        "name" => "beforePlayCardFaceDown",
        "description" => "",
        "type" => "game",
        "action" => "stBeforePlayCardFaceDown",
        "transitions" => array("playCardFaceDown" => 5),
        "updateGameProgression" => TRUE
    ),
    5 => array(
        "name" => "playCardFaceDown",
        "description" => clienttranslate("Some players must play a card face down"),
        "descriptionmyturn" => clienttranslate("\${you} must must play a card face down "),
        "type" => "multipleactiveplayer",
        "possibleactions" => array("playCardFaceDown"),
        "transitions" => array("cardFaceDownPlayed" => 6),
        "args" => "argPlayCard"
    ),
    6 => array(
        "name" => "cardFaceDownPlayed",
        "description" => "",
        "type" => "game",
        "action" => "stCardFaceDownPlayed",
        "transitions" => array("beforePlayCardFaceUp" => 7, "beforeGameEnd" => 98)
    ),
    7 => array(
        "name" => "beforePlayCardFaceUp",
        "description" => "",
        "type" => "game",
        "action" => "stBeforePlayCardFaceUp",
        "transitions" => array("playCardFaceUp" => 2),
        "updateGameProgression" => TRUE
    ),
    98 => array(
        "name" => "beforeGameEnd",
        "description" => "",
        "type" => "game",
        "action" => "stBeforeGameEnd",
        "transitions" => array("gameEnd" => 99),
        "updateGameProgression" => TRUE
    ),
    99 => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )
);
