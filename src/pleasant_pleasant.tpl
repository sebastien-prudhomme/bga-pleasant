{OVERALL_GAME_HEADER}

<div id="pleasant">
    <div id="pleasant_player_hand">
        <h3 class="pleasant_h3">{PLAYER_HAND_TITLE}</h3>
        <div id="pleasant_player_hand_cards"></div>
    </div>
    <!-- BEGIN pleasant_player_farm -->
    <div class="pleasant_player_farm">
        <h3 class="pleasant_h3" style="color: #{PLAYER_COLOR};">{PLAYER_NAME}</h3>
        <div id="pleasant_player_farm_cards_{PLAYER_ID}" class="pleasant_player_farm_cards"></div>
    </div>
    <!-- END pleasant_player_farm -->
</div>

<script type="text/javascript">
    var jstpl_card = " \
        <div id=\"pleasant_card_${CARD_ID}\" class=\"pleasant_card\"> \
            <div id=\"pleasant_card_flipper_${CARD_ID}\" class=\"pleasant_card_flipper\" style=\"transform: rotateY(${CARD_ROTATION}deg);\"> \
                <div id=\"pleasant_card_front_${CARD_ID}\" class=\"pleasant_card_front pleasant_card_type_${CARD_TYPE}\"></div> \
                <div class=\"pleasant_card_back\"></div> \
            </div> \
        </div>";
</script>

{OVERALL_GAME_FOOTER}
