{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- TheOracleOfDelphi implementation : © Robin Zigmond <robinzig@hotmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------
-->

<!-- map -->
<div id="ood_map">
    <!-- BEGIN maptile -->
    <div class="ood_maphex ood_maphex_{TYPE}{COLOR_CLASS}{ROTATION_CLASS}" id="ood_maphex_{X}_{Y}" style="left: {LEFTPERCENT}%; bottom: {BOTTOMPERCENT}%">
    </div>
    <!-- END maptile -->
</div>

<!-- player boards, including cards etc. -->
<div id="ood_all_playerboards">
    <!-- BEGIN playerboard -->
    <div class="whiteblock ood_playerboard_container" id="ood_playerboard_{PLAYER_ID}">
        <div id="ood_playerboard_player_name_wrapper_{PLAYER_ID}">
            <h3 class="ood_playerboard_player_name" style="color: #{PLAYER_COLOR}">{PLAYER_NAME}</h3>
        </div>
        <div class="ood_playerboard_wrapper">
            <div class="ood_playerboard_left_side" id="ood_playerboard_left_side_{PLAYER_ID}">
                <div id="ood_oracle_used_section_{PLAYER_ID}" class="ood_playerboard_side_section ood_oracle_used_section"></div>
                <div id="ood_oracle_section_{PLAYER_ID}" class="ood_playerboard_side_section ood_oracle_section"></div>
                <div id="ood_injury_section_{PLAYER_ID}" class="ood_playerboard_side_section ood_injury_section"></div>
            </div>
            <div class="ood_playerboard ood_playerboard_{PLAYER_COLOR}" id="ood_playerboard_{PLAYER_ID}">
                <div id="ood_zeus_tile_spot_1_{PLAYER_ID}" class="ood_zeus_tile_spot ood_zeus_tile_spot_1"></div>
                <div id="ood_zeus_tile_spot_2_{PLAYER_ID}" class="ood_zeus_tile_spot ood_zeus_tile_spot_2"></div>
                <div id="ood_zeus_tile_spot_3_{PLAYER_ID}" class="ood_zeus_tile_spot ood_zeus_tile_spot_3"></div>
                <div id="ood_zeus_tile_spot_4_{PLAYER_ID}" class="ood_zeus_tile_spot ood_zeus_tile_spot_4"></div>
                <div id="ood_zeus_tile_spot_5_{PLAYER_ID}" class="ood_zeus_tile_spot ood_zeus_tile_spot_5"></div>
                <div id="ood_zeus_tile_spot_6_{PLAYER_ID}" class="ood_zeus_tile_spot ood_zeus_tile_spot_6"></div>
                <div id="ood_zeus_tile_spot_7_{PLAYER_ID}" class="ood_zeus_tile_spot ood_zeus_tile_spot_7"></div>
                <div id="ood_zeus_tile_spot_8_{PLAYER_ID}" class="ood_zeus_tile_spot ood_zeus_tile_spot_8"></div>
                <div id="ood_zeus_tile_spot_9_{PLAYER_ID}" class="ood_zeus_tile_spot ood_zeus_tile_spot_9"></div>
                <div id="ood_zeus_tile_spot_10_{PLAYER_ID}" class="ood_zeus_tile_spot ood_zeus_tile_spot_10"></div>
                <div id="ood_zeus_tile_spot_11_{PLAYER_ID}" class="ood_zeus_tile_spot ood_zeus_tile_spot_11"></div>
                <div id="ood_zeus_tile_spot_12_{PLAYER_ID}" class="ood_zeus_tile_spot ood_zeus_tile_spot_12"></div>
                <div id="ood_shrine_spot_1_{PLAYER_ID}" class="ood_shrine_spot ood_shrine_spot_1"></div>
                <div id="ood_shrine_spot_2_{PLAYER_ID}" class="ood_shrine_spot ood_shrine_spot_2"></div>
                <div id="ood_shrine_spot_3_{PLAYER_ID}" class="ood_shrine_spot ood_shrine_spot_3"></div>
                <div id="ood_dice_spot_red_{PLAYER_ID}" class="ood_dice_spot ood_dice_spot_red"></div>
                <div id="ood_dice_spot_black_{PLAYER_ID}" class="ood_dice_spot ood_dice_spot_black"></div>
                <div id="ood_dice_spot_pink_{PLAYER_ID}" class="ood_dice_spot ood_dice_spot_pink"></div>
                <div id="ood_dice_spot_blue_{PLAYER_ID}" class="ood_dice_spot ood_dice_spot_blue"></div>
                <div id="ood_dice_spot_yellow_{PLAYER_ID}" class="ood_dice_spot ood_dice_spot_yellow"></div>
                <div id="ood_dice_spot_green_{PLAYER_ID}" class="ood_dice_spot ood_dice_spot_green"></div>
                <div id="ood_dice_spot_center_{PLAYER_ID}" class="ood_dice_spot ood_dice_spot_center"></div>
                <div id="ood_shield_spot_{PLAYER_ID}" class="ood_shield_spot"></div>
                <div id="ood_god_column_poseidon_{PLAYER_ID}" class="ood_god_column ood_god_column_poseidon"></div>
                <div id="ood_god_column_apollon_{PLAYER_ID}" class="ood_god_column ood_god_column_apollon"></div>
                <div id="ood_god_column_artemis_{PLAYER_ID}" class="ood_god_column ood_god_column_artemis"></div>
                <div id="ood_god_column_aphrodite_{PLAYER_ID}" class="ood_god_column ood_god_column_aphrodite"></div>
                <div id="ood_god_column_ares_{PLAYER_ID}" class="ood_god_column ood_god_column_ares"></div>
                <div id="ood_god_column_hermes_{PLAYER_ID}" class="ood_god_column ood_god_column_hermes"></div>
                <div class="ood_shiptile" id="ood_shiptile_{PLAYER_ID}"></div>
            </div>
            <div class="ood_playerboard_right_side" id="ood_playerboard_right_side_{PLAYER_ID}">
                <div class="ood_playerboard_side_section ood_favor_section">
                    <div>
                        <div class="ood_favor"></div>
                        <div id="ood_favor_count_{PLAYER_ID}" class="ood_favor_count"></div>
                    </div>
                </div>
                <div id="ood_companion_section_{PLAYER_ID}" class="ood_playerboard_side_section ood_companion_section"></div>
                <div id="ood_equipment_section_{PLAYER_ID}" class="ood_playerboard_side_section ood_equipment_section"></div>
            </div>
        </div>
    </div>
    <!-- END playerboard -->
</div>

<!-- cards area -->
<div id="ood_cards_stock" class="whiteblock">
    <div class="ood_card_deck_discard" id="ood_oracle_cards">
        <h3>{ORACLE_CARDS}</h3>
        <div class="ood_card_deck">
            <h4>{DECK} (<span id="ood_oracle_deck_count"></span>)</h4>
            <div id="ood_oracle_deck" class="ood_card_deck_inner"></div>
        </div>
        <div class="ood_card_discard">
            <h4>{DISCARD}  (<span id="ood_oracle_discard_count"></span>)</h4>
            <div id="ood_oracle_discard" class="ood_card_discard_inner"></div>
        </div>
    </div>
    <div class="ood_card_deck_discard" id="ood_injury_cards">
        <h3>{INJURY_CARDS}</h3>
        <div class="ood_card_deck">
            <h4>{DECK} (<span id="ood_injury_deck_count"></span>)</h4>
            <div id="ood_injury_deck" class="ood_card_deck_inner"></div>
        </div>
        <div class="ood_card_discard">
            <h4>{DISCARD}  (<span id="ood_injury_discard_count"></span>)</h4>
            <div id="ood_injury_discard" class="ood_card_discard_inner"></div>
        </div>
    </div>
    <div class="ood_card_deck_discard" id="ood_equipment_cards">
        <h3>{EQUIPMENT_CARDS}</h3>
        <div class="ood_card_deck">
            <h4>{DECK} (<span id="ood_equipment_deck_count"></span>)</h4>
            <div id="ood_equipment_deck" class="ood_card_deck_inner"></div>
        </div>
        <div class="ood_card_discard">
            <h4>{DISCARD}  (<span id="ood_equipment_discard_count"></span>)</h4>
            <div id="ood_equipment_discard" class="ood_card_discard_inner"></div>
        </div>
        <div class="ood_card_display">
            <h4>{DISPLAY}</h4>
            <div id="ood_equipment_display"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
</script>  

{OVERALL_GAME_FOOTER}
