<?php
/*
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Pleasant Prospect Farm implementation: © Sébastien Prud'homme <sebastien.prudhomme@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 *
 * pleasant.game.php
 *
 */

require_once(APP_GAMEMODULE_PATH . "module/table/table.game.php");

class pleasant extends Table {
    public function pleasant() {
        parent::__construct();

        self::initGameStateLabels(array(
            "progression" => 10,
            "round" => 11
        ));

        $this->cards = self::getNew("module.common.deck");
        $this->cards->init("card");
    }

    protected function getGameName() {
        return "pleasant";
    }

    protected function setupNewGame($players, $options = array()) {
        self::setupPlayers($players);

        self::setupCards();
        self::setupProgression();
        self::setupRound();

        $this->gamestate->setAllPlayersMultiactive();
    }

    private function setupPlayers($players) {
        $colors = $this->PLAYER_COLORS;
        $values = array();

        foreach ($players as $player_id => $player) {
            $player_color = array_shift($colors);
            $player_canal = $player["player_canal"];
            $player_name = addslashes($player["player_name"]);
            $player_avatar = addslashes($player["player_avatar"]);

            $values[] = "(${player_id}, '${player_color}', '${player_canal}', '${player_name}', '${player_avatar}')";
        }

        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $sql .= implode(", ", $values);

        self::DbQuery($sql);
        self::reloadPlayersBasicInfos();
    }

    private function setupCards() {
        $cards = $this->CARDS;

        foreach (array_keys($cards) as $key) {
            $cards[$key]["type_arg"] = 0;
        }

        $this->cards->createCards($cards, "deck");
        $this->cards->shuffle("deck");

        $players = self::loadPlayersBasicInfos();

        foreach (array_keys($players) as $player_id) {
            $this->cards->pickCards($this->HAND_CARD_NUMBER, "deck", $player_id);
        }
    }

    private function setupProgression() {
        self::setGameStateInitialValue("progression", 0);
    }

    private function setupRound() {
        self::setGameStateInitialValue("round", 1);
    }

    protected function getAllDatas() {
        $players = self::loadPlayersBasicInfos();
        $current_player_id = self::getCurrentPlayerId();

        foreach (array_keys($players) as $player_id) {
            if ($player_id == $current_player_id) {
                $hand_cards = $this->cards->getCardsInLocation("hand", $player_id);
                $result["hand"] = $hand_cards;
            }

            $farm_cards = $this->cards->getCardsInLocation("farm_${player_id}" , NULL, "card_location_arg");
            $result["farms"][$player_id] = $farm_cards;
        }

        return $result;
    }

    public function getGameProgression() {
        $progression = self::getGameStateValue("progression");

        $progression = (100 * $progression) / (2 * $this->ROUND_NUMBER);

        return $progression;
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////

    private function getAllowedCards($player_id) {
        $result = $this->cards->getCardsInLocation("hand", $player_id);

        return $result;
    }

    private function hideCard($card) {
        $result = $card;

        $result["type"] = "hidden";

        return $result;
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
////////////

    public function playCardFaceUp($id) {
        self::checkAction("playCardFaceUp");

        $current_player_id = self::getCurrentPlayerId();
        $cards = self::getAllowedCards($current_player_id);

        if (!array_key_exists($id, $cards)) {
            throw new BgaVisibleSystemException(self::_("You can't play this card"));
        }

        $round = self::getGameStateValue("round");
        $location_arg = (2 * $round) - 1;

        $this->cards->moveCard($id, "farm_${current_player_id}", $location_arg);

        $card = $this->cards->getCard($id);
        $player_name = self::getCurrentPlayerName();

        $players = self::loadPlayersBasicInfos();

        foreach (array_keys($players) as $player_id) {
            $private_card = $card;

            if ($player_id != $current_player_id) {
                $private_card = self::hideCard($card);
            }

            self::notifyPlayer($player_id, "cardPlayed", clienttranslate("\${player_name} plays a card"), array(
                "card" => $private_card,
                "player_id" => $current_player_id,
                "player_name" => $player_name
            ));
        }

        $this->gamestate->setPlayerNonMultiactive($current_player_id, "cardFaceUpPlayed");
    }

    public function playCardFaceDown($id) {
        self::checkAction("playCardFaceDown");

        $current_player_id = self::getCurrentPlayerId();
        $cards = self::getAllowedCards($current_player_id);

        if (!array_key_exists($id, $cards)) {
            throw new BgaVisibleSystemException(self::_("You can't play this card"));
        }

        $round = self::getGameStateValue("round");
        $location_arg = 2 * $round;

        $this->cards->moveCard($id, "farm_${current_player_id}", $location_arg);

        $card = $this->cards->getCard($id);
        $player_name = self::getCurrentPlayerName();

        $players = self::loadPlayersBasicInfos();

        foreach (array_keys($players) as $player_id) {
            $private_card = $card;

            if ($player_id != $current_player_id) {
                $private_card = self::hideCard($card);
            }

            self::notifyPlayer($player_id, "cardPlayed", clienttranslate("\${player_name} plays a card"), array(
                "card" => $private_card,
                "player_id" => $current_player_id,
                "player_name" => $player_name
            ));

            if ($player_id == $current_player_id) {
                self::notifyPlayer($player_id, "cardHidden", "", array(
                    "card" => $private_card
                ));
            }
        }

        $this->gamestate->setPlayerNonMultiactive($current_player_id, "cardFaceDownPlayed");
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    public function argPlayCard() {
        $players = self::loadPlayersBasicInfos();

        foreach (array_keys($players) as $player_id) {
            $cards = self::getAllowedCards($player_id);

            $private[$player_id] = array(
                "cards" => $cards
            );
        }

        return array(
            "_private" => $private
        );
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    public function stCardFaceUpPlayed() {
        $players = self::loadPlayersBasicInfos();

        foreach (array_keys($players) as $player_id) {
            $cards = $this->cards->getCardsInLocation("farm_${player_id}" , NULL, "card_location_arg");
            $card = end($cards);

            $card_type = $this->CARD_TYPE_TRANSLATIONS[$card["type"]];
            $player_name = $players[$player_id]["player_name"];

            foreach (array_keys($players) as $other_player_id) {
                if ($other_player_id != $player_id) {
                    self::notifyPlayer($other_player_id, "cardRevealed", clienttranslate("\${player_name} reveals a \${card_type} card"), array(
                        "i18n" => array("card_type"),
                        "card" => $card,
                        "card_type" => $card_type,
                        "player_id" => $player_id,
                        "player_name" => $player_name
                    ));
                }
            }
        }

        $this->gamestate->setAllPlayersMultiactive();

        foreach (array_keys($players) as $player_id) {
            self::giveExtraTime($player_id);
        }

        self::incGameStateValue("progression", 1);

        $this->gamestate->nextState("beforePlayCardFaceDown");
    }

    public function stBeforePlayCardFaceDown() {
        $this->gamestate->nextState("playCardFaceDown");
    }

    public function stCardFaceDownPlayed() {
        $players = self::loadPlayersBasicInfos();

        foreach (array_keys($players) as $player_id) {
            $this->cards->moveAllCardsInLocation("hand", "old_hand", $player_id, $player_id);
        }

        $next_player_table = self::getNextPlayerTable();

        foreach (array_keys($players) as $player_id) {
            $next_player_id = $next_player_table[$player_id];

            $this->cards->moveAllCardsInLocation("old_hand", "hand", $player_id, $next_player_id);
        }

        $round = self::getGameStateValue("round");
        self::setGameStateValue("round", $round + 1);

        foreach (array_keys($players) as $player_id) {
            $new_hand_cards = $this->cards->getCardsInLocation("hand", $player_id);

            $next_player_id = $next_player_table[$player_id];
            $old_hand_cards = $this->cards->getCardsInLocation("hand", $next_player_id);

            self::notifyPlayer($player_id, "roundEnded", clienttranslate("End of round n°${round}"), array(
                "new_hand" => $new_hand_cards,
                "old_hand" => $old_hand_cards,
                "round" => $round
            ));
        }

        if ($round == $this->ROUND_NUMBER) {
            $location_arg = 2 * $this->ROUND_NUMBER + 1;

            foreach (array_keys($players) as $player_id) {
                $cards = $this->cards->getCardsInLocation("hand", $player_id);
                $card = reset($cards);

                $this->cards->moveCard($card["id"], "farm_${player_id}", $location_arg);

                $card_type = $this->CARD_TYPE_TRANSLATIONS[$card["type"]];
                $player_name = $players[$player_id]["player_name"];

                self::notifyAllPlayers("cardPlayed", clienttranslate("\${player_name} plays a \${card_type} card"), array(
                    "i18n" => array("card_type"),
                    "card" => $card,
                    "card_type" => $card_type,
                    "player_id" => $player_id,
                    "player_name" => $player_name
                ));
            }

            foreach (array_keys($players) as $player_id) {
                $player_name = $players[$player_id]["player_name"];

                for ($round = 1; $round <= $this->ROUND_NUMBER; $round++) {
                    $location_arg = 2 * $round;

                    $cards = $this->cards->getCardsInLocation("farm_${player_id}", $location_arg);
                    $card = reset($cards);

                    $card_type = $this->CARD_TYPE_TRANSLATIONS[$card["type"]];

                    self::notifyAllPlayers("cardRevealed", clienttranslate("\${player_name} reveals a \${card_type} card"), array(
                        "i18n" => array("card_type"),
                        "card" => $card,
                        "card_type" => $card_type,
                        "player_id" => $player_id,
                        "player_name" => $player_name
                    ));
                }
            }

            foreach (array_keys($players) as $player_id) {
                $cards = $this->CARDS;

                foreach (array_keys($cards) as $key) {
                    $type = $cards[$key]["type"];

                    $card_count[$type] = 0;
                }

                $cards = $this->cards->getCardsInLocation("farm_${player_id}");

                foreach (array_keys($cards) as $card_id) {
                    $type = $cards[$card_id]["type"];

                    $card_count[$type]++;
                }

                $score = 0;

                $animal_number = $this->POINTS["paddock_capacity"];

                while (($card_count["paddock"] > 0) && ($animal_number > 0)) {
                    $animals = array("cow", "pig", "sheep", "chicken", "donkey");
                    $animal_found = FALSE;

                    foreach ($animals as $animal) {
                        if ($card_count[$animal] >= $animal_number) {
                            $animal_found = TRUE;

                            $card_count["paddock"]--;
                            $card_count[$animal] -= $animal_number;

                            $animal_points = $animal_number * $this->POINTS[$animal];

                            $uppercase_animal = ucfirst($animal);
                            self::incStat($animal_points, "pointsScoredWith${uppercase_animal}Cards", $player_id);
                            $score += $animal_points;

                            break;
                        }
                    }

                    if (!$animal_found) {
                        $animal_number--;
                    }
                }

                $vegetable_points = $card_count["vegetable"] * $this->POINTS["vegetable"];
                $vegetable_points += $card_count["vegetable"] * $card_count["tractor"] * $this->POINTS["tractor_bonus"];

                self::incStat($vegetable_points, "pointsScoredWithVegetableCards", $player_id);
                $score += $vegetable_points;

                $fruit_points = $card_count["fruit"] * $this->POINTS["fruit"];

                self::incStat($fruit_points, "pointsScoredWithFruitCards", $player_id);
                $score += $fruit_points;

                $cereal_points = $card_count["cereal"] * $this->POINTS["cereal"];
                $cereal_points += $card_count["cereal"] * $card_count["tractor"] * $this->POINTS["tractor_bonus"];

                self::incStat($cereal_points, "pointsScoredWithCerealCards", $player_id);
                $score += $cereal_points;

                $tent_points = $card_count["tent"] * ($card_count["tent"] + $this->POINTS["tent"]);
                $tent_points -= $card_count["tent"] * $card_count["tractor"] * $this->POINTS["tractor_penalty"];

                self::incStat($tent_points, "pointsScoredWithTentCards", $player_id);
                $score += $tent_points;

                $sql = "UPDATE player SET player_score = ${score} WHERE player_id = ${player_id}";
                self::DbQuery($sql);
            }

            self::incGameStateValue("progression", 1);

            $this->gamestate->nextState("beforeGameEnd");
        } else {
            foreach (array_keys($players) as $player_id) {
                $card = $this->cards->pickCard("deck", $player_id);

                self::notifyPlayer($player_id, "cardDrawn", "", array(
                    "card" => $card
                ));
            }

            $this->gamestate->setAllPlayersMultiactive();

            foreach (array_keys($players) as $player_id) {
                self::giveExtraTime($player_id);
            }

            self::incGameStateValue("progression", 1);

            $this->gamestate->nextState("beforePlayCardFaceUp");
        }
    }

    public function stBeforePlayCardFaceUp() {
        $this->gamestate->nextState("playCardFaceUp");
    }

    public function stBeforeGameEnd() {
        $this->gamestate->nextState("gameEnd");
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    private function zombieTurn($state, $active_player) {
        throw new BgaVisibleSystemException(self::_("Zombie mode not supported"));
    }
}
