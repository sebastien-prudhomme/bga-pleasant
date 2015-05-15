<?php
/*
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Pleasant Prospect Farm implementation: © Sébastien Prud'homme <sebastien.prudhomme@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 *
 * pleasant.view.php
 *
 */

require_once(APP_BASE_PATH . "view/common/game.view.php");

class view_pleasant_pleasant extends game_view {
    protected function getGameName() {
        return "pleasant";
    }

    protected function build_page($view_args) {
        self::buildPlayerHand();
        self::buildPlayerFarms();
    }

    private function buildPlayerHand() {
        $this->tpl["PLAYER_HAND_TITLE"] = self::_("My hand");
    }

    private function buildPlayerFarms() {
        $this->page->begin_block("pleasant_pleasant", "pleasant_player_farm");

        $players = $this->game->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $this->page->insert_block("pleasant_player_farm", array(
                "PLAYER_ID" => $player_id,
                "PLAYER_NAME" => $player["player_name"],
                "PLAYER_COLOR" => $player["player_color"]
            ));
        }
    }
}
