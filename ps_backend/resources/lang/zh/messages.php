<?php
return array(
    'lost_password_success' => '下一步请检查您的电子邮件！',
    'new_password_success' => '您的密码已更改成功。',
    'deposit_hint' => '示例：如果您在账号1000中存入1000000 key',
    'withdraw_hint' => '示例：如果要在账号1000中提取1000000 key',
    'display_name_changed_success' => '您的妮称已成功更改！',
    'friend_registered' => '感谢您邀请您的朋友注册。已发送注册请求！',
    'transaction_success' => '交易记录已保存。',
    'password_changed_success' => '密码已更改成功。',
    'invalid_news' => '没有发现更新',
    'click_proceed' => '单击此处继续。',
    'no_transactions_found' => '找不到交易记录',
    'no_result' => '找不到结果',
    'no_statement' => '找不到语句',
    'no_bet_details' => '找不到赌注详细信息',
    'no_transfer' => '找不到传输详细信息',
    'no_credit' => '未找到信用详细信息',
    'under_maintenance' => '我们目前正在维修。请稍后再试，由此给您带来的不便，我们深表歉意。',
    'contact_your_agent' => '请联系您各自的上线进行密码查询。',
    'under_maintenance_header' => '我们的网站目前正在维护中',
    'server_disconnected' => '连接不到服务器',
    'disconnected' => '已断开与服务器的连接。',
    'goto_login' => '转到登录页',
    'relogin' => '请重新登录以继续。',
    'account_locked_header' => '帐户锁定',
    'account_deleted_header' => '删除帐户',
    'registration_done' => '谢谢，我们会向您发送确认电子邮件。按照电子邮件中的说明完成注册。',
    'registration_done_header' => '注册成功',
    'lost_password_subtitle' => '如果您忘记了密码，请填写表格，您的密码将通过电子邮件发送给您。',
    'no_security_question' => '如果您没有任何安全问题，请通过你原来注册的电子邮件联系我们的支持团队。 '.Config::get("settings.EMAIL_SENDER"),
    'dont_show_again' => '不再显示此内容',
    'set_display_name' => '设置妮称',
    'display_name_warning' => '警告：妮称只能设置一次',
    'display_name_subtitle' => '输入时：绿色表示为有效的妮称，红色时表示为无效的妮称。',
    'display_name_rule1' => '妮称的长度必须为6-15个字符',
    'display_name_rule2' => '它只能包含字母和/或数字',
    'display_name_rule3' => '禁止使用任何脏话。违反此规则将导致玩家封号。', 
    'contact_label1' => '如果您对我们有任何疑问、问题或建议，请与我们联系。'.Config::get("settings.PRODUCT_NAME").'\'s 的电子邮件和电话7 X 24小时开放',
    'contact_label2' => '咨询与客户支持电话： +63 977 320 1971<br />附属公司（Poipet地区）: +855 719 986 555, +66 86 343 2820',
    'contact_label3' => '如果您有与任何账户交易相关的查询，或者您希望对您的白标签名称账户进行任何修改，请向我们发送电子邮件至<a href="mailto:'.Config::get("settings.EMAIL_SENDER").'">'.Config::get("settings.EMAIL_SENDER").'</a>。发送请电邮之前登录。这是为了安全以及可以确保您的查询得到有效和及时的处理。',
    'change_credentials_subtitle' => '请在继续之前更改您的登录名和密码。',
    'play_now' => '现在开始',
    'loading_message' => '请等待…',
    'login_captcha_subtitle' => '您连续输入了错误的密码/用户名。请在键入下面验证码以继续。',
    'no_transactions_log' => '找不到事务日志',
    'log_notice' => '本报告的结尾是开始日期后一小时',
    'bank_first' => '请先选择银行名称',
    'chatbox_placeholder' => '在此处键入消息，然后按“回车”发送',
    'no_announcement_found' => '找不到公告。',
    'no_running_bets' => '不能运行',
    'transfer_description' => '通过代理站点从代理手动传输',
    'generated_display_name' => '您的妮称已成功更改为：',
    'accept_tac' => '接受条款和条件',
    'withdrawal_success' => '您的提款请求已成功发送。',
    'deposit_success' => '您的存款确认已成功发送。',
    'fund_transfer_success' => '您已转账成功。',
    'incorrect_password' => '密码错误',
    'email_exist_error' => '电子邮件已在使用中。',
    'refresh_captcha' => '获取其他代码',
    'account_suspended' => '您的帐户已被暂停。请联系您的代理人寻求帮助。',
    'err_no_symbol' => '此字段不能包含符号。' ,
    'registration_complete_msg' => '您的注册现已完成。您现在可以登录。',
    'invalid_amount' => '无效数量。',
    'login_name_exist_error' => '登录名不可用。',
    'bank_no_invalid_error' => '银行账号无效。',
    'more_details' => '详细信息',
    'amount_less_zero' => '不能小于零提取。',
    'transfer_adjustment' => '通过管理站点从房屋转移调整',
    'cutoff_transfer' => '通过代理站点从以下位置进行切断传输：',
    'fund_transfer' => '通过代理网站的资金转移来自：',
    'change_credentials_success' => '已成功更改登录名和密码，正在转到页面…',
    'no_chat' => '未找到信息',
    'walkin_chat_online'  => "欢迎用页面联系 {{@site.name}}的客户支援服务人员。",
    'walkin_chat_offline' => "{{@site.name}}的客户支援服务人员离线。",
    'agent_chat_online'  => "您现在已连接到代理。",
    'agent_chat_offline' => "您的代理当前处于脱机状态。请留言。",
    'authenticating' => "正在验证…",
    'redirecting' => "正在刷新…",
    'resend_email' => '重新发送电子邮件验证',
    'sending_email' => '正在发送电子邮件…',
    'logging_out' => '正在注销…',
    'very_weak' => '非常弱',
    'weak' => '弱',
    'better' => '较好',
    'medium' => '一般',
    'strong' => '强的',
    'strongest' => '最强的',
    'password_strength' => '密码强度',
    'avatar_rules' => '图像必须是jpeg或png <br /> 头像不能包函淫秽或暴力色情等政治等禁止内容 <br /> 严禁侵犯商标、版权或隐私 <br /> 我们认为不合适的头像将被删除。',
    'no_avatar'         => '直接拍照，或在计算机上浏览图片。',
    'pending_avatar'    => '头像正在等待批准，目前无法使用。',
    'approved_avatar'   => '你可以用它作为你的动态头像。',
    'active_avatar'     => '这是你当前的动态头像。' ,
    'rejected_avatar'   => '此头像已被拒绝。请尝试上载另一个。',
    'reading_image'     => '正在读取图像',
    'uploading_image'   => '正在上载图像',
    'showing_transfer_from' => '显示来自的传输详细信息',
    'showing_credit_from' => '显示信用明细',
    'showing_betting_of' => '显示的下注详细信息',
    'showing_running_bets' => '显示正在运行的下注详细信息',
    'opening_game'         => '正在开始游戏',
    'opening_bet_details'  => '开始下注详细信息',
    'click_more_info'      => '点击以获取更多信息',
    'click_more_info'      => '点击以获取更多信息',
    'want_logout'         => '您确定要注销吗？',
    'allow_fullscreen'   => '允许全屏显示',
    'disable_fullscreen' => '禁用全屏',
    'no_history_found'     => '找不到乐透历史记录',
    'not_available'        => '无法使用的',
    'betting_open'         => '博彩开始',
    'for_18_plus'             => '仅18岁以上',
    'gamcare'                 => '游戏娱乐',
    'gambling_therapy'        => '博彩治疗',
    'usa_player_prohibited'   => '禁止美国玩家',
    'turn_off_sound'   => '关闭声音',
    'turn_on_sound'   => '关闭声音',
    'resend_email_link' => '重新发送电子邮件？',
    'sending_message_disabled' => '已禁用发送消息',
    'send_when_enable' => '正在等待启用聊天室。',
    'confirm_have_unsent' => '仍有未发送的邮件。你确定要离开吗？'

);
    return require '../../'.PROJECT_DIR.'/front_resources/lang/'.Lang::locale().'/'.basename(__FILE__);