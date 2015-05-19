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
    "dojo/dom-style",
    "dojo/fx",
    "dojo/query",
    "dojox/fx/ext-dojo/complex",
    "ebg/core/gamegui",
    "ebg/counter"
], function(connect, declare, fx, lang, dom, domClass, domConstruct, domStyle, coreFx, query) {
    return declare("bgagame.pleasant", ebg.core.gamegui, {
        constructor: function() {
            this.ANIMATION_DURATION = 1000;
            this.ANIMATION_WAIT = 100;

            this.CARD_HEIGHT = 139;
            this.CARD_WIDTH = 99;

            this.GUTTER = 8;

            this.NOTIFICATIONS = [
                ["cardPlayedFaceUp", this.ANIMATION_DURATION + this.ANIMATION_WAIT],
                ["cardPlayedFaceDown", this.ANIMATION_DURATION + this.ANIMATION_WAIT],
                ["cardHidden", this.ANIMATION_DURATION + this.ANIMATION_WAIT],
                ["cardRevealed", this.ANIMATION_DURATION + this.ANIMATION_WAIT],
                ["roundEnded", this.ANIMATION_DURATION + this.ANIMATION_WAIT],
                ["cardDrawn", this.ANIMATION_DURATION + this.ANIMATION_WAIT],
            ];

            this.temporary_connections = [];
            this.temporary_tooltips = [];
        },

        setup: function(gamedatas) {
            this.setupPlayerHand(gamedatas);
            this.setupPlayerFarms(gamedatas);

            this.setupNotifications();
        },

        setupPlayerHand: function(gamedatas) {
            var player_hand_node = this.getPlayerHandCardsNode();

            var top = 0;
            var left = 0;

            for (var card_id in gamedatas.hand) {
                var card = gamedatas.hand[card_id];

                this.constructCard(card, player_hand_node, top, left);

                left += this.CARD_WIDTH + this.GUTTER;
            }
        },

        setupPlayerFarms: function(gamedatas) {
            for (var player_id in gamedatas.players) {
                var player_farm_node = this.getPlayerFarmCardsNode(player_id);

                var top = 0;
                var left = 0;

                for (var card_id in gamedatas.farms[player_id]) {
                    var card = gamedatas.farms[player_id][card_id];

                    this.constructCard(card, player_farm_node, top, left);

                    left += this.CARD_WIDTH + this.GUTTER;
                }
            }
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

        constructCard: function(card, parent, top, left) {
            top = typeof top !== 'undefined' ? top : 0;
            left = typeof left !== 'undefined' ? left : 0;

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
                CARD_TYPE: card_type,
                CARD_TOP: top,
                CARD_LEFT: left,
                CARD_ROTATION: card_rotation
            }), parent);

            return card_node;
        },

        constructCardsAnimation: function(node) {
            var card_nodes = query("#" + node + " >");

            var animations = [];
            var left = 0;

            for (var i = 0; i < card_nodes.length; i++) {
                var card_node = card_nodes[i];

                var animation = fx.animateProperty({
                    node: card_node,
                    duration: this.ANIMATION_DURATION,
                    properties: {
                        top: 0,
                        left: left
                    }
                });

                animations.push(animation);

                left += this.CARD_WIDTH + this.GUTTER;
            }

            return coreFx.combine(animations);
        },

        getCardFlipperNode: function(card_id) {
            return "pleasant_card_flipper_" + card_id;
        },

        getCardFrontNode: function(card_id) {
            return "pleasant_card_front_" + card_id;
        },

        getCardNode: function(card_id) {
            return "pleasant_card_" + card_id;
        },

        getGameNode: function() {
            return "pleasant";
        },

        getPlayerFarmCardsNode: function(player_id) {
            return "pleasant_player_farm_cards_" + player_id;
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
        },

        notifCardPlayedFaceUp: function(notif) {
            var card = notif.args.card;
            var player_id = notif.args.player_id;

            var player_farm_node = this.getPlayerFarmCardsNode(player_id);

            if  (player_id != this.player_id) {
                domStyle.set(player_farm_node, "overflow", "hidden");

                var card_nodes = query("#" + player_farm_node + " >");

                var top = -this.CARD_HEIGHT - this.GUTTER;
                var left = (this.CARD_WIDTH + this.GUTTER) * card_nodes.length;

                this.constructCard(card, player_farm_node, top, left);

                var animation = this.constructCardsAnimation(player_farm_node);

                connect.connect(animation, "onEnd", function(player_farm_node) {
                    domStyle.set(player_farm_node, "overflow", "");
                });

                animation.play();
            } else {
                var card_node = this.getCardNode(card.id);
                var player_hand_node = this.getPlayerHandCardsNode();

                this.attachToNewParent(card_node, player_farm_node);

                var farm_animation = this.constructCardsAnimation(player_farm_node);
                farm_animation.play();

                var hand_animation = this.constructCardsAnimation(player_hand_node);
                hand_animation.play();
            }
        },

        notifCardPlayedFaceDown: function(notif) {
            var card = notif.args.card;
            var player_id = notif.args.player_id;

            var player_farm_node = this.getPlayerFarmCardsNode(player_id);

            if  (player_id != this.player_id) {
                domStyle.set(player_farm_node, "overflow", "hidden");

                var card_nodes = query("#" + player_farm_node + " >");

                var top = -this.CARD_HEIGHT - this.GUTTER;
                var left = (this.CARD_WIDTH + this.GUTTER) * card_nodes.length;

                this.constructCard(card, player_farm_node, top, left);

                var animation = this.constructCardsAnimation(player_farm_node);

                connect.connect(animation, "onEnd", function(player_farm_node) {
                    domStyle.set(player_farm_node, "overflow", "");
                });

                animation.play();
            } else {
                var card_node = this.getCardNode(card.id);
                var player_hand_node = this.getPlayerHandCardsNode();

                this.attachToNewParent(card_node, player_farm_node);

                var farm_animation = this.constructCardsAnimation(player_farm_node);
                farm_animation.play();

                var hand_animation = this.constructCardsAnimation(player_hand_node);
                hand_animation.play();
            }
        },

        notifCardHidden: function(notif) {
            var card = notif.args.card;

            var card_flipper_node = this.getCardFlipperNode(card.id);

            var animation = fx.animateProperty({
                node: card_flipper_node,
                duration: this.ANIMATION_DURATION,
                properties: {
                    transform: {
                        start: "rotateY(0deg)",
                        end: "rotateY(-180deg)"
                    }
                }
            });

            animation.play();
        },

        notifCardRevealed: function(notif) {
            var card = notif.args.card;
            var player_id = notif.args.player_id;

            if  (player_id != this.player_id) {
                var card_front_node = this.getCardFrontNode(card.id);

                var remove_class = "pleasant_card_type_back";
                var add_class = "pleasant_card_type_" + card.type;

                domClass.replace(card_front_node, add_class, remove_class);
            }

            var card_flipper_node = this.getCardFlipperNode(card.id);

            var animation = fx.animateProperty({
                node: card_flipper_node,
                duration: this.ANIMATION_DURATION,
                properties: {
                    transform: {
                        start: "rotateY(-180deg)",
                        end: "rotateY(0deg)"
                    }
                }
            });

            animation.play();
        },

        notifRoundEnded: function(notif) {
            var new_hand = notif.args.new_hand;
            var old_hand = notif.args.old_hand;

            var animations = [];

            var player_hand_node = this.getPlayerHandCardsNode();

            var top = -this.CARD_HEIGHT - this.GUTTER;
            var left = 0;

            for (var card_id in new_hand) {
                var card = new_hand[card_id];

                var card_node = this.constructCard(card, player_hand_node, top, left);

                var animation = fx.animateProperty({
                    node: card_node,
                    duration: this.ANIMATION_DURATION,
                    properties: {
                        top: 0
                    }
                });

                animations.push(animation);

                left += this.CARD_WIDTH + this.GUTTER;
            }

            top = this.CARD_HEIGHT + this.GUTTER;

            for (var card_id in old_hand) {
                var card = old_hand[card_id];
                var card_node = this.getCardNode(card.id);

                var animation = fx.animateProperty({
                    node: card_node,
                    duration: this.ANIMATION_DURATION,
                    properties: {
                        top: top
                    }
                });

                connect.connect(animation, "onEnd", function(card_node) {
                    domConstruct.destroy(card_node);
                });

                animations.push(animation);
            }

            coreFx.combine(animations).play();
        },

        notifCardDrawn: function(notif) {
            var card = notif.args.card;

            var player_hand_node = this.getPlayerHandCardsNode();
            var card_nodes = query("#" + player_hand_node + " >");

            var top = -this.CARD_HEIGHT - this.GUTTER;
            var left = (this.CARD_WIDTH + this.GUTTER) * card_nodes.length;

            var card_node = this.constructCard(card, player_hand_node, top, left);

            var animation = fx.animateProperty({
                node: card_node,
                duration: this.ANIMATION_DURATION,
                properties: {
                    top: 0
                }
            });

            animation.play();
        }
    });
});
