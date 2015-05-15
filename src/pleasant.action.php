<?php
/*
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Pleasant Prospect Farm implementation: © Sébastien Prud'homme <sebastien.prudhomme@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 *
 * pleasant.action.php
 *
 */

class action_pleasant extends APP_GameAction {
    public function __default() {
        if (self::isArg("notifwindow")) {
            $this->view = "common_notifwindow";
            $this->viewArgs["table"] = self::getArg("table", AT_posint, TRUE);
        } else {
            $this->view = "pleasant_pleasant";
        }
    }

    public function playCardFaceUp() {
        self::setAjaxMode();

        $id = self::getArg("id", AT_posint, TRUE);
        $this->game->playCardFaceUp($id);

        self::ajaxResponse();
    }

    public function playCardFaceDown() {
        self::setAjaxMode();

        $id = self::getArg("id", AT_posint, TRUE);
        $this->game->playCardFaceDown($id);

        self::ajaxResponse();
    }
}
