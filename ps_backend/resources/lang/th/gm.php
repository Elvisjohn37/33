<?php
return array(
    '0' => array(
        "title" => 'กฎการเล่นทั่วไป',
        "description" => 'กฎต่อไปนี้ (“กฎการเล่น”) ควบคุมการใช้งานผลิตภัณฑ์คาสิโนแบบโต้ตอบ (หรือ {{@site.name}}) และบริการ (“เกมคาสิโน”) ของผู้ประกอบการทั้งหมดที่ให้บริการที่ {{@site.domains.desktop}} (“เว็บไซต์”) ต่อผู้ใช้ (“ผู้เล่น”, “คุณ” หรือ “ของคุณ”) '
        . 'โดยรวมเป็นส่วนหนึ่งของข้อตกลงและเงื่อนไขของผู้ประกอบการ ซึ่งบังคับใช้กับเกมคาสิโนทั้งหมดที่ผู้ประกอบการนำเสนอ และผู้เล่นต้องยอมรับเมื่อลงทะเบียนสำหรับเกมคาสิโนใดๆ '
        . 'หากมีความไม่สอดคล้องกันระหว่างข้อตกลงและเงื่อนไข กับกฎการเล่นใดๆ เหล่านี้ ข้อตกลงและเงื่อนไขจะมีผลเหนือกว่า',
        "items" => array(
            array("topic" => 'วันที่กฎมีผลบังคับใช้',
                "paragraphs" => array('กฎเหล่านี้จะมีผลบังคับใช้เมื่อวันที่ {{@lang.custom.help.gamerules_effective_date}}.')),
            array("topic" => 'ความไม่สอดคล้องกันระหว่างภาษาอังกฤษกับภาษาอื่นๆ',
                "paragraphs" => array('ในกรณีที่เกิดความไม่สอดคล้องกันในกฎการเดิมพันของเกมระหว่างเวอร์ชันภาษาอังกฤษกับในภาษาอื่นๆ เวอร์ชันภาษาอังกฤษจะมีผลเหนือกว่า')),
            array("topic" => 'การตีความกฎต่างๆ',
                "paragraphs" => array('หากมีข้อพิพาทใดๆ ในการตีความกฎการเดิมพันของเกมเหล่านี้ การตีความกฎของผู้ประกอบการมีผลเหนือกว่า')),
            array("topic" => 'วิธีการวางเดิมพัน',
                "paragraphs" => array('ผู้ประกอบการจะรับเดิมพันผ่านทางออนไลน์เท่านั้น '
                    . 'จะไม่ยอมรับวิธีการวางเดิมพันอื่นๆ (เช่น เดิมพันทางโทรศัพท์)')),
            array("topic" => 'เดิมพันที่ถูกต้อง',
                "paragraphs" => array('จะถือว่าเดิมพันได้รับการยอมรับต่อเมื่อผู้ประกอบการได้มีการจัดสรรเลขประจำตัว (“ID เดิมพัน”) แล้ว')),
            array("topic" => 'ประวัติการวางเดิมพัน',
                "paragraphs" => array('หากต้องการดูรายละเอียดเดิมพันของคุณ ให้คุณตรวจสอบคำชี้แจงที่มีอยู่ภายในเกมที่เกี่ยวข้อง')),
            array("topic" => 'ระบบกำหนดตัวเลขแบบสุ่ม',
                "paragraphs" => array('เพื่อให้มั่นใจว่าเกมมีความโปร่งใสและยุติธรรม ผลทั้งหมดเกิดจากระบบกำหนดตัวเลขแบบสุ่ม (“RNG”) '
                    . 'ระเบียบวิธี RNG ของผู้ประกอบการได้รับการประเมินและได้รับการรองรับโดยบริษัททดสอบอิสระของบุคคลที่สาม')),
            array("topic" => 'การเชื่อมต่อล้มเหลวระหว่างการเล่น',
                "paragraphs" => array('‘เสมอ’ เป็นการเลือกผลสำหรับการเล่นเกมบนพื้นฐานแบบสุ่ม '
                    . 'หากขาดการเชื่อมต่อระหว่างเล่นเกมแบบจั่วตามต้องการ (Draw On Demand) ที่มีขั้นตอนเดียว เกมจะจัดการให้คุณโดยอัตโนมัติ '
                    . 'หากขาดการเชื่อมต่อระหว่างเล่นเกมแบบจั่วตามต้องการที่มีหลายขั้นตอน (เช่น สล็อตแมชชีนที่มีรอบโบนัสหรือรอบพิเศษ) และเกมของคุณอยู่ในขั้นตอนที่สอง รายละเอียดเกมของคุณจะถูกเก็บไว้เพื่อให้คุณกลับเข้าสู่ระบบมาเล่นต่อ '
                    . 'หากเกมของคุณยังไม่ได้เข้าสู่ขั้นตอนที่สองก่อนที่จะขาดการเชื่อมต่อ เกมจะจัดการให้คุณโดยอัตโนมัติ',
                    'หากขาดการเชื่อมต่อระหว่างเกมจั่วตามต้องการจะมีการจัดการให้เฉพาะเกมในปัจจุบันเท่านั้น '
                    . 'หากมีการใช้ หมุนอัตโนมัติ โดยลูกค้าเพื่อระบุเกมต่อไป เกมที่อยู่ระหว่างรอดำเนินการจะถูกยกเลิก '
                    . 'ไม่มีการตัดเงินจากบัญชีของลูกค้าเมื่อยกเลิกเกมที่อยู่ระหว่างรอดำเนินการ',
                    'หมายเหตุ: ในกรณีที่ขาดการเชื่อมต่อในเกมจั่วตามต้องการ คุณสามารถดูรายละเอียดการเดิมพันของคุณได้ผ่านทางประวัติในเกมดังกล่าวเมื่อคุณเชื่อมต่อกลับเข้ามาอีกครั้ง')),
            array("topic" => 'การจำกัดเดิมพัน',
                "paragraphs" => array('การจำกัดเดิมพันจะมีรายละเอียดอยู่ในไฟล์ช่วยเหลือของแต่ละเกม '
                    . 'ดู กฎการเดิมพันเฉพาะ สำหรับแต่ละเกม (ข้อที่ 14 ด้านล่าง)')),
            array("topic" => 'เดิมพันเป็นโมฆะ',
                "paragraphs" => array('ในกรณีที่การเดิมพันถูกประกาศว่าเป็นโมฆะ จะมีการคืนเงินเดิมพันเดิมให้',
                    'ความบกพร่องส่งผลให้การจ่ายเงินและการเล่นทั้งหมดเป็นโมฆะ',
                    '‘ความบกพร่องทางเทคนิคใดๆ รวมถึงแต่ไม่จำกัดเฉพาะซอฟต์แวร์ ฮาร์ดแวร์ หรือความผิดปกติจากการเชื่อมต่อ จะส่งผลให้เดิมพันที่ได้รับผลกระทบทั้งหมดถูกประกาศว่าเป็นโมฆะ โดยไม่คำนึงถึงผลใดๆ',
                    'เรายังขอสงวนสิทธิ์ในการประกาศเดิมพันเป็นโมฆะหาก:'),
                "bullets" => array('มีรายละเอียดเดิมพันเพียงบางส่วนหรือไม่ถูกต้อง;',
                    'เรามีข้อสงสัยใดๆ ว่าลูกค้ามีส่วนร่วมในการทุจริต สมรู้ร่วมคิด ฟอกเงิน หรือกิจกรรมที่ผิดกฎหมายอื่นใด;',
                    'เราเชื่อว่าลูกค้ามีอายุต่ำกว่า 18 ปีหรือมีอายุทางกฎหมายต่ำกว่าที่จำเป็นสำหรับการเล่นการพนัน;',
                    'การยอมรับเดิมพันที่เกินขีดจำกัดของเกม',
                    'จำนวนเงินเดิมพันไม่มีอยู่ในบัญชีของลูกค้า',
                    'ข้อผิดพลาด (จากมนุษย์หรืออื่นใด) หรือระบบล้มเหลวส่งผลให้มีการใช้อัตราต่อรองที่ไม่ถูกต้อง',
                    'ความผิดพลาดทางเทคนิค เช่น ความล้มเหลวในการสื่อสาร ข้อบกพร่อง การหยุดชะงัก ความล่าช้า ไวรัส การโจมตีให้ใช้บริการไม่ได้ หรือเกิดความเสียหายต่อข้อมูล; หรือ',
                    'หากข้อบกพร่องทางเทคนิคทำให้มีเงินรางวัลมากเกินไปหรือผิดปกติ'),
                "paragraphs-sub" => array('หากเดิมพันถูกตัดสินอย่างไม่ถูกต้องเนื่องจากข้อผิดพลาด (จากมนุษย์หรืออื่นใด) หรือระบบล้มเหลว จะถือว่าการชำระเงินไม่ถูกต้องและจะต้องคืนเงิน '
                    . 'หากลูกค้ามีเงินในบัญชีไม่เพียงพอสำหรับคืน ลูกค้าจะต้องส่งคืนเงินตามจำนวนนั้นให้ผู้ประกอบการ')),
            array("topic" => 'การใช้งานเกม',
                "paragraphs" => array('เกมใช้สำหรับความบันเทิงส่วนบุคคลแต่เพียงผู้เดียว '
                    . 'ห้ามใช้งานเกมหรือองค์ประกอบอื่นๆ ที่เกี่ยวข้อง (เช่น กราฟิก ข้อความ เป็นต้น) รวมถึงแต่ไม่จำกัดเฉพาะการใช้งานเพื่อธุรกิจหรือมืออาชีพ และเป็นการละเมิดโดยตรงในกรรมสิทธิ์และสิทธิ์อื่นๆ ของผู้ประกอบการ ผู้ที่อาจดำเนินการทางกฎหมายใดๆ ในการบังคับใช้สิทธิ์ดังกล่าว')),
            array("topic" => 'การร้องเรียน',
                "paragraphs" => array('หากคุณมีข้อร้องเรียนเกี่ยวกับผลของการเดิมพันในเกมใดๆ คุณต้องติดต่อทีมงานฝ่ายสนับสนุนลูกค้าภายใน 72 ชั่วโมงทางอีเมล เขียนคำร้องเรียนของคุณให้ครบถ้วน รวมถึงรายละเอียดด้านเวลา <a href="mailto:' . Config::get("settings.EMAIL_SENDER") . '">' . Config::get("settings.EMAIL_SENDER") . '</a> '
                    . 'ผู้ประกอบการจะตรวจสอบการร้องเรียนโดยใช้ข้อมูลจากระบบของพวกเขาเอง '
                    . 'คุณยอมรับว่าอำนาจการตัดสินขาดในเรื่องที่ร้องเรียนเป็นของผู้ประกอบการ ซึ่งจะทำหน้าที่อย่างเหมาะสมตามหลักฐานทั้งหมดที่มีอยู่ และถือเป็นที่สิ้นสุด')),
            array("topic" => 'ความรับผิด',
                "paragraphs" => array(
                    'คุณยอมรับว่าคุณไม่มีอำนาจฟ้องร้อง และขอสละสิทธิ์หรือการเรียกร้องใดๆ ต่อผู้พัฒนาซอฟต์แวร์ของเกมสำหรับเรื่อง มูลเหตุ หรือสิ่งที่เกี่ยวข้องใดๆ กับการมีส่วนร่วมในเกมของคุณหรืออื่นใด ',
                    'คุณยอมรับว่าคุณไม่มีอำนาจฟ้องร้อง และขอสละสิทธิ์หรือการเรียกร้องใดๆ ต่อผู้ประกอบการในเรื่องของการใช้งาน การใช้งานในทางที่ผิด หรือการละเมิดโดยคุณหรือโดยบุคคลที่สามในบัญชีของคุณ หรือในส่วนของการดำเนินการต่อลูกค้ารายอื่น หรือในส่วนของความผิดพลาดทางเทคนิคหรือข้อบกพร่องใดๆ รวมถึงแต่ไม่จำกัดเฉพาะที่ระบุไว้ในข้อ 10 ดังกล่าวข้างต้น ',
                    'แม้ว่าผู้ประกอบการจะปฏิบัติตามขั้นตอนที่เหมาะสมในการให้ความร่วมมือกับลูกค้าที่มีความประสงค์จะพักการใช้งาน ลูกค้าขอยอมรับว่าผู้ประกอบการจะไม่รับผิดหากลูกค้าที่พักการใช้งานไปเล่นการพนันกับผู้ประกอบการรายอื่น หรือหากลูกค้าที่พักการใช้งานยังคงเล่นการพนันกับผู้ประกอบการโดยใช้บัญชีที่มีรายละเอียดการสมัครที่แตกต่างกันหรือเปลี่ยนแปลงใหม่ หรือหากลูกค้าพยายามหลบเลี่ยงผลของการพักการใช้งาน')),
            array("topic" => 'กฎการเดิมพันเฉพาะ',
                "paragraphs" => array('กฎการเดิมพันเฉพาะสำหรับเกมที่มีให้บริการในเว็บไซต์สามารถดูได้จากลิงค์ดังต่อไปนี้',
                     "<span class='ps_js-game_guide_menu'></span>")),
            array("topic" => 'การเปลี่ยนแปลงกฎเหล่านี้',
                "paragraphs" => array('ผู้ประกอบการมีสิทธิ์ในการเปลี่ยนกฎการเดิมพันเหล่านี้ (รวมถึงกฎการเดิมพันเฉพาะใดๆ) ได้ทุกเมื่อโดยสอดคล้องกับข้อตกลงและเงื่อนไขทั่วไป'))
        )
    ),
    '2' => array(
        "title" => 'กฎการเล่นเกมที่ใช้ทักษะ',
        "description" => 'กฎต่อไปนี้ (“กฎการเล่นเกมที่ใช้ทักษะ”) ควบคุมการใช้งานผลิตภัณฑ์แบบโต้ตอบที่มีการวางเดิมพันและบริการ (“เกมใช้ทักษะ”) ของผู้ประกอบการทั้งหมดที่ให้บริการในแท็บ “เกมใช้ทักษะ” ของ {{@site.domains.desktop}} (“เว็บไซต์”) ต่อผู้ใช้. '
        . 'โดยรวมเป็นส่วนหนึ่งของข้อตกลงและเงื่อนไขทั่วไปของผู้ประกอบการ ซึ่งบังคับใช้กับเกมทั้งหมดและลูกค้าต้องยอมรับเมื่อลงทะเบียนสำหรับเกมใดๆ. '
        . 'ในกรณีที่มีความไม่สอดคล้องกันระหว่างข้อตกลงและเงื่อนไขทั่วไป กับกฎการเดิมพันใดๆ เหล่านี้ ข้อตกลงและเงื่อนไขทั่วไปจะมีผลเหนือกว่า</p><p>'
        . 'Aการเดิมพันทั้งหมดในเกมใช้ทักษะ (“การเดิมพันในเกมใช้ทักษะ”) ที่ยอมรับโดยผู้ประกอบการจะอยู่ภายใต้กฎการเดิมพันเหล่านี้</p><p>'
        . 'Tตลอดทั้งหมดในกฎการเดิมพันในเกมใช้ทักษะเหล่านี้ การอ้างอิงถึง “เรา” “ของเรา” หรือ “พวกเรา” หมายถึงผู้ประกอบการ และการอ้างอิงถึง “ลูกค้า” “คุณ” หรือ “ของคุณ” หมายถึงผู้ใช้',
        "items" => array(
            array("topic" => 'ผู้เล่นต้องยอมรับข้อตกลงและเงื่อนไขและกฎทั่วไปของเจ้าของ',
                "paragraphs" => array()),
            array("topic" => '{{@site.name}} อาจระงับ พักการใช้งาน หรือยกเลิกบัญชีของผู้เล่นได้ในทันทีหากมีการละเมิดข้อภาระผูกพันของผู้เล่นตามข้อตกลงและเงื่อนไขและกฎของเจ้าของเกมใช้ทักษะ',
                "paragraphs" => array()),
            array("topic" => 'ผู้เล่นจะต้องมีอายุ 18 ปีขึ้นไปในการสมัครและมีส่วนร่วมใน {{@site.name}}.',
                "paragraphs" => array()),
            array("topic" => 'ผู้เล่นแต่ละคนได้รับอนุญาตให้สมัครเพียง 1 บัญชีเท่านั้นใน {{@site.name}} และห้ามสมัครหลายบัญชี. การฝ่าฝืนหรือละเมิดนโยบายนี้จะมีผลให้เกิดการระงับ/พักการใช้งานบัญชีของผู้เล่น',
                "paragraphs" => array()),
            array("topic" => '{{@site.name}} จะไม่ยอมให้มีการสมรู้ร่วมคิดหรือการโกงรูปแบบอื่นใด. ซึ่งอาจรวมถึงแต่ไม่จำกัดเพียงการแบ่งปันข้อมูลระหว่างผู้เล่นเพื่อให้ได้เปรียบกว่าผู้เล่นอื่น การมอบชิปให้ (การโอนเงินจากบัญชีหนึ่งไปยังบัญชีอื่นโดยเจตนา) การอ่อนข้อให้ และการใช้โปรแกรมการสะสมเงินกองกลางที่เรียกว่าบอท การฝ่าฝืนหรือละเมิดนโยบายนี้จะมีผลให้เกิดการระงับและพักการใช้งานบัญชีของผู้เล่น. ผู้เล่นจะถูกตัดสิทธิ์จากการแข่งขัน/เงินรางวัลโดยอัตโนมัติด้วย. เราคาดหวังเป็นอย่างสูงว่าผู้เล่นจะมีส่วนร่วมในการหยุดการละเมิดนี้ เพียงติดต่อฝ่ายบริการลูกค้าและแจ้ง ID เกมและคำอธิบายสั้นๆ ถึงเหตุการณ์ดังกล่าว จากนั้นเราจะตรวจสอบกับข้อมูลของเราและจะดำเนินการตามความเหมาะสม',
                "paragraphs" => array()),
            array("topic" => 'ความบกพร่องของซอฟต์แวร์หรือฮาร์ดแวร์จะทำให้การเล่นเป็นโมฆะ. ซึ่งหมายความว่าผู้เล่นจะได้รับเดิมพันคืนโดยไม่คำนึงถึงผลลัพธ์ที่ออกมา.',
                "paragraphs" => array()),
            array("topic" => 'ผู้เล่นที่ไม่ดำเนินการภายในเวลาที่กำหนดด้วยเหตุผลใดก็ตามจะหมดเวลาและถือว่าผู้เล่นผ่านหรือหมอบ. Tจากนั้นจะเปลี่ยนสถานะเป็น นั่งดู จนกว่าจะกลับมาและเริ่มเล่นต่อ.',
                "paragraphs" => array()),
            array("topic" => 'หากเครดิตของผู้เล่นน้อยกว่าหรือเท่าบิ๊กบลายด์ หน้าต่างซื้อชิปเข้าเล่นจะปรากฏขึ้นมา. หากผู้เล่นไม่ซื้อชิปเข้าเล่นก่อนจะเข้าเกมถัดไป ระบบจะเปลี่ยนสถานะเป็น นั่งดู จนกว่าผู้เล่นจะซื้อชิปเข้ามาเพิ่ม.',
                "paragraphs" => array()),
            array("topic" => 'ไม่อนุญาตให้พยายามทำการใดๆ โดยมีเจตนาถ่วงเวลาในการเล่นที่โต๊ะเกมใช้ทักษะ {{@site.name}}.',
                "paragraphs" => array()),
            array("topic" => '{{@site.name}} มีสิทธิ์ในการเริ่ม หยุด หยุดชั่วคราว หรือเริ่มต้นอีกครั้งในเกมใดๆ ในเวลาใดก็ได้.',
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
        "title" => 'กฎของ Tangkas',
        "description" => 'กฎต่อไปนี้ (“กฎของ Tangkas”) ควบคุมการใช้งานผลิตภัณฑ์แบบโต้ตอบที่มีการวางเดิมพันและบริการ (“Tangkas”) ของผู้ประกอบการทั้งหมดที่ให้บริการในแท็บ “TANGKAS” ของ  {{@site.domains.desktop}} (“เว็บไซต์”) ต่อผู้ใช้. โดยรวมเป็นส่วนหนึ่งของข้อตกลงและเงื่อนไขทั่วไปของผู้ประกอบการ ซึ่งบังคับใช้กับเกมทั้งหมดและลูกค้าต้องยอมรับเมื่อลงทะเบียนสำหรับเกมใดๆ. ในกรณีที่มีความไม่สอดคล้องกันระหว่างข้อตกลงและเงื่อนไขทั่วไป กับกฎการเดิมพันใดๆ เหล่านี้ ข้อตกลงและเงื่อนไขทั่วไปจะมีผลเหนือกว่า.</p><p>การเดิมพันทั้งหมดในเกม Tangkas (“การเดิมพันใน Tangkas”) ที่ยอมรับโดยผู้ประกอบการจะอยู่ภายใต้กฎการเดิมพันเหล่านี้ของ Tangkas.</p><p>ตลอดทั้งหมดในกฎการเดิมพันเหล่านี้ของ Tangkas การอ้างอิงถึง “เรา” “ของเรา” หรือ “พวกเรา” หมายถึงผู้ประกอบการ และการอ้างอิงถึง “ลูกค้า” “คุณ” หรือ “ของคุณ” หมายถึงผู้ใช้.',
        "items" => array(
            array("topic" => "ผู้เล่นต้องยอมรับข้อตกลงและเงื่อนไขและกฎทั่วไปของเจ้าของ.",
                "paragraphs" => array()),
            array("topic" => "{{@site.name}} อาจระงับ พักการใช้งาน หรือยกเลิกบัญชีของผู้เล่นได้ในทันทีหากมีการละเมิดข้อภาระผูกพันของผู้เล่นตามข้อตกลงและเงื่อนไขและกฎของ Tangkas.",
                "paragraphs" => array()),
            array("topic" => "ผู้เล่นจะต้องมีอายุ 18 ปีขึ้นไปในการสมัครและมีส่วนร่วมใน  {{@site.name}}",
                "paragraphs" => array()),
            array("topic" => "ผู้เล่นแต่ละคนได้รับอนุญาตให้สมัครเพียง 1 บัญชีเท่านั้นใน {{@site.name}} และห้ามสมัครหลายบัญชี. ความพยายามที่จะฝ่าฝืนหรือละเมิดนโยบายนี้จะมีผลในการระงับ/พักการใช้งานบัญชีของผู้เล่น.",
                "paragraphs" => array()),
            array("topic" => "{{@site.name}} จะไม่ยอมให้มีการสมรู้ร่วมคิดหรือการโกงรูปแบบอื่นใด. ห้ามใช้งานปัญญาประดิษฐ์หรือ “บอท” บนแพลตฟอร์มเกม. นอกจากนี้ ห้ามใช้แอปพลิเคชันใดที่ใช้ข้อมูลบางส่วนหรือทั้งหมดที่มีอยู่บนแพลตฟอร์มเกมเพื่อวัตถุประสงค์อื่น (รวมถึงแต่ไม่จำกัดเพียงวัตถุประสงค์ในเชิงพาณิชย์) นอกเหนือจากการเข้าร่วมในแพลตฟอร์มเกม (เช่น การสเครปปิงข้อมูล). การฝ่าฝืนหรือละเมิดนโยบายนี้จะมีผลให้เกิดการระงับและพักการใช้งานบัญชีของผู้เล่น. ผู้เล่นจะถูกตัดสิทธิ์จากการแข่งขัน/เงินรางวัลโดยอัตโนมัติด้วย.",
                "paragraphs" => array()),
            array("topic" => "ความบกพร่องของซอฟต์แวร์หรือฮาร์ดแวร์จะทำให้การเล่นเป็นโมฆะ. ซึ่งหมายความว่าผู้เล่นจะได้รับเดิมพันคืนโดยไม่คำนึงถึงผลลัพธ์ที่ออกมา.",
                "paragraphs" => array()),
            array("topic" => "ลูกค้าที่ต้องการที่จะเดิมพันหรือเล่นการพนันกับ{{@site.name}} จะได้รับคำเตือนว่าอาจมีกฎหมายเฉพาะในประเทศของตนเอง สถานที่พำนักอาศัย หรือสถานที่ที่อยู่ในปัจจุบัน ซึ่งห้ามมิให้มีการเดิมพัน การพนัน หรือการเล่นเกม. ไม่อนุญาตให้มีการชมหรือเล่นเว็บไซต์ {{@site.name}} ในที่ใดก็ตามที่ต้องห้ามตามกฎหมายที่ใช้บังคับ. ลูกค้าจะต้องปฏิบัติตามกฎหมายท้องถิ่นตลอดเวลา และหากมีข้อสงสัยใดๆ ควรขอคำแนะนำในท้องถิ่นเพื่อไม่ให้ขัดต่อกฎหมาย. {{@site.name}} ไม่สามารถ และไม่รับผิดชอบการละเมิดกฎหมายการพนันหรือการเดิมพันท้องถิ่นใดๆ.",
                "paragraphs" => array()),
            array("topic" => "ลูกค้าทุกท่านจะต้องมีอายุสิบแปด (18) ปีขึ้นไปในการลงทะเบียนเพื่อเล่นเกม {{@site.name}} & Tangkas. และจะเป็นความผิดในการเล่นเกม {{@site.name}} & Tangkas หากคุณอายุต่ำกว่าสิบแปด (18) ปี. นอกจากนี้ยังเป็นความผิดในการให้รายละเอียดส่วนบุคคลที่เป็นเท็จกับ {{@site.name}} และรายละเอียดที่เป็นเท็จในการลงทะเบียนกับ {{@site.name}} เพื่อเล่นเกม {{@site.name}} & Tangkas.",
                "paragraphs" => array()),
            array("topic" => "เป็นหน้าที่ของลูกค้าแต่เพียงผู้เดียวในการพิจารณาว่าการกระทำของลูกค้าถูกต้องตามกฎหมายในประเทศหรือภูมิภาคที่ลูกค้าอาศัยอยู่หรือไม่. การชมและเล่นเว็บไซต์นี้บ่งชี้ว่าผู้เล่นเข้าใจอย่างถี่ถ้วนและยอมรับเงื่อนไขของข้อตกลงนี้ และปฏิญาณว่าผู้เล่นมีนิติภาวะตามที่กำหนดในกฎหมายของประเทศหรือภูมิภาคของผู้เล่นในการมีส่วนร่วมในกิจกรรมการเล่นเกม.",
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
        "title" => 'กฎการเล่น',
        "description" => 'กฎต่อไปนี้ (“กฎการเล่น”) ควบคุมการใช้งานผลิตภัณฑ์แบบโต้ตอบที่มีการวางเดิมพันและบริการ (“เกม”) ของผู้ประกอบการทั้งหมดที่ให้บริการผ่าน “เกม” (“เว็บไซต์”) ต่อผู้ใช้ {{@site.domains.desktop}} (the "Website"). โดยรวมเป็นส่วนหนึ่งของข้อตกลงและเงื่อนไขทั่วไปของผู้ประกอบการ ซึ่งบังคับใช้กับเกมทั้งหมดและลูกค้าต้องยอมรับเมื่อลงทะเบียนสำหรับเกมใดๆ. ในกรณีที่มีความไม่สอดคล้องกันระหว่างข้อตกลงและเงื่อนไขทั่วไป กับกฎการเดิมพันใดๆ เหล่านี้ ข้อตกลงและเงื่อนไขทั่วไปจะมีผลเหนือกว่า.</p><p>การเดิมพันทั้งหมดในเกม (“การเดิมพันในเกม”) ที่ยอมรับโดยผู้ประกอบการจะอยู่ภายใต้กฎการเดิมพันเหล่านี้.</p><p>ตลอดทั้งหมดในกฎการเดิมพันในเกมเหล่านี้ การอ้างอิงถึง “เรา” “ของเรา” หรือ “พวกเรา” หมายถึงผู้ประกอบการ และการอ้างอิงถึง “ลูกค้า” “คุณ” หรือ “ของคุณ” หมายถึงผู้ใช้.',
        "items" => array(
            array("topic" => "ความรับผิด",
                "paragraphs" => array('ผู้เล่นไม่มีอำนาจฟ้องร้อง และขอสละสิทธิ์หรือการเรียกร้องใดๆ ต่อผู้พัฒนาซอฟต์แวร์ของเกมคาสิโนสำหรับเรื่อง มูลเหตุ หรือสิ่งที่เกี่ยวข้องใดๆ กับการมีส่วนร่วมในเกมคาสิโนของคุณหรืออื่นใด.')),
            array("topic" => "เล่นเพื่อความสนุก",
                "paragraphs" => array('ผู้เล่นยอมรับว่าเล่นเกมคาสิโนเพื่อความบันเทิงเท่านั้น. ผู้เล่นเข้าใจและทราบว่าไม่จำเป็นต้องเดิมพันด้วยเงินหรือจำเป็นต่อการเล่นเกมคาสิโน. หากผู้เล่นมีความประสงค์ที่จะเล่นโดยไม่มีเงินเดิมพัน ผู้เล่นสามารถทำได้ในส่วน “สาธิตการเล่น” เท่านั้น.')),
            array("topic" => "การใช้ส่วนบุคคลเท่านั้น",
                "paragraphs" => array('ความสนใจของผู้เล่นในคาสิโนและเว็บไซต์เป็นความสนใจส่วนบุคคลและไม่ได้ทำเป็นอาชีพ. ผู้เล่นเข้าสู่เว็บไซต์เพื่อความบันเทิงส่วนบุคคลเพียงอย่างเดียว และทางเข้า การเข้าถึง การใช้งาน หรือการใช้งานซ้ำอื่นๆ ในเกมคาสิโนเป็นข้อห้ามโดยเด็ดขาด.')),
            array("topic" => "การทำงานผิดปกติ",
                "paragraphs" => array('เว้นเสียแต่จะระบุไว้เป็นอื่น การทำงานผิดปกติ (ซอฟต์แวร์หรือฮาร์ดแวร์) ใดๆ จะทำให้การเล่นเป็นโมฆะ. ซึ่งหมายความว่าเงินเดิมพันจะคืนกลับให้โดยไม่คำนึงถึงผลที่ออกมาใดๆ.')),
            array("topic" => "ผู้เล่น SMART และปัญญาประดิษฐ์",
                "paragraphs" => array('{{@site.name}} มีสิทธิ์ที่จะปฏิเสธผู้เล่น SMART หรือสงสัยว่าเป็นผู้เล่น SMART และการนับไพ่. การกระทำใดๆ ที่ใช้ปัญญาประดิษฐ์ที่เรียกว่าบอทเป็นข้อห้ามโดยเด็ดขาด. ความพยายามที่จะฝ่าฝืนหรือละเมิดนโยบายนี้จะมีผลในการระงับและพักการใช้งานบัญชีของผู้เล่น. Aเงินรางวัลและค่าคอมมิชชั่นจะถูกริบ.')),
            array("topic" => "การร้องเรียนในเกมที่มีการกำหนดตัวเลขแบบสุ่ม",
                "paragraphs" => array('หากผู้เล่นมีข้อร้องเรียนใดๆ เกี่ยวกับลักษณะของเกมคาสิโนใดๆ ผู้เล่นควรยื่นเรื่องร้องเรียนเรื่องดังกล่าวภายใน 14 (สิบสี่) วันนับจากเหตุการณ์ที่เกิดขึ้น ' . Config::get("settings.EMAIL_SENDER") . ' และพร้อมด้วย ID ผู้ใช้ เวลาและวันที่เล่น และข้อมูลเพิ่มเติมใดๆ ที่อาจเกี่ยวข้อง. โปรดทราบว่าผู้ประกอบการจะตรวจสอบการร้องเรียนโดยใช้ข้อมูลจากระบบของผู้ประกอบการ แต่อำนาจการตัดสินขาดในเรื่องที่ร้องเรียนเป็นของผู้ประกอบการ ซึ่งจะทำหน้าที่อย่างเหมาะสมตามหลักฐานทั้งหมดที่มีอยู่.')),
            array("topic" => "จำนวนครั้งการชนะสูงสุด",
                "paragraphs" => array('ไม่มีการจำกัดจำนวนครั้งการชนะสูงสุดใดๆ.')),
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