<?php

return array(
    '-1'        => array('正在处理',      '正在刷新页面…'),
    '-2'        => array('正在处理',      '正在刷新页面…'),
    '-3'        => array('发送超时', '您的发送已过期。请重新登录以继续。'),
    '-4'        => array('Error',           '注意!出错.'),
    'ERR_00001' => array('Error',           '注意!出错.'),
    'ERR_00002' => '必填字段',
    'ERR_00003' => '先填此字段',
    'ERR_00004' => '此字段不能包含特殊字符',
    'ERR_00005' => '不允许执行此过程。',
    'ERR_00006' => '登录名无效<p class="ps_components-policy"><span class="ps_components-title">登录名称规则：</span><span><strong>1.</strong> 登录名必须在6到15个字符之间。</span><span><strong>2.</strong> 登录名只包含字母（a-z）和数字（0-9），并以字母开头</span><span><strong>3.</strong> 英文字母不区分大小写。</span></p>',
    'ERR_00007' => 'T登录名必须介于6到15个字符之间，只包含字母（a-z）和数字（0-9），并以字母开头。这些字母不区分大小写。',
    'ERR_00008' => array('登录名', '登录名已被其他玩家使用，请尝试新的登录名。'),
    'ERR_00009' => '密码必须包含8-15个字符，必须包含大小写字母和数字的组合，不得包含用户名、登录名、名、姓和任何空格。',
    'ERR_00010' => '密码无效<p class="ps_components-policy"><span class="ps_components-title">密码规则</span><span><strong>1.</strong> 密码必须包含8-15个字符。</span><span><strong>2.</strong> 2.密码必须包括字母字符（大写或小写字母），数字和符号的组合。</span><span><strong>3.</strong> 密码不能包含用户名、名和姓。</span><span><strong>4.</strong> 密码不能包含任何空格。</span></p>',
    'ERR_00011' => '密码不匹配。',
    'ERR_00012' => array('更改密码', '新密码不能与当旧密码相同。'),
    'ERR_00013' => '电子邮件地址无效',
    'ERR_00014' => array('此电邮地址已使用', '此电子邮件地址已被其他玩家使用，请尝试其他电子邮件。'),
    'ERR_00015' => array('此电邮地址已使用', '此电子邮件已注册，正在等待激活。请检查激活的电子邮件地址。<span class="ps_js-render" data-type="resend"></span>'),
    'ERR_00016' => array('','电子邮件地址格式不正确。'),
    'ERR_00017' => array('','无效的手机号码'),
    'ERR_00018' => array('','此手机号码已被其他玩家使用，请尝试其他号码。'),
    'ERR_00019' => array('','手机号码长度应为5到20'),
    'ERR_00020' => '您指定的银行无效。请从选项中选择。',
    'ERR_00021' => '无效银行号码',
    'ERR_00022' => '银行账号字段为空。请填写正确的帐户银行账号。',
    'ERR_00023' => '帐户银行名称无效。',
    'ERR_00024' => '不支持您指定的货币。',
    'ERR_00025' => '错误的问题。',
    'ERR_00026' => '您指定的问题无效。请从选项中选择。',
    'ERR_00027' => '找不到登录名',
    'ERR_00028' => '答案错误。',
    'ERR_00029' => '该妮称已被其他用户使用。请用其它妮称再试。',
    'ERR_00030' => '妮称的长度必须为6到20个字符。它只能包含数字和字母。禁止使用任何脏话。违反规则将会封停账号。',
    'ERR_00031' => '你的妮称更新成功',
    'ERR_00032' => '妮称不能包含用户名或登录名',
    'ERR_00033' => '无法处理请求。',
    'ERR_00034' => '无效的事务类型',
    'ERR_00035' => '名字无效',
    'ERR_00036' => '姓氏无效',
    'ERR_00037' => array('验证码', '验证码错误'),
    'ERR_00038' => array('登录失败', '用户名或密码无效。'),
    'ERR_00039' => '登录名或电子邮件无效。',
    'ERR_00040' => array('账户验证', '您的帐户尚未验证。请检查您的电子邮件或重新发送电子邮件验证。'),
    'ERR_00041' => array('账号暂停使用', '您的账户已被暂停使用。请联系您的代理人寻求解决。'),
    'ERR_00042' => array('账户关闭', '您的帐户已关闭。请联系您的代理人寻求解决。'),
    'ERR_00043' => array('账号锁定', '您的帐户已锁定。请联系您的代理人寻求解决。'),
    'ERR_00044' => array('帐户已删除', '您的帐户已删除。请联系您的代理人寻求解决。'),
    'ERR_00045' => '登录时出错。请联系您的代理人寻求解决',
    'ERR_00046' => array('账号暂停使用', '您的帐户已被 '.Config::get("settings.PRODUCT_NAME").'. 暂停使用。请通过 '.Config::get("settings.EMAIL_SENDER").'联系我们的支持团队。'),
    'ERR_00047' => array('帐户已删除', '您的帐户已被 '.Config::get("settings.PRODUCT_NAME").'. 删除。请通过 '.Config::get("settings.EMAIL_SENDER").'联系我们的支持团队。'),
    'ERR_00048' => array('账户关闭', '您的帐户已被 '.Config::get("settings.PRODUCT_NAME").'. 关闭。请通过 '.Config::get("settings.EMAIL_SENDER").'联系我们的支持团队。'),
    'ERR_00049' => array('','无效数据'),
    'ERR_00050' => '请输入大于0的金额。',
    'ERR_00051' => '由于法规限制，您的IP地址被阻止进入我们的网站。请联系我们的客户支持以获得进一步的帮助。谢谢您。',
    'ERR_00052' => array('传送失败', '您当前的可用余额不足。'),
    'ERR_00053' => array('Error', '安全代码仍未验证'),
    'ERR_00054' => '没有所选货币的可用代理',
    'ERR_00055' => '禁止使用脏话。',
    'ERR_00056' => array('此游戏当前不可用。', '请稍后再重试，对于由此给您带来的不便，我们深表歉意。'),
    'ERR_00057' => '您需要先重置密码才能进入游戏。',
    'ERR_00058' => array('会话超时', '您的会话已过期。请重新登录以继续。'),
    'ERR_00059' => array('维护中', '您尝试加载的产品当前正在维护中。请稍后再检查，对于由此给您带来的不便，我们深表歉意。'),
    'ERR_00060' => array('传输失败', '金额必须大于零。'),
    'ERR_00061' => array('维护', '我们目前正在维修。请稍后再试，对于由此给您带来的不便，我们深表歉意。'),
    'ERR_00062' => array('注意', '很抱歉，由于最近登录尝试失败的次数太多，暂不能正常登录。请回答下面的验证码以再进入登录页面。'),
    'ERR_00063' => array('我们的网站目前正在维护中', '请稍后再重试，对于由此给您带来的不便，我们深表歉意。'),
    'ERR_00064' => array('忘记密码', '请联系您你的上级进行密码查询。'),
    'ERR_00065' => array('验证码', '需要验证码'),
    'ERR_00066' => array('忘记密码', '用户名或用户电子邮件无效。'),
    'ERR_00067' => array('传输失败', '金额不能超过您的钱包余额。'),
    'ERR_00068' => array('Error', '一个或多个必填字段为空'),
    'ERR_00069' => array('传输失败', '不能转移到同一个钱包。'),
    'ERR_00070' => array('传输失败', '金额不得低于最低存款额。'),
    'ERR_00071' => array('注意', '404页未找到'),
    'ERR_00072' => array('注册会员成功', '保存佣金时出错。'),
    'ERR_00073' => array('注册客户端产品', '无法连接到客户端管道。'),
    'ERR_00074' => array("无法处理请求", 'cookie不匹配。'),
    'ERR_00075' => array("未完成的游戏", "您已经选择了 {{@gameName}}，但仍然有一个不完整的 {{@runningGame}}游戏。你需要先完成 {{@runningGame}}。"),
    'ERR_00076' => array("无效插件", "请求的插件名称不正确。"),
    'ERR_00077' => array('更改密码失败', '您的密码已更改。请重新登录以重置密码。'),
    'ERR_00078' => array('仅远端桌面支持', '此游戏仅支持台式电脑。'),
    'ERR_00079' => '组中的一个或多个字段为空',
    'ERR_00080' => '无效银行',
    'ERR_00081' => array('阻止弹出', '我们建议关闭网页浏览器上的弹出窗口阻止程序。'),
    'ERR_00082' => array('不支持此方向','请旋转设备。'),
    'ERR_00083' => '金额只能是整数',
    'ERR_00084' => '密码错误',
    'ERR_00085' => '此页面要是资金转移的接收者或发送者才能操作。',
    'ERR_00086' => '您已断开与客户支持的连接，请刷新此页并重试。',
    'ERR_00087' => '已创建虚拟头像。',
    'ERR_00088' => '仅支持jpeg或png格式上载图像',
    'ERR_00089' => '图像大小不能大于200KB。',
    'ERR_00090' => '无法将图像设置为配置文件虚拟头像',
    'ERR_00091' => array("无法处理请求", '请刷新页面并完成要求。'),
    'ERR_00092' => '找不到事项。',
    'ERR_00093' => array('网络错误', '请刷新此页或稍后再试。'),
    'ERR_00094' => '找不到项目。',
    'ERR_00095' => array('检测到网络连接不稳定', '请刷新此页或稍后再试。'),
    'ERR_00096' => '加载图像时出现问题。',
    'ERR_00097' => '此浏览器不支持视频。',
    'ERR_00098' => '相机错误。',
    'ERR_00099' => array('在建项目', '游戏教程正在建设中'),
    'ERR_00100' => array('ERROR', '游戏教程页面不可用。'),
    'ERR_00101' => array('找不到', '没有发现游戏。'),
    'ERR_00102' => array('弹出式拦截', '请禁用弹出窗口阻止程序，此网站的某些资源通过弹出窗口打开。'),
    'ERR_00103' => array('视频加载失败', "加载视频时出错。"),
    'ERR_00104' => array('找不到', '找不到促销活动。'),
    'ERR_00105' => array('Not allowed', '您必须先登出才能执行操作。'),
    'ERR_00106' => array('账户验证', '帐户验证失败。'),
    'ERR_00107' => array('', '此游戏不支持当前屏幕高度。请尝试旋转设备。'),
    'ERR_00108' => array('必需填写姓名', '请设置显示名称以继续。'),
    'ERR_00109' => '此字段不能包含符号。',
    'ERR_00110' => array('这电邮地址已经注册', '此电子邮件已注册，正在等待激活。请检查激活的电子邮件地址'),
    'ERR_00111' => array('这电邮地址已经注册', '此电子邮件已注册，正在等待激活。<span class="ps_js-render" data-type="resend"></span>'),
    'ERR_00112' => array('这电邮地址已经注册', '此电子邮件已注册，正在等待激活。'),
    'ERR_00113' => array('游戏不可用', '游戏不可用。请稍后再试或联系我们的客户支持。'),
    'ERR_00114' => array('Error', '选择的语言无效。'),
    'ERR_00115' => array('Error','由于短时间内连续发送消息，发送消息功能被禁用几秒钟。'),
    'ERR_00116' => array('Error','This field couldn\'t be more than 50 charactes.'),
    'ERR_00117' => array('Error','This field couldn\'t be more than 100 charactes.'),
    'ERR_00118' => array('Login failed','This website is under maintenance mode'),
    'ERR_00119' =>'This website is currently under maintenance mode. Please come back and try again later. Our apologies for any inconvenience this may have caused you.'

);

    return require '../../'.PROJECT_DIR.'/front_resources/lang/'.Lang::locale().'/'.basename(__FILE__);