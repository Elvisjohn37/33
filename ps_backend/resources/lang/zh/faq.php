<?php
    return array('0'=>array(
                                array('我的个人信息在您的网站上是否安全？'=>'我们的 {{@site.name}} 网站使用最新的加密和保护技术，以确保您的个人信息和个人财务信息完全安全'),
                                array('我如何联系客户支持？'=>'您可以点击我们网站左下角的“联系我们”按钮。 该页面将指导您一个新页面，列出通过电话，实时聊天或电子邮件联系客户支持的各种方式。'),
                                array('我有资格参加比赛吗？'=>'您必须超过法律同意年龄，这是在您所在地区适用的法律规定的，并且无论如何您必须年满18岁。 玩家还必须拥有自己名下的有效付款方式。'),
                                array('如果我的互联网连接在游戏中失败会怎样？'=>'由于几个原因，可能会发生互联网连接失败。 最后，我们开发了一种机制来处理这种异常断开问题。 这完全取决于你正在玩的游戏。 如果您正在玩老虎机游戏并且您已经点击了旋转按钮并且您的连接丢失了，那么当您返回时，该游戏的结果将显示在玩家的历史详细信息中，您可以在其中查看游戏并查看该游戏中发生的情况。 如果你正在玩桌面游戏，那么它取决于你所处的游戏阶段，它可以由系统自动保存或完成。'),
                                array('我有投诉，我该联系谁？'=>'如果是投诉，请发送电子邮件至{{@site.name}} 并尽快帮助解决投诉，请尽可能多地提供相关信息。  {{@site.name}} 承诺尽快调查和解决投诉。'),
                                array('用户名，登录名和密码是否区分大小写?'=>'只有密码区分大小写'),
                                array('我忘记了密码，我该怎么办？'=>'点击“忘记密码”按钮并填写结果表格并提交表格，这样赌场就可以注册您的新密码。 如果信息正确，系统将通过电子邮件向玩家发送链接，以便玩家续订或更改密码。'),
                                array('我怎样才能找到更多有关游戏的信息？'=>'您可以在游戏规则页面上找到有关游戏的信息（第14节）。 每个游戏部分都会提供链接，指导您了解游戏的每个细节。'),
                                array('我怎么能确定比赛是公平的？'=>'的游戏软件由世界上最重要的游戏实验室之一进行了测试并通过了国库标准认证，这些实验室检查了赌场运营的各个方面。 不仅检查了随机数发生器，还检查了软件，硬件和操作的所有方面。'),
                                array('我怎么能确定比赛是公平的？'=>'{{@site.name}}\'s 的游戏软件由世界上最重要的游戏实验室之一进行了测试并通过了国库标准认证，这些实验室检查了赌场运营的各个方面。 不仅检查了随机数发生器，还检查了软件，硬件和操作的所有方面。'),
                                array('游戏的结果是如何产生的？'=>'{{@site.name}} 正在使用一个非常复杂的“随机数生成器”（RNG）为每个游戏创建一个随机结果，保证每个游戏都有完全随机的结果。'),
                                array('我不确定以前的比赛结果是什么。我该怎么办？'=>'您可以进入“游戏历史”部分，查看前几轮及其结果。 如果您有任何其他问题，请随时联系我们的客户支持人员 '.Config::get("settings.EMAIL_SENDER").'。 您可以随时致电或发送电子邮件给我们，每周7天，每天24小时。'),
                                array('最小和最大赌注是什么？'=>'赌注取决于您正在玩的游戏。 要了解特定游戏的赌注，您可以访问“”游戏规则“页面，找到有关赌注，支出，赢线和游戏规则的信息。'),
                                array('我可以在手机上玩游戏吗？'=>'是的，你可以在Android手机，iPhone和平板电脑等移动设备上玩我们的游戏。 您可以在此处访问我们的移动网站，也可以在设备上点击{{@site.domains.mobile}}。'),
                                array('我无法访问您的赌场游戏，我该怎么办？' => '如果您在访问我们的赌场游戏时遇到困难，可能是您在防火墙后面或者您的计算机不符合最低规格来玩我们的游戏。 尝试关闭所有防火墙软件并确保您可以访问互联网上的其他站点以确保连接正常。 如果您仍然遇到困难，我们的技术支持团队可能会提供帮助。'),
                                array('查看{{@site.name}}网站的最佳浏览器是什么？'=>'最好使用以下浏览器查看{{@site.name}}网站：<br /><ul><li>Mozilla Firefox 31</li><li>Google Chrome 31</li><li>Internet Explorer 11</li></ul>'),
                                array('玩{{@site.name}} 游戏的计算机要求是什么？?'=>'要求是：<br /><ul><li>PIII-800及以上</li><li>Adobe Flash Player v.11及更高版本</li><li>稳定的互联网连接（首选宽带）/li><li>最低屏幕分辨率： 1024 x 768</li></ul>'),
                                ),
                '2'=>   array(
                            array('如果我在手中断开连接会怎么样？'=>'如果您在手中间断开连接，您将收到x秒以恢复游戏（在我们的服务器端配置为断开连接保护）。 如果您在给定的时间内未能在手边行动，那么手将超时，并且手将被视为已检查或折叠。 他们将被安排坐下来，直到他们可以返回并继续比赛。'),
                            array('如果我在手中断开连接会怎么样？'=>'如果您在手中间断开连接，您将收到x秒以恢复游戏（在我们的服务器端配置为断开连接保护）。 如果您在给定的时间内未能在手边行动，那么手将超时，并且手将被视为已检查或折叠。 他们将被安排坐下来，直到他们可以返回并继续比赛。'),
                            array('我想加入的扑克游戏已满。我该怎么办？'=>'如果您要加入的牌桌已满，您仍然可以进入该牌桌并观察当前玩家是否会坐在那里，这样您就可以直接进入。 换句话说，你选择坐在另一张桌子上，因为我们提供了几张具有相同设置的桌子。'),
                            array('什么是最低买入？'=>'最低买入额取决于每个表格设置。 最低买入的信息将在游戏大厅的每张桌子上列出。'),
                            array('什么是”“耙子”“？ {{@site.name}}的耙子多少钱？'=>'耙子是一个小额补偿，房子从每个环形游戏罐收到，因为{{@site.name}} 是一个基于互联网扑克的服务，这意味着你从不玩扑克对抗房子，只对其他玩家。 目前我们的佣金只有3％。'),
                            array('我怎么能看到手的历史？ '=>'在桌子内部，玩家UI的左侧有历史按钮。 玩家可以单击它以查看当前历史记录，或单击查看详细信息历史记录以查看该表所有人的完整详细信息。'),
                            array('我可以和朋友在同一张桌子上玩吗？'=>'是。 {{@site.name}} 很高兴朋友，亲戚和熟人可以在同一张桌子上玩耍。 但是，当你这样做时，你应该像对待任何其他玩家一样对你的朋友进行竞争，并且你不得分享你持有的卡的任何信息或秘密制定任何比赛协议（做 串通也是如此，这是严格禁止的。'),
                            array('你的聊天规则是什么？'=>'聊天仅适用于就座的玩家。 在系统中实施了一组黑名单词和关于聊天洪水的规则，以确保聊天系统不被某些玩家滥用。 完整的聊天规则可以在游戏规则第6点看到。'),
                            array('我如何为自己制作图像？'=>'您可以在游戏大厅（玩家用户界面的右上角）上传自己或头像的图像。 要上传的图像必须在100x100像素分辨率和jpg或png格式之内。 除此之外，图像不得淫秽或冒犯并侵犯商标，严禁隐私权。'),
                            array('允许{{@site.name}}员工在您的网站上玩吗？'=>'不可以。任何{{@site.name}}员工都不能玩这个扑克。 这是为了防止可能滥用有关{{@site.name}}扑克玩家的任何信息，并为所有玩家提供公平竞争。'),
                            array('我在哪里可以观看即将举行的扑克锦标赛？'=>'您可以在游戏大厅中查看即将举行的锦标赛列表。 那里有一个比赛标签，宣布将在近期举行的整个锦标赛。'),
                            array('我如何报名参加扑克锦标赛？'=>'如果相应的锦标赛注册时间已经打开，您可以注册参加扑克锦标赛。 您可以点击每个锦标赛的详情来查看注册时间。'),
                            array('我可以同时在一张桌子上玩吗？'=>'是的你可以。 {{@site.name}}支持玩家一次玩多个牌桌。'),
                            array('如果锦标赛需要取消，会发生什么？'=>'如果比赛符合条件需要取消，整个球员的买入，重购和注册费将立即返还。'),
                            ),
                '3'=> array(
                            array('Tangkas {{@site.name}}提供哪些功能？' => 'Tangkas {{@site.name}} 提供各种令人兴奋的功能，使其与市场上的竞争对手不同，例如： <br /><ul><li>第一张或第三张牌上的小丑将触发最低4K的胜利</li><li>两个Jokers以任意组合出现将触发最小的STR Flush获胜</li><li>第一张牌和第三张牌上出现一张牌，将触发全场最低胜利</li><li>赢得任何jp4K，jpSTR，jp5K，jpROYAL和其他jp奖金都很容易</li><li>有机会赢得Tangkas 超级累积奖金的机会</li><li>一次最多可以播放三张桌子</li></ul>'),
                            array('你对{{@site.name}} Tangkas的游戏步骤有任何限制吗？'=>'{{@site.name}} Tangkas 仅将游戏步骤限制为2步模式以获得最佳游戏体验。'),
                            array('我可以在无人看管的情况下离开桌子多长时间？' => '你可以在没有任何活动的情况下让你的桌子无人看管最多1小时。 一旦超过，系统将自动完成游戏，你将被从桌子中踢出。'),
                            array('有没有时间由于维护而无法播放游戏？'=>'是的，每周三都会有一个维护计划，停机时间为30分钟到1小时，之后玩家可以继续正常游戏。'),
                            array('玩这个游戏的最低信用额度是多少？'=>'玩这个游戏的玩家的最低信用额度为1个。'),
                            array('我忘记了密码，我该怎么办？'=>'如果您忘记了登录名或密码，可以通过帐户帮助页面检索要发送给您的密码。 玩家需要点击登录栏下方的“忘记密码”按钮。 需要填写并提交登录名，用户电子邮件，安全答案和验证码。 一旦系统验证了您的所有数据，系统将通过电子邮件向玩家发送链接，以便玩家续订或更改密码。'),
                            array('播放{{@site.name}} tangkas的推荐支持的浏览器是什么？'=>'我们的系统支持Google Chrome和Mozilla Firefox浏览器，可提供最佳用户游戏体验。'),
                            array('我可以在Android设备或iPhone上玩这个游戏吗？'=>'是的，这个游戏可以使用Android设备或iPhone播放，只要播放器使用上面提到的支持的浏览器。'),
                            ),
                '1'=>   array(
                            array('如何下注？'=>'在老虎机游戏中下注是选择要下注的线数和每线下注值。'),
                            array('如何增加/减少赌注和有效支付线？'=>'要设置每线下注值和有效支付线数量，请在值字段两侧使用+或>符号和 - 或<符号控制按钮，每按一次按钮，每行的下注将增加。 当达到最大允许下注金额时，+或>控制按钮将被禁用。 当达到最小允许下注金额时， - 或<控制按钮将被禁用。</p><p>玩家用户界面还提供按钮功能，以选择最大金额和最小金额的赌注和线。'),
                            array('什么是自动旋转？'=>'此选项允许您选择具有所选支付线数量和每线值下注的旋转重复次数。 您可以选择5,10,25,50,100,500或1000次旋转重复，然后单击“自动旋转”按钮或按键盘上的空格键以激活此选项。</p><p>运行此功能后，“自动旋转”按钮将直接替换为“停止”按钮。 要停止卷轴，请使用“停止”控件或按键盘上的空格键。'),
                            array('什么是支付线？'=>'支付线是直线或锯齿形线，穿过每个卷轴上的一个符号，沿着该线评估获胜组合。 每条支付线都在游戏屏幕上突出显示。'),
                            array('什么是狂野的象征？'=>'一个狂野的符号，可以替代任何其他符号（奖励符号除外），可以帮助形成一个成功的组合。'),
                            array('什么是免费旋转？'=>'获得“自由”旋转意味着在游戏过程中，额外的奖励功能由特殊的“分散”符号组合触发。 此奖励功能使您可以旋转卷轴而无需下注。 但是你在这轮比赛中获得的胜利与你一样，就像玩真钱游戏一样。'),
                            array('什么是赌博功能？'=>'任何支付线赢取激活赌博功能。 你的最后一次胜利金额将成为本轮比赛的赌注，你可以冒险将其赢得双倍的机会。 每次获胜后，您可以收取获胜金额并返回主游戏。'),
                            array('在线老虎机如何运作？'=>'在线老虎机由带有符号的虚拟卷轴组成，旋转后卷轴停止的位置取决于RNG或随机数生成器。 随机生成的数字对应并映射到投币游戏中的转轴上的某些位置，这就是它们降落的地方。'),
                            array('在线播放插槽安全吗？'=>'您会发现安全性，安全性和玩家隐私在在线老虎机赌场中具有相当重要的意义。 在线游戏行业是一个受到良好监管的行业，欺诈或误用玩家信息的可能性很小。 所以，是的，在网上玩老虎机是安全的。'),
                            array('网上有哪些插槽？'=>'网上有各种各样的老虎机游戏。 玩家可以选择经典的3个卷轴插槽，奖金插槽和累积奖金插槽，并从丰富的图形和声音效果中选择丰富的有趣主题。'),
                            array('在线老虎机游戏是否公平？'=>'是的，完全。 赢或输是由游戏中的RNG或随机数发生器决定的，并且无法影响游戏的结果。'),
                            array('在线老虎机游戏是否公平？'=>'赢或输是由游戏中的RNG或随机数发生器决定的，并且无法影响游戏的结果。'),
                            array('什么是符号？'=>'符号是位于卷轴上的图片或图像。 他们经常遵循游戏的主题。 投币游戏中的奖励是根据符号的位置获得的，可以单独使用，也可以相互组合使用。'),
                            array('如果我的网络连接在游戏过程中被切断，会发生什么？'=>'“如果我的网络连接在游戏过程中被切断，会发生什么？如果您在在线老虎机游戏中断时无法重新连接到游戏，则只需在一段时间不活动后退出。 在在线老虎机游戏中，播放器不受间歇性网络连接的影响，因为服务器立即或在系统设置的时间段内解决了结果。 如果玩家在功能游戏中需要执行操作时断开连接，系统将等待一段时间让玩家重新登录并继续游戏，否则结果将由系统本身重置，如果时间段 已经过去了。'),
                            )
                );
    return require '../../'.PROJECT_DIR.'/front_resources/lang/'.Lang::locale().'/'.basename(__FILE__);