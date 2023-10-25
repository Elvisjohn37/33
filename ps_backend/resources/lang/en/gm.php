<?php
return array(
    '0' => array(
        "title" => 'General Games Rules',
        "description" => 'The following rules (the "Game Rules") govern the end user\'s (the "Player", "you" or "your") use of all the Operator\'s (or {{@site.name}}\'s) interactive casino products and services (the "Casino Games") available at {{@site.domains.desktop}} (the "Website"). They form part of the Operator\'s Terms and Conditions, which apply to all the Casino Games the Operator offers and which the Player has to accept upon registering for any of the Casino Games. To the extent there is any inconsistency between the Terms and Conditions and any of these Gaming Rules, the Terms and Conditions shall prevail.',
        "items" => array(
            array("topic" => 'Effective date of the rules',
                "paragraphs" => array('These Rules are effective as of {{@lang.custom.help.gamerules_effective_date}}.')),
            array("topic" => 'Inconsistency between English and other languages',
                "paragraphs" => array('In the event of any inconsistency between the English and the non-English versions of the Games Betting Rules, the English version shall prevail.')),
            array("topic" => 'Interpretation of rules',
                "paragraphs" => array('If there is any dispute over the interpretation of these Games Betting Rules, the Operator\'s interpretation shall prevail.')),
            array("topic" => 'Betting method',
                "paragraphs" => array('The Operator will only accept Bets that are made online. Any other betting method (eg telephone betting) will not be accepted.')),
            array("topic" => 'Valid bets',
                "paragraphs" => array('A Bet is deemed accepted only when the Operator has allocated an identification number (a "Bet ID") to it.')),
            array("topic" => 'Betting history',
                "paragraphs" => array('If you wish to view details of your Bets Details at any time, you may check the Statement available within the relevant Game.')),
            array("topic" => 'Random number generator',
                "paragraphs" => array('To ensure that the Games are completely fair, all results are produced by a random number generator ("RNG"). The methodology of the Operator\'s RNG has been evaluated and certified by an independent third party testing company.')),
            array("topic" => 'Connection failures during game play',
                "paragraphs" => array('A \'Draw\' is the selection of a result for a game on a random basis. If connection is lost whilst playing a single-stage Draw On Demand game, the game will be completed automatically for you. If connection is lost whilst playing a multi-stage Draw On Demand game (eg a slot machine that has a bonus or feature round) and your game has entered the second stage, your game details will be retained so that you can log back in and continue play. If your game did not enter the second stage before loss of connection, the game will be completed automatically for you.',
                    'When connection is lost during a Draw On Demand game, only the current game is handled. If Auto Play was used by the customer to specify further games, the pending games will be cancelled. No funds will be removed from the customer\'s account in relation to the cancelled pending games.',
                    'NOTE: In the case of a connection loss in respect of a Draw On Demand game, you will be able to see your bet details via your Statement in the specific Game once you are reconnected.')),
            array("topic" => 'Bet limits',
                "paragraphs" => array('Game betting limits are detailed in the help file for each game. See the Specific Game Betting Rules for each game (section 14 below).')),
            array("topic" => 'Void bets',
                "paragraphs" => array('In the unlikely event of a Games Bet being declared void, your original stake will be returned.',
                    'MALFUNCTION VOIDS ALL PAYS AND PLAY.',
                    'Any technical malfunction of any sort, including but not exclusively software, hardware or connection malfunction, will result in all affected Games Bets being declared void, irrespective of any indicative result.',
                    'We further reserve the right to declare a Bet to be void if:'),
                "bullets" => array('only partial or incorrect bet details are available;',
                    'we have any suspicion that the customer is participating in fraud, collusion, money laundering or other illegal activities;',
                    'we believe the customer to be under the age of 18 or below the legal age required for gambling in his or her jurisdiction;',
                    'acceptance of the bet would exceed set game limits;',
                    'the stake amount is not available in the customer\'s account;',
                    'an error (human or otherwise) or system failure results in incorrect odds being used;',
                    'technical faults such as communication failures, defects, interruptions, delays, viruses, denial of service attacks, or data corruption occur; or',
                    'if a technical defect produces excessive or irregular winnings.'),
                "paragraphs-sub" => array('Should a bet be settled incorrectly due to error (human or otherwise) or system failure, the settlement will be deemed as invalid and will be reversed. Should the customer have insufficient funds in their account to allow this, the customer will be required to return the necessary amount to the Operator.')),
            array("topic" => 'Game use',
                "paragraphs" => array('The Games are to be used solely for personal entertainment. Any other use of the Games or any related elements (eg graphics, text, etc.), including but not exclusively any business or professional use, is strictly prohibited and is a direct infringement of the proprietary and other rights of the Operator, who may take any legal action to enforce such rights.')),
            array("topic" => 'Complaints',
                "paragraphs" => array('If you have a complaint regarding the outcome of a bet on any Game, you should contact the Customer Service team within 72 hours via the email address <a href="mailto:' . Config::get("settings.EMAIL_SENDER") . '">' . Config::get("settings.EMAIL_SENDER") . '</a>, setting out your complaint in full, including detailed timings. The Operator will investigate the complaint using data from its own systems. You hereby agree that the ultimate decision in respect of the complaint rests with the Operator acting reasonably in the light of all the available evidence, and is final.')),
            array("topic" => 'Liability',
                "paragraphs" => array('You hereby agree that you shall have no cause of action and you hereby waive any rights or claims against the software developer of the Games for any matter, cause or thing involving your participation in the Games or otherwise.',
                    'You hereby agree that you shall have no cause of action and you hereby waive any rights or claims against the Operator in respect of the use, misuse or abuse by you or by any third party of your account, or in respect of the conduct of other customers, or in respect of any technical faults or defects, including but not exclusively those set out in section 10 above.',
                    'Whilst the Operator will take reasonable steps to co-operate with a customer who wishes to self-exclude, the customer hereby agrees that the Operator will not be liable if a self-excluded customer gambles with another operator, or if the self-excluded customer continues to gamble with the Operator using accounts with different or rearranged registration details, or if the customer otherwise tries to evade the effect of the self-exclusion.')),
            array("topic" => 'Specific game betting rules',
                "paragraphs" => array('Specific Game Betting Rules for the Games currently available on the Website can be found using the following links:',
                    "<span class='ps_js-game_guide_menu'></span>")),
            array("topic" => 'Change to these rules',
                "paragraphs" => array('The Operator reserves the right to change any of these Games Betting Rules (including any Specific Game Betting Rules) at any time in accordance with the General Terms & Conditions.'))
        )
    ),
    '2' => array(
        "title" => 'Skill Game\'s Rules',
        "description" => 'The following rules (the "Skill Games Rules") govern the end user\'s use of all the Operator\'s interactive games betting products and services (the "Skill Games") offered via the "Skill Games" tab of {{@site.domains.desktop}} (the "Website"). They form part of the Operator\'s General Terms & Conditions, which apply to all the Games and which the customer must accept upon registering for any of the Games. In the case of any inconsistency between the General Terms and Conditions and any of these Games Betting Rules, the General Terms and Conditions shall prevail.</p><p>All bets on the Skill Games ("Skill Games Bets") accepted by the Operator are subject to these Skill Games Betting Rules.</p><p>Throughout these Skill Games Betting Rules, references to "we", "our", or "us" refer to the Operator and references to "the customer", "you" or "your" refer to the end user.',
        "items" => array(
            array("topic" => 'Players must agree to {{@site.name}} Terms & Conditions and General House Rules.',
                "paragraphs" => array()),
            array("topic" => '{{@site.name}} may suspend, exclude or cancel the Player\'s Account immediately for any breach of the Player\'s obligations under the Terms & Conditions, and Skill Games House Rules.',
                "paragraphs" => array()),
            array("topic" => 'Player must be at least 18 years old to register and participate in {{@site.name}}.',
                "paragraphs" => array()),
            array("topic" => 'Each Player is ONLY allowed to register 1 account in {{@site.name}} and is prohibited to register multiple accounts. Any attempt to breach or violate this policy will result in suspension/exclusion of the Player\'s Account.',
                "paragraphs" => array()),
            array("topic" => '{{@site.name}} will not tolerate any kind of collusive activity or any other form of cheating. These may include but not limited to sharing of information between Players to gain advantage over other Players, chips dumping (transferring of funds from one account to another account intentionally), soft-playing and using any artificial pot-building called bots are prohibited. Any attempt to breach or violate this policy will result in suspension and exclusion of the Player\'s Account. Player will be also automatically disqualified from the competitions/winnings. Players are highly expected to involve in stopping this violation simply by contacting {{@site.name}}\'s customer services and providing the Game ID and brief description of the event. {{@site.name}} will then observe it against our data and shall take any proper action.',
                "paragraphs" => array()),
            array("topic" => 'Malfunctions of any sort (software or hardware) will void play. This means that any stake placed will be returned irrespective of any indicative result.',
                "paragraphs" => array()),
            array("topic" => 'Players who for any reason do not act within the time given will time out the hand and the hand will be considered checked or folded. They will then be placed on sit out until they can return and continue to play.',
                "paragraphs" => array()),
            array("topic" => 'If player’s credits is less or equal to the Big Blind, a buy in window will pop-up. If the player does not buy-in before the game moves to the next hand, the system will then sit out the player until player has re-bought.',
                "paragraphs" => array()),
            array("topic" => 'Any intentional attempts to slow down the play at the Skill Games tables is not be permitted by {{@site.name}}.',
                "paragraphs" => array()),
            array("topic" => '{{@site.name}} reserves the right to start, stop, pause or resume any game at any time.',
                "paragraphs" => array()),
            array("topic" => 'Players who lose their connection to {{@site.name}}\'s Skill Games servers and are completely disconnected from the game server, the following will apply:',
                "paragraphs" => array(),
                "bullets" => array('If Player loses their connection during the play of the hand but is able to reconnect while it is still their turn to act, the Player may then act on their hand.', 'If a Player fails to act on their hand within the time given because they have been disconnected, then the hand will time out and the hand will be considered checked or folded. They will then be placed on sit out until they can return and continue to play.')),
            array("topic" => 'Chat Rules:',
                "paragraphs" => array(),
                "bullets" => array('{{@site.name}} encourages that all chat must be in English.', 'The chat feature may not be used to facilitate any collusive activities in any way.', 'Any language deemed as insulting, abusive, sexist, racist, or otherwise disrupting play are strictly prohibited.', 'Disclosure of cards either currently being held or folded while the hand is in play is forbidden.', 'No one may claim to be a representative of {{@site.name}}.', 'No advertising or "spamming" is permitted.')),
            array("topic" => '{{@site.name}} reserves the right to amend these Skill Games House Rules (including rectify errors or mistakes discovered on the Skill Games system) at any time without prior notice. However, {{@site.name}} will ensure that any significant changes to these Skill Games House Rules will be notified to the Player by an appropriate method (for example via a prominent notice on the Internet Gaming Service or the Gaming Platform). The changes will apply to the use of the Internet Gaming Service or the Gaming Platform after {{@site.name}} has given notice. If the Player does not wish to accept the new Skill Games House Rules the Player should not continue to use the Gaming Platform. If the Player continues to use the Gaming Platform after the date on which the change comes into effect, the Player\'s use of the Internet Gaming Service or the Gaming Platform will be confirmation of the Player\'s agreement to be bound by the new Skill Games House Rules.',
                "paragraphs" => array()),
        )
    ),
    '3' => array(
        "title" => 'Tangkas\' Rules',
        "description" => 'The following rules (the "Tangkas Rules") govern the end user\'s use of all the Operator\'s interactive games betting products and services (the "Tangkas") offered via the "TANGKAS" tab of {{@site.domains.desktop}} (the "Website"). They form part of the Operator\'s General Terms & Conditions, which apply to all the Games and which the customer must accept upon registering for any of the Games. In the case of any inconsistency between the General Terms and Conditions and any of these Games Betting Rules, the General Terms and Conditions shall prevail.</p><p>All bets on the Tangkas Games ("Tangkas Bets") accepted by the Operator are subject to these Tangkas Betting Rules.</p><p>Throughout these Tangkas Betting Rules, references to "we", "our", or "us" refer to the Operator and references to "the customer", "you" or "your" refer to the end user.',
        "items" => array(
            array("topic" => "Players must agree to {{@site.name}} Terms & Conditions and General House Rules.",
                "paragraphs" => array()),
            array("topic" => "{{@site.name}} may suspend, exclude or cancel the Player\'s Account immediately for any breach of the Player\'s obligations under the Terms & Conditions, and Tangkas’ Rules.",
                "paragraphs" => array()),
            array("topic" => "Player must be at least 18 years old to register and participate in {{@site.name}}",
                "paragraphs" => array()),
            array("topic" => "Each Player is ONLY allowed to register 1 account in {{@site.name}} and is prohibited to register multiple accounts. Any attempt to breach or violate this policy will result in suspension/exclusion of the Player\'s Account.",
                "paragraphs" => array()),
            array("topic" => "{{@site.name}} will not tolerate any kind of collusive activity or any other form of cheating. The use of artificial intelligence or \"bots\" on the Gaming Platform is strictly forbidden. Likewise, any application that uses any or all of the data contained on the Gaming Platform for purposes (including but not limited to commercial purposes) other than participating in the Gaming Platform (e.g. via screen scraping) is prohibited. Any attempt to breach or violate this policy will result in suspension and exclusion of the Player\'s Account. Player will be also automatically disqualified from the competitions/winnings.",
                "paragraphs" => array()),
            array("topic" => "Malfunctions of any sort (software or hardware) will void play. This means that any stake placed will be returned irrespective of any indicative result.",
                "paragraphs" => array()),
            array("topic" => "Customers wishing to bet or gamble with {{@site.name}} are warned that there may be specific laws in their own country, their place of residence, or the place where they currently are, which prohibit betting, gambling or gaming. Viewing or playing on {{@site.name}} website is not allowed wherever prohibited by any applicable law. Customers MUST abide by the relevant local laws at all times and if in any doubt, should obtain local advice as to the legal position. {{@site.name}} cannot, and does not, accept any responsibility for any breach of any local gambling or betting laws.",
                "paragraphs" => array()),
            array("topic" => "All customers must be eighteen (18) years of age or older to register to play {{@site.name}} & Tangkas games. It is an offense to play {{@site.name}} & Tangkas games if you are under the age of eighteen (18). It is also an offense to provide {{@site.name}} with false personal details and particulars in order to register with {{@site.name}} to play the {{@site.name}} & Tangkas games.",
                "paragraphs" => array()),
            array("topic" => "Customers accept sole responsibility for determining whether their activity is legal in the country or region where they live. By viewing and playing this website, indicates that players thoroughly understand and agree to the terms of this agreement and declare that they are in their legal age as determined by the laws of their country or region to participate in gaming activities.",
                "paragraphs" => array()),
            array("topic" => "{{@site.name}} has taken every precaution to ensure the security of customers\' account. However, {{@site.name}} cannot be responsible for the unauthorized use of customers\' account. Customers are responsible for keeping their privacy and security information, including their user names and passwords. All game results are considered valid when a correct username and password are logged-into the customer\'s account. Customers are not allowed to share or give away their own account and/or password. Your username and password are confidential and should not be disclosed to anybody. You shall be responsible for all transactions conducted in relation to your Player Account using your Password. All transactions being conducted with the correct Username and Password will be regarded as valid transactions.",
                "paragraphs" => array()),
            array("topic" => "{{@site.name}} reserves the rights to refuse/reject and suspend any customers who is being suspected of cheating, hacking, attacking or damaging our normal gaming operations without prior notification. {{@site.name}} also reserves the rights to modify the rules and conditions of any games, including table limits and game features. In the event customers suspect that their personal information is stolen, please inform us immediately to change the detail information.",
                "paragraphs" => array()),
            array("topic" => "In the event of system malfunction or damage which results in the loss of data, the management will try their best to recover all historic data and all recovered data will be deemed official.",
                "paragraphs" => array()),
            array("topic" => "Customers have the responsibility for checking their account balance before or after each gaming transaction and prior to departing from the game session. If customers suspect any errors, customers must report it immediately to the management before participating in the next or another game. Failure to do so will result in customers waiving their rights to raise future disputes and acceptance of all previous game records as true and correct.",
                "paragraphs" => array()),
            array("topic" => "In case of any dispute, customers acknowledge and agree that the management decisions are final and official.",
                "paragraphs" => array())
        ),
    ),
    '1' => array(
        "title" => 'Games\' Rules',
        "description" => 'The following rules (the "Games Rules") govern the end user\'s use of all the Operator\'s interactive games betting products and services (the "Games") offered via the "GAMES" tab of {{@site.domains.desktop}} (the "Website"). They form part of the Operator\'s General Terms & Conditions, which apply to all the Games and which the customer must accept upon registering for any of the Games. In the case of any inconsistency between the General Terms and Conditions and any of these Games Betting Rules, the General Terms and Conditions shall prevail.</p><p>All bets on the Games ("Games Bets") accepted by the Operator are subject to these Games Betting Rules.</p><p>Throughout these Games Betting Rules, references to "we", "our", or "us" refer to the Operator and references to "the customer", "you" or "your" refer to the end user.',
        "items" => array(
            array("topic" => "Liability",
                "paragraphs" => array('The Player shall have no cause of action and hereby waives any rights or claims against the software developer of the Casino Games for any matter, cause or thing involving the Player’s participation in the Casino Games or otherwise.')),
            array("topic" => "Play for fun",
                "paragraphs" => array('The Player agrees that the Casino Games are for entertainment value only. The Player understands and acknowledges that no monetary bet is necessary or required to play the Casino Games. If the Player wishes to play without betting money, they may do so in the "demo play" area only.')),
            array("topic" => "Personal use only",
                "paragraphs" => array('The Player\'s interest in the Casino and the Website is personal and not professional. A Player entering the Website does so solely for their own personal entertainment and any other entrance, access, use or re-use of the Casino Games is strictly prohibited.')),
            array("topic" => "Malfunctions",
                "paragraphs" => array('Unless otherwise specified, malfunctions of any sort (software or hardware) will void play. This means that any stake placed will be returned irrespective of any indicative result.')),
            array("topic" => "Smart Player and Artificial Intelligence",
                "paragraphs" => array('{{@site.name}} reserves the right to reject SMART players or any suspected SMART players and Card Counters. Any activities using artificial intelligence called bots are strictly prohibited. Any attempt to breach or violate this policy will result in suspension and exclusion of the Player\'s Account. All winnings and commissions will be forfeited.')),
            array("topic" => "Complaints for random number generated gaming",
                "paragraphs" => array('If a Player has any complaints about any aspect of the Casino Games, they should submit the nature such complaint within 14 (fourteen) days of the incident occurring to ' . Config::get("settings.EMAIL_SENDER") . ' together with their User ID, the time and date of playing and any further information that may be relevant. Please note that the Operator will investigate the complaint fully using data from the Operator’s servers but the ultimate decision on the complaint rests with the Operator acting reasonably in light of all the available evidence.')),
            array("topic" => "Maximum win",
                "paragraphs" => array('There is no maximum gross win in any day.')),
        )
    ),
    '4' => array(
        "title" => 'Live Togel\' Rules',
        "description" => 'The following rules (the "Live Togel Rules") govern the end user\'s use of all the Operator\'s interactive games betting products and services (the "Live Togel") offered via the "Live Togel" tab of {{@site.domains.desktop}} (the "Website"). They form part of the Operator\'s General Terms & Conditions, which apply to all the Games and which the customer must accept upon registering for any of the Games. In the case of any inconsistency between the General Terms and Conditions and any of these Games Betting Rules, the General Terms and Conditions shall prevail.</p><p>All bets on the Live Togel ("Live Togel Bets") accepted by the Operator are subject to these Live Togel Betting Rules.</p><p>Throughout these Live Togel Betting Rules, references to "we", "our", or "us" refer to the Operator and references to "the customer", "you" or "your" refer to the end user.',
        "items" => array(
            array("topic" => "Liability",
                "paragraphs" => array('The Player shall have no cause of action and hereby waives any rights or claims against the software developer of the Casino Games for any matter, cause or thing involving the Player’s participation in the Casino Games or otherwise.')),
            array("topic" => "Play for fun",
                "paragraphs" => array('The Player agrees that the Casino Games are for entertainment value only. The Player understands and acknowledges that no monetary bet is necessary or required to play the Casino Games. If the Player wishes to play without betting money, they may do so in the "demo play" area only.')),
            array("topic" => "Personal use only",
                "paragraphs" => array('The Player\'s interest in the Casino and the Website is personal and not professional. A Player entering the Website does so solely for their own personal entertainment and any other entrance, access, use or re-use of the Casino Games is strictly prohibited.')),
            array("topic" => "Malfunctions",
                "paragraphs" => array('Unless otherwise specified, malfunctions of any sort (software or hardware) will void play. This means that any stake placed will be returned irrespective of any indicative result.')),
            array("topic" => "Physical Malfunctions",
                "paragraphs" => array('The Operator reserves the rights to re-draw or correct the winning numbers or declare the drawing number is invalid if any human or non-human errors occur during the drawing number process or during the input result process.')),
            array("topic" => "Smart Player and Artificial Intelligence",
                "paragraphs" => array('{{@site.name}} reserves the right to reject SMART players or any suspected SMART players. Any activities using artificial intelligence called bots are strictly prohibited. Any attempt to breach or violate this policy will result in suspension and exclusion of the Player\'s Account. All winnings and commissions will be forfeited.')),
            array("topic" => "Maximum win",
                "paragraphs" => array('There is no maximum gross win in any day.')),
            array("topic" => "Complaints for live gaming",
                "paragraphs" => array('If a Player wishes to make a complaint or dispute a live Casino Game result, they must provide the Operator with their user ID the time of playing, the dealer\'s name, the Table ID and Round ID at the time of contacting the Operator. Failure to do so may result in the complaint being unable to be addressed by the Operator. Video images of live Casino Games is kept for 24 hours and therefore Players must address their complaint within 24 hours of the dispute occurring. Any complaints submitted after 24 hours will be rejected by the Operator due to the absence of video evidence.</p><p>In case of any dispute, the Player acknowledges and agrees that the Operator’s decision is final and official.')),
            array("topic" => "Live Casino Games",
                "paragraphs" => array('For Live Casino Games, valid results on the Casino Games are those results which are detected by the electronic sensor equipment installed for that purpose. If for any reason a result is not detected and registered by the electronic sensors, then that result is deemed to have not occurred, and any bets locked will remain locked until a valid result is determined.')),
        )
    )
);
    return require '../../'.PROJECT_DIR.'/front_resources/lang/'.Lang::locale().'/'.basename(__FILE__);