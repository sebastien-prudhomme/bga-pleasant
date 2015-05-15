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
        "transitions" => array("playCardFaceDown" => 4)
    ),
    4 => array(
        "name" => "playCardFaceDown",
        "description" => clienttranslate("Some players must play a card face down"),
        "descriptionmyturn" => clienttranslate("\${you} must must play a card face down "),
        "type" => "multipleactiveplayer",
        "possibleactions" => array("playCardFaceDown"),
        "transitions" => array("cardFaceDownPlayed" => 5),
        "args" => "argPlayCard"
    ),
    5 => array(
        "name" => "cardFaceDownPlayed",
        "description" => "",
        "type" => "game",
        "action" => "stCardFaceDownPlayed",
        "transitions" => array("playCardFaceUp" => 2, "gameEnd" => 99)
    ),
    99 => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )
);
