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

<!-- player boards, including cards etc. (TODO) -->

<!-- cards area -->
<div id="ood_cards_stock" class="whiteblock">
    <div class="ood_card_deck_discard" id="ood_oracle_cards">
        <h3>{ORACLE_CARDS}</h3>
        <div class="ood_card_deck">
            <h4>{DECK}</h4>
            <div id="ood_oracle_deck_count"></div>
            <div id="ood_oracle_deck"></div>
        </div>
        <div class="ood_card_discard">
            <h4>{DISCARD}</h4>
            <div id="ood_oracle_discard_count"></div>
            <div id="ood_oracle_discard"></div>
        </div>
    </div>
    <div class="ood_card_deck_discard" id="ood_injury_cards">
        <h3>{INJURY_CARDS}</h3>
        <div class="ood_card_deck">
            <h4>{DECK}</h4>
            <div id="ood_injury_deck_count"></div>
            <div id="ood_injury_deck"></div>
        </div>
        <div class="ood_card_discard">
            <h4>{DISCARD}</h4>
            <div id="ood_injury_discard_count"></div>
            <div id="ood_injury_discard"></div>
        </div>
    </div>
        <div class="ood_card_deck_discard" id="ood_equipment_cards">
        <h3>{EQUIPMENT_CARDS}</h3>
        <div class="ood_card_deck">
            <h4>{DECK}</h4>
            <div id="ood_equipment_deck_count"></div>
            <div id="ood_equipment_deck"></div>
        </div>
        <div class="ood_card_discard">
            <h4>{DISCARD}</h4>
            <div id="ood_equipment_discard_count"></div>
            <div id="ood_equipment_discard"></div>
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
