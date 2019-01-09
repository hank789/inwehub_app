/*! xdnphb 2019-01-03 */
define(["jquery", "common", "memory", "dialog", "cookie", "login", "md5"],
    function(a) {
        var b = a("jquery"),
            c = a("common"),
            d = a("memory"),
            e = a("dialog"),
            f = a("cookie"),
            g = a("login"),
            h = a("md5"),
            i = {},
            j = {},
            k = {
                rememberPwd: !0,
                errorTimeout: "",
                errorHasShow: !1,
                qrCode: "",
                ticket: "",
                userNameIsExists: !0,
                pwdThreeTimesError: !1,
                reSendInterval: "",
                reSendTime: 60,
                canSendMs: !0,
                WechatTimeInterval: "",
                codeFlag: "",
                canLogin: !0
            },
            l = c.getRequest(),
            m = l.back || window.location.href,
            n = l.isDetail,
            o = l.detailAccount;
        l.isAccountSearch,
            l.keyName;
        j.setWx = function() {
            var a = ['<div class="login-action-code"></div>', '<div class="login-action-code-footer">打开微信扫一扫，经新榜服务验证后即可登录/注册</div>'];
            b("#login_zone").html(a.join("")),
                i.getQrcode()
        },
            j.setBanner = function() {
                g.newLogin.getLoginBanner(function(a) {
                    a.img_url && b(".login-main").css("background-image", "url(" + a.img_url + ")")
                })
            },
            j.setPwd = function() {
                var a = ['<div class="login-action-pwd">', '<div class="login-action-pwd-line clear"><div class="line-part user"><i class="login-pic"></i></div><input id="account_input" type="text" placeholder="手机/邮箱/新榜ID" value=""/></div>', '<div class="login-action-pwd-line clear"><div class="line-part psd"><i class="login-pic"></i></div><input id="password_input" type="password" placeholder="密码" value=""/></div>', '<div class="login-action-pwd-identify">', '<div class="login-action-pwd-identify-input"><input id="identify_input" type="text" placeholder="请输入验证码" value=""/></div>', '<img id="identifyCode" class="newmain-right-login-bottom-zhdl-yzm-identifycode" src="" alt="验证码加载失败"/>', "</div>", '<div class="login-action-set clear">', '<p><i class="login-pic checked"></i>保持登录状态</p>', '<a href="' + d.rootUrl.main + "public/login/reset.html?back=" + m + '">忘记密码</a>', "</div>", '<div id="pwd_confirm" class="login-action-btn">登录</div>', "</div>", '<div class="login-action-footer">', '还没有账号？<a href="' + d.rootUrl.main + 'public/login/register_new.html" target="_blank">立即注册</a>', "</div>", '<div id="error" class="login-action-pwd-error" style="display:none;"></div>'];
                b("#login_zone").html(a.join("")),
                    i.bindPwdFun(),
                    j.changeIdentifyCode()
            },
            j.setPhone = function() {
                var a = ['<div class="login-action-phone">', '<p class="title">手机短信验证登录</p>', '<div class="login-action-pwd-line clear"><div class="line-part phone"><i class="login-pic"></i></div><input id="phone_input" type="text" placeholder="请输入手机号" maxlength="11" value=""/></div>', '<div class="login-action-identify clear"><input id="phone_code_input" type="text" placeholder="请输入手机验证码" maxlength="8" value=""/><div id="phone_code_btn" class="identify-btn phone-active">获取验证码</div></div>', '<div id="phone_confirm" class="login-action-btn">登录/注册</div>', "</div>", '<div class="login-action-footer">', '还没有账号？<a href="' + d.rootUrl.main + 'public/login/register_new.html" target="_blank">立即注册</a>', "</div>", '<div id="error" class="login-action-phone-error" style="display:none;"></div>'];
                b("#login_zone").html(a.join("")),
                    i.bindPhoneFun()
            },
            j.setUserRemind = function(a) {
                var c = ['<div class="login-user">', '<div class="login-user-img"><img src="' + a.headimgurl + '" alt="" width="100%"/></div>', "<p>" + a.nickname + "</p>", "<p>欢迎回来</p>", "</div>"];
                b("#login_zone").html(c.join(""))
            },
            j.setUserChoose = function(a) {
                var c = ['<div class="login-account">', '<div class="clear">', '<div class="login-account-img"><img src="' + a.userinfo.headimgurl + '" alt="" width="100%"/></div>', '<div class="login-account-name">' + a.userinfo.nickname + "</div>", "</div>", '<p class="login-account-remind">请选择您想要登录的账号</p>', '<ul id="account_list" class="login-account-list"></ul>', "</div>"];
                b("#login_zone").html(c.join(""));
                for (var d = 0; d < a.userzh.length; d++) {
                    var e = b("#account_list"),
                        f = a.userzh[d].nick_name || a.userzh[d].nr_name;
                    e.append('<li data-login="' + a.userzh[d].nr_name + '">' + f + (a.userzh[d].user_type >= 100 || a.userzh[d].user_type == -1 ? '<div class="ade-type">广告主</div>': "") + "</li>")
                }
                i.bindChoseClick()
            },
            j.changeIdentifyCode = function() {
                k.codeFlag = (new Date).getTime() + "" + Math.random(),
                "none" == b("#identifyCode").css("display") && b("#identifyCode").show(),
                    b("#identifyCode").attr("src", d.urlBase + "login/getIdentifyCode.json?flag=" + k.codeFlag)
            },
            j.setPhoneUserChoose = function(a, c) {
                var d = "",
                    e = c ? a.sysuser: a.users;
                b.each(e,
                    function(a, b) {
                        var c = b.nick_name || b.nr_name;
                        d += '<li class="login-account-phone-item" data-phone="' + b.phone_login + '">' + c + (b.user_type >= 100 || b.user_type == -1 ? '<div class="ade-type">广告主</div>': "") + "</li>"
                    });
                var f = ['<div class="login-account">', '<div class="login-account-wc"><span class="login-account-wc-strong">' + a.userName + "</span>用户，您好！</div>", '<p class="login-account-remind">请选择您想要登录的账号</p>', '<ul id="account_list" class="login-account-list">' + d + "</ul>", "</div>"];
                if (b("#login_zone").html(f.join("")), c) return i.bindPhonesmsChoseClick();
                i.bindPhoneChoseClick()
            },
            i.goPage = function() {
                n && o && 1 == n ? i.isLoad(d.rootUrl.main + "public/info/detail.html?account=" + o,
                    function(a) {
                        a ? location.replace(d.rootUrl.main + "public/info/detail.html?account=" + o) : location.replace(d.rootUrl.main)
                    }) : m ? i.isLoad(m,
                    function(a) {
                        a ? location.replace(decodeURIComponent(m)) : location.replace(d.rootUrl.main)
                    }) : location.replace(d.rootUrl.main)
            },
            i.isLoad = function(a, c) {
                b.ajax({
                    type: "get",
                    url: a,
                    cache: !1,
                    dataType: "jsonp",
                    processData: !1,
                    timeout: 1e4,
                    complete: function(a) {
                        4 == a.readyState && c(a.status >= 200 && a.status < 300 || 304 == a.status ? !0 : !1)
                    }
                })
            },
            i.showIdentifyCode = function(a) {
                b(".login-action-pwd-identify").css("visibility", a)
            },
            i.errorShow = function(a) {
                if (!k.errorHasShow) {
                    clearTimeout(k.errorTimeout);
                    var c = b("#error");
                    c.html(a),
                        c.show(),
                        k.errorHasShow = !0
                }
            },
            i.errorHide = function() {
                k.errorHasShow && (k.errorHasShow = !1, k.errorTimeout = setTimeout(function() {
                        b("#error").hide()
                    },
                    3e3))
            },
            i.bindFun = function() {
                b("div.login-type").unbind("click").bind("click",
                    function() {
                        clearInterval(k.WechatTimeInterval);
                        var a = b(this);
                        "wx" == a.attr("data-type") ? (a.css("background-position", "-44px -44px"), a.attr("data-type", "phone"), b("div.login-normal-tap").removeClass("selected"), j.setPhone()) : "phone" == a.attr("data-type") && (a.css("background-position", "0 0"), a.attr("data-type", "wx"), b("div.login-normal-tap").removeClass("selected"), b("div.login-normal-tap[data-type=wx]").addClass("selected"), j.setWx())
                    }),
                    b("div.login-normal-tap").unbind("click").bind("click",
                        function() {
                            clearInterval(k.WechatTimeInterval);
                            var a = b(this);
                            a.hasClass("selected") || (b("div.login-normal-tap").removeClass("selected"), a.addClass("selected"));
                            var c = b("div.login-type");
                            c.css("background-position", "0 0"),
                                c.attr("data-type", "wx"),
                                "wx" == a.attr("data-type") ? j.setWx() : "pwd" == a.attr("data-type") && j.setPwd()
                        }),
                    b("#identifyCode").unbind("click").bind("click",
                        function() {
                            j.changeIdentifyCode()
                        }),
                    setTimeout(function() {
                            clearInterval(k.WechatTimeInterval),
                                b(".login-action-code").html('请<a href="">刷新</a>页面后重试')
                        },
                        3e5)
            },
            i.bindPwdFun = function() {
                b("#login_zone input").unbind("focus").bind("focus",
                    function() {
                        i.errorHide()
                    }),
                    b("div.login-action-set p").unbind("click").bind("click",
                        function() {
                            var a = b(this);
                            k.rememberPwd ? (a.find("i").removeClass("checked"), k.rememberPwd = !1) : (a.find("i").addClass("checked"), k.rememberPwd = !0)
                        }),
                    b("#account_input").unbind("blur").bind("blur",
                        function() {
                            var a = b(this).val();
                            "" != a ? (k.userNameIsExists = !1, g.newLogin.usernameExists(a,
                                function(b) {
                                    0 == b.code ? i.errorShow("该账号不存在") : 0 == b.pass ? i.errorShow("未设置密码，请用其他方式登录") : (k.userNameIsExists = !0, i.checkLoginError(a))
                                })) : i.errorShow("账号不能为空")
                        }),
                    b("#pwd_confirm").unbind("click").bind("click",
                        function() {
                            i.pwdLogin()
                        }),
                    b("#password_input").unbind("keyup").bind("keyup",
                        function(a) {
                            13 == a.which && i.pwdLogin()
                        })
            },
            i.getUserAndPwd = function() {
                k.userName = b("#account_input").val()
            },
            i.pwdLogin = function() {
                var a = b("#account_input").val(),
                    c = h(h(b("#password_input").val()) + d.mdValue),
                    e = b("#identify_input").val();
                "" != a && "" != c && k.userNameIsExists && k.canLogin && (k.canLogin = !1, g.newLogin.usernameLogin(a, c, k.codeFlag, e,
                    function(b) {
                        if (1 == b.code) return k.rememberPwd ? (f.setCookie("rmbuser", "true", 30), f.setCookie("name", a, 365), f.setCookie("token", b.token, 30), f.setCookie("useLoginAccount", "true", 30)) : (f.setCookie("useLoginAccount", null), f.setCookie("rmbuser", null), f.setCookie("name", null), f.setCookie("token", null), f.setCookie("token", b.token)),
                            i.goPage();
                        i.errorShow(b.msg),
                            b.code == -10 ? i.checkLoginError(a) : b.code == -4 ? j.changeIdentifyCode() : "000" === b.code && (b.userName = a, j.setPhoneUserChoose(b)),
                            k.canLogin = !0
                    }))
            },
            i.checkLoginError = function(a) {
                g.newLogin.loginCount(a,
                    function(a) {
                        3 == a ? (k.pwdThreeTimesError = !0, i.showIdentifyCode("visible")) : (k.pwdThreeTimesError = !1, i.showIdentifyCode("hidden"))
                    })
            },
            i.bindPhoneFun = function() {
                b("#login_zone input").unbind("focus").bind("focus",
                    function() {
                        i.errorHide()
                    }),
                    b("#phone_input").unbind("blur").bind("blur",
                        function() {
                            "" == b(this).val() && i.errorShow("手机号不能为空")
                        }),
                    b("#phone_code_btn").unbind("click").bind("click",
                        function() {
                            if (f.getCookie("isSendMs")) i.errorShow("发送过于频繁，请稍后再试");
                            else {
                                var a = b("#phone_input").val();
                                i.sendSMSNewUsername(a)
                            }
                        }),
                    b("#phone_confirm").unbind("click").bind("click",
                        function() {
                            i.phoneLogin()
                        }),
                    b("#phone_code_input").unbind("keyup").bind("keyup",
                        function(a) {
                            13 == a.which && i.phoneLogin()
                        })
            },
            i.sendSMSNewUsername = function(a) {
                return "" == a ? void i.errorShow("手机号不能为空") : /^(1+[0-9]{10})$/.test(a) ? (f.setCookie("isSendMs", !0, 1 / 1440), k.canSendMs && i.setMsCount(), void g.newLogin.sendSMSNewUsername(a, "login",
                    function(a) {
                        a == -1 ? i.errorShow("验证码发送失败") : a == -2 && i.errorShow("当天发送短信已达上限")
                    })) : void i.errorShow("手机号格式错误")
            },
            i.setMsCount = function() {
                var a = b("#phone_code_btn");
                a.removeClass("phone-active"),
                    a.addClass("phone-disable"),
                    a.html(k.reSendTime + "秒后重发"),
                    k.reSendTime--,
                    k.canSendMs = !1,
                    k.reSendInterval = setInterval(function() {
                            if (0 == k.reSendTime) return clearInterval(k.reSendInterval),
                                a.removeClass("phone-disable"),
                                a.addClass("phone-active"),
                                a.html("获取验证码"),
                                k.canSendMs = !0,
                                void(k.reSendTime = 60);
                            a.html(k.reSendTime + "秒后重发"),
                                k.reSendTime--
                        },
                        1e3)
            },
            i.phoneLogin = function() {
                var a = b("#phone_input").val(),
                    c = b("#phone_code_input").val();
                "" != a && "" != c && k.canLogin && (k.canLogin = !1, g.newLogin.phoneLogin(a, c,
                    function(b) {
                        if (k.canLogin = !0, 0 == b.code) return b.userName = a,
                            j.setPhoneUserChoose(b, "sms");
                        if (1 == b.code) b.msg ? (e.showTopTip("系统检测到您是初次登录，已为您创建账号。"), setTimeout(function() {
                                f.setCookie("token", null),
                                    f.setCookie("token", b.token, 30),
                                    i.goPage()
                            },
                            2e3)) : (f.setCookie("token", null), f.setCookie("token", b.token, 30), i.goPage());
                        else switch (b.code) {
                            case "-1":
                                i.errorShow("手机验证码错误");
                                break;
                            case "-2":
                                i.errorShow("手机验证码已过期");
                                break;
                            case "-7":
                                i.errorShow("请先获取短信验证码")
                        }
                    }))
            },
            i.getQrcode = function() {
                var a = b(".login-action-code"),
                    c = function() {
                        var c = new Image;
                        c.src = k.qrCode,
                            b(c).width(232),
                            b(c).height(232),
                            b(c).on("load",
                                function() {
                                    a.html(""),
                                        b(c).appendTo(a)
                                })
                    };
                if (a.html('<img src="' + d.rootUrl.common + 'assets/common/img/public/loading.gif" width="80" height="80" style="margin-top:60px;"/>'), k.qrCode && k.ticket) return c(),
                    void i.checkWechatLogin();
                f.getCookie("ticket") && (k.ticket = f.getCookie("ticket")),
                    g.newLogin.getEwmData(k.ticket,
                        function(b) {
                            1 == b.code ? (k.ticket = b.ticket, f.setCookie("ticket", b.ticket, 1 / 24), k.qrCode = b.url, c(), i.checkWechatLogin()) : a.html('请<a href="">刷新</a>页面后重试')
                        })
            },
            i.checkWechatLogin = function() {
                var a = 0,
                    c = b(".login-action-code");
                k.WechatTimeInterval = setInterval(function() {
                        a++>120 ? c.html('请<a href="">刷新</a>页面后重试') : g.newLogin.getEwmSubNotice(k.ticket,
                            function(a) {
                                1 == a.smtype && (clearInterval(k.WechatTimeInterval), j.setUserRemind(a.userinfo), 0 == a.wxglzh ? f.getCookie("openid") != a.userinfo.openid && (f.setCookie("openid", a.userinfo.openid), i.registerNewUser(a.userinfo)) : 1 == a.wxglzh ? (f.setCookie("token", null), f.setCookie("token", a.token, 30), setTimeout(function() {
                                        i.goPage()
                                    },
                                    1e3)) : i.setUser(a))
                            })
                    },
                    500)
            },
            i.registerNewUser = function(a) {
                g.newLogin.openidCreateNewAccount(a.openid, k.ticket,
                    function(a) {
                        1 == a.code ? (e.showTopTip("系统检测到您是初次登录，已为您创建账号。"), setTimeout(function() {
                                f.setCookie("token", null),
                                    f.setCookie("token", a.token, 30),
                                    i.goPage()
                            },
                            2e3)) : a.code == -6 ? e.showTopTip("注册失败") : a.code == -7 && e.showTopTip("微信号为空")
                    })
            },
            i.directLogin = function(a) {
                k.canLogin && (k.canLogin = !1, g.newLogin.loginChoose(a, k.ticket,
                    function(a) {
                        1 == a.code ? (f.setCookie("token", null), f.setCookie("token", a.token, 30), i.goPage()) : a.code == -1 ? e.showTopTip("该账号不存在") : a.code == -2 ? e.showTopTip("未通过微信验证，请重新登录") : a.code == -1e4 && e.showTopTip("该账号已被冻结"),
                            k.canLogin = !0
                    }))
            },
            i.setUser = function(a) {
                setTimeout(function() {
                        j.setUserChoose(a)
                    },
                    1e3)
            },
            i.bindChoseClick = function() {
                b(".login-account-list li").unbind("click").bind("click",
                    function() {
                        var a = b(this).attr("data-login");
                        i.directLogin(a)
                    })
            },
            i.bindPhoneChoseClick = function() {
                b(".login-account-phone-item").unbind("click").bind("click",
                    function() {
                        var a = b(this).data("phone");
                        k.canLogin && (k.canLogin = !1, g.newLogin.usernameLogin(a,
                            function(a) {
                                return k.canLogin = !0,
                                    1 == a.code ? (f.setCookie("token", null), f.setCookie("token", a.token, 30), i.goPage()) : a.code == -2 ? e.showTopTip("选择账号超时") : a.code == -3 ? e.showTopTip("账号不存在") : a.code == -1e4 ? e.showTopTip("账号被冻结") : void 0
                            }))
                    })
            },
            i.bindPhonesmsChoseClick = function() {
                b(".login-account-phone-item").unbind("click").bind("click",
                    function() {
                        var a = b(this).data("phone");
                        k.canLogin && (k.canLogin = !1, g.newLogin.phoneLogins(a,
                            function(a) {
                                return k.canLogin = !0,
                                    1 == a.code ? (f.setCookie("token", null), f.setCookie("token", a.token, 30), i.goPage()) : a.code == -2 ? e.showTopTip("选择账号超时") : void 0
                            }))
                    })
            },
            i.init = function() {
                j.setWx(),
                    j.setBanner(),
                    i.bindFun()
            },
            i.init()
    });