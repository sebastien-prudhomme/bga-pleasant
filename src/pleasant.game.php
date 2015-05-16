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

        $card = $this->cards->getCard($id);

        $player_name = self::getCurrentPlayerName();

        self::notifyAllPlayers("cardPlayed", clienttranslate("\${player_name} plays a card"), array(
            "card" => $card,
            "player_name" => $player_name
        ));

        $this->gamestate->setPlayerNonMultiactive($current_player_id, "cardFaceUpPlayed");
    }

    public function playCardFaceDown($id) {
        self::checkAction("playCardFaceDown");

        $current_player_id = self::getCurrentPlayerId();
        $cards = self::getAllowedCards($current_player_id);

        if (!array_key_exists($id, $cards)) {
            throw new BgaVisibleSystemException(self::_("You can't play this card"));
        }

        $card = $this->cards->getCard($id);

        $player_name = self::getCurrentPlayerName();

        self::notifyAllPlayers("cardPlayed", clienttranslate("\${player_name} plays a card"), array(
            "card" => $card,
            "player_name" => $player_name
        ));

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
        $this->gamestate->setAllPlayersMultiactive();

        $players = self::loadPlayersBasicInfos();

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
            self::setGameStateValue("round", $round + 1);

            $this->gamestate->setAllPlayersMultiactive();

            $players = self::loadPlayersBasicInfos();

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
