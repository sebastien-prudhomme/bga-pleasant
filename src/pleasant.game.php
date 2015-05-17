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
            "round" => 10
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
        return 0;
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

            self::notifyPlayer($player_id, "cardPlayedFaceUp", clienttranslate("\${player_name} plays a card"), array(
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

            self::notifyPlayer($player_id, "cardPlayedFaceDown", clienttranslate("\${player_name} plays a card"), array(
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

            self::notifyAllPlayers("cardRevealed", clienttranslate("\${player_name} reveals a \${card_type} card"), array(
                "i18n" => array("card_type"),
                "card" => $card,
                "card_type" => $card_type,
                "player_id" => $player_id,
                "player_name" => $player_name
            ));
        }

        $this->gamestate->setAllPlayersMultiactive();

        foreach (array_keys($players) as $player_id) {
            self::giveExtraTime($player_id);
        }

        $this->gamestate->nextState("playCardFaceDown");
    }

    public function stCardFaceDownPlayed() {
        $round = self::getGameStateValue("round");

        if ($round == $this->ROUND_NUMBER) {
            $this->gamestate->nextState("gameEnd");
        } else {
            $players = self::loadPlayersBasicInfos();

            foreach (array_keys($players) as $player_id) {
                $this->cards->moveAllCardsInLocation("hand", "old_hand", $player_id, $player_id);
            }

            $next_player_table = self::getNextPlayerTable();

            foreach (array_keys($players) as $player_id) {
                $next_player_id = $next_player_table[$player_id];

                $this->cards->moveAllCardsInLocation("old_hand", "hand", $player_id, $next_player_id);
            }

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

            $this->gamestate->nextState("playCardFaceUp");
        }
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    private function zombieTurn($state, $active_player) {
        throw new BgaVisibleSystemException(self::_("Zombie mode not supported"));
    }
}
