{{-- 
| This is the default view type
| PS main landing page
--}}
<div class='ps-main_wrapper'>

    {{-- Header --}}
    <header class='ps_js-render' data-custom-tags='options,navigation,language,savvy,media'>
        <div class='ps-row ps_navigation-top'>
            <div class='ps-container'>
                <ps-savvy-announcement 
                    v-bind:language = 'shared.lang_config.active'
                    v-bind:client   = 'shared.user.id'
                    v-bind:game     = "shared.user.is_auth? 'Player Site' : 'Before Login'"
                ></ps-savvy-announcement>
                @if(Auth::check())
                    <ul class='ps_navigation-toolbar ps-ul_horizontal'>
                        <li>
                            <a href='#profile'><i class='ps_icon md-person'></i>@{{ shared.user.username }}</a>
                        </li>
                        <li>
                            <a href='#ingame_balance'>
                                <i class='ps_icon md-account_balance_wallet'></i>
                                <span class='ps_navigation-availableBalance'>
                                    @{{ shared.user.currency_code }} @{{ shared.user.availableBalance }}
                                </span>
                            </a>
                            <ps-options-refresh-balance></ps-options-refresh-balance>
                        </li>
                        <li>
                            <ps-options-logout>
                                <i class='ps_icon md-exit_to_app'></i>@{{ shared.lang.language.logout }}
                            </ps-options-logout>
                        </li>
                    </ul>
                @endif 

            </div>
        </div>

        <div class='ps-row ps_navigation-mid'>
            <div class='ps-container'>
                <div class='ps_navigation-logo'>
                    <a href=''><ps-indicator-logo></ps-indicator-logo></a>
                </div>
                @if(Auth::check())
                    <nav class='ps_navigation-submenu'>
                        <ps-navigation-menu item='secondary' expected='3'></ps-navigation-menu>
                        <ps-navigation-info>
                            <template scope='info'>
                                <ul 
                                    v-if  = "info.root.hashes['#fund_transfer']" 
                                    class = 'ps_navigation-menu ps-ul_horizontal'
                                >
                                    <li>
                                        <a class='ps_navigation-menu_item' href='#fund_transfer'>
                                            @{{ shared.lang.language.fund_transfer }}
                                        </a>
                                    </li>
                                </ul>
                            </template>
                        </ps-navigation-info>
                        <ps-language-selector></ps-language-selector>
                    </nav>
                @else
                    <div class='ps_navigation-login' data-custom-tags='login_form'>
                        <ps-login-form-main></ps-login-form-main>
                    </div>
                @endif
            </div>
        </div>

        <div class='ps-row ps_navigation-bottom'>
            <div class='ps-container ps_navigation-menu_container'>
                <ps-navigation-menu item='primary'></ps-navigation-menu>
            </div>
        </div>
    </header>

    {{-- Pages --}}
    <section id='ps_js-navigation-pages'></section>

    {{-- Features --}}
    <section 
        class            = 'ps_js-render ps-features' 
        data-custom-tags = 'news,jackpot,latest_transactions,image'
    >
        <div class='ps-features_latest'>
            <div class='ps-features_boundary'>
                <div class='ps-features_item ps-features_first'>
                    <ps-news-main v-bind:rows='2'></ps-news-main>
                </div>
                <div class='ps-features_item ps-features_less_space'>
                    <div class='ps-features_jackpot'>
                        <ps-jackpot-main></ps-jackpot-main>
                    </div>
                    <div class='ps-features_banks'>
                        <ps-image-lazy 
                            class      = 'ps-footer_bank ps-footer_bank_bca'
                            background = 'true'
                            v-bind:src = "shared.rso.assets + 'images/ps_footer/bank_sprites.png'"
                        ></ps-image-lazy>
                        <ps-image-lazy 
                            class      = 'ps-footer_bank ps-footer_bank_mandiri'
                            background = 'true'
                            v-bind:src = "shared.rso.assets + 'images/ps_footer/bank_sprites.png'"
                        >
                        </ps-image-lazy>
                        <ps-image-lazy 
                            class      = 'ps-footer_bank ps-footer_bank_bri'
                            background = 'true'
                            v-bind:src = "shared.rso.assets + 'images/ps_footer/bank_sprites.png'"
                        ></ps-image-lazy>
                        <ps-image-lazy 
                            class      = 'ps-footer_bank  ps-footer_bank_bni'
                            background = 'true'
                            v-bind:src = "shared.rso.assets + 'images/ps_footer/bank_sprites.png'"
                        ></ps-image-lazy>
                    </div>
                </div>
                <div class='ps-features_item ps-features_last'>
                    <ps-latest-transactions-main content='winners,deposit,withdrawal'>
                    </ps-latest-transactions-main>
                </div>
            </div>
        </div>

        <div v-if='shared.site.is_mobile_ready' class='ps-features_mobile_view'>
            <a class='ps_components-button' v-bind:href='shared.site.domains.mobile'>
                <i class='ps_icon md-phone_iphone'></i> @{{ shared.lang.language.mobile_view }}
            </a>
        </div>
    </section>

    {{-- Footer --}}
    <footer class='ps_js-render' data-custom-tags='pop,image'> 
        <div class='ps-container'>
            <a href='#contact_us'>
                <i class='icon-message'></i> @Lang('language.contact_us')
            </a>
            <a href='#terms_and_conditions'>
                <i class='icon-information'></i> @Lang('language.terms_and_conditions')
            </a>
            <a href='#gaming_rules_general'>
                <i class='icon-file'></i> @Lang('language.gaming_rules')
            </a>
            <span class='ps-footercopy_right'>@{{shared.lang.custom.theme.copy_right}}</span>
            <div class='ps-footer_game_supports'>
                <ps-pop-tooltip
                    v-bind:title   = "shared.lang.messages.for_18_plus"
                    data-placement = 'top'
                    data-container = 'body'
                    class          = 'ps-footer_18_plus ps-game_supports_icon'
                >
                    <ps-image-lazy 
                        background   = 'true'
                        v-bind:src   = "shared.rso.assets + 'images/ps_footer/game_supports.png'" 
                    ></ps-image-lazy>
                </ps-pop-tooltip>
                <ps-pop-tooltip
                    v-bind:title   = "shared.lang.messages.gamcare"
                    data-placement = 'top'
                    data-container = 'body'
                    class          = 'ps-footer_gamcare ps-game_supports_icon ps-game_supports_link'
                    target         = '_blank'
                    href           = 'http://www.gamcare.org.uk/'
                >
                    <ps-image-lazy 
                        background   = 'true'
                        v-bind:src   = "shared.rso.assets + 'images/ps_footer/game_supports.png'" 
                    ></ps-image-lazy>
                </ps-pop-tooltip>
                <ps-pop-tooltip
                    v-bind:title   = "shared.lang.messages.gambling_therapy"
                    data-placement = 'top'
                    data-container = 'body'
                    class          = 'ps-footer_gambling_therapy ps-game_supports_icon ps-game_supports_link'
                    target         = '_blank'
                    href           = 'https://www.gamblingtherapy.org'
                >
                    <ps-image-lazy 
                        background   = 'true'
                        v-bind:src   = "shared.rso.assets + 'images/ps_footer/game_supports.png'" 
                    ></ps-image-lazy>
                </ps-pop-tooltip>
                <ps-pop-tooltip
                    v-bind:title   = "shared.lang.messages.usa_player_prohibited"
                    data-placement = 'top'
                    data-container = 'body'
                    class          = 'ps-footer_no_usa ps-game_supports_icon'
                >
                    <ps-image-lazy 
                        background   = 'true'
                        v-bind:src   = "shared.rso.assets + 'images/ps_footer/game_supports.png'" 
                    ></ps-image-lazy>
                </ps-pop-tooltip>
            </div>
        </div>
    </footer>
</div>

{{-- Chatbox --}}
<span class='ps_js-render' data-custom-tags='chatbox'>
    <ps-chatbox-main></ps-chatbox-main>
</span>
