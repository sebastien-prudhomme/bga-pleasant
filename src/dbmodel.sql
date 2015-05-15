/*
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Pleasant Prospect Farm implementation: © Sébastien Prud'homme <sebastien.prudhomme@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 *
 * dbmodel.sql
 *
 */

CREATE TABLE IF NOT EXISTS card (
    card_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    card_type VARCHAR(32) NOT NULL,
    card_type_arg INT(11) NOT NULL,
    card_location VARCHAR(32) NOT NULL,
    card_location_arg INT(11) NOT NULL,
    PRIMARY KEY (card_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
