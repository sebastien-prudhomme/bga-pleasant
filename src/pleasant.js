/*
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Pleasant Prospect Farm implementation: © Sébastien Prud'homme <sebastien.prudhomme@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 *
 * pleasant.js
 *
 */

"use strict";

define([
    "dojo/_base/connect",
    "dojo/_base/declare",
    "dojo/_base/fx",
    "dojo/_base/lang",
    "dojo/dom",
    "dojo/dom-class",
    "dojo/dom-construct",
    "dojo/dom-geometry",
    "dojo/dom-style",
    "dojo/query",
    "dojox/fx/ext-dojo/complex",
    "ebg/core/gamegui",
    "ebg/counter"
], function(connect, declare, fx, lang, dom, domClass, domConstruct, domGeom, domStyle, query) {
    return declare("bgagame.pleasant", ebg.core.gamegui, {
        constructor: function() {
            this.ANIMATION_DURATION = 1000;
            this.ANIMATION_WAIT = 100;

            this.NOTIFICATIONS = [];

            this.temporary_connections = [];
            this.temporary_tooltips = [];
        },

        setup: function(gamedatas) {
            this.setupPlayerHand(gamedatas);

            this.setupNotifications();
        },

        setupPlayerHand: function(gamedatas) {
            var player_hand_node = this.getPlayerHandCardsNode();

            for (var card_id in gamedatas.hand) {
                var card = gamedatas.hand[card_id];

                var card_node = this.constructCard(card, player_hand_node);
            }

            this.updatePlayerHand();
        },

        ///////////////////////////////////////////////////
        //// States

        onEnteringState: function(state_name, args) {
            switch (state_name) {
                case "playCardFaceUp":
                    this.updatePlayableCardsFaceUp(args.args._private.cards);
                    break;

                case "playCardFaceDown":
                    this.updatePlayableCardsFaceDown(args.args._private.cards);
                    break;
            }
        },

        ///////////////////////////////////////////////////
        //// Utilities

        addTemporaryConnection: function(node, evt, action, arg) {
            var handle = connect.connect(dom.byId(node), evt, lang.hitch(this, action, arg));

            this.temporary_connections.push(handle);
        },

        addTemporaryTooltip: function(node, message) {
            domStyle.set(node, "cursor", "pointer");
            this.addTooltip(node, "", message);

            this.temporary_tooltips.push(node);
        },

        constructCard: function(card, parent) {
            var card_rotation;
            var card_type;

            if (card.type != "hidden") {
                card_rotation = 0;
                card_type = card.type;
            } else {
                card_rotation = -180;
                card_type = "back";
            }

            var card_node = domConstruct.place(this.format_block("jstpl_card", {
                CARD_ID: card.id,
                CARD_ROTATION: card_rotation,
                CARD_TYPE: card_type
            }), parent);

            return card_node;
        },

        getCardNode: function(card_id) {
            return "pleasant_card_" + card_id;
        },

        getGameNode: function() {
            return "pleasant";
        },

        getPlayerHandCardsNode: function() {
            return "pleasant_player_hand_cards";
        },

        removeTemporaryConnections: function() {
            while (this.temporary_connections.length > 0) {
                var handle = this.temporary_connections.pop();

                connect.disconnect(handle);
            }
        },

        removeTemporaryTooltips: function() {
            while (this.temporary_tooltips.length > 0) {
                var node = this.temporary_tooltips.pop();

                domStyle.set(node, "cursor", "default");
                this.removeTooltip(node);
            }
        },

        updatePlayableCardsFaceUp: function(cards) {
            for (var id in cards) {
                var card = cards[id];

                if (this.isCurrentPlayerActive()) {
                    var card_node = this.getCardNode(card.id);

                    this.addTemporaryConnection(card_node, "onclick", "onPlayCardFaceUp", card.id);
                    this.addTemporaryTooltip(card_node, _("Play this card face up"));
                }
            }
        },

        updatePlayableCardsFaceDown: function(cards) {
            for (var id in cards) {
                var card = cards[id];

                if (this.isCurrentPlayerActive()) {
                    var card_node = this.getCardNode(card.id);

                    this.addTemporaryConnection(card_node, "onclick", "onPlayCardFaceDown", card.id);
                    this.addTemporaryTooltip(card_node, _("Play this card face down"));
                }
            }
        },

        updatePlayerHand: function() {
            var hand_node = this.getPlayerHandCardsNode();
            var hand_children = query("#" + hand_node + " >");

            var left_incr = 0;

            if (hand_children.length > 1) {
                var hand_width = domGeom.getContentBox(hand_node).w;
                var child_width = domGeom.getContentBox(hand_children[0]).w;

                var left_incr_1 = (hand_width - child_width) / (hand_children.length - 1);

                var game_node = this.getGameNode();
                var game_width = domGeom.getContentBox(game_node).w;

                var left_incr_2 = child_width + (game_width * 0.01) ;

                left_incr = Math.min(left_incr_1, left_incr_2);
            }

            var left = 0;

            for (var i = 0; i < hand_children.length; i++) {
                var animation = fx.animateProperty({
                    node: hand_children[i],
                    duration: this.ANIMATION_DURATION,
                    properties: {
                        top: 0,
                        left: Math.ceil(left)
                    }
                });

                animation.play();

                left += left_incr;
            }
        },

        ///////////////////////////////////////////////////
        //// Actions

        onPlayCardFaceUp: function(id, evt) {
            evt.preventDefault();
            evt.stopPropagation();

            if (this.checkAction("playCardFaceUp")) {
                this.ajaxcall("/pleasant/pleasant/playCardFaceUp.html", {
                    lock: true,
                    id: id
                }, this, function(result) {
                    this.removeTemporaryConnections();
                    this.removeTemporaryTooltips();
                });
            }
        },

        onPlayCardFaceDown: function(id, evt) {
            evt.preventDefault();
            evt.stopPropagation();

            if (this.checkAction("playCardFaceDown")) {
                this.ajaxcall("/pleasant/pleasant/playCardFaceDown.html", {
                    lock: true,
                    id: id
                }, this, function(result) {
                    this.removeTemporaryConnections();
                    this.removeTemporaryTooltips();
                });
            }
        },

        ///////////////////////////////////////////////////
        //// Notifications

        setupNotifications: function() {
            for (var i = 0; i < this.NOTIFICATIONS.length; i++) {
                var notif_name = this.NOTIFICATIONS[i][0];
                var notif_duration = this.NOTIFICATIONS[i][1];

                var method_name = "notif" + notif_name[0].toUpperCase() + notif_name.slice(1);

                connect.subscribe(notif_name, this, method_name);
                this.notifqueue.setSynchronous(notif_name, notif_duration);
            }
        }
    });
});
