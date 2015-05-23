<?php
/*
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Pleasant Prospect Farm implementation: © Sébastien Prud'homme <sebastien.prudhomme@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 *
 * material.inc.php
 *
 */

$this->CARDS = array(
    array("type" => "cereal", "nbr" => 3),
    array("type" => "chicken", "nbr" => 2),
    array("type" => "cow", "nbr" => 2),
    array("type" => "donkey", "nbr" => 2),
    array("type" => "fruit", "nbr" => 3),
    array("type" => "paddock", "nbr" => 7),
    array("type" => "pig", "nbr" => 2),
    array("type" => "sheep", "nbr" => 2),
    array("type" => "tractor", "nbr" => 1),
    array("type" => "tent", "nbr" => 3),
    array("type" => "vegetable", "nbr" => 3)
);

$this->CARD_TYPE_TRANSLATIONS = array(
    "cereal" => clienttranslate("Cereal"),
    "chicken" => clienttranslate("Chicken"),
    "cow" => clienttranslate("Cow"),
    "donkey" => clienttranslate("Donkey"),
    "fruit" => clienttranslate("Fruit"),
    "paddock" => clienttranslate("Paddock"),
    "pig" => clienttranslate("Pig"),
    "sheep" => clienttranslate("Sheep"),
    "tractor" => clienttranslate("Tractor"),
    "tent" => clienttranslate("Tent"),
    "vegetable" => clienttranslate("Vegetable")
);

$this->HAND_CARD_NUMBER = 5;

$this->PLAYER_COLORS = array(
    "ff0000",
    "008000",
    "0000ff",
    "ffff00"
);

$this->POINTS = array(
    "cereal" => 3,
    "chicken" => 7,
    "cow" => 10,
    "donkey" => 6,
    "fruit" => 4,
    "paddock_capacity" => 2,
    "pig" => 9,
    "sheep" => 8,
    "tent" => 3,
    "tractor_bonus" => 3,
    "tractor_penalty" => 2,
    "vegetable" => 4
);

$this->ROUND_NUMBER = 3;
