/*! xdnphb-html-common 2018-12-11 */
void 0 == window.relativeUrl && (window.relativeUrl = "../../");
var _hmt = _hmt || []; !
    function() {
        var a = window.location.href,
            b = "";
        0 == a.indexOf("https://www.newrank.cn") ? b = "a19fd7224d30e3c8a6558dcb38c4beed": 0 == a.indexOf("https://data.newrank.cn") ? b = "291c15d512fe0d0adc8110e8eef26919": 0 == a.indexOf("https://edit.newrank.cn") ? b = "42d598738d95bd558dfa37585b6dd77c": 0 == a.indexOf("https://zhaopin.newrank.cn") ? b = "5cdd1ddf73790b274e6e45fa44b958ec": 0 == a.indexOf("https://a.newrank.cn") && (b = "ee5b2d8cb0e6152b26e761ea02aa099d");
        var c = document.createElement("script");
        c.src = "https://hm.baidu.com/hm.js?" + b;
        var d = document.getElementsByTagName("script")[0];
        d.parentNode.insertBefore(c, d)
    } (),
    function(a, b) {
        function c(a) {
            return function(b) {
                return {}.toString.call(b) == "[object " + a + "]"
            }
        }
        function d() {
            return z++
        }
        function e(a) {
            return a.match(C)[0]
        }
        function f(a) {
            for (a = a.replace(D, "/"), a = a.replace(F, "$1/"); a.match(E);) a = a.replace(E, "/");
            return a
        }
        function g(a) {
            var b = a.length - 1,
                c = a.charAt(b);
            return "#" === c ? a.substring(0, b) : ".js" === a.substring(b - 2) || a.indexOf("?") > 0 || "/" === c ? a: a + ".js"
        }
        function h(a) {
            var b = u.alias;
            return b && w(b[a]) ? b[a] : a
        }
        function i(a) {
            var b, c = u.paths;
            return c && (b = a.match(G)) && w(c[b[1]]) && (a = c[b[1]] + b[2]),
                a
        }
        function j(a) {
            var b = u.vars;
            return b && a.indexOf("{") > -1 && (a = a.replace(H,
                function(a, c) {
                    return w(b[c]) ? b[c] : a
                })),
                a
        }
        function k(a) {
            var b = u.map,
                c = a;
            if (b) for (var d = 0,
                            e = b.length; e > d; d++) {
                var f = b[d];
                if ((c = y(f) ? f(a) || a: a.replace(f[0], f[1])) !== a) break
            }
            return c
        }
        function l(a, b) {
            var c, d = a.charAt(0);
            if (I.test(a)) c = a;
            else if ("." === d) c = f((b ? e(b) : u.cwd) + a);
            else if ("/" === d) {
                var g = u.cwd.match(J);
                c = g ? g[0] + a.substring(1) : a
            } else c = u.base + a;
            return 0 === c.indexOf("//") && (c = location.protocol + c),
                c
        }
        function m(a, b) {
            if (!a) return "";
            a = h(a),
                a = i(a),
                a = j(a),
                a = g(a);
            var c = l(a, b);
            return c = k(c)
        }
        function n(a) {
            return a.hasAttribute ? a.src: a.getAttribute("src", 4)
        }
        function o(a, b, c) {
            var d = K.createElement("script");
            if (c) {
                var e = y(c) ? c(a) : c;
                e && (d.charset = e)
            }
            p(d, b, a),
                d.async = !0,
                d.src = a,
                P = d,
                S ? R.insertBefore(d, S) : R.appendChild(d),
                P = null
        }
        function p(a, b, c) {
            function d() {
                a.onload = a.onerror = a.onreadystatechange = null,
                u.debug || R.removeChild(a),
                    a = null,
                    b()
            }
            "onload" in a ? (a.onload = d, a.onerror = function() {
                B("error", {
                    uri: c,
                    node: a
                }),
                    d()
            }) : a.onreadystatechange = function() { / loaded | complete / .test(a.readyState) && d()
            }
        }
        function q() {
            if (P) return P;
            if (Q && "interactive" === Q.readyState) return Q;
            for (var a = R.getElementsByTagName("script"), b = a.length - 1; b >= 0; b--) {
                var c = a[b];
                if ("interactive" === c.readyState) return Q = c
            }
        }
        function r(a) {
            var b = [];
            return a.replace(V, "").replace(U,
                function(a, c, d) {
                    d && b.push(d)
                }),
                b
        }
        function s(a, b) {
            this.uri = a,
                this.dependencies = b || [],
                this.exports = null,
                this.status = 0,
                this._waitings = {},
                this._remain = 0
        }
        if (!a.seajs) {
            var t = a.seajs = {
                    version: "2.3.0"
                },
                u = t.data = {},
                v = c("Object"),
                w = c("String"),
                x = Array.isArray || c("Array"),
                y = c("Function"),
                z = 0,
                A = u.events = {};
            t.on = function(a, b) {
                return (A[a] || (A[a] = [])).push(b),
                    t
            },
                t.off = function(a, b) {
                    if (!a && !b) return A = u.events = {},
                        t;
                    var c = A[a];
                    if (c) if (b) for (var d = c.length - 1; d >= 0; d--) c[d] === b && c.splice(d, 1);
                    else delete A[a];
                    return t
                };
            var B = t.emit = function(a, b) {
                    var c = A[a];
                    if (c) {
                        c = c.slice();
                        for (var d = 0,
                                 e = c.length; e > d; d++) c[d](b)
                    }
                    return t
                },
                C = /[^?#]*\//,
                D = /\/\.\//g,
                E = /\/[^\/]+\/\.\.\//,
                F = /([^:\/])\/+\//g,
                G = /^([^\/:]+)(\/.+)$/,
                H = /{([^{]+)}/g,
                I = /^\/\/.|:\//,
                J = /^.*?\/\/.*?\//,
                K = document,
                L = location.href && 0 !== location.href.indexOf("about:") ? e(location.href) : "",
                M = K.scripts,
                N = K.getElementById("seajsnode") || M[M.length - 1],
                O = e(n(N) || L);
            t.resolve = m;
            var P, Q, R = K.head || K.getElementsByTagName("head")[0] || K.documentElement,
                S = R.getElementsByTagName("base")[0];
            t.request = o;
            var T, U = /"(?:\\"|[^"])*"|'(?:\\'|[^'])*'|\/\*[\S\s]*?\*\/|\/(?:\\\/|[^\/\r\n])+\/(?=[^\/])|\/\/.*|\.\s*require|(?:^|[^$])\brequire\s*\(\s*(["'])(.+?)\1\s*\)/g,
                V = /\\\\/g,
                W = t.cache = {},
                X = {},
                Y = {},
                Z = {},
                $ = s.STATUS = {
                    FETCHING: 1,
                    SAVED: 2,
                    LOADING: 3,
                    LOADED: 4,
                    EXECUTING: 5,
                    EXECUTED: 6
                };
            s.prototype.resolve = function() {
                for (var a = this,
                         b = a.dependencies,
                         c = [], d = 0, e = b.length; e > d; d++) c[d] = s.resolve(b[d], a.uri);
                return c
            },
                s.prototype.load = function() {
                    var a = this;
                    if (! (a.status >= $.LOADING)) {
                        a.status = $.LOADING;
                        var c = a.resolve();
                        B("load", c);
                        for (var d, e = a._remain = c.length,
                                 f = 0; e > f; f++) d = s.get(c[f]),
                            d.status < $.LOADED ? d._waitings[a.uri] = (d._waitings[a.uri] || 0) + 1 : a._remain--;
                        if (0 === a._remain) return a.onload(),
                            b;
                        var g = {};
                        for (f = 0; e > f; f++) d = W[c[f]],
                            d.status < $.FETCHING ? d.fetch(g) : d.status === $.SAVED && d.load();
                        for (var h in g) g.hasOwnProperty(h) && g[h]()
                    }
                },
                s.prototype.onload = function() {
                    var a = this;
                    a.status = $.LOADED,
                    a.callback && a.callback();
                    var b, c, d = a._waitings;
                    for (b in d) d.hasOwnProperty(b) && (c = W[b], c._remain -= d[b], 0 === c._remain && c.onload());
                    delete a._waitings,
                        delete a._remain
                },
                s.prototype.fetch = function(a) {
                    function c() {
                        t.request(g.requestUri, g.onRequest, g.charset)
                    }
                    function d() {
                        delete X[h],
                            Y[h] = !0,
                        T && (s.save(f, T), T = null);
                        var a, b = Z[h];
                        for (delete Z[h]; a = b.shift();) a.load()
                    }
                    var e = this,
                        f = e.uri;
                    e.status = $.FETCHING;
                    var g = {
                        uri: f
                    };
                    B("fetch", g);
                    var h = g.requestUri || f;
                    return ! h || Y[h] ? (e.load(), b) : X[h] ? (Z[h].push(e), b) : (X[h] = !0, Z[h] = [e], B("request", g = {
                        uri: f,
                        requestUri: h,
                        onRequest: d,
                        charset: u.charset
                    }), g.requested || (a ? a[g.requestUri] = c: c()), b)
                },
                s.prototype.exec = function() {
                    function a(b) {
                        return s.get(a.resolve(b)).exec()
                    }
                    var c = this;
                    if (c.status >= $.EXECUTING) return c.exports;
                    c.status = $.EXECUTING;
                    var e = c.uri;
                    a.resolve = function(a) {
                        return s.resolve(a, e)
                    },
                        a.async = function(b, c) {
                            return s.use(b, c, e + "_async_" + d()),
                                a
                        };
                    var f = c.factory,
                        g = y(f) ? f(a, c.exports = {},
                            c) : f;
                    return g === b && (g = c.exports),
                        delete c.factory,
                        c.exports = g,
                        c.status = $.EXECUTED,
                        B("exec", c),
                        g
                },
                s.resolve = function(a, b) {
                    var c = {
                        id: a,
                        refUri: b
                    };
                    return B("resolve", c),
                    c.uri || t.resolve(c.id, b)
                },
                s.define = function(a, c, d) {
                    var e = arguments.length;
                    1 === e ? (d = a, a = b) : 2 === e && (d = c, x(a) ? (c = a, a = b) : c = b),
                    !x(c) && y(d) && (c = r("" + d));
                    var f = {
                        id: a,
                        uri: s.resolve(a),
                        deps: c,
                        factory: d
                    };
                    if (!f.uri && K.attachEvent) {
                        var g = q();
                        g && (f.uri = g.src)
                    }
                    B("define", f),
                        f.uri ? s.save(f.uri, f) : T = f
                },
                s.save = function(a, b) {
                    var c = s.get(a);
                    c.status < $.SAVED && (c.id = b.id || a, c.dependencies = b.deps || [], c.factory = b.factory, c.status = $.SAVED, B("save", c))
                },
                s.get = function(a, b) {
                    return W[a] || (W[a] = new s(a, b))
                },
                s.use = function(b, c, d) {
                    var e = s.get(d, x(b) ? b: [b]);
                    e.callback = function() {
                        for (var b = [], d = e.resolve(), f = 0, g = d.length; g > f; f++) b[f] = W[d[f]].exec();
                        c && c.apply(a, b),
                            delete e.callback
                    },
                        e.load()
                },
                t.use = function(a, b) {
                    return s.use(a, b, u.cwd + "_use_" + d()),
                        t
                },
                s.define.cmd = {},
                a.define = s.define,
                t.Module = s,
                u.fetchedList = Y,
                u.cid = d,
                t.require = function(a) {
                    var b = s.get(s.resolve(a));
                    return b.status < $.EXECUTING && (b.onload(), b.exec()),
                        b.exports
                },
                u.base = O,
                u.dir = O,
                u.cwd = L,
                u.charset = "utf-8",
                t.config = function(a) {
                    for (var b in a) {
                        var c = a[b],
                            d = u[b];
                        if (d && v(d)) for (var e in c) d[e] = c[e];
                        else x(d) ? c = d.concat(c) : "base" === b && ("/" !== c.slice( - 1) && (c += "/"), c = l(c)),
                            u[b] = c
                    }
                    return B("config", a),
                        t
                }
        }
    } (this),
    function() {
        function a(a) {
            return function(b) {
                return {}.toString.call(b) == "[object " + a + "]"
            }
        }
        function b(a) {
            return "[object Function]" == {}.toString.call(a)
        }
        function c(a, c, e) {
            var f = u.test(a),
                g = r.createElement(f ? "link": "script");
            if (e) {
                var h = b(e) ? e(a) : e;
                h && (g.charset = h)
            }
            d(g, c, f, a),
                f ? (g.rel = "stylesheet", g.href = a) : (g.async = !0, g.src = a),
                p = g,
                t ? s.insertBefore(g, t) : s.appendChild(g),
                p = null
        }
        function d(a, b, c, d) {
            function f() {
                a.onload = a.onerror = a.onreadystatechange = null,
                c || seajs.data.debug || s.removeChild(a),
                    a = null,
                    b()
            }
            var g = "onload" in a;
            return ! c || !v && g ? void(g ? (a.onload = f, a.onerror = function() {
                seajs.emit("error", {
                    uri: d,
                    node: a
                }),
                    f()
            }) : a.onreadystatechange = function() { / loaded | complete / .test(a.readyState) && f()
            }) : void setTimeout(function() {
                    e(a, b)
                },
                1)
        }
        function e(a, b) {
            var c, d = a.sheet;
            if (v) d && (c = !0);
            else if (d) try {
                d.cssRules && (c = !0)
            } catch(f) {
                "NS_ERROR_DOM_SECURITY_ERR" === f.name && (c = !0)
            }
            setTimeout(function() {
                    c ? b() : e(a, b)
                },
                20)
        }
        function f(a) {
            return a.match(x)[0]
        }
        function g(a) {
            for (a = a.replace(y, "/"), a = a.replace(A, "$1/"); a.match(z);) a = a.replace(z, "/");
            return a
        }
        function h(a) {
            var b = a.length - 1,
                c = a.charAt(b);
            return "#" === c ? a.substring(0, b) : ".js" === a.substring(b - 2) || a.indexOf("?") > 0 || ".css" === a.substring(b - 3) || "/" === c ? a: a + ".js"
        }
        function i(a) {
            var b = w.alias;
            return b && q(b[a]) ? b[a] : a
        }
        function j(a) {
            var b, c = w.paths;
            return c && (b = a.match(B)) && q(c[b[1]]) && (a = c[b[1]] + b[2]),
                a
        }
        function k(a) {
            var b = w.vars;
            return b && a.indexOf("{") > -1 && (a = a.replace(C,
                function(a, c) {
                    return q(b[c]) ? b[c] : a
                })),
                a
        }
        function l(a) {
            var c = w.map,
                d = a;
            if (c) for (var e = 0,
                            f = c.length; f > e; e++) {
                var g = c[e];
                if ((d = b(g) ? g(a) || a: a.replace(g[0], g[1])) !== a) break
            }
            return d
        }
        function m(a, b) {
            var c, d = a.charAt(0);
            if (D.test(a)) c = a;
            else if ("." === d) c = g((b ? f(b) : w.cwd) + a);
            else if ("/" === d) {
                var e = w.cwd.match(E);
                c = e ? e[0] + a.substring(1) : a
            } else c = w.base + a;
            return 0 === c.indexOf("//") && (c = location.protocol + c),
                c
        }
        function n(a, b) {
            if (!a) return "";
            a = i(a),
                a = j(a),
                a = k(a),
                a = h(a);
            var c = m(a, b);
            return c = l(c)
        }
        function o(a) {
            return a.hasAttribute ? a.src: a.getAttribute("src", 4)
        }
        var p, q = a("String"),
            r = document,
            s = r.head || r.getElementsByTagName("head")[0] || r.documentElement,
            t = s.getElementsByTagName("base")[0],
            u = /\.css(?:\?|$)/i,
            v = +navigator.userAgent.replace(/.*(?:AppleWebKit|AndroidWebKit)\/?(\d+).*/i, "$1") < 536;
        seajs.request = c;
        var w = seajs.data,
            x = /[^?#]*\//,
            y = /\/\.\//g,
            z = /\/[^\/]+\/\.\.\//,
            A = /([^:\/])\/+\//g,
            B = /^([^\/:]+)(\/.+)$/,
            C = /{([^{]+)}/g,
            D = /^\/\/.|:\//,
            E = /^.*?\/\/.*?\//,
            r = document,
            F = location.href && 0 !== location.href.indexOf("about:") ? f(location.href) : "",
            G = r.scripts;
        f(o(r.getElementById("seajsnode") || G[G.length - 1]) || F),
            seajs.resolve = n,
            define("seajs/seajs-css/1.0.4/seajs-css", [], {})
    } (),
    seajs.production = !0,
    seajs.xdndata = {
        dataDebug: !1,
        time: "今天21:00"
    },
    define.amd = !0,
    seajs.config({
        base: "/",
        paths: {
            gallery: "https://cdn.newrank.cn/resource/static",
            commonPlugin: "https://assets.newrank.cn/assets/common/plugins",
            commonBase: "https://assets.newrank.cn/assets/common"
        },
        alias: {
            jquery: "gallery/spm_modules/jquery/1.11.1/jquery.min.js",
            "jquery-ui": "gallery/spm_modules/jquery-ui/1.11.1/jquery-ui.js",
            "jquery-ui-css": "gallery/spm_modules/jquery-ui/1.11.1/jquery-ui.css",
            "jquery-easyui": "gallery/spm_modules/jquery-easyui/1.4.2/jquery.easyui.min.js",
            "easyui-lang-zh": "gallery/spm_modules/jquery-easyui/1.4.2/easyui-lang-zh_CN.js",
            jstree: "gallery/spm_modules/jstree/3.0.2/jstree",
            spinner: "gallery/spm_modules/spinner/jquery-ui.js",
            json: "gallery/spm_modules/json3/3.3.2/lib/json3.min.js",
            bootstrap: "gallery/spm_modules/bootstrap/3.3.1/js/bootstrap.min.js",
            datepicker: "gallery/spm_modules/bootstrap-datepicker/1.3.0/bootstrap-datepicker.js",
            "jquery.fileupload": "gallery/spm_modules/fileupload/jquery.fileupload.js",
            "jquery.ui.widget": "gallery/spm_modules/fileupload/vendor/jquery.ui.widget.js",
            "jquery.iframe-transport": "gallery/spm_modules/fileupload/jquery.iframe-transport.js",
            datetimepicker: "gallery/spm_modules/bootstrap-datetimepicker/js/bootstrap-datetimepicker",
            highcharts: "gallery/spm_modules/highcharts/4.0.4/highcharts",
            "api-common": "assets/common/js/api-common",
            "arale-cookie": "assets/common/js/arale-cookie",
            authority: "assets/common/js/ade/authority",
            "ade-common": "assets/common/js/ade/ade-common",
            common: "assets/common/js/common",
            cookie: "assets/common/js/cookie",
            dialog: "assets/common/js/dialog",
            md5: "assets/common/js/md5",
            memory: "assets/common/js/memory",
            nav: "assets/common/js/nav",
            api: "assets/common/js/old/api-main",
            filter: "assets/common/js/filter",
            services: "assets/common/js/services",
            login: "assets/common/js/login",
            "login-dialog": "assets/common/js/login-dialog",
            cropper: "assets/common/plugins/cropper/cropper",
            exports: "assets/common/js/exports",
            "ade-api-common": "assets/common/js/ade/ade-api-common",
            "ade-common-UI": "assets/common/js/ade/ade-common-UI",
            "ade-nav": "assets/common/js/ade/ade-nav",
            detail: "commonPlugin/list/detail",
            iCheck: "commonPlugin/iCheck/icheck",
            ajaxfileupload: "commonPlugin/ajaxfileupload/ajaxfileupload",
            customer: "commonPlugin/customer/jquery.mCustomScrollbar",
            lunbo: "commonPlugin/lunbo/js/lunbo",
            pagebar: "commonPlugin/pagebar/pagebar",
            echarts: "commonPlugin/echarts/echarts",
            moment: "commonPlugin/fullcalendar/moment.min",
            fullcalendar: "commonPlugin/fullcalendar/fullcalendar",
            mousewheel: "commonPlugin/customer/mousewheel",
            autocomplete: "commonPlugin/customer/autocomplete",
            code: "commonPlugin/code/jquery.qrcode.min",
            freewall: "commonPlugin/freewall/freewall",
            modernizr: "commonPlugin/snow/modernizr",
            snow: "commonPlugin/snow/snow",
            cxselect: "commonPlugin/cxSelect/jquery.cxselect",
            regexp: "commonPlugin/ade/regexp",
            niceScroll: "commonPlugin/niceScroll/jquery.nicescroll"
        },
        map: [[/^(.*\/static\/.*\.(?:css|js))(?:.*)$/i, "$1?t=1544512756967"]]
    }),
    window.rootPath = window.relativeUrl + "static",
    window.rootPath = window.relativeUrl + "build/src/static",
    seajs.data.debug = !0,
    define("assets/common/js/api-common", ["jquery"],
        function(a) {
            var b = a("jquery"),
                c = (a("common"), a("cookie"), a("memory")),
                d = a("md5");
            b.ajaxSetup({
                type: "POST",
                dataType: "json"
            });
            var e = c.Constants.Login.LoginFail,
                f = c.urlBase,
                g = function(a, e) {
                    var f = [];
                    b.each(e,
                        function(a, c) {
                            f.push(b.trim(a))
                        }),
                        f.sort();
                    var g = {},
                        i = "";
                    0 == a.indexOf("http://") ? i += a.slice(a.indexOf("/", 7)) + "?AppKey=" + c.AppKey: 0 == a.indexOf("https://") ? i += a.slice(a.indexOf("/", 8)) + "?AppKey=" + c.AppKey: i = a + "?AppKey=" + c.AppKey,
                        b(f).each(function() {
                            var a = this;
                            g[a] = e[a],
                                i += "&" + a + "=" + e[a]
                        });
                    var j = h();
                    return g.nonce = j,
                        i += "&nonce=" + j,
                        g.xyz = d(i),
                        {
                            objParameter: g
                        }
                },
                h = function() {
                    for (var a = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f"], b = 0; b < 500; b++)
                        for (var c = "", d = 0; d < 9; d++) {
                        var e = Math.floor(16 * Math.random());
                        c += a[e]
                    }
                    return c
                },
                i = function(a) {
                    window.open("/public/info/detail.html?account=" + a)
                },
                j = function(a, d, f, h, i, j) {
                    var k = g(a, d),
                        l = k.objParameter;
                    b.ajax({
                        url: a + c.accessSuffix,
                        async: f,
                        data: l,
                        success: function(a) {
                            a.success && ( - 999 == a.value ? j == e.Option_None ? h(a.value) : j == e.Option_NeedLogin ? window.location.href = c.rootUrl.main + "error.html": j == e.Option_ShowFlag && h(e.LoginFailFlag) : -1e4 == a.value ? window.location.href = c.rootUrl.main + "frozen.html": h(a.value))
                        },
                        error: function(a) {
                            b.isFunction(i) && i(a)
                        }
                    })
                },
                k = function(a, b, c, d, f) {
                    j(a, b, c, d, f, e.Option_None)
                },
                l = function(a, b, c, d) {
                    k(a, b, !0, c, d)
                },
                m = function(a, b, c, d) {
                    k(a, b, !1, c, d)
                },
                n = function(a, b, c, d, f) {
                    j(a, b, c, d, f, e.Option_NeedLogin)
                },
                o = function(a, b, c, d) {
                    n(a, b, !0, c, d)
                },
                p = function(a, b, c, d, f) {
                    j(a, b, c, d, f, e.Option_ShowFlag)
                },
                q = function(a, b, c, d) {
                    p(a, b, !0, c, d)
                },
                r = function(a, c) {
                    var d = b("<form>");
                    d.attr("style", "display:none"),
                        d.attr("target", ""),
                        d.attr("method", "post"),
                        d.attr("action", a),
                        b("body").append(d);
                    for (var e in c) {
                        var f = b("<input>");
                        f.attr("type", "hidden"),
                            f.attr("name", e),
                            f.attr("value", c[e]),
                            d.append(f)
                    }
                    d.submit()
                },
                s = function(a) {
                    o(f + "account/ade/getCurrentTime", {},
                        a)
                },
                t = function(a, b) {
                    o(f + "account/ade/open/getInvite", {
                            uuid: a
                        },
                        b)
                },
                u = function(a) {
                    o(f + "sys/user/wallet/getWalletInfo", {},
                        a)
                },
                v = function(a, b, c) {
                    o(f + "account/ade/getDealAccountInfo", {
                            title: a,
                            type: b
                        },
                        c)
                },
                w = function() {
                    var a = c.rootUrl.common + "assets/common/plugins/upload/lib/plupload-2.1.2/js/",
                        d = function() {
                            var a = f + "aly/auth/";
                            return {
                                auth: function(b, c, d) {
                                    o(a + b, {},
                                        d)
                                },
                                getDir: function(b, c) {
                                    o(a + "getDir", {
                                            dir: b
                                        },
                                        c)
                                }
                            }
                        } (),
                        e = {},
                        g = {},
                        h = function(a, b, c, d, e) {
                            var f = c.substr(c.lastIndexOf(".") + 1),
                                h = d + "." + f,
                                i = b.e,
                                j = i + "/" + h;
                            g[e.id] = {
                                md5: d,
                                uploadProgressFun: void 0,
                                fileUrl: {
                                    url: j + "?uuid=" + ((new Date).getTime() + Math.random()),
                                    dataUrl: j
                                }
                            },
                                a.setOption({
                                    url: i,
                                    multipart_params: {
                                        OSSAccessKeyId: b.a,
                                        policy: b.b,
                                        signature: b.c,
                                        expire: parseInt(b.f),
                                        key: h,
                                        success_action_status: "200",
                                        callback: ""
                                    }
                                }),
                                a.start()
                        },
                        i = function(a, b, c) {
                            e[c.apiName] || (e[c.apiName] = {
                                expire: 0,
                                data: {},
                                dir: ""
                            });
                            var f = {};
                            if (c.fileName && (f.fileName = c.fileName), (new Date).getTime() + 1e4 > e[c.apiName].expire || c.request && !0 === c.request) d.auth(c.apiName, f,
                                function(d) {
                                    if (d.a) {
                                        var f = d.d;
                                        e[c.apiName] = {
                                            expire: parseInt(d.f),
                                            data: d,
                                            dir: f
                                        },
                                            h(a, d, b, f, c)
                                    }
                                });
                            else {
                                var i = g[c.id].md5;
                                if (i) h(a, e[c.apiName].data, b, i, c);
                                else if (f.fileName) h(a, e[c.apiName].data, b, f.fileName, c);
                                else {
                                    var j = e[c.apiName].dir;
                                    j = j.substr(0, j.lastIndexOf("/")),
                                        d.getDir(j,
                                            function(d) {
                                                h(a, e[c.apiName].data, b, d, c)
                                            })
                                }
                            }
                        },
                        j = function(c) {
                            var d = new plupload.Uploader({
                                runtimes: "html5,flash,silverlight,html4",
                                browse_button: c.id + "",
                                flash_swf_url: a + "Moxie.swf",
                                silverlight_xap_url: a + "Moxie.xap",
                                url: "https://oss-cn-hangzhou.aliyuncs.com",
                                filters: {
                                    mime_types: [{
                                        title: "Image files",
                                        extensions: c.suffixs
                                    }],
                                    max_file_size: c.fileSize,
                                    prevent_duplicates: !0
                                },
                                init: {
                                    PostInit: function() {},
                                    FilesAdded: function(a, e) {
                                        b.isFunction(c.uploadBefore) && c.uploadBefore(a, e),
                                            i(d, e[0].name, c)
                                    },
                                    BeforeUpload: function(a, b) {},
                                    UploadProgress: function(a, d) {
                                        var e = g[c.id].uploadProgressFun;
                                        e ? e(a, d, d.percent) : b.isFunction(c.uploadProgress) && (g[c.id].uploadProgressFun = c.uploadProgress(a, d, d.percent))
                                    },
                                    FileUploaded: function(a, d, e) {
                                        b.isFunction(c.uploadSuccess) && c.uploadSuccess(a, d, 200 == e.status, g[c.id].fileUrl, e.response)
                                    },
                                    Error: function(a, d) {
                                        b.isFunction(c.uploadError) && c.uploadError(a, d.code, d.response)
                                    }
                                }
                            });
                            d.init()
                        };
                    return function(c) {
                        b.ajax({
                            url: a + "plupload.full.min.js",
                            dataType: "script",
                            cache: !0,
                            success: function() {
                                j(c)
                            }
                        })
                    }
                } (),
                x = {};
            return x.common4init = function() {
                String.HasText = function(a) {
                    try {
                        if (void 0 === a) return ! 1;
                        if (null == a) return ! 1;
                        if ("null" == a) return ! 1;
                        if ("undefined" == a) return ! 1;
                        if ("string" == typeof a && (a = a.replace(/(^\s*)|(\s*$)/g, "")), "" === a) return ! 1
                    } catch(b) {
                        return ! 1
                    }
                    return ! 0
                }
            } (),
                x.common4getUrlParam = function(a) {
                    var b = {};
                    if ( - 1 != a.indexOf("?")) for (var c = a.split("?")[1], d = c.split("&"), e = 0; e < d.length; e++) b[d[e].split("=")[0]] = unescape(d[e].split("=")[1]);
                    return b
                },
                x.request = function(a) {
                    var d = {};
                    d.restDate = function(b) {
                        var c = {};
                        return c.url = a.url,
                            c.async = !String.HasText(a.async) || a.async,
                            c.success = a.success,
                            c.fail = a.fail,
                            c.failFlag = !String.HasText(a.failFlag) || a.async,
                            c.param = a.param,
                            c
                    },
                        d.pub = function(a) {
                            var d = a.ajax,
                                f = "POST" == d.method ? "application/x-www-form-urlencoded": "application/json",
                                h = a.url,
                                i = a.async,
                                j = a.success,
                                k = a.fail,
                                l = a.failFlag,
                                a = a.param,
                                m = g(h, a),
                                n = m.objParameter;
                            if ("DELETE" == d.method || "PUT" == d.method) {
                                var o = "";
                                for (var p in n) o += p + "=" + n[p] + "&";
                                o = o.substring(0, o.length - 1),
                                    h = h + "?" + o
                            }
                            b.ajax({
                                url: h + c.accessSuffix,
                                type: d.method,
                                contentType: f,
                                async: i,
                                data: n,
                                beforeSend: function() {
                                    var a = this;
                                    "GET" == d.method && a.url.indexOf("&_=") > 0 && (a.url = a.url.split("&_=")[0])
                                },
                                success: function(a) {
                                    a.success && ( - 999 == a.value ? l == e.Option_None ? j(a.value) : l == e.Option_NeedLogin ? window.location.href = c.rootUrl.main + "error.html": l == e.Option_ShowFlag && j(e.LoginFailFlag) : -1e4 == a.value ? window.location.href = c.rootUrl.main + "frozen.html": j(a.value))
                                },
                                error: function(a) {
                                    b.isFunction(k) && k(a)
                                }
                            })
                        };
                    var f = d.restDate();
                    f.ajax = {
                        method: a.method
                    },
                        d.pub(f)
                },
                {
                    getCommonDataSync: k,
                    getCommonData: l,
                    getCommonDataSynchronous: m,
                    getNeedLoginDataSync: n,
                    getNeedLoginData: o,
                    getShowFlagDataSync: p,
                    getShowFlagData: q,
                    request: x.request,
                    download: r,
                    getCurrentTime: s,
                    getInvite: t,
                    getWalletInfo: u,
                    getDealAccountInfo: v,
                    showDetail: i,
                    sortOptions: g,
                    setNonce: h,
                    uploadFile: w
                }
        }),
    define("assets/common/js/common", ["jquery"],
        function(a) {
            function b(a, b) {
                var c;
                return c = b.log1p_mark,
                    parseFloat(c).toFixed(1)
            }
            function c(a) {
                var b = parseInt(.3 * e(window).width());
                a.css({
                    width: b
                });
                var c = a.height(),
                    d = (e(window).height() - c) / 2 + e(window).scrollTop(),
                    f = (e(window).width() - b) / 2;
                d < 50 && (d = 50),
                    a.css({
                        top: d,
                        left: f
                    }),
                    e(".mask").show(),
                    a.show()
            }
            function d() {
                this.content = new Array
            }
            var e = a("jquery"),
                f = a("cookie"),
                g = a("memory"),
                h = !0;
            Date.prototype.Format = function(a) {
                var b = {
                    "M+": this.getMonth() + 1,
                    "d+": this.getDate(),
                    "h+": this.getHours(),
                    "m+": this.getMinutes(),
                    "s+": this.getSeconds(),
                    "q+": Math.floor((this.getMonth() + 3) / 3),
                    S: this.getMilliseconds()
                };
                /(y+)/.test(a) && (a = a.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length)));
                for (var c in b) new RegExp("(" + c + ")").test(a) && (a = a.replace(RegExp.$1, 1 == RegExp.$1.length ? b[c] : ("00" + b[c]).substr(("" + b[c]).length)));
                return a
            },
                String.prototype.DateFormat = function(a) {
                    return "" != this ? new Date(this.replace(/-/g, "/").replace(".0", "")).Format(a) : this
                },
                String.prototype.CaseDate = function(a) {
                    if ("" != this) {
                        var b = new Date(this.replace(/-/g, "/").replace(".0", ""));
                        return a && (b = b.Format(a)),
                            b
                    }
                    return this
                },
                String.prototype.trim = function() {
                    return this.replace(/^\s\s*/, "").replace(/\s\s*$/, "")
                },
                String.prototype.replaceRN = function() {
                    return this.replace(/[\r\n]/g, "")
                },
                String.prototype.getActualLength = function() {
                    return this.replace(/[^\x00-\xff]/g, "xx").length
                },
                String.prototype.getSubStr = function(a) {
                    return this ? this.getActualLength() > a ? this.substr(0, this.getSubNum(a)) + "...": this: ""
                },
                String.prototype.getSubStrFixedlength = function(a) {
                    return this.getActualLength() > a ? this.substr(0, this.getSubNum(a)) : this
                },
                String.prototype.getSubNum = function(a) {
                    for (var b = 0,
                             c = this.length,
                             d = this,
                             e = 0; e < d.length; e++) if (1 == (d[e] + "").replace(/[^\x00-\xff]/g, "xx").length ? b += 1 : b += 2, b > a) {
                        c = e;
                        break
                    }
                    return c
                },
                String.prototype.getInterceptedStr = function(a) {
                    if (this.replace(/[^\x00-\xff]/g, "xx").length <= a) return this;
                    for (var b, c = "",
                             d = 0,
                             e = 0; (b = this.charAt(e)) && (c += b, !((d += null != b.match(/[^\x00-\xff]/) ? 2 : 1) >= a)); e++);
                    return c
                },
                String.prototype.getLength = function() {
                    for (var a = 0,
                             b = this.length,
                             c = -1,
                             d = 0; d < b; d++) c = this.charCodeAt(d),
                        a += c >= 0 && c <= 128 ? 1 : 2;
                    return a
                },
                String.prototype.contain = function(a) {
                    return "" != this && ("" == a || ("," + this + ",").indexOf("," + a + ",") >= 0)
                },
                String.prototype.remove = function(a) {
                    if ("" == this) return this;
                    if ("" == a) return this;
                    var b = ("," + this + ",").indexOf("," + a + ",");
                    return 0 == b ? this.substring(a.length + 1) : this.substring(0, b - 1) + this.substring(b + a.length)
                },
                String.prototype.bitContain = function(a) {
                    if ("" == this || "" == a) return ! 1;
                    if ("0" == this || "0" == a) return ! 1;
                    var b = parseInt(this).toString(2),
                        c = parseInt(a).toString(2);
                    if (b.length < c.length) return ! 1;
                    var d = b.length - c.length;
                    return "0" != b.substring(d, d + 1)
                },
                String.prototype.bitSet = function(a) {
                    if ("" == this || "" == a) return this;
                    if ("0" == this) return a;
                    if ("0" == a) return this;
                    var b = parseInt(this).toString(2),
                        c = parseInt(a).toString(2);
                    return b.length < c.length ? parseInt(c.substring(0, c.length - b.length) + b, 2).toString(10) : parseInt(b.substring(0, b.length - c.length) + "1" + b.substring(b.length - c.length + 1), 2).toString(10)
                },
                String.prototype.bitUnSet = function(a) {
                    if ("" == this || "" == a) return this;
                    if ("0" == this) return this;
                    if ("0" == a) return this;
                    var b = parseInt(this).toString(2),
                        c = parseInt(a).toString(2);
                    return b.length < c.length ? this: parseInt(b.substring(0, b.length - c.length) + "0" + b.substring(b.length - c.length + 1), 2).toString(10)
                },
                String.prototype.pastTime = function(a) {
                    var b = a ? a.getTime() : (new Date).getTime(),
                        c = new Date(Date.parse(this.replace(/\-/g, "/").replace(/\.0/g, ""))).getTime(),
                        d = b - c,
                        e = 6e4,
                        f = 60 * e,
                        g = 24 * f,
                        h = d / g,
                        i = d / f,
                        j = d / e,
                        k = j % 60 > 0 ? j % 60 : 0;
                    return h >= 1 ? parseInt(h) + "天前": i >= 1 ? parseInt(i) + "小时前": j >= 1 ? parseInt(k) + "分钟前": "刚刚"
                },
                String.HasText = function(a) {
                    try {
                        if (void 0 === a) return ! 1;
                        if (null == a) return ! 1;
                        if ("null" == a) return ! 1;
                        if ("undefined" == a) return ! 1;
                        if ("string" == typeof a && (a = a.replace(/(^\s*)|(\s*$)/g, "")), "" === a) return ! 1
                    } catch(b) {
                        return ! 1
                    }
                    return ! 0
                },
                String.changeUrl = function(a, b, c) {
                    if (a && "string" == typeof a) {
                        if (h) return "code" === b ? "https://open.weixin.qq.com/qr/code?username=" + a: "url(https://open.weixin.qq.com/qr/code?username=" + a + ") center/" + (c || "500%") + " no-repeat scroll";
                        var d = "head";
                        b && "string" == typeof b && (d = b);
                        var e = "",
                            f = a;
                        return e = a.length > 4 ? f.substr(f.length - 4, f.length).split("").reverse().join("/") + "/" + a: "a/" + a,
                            e = d + "/" + e + ".png",
                            "code" === d ? "https://img-weixin-url.newrank.cn/img/" + e: "url(https://img-weixin-url.newrank.cn/img/" + e + ") center/100% no-repeat scroll"
                    }
                },
                String.Trim = function(a) {
                    return String.HasText(a) && (a = a.replace(/(^\s*)|(\s*$)/g, "")),
                        a
                },
                Array.prototype.isContains = function(a) {
                    for (var b = 0; b < this.length; b++) if (this[b] == a) return ! 0;
                    return ! 1
                },
                Array.prototype.remove = function(a) {
                    var b = this.indexOf(a);
                    b > -1 && this.splice(b, 1)
                },
                String.prototype.getTime = function() {
                    return new Date(Date.parse(this.replace(/-/g, "/").replace(/\.0/g, ""))).getTime()
                };
            var i = function(a, b, c) {
                    void 0 !== c ? (f.cookie(a, b, {
                        expires: c,
                        path: appBase
                    }), f.cookie(a, b, {
                        expires: c,
                        path: "/"
                    }), f.cookie(a, b, {
                        expires: c,
                        path: appBase,
                        domain: appDomain
                    }), e.cookie(a, b, {
                        expires: c,
                        path: "/",
                        domain: appDomain
                    })) : (f.cookie(a, b, {
                        expires: c,
                        path: appBase
                    }), f.cookie(a, b, {
                        expires: c,
                        path: "/"
                    }), f.cookie(a, b, {
                        path: appBase,
                        domain: appDomain
                    }), f.cookie(a, b, {
                        path: "/",
                        domain: appDomain
                    }))
                },
                j = function(a) {
                    return f.getCookie(a)
                };
            window.molHover = function(a, b, c, d) {
                e(a).on({
                    mouseenter: function() {
                        b && e(this).find("i").css({
                            "background-position": b
                        }),
                        d && (e(this).css({
                            "background-color": d
                        }), e(this).css({
                            color: "#fff"
                        }))
                    },
                    mouseleave: function() {
                        c && e(this).find("i").css({
                            "background-position": c
                        }),
                        d && (e(this).css({
                            "background-color": "#fff"
                        }), e(this).css({
                            color: d
                        }))
                    }
                })
            },
                window.picHover = function(a, b, c) {
                    e(a).on({
                        mouseenter: function() {
                            e(this).css({
                                "background-position": b
                            })
                        },
                        mouseleave: function() {
                            e(this).css({
                                "background-position": c
                            })
                        }
                    })
                },
                window.showModel = function(a) {
                    e(a).each(function() {
                        e("" + this).show()
                    })
                };
            var k = function(a) {
                var b = location.search,
                    c = new Object;
                if ( - 1 != b.indexOf("?")) {
                    var d = b.substr(1);
                    strs = d.split("&");
                    for (var e = 0; e < strs.length; e++) c[strs[e].split("=")[0]] = a ? decodeURIComponent(strs[e].split("=")[1]) : unescape(strs[e].split("=")[1])
                }
                return c
            };
            e(document).delegate(".editor-input", "keyup",
                function() {
                    var a = e(this).val().length;
                    e(this).next().find("span").html(a)
                }),
                e(document).delegate(".text-input", "focus blur",
                    function(a) {
                        var b = e(this),
                            c = a.handleObj.origType,
                            d = b.attr("tip"),
                            f = b.val();
                        "focus" == c && d === f ? b.val("") : "blur" != c || d !== f && 0 != f.trim().length || b.val(d)
                    });
            var l = function(a, b) {
                    a = void 0 === a ? "0": 0 == a.trim().length ? "0": a.trim();
                    var c = parseInt(a),
                        d = !1;
                    if (c >= 1e5 && !b && (a = Math.floor(c / 1e4) + "万+", d = !0), a.length <= 3) return a;
                    if (!/^(\+|-)?(\d+)(\.\d+)?$/.test(a)) return a;
                    var e = RegExp.$1,
                        b = RegExp.$2,
                        f = RegExp.$3,
                        g = new RegExp;
                    for (g.compile("(\\d)(\\d{3})(,|$)"); g.test(b);) b = b.replace(g, "$1,$2$3");
                    return e + "" + b + f + (d ? "+": "")
                },
                m = function(a, b) {
                    var c = "asc" == a ? ">": "<";
                    return new Function("a", "b", "return a." + b + c + "b." + b + "?1:-1")
                },
                n = function(a) {
                    return void 0 === a || 0 == e.trim(a).length ? "": e.trim(a)
                },
                o = function(a, b) {
                    return void 0 === a || 0 == e.trim(a).length ? b || "": e.trim(a)
                },
                p = function(a, b, c) {
                    return g.rootUrl.main + "redirect?project=" + a + "&from=" + b + "&to=" + c
                },
                q = function() {
                    var a = {
                        versions: function() {
                            var a = navigator.userAgent;
                            navigator.appVersion;
                            return {
                                ie: a.indexOf("Trident") > -1,
                                presto: a.indexOf("Presto") > -1,
                                webKit: a.indexOf("AppleWebKit") > -1,
                                gecko: a.indexOf("Gecko") > -1 && -1 == a.indexOf("KHTML"),
                                mobile: !!a.match(/AppleWebKit.*Mobile.*/),
                                ios: !!a.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/),
                                android: a.indexOf("Android") > -1 || a.indexOf("Linux") > -1,
                                iPhone: a.indexOf("iPhone") > -1 || a.indexOf("Mac") > -1,
                                iPad: a.indexOf("iPad") > -1,
                                webApp: -1 == a.indexOf("Safari"),
                                isGeqIE10: a.indexOf("Trident") > -1 && ( - 1 != a.indexOf("MSIE 10.0") || -1 != a.indexOf("rv:11.0"))
                            }
                        } ()
                    };
                    return {
                        movebrowser: a.versions.mobile,
                        iosTerminal: a.versions.ios,
                        androidTerminal: a.versions.android,
                        iPhone: a.versions.iPhone,
                        iPad: a.versions.iPad,
                        userAgent: navigator.userAgent,
                        ie: a.versions.ie,
                        isGeqIE10: a.versions.isGeqIE10
                    }
                } (),
                r = function() {
                    return e("body").append('<div class="m-return-top" id="m_return_top" style="position: fixed;bottom:60px;right: 20px;z-index:1002;width: 60px;height: 60px;display: none;"><div class=" iconfont icon-xdn-back" style="font-size: 2.5rem;color:#333;margin: 14px 0 0 20px;opacity: .5;filter: alpha(opacity=50);"></div></div>'),
                        {
                            init: function() {
                                var a = e(".m-return-top");
                                0 == e(".m-footer").length ? e("#m_return_top").css("bottom", "20px") : e("#m_return_top").css("bottom", "60px"),
                                    a.click(function(a) {
                                        a.stopPropagation(),
                                            e("html, body").stop().animate({
                                                    scrollTop: 0
                                                },
                                                "fast")
                                    }),
                                    e(window).scroll(function() {
                                        e(window).scrollTop() > 600 ? a.show() : a.hide()
                                    })
                            } ()
                        }
                },
                s = function(a) {
                    var b = "index" == a ? "active": "",
                        c = "list" == a ? "active": "",
                        d = "mine" == a ? "active": "",
                        f = ['<div class="m-footer">', '<a href="' + g.rootUrl.main + 'm/index.html">', '<div class="f-div f-index ' + b + '">', '<span class="iconfont icon-xdn-home-default"></span>', "<p>首页</p>", "</div>", "</a>", '<a href="' + g.rootUrl.main + 'm/list.html">', '<div class="f-div f-list ' + c + '">', '<span class="iconfont icon-xdn-bangdan-default"></span>', "<p>榜单</p>", "</div>", "</a>", '<a href="' + g.rootUrl.main + 'm/mine.html">', '<div class="f-div f-about ' + d + '">', '<span class="iconfont icon-xdn-guanyu-default"></span>', "<p>我的</p>", "</div>", "</a>", "</div>"];
                    return {
                        init: function() {
                            0 == e(".m-footer").length && e("body").append(f.join(""))
                        } ()
                    }
                },
                t = function(a, b) {
                    var c, d, f = !0;
                    if ("" != a && null != a && "null" != a) {
                        void 0 != c && clearTimeout(c),
                        f && (d = e('<div style=" position: fixed;padding: 5px 20px;z-index: 1001;text-align: center;background-color: #000;color: rgb(255, 255, 255);border-radius: .7rem;line-height: 34px;border: none;font-size: 18px;opacity: .4;"></div>').appendTo(e("body")), f = !1),
                            d.html(a).show();
                        var g = d.width() + 40,
                            h = e(window).width(),
                            i = (h - g) / 2,
                            j = b || 100;
                        d.css({
                            top: j,
                            left: i
                        }),
                            c = setTimeout(function() {
                                    d.hide()
                                },
                                700)
                    }
                };
            return String.prototype.codeText = function() {
                var a, b, c, d;
                for (a = "", c = this.length, b = 0; b < c; b++) d = this.charCodeAt(b),
                    d >= 1 && d <= 127 ? a += this.charAt(b) : d > 2047 ? (a += String.fromCharCode(224 | d >> 12 & 15), a += String.fromCharCode(128 | d >> 6 & 63), a += String.fromCharCode(128 | d >> 0 & 63)) : (a += String.fromCharCode(192 | d >> 6 & 31), a += String.fromCharCode(128 | d >> 0 & 63));
                return a
            },
                d.prototype.append = function(a) {
                    this.content.push(a)
                },
                d.prototype.prepend = function(a) {
                    this.content.unshift(a)
                },
                d.prototype.toString = function() {
                    return this.content.join("")
                },
                {
                    getRequest: k,
                    splitRequestParam: function(a) {
                        var b = new Object,
                            c = a.indexOf("?");
                        if ( - 1 != c) for (var d = a.substr(c + 1), e = d.split("&"), f = 0; f < e.length; f++) {
                            var g = e[f],
                                h = g.indexOf("=");
                            h > 0 && (b[g.substr(0, h)] = unescape(g.substr(h + 1)))
                        }
                        return b
                    },
                    formatNumGt1w: l,
                    cookie: {
                        setCookie: i,
                        getCookie: j
                    },
                    getRankMark: b,
                    showRemind: c,
                    sortByArray: m,
                    browser: q,
                    terminal: q,
                    backgroundUrl: h,
                    doNull: n,
                    StringBuffer: d,
                    doNullDefault: o,
                    formatUrl: p,
                    mobileReturnUp: r,
                    mobileBottomMenu: s,
                    showMobileTip: t
                }
        }),
    define("assets/common/js/cookie", ["memory"],
        function(a) {
            var b = a("memory"),
                c = a("arale-cookie");
            return {
                setCookie: function(a, d, e) {
                    null != d && void 0 != d || (d = ""),
                        void 0 !== e ? (c.set(a, d, {
                            expires: -1,
                            path: b.appBase
                        }), c.set(a, d, {
                            expires: -1,
                            path: "/"
                        }), c.set(a, d, {
                            expires: e,
                            path: b.appBase,
                            domain: "." + b.appDomain
                        }), c.set(a, d, {
                            expires: e,
                            path: "/",
                            domain: "." + b.appDomain
                        }), c.set(a, d, {
                            expires: e,
                            path: b.appBase,
                            domain: b.appDomain
                        }), c.set(a, d, {
                            expires: e,
                            path: "/",
                            domain: b.appDomain
                        })) : (c.set(a, d, {
                            expires: -1,
                            path: b.appBase
                        }), c.set(a, d, {
                            expires: -1,
                            path: "/"
                        }), c.set(a, d, {
                            path: b.appBase,
                            domain: "." + b.appDomain
                        }), c.set(a, d, {
                            path: "/",
                            domain: "." + b.appDomain
                        }), c.set(a, d, {
                            path: b.appBase,
                            domain: b.appDomain
                        }), c.set(a, d, {
                            path: "/",
                            domain: b.appDomain
                        }))
                },
                getCookie: function(a) {
                    return c.get(a)
                },
                removeCookie: function(a) {
                    c.remove(a, {
                        expires: -1,
                        path: b.appBase
                    }),
                        c.remove(a, {
                            expires: -1,
                            path: "/"
                        }),
                        c.remove(a, {
                            path: b.appBase,
                            domain: "." + b.appDomain
                        }),
                        c.remove(a, {
                            path: b.appBase,
                            domain: b.appDomain
                        }),
                        c.remove(a, {
                            path: "/",
                            domain: b.appDomain
                        })
                }
            }
        }),
    define("assets/common/js/dialog", ["jquery"],
        function(a, b, c) {
            var d, e, f = a("jquery"),
                g = a("memory"),
                h = function(a) {
                    return f(".mCustomScrollBox").length > 1 ? (f(window).height() - a) / 2 + f(".mCSB_dragger").css("top") : (f(window).height() - a) / 2 + f(window).scrollTop()
                },
                i = !0;
            c.exports.progressBar = {
                show: function() {
                    i && (e = f("<div></div>").addClass("dialog-progressbar-progress"), d = f("<div></div>").addClass("dialog-processbar").append(e).appendTo(f("body")), i = !1, e.progressbar({
                        value: !1,
                        change: function() {},
                        complete: function() {}
                    }), d.dialog({
                        autoOpen: !1,
                        closeOnEscape: !1,
                        resizable: !1
                    })),
                        d.dialog("open")
                },
                update: function(a, b) {
                    e.progressbar("value", a / b * 100)
                },
                close: function() {
                    d.dialog("close")
                }
            },
                c.exports.GetRequest = function() {
                    var a = location.search,
                        b = new Object;
                    if ( - 1 != a.indexOf("?")) {
                        var c = a.substr(1);
                        strs = c.split("&");
                        for (var d = 0; d < strs.length; d++) b[strs[d].split("=")[0]] = unescape(strs[d].split("=")[1])
                    }
                    return b
                },
                c.exports.molHover = function(a, b, c, d) {
                    f(document).delegate(a, "mouseenter",
                        function() {
                            b && f(this).find("i").css({
                                "background-position": b
                            }),
                            d && (f(this).css({
                                "background-color": d
                            }), f(this).css({
                                color: "#fff"
                            }))
                        }),
                        f(document).delegate(a, "mouseleave",
                            function() {
                                c && f(this).find("i").css({
                                    "background-position": c
                                }),
                                d && (f(this).css({
                                    "background-color": "#fff"
                                }), f(this).css({
                                    color: d
                                }))
                            })
                },
                c.exports.molHoverSpe = function(a, b, c, d, e) {
                    f(document).delegate(a, "mouseenter",
                        function() {
                            b && f(this).find("i").css({
                                "background-position": b
                            }),
                            d && f(this).css({
                                color: d
                            })
                        }),
                        f(document).delegate(a, "mouseleave",
                            function() {
                                c && f(this).find("i").css({
                                    "background-position": c
                                }),
                                e && f(this).css({
                                    color: e
                                })
                            })
                },
                c.exports.picHover = function(a, b, c) {
                    f(document).delegate(a, "mouseenter",
                        function() {
                            f(this).css({
                                "background-position": b
                            })
                        }),
                        f(document).delegate(a, "mouseleave",
                            function() {
                                f(this).css({
                                    "background-position": c
                                })
                            })
                },
                c.exports.custom_pop = function(a, b) {
                    f(a).show(),
                        f(a).height(f(document).height()),
                        f(b).show();
                    var c = f(b).width(),
                        d = f(b).height(),
                        e = h(d),
                        g = (f(window).width() - c) / 2;
                    e <= 50 && (e = 50),
                        f(b).css("left", g),
                        f(b).animate({
                                top: e
                            },
                            "fast")
                },
                c.exports.customDialog = function(a) {
                    var b = a.width || 500,
                        c = a.height || 500,
                        d = a.opacity || .3,
                        e = a.cancel || {
                            style: {},
                            name: "取消"
                        },
                        g = e.style || {},
                        h = e.name || "取消",
                        i = a.ok || {
                            style: {},
                            name: "确定"
                        },
                        j = i.style || {},
                        k = i.name || "确定",
                        l = a.element || "",
                        m = f("body"),
                        n = f("<div class='dialog-top'></div>").css({
                            "margin-left": "-" + b / 2 + "px",
                            "margin-top": "-" + c / 2 + "px"
                        }),
                        o = f("<div class='mask'></div>").css("opacity", d).show(),
                        p = f("<div class='dialog-button-div'></div>"),
                        q = f("<i class='ad-pic dialog-close'></i>"),
                        r = f("<a class='dialog_cancel dialog-button-a'>" + h + "</a>").css(g),
                        s = f("<a class='dialog_ok dialog-button-a'>" + k + "</a>").css(j);
                    f(o).appendTo(m),
                        f(n).appendTo(m),
                        f(l).appendTo(n),
                        f(p).appendTo(n),
                        f(q).appendTo(n),
                        f(r).appendTo(p).css({
                            float: "left",
                            "margin-right": "40px"
                        }),
                        f(s).appendTo(p).css({
                            float: "right"
                        });
                    var t = function() {
                        n.remove(),
                            o.remove()
                    };
                    r.click(function() {
                        f.isFunction(e.click) ? (e.click(), t()) : t()
                    }),
                        s.click(function() {
                            f.isFunction(i.click) && i.click()
                        }),
                        q.click(function() {
                            f.isFunction(e.close) ? (close.close(), t()) : t()
                        }),
                    a.init && a.init(s, r, p)
                },
                c.exports.custom_quit = function(a, b) {
                    f(a).hide(),
                        f(b).hide()
                },
                c.exports.getTime = function(a) {
                    var b = a.split(" ")[0],
                        c = a.split(" ")[1],
                        d = new Date;
                    if (b) {
                        var e = b.split("-");
                        d.setFullYear(e[0]),
                            d.setMonth(parseInt(e[1]) - 1, parseInt(e[2]))
                    }
                    if (c) {
                        var f = c.split(":");
                        d.setHours(f[0]),
                            d.setMinutes(f[1]),
                        f[2] && d.setSeconds(f[2])
                    }
                    return d.getTime()
                },
                c.exports.formatMoney = function(a) {
                    var b = a.split(","),
                        a = "";
                    return f(b).each(function() {
                        a += this
                    }),
                        a
                };
            var j, k, l = !0;
            c.exports.showTopTip = function(a) {
                if ("" != a && null != a && "null" != a) {
                    void 0 != j && clearTimeout(j),
                    l && (k = f("<div></div>").addClass("well").addClass("dialog-tip").appendTo(f("body")), l = !1),
                        k.html(a),
                        k.show();
                    var b = k.width() + 40,
                        c = f(window).width(),
                        d = (c - b) / 2;
                    k.css({
                        top: 30,
                        left: d
                    }),
                        j = setTimeout(function() {
                                k.hide()
                            },
                            2e3)
                }
            };
            var m = function(a, b, c, d) {
                    if (f(".dialog-mask").length <= 0 && f("body").append("<div class='dialog-mask'></div>"), f(".dialog-box").length <= 0) {
                        var e = ['<div class="dialog-box">', '       <div class="modal-content">', '           <div class="modal-header">', '               <button type="button" class="close dialog-box-close" aria-hidden="true">', "               &times;", "               </button>", '               <h4 id="dialog_title" class="modal-title">确认</h4>', "           </div>", '           <div id="dialog_body" class="modal-body">确定要删除吗？</div>', '           <div class="modal-footer">', '               <button id="dialog_no" type="button" class="btn btn-default">关闭</button>', '               <button id="dialog_yes" type="button" class="btn btn-success">提交更改</button>', "       </div>\x3c!-- /.modal-content --\x3e", "</div>"].join("");
                        f("body").append(e),
                            f("#dialog_no").click(function() {
                                f(".dialog-mask").hide(),
                                    f(".dialog-box").hide()
                            }),
                            f(".dialog-box-close").click(function() {
                                f(".dialog-mask").hide(),
                                    f(".dialog-box").hide()
                            })
                    }
                    f(".confirm-mask").show(),
                        f("#dialog_title").html(a),
                        f("#dialog_body").html(b);
                    var g = 400,
                        i = 215,
                        j = h(i),
                        k = (f(window).width() - g) / 2;
                    j < 50 && (j = 50),
                        f(".dialog-box").show(),
                        f(".dialog-box").css({
                            top: 0,
                            left: k
                        }),
                        f(".dialog-box").animate({
                                top: j
                            },
                            "fast"),
                        f("#dialog_yes").unbind("click"),
                        f("#dialog_yes").click(function() {
                            f(".dialog-mask").hide(),
                                f(".dialog-box").hide(),
                                c(d)
                        })
                },
                n = function(a, b) {
                    var c = a.title,
                        d = a.content,
                        e = 400,
                        g = 448;
                    if (c = a.title, d = a.content, e = String.HasText(a.width) ? a.width: e, g = String.HasText(a.height) ? a.height: height, f(".dialog-mask").length <= 0 && f("body").append("<div class='dialog-mask'></div>"), f(".dialog-box").length <= 0) {
                        var i = ['<div class="dialog-box" >', '       <div class="modal-content" style="width:' + e + 'px">', '           <div class="modal-header">', '               <button type="button" class="close dialog-box-close" aria-hidden="true">', "               &times;", "               </button>", '               <h4 id="dialog_title" class="modal-title">确认</h4>', "           </div>", '           <div id="dialog_body" class="modal-body">确定要删除吗？</div>', "</div>"].join("");
                        f("body").append(i),
                            f(".dialog-box-close").click(function() {
                                f(".dialog-mask").hide(),
                                    f(".dialog-box").hide()
                            })
                    } else f(".modal-content").css({
                        width: e + "px"
                    });
                    f(".confirm-mask").show(),
                        f("#dialog_title").html(c),
                        f("#dialog_body").html(d);
                    var j = h(g),
                        k = (f(window).width() - e) / 2;
                    j < 50 && (j = 50),
                        f(".dialog-box").show(),
                        f(".dialog-box").css({
                            top: 0,
                            left: k
                        }),
                        f(".dialog-box").animate({
                                top: j
                            },
                            "fast"),
                        f("div.dialog-mask").show(),
                        b()
                };
            c.exports.showProp = n,
                c.exports.showDialog = m;
            var o = function(a, b, c, d, e) {
                f(".dialog-mask").length <= 0 && f("body").append("<div class='dialog-mask'></div>");
                var g = f('<button type="button" class="close dialog-box-close" ' + (d ? 'style="color:#fff"': "") + 'aria-hidden="true">&times;</button>');
                a.find(".modal-header").prepend(g);
                var i = "dialog_" + Math.random();
                a.addClass("dialog-custom"),
                    a.width(b),
                    a.height(c);
                var j = function() {
                    f(".dialog-mask").hide(),
                        a.hide()
                };
                return g.click(function(a) {
                    e && e(),
                        j(),
                        a.stopImmediatePropagation()
                }),
                    {
                        hide: function() {
                            j()
                        },
                        show: function() {
                            var d = b,
                                e = c,
                                g = h(e),
                                j = (f(window).width() - d) / 2;
                            g < 100 && (g = 100),
                                a.show(),
                                a.css({
                                    top: 0,
                                    left: j
                                }),
                                a.animate({
                                        top: g
                                    },
                                    "fast"),
                                f(".dialog-mask").show(),
                                f("#" + i).show()
                        }
                    }
            };
            c.exports.makeDialog = o;
            var p = function(a, b, c, d) {
                f(".dialog-mask").length <= 0 && f("body").append("<div class='dialog-mask'></div>");
                var e = f('<button type="button" class="close dialog-box-close" ' + (d ? 'style="color:#fff"': "") + 'aria-hidden="true">&times;</button>');
                a.find(".modal-header").prepend(e);
                var g = "dialog_" + Math.random();
                a.addClass("dialog-custom"),
                    a.width(b),
                    a.height(c);
                var i = function() {
                    f(".dialog-mask").hide(),
                        a.hide()
                };
                return e.click(function(a) {
                    i(),
                        a.stopImmediatePropagation()
                }),
                    {
                        hide: function() {
                            i()
                        },
                        show: function() {
                            var d = b,
                                e = c,
                                i = h(e),
                                j = (f(window).width() - d) / 2;
                            i < 100 && (i = 100),
                                a.show(),
                                a.css({
                                    top: i,
                                    left: j
                                }),
                                f(".dialog-mask").show(),
                                f("#" + g).show()
                        }
                    }
            };
            c.exports.makeDialogNoSlide = p,
                c.exports.showConfirm = function(a, b, c) {
                    if (f(".confirm_box").length <= 0) {
                        var d = ['<div class="confirm-mask"></div>', '<div class="confirm_box">', '       <div class="modal-content">', '           <div class="modal-header">', '               <button type="button" class="close confirm-box-close" aria-hidden="true">', "               &times;", "               </button>", '               <h4 class="modal-title" id="myModalLabel">确认</h4>', "           </div>", '           <div class="modal-body">' + a + "</div>", '           <div class="modal-footer">', '               <button id="confirm_no" type="button" class="btn btn-default">关闭</button>', '               <button id="confirm_yes" type="button" class="btn btn-success">确定</button>', "       </div>\x3c!-- /.modal-content --\x3e", "</div>"].join("");
                        f("body").append(d),
                            f("#confirm_no").click(function() {
                                f(".confirm-mask").hide(),
                                    f(".confirm_box").hide()
                            }),
                            f(".confirm-box-close").click(function() {
                                f(".confirm-mask").hide(),
                                    f(".confirm_box").hide()
                            })
                    }
                    f(".confirm-mask").show();
                    var e = 400,
                        g = 215,
                        i = h(g),
                        j = (f(window).width() - e) / 2;
                    f(".confirm_box").show(),
                        f(".confirm_box").css({
                            top: 0,
                            left: j
                        }),
                        f(".confirm_box").animate({
                                top: i
                            },
                            "fast"),
                        f("#confirm_yes").unbind("click"),
                        f("#confirm_yes").click(function() {
                            f(".confirm-mask").hide(),
                                f(".confirm_box").hide(),
                                b(c)
                        })
                },
                c.exports.showSpeTip = function(a) {
                    if (f(".pop-spe-box").length <= 0) {
                        var b = [f(".tip-mask").length <= 0 ? '<div class="tip-mask"></div>': "", '<div class="pop_box pop-spe-box">', '<div class="pop_head">', '    <p class="pop_remind"><i></i>提示</p>', "</div>", '<div class="pop_body">', '    <p id="pop_info"></p>', "</div>", "</div>"].join("");
                        f("body").append(b)
                    }
                    f(".tip-mask").show();
                    var c = 400,
                        d = 215,
                        e = h(d),
                        g = (f(window).width() - c) / 2;
                    e < 100 && (e = 50),
                        f(".pop_box").show(),
                        f("#pop_info").html(a),
                        f(".pop_box").css({
                            top: e,
                            left: g
                        })
                },
                c.exports.showPaySuccessTip = function(a) {
                    if (f(".pop-pay-box").length <= 0) {
                        var b = [f(".tip-mask").length <= 0 ? '<div class="tip-mask"></div>': "", '<div class="pop-pay-box">', '<div class="pop-head">', '    <p class="pop-remind"><i></i>提示</p>', "</div>", '<div class="pop-pay-body">', '    <p id="pop_pay_info"></p>', '</div><div class="pop-pay-bottom"><button class="pop-pay-btn pop-pay-btn-losing">遇到问题</button><button class="pop-pay-btn pop-pay-btn-win">顺利完成</button></div>', "</div>"].join("");
                        f("body").append(b),
                            f(".pop-pay-btn").on("click",
                                function() {
                                    window.location.reload()
                                })
                    }
                    f(".tip-mask").show();
                    var c = 406,
                        d = 263,
                        e = h(d),
                        g = (f(window).width() - c) / 2;
                    e < 100 && (e = 50),
                        f(".pop-pay-box").show(),
                        f("#pop_pay_info").html(a),
                        f(".pop-pay-box").css({
                            top: e,
                            left: g
                        })
                };
            var q = function() {
                if (f(".pop-spe-box").length <= 0) {
                    var a = ['<div class="dialog-mask-over"></div>', '<div class="mask-over-work">', '<div class="pop_head">', '    <p class="pop_remind"><i></i>提示</p>', "</div>", '<div class="mask-over-work-textts">', "请在完成操作后点击关闭本窗口", "</div>", '<div class="mask-over-work-cancel only-code-check">', "<a>遇到问题?改用唯一码 </a>", "</div>", '<div class="mask-over-work-sucessover">', "顺利完成", "</div>", "</div>"].join("");
                    f("body").append(a)
                }
                f(".mask-over-work-sucessover").click(function() {
                    location.reload()
                }),
                    f(".dialog-mask-over").show(),
                    f(".mask-over-work").show()
            };
            c.exports.showMask = q,
                c.exports.showGuideTip = function(a, b, c) {
                    if (f(".pop-guide-box").length <= 0) {
                        var d = [f(".tip-mask").length <= 0 ? '<div class="tip-mask"></div>': "", '<div class="pop-guide-box">', '<div class="pop_head">', '    <p class="pop_remind"><i></i>提示</p>', "</div>", '<div class="pop_body pop-guide-body">', '    <p id="pop_guide_info"></p>', '    <p class="pop-button"><span id="pop_guide_quit" class="quit">确定</span></p>', "</div>", "</div>"].join("");
                        f("body").append(d),
                            f("#pop_guide_quit").on("click",
                                function() {
                                    f(".tip-mask").hide(),
                                        f(".pop-guide-box").hide(),
                                    b && b(c)
                                })
                    }
                    f(".tip-mask").show();
                    var e = 900,
                        g = 600,
                        i = h(g),
                        j = (f(window).width() - e) / 2;
                    i < 100 && (i = 50),
                        f(".pop-guide-box").show(),
                        f("#pop_guide_info").html(a),
                        f(".pop-guide-box").css({
                            top: i,
                            left: j
                        })
                },
                c.exports.showTip = function(a, b) {
                    if (f(".pop-normal-box-showTip").length <= 0) {
                        var c = [f(".tip-mask").length <= 0 ? '<div class="tip-mask"></div>': "", '<div class="pop_box pop-normal-box pop-normal-box-showTip">', '<div class="pop_head">', '    <p class="pop_remind"><i></i>提示</p>', "</div>", '<div class="pop_body">', '    <p id="pop_info"></p>', '    <p class="pop-button" id="pop-button"><span id="pop_quit" class="quit">确定</span></p>', "</div>", "</div>"].join("");
                        f("body").append(c),
                            f("#pop_quit").on("click",
                                function() {
                                    f(".tip-mask").hide(),
                                        f(".pop_box").hide()
                                })
                    }
                    f(".tip-mask").show();
                    var d = 400,
                        e = 215,
                        g = b || h(e),
                        i = (f(window).width() - d) / 2;
                    g < 100 && (g = 50),
                        f(".pop_box").show(),
                        f("#pop_info").html(a),
                        f(".pop_box").css({
                            top: g,
                            left: i
                        }),
                        f("#pop-button").on("click",
                            function() {
                                f(".tip-mask").remove(),
                                    f(".pop_box").remove(),
                                    f(".pop-normal-box-showTip").remove()
                            })
                },
                c.exports.showAdeTip = function(a, b) {
                    if (f(".pop-normal-box-showTip").length <= 0) {
                        var c = [f(".tip-mask").length <= 0 ? '<div class="tip-mask"></div>': "", '<div class="pop_box pop-normal-box pop-normal-box-showTip">', '<div class="pop_head">', '    <p class="pop_remind"><i></i>提示</p>', "</div>", '<div class="pop_body">', '    <p id="pop_info"></p>', '    <p class="pop-button" id="pop-button"><span id="pop_quit" class="cc-to-register" style="border: 1px solid #000000;color: #000000;">立即注册</span></p>', "</div>", "</div>"].join("");
                        f("body").append(c),
                            f("#pop_quit").on("click",
                                function() {
                                    f(".tip-mask").hide(),
                                        f(".pop_box").hide()
                                })
                    }
                    f(".tip-mask").show();
                    var d = 400,
                        e = 215,
                        g = b || h(e),
                        i = (f(window).width() - d) / 2;
                    g < 100 && (g = 50),
                        f(".pop_box").show(),
                        f("#pop_info").html(a),
                        f(".pop_box").css({
                            top: g,
                            left: i
                        }),
                        f("#pop-button").on("click",
                            function() {
                                f(".tip-mask").remove(),
                                    f(".pop_box").remove(),
                                    f(".pop-normal-box-showTip").remove()
                            })
                },
                c.exports.showConfirmOK = function(a, b, c, d, e) {
                    if (f(".pop-normal-box-showConfirmOK").length <= 0) {
                        var g = [f(".tip-mask").length <= 0 ? '<div class="tip-mask"></div>': "", '<div class="pop_box pop-normal-box pop-normal-box-showConfirmOK">', '<div class="pop_head">', e ? "<p>" + e + "</p>": '<p class="pop_remind"><i></i>提示</p>', "</div>", '<div class="pop_body">', '<p id="pop_info"></p>', '<p class="pop-button" style="float: left;margin:0px 81px 40px 39px;"><span id="pop_canle" class="quit">取消</span></p>', '<p class="pop-button" style="float: left;margin:0px 20px 40px 0;"><span id="pop_quit" style="background-color: #fd8c25;color: #fff;border: none;" class="quit">确定</span></p>', "</div>", "</div>"].join("");
                        f("body").append(g),
                            f("#pop_canle").on("click",
                                function() {
                                    f.isFunction(d) && d(),
                                        f(".tip-mask").remove(),
                                        f(".pop_box").remove(),
                                        f(".pop-normal-box-showConfirmOK").remove()
                                }),
                            f("#pop_quit").on("click",
                                function() {
                                    f.isFunction(c) && c(),
                                        f(".tip-mask").remove(),
                                        f(".pop_box").remove(),
                                        f(".pop-normal-box-showConfirmOK").remove()
                                })
                    }
                    f(".tip-mask").show();
                    var i = 400,
                        j = 215,
                        k = b || h(j),
                        l = (f(window).width() - i) / 2;
                    k < 100 && (k = 50),
                        f(".pop_box").show(),
                        f("#pop_info").html(a);
                    var m = window.pageYOffset + 200;
                    f(".pop_box").css({
                        top: m + "px",
                        left: l
                    })
                },
                c.exports.showAdeLogin = function(a, b, c, d, e, g) {
                    if (f(".pop-normal-box-showConfirmOK").length <= 0) {
                        var i = [f(".tip-mask").length <= 0 ? '<div class="tip-mask"></div>': "", '<div class="pop_box pop-normal-box pop-normal-box-showConfirmOK">', '<div class="pop_head">', g ? "<p>" + g + "</p>": '<p class="pop_remind"><i></i>提示</p>', "</div>", '<div class="pop_body">', '<p id="pop_info_t" style="font-size: 16px;color: #888;margin: 36px auto 20px;"></p>', '<p id="pop_info" style="font-size: 14px;margin: 20px auto 50px;"></p>', '<p class="pop-button" style="float: left;margin:0px 81px 40px 39px;"><span id="pop_canle" class="quit">取消</span></p>', '<p class="pop-button" style="float: left;margin:0px 20px 40px 0;"><span id="pop_quit" style="color: #fff;border: none;" class="quit">登录</span></p>', "</div>", "</div>"].join("");
                        f("body").append(i),
                            f("#pop_canle").on("click",
                                function() {
                                    f.isFunction(e) && e(),
                                        f(".tip-mask").remove(),
                                        f(".pop_box").remove(),
                                        f(".pop-normal-box-showConfirmOK").remove()
                                }),
                            f("#pop_quit").on("click",
                                function() {
                                    f.isFunction(d) && d(),
                                        f(".tip-mask").remove(),
                                        f(".pop_box").remove(),
                                        f(".pop-normal-box-showConfirmOK").remove()
                                })
                    }
                    f(".tip-mask").show();
                    var j = 400,
                        k = 215,
                        l = c || h(k),
                        m = (f(window).width() - j) / 2;
                    l < 100 && (l = 50),
                        f(".pop_box").show(),
                        f("#pop_info_t").html(a),
                        f("#pop_info").html(b);
                    var n = window.pageYOffset + 200;
                    f(".pop_box").css({
                        top: n + "px",
                        left: m
                    })
                },
                c.exports.showLoginTip = function(a, b, c) {
                    if (b) {
                        if ("case" == b && f(".pop-login-box").length <= 0) {
                            var d = ['<div class="mask"></div>', '<div class="pop_box pop-login-box">', '<div class="pop_head">', '    <p class="pop_remind"><i></i>提示</p>', "</div>", '<div class="pop_body">', '    <p id="pop_login_info" class="pop-confirm-info"></p>', '    <div class="pop-buttons">', '<span id="pop_add_quit" class="quit">取消</span>', '<span id="pop_add_case" class="confirm">前往新增</span>', "</div>", "</div>", "</div>"].join("");
                            f("body").append(d),
                                f("#pop_add_quit").on("click",
                                    function() {
                                        f(".mask").hide(),
                                            f(".pop-login-box").hide()
                                    }),
                                f("#pop_add_case").click(function() {
                                    window.location.href = window.relativeUrl + "../account/ade/popularize.html"
                                }),
                                f("#pop_login_info").html(a)
                        }
                    } else {
                        if (f(".pop-login-box").length <= 0) {
                            var d = ['<div class="mask"></div>', '<div class="pop_box pop-login-box">', '<div class="pop_head">', '    <p class="pop_remind"><i></i>提示</p>', "</div>", '<div class="pop_body">', '    <p id="pop_login_info"></p>', '    <div class="pop-buttons">', '<span id="pop_login_quit" class="quit">取消</span>', '<span id="pop_login" class="confirm">登录</span>', "</div>", "</div>", "</div>"].join("");
                            f("body").append(d),
                                f("#pop_login_quit").on("click",
                                    function() {
                                        f(".mask").hide(),
                                            f(".pop-login-box").hide()
                                    }),
                                f("#pop_login").click(function() {
                                    window.location.href = g.rootUrl.main + "public/login/login.html?back=" + escape(window.location.href)
                                })
                        }
                        f("#pop_login_info").html(a + "为注册用户专享权益，请登录或注册。")
                    }
                    f(".mask").show();
                    var e = 400,
                        i = 215,
                        j = h(i),
                        k = (f(window).width() - e) / 2;
                    f(".mask").height(f(window).height()),
                        f(".pop-login-box").show(),
                        f(".pop-login-box").css({
                            top: j,
                            left: k
                        })
                },
                c.exports.showBindTip = function(a, b) {
                    if ("phone" == b) var c = "../account/user/user.html?isBp=true";
                    else if ("account" == b) var c = "../account/user/user.html?isBa=true";
                    if (f(".pop-login-box").length <= 0) {
                        var d = ['<div class="mask"></div>', '<div class="pop_box pop-login-box">', '<div class="pop_head">', '    <p class="pop_remind"><i></i>提示</p>', "</div>", '<div class="pop_body">', '    <p id="pop_login_info" class="pop-confirm-info"></p>', '    <div class="pop-buttons">', '<span id="pop_bind_quit" class="quit">取消</span>', '<span id="pop_bind" class="confirm">前往绑定</span>', "</div>", "</div>", "</div>"].join("");
                        f("body").append(d),
                            f("#pop_bind_quit").on("click",
                                function() {
                                    f(".mask").hide(),
                                        f(".pop-login-box").hide()
                                }),
                            f("#pop_bind").click(function() {
                                window.location.href = window.relativeUrl + c
                            }),
                            f("#pop_login_info").html(a)
                    }
                    f(".mask").show();
                    var e = 400,
                        g = 215,
                        i = h(g),
                        j = (f(window).width() - e) / 2;
                    f(".mask").height(f(window).height()),
                        f(".pop-login-box").show(),
                        f(".pop-login-box").css({
                            top: i,
                            left: j
                        })
                },
                c.exports.getHeadLog = function(a, b) {
                    f("#logo_" + a).css("background", 'url("https://open.weixin.qq.com/qr/code?username=' + b + '") no-repeat -173px -173px')
                },
                c.exports.doNullStr = function(a) {
                    return void 0 === a ? "": 0 === f.trim(a).length ? "": f.trim(a)
                },
                c.exports.thousandBitSeparator = function(a) {
                    var b = String(a).split(".");
                    return b[0] = b[0].replace(new RegExp("(\\d)(?=(\\d{3})+$)", "ig"), "$1,"),
                        b.join(".")
                },
                c.exports.decimal = function(a, b) {
                    var c = Math.pow(10, b);
                    return Math.round(a * c) / c
                },
                c.exports.searchCurrent = function(a, b) {
                    var c = f("#" + a).val();
                    if ("" == c) f("." + b).show();
                    else {
                        var d = f("." + b);
                        f.each(d,
                            function() { - 1 != f(this).html().indexOf(c) ? f(this).show() : f(this).hide()
                            })
                    }
                },
                c.exports.do_10W = function(a) {
                    if (a) {
                        var b = parseInt(a);
                        return b >= 1e5 ? "10w+": b
                    }
                    return ""
                },
                c.exports.doNullNum = function(a) {
                    return void 0 === a ? "0": 0 === a.trim().length ? "0": a.trim()
                },
                c.exports.showConfirmSpe = function(a, b, c) {
                    if (f(".confirm_box").length <= 0) {
                        var d = ['<div id="confirm_box_mask"></div>', '<div class="confirm_box">', '<div class="confirm_head">', '<p class="confirm_remind"><i></i>提示</p>', "</div>", '<div class="confirm_body">', '<p id="confirm_info"></p>', '<span id="confirm_no">取消</span>', '<span id="confirm_yes">确定</span>', "</div>", "</div>"].join("");
                        f("body").append(d),
                            f("#confirm_no").click(function() {
                                f("#confirm_box_mask").hide(),
                                    f(".confirm_box").hide()
                            })
                    }
                    f("#confirm_box_mask").show();
                    var e = 400,
                        g = 215,
                        h = (f(window).height() - g) / 2 + f(window).scrollTop(),
                        i = (f(window).width() - e) / 2,
                        j = f("body").height();
                    f("#confirm_box_mask").height(j),
                        f(".confirm_box").show(),
                        f(".confirm_box").css({
                            top: h,
                            left: i
                        }),
                        f("#confirm_info").html(a),
                        f("#confirm_yes").unbind("click"),
                        f("#confirm_yes").click(function() {
                            f("#confirm_box_mask").hide(),
                                f(".confirm_box").hide(),
                                b(c)
                        })
                },
                c.exports.showCustom = function(a) {
                    if (f(".confirm_box").length <= 0) {
                        var b = [f(".tip-mask").length <= 0 ? '<div class="tip-mask"></div>': "", '<div class="confirm_box">', '<div class="confirm_head">', '<p class="confirm_remind"><i></i>提示</p>', "</div>", '<div class="confirm_body">', '<p id="confirm_info"></p>', '<span id="confirm_no">' + (a.cancelName || "取消") + "</span>", '<span id="confirm_yes">' + (a.okName || "确定") + "</span>", "</div>", "</div>"].join("");
                        f("body").append(b),
                            f("#confirm_no").click(function() {
                                f(".tip-mask").remove(),
                                    f(".confirm_box").remove()
                            })
                    }
                    f(".tip-mask").show();
                    var c = a.width || 400,
                        d = a.height || 215,
                        e = (f(window).height() - d) / 2 + f(window).scrollTop(),
                        g = (f(window).width() - c) / 2;
                    f("body").height();
                    f(".confirm_box").show(),
                        f(".confirm_box").css({
                            top: e,
                            left: g
                        }),
                        f("#confirm_info").html(a.content || ""),
                        f("#confirm_yes").unbind("click"),
                        f("#confirm_yes").click(function() {
                            f.isFunction(a.ok) && a.ok(),
                                f(".tip-mask").remove(),
                                f(".confirm_box").remove()
                        })
                },
                c.exports.openService = function(a) {
                    var b = a.width ? a.width: 400,
                        c = a.height ? a.height: 320,
                        d = -600,
                        e = "margin-left: -" + b / 2 + "px;",
                        g = "widht:" + b + "px; height:" + c + "px; top: " + d + "px; " + e,
                        h = a.src ? 'href="' + a.src + '" target="_blank"': "",
                        i = ['<div class="open-service" style="' + g + '">', '<div class="head" style="width: ' + b + 'px;">', '<div class="tip"><span>' + (a.title ? a.title: "") + "</span></div>", '<div class="cancel"><span class="product-pic"></span></div>', "</div>", '<div class="body">', '<div class="content">' + (a.body ? a.body: "") + "</div>", "</div>", '<div class="footer">', '<div class="button"><a ' + h + " >" + (a.buttonName ? a.buttonName: "") + "</a></div>", '<div class="tip"><span>' + (a.footerTip ? a.footerTip: "") + "</span></div>", "</div>", "</div>"],
                        j = f(i.join("")),
                        k = f("body");
                    k.append(j),
                    0 == f(".tip-mask").length && k.append('<div class="tip-mask" style="display: none;"></div>');
                    var l = function() {
                        k.find(".tip-mask").hide(),
                            j.css({
                                top: "-600px"
                            })
                    };
                    return k.find(".cancel").click(function() {
                        k.find(".tip-mask").hide(),
                            l()
                    }),
                    f.isFunction(a.buttonClick) && k.find(".button a").click(function() {
                        a.buttonClick(),
                            l()
                    }),
                        {
                            element: j,
                            show: function() {
                                j.css({
                                    top: "220px"
                                }),
                                    k.find(".tip-mask").show()
                            },
                            hide: function() {
                                l()
                            }
                        }
                };
            var r = "YDYQSXMWZSSXJBYMGCCZQPSSQBYCDSCDQLDYLYBSSJGYZZJJFKCCLZDHWDWZJLJPFYYNWJJTMYHZWZHFLZPPQHGSCYYYNJQYXXGJHHSDSJNKKTMOMLCRXYPSNQSECCQZGGLLYJLMYZZSECYKYYHQWJSSGGYXYZYJWWKDJHYCHMYXJTLXJYQBYXZLDWRDJRWYSRLDZJPCBZJJBRCFTLECZSTZFXXZHTRQHYBDLYCZSSYMMRFMYQZPWWJJYFCRWFDFZQPYDDWYXKYJAWJFFXYPSFTZYHHYZYSWCJYXSCLCXXWZZXNBGNNXBXLZSZSBSGPYSYZDHMDZBQBZCWDZZYYTZHBTSYYBZGNTNXQYWQSKBPHHLXGYBFMJEBJHHGQTJCYSXSTKZHLYCKGLYSMZXYALMELDCCXGZYRJXSDLTYZCQKCNNJWHJTZZCQLJSTSTBNXBTYXCEQXGKWJYFLZQLYHYXSPSFXLMPBYSXXXYDJCZYLLLSJXFHJXPJBTFFYABYXBHZZBJYZLWLCZGGBTSSMDTJZXPTHYQTGLJSCQFZKJZJQNLZWLSLHDZBWJNCJZYZSQQYCQYRZCJJWYBRTWPYFTWEXCSKDZCTBZHYZZYYJXZCFFZZMJYXXSDZZOTTBZLQWFCKSZSXFYRLNYJMBDTHJXSQQCCSBXYYTSYFBXDZTGBCNSLCYZZPSAZYZZSCJCSHZQYDXLBPJLLMQXTYDZXSQJTZPXLCGLQTZWJBHCTSYJSFXYEJJTLBGXSXJMYJQQPFZASYJNTYDJXKJCDJSZCBARTDCLYJQMWNQNCLLLKBYBZZSYHQQLTWLCCXTXLLZNTYLNEWYZYXCZXXGRKRMTCNDNJTSYYSSDQDGHSDBJGHRWRQLYBGLXHLGTGXBQJDZPYJSJYJCTMRNYMGRZJCZGJMZMGXMPRYXKJNYMSGMZJYMKMFXMLDTGFBHCJHKYLPFMDXLQJJSMTQGZSJLQDLDGJYCALCMZCSDJLLNXDJFFFFJCZFMZFFPFKHKGDPSXKTACJDHHZDDCRRCFQYJKQCCWJDXHWJLYLLZGCFCQDSMLZPBJJPLSBCJGGDCKKDEZSQCCKJGCGKDJTJDLZYCXKLQSCGJCLTFPCQCZGWPJDQYZJJBYJHSJDZWGFSJGZKQCCZLLPSPKJGQJHZZLJPLGJGJJTHJJYJZCZMLZLYQBGJWMLJKXZDZNJQSYZMLJLLJKYWXMKJLHSKJGBMCLYYMKXJQLBMLLKMDXXKWYXYSLMLPSJQQJQXYXFJTJDXMXXLLCXQBSYJBGWYMBGGBCYXPJYGPEPFGDJGBHBNSQJYZJKJKHXQFGQZKFHYGKHDKLLSDJQXPQYKYBNQSXQNSZSWHBSXWHXWBZZXDMNSJBSBKBBZKLYLXGWXDRWYQZMYWSJQLCJXXJXKJEQXSCYETLZHLYYYSDZPAQYZCMTLSHTZCFYZYXYLJSDCJQAGYSLCQLYYYSHMRQQKLDXZSCSSSYDYCJYSFSJBFRSSZQSBXXPXJYSDRCKGJLGDKZJZBDKTCSYQPYHSTCLDJDHMXMCGXYZHJDDTMHLTXZXYLYMOHYJCLTYFBQQXPFBDFHHTKSQHZYYWCNXXCRWHOWGYJLEGWDQCWGFJYCSNTMYTOLBYGWQWESJPWNMLRYDZSZTXYQPZGCWXHNGPYXSHMYQJXZTDPPBFYHZHTJYFDZWKGKZBLDNTSXHQEEGZZYLZMMZYJZGXZXKHKSTXNXXWYLYAPSTHXDWHZYMPXAGKYDXBHNHXKDPJNMYHYLPMGOCSLNZHKXXLPZZLBMLSFBHHGYGYYGGBHSCYAQTYWLXTZQCEZYDQDQMMHTKLLSZHLSJZWFYHQSWSCWLQAZYNYTLSXTHAZNKZZSZZLAXXZWWCTGQQTDDYZTCCHYQZFLXPSLZYGPZSZNGLNDQTBDLXGTCTAJDKYWNSYZLJHHZZCWNYYZYWMHYCHHYXHJKZWSXHZYXLYSKQYSPSLYZWMYPPKBYGLKZHTYXAXQSYSHXASMCHKDSCRSWJPWXSGZJLWWSCHSJHSQNHCSEGNDAQTBAALZZMSSTDQJCJKTSCJAXPLGGXHHGXXZCXPDMMHLDGTYBYSJMXHMRCPXXJZCKZXSHMLQXXTTHXWZFKHCCZDYTCJYXQHLXDHYPJQXYLSYYDZOZJNYXQEZYSQYAYXWYPDGXDDXSPPYZNDLTWRHXYDXZZJHTCXMCZLHPYYYYMHZLLHNXMYLLLMDCPPXHMXDKYCYRDLTXJCHHZZXZLCCLYLNZSHZJZZLNNRLWHYQSNJHXYNTTTKYJPYCHHYEGKCTTWLGQRLGGTGTYGYHPYHYLQYQGCWYQKPYYYTTTTLHYHLLTYTTSPLKYZXGZWGPYDSSZZDQXSKCQNMJJZZBXYQMJRTFFBTKHZKBXLJJKDXJTLBWFZPPTKQTZTGPDGNTPJYFALQMKGXBDCLZFHZCLLLLADPMXDJHLCCLGYHDZFGYDDGCYYFGYDXKSSEBDHYKDKDKHNAXXYBPBYYHXZQGAFFQYJXDMLJCSQZLLPCHBSXGJYNDYBYQSPZWJLZKSDDTACTBXZDYZYPJZQSJNKKTKNJDJGYYPGTLFYQKASDNTCYHBLWDZHBBYDWJRYGKZYHEYYFJMSDTYFZJJHGCXPLXHLDWXXJKYTCYKSSSMTWCTTQZLPBSZDZWZXGZAGYKTYWXLHLSPBCLLOQMMZSSLCMBJCSZZKYDCZJGQQDSMCYTZQQLWZQZXSSFPTTFQMDDZDSHDTDWFHTDYZJYQJQKYPBDJYYXTLJHDRQXXXHAYDHRJLKLYTWHLLRLLRCXYLBWSRSZZSYMKZZHHKYHXKSMDSYDYCJPBZBSQLFCXXXNXKXWYWSDZYQOGGQMMYHCDZTTFJYYBGSTTTYBYKJDHKYXBELHTYPJQNFXFDYKZHQKZBYJTZBXHFDXKDASWTAWAJLDYJSFHBLDNNTNQJTJNCHXFJSRFWHZFMDRYJYJWZPDJKZYJYMPCYZNYNXFBYTFYFWYGDBNZZZDNYTXZEMMQBSQEHXFZMBMFLZZSRXYMJGSXWZJSPRYDJSJGXHJJGLJJYNZZJXHGXKYMLPYYYCXYTWQZSWHWLYRJLPXSLSXMFSWWKLCTNXNYNPSJSZHDZEPTXMYYWXYYSYWLXJQZQXZDCLEEELMCPJPCLWBXSQHFWWTFFJTNQJHJQDXHWLBYZNFJLALKYYJLDXHHYCSTYYWNRJYXYWTRMDRQHWQCMFJDYZMHMYYXJWMYZQZXTLMRSPWWCHAQBXYGZYPXYYRRCLMPYMGKSJSZYSRMYJSNXTPLNBAPPYPYLXYYZKYNLDZYJZCZNNLMZHHARQMPGWQTZMXXMLLHGDZXYHXKYXYCJMFFYYHJFSBSSQLXXNDYCANNMTCJCYPRRNYTYQNYYMBMSXNDLYLYSLJRLXYSXQMLLYZLZJJJKYZZCSFBZXXMSTBJGNXYZHLXNMCWSCYZYFZLXBRNNNYLBNRTGZQYSATSWRYHYJZMZDHZGZDWYBSSCSKXSYHYTXXGCQGXZZSHYXJSCRHMKKBXCZJYJYMKQHZJFNBHMQHYSNJNZYBKNQMCLGQHWLZNZSWXKHLJHYYBQLBFCDSXDLDSPFZPSKJYZWZXZDDXJSMMEGJSCSSMGCLXXKYYYLNYPWWWGYDKZJGGGZGGSYCKNJWNJPCXBJJTQTJWDSSPJXZXNZXUMELPXFSXTLLXCLJXJJLJZXCTPSWXLYDHLYQRWHSYCSQYYBYAYWJJJQFWQCQQCJQGXALDBZZYJGKGXPLTZYFXJLTPADKYQHPMATLCPDCKBMTXYBHKLENXDLEEGQDYMSAWHZMLJTWYGXLYQZLJEEYYBQQFFNLYXRDSCTGJGXYYNKLLYQKCCTLHJLQMKKZGCYYGLLLJDZGYDHZWXPYSJBZKDZGYZZHYWYFQYTYZSZYEZZLYMHJJHTSMQWYZLKYYWZCSRKQYTLTDXWCTYJKLWSQZWBDCQYNCJSRSZJLKCDCDTLZZZACQQZZDDXYPLXZBQJYLZLLLQDDZQJYJYJZYXNYYYNYJXKXDAZWYRDLJYYYRJLXLLDYXJCYWYWNQCCLDDNYYYNYCKCZHXXCCLGZQJGKWPPCQQJYSBZZXYJSQPXJPZBSBDSFNSFPZXHDWZTDWPPTFLZZBZDMYYPQJRSDZSQZSQXBDGCPZSWDWCSQZGMDHZXMWWFYBPDGPHTMJTHZSMMBGZMBZJCFZWFZBBZMQCFMBDMCJXLGPNJBBXGYHYYJGPTZGZMQBQTCGYXJXLWZKYDPDYMGCFTPFXYZTZXDZXTGKMTYBBCLBJASKYTSSQYYMSZXFJEWLXLLSZBQJJJAKLYLXLYCCTSXMCWFKKKBSXLLLLJYXTYLTJYYTDPJHNHNNKBYQNFQYYZBYYESSESSGDYHFHWTCJBSDZZTFDMXHCNJZYMQWSRYJDZJQPDQBBSTJGGFBKJBXTGQHNGWJXJGDLLTHZHHYYYYYYSXWTYYYCCBDBPYPZYCCZYJPZYWCBDLFWZCWJDXXHYHLHWZZXJTCZLCDPXUJCZZZLYXJJTXPHFXWPYWXZPTDZZBDZCYHJHMLXBQXSBYLRDTGJRRCTTTHYTCZWMXFYTWWZCWJWXJYWCSKYBZSCCTZQNHXNWXXKHKFHTSWOCCJYBCMPZZYKBNNZPBZHHZDLSYDDYTYFJPXYNGFXBYQXCBHXCPSXTYZDMKYSNXSXLHKMZXLYHDHKWHXXSSKQYHHCJYXGLHZXCSNHEKDTGZXQYPKDHEXTYKCNYMYYYPKQYYYKXZLTHJQTBYQHXBMYHSQCKWWYLLHCYYLNNEQXQWMCFBDCCMLJGGXDQKTLXKGNQCDGZJWYJJLYHHQTTTNWCHMXCXWHWSZJYDJCCDBQCDGDNYXZTHCQRXCBHZTQCBXWGQWYYBXHMBYMYQTYEXMQKYAQYRGYZSLFYKKQHYSSQYSHJGJCNXKZYCXSBXYXHYYLSTYCXQTHYSMGSCPMMGCCCCCMTZTASMGQZJHKLOSQYLSWTMXSYQKDZLJQQYPLSYCZTCQQPBBQJZCLPKHQZYYXXDTDDTSJCXFFLLCHQXMJLWCJCXTSPYCXNDTJSHJWXDQQJSKXYAMYLSJHMLALYKXCYYDMNMDQMXMCZNNCYBZKKYFLMCHCMLHXRCJJHSYLNMTJZGZGYWJXSRXCWJGJQHQZDQJDCJJZKJKGDZQGJJYJYLXZXXCDQHHHEYTMHLFSBDJSYYSHFYSTCZQLPBDRFRZTZYKYWHSZYQKWDQZRKMSYNBCRXQBJYFAZPZZEDZCJYWBCJWHYJBQSZYWRYSZPTDKZPFPBNZTKLQYHBBZPNPPTYZZYBQNYDCPJMMCYCQMCYFZZDCMNLFPBPLNGQJTBTTNJZPZBBZNJKLJQYLNBZQHKSJZNGGQSZZKYXSHPZSNBCGZKDDZQANZHJKDRTLZLSWJLJZLYWTJNDJZJHXYAYNCBGTZCSSQMNJPJYTYSWXZFKWJQTKHTZPLBHSNJZSYZBWZZZZLSYLSBJHDWWQPSLMMFBJDWAQYZTCJTBNNWZXQXCDSLQGDSDPDZHJTQQPSWLYYJZLGYXYZLCTCBJTKTYCZJTQKBSJLGMGZDMCSGPYNJZYQYYKNXRPWSZXMTNCSZZYXYBYHYZAXYWQCJTLLCKJJTJHGDXDXYQYZZBYWDLWQCGLZGJGQRQZCZSSBCRPCSKYDZNXJSQGXSSJMYDNSTZTPBDLTKZWXQWQTZEXNQCZGWEZKSSBYBRTSSSLCCGBPSZQSZLCCGLLLZXHZQTHCZMQGYZQZNMCOCSZJMMZSQPJYGQLJYJPPLDXRGZYXCCSXHSHGTZNLZWZKJCXTCFCJXLBMQBCZZWPQDNHXLJCTHYZLGYLNLSZZPCXDSCQQHJQKSXZPBAJYEMSMJTZDXLCJYRYYNWJBNGZZTMJXLTBSLYRZPYLSSCNXPHLLHYLLQQZQLXYMRSYCXZLMMCZLTZSDWTJJLLNZGGQXPFSKYGYGHBFZPDKMWGHCXMSGDXJMCJZDYCABXJDLNBCDQYGSKYDQTXDJJYXMSZQAZDZFSLQXYJSJZYLBTXXWXQQZBJZUFBBLYLWDSLJHXJYZJWTDJCZFQZQZZDZSXZZQLZCDZFJHYSPYMPQZMLPPLFFXJJNZZYLSJEYQZFPFZKSYWJJJHRDJZZXTXXGLGHYDXCSKYSWMMZCWYBAZBJKSHFHJCXMHFQHYXXYZFTSJYZFXYXPZLCHMZMBXHZZSXYFYMNCWDABAZLXKTCSHHXKXJJZJSTHYGXSXYYHHHJWXKZXSSBZZWHHHCWTZZZPJXSNXQQJGZYZYWLLCWXZFXXYXYHXMKYYSWSQMNLNAYCYSPMJKHWCQHYLAJJMZXHMMCNZHBHXCLXTJPLTXYJHDYYLTTXFSZHYXXSJBJYAYRSMXYPLCKDUYHLXRLNLLSTYZYYQYGYHHSCCSMZCTZQXKYQFPYYRPFFLKQUNTSZLLZMWWTCQQYZWTLLMLMPWMBZSSTZRBPDDTLQJJBXZCSRZQQYGWCSXFWZLXCCRSZDZMCYGGDZQSGTJSWLJMYMMZYHFBJDGYXCCPSHXNZCSBSJYJGJMPPWAFFYFNXHYZXZYLREMZGZCYZSSZDLLJCSQFNXZKPTXZGXJJGFMYYYSNBTYLBNLHPFZDCYFBMGQRRSSSZXYSGTZRNYDZZCDGPJAFJFZKNZBLCZSZPSGCYCJSZLMLRSZBZZLDLSLLYSXSQZQLYXZLSKKBRXBRBZCYCXZZZEEYFGKLZLYYHGZSGZLFJHGTGWKRAAJYZKZQTSSHJJXDCYZUYJLZYRZDQQHGJZXSSZBYKJPBFRTJXLLFQWJHYLQTYMBLPZDXTZYGBDHZZRBGXHWNJTJXLKSCFSMWLSDQYSJTXKZSCFWJLBXFTZLLJZLLQBLSQMQQCGCZFPBPHZCZJLPYYGGDTGWDCFCZQYYYQYSSCLXZSKLZZZGFFCQNWGLHQYZJJCZLQZZYJPJZZBPDCCMHJGXDQDGDLZQMFGPSYTSDYFWWDJZJYSXYYCZCYHZWPBYKXRYLYBHKJKSFXTZJMMCKHLLTNYYMSYXYZPYJQYCSYCWMTJJKQYRHLLQXPSGTLYYCLJSCPXJYZFNMLRGJJTYZBXYZMSJYJHHFZQMSYXRSZCWTLRTQZSSTKXGQKGSPTGCZNJSJCQCXHMXGGZTQYDJKZDLBZSXJLHYQGGGTHQSZPYHJHHGYYGKGGCWJZZYLCZLXQSFTGZSLLLMLJSKCTBLLZZSZMMNYTPZSXQHJCJYQXYZXZQZCPSHKZZYSXCDFGMWQRLLQXRFZTLYSTCTMJCXJJXHJNXTNRZTZFQYHQGLLGCXSZSJDJLJCYDSJTLNYXHSZXCGJZYQPYLFHDJSBPCCZHJJJQZJQDYBSSLLCMYTTMQTBHJQNNYGKYRQYQMZGCJKPDCGMYZHQLLSLLCLMHOLZGDYYFZSLJCQZLYLZQJESHNYLLJXGJXLYSYYYXNBZLJSSZCQQCJYLLZLTJYLLZLLBNYLGQCHXYYXOXCXQKYJXXXYKLXSXXYQXCYKQXQCSGYXXYQXYGYTQOHXHXPYXXXULCYEYCHZZCBWQBBWJQZSCSZSSLZYLKDESJZWMYMCYTSDSXXSCJPQQSQYLYYZYCMDJDZYWCBTJSYDJKCYDDJLBDJJSODZYSYXQQYXDHHGQQYQHDYXWGMMMAJDYBBBPPBCMUUPLJZSMTXERXJMHQNUTPJDCBSSMSSSTKJTSSMMTRCPLZSZMLQDSDMJMQPNQDXCFYNBFSDQXYXHYAYKQYDDLQYYYSSZBYDSLNTFQTZQPZMCHDHCZCWFDXTMYQSPHQYYXSRGJCWTJTZZQMGWJJTJHTQJBBHWZPXXHYQFXXQYWYYHYSCDYDHHQMNMTMWCPBSZPPZZGLMZFOLLCFWHMMSJZTTDHZZYFFYTZZGZYSKYJXQYJZQBHMBZZLYGHGFMSHPZFZSNCLPBQSNJXZSLXXFPMTYJYGBXLLDLXPZJYZJYHHZCYWHJYLSJEXFSZZYWXKZJLUYDTMLYMQJPWXYHXSKTQJEZRPXXZHHMHWQPWQLYJJQJJZSZCPHJLCHHNXJLQWZJHBMZYXBDHHYPZLHLHLGFWLCHYYTLHJXCJMSCPXSTKPNHQXSRTYXXTESYJCTLSSLSTDLLLWWYHDHRJZSFGXTSYCZYNYHTDHWJSLHTZDQDJZXXQHGYLTZPHCSQFCLNJTCLZPFSTPDYNYLGMJLLYCQHYSSHCHYLHQYQTMZYPBYWRFQYKQSYSLZDQJMPXYYSSRHZJNYWTQDFZBWWTWWRXCWHGYHXMKMYYYQMSMZHNGCEPMLQQMTCWCTMMPXJPJJHFXYYZSXZHTYBMSTSYJTTQQQYYLHYNPYQZLCYZHZWSMYLKFJXLWGXYPJYTYSYXYMZCKTTWLKSMZSYLMPWLZWXWQZSSAQSYXYRHSSNTSRAPXCPWCMGDXHXZDZYFJHGZTTSBJHGYZSZYSMYCLLLXBTYXHBBZJKSSDMALXHYCFYGMQYPJYCQXJLLLJGSLZGQLYCJCCZOTYXMTMTTLLWTGPXYMZMKLPSZZZXHKQYSXCTYJZYHXSHYXZKXLZWPSQPYHJWPJPWXQQYLXSDHMRSLZZYZWTTCYXYSZZSHBSCCSTPLWSSCJCHNLCGCHSSPHYLHFHHXJSXYLLNYLSZDHZXYLSXLWZYKCLDYAXZCMDDYSPJTQJZLNWQPSSSWCTSTSZLBLNXSMNYYMJQBQHRZWTYYDCHQLXKPZWBGQYBKFCMZWPZLLYYLSZYDWHXPSBCMLJBSCGBHXLQHYRLJXYSWXWXZSLDFHLSLYNJLZYFLYJYCDRJLFSYZFSLLCQYQFGJYHYXZLYLMSTDJCYHBZLLNWLXXYGYYHSMGDHXXHHLZZJZXCZZZCYQZFNGWPYLCPKPYYPMCLQKDGXZGGWQBDXZZKZFBXXLZXJTPJPTTBYTSZZDWSLCHZHSLTYXHQLHYXXXYYZYSWTXZKHLXZXZPYHGCHKCFSYHUTJRLXFJXPTZTWHPLYXFCRHXSHXKYXXYHZQDXQWULHYHMJTBFLKHTXCWHJFWJCFPQRYQXCYYYQYGRPYWSGSUNGWCHKZDXYFLXXHJJBYZWTSXXNCYJJYMSWZJQRMHXZWFQSYLZJZGBHYNSLBGTTCSYBYXXWXYHXYYXNSQYXMQYWRGYQLXBBZLJSYLPSYTJZYHYZAWLRORJMKSCZJXXXYXCHDYXRYXXJDTSQFXLYLTSFFYXLMTYJMJUYYYXLTZCSXQZQHZXLYYXZHDNBRXXXJCTYHLBRLMBRLLAXKYLLLJLYXXLYCRYLCJTGJCMTLZLLCYZZPZPCYAWHJJFYBDYYZSMPCKZDQYQPBPCJPDCYZMDPBCYYDYCNNPLMTMLRMFMMGWYZBSJGYGSMZQQQZTXMKQWGXLLPJGZBQCDJJJFPKJKCXBLJMSWMDTQJXLDLPPBXCWRCQFBFQJCZAHZGMYKPHYYHZYKNDKZMBPJYXPXYHLFPNYYGXJDBKXNXHJMZJXSTRSTLDXSKZYSYBZXJLXYSLBZYSLHXJPFXPQNBYLLJQKYGZMCYZZYMCCSLCLHZFWFWYXZMWSXTYNXJHPYYMCYSPMHYSMYDYSHQYZCHMJJMZCAAGCFJBBHPLYZYLXXSDJGXDHKXXTXXNBHRMLYJSLTXMRHNLXQJXYZLLYSWQGDLBJHDCGJYQYCMHWFMJYBMBYJYJWYMDPWHXQLDYGPDFXXBCGJSPCKRSSYZJMSLBZZJFLJJJLGXZGYXYXLSZQYXBEXYXHGCXBPLDYHWETTWWCJMBTXCHXYQXLLXFLYXLLJLSSFWDPZSMYJCLMWYTCZPCHQEKCQBWLCQYDPLQPPQZQFJQDJHYMMCXTXDRMJWRHXCJZYLQXDYYNHYYHRSLSRSYWWZJYMTLTLLGTQCJZYABTCKZCJYCCQLJZQXALMZYHYWLWDXZXQDLLQSHGPJFJLJHJABCQZDJGTKHSSTCYJLPSWZLXZXRWGLDLZRLZXTGSLLLLZLYXXWGDZYGBDPHZPBRLWSXQBPFDWOFMWHLYPCBJCCLDMBZPBZZLCYQXLDOMZBLZWPDWYYGDSTTHCSQSCCRSSSYSLFYBFNTYJSZDFNDPDHDZZMBBLSLCMYFFGTJJQWFTMTPJWFNLBZCMMJTGBDZLQLPYFHYYMJYLSDCHDZJWJCCTLJCLDTLJJCPDDSQDSSZYBNDBJLGGJZXSXNLYCYBJXQYCBYLZCFZPPGKCXZDZFZTJJFJSJXZBNZYJQTTYJYHTYCZHYMDJXTTMPXSPLZCDWSLSHXYPZGTFMLCJTYCBPMGDKWYCYZCDSZZYHFLYCTYGWHKJYYLSJCXGYWJCBLLCSNDDBTZBSCLYZCZZSSQDLLMQYYHFSLQLLXFTYHABXGWNYWYYPLLSDLDLLBJCYXJZMLHLJDXYYQYTDLLLBUGBFDFBBQJZZMDPJHGCLGMJJPGAEHHBWCQXAXHHHZCHXYPHJAXHLPHJPGPZJQCQZGJJZZUZDMQYYBZZPHYHYBWHAZYJHYKFGDPFQSDLZMLJXKXGALXZDAGLMDGXMWZQYXXDXXPFDMMSSYMPFMDMMKXKSYZYSHDZKXSYSMMZZZMSYDNZZCZXFPLSTMZDNMXCKJMZTYYMZMZZMSXHHDCZJEMXXKLJSTLWLSQLYJZLLZJSSDPPMHNLZJCZYHMXXHGZCJMDHXTKGRMXFWMCGMWKDTKSXQMMMFZZYDKMSCLCMPCGMHSPXQPZDSSLCXKYXTWLWJYAHZJGZQMCSNXYYMMPMLKJXMHLMLQMXCTKZMJQYSZJSYSZHSYJZJCDAJZYBSDQJZGWZQQXFKDMSDJLFWEHKZQKJPEYPZYSZCDWYJFFMZZYLTTDZZEFMZLBNPPLPLPEPSZALLTYLKCKQZKGENQLWAGYXYDPXLHSXQQWQCQXQCLHYXXMLYCCWLYMQYSKGCHLCJNSZKPYZKCQZQLJPDMDZHLASXLBYDWQLWDNBQCRYDDZTJYBKBWSZDXDTNPJDTCTQDFXQQMGNXECLTTBKPWSLCTYQLPWYZZKLPYGZCQQPLLKCCYLPQMZCZQCLJSLQZDJXLDDHPZQDLJJXZQDXYZQKZLJCYQDYJPPYPQYKJYRMPCBYMCXKLLZLLFQPYLLLMBSGLCYSSLRSYSQTMXYXZQZFDZUYSYZTFFMZZSMZQHZSSCCMLYXWTPZGXZJGZGSJSGKDDHTQGGZLLBJDZLCBCHYXYZHZFYWXYZYMSDBZZYJGTSMTFXQYXQSTDGSLNXDLRYZZLRYYLXQHTXSRTZNGZXBNQQZFMYKMZJBZYMKBPNLYZPBLMCNQYZZZSJZHJCTZKHYZZJRDYZHNPXGLFZTLKGJTCTSSYLLGZRZBBQZZKLPKLCZYSSUYXBJFPNJZZXCDWXZYJXZZDJJKGGRSRJKMSMZJLSJYWQSKYHQJSXPJZZZLSNSHRNYPZTWCHKLPSRZLZXYJQXQKYSJYCZTLQZYBBYBWZPQDWWYZCYTJCJXCKCWDKKZXSGKDZXWWYYJQYYTCYTDLLXWKCZKKLCCLZCQQDZLQLCSFQCHQHSFSMQZZLNBJJZBSJHTSZDYSJQJPDLZCDCWJKJZZLPYCGMZWDJJBSJQZSYZYHHXJPBJYDSSXDZNCGLQMBTSFSBPDZDLZNFGFJGFSMPXJQLMBLGQCYYXBQKDJJQYRFKZTJDHCZKLBSDZCFJTPLLJGXHYXZCSSZZXSTJYGKGCKGYOQXJPLZPBPGTGYJZGHZQZZLBJLSQFZGKQQJZGYCZBZQTLDXRJXBSXXPZXHYZYCLWDXJJHXMFDZPFZHQHQMQGKSLYHTYCGFRZGNQXCLPDLBZCSCZQLLJBLHBZCYPZZPPDYMZZSGYHCKCPZJGSLJLNSCDSLDLXBMSTLDDFJMKDJDHZLZXLSZQPQPGJLLYBDSZGQLBZLSLKYYHZTTNTJYQTZZPSZQZTLLJTYYLLQLLQYZQLBDZLSLYYZYMDFSZSNHLXZNCZQZPBWSKRFBSYZMTHBLGJPMCZZLSTLXSHTCSYZLZBLFEQHLXFLCJLYLJQCBZLZJHHSSTBRMHXZHJZCLXFNBGXGTQJCZTMSFZKJMSSNXLJKBHSJXNTNLZDNTLMSJXGZJYJCZXYJYJWRWWQNZTNFJSZPZSHZJFYRDJSFSZJZBJFZQZZHZLXFYSBZQLZSGYFTZDCSZXZJBQMSZKJRHYJZCKMJKHCHGTXKXQGLXPXFXTRTYLXJXHDTSJXHJZJXZWZLCQSBTXWXGXTXXHXFTSDKFJHZYJFJXRZSDLLLTQSQQZQWZXSYQTWGWBZCGZLLYZBCLMQQTZHZXZXLJFRMYZFLXYSQXXJKXRMQDZDMMYYBSQBHGZMWFWXGMXLZPYYTGZYCCDXYZXYWGSYJYZNBHPZJSQSYXSXRTFYZGRHZTXSZZTHCBFCLSYXZLZQMZLMPLMXZJXSFLBYZMYQHXJSXRXSQZZZSSLYFRCZJRCRXHHZXQYDYHXSJJHZCXZBTYNSYSXJBQLPXZQPYMLXZKYXLXCJLCYSXXZZLXDLLLJJYHZXGYJWKJRWYHCPSGNRZLFZWFZZNSXGXFLZSXZZZBFCSYJDBRJKRDHHGXJLJJTGXJXXSTJTJXLYXQFCSGSWMSBCTLQZZWLZZKXJMLTMJYHSDDBXGZHDLBMYJFRZFSGCLYJBPMLYSMSXLSZJQQHJZFXGFQFQBPXZGYYQXGZTCQWYLTLGWSGWHRLFSFGZJMGMGBGTJFSYZZGZYZAFLSSPMLPFLCWBJZCLJJMZLPJJLYMQDMYYYFBGYGYZMLYZDXQYXRQQQHSYYYQXYLJTYXFSFSLLGNQCYHYCWFHCCCFXPYLYPLLZYXXXXXKQHHXSHJZCFZSCZJXCPZWHHHHHAPYLQALPQAFYHXDYLUKMZQGGGDDESRNNZLTZGCHYPPYSQJJHCLLJTOLNJPZLJLHYMHEYDYDSQYCDDHGZUNDZCLZYZLLZNTNYZGSLHSLPJJBDGWXPCDUTJCKLKCLWKLLCASSTKZZDNQNTTLYYZSSYSSZZRYLJQKCQDHHCRXRZYDGRGCWCGZQFFFPPJFZYNAKRGYWYQPQXXFKJTSZZXSWZDDFBBXTBGTZKZNPZZPZXZPJSZBMQHKCYXYLDKLJNYPKYGHGDZJXXEAHPNZKZTZCMXCXMMJXNKSZQNMNLWBWWXJKYHCPSTMCSQTZJYXTPCTPDTNNPGLLLZSJLSPBLPLQHDTNJNLYYRSZFFJFQWDPHZDWMRZCCLODAXNSSNYZRESTYJWJYJDBCFXNMWTTBYLWSTSZGYBLJPXGLBOCLHPCBJLTMXZLJYLZXCLTPNCLCKXTPZJSWCYXSFYSZDKNTLBYJCYJLLSTGQCBXRYZXBXKLYLHZLQZLNZCXWJZLJZJNCJHXMNZZGJZZXTZJXYCYYCXXJYYXJJXSSSJSTSSTTPPGQTCSXWZDCSYFPTFBFHFBBLZJCLZZDBXGCXLQPXKFZFLSYLTUWBMQJHSZBMDDBCYSCCLDXYCDDQLYJJWMQLLCSGLJJSYFPYYCCYLTJANTJJPWYCMMGQYYSXDXQMZHSZXPFTWWZQSWQRFKJLZJQQYFBRXJHHFWJJZYQAZMYFRHCYYBYQWLPEXCCZSTYRLTTDMQLYKMBBGMYYJPRKZNPBSXYXBHYZDJDNGHPMFSGMWFZMFQMMBCMZZCJJLCNUXYQLMLRYGQZCYXZLWJGCJCGGMCJNFYZZJHYCPRRCMTZQZXHFQGTJXCCJEAQCRJYHPLQLSZDJRBCQHQDYRHYLYXJSYMHZYDWLDFRYHBPYDTSSCNWBXGLPZMLZZTQSSCPJMXXYCSJYTYCGHYCJWYRXXLFEMWJNMKLLSWTXHYYYNCMMCWJDQDJZGLLJWJRKHPZGGFLCCSCZMCBLTBHBQJXQDSPDJZZGKGLFQYWBZYZJLTSTDHQHCTCBCHFLQMPWDSHYYTQWCNZZJTLBYMBPDYYYXSQKXWYYFLXXNCWCXYPMAELYKKJMZZZBRXYYQJFLJPFHHHYTZZXSGQQMHSPGDZQWBWPJHZJDYSCQWZKTXXSQLZYYMYSDZGRXCKKUJLWPYSYSCSYZLRMLQSYLJXBCXTLWDQZPCYCYKPPPNSXFYZJJRCEMHSZMSXLXGLRWGCSTLRSXBZGBZGZTCPLUJLSLYLYMTXMTZPALZXPXJTJWTCYYZLBLXBZLQMYLXPGHDSLSSDMXMBDZZSXWHAMLCZCPJMCNHJYSNSYGCHSKQMZZQDLLKABLWJXSFMOCDXJRRLYQZKJMYBYQLYHETFJZFRFKSRYXFJTWDSXXSYSQJYSLYXWJHSNLXYYXHBHAWHHJZXWMYLJCSSLKYDZTXBZSYFDXGXZJKHSXXYBSSXDPYNZWRPTQZCZENYGCXQFJYKJBZMLJCMQQXUOXSLYXXLYLLJDZBTYMHPFSTTQQWLHOKYBLZZALZXQLHZWRRQHLSTMYPYXJJXMQSJFNBXYXYJXXYQYLTHYLQYFMLKLJTMLLHSZWKZHLJMLHLJKLJSTLQXYLMBHHLNLZXQJHXCFXXLHYHJJGBYZZKBXSCQDJQDSUJZYYHZHHMGSXCSYMXFEBCQWWRBPYYJQTYZCYQYQQZYHMWFFHGZFRJFCDPXNTQYZPDYKHJLFRZXPPXZDBBGZQSTLGDGYLCQMLCHHMFYWLZYXKJLYPQHSYWMQQGQZMLZJNSQXJQSYJYCBEHSXFSZPXZWFLLBCYYJDYTDTHWZSFJMQQYJLMQXXLLDTTKHHYBFPWTYYSQQWNQWLGWDEBZWCMYGCULKJXTMXMYJSXHYBRWFYMWFRXYQMXYSZTZZTFYKMLDHQDXWYYNLCRYJBLPSXCXYWLSPRRJWXHQYPHTYDNXHHMMYWYTZCSQMTSSCCDALWZTCPQPYJLLQZYJSWXMZZMMYLMXCLMXCZMXMZSQTZPPQQBLPGXQZHFLJJHYTJSRXWZXSCCDLXTYJDCQJXSLQYCLZXLZZXMXQRJMHRHZJBHMFLJLMLCLQNLDXZLLLPYPSYJYSXCQQDCMQJZZXHNPNXZMEKMXHYKYQLXSXTXJYYHWDCWDZHQYYBGYBCYSCFGPSJNZDYZZJZXRZRQJJYMCANYRJTLDPPYZBSTJKXXZYPFDWFGZZRPYMTNGXZQBYXNBUFNQKRJQZMJEGRZGYCLKXZDSKKNSXKCLJSPJYYZLQQJYBZSSQLLLKJXTBKTYLCCDDBLSPPFYLGYDTZJYQGGKQTTFZXBDKTYYHYBBFYTYYBCLPDYTGDHRYRNJSPTCSNYJQHKLLLZSLYDXXWBCJQSPXBPJZJCJDZFFXXBRMLAZHCSNDLBJDSZBLPRZTSWSBXBCLLXXLZDJZSJPYLYXXYFTFFFBHJJXGBYXJPMMMPSSJZJMTLYZJXSWXTYLEDQPJMYGQZJGDJLQJWJQLLSJGJGYGMSCLJJXDTYGJQJQJCJZCJGDZZSXQGSJGGCXHQXSNQLZZBXHSGZXCXYLJXYXYYDFQQJHJFXDHCTXJYRXYSQTJXYEFYYSSYYJXNCYZXFXMSYSZXYYSCHSHXZZZGZZZGFJDLTYLNPZGYJYZYYQZPBXQBDZTZCZYXXYHHSQXSHDHGQHJHGYWSZTMZMLHYXGEBTYLZKQWYTJZRCLEKYSTDBCYKQQSAYXCJXWWGSBHJYZYDHCSJKQCXSWXFLTYNYZPZCCZJQTZWJQDZZZQZLJJXLSBHPYXXPSXSHHEZTXFPTLQYZZXHYTXNCFZYYHXGNXMYWXTZSJPTHHGYMXMXQZXTSBCZYJYXXTYYZYPCQLMMSZMJZZLLZXGXZAAJZYXJMZXWDXZSXZDZXLEYJJZQBHZWZZZQTZPSXZTDSXJJJZNYAZPHXYYSRNQDTHZHYYKYJHDZXZLSWCLYBZYECWCYCRYLCXNHZYDZYDYJDFRJJHTRSQTXYXJRJHOJYNXELXSFSFJZGHPZSXZSZDZCQZBYYKLSGSJHCZSHDGQGXYZGXCHXZJWYQWGYHKSSEQZZNDZFKWYSSTCLZSTSYMCDHJXXYWEYXCZAYDMPXMDSXYBSQMJMZJMTZQLPJYQZCGQHXJHHLXXHLHDLDJQCLDWBSXFZZYYSCHTYTYYBHECXHYKGJPXHHYZJFXHWHBDZFYZBCAPNPGNYDMSXHMMMMAMYNBYJTMPXYYMCTHJBZYFCGTYHWPHFTWZZEZSBZEGPFMTSKFTYCMHFLLHGPZJXZJGZJYXZSBBQSCZZLZCCSTPGXMJSFTCCZJZDJXCYBZLFCJSYZFGSZLYBCWZZBYZDZYPSWYJZXZBDSYUXLZZBZFYGCZXBZHZFTPBGZGEJBSTGKDMFHYZZJHZLLZZGJQZLSFDJSSCBZGPDLFZFZSZYZYZSYGCXSNXXCHCZXTZZLJFZGQSQYXZJQDCCZTQCDXZJYQJQCHXZTDLGSCXZSYQJQTZWLQDQZTQCHQQJZYEZZZPBWKDJFCJPZTYPQYQTTYNLMBDKTJZPQZQZZFPZSBNJLGYJDXJDZZKZGQKXDLPZJTCJDQBXDJQJSTCKNXBXZMSLYJCQMTJQWWCJQNJNLLLHJCWQTBZQYDZCZPZZDZYDDCYZZZCCJTTJFZDPRRTZTJDCQTQZDTJNPLZBCLLCTZSXKJZQZPZLBZRBTJDCXFCZDBCCJJLTQQPLDCGZDBBZJCQDCJWYNLLZYZCCDWLLXWZLXRXNTQQCZXKQLSGDFQTDDGLRLAJJTKUYMKQLLTZYTDYYCZGJWYXDXFRSKSTQTENQMRKQZHHQKDLDAZFKYPBGGPZREBZZYKZZSPEGJXGYKQZZZSLYSYYYZWFQZYLZZLZHWCHKYPQGNPGBLPLRRJYXCCSYYHSFZFYBZYYTGZXYLXCZWXXZJZBLFFLGSKHYJZEYJHLPLLLLCZGXDRZELRHGKLZZYHZLYQSZZJZQLJZFLNBHGWLCZCFJYSPYXZLZLXGCCPZBLLCYBBBBUBBCBPCRNNZCZYRBFSRLDCGQYYQXYGMQZWTZYTYJXYFWTEHZZJYWLCCNTZYJJZDEDPZDZTSYQJHDYMBJNYJZLXTSSTPHNDJXXBYXQTZQDDTJTDYYTGWSCSZQFLSHLGLBCZPHDLYZJYCKWTYTYLBNYTSDSYCCTYSZYYEBHEXHQDTWNYGYCLXTSZYSTQMYGZAZCCSZZDSLZCLZRQXYYELJSBYMXSXZTEMBBLLYYLLYTDQYSHYMRQWKFKBFXNXSBYCHXBWJYHTQBPBSBWDZYLKGZSKYHXQZJXHXJXGNLJKZLYYCDXLFYFGHLJGJYBXQLYBXQPQGZTZPLNCYPXDJYQYDYMRBESJYYHKXXSTMXRCZZYWXYQYBMCLLYZHQYZWQXDBXBZWZMSLPDMYSKFMZKLZCYQYCZLQXFZZYDQZPZYGYJYZMZXDZFYFYTTQTZHGSPCZMLCCYTZXJCYTJMKSLPZHYSNZLLYTPZCTZZCKTXDHXXTQCYFKSMQCCYYAZHTJPCYLZLYJBJXTPNYLJYYNRXSYLMMNXJSMYBCSYSYLZYLXJJQYLDZLPQBFZZBLFNDXQKCZFYWHGQMRDSXYCYTXNQQJZYYPFZXDYZFPRXEJDGYQBXRCNFYYQPGHYJDYZXGRHTKYLNWDZNTSMPKLBTHBPYSZBZTJZSZZJTYYXZPHSSZZBZCZPTQFZMYFLYPYBBJQXZMXXDJMTSYSKKBJZXHJCKLPSMKYJZCXTMLJYXRZZQSLXXQPYZXMKYXXXJCLJPRMYYGADYSKQLSNDHYZKQXZYZTCGHZTLMLWZYBWSYCTBHJHJFCWZTXWYTKZLXQSHLYJZJXTMPLPYCGLTBZZTLZJCYJGDTCLKLPLLQPJMZPAPXYZLKKTKDZCZZBNZDYDYQZJYJGMCTXLTGXSZLMLHBGLKFWNWZHDXUHLFMKYSLGXDTWWFRJEJZTZHYDXYKSHWFZCQSHKTMQQHTZHYMJDJSKHXZJZBZZXYMPAGQMSTPXLSKLZYNWRTSQLSZBPSPSGZWYHTLKSSSWHZZLYYTNXJGMJSZSUFWNLSOZTXGXLSAMMLBWLDSZYLAKQCQCTMYCFJBSLXCLZZCLXXKSBZQCLHJPSQPLSXXCKSLNHPSFQQYTXYJZLQLDXZQJZDYYDJNZPTUZDSKJFSLJHYLZSQZLBTXYDGTQFDBYAZXDZHZJNHHQBYKNXJJQCZMLLJZKSPLDYCLBBLXKLELXJLBQYCXJXGCNLCQPLZLZYJTZLJGYZDZPLTQCSXFDMNYCXGBTJDCZNBGBQYQJWGKFHTNPYQZQGBKPBBYZMTJDYTBLSQMPSXTBNPDXKLEMYYCJYNZCTLDYKZZXDDXHQSHDGMZSJYCCTAYRZLPYLTLKXSLZCGGEXCLFXLKJRTLQJAQZNCMBYDKKCXGLCZJZXJHPTDJJMZQYKQSECQZDSHHADMLZFMMZBGNTJNNLGBYJBRBTMLBYJDZXLCJLPLDLPCQDHLXZLYCBLCXZZJADJLNZMMSSSMYBHBSQKBHRSXXJMXSDZNZPXLGBRHWGGFCXGMSKLLTSJYYCQLTSKYWYYHYWXBXQYWPYWYKQLSQPTNTKHQCWDQKTWPXXHCPTHTWUMSSYHBWCRWXHJMKMZNGWTMLKFGHKJYLSYYCXWHYECLQHKQHTTQKHFZLDXQWYZYYDESBPKYRZPJFYYZJCEQDZZDLATZBBFJLLCXDLMJSSXEGYGSJQXCWBXSSZPDYZCXDNYXPPZYDLYJCZPLTXLSXYZYRXCYYYDYLWWNZSAHJSYQYHGYWWAXTJZDAXYSRLTDPSSYYFNEJDXYZHLXLLLZQZSJNYQYQQXYJGHZGZCYJCHZLYCDSHWSHJZYJXCLLNXZJJYYXNFXMWFPYLCYLLABWDDHWDXJMCXZTZPMLQZHSFHZYNZTLLDYWLSLXHYMMYLMBWWKYXYADTXYLLDJPYBPWUXJMWMLLSAFDLLYFLBHHHBQQLTZJCQJLDJTFFKMMMBYTHYGDCQRDDWRQJXNBYSNWZDBYYTBJHPYBYTTJXAAHGQDQTMYSTQXKBTZPKJLZRBEQQSSMJJBDJOTGTBXPGBKTLHQXJJJCTHXQDWJLWRFWQGWSHCKRYSWGFTGYGBXSDWDWRFHWYTJJXXXJYZYSLPYYYPAYXHYDQKXSHXYXGSKQHYWFDDDPPLCJLQQEEWXKSYYKDYPLTJTHKJLTCYYHHJTTPLTZZCDLTHQKZXQYSTEEYWYYZYXXYYSTTJKLLPZMCYHQGXYHSRMBXPLLNQYDQHXSXXWGDQBSHYLLPJJJTHYJKYPPTHYYKTYEZYENMDSHLCRPQFDGFXZPSFTLJXXJBSWYYSKSFLXLPPLBBBLBSFXFYZBSJSSYLPBBFFFFSSCJDSTZSXZRYYSYFFSYZYZBJTBCTSBSDHRTJJBYTCXYJEYLXCBNEBJDSYXYKGSJZBXBYTFZWGENYHHTHZHHXFWGCSTBGXKLSXYWMTMBYXJSTZSCDYQRCYTWXZFHMYMCXLZNSDJTTTXRYCFYJSBSDYERXJLJXBBDEYNJGHXGCKGSCYMBLXJMSZNSKGXFBNBPTHFJAAFXYXFPXMYPQDTZCXZZPXRSYWZDLYBBKTYQPQJPZYPZJZNJPZJLZZFYSBTTSLMPTZRTDXQSJEHBZYLZDHLJSQMLHTXTJECXSLZZSPKTLZKQQYFSYGYWPCPQFHQHYTQXZKRSGTTSQCZLPTXCDYYZXSQZSLXLZMYCPCQBZYXHBSXLZDLTCDXTYLZJYYZPZYZLTXJSJXHLPMYTXCQRBLZSSFJZZTNJYTXMYJHLHPPLCYXQJQQKZZSCPZKSWALQSBLCCZJSXGWWWYGYKTJBBZTDKHXHKGTGPBKQYSLPXPJCKBMLLXDZSTBKLGGQKQLSBKKTFXRMDKBFTPZFRTBBRFERQGXYJPZSSTLBZTPSZQZSJDHLJQLZBPMSMMSXLQQNHKNBLRDDNXXDHDDJCYYGYLXGZLXSYGMQQGKHBPMXYXLYTQWLWGCPBMQXCYZYDRJBHTDJYHQSHTMJSBYPLWHLZFFNYPMHXXHPLTBQPFBJWQDBYGPNZTPFZJGSDDTQSHZEAWZZYLLTYYBWJKXXGHLFKXDJTMSZSQYNZGGSWQSPHTLSSKMCLZXYSZQZXNCJDQGZDLFNYKLJCJLLZLMZZNHYDSSHTHZZLZZBBHQZWWYCRZHLYQQJBEYFXXXWHSRXWQHWPSLMSSKZTTYGYQQWRSLALHMJTQJSMXQBJJZJXZYZKXBYQXBJXSHZTSFJLXMXZXFGHKZSZGGYLCLSARJYHSLLLMZXELGLXYDJYTLFBHBPNLYZFBBHPTGJKWETZHKJJXZXXGLLJLSTGSHJJYQLQZFKCGNNDJSSZFDBCTWWSEQFHQJBSAQTGYPQLBXBMMYWXGSLZHGLZGQYFLZBYFZJFRYSFMBYZHQGFWZSYFYJJPHZBYYZFFWODGRLMFTWLBZGYCQXCDJYGZYYYYTYTYDWEGAZYHXJLZYYHLRMGRXXZCLHNELJJTJTPWJYBJJBXJJTJTEEKHWSLJPLPSFYZPQQBDLQJJTYYQLYZKDKSQJYYQZLDQTGJQYZJSUCMRYQTHTEJMFCTYHYPKMHYZWJDQFHYYXWSHCTXRLJHQXHCCYYYJLTKTTYTMXGTCJTZAYYOCZLYLBSZYWJYTSJYHBYSHFJLYGJXXTMZYYLTXXYPZLXYJZYZYYPNHMYMDYYLBLHLSYYQQLLNJJYMSOYQBZGDLYXYLCQYXTSZEGXHZGLHWBLJHEYXTWQMAKBPQCGYSHHEGQCMWYYWLJYJHYYZLLJJYLHZYHMGSLJLJXCJJYCLYCJPCPZJZJMMYLCQLNQLJQJSXYJMLSZLJQLYCMMHCFMMFPQQMFYLQMCFFQMMMMHMZNFHHJGTTHHKHSLNCHHYQDXTMMQDCYZYXYQMYQYLTDCYYYZAZZCYMZYDLZFFFMMYCQZWZZMABTBYZTDMNZZGGDFTYPCGQYTTSSFFWFDTZQSSYSTWXJHXYTSXXYLBYQHWWKXHZXWZNNZZJZJJQJCCCHYYXBZXZCYZTLLCQXYNJYCYYCYNZZQYYYEWYCZDCJYCCHYJLBTZYYCQWMPWPYMLGKDLDLGKQQBGYCHJXY",
                s = {
                    19969 : "DZ",
                    19975 : "WM",
                    19988 : "QJ",
                    20048 : "YL",
                    20056 : "SC",
                    20060 : "NM",
                    20094 : "QG",
                    20127 : "QJ",
                    20167 : "QC",
                    20193 : "YG",
                    20250 : "KH",
                    20256 : "ZC",
                    20282 : "SC",
                    20285 : "QJG",
                    20291 : "TD",
                    20314 : "YD",
                    20340 : "NE",
                    20375 : "TD",
                    20389 : "YJ",
                    20391 : "CZ",
                    20415 : "PB",
                    20446 : "YS",
                    20447 : "SQ",
                    20504 : "TC",
                    20608 : "KG",
                    20854 : "QJ",
                    20857 : "ZC",
                    20911 : "PF",
                    20504 : "TC",
                    20608 : "KG",
                    20854 : "QJ",
                    20857 : "ZC",
                    20911 : "PF",
                    20985 : "AW",
                    21032 : "PB",
                    21048 : "XQ",
                    21049 : "SC",
                    21089 : "YS",
                    21119 : "JC",
                    21242 : "SB",
                    21273 : "SC",
                    21305 : "YP",
                    21306 : "QO",
                    21330 : "ZC",
                    21333 : "SDC",
                    21345 : "QK",
                    21378 : "CA",
                    21397 : "SC",
                    21414 : "XS",
                    21442 : "SC",
                    21477 : "JG",
                    21480 : "TD",
                    21484 : "ZS",
                    21494 : "YX",
                    21505 : "YX",
                    21512 : "HG",
                    21523 : "XH",
                    21537 : "PB",
                    21542 : "PF",
                    21549 : "KH",
                    21571 : "E",
                    21574 : "DA",
                    21588 : "TD",
                    21589 : "O",
                    21618 : "ZC",
                    21621 : "KHA",
                    21632 : "ZJ",
                    21654 : "KG",
                    21679 : "LKG",
                    21683 : "KH",
                    21710 : "A",
                    21719 : "YH",
                    21734 : "WOE",
                    21769 : "A",
                    21780 : "WN",
                    21804 : "XH",
                    21834 : "A",
                    21899 : "ZD",
                    21903 : "RN",
                    21908 : "WO",
                    21939 : "ZC",
                    21956 : "SA",
                    21964 : "YA",
                    21970 : "TD",
                    22003 : "A",
                    22031 : "JG",
                    22040 : "XS",
                    22060 : "ZC",
                    22066 : "ZC",
                    22079 : "MH",
                    22129 : "XJ",
                    22179 : "XA",
                    22237 : "NJ",
                    22244 : "TD",
                    22280 : "JQ",
                    22300 : "YH",
                    22313 : "XW",
                    22331 : "YQ",
                    22343 : "YJ",
                    22351 : "PH",
                    22395 : "DC",
                    22412 : "TD",
                    22484 : "PB",
                    22500 : "PB",
                    22534 : "ZD",
                    22549 : "DH",
                    22561 : "PB",
                    22612 : "TD",
                    22771 : "KQ",
                    22831 : "HB",
                    22841 : "JG",
                    22855 : "QJ",
                    22865 : "XQ",
                    23013 : "ML",
                    23081 : "WM",
                    23487 : "SX",
                    23558 : "QJ",
                    23561 : "YW",
                    23586 : "YW",
                    23614 : "YW",
                    23615 : "SN",
                    23631 : "PB",
                    23646 : "ZS",
                    23663 : "ZT",
                    23673 : "YG",
                    23762 : "TD",
                    23769 : "ZS",
                    23780 : "QJ",
                    23884 : "QK",
                    24055 : "XH",
                    24113 : "DC",
                    24162 : "ZC",
                    24191 : "GA",
                    24273 : "QJ",
                    24324 : "NL",
                    24377 : "TD",
                    24378 : "QJ",
                    24439 : "PF",
                    24554 : "ZS",
                    24683 : "TD",
                    24694 : "WE",
                    24733 : "LK",
                    24925 : "TN",
                    25094 : "ZG",
                    25100 : "XQ",
                    25103 : "XH",
                    25153 : "PB",
                    25170 : "PB",
                    25179 : "KG",
                    25203 : "PB",
                    25240 : "ZS",
                    25282 : "FB",
                    25303 : "NA",
                    25324 : "KG",
                    25341 : "ZY",
                    25373 : "WZ",
                    25375 : "XJ",
                    25384 : "A",
                    25457 : "A",
                    25528 : "SD",
                    25530 : "SC",
                    25552 : "TD",
                    25774 : "ZC",
                    25874 : "ZC",
                    26044 : "YW",
                    26080 : "WM",
                    26292 : "PB",
                    26333 : "PB",
                    26355 : "ZY",
                    26366 : "CZ",
                    26397 : "ZC",
                    26399 : "QJ",
                    26415 : "ZS",
                    26451 : "SB",
                    26526 : "ZC",
                    26552 : "JG",
                    26561 : "TD",
                    26588 : "JG",
                    26597 : "CZ",
                    26629 : "ZS",
                    26638 : "YL",
                    26646 : "XQ",
                    26653 : "KG",
                    26657 : "XJ",
                    26727 : "HG",
                    26894 : "ZC",
                    26937 : "ZS",
                    26946 : "ZC",
                    26999 : "KJ",
                    27099 : "KJ",
                    27449 : "YQ",
                    27481 : "XS",
                    27542 : "ZS",
                    27663 : "ZS",
                    27748 : "TS",
                    27784 : "SC",
                    27788 : "ZD",
                    27795 : "TD",
                    27812 : "O",
                    27850 : "PB",
                    27852 : "MB",
                    27895 : "SL",
                    27898 : "PL",
                    27973 : "QJ",
                    27981 : "KH",
                    27986 : "HX",
                    27994 : "XJ",
                    28044 : "YC",
                    28065 : "WG",
                    28177 : "SM",
                    28267 : "QJ",
                    28291 : "KH",
                    28337 : "ZQ",
                    28463 : "TL",
                    28548 : "DC",
                    28601 : "TD",
                    28689 : "PB",
                    28805 : "JG",
                    28820 : "QG",
                    28846 : "PB",
                    28952 : "TD",
                    28975 : "ZC",
                    29100 : "A",
                    29325 : "QJ",
                    29575 : "SL",
                    29602 : "FB",
                    30010 : "TD",
                    30044 : "CX",
                    30058 : "PF",
                    30091 : "YSP",
                    30111 : "YN",
                    30229 : "XJ",
                    30427 : "SC",
                    30465 : "SX",
                    30631 : "YQ",
                    30655 : "QJ",
                    30684 : "QJG",
                    30707 : "SD",
                    30729 : "XH",
                    30796 : "LG",
                    30917 : "PB",
                    31074 : "NM",
                    31085 : "JZ",
                    31109 : "SC",
                    31181 : "ZC",
                    31192 : "MLB",
                    31293 : "JQ",
                    31400 : "YX",
                    31584 : "YJ",
                    31896 : "ZN",
                    31909 : "ZY",
                    31995 : "XJ",
                    32321 : "PF",
                    32327 : "ZY",
                    32418 : "HG",
                    32420 : "XQ",
                    32421 : "HG",
                    32438 : "LG",
                    32473 : "GJ",
                    32488 : "TD",
                    32521 : "QJ",
                    32527 : "PB",
                    32562 : "ZSQ",
                    32564 : "JZ",
                    32735 : "ZD",
                    32793 : "PB",
                    33071 : "PF",
                    33098 : "XL",
                    33100 : "YA",
                    33152 : "PB",
                    33261 : "CX",
                    33324 : "BP",
                    33333 : "TD",
                    33406 : "YA",
                    33426 : "WM",
                    33432 : "PB",
                    33445 : "JG",
                    33486 : "ZN",
                    33493 : "TS",
                    33507 : "QJ",
                    33540 : "QJ",
                    33544 : "ZC",
                    33564 : "XQ",
                    33617 : "YT",
                    33632 : "QJ",
                    33636 : "XH",
                    33637 : "YX",
                    33694 : "WG",
                    33705 : "PF",
                    33728 : "YW",
                    33882 : "SR",
                    34067 : "WM",
                    34074 : "YW",
                    34121 : "QJ",
                    34255 : "ZC",
                    34259 : "XL",
                    34425 : "JH",
                    34430 : "XH",
                    34485 : "KH",
                    34503 : "YS",
                    34532 : "HG",
                    34552 : "XS",
                    34558 : "YE",
                    34593 : "ZL",
                    34660 : "YQ",
                    34892 : "XH",
                    34928 : "SC",
                    34999 : "QJ",
                    35048 : "PB",
                    35059 : "SC",
                    35098 : "ZC",
                    35203 : "TQ",
                    35265 : "JX",
                    35299 : "JX",
                    35782 : "SZ",
                    35828 : "YS",
                    35830 : "E",
                    35843 : "TD",
                    35895 : "YG",
                    35977 : "MH",
                    36158 : "JG",
                    36228 : "QJ",
                    36426 : "XQ",
                    36466 : "DC",
                    36710 : "JC",
                    36711 : "ZYG",
                    36767 : "PB",
                    36866 : "SK",
                    36951 : "YW",
                    37034 : "YX",
                    37063 : "XH",
                    37218 : "ZC",
                    37325 : "ZC",
                    38063 : "PB",
                    38079 : "TD",
                    38085 : "QY",
                    38107 : "DC",
                    38116 : "TD",
                    38123 : "YD",
                    38224 : "HG",
                    38241 : "XTC",
                    38271 : "ZC",
                    38415 : "YE",
                    38426 : "KH",
                    38461 : "YD",
                    38463 : "AE",
                    38466 : "PB",
                    38477 : "XJ",
                    38518 : "YT",
                    38551 : "WK",
                    38585 : "ZC",
                    38704 : "XS",
                    38739 : "LJ",
                    38761 : "GJ",
                    38808 : "SQ",
                    39048 : "JG",
                    39049 : "XJ",
                    39052 : "HG",
                    39076 : "CZ",
                    39271 : "XT",
                    39534 : "TD",
                    39552 : "TD",
                    39584 : "PB",
                    39647 : "SB",
                    39730 : "LG",
                    39748 : "TPB",
                    40109 : "ZQ",
                    40479 : "ND",
                    40516 : "HG",
                    40536 : "HG",
                    40583 : "QJ",
                    40765 : "YQ",
                    40784 : "QJ",
                    40840 : "YK",
                    40863 : "QJG"
                };
            c.exports.makePy = function(a) {
                if ("string" != typeof a) throw new Error( - 1, "函数makePy需要字符串类型参数!");
                for (var b = new Array,
                         c = 0,
                         d = a.length; c < d; c++) {
                    var e = a.charAt(c);
                    b.push(t(e))
                }
                return u(b)
            };
            var t = function(a) {
                    var b = a.charCodeAt(0);
                    return b > 40869 || b < 19968 ? a: s[b] ? s[b] : r.charAt(b - 19968)
                },
                u = function(a) {
                    for (var b = [""], c = 0, d = a.length; c < d; c++) {
                        var e = a[c],
                            f = e.length;
                        if (1 == f) for (var g = 0; g < b.length; g++) b[g] += e;
                        else {
                            var h = b.slice(0);
                            for (b = [], g = 0; g < f; g++) {
                                for (var i = h.slice(0), j = 0; j < i.length; j++) i[j] += e.charAt(g);
                                b = b.concat(i)
                            }
                        }
                    }
                    return b
                }
        }),
    define("assets/common/js/memory", ["jquery"],
        function(a) {
            var b = "/xdnphb",
                c = {
                    zz: "/zz"
                },
                d = a("jquery"),
                e = [["ss", "时事", "以时政社会类新闻报道评论为主要内容，通常为拥有新闻信息服务资质的媒体机构主办。"], ["mgs", "民生", "着眼于贴近生活服务，例如民生政策、交通资讯、吃喝玩乐、打折信息等。"], ["cf", "财富", "涵盖宏观与微观两个层面的财经投资领域，包含财经、产经、证券、理财等。"], ["kj", "科技", "关注以TMT为重点的互联网事业发展，也包括电脑、手机的软硬件资讯，以及大数据等新兴科学技术。"], ["cy", "创业", "从创业者、管理层角度观察，提供管理、营销、电商等方面的资讯与分析。"], ["qc", "汽车", "汽车产业相关讯息，包括车市情报、驾车技巧、路况信息、违章查询等。"], ["ls", "楼市", "聚焦房地产行业，以楼市情报、分析为主。"], ["zc", "职场", "帮助实现职场能力提升与职位晋升，既包括实际工作技能传授，也包括培训招聘信息。"], ["jy", "教育", "涵盖从母婴、亲子到K12、高校、职业教育全程。"], ["xs", "学术", "学术动态、前沿课题，理论思考及研究。"], ["zw", "政务", "宣传国家政策、介绍政府动态、提供电子政务服务等，通常为各级政府部门或事业单位主办。"], ["qy", "企业", "主要内容和功能是用于宣传推广企业自身产品服务或文化。"]],
                f = [["wh", "文化", "涵盖艺术、历史、读书、思想、小说等各类文化产品形态。"], ["bk", "百科", "传递讲解世间万物百科原理，或专注于某一知识兴趣领域。"], ["jk", "健康", "健康养生类资讯，以及医疗行业情报。"], ["shs", "时尚", "聚焦时尚潮流，包括名流生活、衣妆搭配等。"], ["ms", "美食", "各地美食美酒推荐，以及美食制作、菜谱等。"], ["sj", "乐活", "现代人起居生活、爱好乐趣相关事物，包括户外、宠物、摄影、设计、收藏、家居等。"], ["lx", "旅行", "专注于各地旅行信息推荐发布，以及游记。"], ["ym", "幽默", "通过段子、视频、GIF图等载体推送的笑话减压类内容。"], ["qg", "情感", "婚恋咨询、情感沟通、心灵感悟等。"], ["ty", "体娱", "涵盖体育、娱乐、明星八卦、影视资讯以及网络游戏。"], ["mt", "美体", "瘦身健身类资讯，以及美容美发护肤保养经验传授。"], ["zs", "文摘", "以转载整合热门内容为主要特征，无明确细分定位。"]],
                g = [{
                    name: "总排名",
                    type: "zongpaiming"
                },
                    {
                        name: "视频榜",
                        type: "shipin"
                    },
                    {
                        name: "时事",
                        type: "shishi"
                    },
                    {
                        name: "财经",
                        type: "caijing"
                    },
                    {
                        name: "军事",
                        type: "junshi"
                    },
                    {
                        name: "历史",
                        type: "lishi"
                    },
                    {
                        name: "教育",
                        type: "jiaoyu"
                    },
                    {
                        name: "汽车",
                        type: "qiche"
                    },
                    {
                        name: "文化",
                        type: "wenhua"
                    },
                    {
                        name: "科技",
                        type: "keji"
                    },
                    {
                        name: "搞笑",
                        type: "gaoxiao"
                    },
                    {
                        name: "娱乐",
                        type: "yule"
                    },
                    {
                        name: "时尚",
                        type: "shishang"
                    },
                    {
                        name: "美食",
                        type: "meishi"
                    },
                    {
                        name: "游戏",
                        type: "youxi"
                    },
                    {
                        name: "健康",
                        type: "jiankang"
                    },
                    {
                        name: "体育",
                        type: "tiyu"
                    },
                    {
                        name: "旅游",
                        type: "lvyou"
                    },
                    {
                        name: "情感",
                        type: "qinggan"
                    },
                    {
                        name: "母婴",
                        type: "muying"
                    },
                    {
                        name: "星座",
                        type: "xingzuo"
                    },
                    {
                        name: "三农",
                        type: "sannong"
                    },
                    {
                        name: "宠物",
                        type: "chongwu"
                    },
                    {
                        name: "生活",
                        type: "shenghuo"
                    }],
                h = [["all", "总排名"], ["jrcj", "财富产经"], ["jkys", "健康养生"], ["kjzn", "科技智能"], ["lhqw", "乐活趣玩"], ["lxhw", "旅行户外"], ["mscy", "美食餐饮"], ["mzsh", "美妆时尚"], ["qcjs", "汽车驾驶"], ["qzye", "亲子育儿"], ["qgxl", "情感心理"], ["shbk", "生活百科"], ["tyjs", "体育健身"], ["whjy", "文化教育"], ["xwzx", "热门资讯"], ["ysyl", "影视娱乐"], ["ymgx", "幽默搞笑"], ["yxdm", "游戏动漫"]],
                i = ["ss", "mgs", "cf", "kj", "cy", "qc", "ls", "zc", "jy", "xs", "zw", "qy"],
                j = ["wh", "bk", "jk", "shs", "ms", "sj", "lx", "ym", "qg", "ty", "mt", "zs"],
                k = ["shishi", "zhengwu", "guoji", "caijing", "fangchan", "junshi", "lishi", "jiaoyu", "zhichang", "shehui", "nongye", "qiche", "wenhua", "keji", "baike", "gaoxiao", "yule", "shishang", "meishi", "youxi", "jiankang", "tiyu", "lvyou", "qinggan", "lehuo", "muying", "zongjiao", "zonghe"],
                l = ["all", "jrcj", "jkys", "kjzn", "lhww", "hwly", "mscy", "myzb", "qcjs", "qzye", "qgxl", "shbk", "tyjs", "whjy", "xwzx", "ysyl", "ymgx", "yxdm"],
                m = [],
                n = {},
                o = {},
                p = {};
            d.each(e,
                function() {
                    m.push(this[1]),
                        n[this[1]] = this[0],
                        o[this[0]] = this[1],
                        p[this[0]] = "资讯"
                }),
                d.each(f,
                    function() {
                        m.push(this[1]),
                            n[this[1]] = this[0],
                            o[this[0]] = this[1],
                            p[this[0]] = "生活"
                    });
            var q = {
                    pgcType: h,
                    pgcIcon: l,
                    toutiaoArr: g,
                    toutiaoIcon: k,
                    zixun: e,
                    zixunIcon: i,
                    life: f,
                    lifeIcon: j,
                    lifeObj: {
                        "时事": "ss",
                        "民生": "mgs",
                        "财富": "cf",
                        "科技": "kj",
                        "创业": "cy",
                        "汽车": "qc",
                        "楼市": "ls",
                        "职场": "zc",
                        "教育": "jy",
                        "学术": "xs",
                        "政务": "zw",
                        "企业": "qy",
                        "文化": "wh",
                        "百科": "bk",
                        "健康": "jk",
                        "时尚": "shs",
                        "美食": "ms",
                        "乐活": "sj",
                        "旅行": "lx",
                        "幽默": "ym",
                        "情感": "qg",
                        "体娱": "ty",
                        "美体": "mt",
                        "文摘": "zs"
                    },
                    getArray: function() {
                        var a = [];
                        return d.each(this.zixun,
                            function() {
                                a.push(this[1])
                            }),
                            d.each(this.life,
                                function() {
                                    a.push(this[1])
                                }),
                            a
                    },
                    getAdeArray: function() {
                        var a = [];
                        return d.each(this.zixun,
                            function() {
                                "政务" != this[1] && "企业" != this[1] && a.push(this[1])
                            }),
                            d.each(this.life,
                                function() {
                                    "政务" != this[1] && "企业" != this[1] && a.push(this[1])
                                }),
                            a
                    },
                    getMap: function(a) {
                        return this.lifeObj[a]
                    },
                    getNameByIcon: function(a) {
                        return o[a]
                    },
                    getGroupNameByIcon: function(a) {
                        return p[a]
                    }
                },
                r = ["北京", "上海", "天津", "重庆", "河北", "山西", "辽宁", "吉林", "黑龙江", "江苏", "浙江", "安徽", "福建", "江西", "山东", "河南", "湖北", "湖南", "广东", "海南", "四川", "贵州", "云南", "陕西", "甘肃", "青海", "内蒙古", "广西", "西藏", "宁夏", "新疆", "香港", "澳门", "台湾", "海外"],
                s = [{
                    name: "新浪微博",
                    type: 0,
                    key: "weibo",
                    account: "央视新闻",
                    url: "http://weibo.com/cctvxinwen"
                },
                    {
                        name: "今日头条",
                        type: 1,
                        key: "toutiao",
                        account: "央视新闻",
                        url: "http://toutiao.com/m4492956276/"
                    },
                    {
                        name: "QQ公众号",
                        type: 2,
                        key: "qq-account",
                        account: "新榜",
                        url: ""
                    },
                    {
                        name: "优酷",
                        type: 3,
                        key: "youku",
                        account: "-影人影视传媒-",
                        url: "http://i.youku.com/iyingren"
                    },
                    {
                        name: "喜马拉雅FM",
                        type: 4,
                        key: "xima",
                        account: "飞议于论",
                        url: "http://www.ximalaya.com/zhubo/11734436"
                    },
                    {
                        name: "蜻蜓FM",
                        type: 5,
                        key: "qingting",
                        account: "CNR音乐之声",
                        url: "http://qingting.fm/channels/388"
                    },
                    {
                        name: "荔枝FM",
                        type: 6,
                        key: "lizhi",
                        account: "罗辑思维",
                        url: "http://www.lizhi.fm/17248/"
                    },
                    {
                        name: "考拉FM",
                        type: 7,
                        key: "kaola",
                        account: "新闻深喉",
                        url: "http://www.kaolafm.com/zj/V0mc7D_Q.html"
                    },
                    {
                        name: "一点资讯",
                        type: 8,
                        key: "yidianzixun",
                        account: "王尼玛",
                        url: "http://www.yidianzixun.com/channel/m352253"
                    },
                    {
                        name: "天天快报",
                        type: 9,
                        key: "tiantian",
                        account: "光明网",
                        url: ""
                    },
                    {
                        name: "腾讯新闻",
                        type: 10,
                        key: "qq",
                        account: "新闻哥",
                        url: ""
                    },
                    {
                        name: "搜狐号",
                        type: 11,
                        key: "sohu",
                        account: "财联社",
                        url: "http://mp.sohu.com/profile?xpt=c29odXptdG5hdnVnMDhAc29odS5jb20="
                    },
                    {
                        name: "网易号",
                        type: 12,
                        key: "163",
                        account: "好奇实验室",
                        url: "http://news.163.com"
                    },
                    {
                        name: "凤凰号",
                        type: 13,
                        key: "ifeng",
                        account: "汽车洋葱圈",
                        url: "http://wemedia.ifeng.com/listpage/5894_1/list.shtml"
                    },
                    {
                        name: "zaker",
                        type: 14,
                        key: "zaker",
                        account: "观察者网",
                        url: "http://www.myzaker.com/source/12557"
                    },
                    {
                        name: "澎湃新闻",
                        type: 15,
                        key: "thepaper",
                        account: "文化课",
                        url: "http://www.thepaper.cn/list_25450"
                    },
                    {
                        name: "百家号",
                        type: 16,
                        key: "baijia",
                        account: "云飞扬",
                        url: "http://yunfeiyang.baijia.baidu.com/"
                    },
                    {
                        name: "百度新闻",
                        type: 17,
                        key: "baidu",
                        account: "参考消息网",
                        url: ""
                    },
                    {
                        name: "企鹅号",
                        type: 18,
                        key: "qie",
                        account: "参考消息",
                        url: "https://kuaibao.qq.com/s/MEDIANEWSLIST?chlid=1048"
                    },
                    {
                        name: "大鱼号",
                        type: 19,
                        key: "dayu",
                        account: "sir电影",
                        url: ""
                    },
                    {
                        name: "知乎",
                        type: 20,
                        key: "zhihu",
                        account: "有车以后",
                        url: "https://zhuanlan.zhihu.com/iyourcar"
                    },
                    {
                        name: "界面",
                        type: 21,
                        key: "jiemian",
                        account: "有车以后",
                        url: "https://a.jiemian.com/index.php?m=user&a=centerArticle&id=101447243"
                    },
                    {
                        name: "36氪",
                        type: 22,
                        key: "36kr",
                        account: "少数派",
                        url: "http://36kr.com/user/569821167"
                    },
                    {
                        name: "企鹅FM",
                        type: 23,
                        key: "qiefm",
                        account: "",
                        url: ""
                    },
                    {
                        name: "腾讯视频",
                        type: 24,
                        key: "tengxunvideo",
                        account: "陈翔六点半",
                        url: "http://v.qq.com/vplus/xiaotaijiong"
                    },
                    {
                        name: "爱奇艺",
                        type: 25,
                        key: "iqiyi",
                        account: "陈翔六点半",
                        url: "http://www.iqiyi.com/u/1071611103"
                    },
                    {
                        name: "秒拍",
                        type: 26,
                        key: "miaopai",
                        account: "陈翔六点半",
                        url: "https://www.miaopai.com/u/paike_5no3e2iw6g"
                    },
                    {
                        name: "美拍",
                        type: 27,
                        key: "meipai",
                        account: "陈翔六点半",
                        url: "http://www.meipai.com/user/32821588"
                    }],
                t = [{
                    name: "youcai1",
                    color: "rgb(169,218,255)",
                    url: "#"
                },
                    {
                        name: "youcai2",
                        color: "rgb(169,218,255)",
                        url: "#"
                    },
                    {
                        name: "index_client",
                        color: "#e8e8e8",
                        url: "https://jinshuju.net/f/mTX8It"
                    }],
                u = [{
                    name: "banner_3",
                    color: "",
                    url: "https://jinshuju.net/f/DW0FpJ"
                },
                    {
                        name: "banner_1",
                        color: "",
                        url: "#"
                    },
                    {
                        name: "banner_2",
                        color: "",
                        url: "#"
                    },
                    {
                        name: "banner_4",
                        color: "",
                        url: "http://719a18b2653a.ih5.cn/idea/KhrNrY2"
                    }],
                v = [{
                    name: "bigpic",
                    color: "rgb(253,147,73)",
                    url: "#"
                },
                    {
                        name: "haowai",
                        color: "rgb(25,149,201)",
                        url: "https://jinshuju.net/f/mTX8It"
                    }],
                w = {
                    rong: "https://zhaopin.newrank.cn/",
                    accelerator: "https://accelerator.newrank.cn/",
                    login: "https://login.newrank.cn/",
                    main: "https://www.newrank.cn/",
                    admin: "https://admin.newrank.cn/",
                    edit: "https://edit.newrank.cn/",
                    copyright: "https://cc.newrank.cn/",
                    data: "https://data.newrank.cn/",
                    common: "https://assets.newrank.cn/",
                    pay: "https://pay.newrank.cn/",
                    goldrank: "https://gold.newrank.cn/",
                    report: "https://report.newrank.cn/",
                    zz: "https://zz.newrank.cn/",
                    annual: "https://2017.newrank.cn/",
                    demo: "https://demo.newrank.cn/",
                    shop: "https://cec.newrank.cn/",
                    original: "https://hot.newrank.cn/",
                    ade: "https://a.newrank.cn/",
                    esc: "https://esc.newrank.cn/",
                    help: "https://help.newrank.cn/",
                    api: "https://api.newrank.cn/",
                    mini: "https://mini.newrank.cn/",
                    services: "https://services.newrank.cn/"
                };
            return {
                appBase: b,
                prefix: c,
                urlBase: b + "/",
                rootUrl: w,
                servicesUUid: {
                    trend: "CE427E47CCE77D2E9275DFE757079F70"
                },
                appDomain: "newrank.cn",
                uploadBase: "/genus/upload/",
                accessSuffix: "",
                Constants: {
                    Login: {
                        LoginFail: {
                            Option_None: 0,
                            Option_NeedLogin: 1,
                            Option_ShowFlag: 2,
                            LoginFailFlag: "you have failed"
                        }
                    }
                },
                currentUser: {},
                weixinTypes: q,
                positionList: r,
                hostWord: "1、请严格遵守《广告法》和平台相关规定。</br></br>2、请尽量提供明确完整的投放文案素材，并承诺尽量不在后期沟通时要求更改。如果您不能提供明确完整的投放文案素材，或需要由媒体主自行创作，请在文案中说明。媒体主会根据您的需求来判断是否承接。</br></br>3、您的询购和接受竞标的行为均视作要约，请慎重执行，如果无故违约，将有可能受到降低广告主等级权限和纳入信用记录等处罚。</br></br>4、您如果发布了竞标任务，请及时查看并作出选择。</br></br>5、新榜鼓励广告主和媒体主直接通过留言或私信沟通，但请不要在竞标任务中直接张贴联系方式。</br></br>6、任何绕过本平台的场外私下交易，将无法获得新榜提供的交易安全保障，包括资金担保、正规发票以及效果监测等。同时，这也将影响您的广告主信用记录。</br></br>7、新榜拥有审核投放任务的权利。</br></br>8、新榜将为您的投放提供正规服务费发票，税费由您承担，是您与媒体主约定投放净价的6%。</br></br>9、如果您有垫付款项、策划执行等超出自助交易以外的需求，请联络新榜旗下媒介营销机构博选优采热线电话4000066059。",
                clientWord: "1、请严格遵守《广告法》和平台相关规定。</br></br>2、请自行评估承接广告主投放任务的收益和风险。</br></br>3、您的接受询购和竞标的行为均视作要约，请慎重执行，如果无故违约，将有可能受到纳入信用记录等处罚。</br></br>4、您与广告主达成合作意向后，请严格遵守在线合同约定执行投放，特别是约定的投放时间和位置，在线合同是新榜用来判断是否向您汇入款项的唯一标准。</br></br>5、任何绕过本平台的场外私下交易，将无法获得新榜提供的交易安全担保，包括100%付款保障等。同时，这也将影响您的媒体主信用记录和品牌价值评分。</br></br>6、新榜将为广告主的投放提供正规服务费发票，税费由广告主另行承担，无关您与广告主约定的投放净价。",
                mdValue: "daddy",
                AppKey: "joker",
                microList: s,
                YouxuanLunbo: t,
                YouxuanLunboNew: u,
                cookietime: 2592e3,
                CopyRightLunBo: v,
                roleService: {
                    661 : {
                        name: "趋势查询",
                        uuid: "CE427E47CCE77D2E9275DFE757079F70",
                        bangdou: 300,
                        redirect: {
                            name: "趋势查询",
                            url: w.data + "trendResult.html"
                        }
                    },
                    92 : {
                        name: "文章高级搜索",
                        uuid: "b238b1b8913849f09fefa2cb405c37aa",
                        bangdou: 500,
                        alias: "高级搜索版",
                        redirect: {
                            name: "文章搜索",
                            url: w.data + "articleResult.html"
                        }
                    },
                    93 : {
                        name: "文章舆情应用",
                        uuid: "f20c22ba38c6416bb40d960c34f6b3aa",
                        bangdou: 1e3,
                        alias: "舆情应用版",
                        redirect: {
                            name: "文章搜索",
                            url: w.data + "articleResult.html"
                        }
                    },
                    94 : {
                        name: "文章数据应用",
                        uuid: "d5cf14743b004ac285be2af72bbbbc2b",
                        bangdou: 5e3,
                        alias: "数据应用版",
                        redirect: {
                            name: "文章搜索",
                            url: w.data + "articleResult.html"
                        }
                    },
                    95 : {
                        name: "文章高级应用",
                        uuid: "8fcf3464f28c44609479ce8cffa3a12f",
                        bangdou: 8e3,
                        alias: "高级应用版",
                        redirect: {
                            name: "文章搜索",
                            url: w.data + "articleResult.html"
                        }
                    },
                    96 : {
                        name: "文章集团定制",
                        uuid: "94448af7d0bb4943af4dbe09a225f71b",
                        bangdou: 1e4,
                        alias: "集团定制版",
                        redirect: {
                            name: "文章搜索",
                            url: w.data + "articleResult.html"
                        }
                    }
                },
                hasNewAds: "none",
                appId: {
                    test: "wxd1d1638e443fe774",
                    develop: "wxd1d1638e443fe774"
                }
            }
        }),
    define("assets/common/js/nav", ["jquery"],
        function(a, b, c) {
            var d = (c.uri, !0),
                e = a("jquery"),
                f = (a("exports"), a("dialog")),
                g = a("cookie"),
                h = a("common"),
                i = a("login-dialog"),
                j = a("services"),
                k = a("api-common"),
                l = a("memory"),
                m = a(l.rootUrl.main + "static/public/info/api-search");
            a("autocomplete");
            var n, o = h.StringBuffer,
                p = a("api-common"),
                q = (function() {
                    var a = k.getNeedLoginData,
                        b = (k.getNeedLoginDataSync, l.urlBase),
                        c = b + "editor/ads/"
                } (),
                    function() {
                        var a = k.getNeedLoginData,
                            b = (k.getNeedLoginDataSync, l.urlBase);
                        k.download; (function() {
                            var a = b + "message/relation/"
                        })()
                    } (),
                    function() {
                        var a = l.urlBase,
                            b = a + "common/account/",
                            c = k.getCommonData,
                            d = k.getCommonDataSynchronous;
                        return {
                            info: {
                                getUser: function(a) {
                                    c(b + "getFull", {},
                                        a)
                                }
                            },
                            infoSync: {
                                getUser: function(a) {
                                    d(b + "getFull", {},
                                        a)
                                }
                            },
                            getUser: function(a) {
                                c(b + "get", {},
                                    a)
                            }
                        }
                    } ()),
                r = function() {
                    var a = k.getCommonData,
                        b = l.urlBase,
                        c = b + "indexNew/";
                    return {
                        getIcon: function(b) {
                            a(c + "getAdvertBannerImage", {},
                                b)
                        }
                    }
                } (),
                s = (function() {
                    var a = l.urlBase,
                        b = a + "knowledgepay/v1/api/distribution/"
                } (), "");
            s = "新榜——内容创业服务平台" == document.title || window.rootPage ? "": window.relativeUrl,
                s = "https://www.newrank.cn/";
            var t, u = 0,
                v = 0,
                w = !1,
                x = h.getRequest(!0),
                y = x.isBind,
                z = x.bindType,
                A = x.value;
            void 0 == y && (y = !1);
            var B = {
                    normal: "请输入微信公众号ID、名称（如：新榜）",
                    touFang: "输入投放关键词",
                    ziXun: "请输入所搜资讯关键字",
                    keyWords: "请输入关键词"
                },
                C = {
                    toAde: {
                        title: "广告",
                        bindType: "toAde",
                        text: B.touFang,
                        logoClass: "header-search-youxuan",
                        clickFunction: function(a) {
                            window.open(l.rootUrl.ade + "center/#/user/cpc_action/wx_aticle")
                        },
                        searchFunction: function(a) {},
                        logoFunction: function() {}
                    },
                    xungou: {
                        title: "可询购",
                        bindType: "xungou",
                        text: B.normal,
                        logoClass: "header-search-logo",
                        clickFunction: function(a) {
                            window.open(l.rootUrl.ade + "enquire/select.html")
                        },
                        searchFunction: function(a) {
                            window.location.href = s + "public/info/search.html?value=" + encodeURIComponent(a) + "&isBind=" + y
                        },
                        logoFunction: function() {}
                    },
                    report: {
                        title: "报告",
                        bindType: "report",
                        text: B.keyWords,
                        logoClass: "header-search-logo-report",
                        clickFunction: function(a) {
                            F.checkDefaultText(a) ? window.open(l.rootUrl.report + "index.html?bindType=" + this.bindType) : window.open(l.rootUrl.report + "report_search.html?value=" + encodeURIComponent(a) + "&bindType=" + this.bindType)
                        },
                        searchFunction: function(a) {
                            window.location.href = l.rootUrl.report + "report_search.html?value=" + encodeURIComponent(a) + "&bindType=" + z
                        },
                        logoFunction: function() {
                            window.location.href = l.rootUrl.report + "index.html?bindType=" + this.bindType
                        }
                    },
                    media: {
                        title: "资讯",
                        bindType: "media",
                        text: B.ziXun,
                        logoClass: "header-search-logo-zixun",
                        clickFunction: function(a) {
                            F.checkDefaultText(a) ? window.open(s + "public/news.html") : window.open(s + "public/news.html?value=" + encodeURIComponent(a))
                        },
                        searchFunction: function(a) {},
                        logoFunction: function() {
                            window.location.href = s + "public/news.html"
                        }
                    },
                    account: {
                        title: "公众号",
                        bindType: "account",
                        text: B.normal,
                        logoClass: "header-search-logo",
                        clickFunction: function(a) {
                            F.checkDefaultText(a) ? window.open(s + "index.html") : window.open(s + "public/info/search.html?value=" + encodeURIComponent(a) + "&isBind=false")
                        },
                        searchFunction: function(a) {
                            window.location.href = s + "public/info/search.html?value=" + encodeURIComponent(a) + "&isBind=" + y
                        },
                        logoFunction: function() {}
                    },
                    trend: {
                        title: "趋势",
                        bindType: "trend",
                        text: B.normal,
                        logoClass: "header-search-logo",
                        clickFunction: function(a) {
                            window.open(l.rootUrl.data + "trend.html")
                        },
                        searchFunction: function(a) {}
                    },
                    article: {
                        title: "文章",
                        bindType: "article",
                        text: B.normal,
                        logoClass: "header-search-logo",
                        clickFunction: function(a) {
                            F.checkDefaultText(a) ? window.open(l.rootUrl.data + "articleSearch.html") : window.open(l.rootUrl.data + "articleResult.html?value=" + escape(a) + "&category=&flag=true")
                        },
                        searchFunction: function(a) {}
                    },
                    copyright: {
                        title: "正版转载",
                        bindType: "copyright",
                        text: B.normal,
                        logoClass: "header-search-logo",
                        clickFunction: function(a) {
                            window.open(l.rootUrl.copyright + "reprint-side.html")
                        },
                        searchFunction: function(a) {}
                    }
                },
                D = function(a, b, c) { (function() {
                    a(function(a) {
                        t = a.user || "",
                            n = t.user_type || "";
                        var h = !1; (!t || n >= 100 || -1 == n) && (h = !0);
                        var j = [d ? '<div class="new-header-old">': '<div class="new-header-con">', '<ul class="new-header-nav">', '<li class="index-li"><a href="' + l.rootUrl.main + '" class="new-header-index-link">首页</a><i class="drop-icon footer-pic"></i><span class="seperate-line"></span>', "<ul>", '<div class="white-line"></div>', '<li><a href="' + l.rootUrl.main + 'public/news.html" target="_blank">资讯情报</a><span class="seperate-line"></span></li >', '<li><a href="' + l.rootUrl.original + '" target="_blank">热门内容</a><span class="seperate-line"></span></li >', '<li><a href="' + l.rootUrl.report + 'index.html?bindType=report" target="_blank">新榜报告</a><span class="seperate-line"></span></li >', '<li><a href="' + l.rootUrl.help + 'help_detail.html?uuid=7DB1C684B0B3C368CF74C660F7B8DDA6" target="_blank">帮助中心</a><span class="footer-pic new-icon new-header-help"></span></li >', '<li><a href="' + l.rootUrl.main + 'public/auth/accountAuth.html" target="_blank">新榜认证</a><span class="seperate-line"></span></li >', '<li><a href="' + l.rootUrl.main + 'public/brand/brand_zone.html" target="_blank">品牌专区</a><span class="seperate-line"></span></li >', '<li><a href="' + l.rootUrl.edit + '" target="_blank">新榜编辑器</a></li >', '<div class="split-line clear"></div>', '<li><a href="' + l.rootUrl.main + 'public/about/about.html" target="_blank">关于新榜</a><span class="seperate-line"></span></li >', '<li><a href="' + l.rootUrl.main + 'public/about/reference.pdf" target="_blank">新榜指数</a><span class="seperate-line"></span></li >', '<li><a href="' + l.rootUrl.main + 'public/about/cooperation.html" target="_blank">合作伙伴</a></li >', "</ul>", "</li>", '<li class="bangdan-li"><a href="' + s + 'public/info/list.html?period=day&type=data">榜单</a><i class="drop-icon footer-pic"></i><span class="seperate-line"></span>', "<ul>", '<div class="white-line"></div>', '<li><a href="' + l.rootUrl.main + 'public/info/list.html?period=day&type=data" target="_blank">微信</a><span class="seperate-line"></span><a href="' + l.rootUrl.main + 'public/info/list.html?period=weibo_day&type=data" target="_blank">微博</a><span class="seperate-line"></span><a href="' + l.rootUrl.main + 'public/info/list.html?period=toutiao_day&type=data" target="_blank">头条号</a><span class="seperate-line"></span><a href="' + l.rootUrl.main + 'public/info/list.html?period=pgcweek&type=data" target="_blank">PGC视频</a><span class="seperate-line"></span><a href="' + l.rootUrl.main + 'public/info/list.html?period=taobao_day&type=data" target="_blank">淘直播</a><span class="footer-pic new-icon new-header-routine"></span></li>', '<li><a href="' + l.rootUrl.main + 'public/info/list.html?period=routine_day&type=data" target="_blank">小程序</a><span class="seperate-line"></span><a href="' + l.rootUrl.main + 'public/info/rank.html?type=2" target="_blank">更多平台</a><span class="seperate-line"></span><a href="' + l.rootUrl.main + 'account/user/ranklist.html" target="_blank">自定义榜单</a><span class="seperate-line"></span><a href="' + l.rootUrl.main + 'public/about/add.html" target="_blank">冲榜入口</a></li>', "</ul>", "</li>", '<li class="ade-li"><a href="' + l.rootUrl.ade + (h ? "center/#/index/": "center/#/user/") + '">有赚</a><i class="drop-icon footer-pic"></i><span class="seperate-line"></span>', "<ul>", '<div class="white-line"></div>', '<li><a class="tiepian-a zmb-nav-link" href="###"><i class="footer-pic tiepian-icon"></i>自媒宝</a><span class="seperate-line"></span><span class="footer-pic new-icon new-header-ade"></span></li>', '<li><a class="jingbiao-a" href="' + l.rootUrl.ade + 'center/#/user/cpm_action" target="_blank"><i class="footer-pic jingbiao-icon"></i>互选广告</a></li>', '<li><a class="xungou-a" href="' + (n >= 100 || -1 == n ? "http://jinshuju.newrank.cn/f/Dxivey": l.rootUrl.ade + "center/#/user/cps_market") + '" target="_blank"><i class="footer-pic xungou-icon"></i>内容分销</a><span class="seperate-line"></span></li>', '<li><a class="toufang-a" href="' + l.rootUrl.main + 'public/about/agency_delivery.html" target="_blank"><i class="footer-pic toufang-icon"></i>代理投放</a></li>', '<li class="ade-my"><a href="' + (h ? l.rootUrl.ade + "center/#/index/": l.rootUrl.ade + "center/#/user/") + '" target="_blank">我的有赚</a></li>', n >= 100 || -1 == n ? "": '<li class="ade-my"><a href="' + l.rootUrl.ade + 'ade/register.html" target="_blank">广告主注册</a></li>', "</ul>", "</li>", "</li>", '            <li class="data-li"><a href="' + l.rootUrl.data + '">有数</a><i class="drop-icon footer-pic"></i><span class="seperate-line"></span>', "<ul>", '<div class="white-line"></div>', '<div class="nav-split-header">数据<div class="pay-bd"><a href="' + l.rootUrl.main + 'account/user/chongzhi.html" target="_blank">￥榜豆充值<i class="footer-pic pay-icon"></i></a></div></div>', '<li><a href="' + l.rootUrl.data + 'mins-monitor.html" target="_blank" class="moniter-a"><i class="footer-pic moniter-icon"></i>分钟级监测</a><span class="seperate-line"></span></li>', '<li><a href="' + l.rootUrl.data + 'dataAcq.html" target="_blank" class="data-acq-a"><i class="footer-pic data-acq-icon"></i>公众号回采</a></li>', '<li><a href="' + l.rootUrl.data + 'readNum.html" target="_blank" class="read-a"><i class="footer-pic read-icon"></i>阅读数更新</a><span class="seperate-line"></span></li>', '<li><a href="' + l.rootUrl.data + 'articleSearch.html" target="_blank" class="article-a"><i class="footer-pic article-icon"></i>文章搜索</a></li>', '<li><a href="' + l.rootUrl.data + 'trend.html" target="_blank" class="trend-a"><i class="footer-pic trend-icon"></i>趋势查询</a><span class="seperate-line"></span></li>', '<li><a href="' + l.rootUrl.data + 'customList.html" target="_blank" class="bd-a"><i class="footer-pic bd-icon"></i>自定义榜单</a></li>', '<li><a href="' + l.rootUrl.data + 'dataWebo.html" target="_blank" class="weibo-acq-a"><i class="footer-pic weibo-acq-icon"></i>微博回采</a><span class="seperate-line"></span></li>', '<li><a href="' + l.rootUrl.data + 'delArticle.html" target="_blank" class="del-article-a"><i class="footer-pic del-article-icon"></i>删文检测</a><span class="footer-pic new-icon new-header-compare"></span></li>', '<li class="history-li"><a href="' + l.rootUrl.data + 'history.html" target="_blank" class="history-a">查询记录</a></li>', '<div class="nav-split-header">工具</div>', '<li><a href="' + l.rootUrl.data + 'fansCompared.html" target="_blank" class="fans-compare-a"><i class="fans-compare-icon"></i>粉丝对比</a><span class="seperate-line"></span><span class="footer-pic new-icon new-header-compare"></span></li>', '<li><a href="' + l.rootUrl.api + '" target="_blank" class="api-data-a"><i class="footer-pic api-data-icon"></i>数据API</a></li>', '<li class="num-search"><a href="' + l.rootUrl.data + 'searchInaccount.html" target="_blank" class="num-search-a"><i class="footer-pic num-search-icon"></i>号内搜</a><span class="seperate-line"></span></li>', '<li><a href="' + l.rootUrl.data + 'logowall.html" target="_blank" class="logo-wall-a"><i class="footer-pic logo-wall-icon"></i>Logo墙</a></li>', "</ul>", "</li>", '<li class="service-li"><a href="' + s + '#service">有服务</a><i class="drop-icon footer-pic"></i><span class="seperate-line"></span>', "<ul>", '<div class="white-line"></div>', '<li><a href="' + l.rootUrl.ade + 'bocai/index.html" target="_blank">内容电商</a><span class="seperate-line"></span>', '<a href="' + l.rootUrl.services + 'trade/#/" target="_blank">公号交易</a><span class="seperate-line"></span>', '<a href="' + l.rootUrl.ade + 'mp/rise.html" target="_blank">公号涨粉</a><span class="seperate-line"></span>', '<a href="' + l.rootUrl.accelerator + '" target="_blank">投资孵化</a>', "</li>", "<li>", '<a href="' + l.rootUrl.copyright + '" target="_blank">版权交易</a><span class="seperate-line"></span>', '<a href="' + l.rootUrl.rong + '" target="_blank">新媒体招聘</a><span class="seperate-line"></span>', '<a href="' + s + '#service">更多服务</a>', "</li>", "</ul>", "</li>", '<li class="mini-li"><a href="' + l.rootUrl.mini + '">小程序</a><i class="drop-icon footer-pic"></i><span class="seperate-line"></span>', "<ul>", '<div class="white-line"></div>', '<li><a href="' + l.rootUrl.mini + 'miniFind_intro" target="_blank">量子查查</a><span class="seperate-line"></span>', '<a href="' + l.rootUrl.main + 'public/news.html#mini" target="_blank">行业快讯</a><span class="seperate-line"></span>', '<a href="' + l.rootUrl.mini + 'prom_collection" target="_blank">小程序推广</a><span class="seperate-line"></span>', '<a href="' + l.rootUrl.mini + 'flow_collection" target="_blank">流量募集</a></li>', '<li><div class="mini-rong"><div class="mini-rong-item"><div class="mini-rong-free">有容<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAaCAYAAACgoey0AAADuElEQVRIia3We5DNZRgH8M8e20arkrLNRnYiQ1gS1UqlyR9aI2rqD7q65DJDNU0zYlSki7TTdGF05Y+aUE3UYEajQXILjcukTDd0sUkXWonatf3x/M7usbZ1qO/MmXd+7/uc5/vc3zenerz/gjMwDqNwG97P9o+pkyTMw734GhNRif0Z560xFqf8X8Qp3ILteBaNMQntsC6RGZicl+G8enTkIv9EiPtiI95AS8xAG0zBXxiDB1CRyBRjVx2j78BX+DI3C8IemIZrUY25eEiEOQeD8Sjaolx4uayOjgF4Ap3wO6Y05PGFmIf1CSn8huYYLorpE8zB+ZiJbnV0XI3VeC/R90xi4NM59VT1qSJkE0URHQ/bcb0IYRrFeBL9UIXXMBnfpgXqetxO5PGRLEmhgwh1fsbeuyhN1i4Yht24AWfVJS7GWnTOkjATg7AJS0ULDcWluBGfJetWLMDdRGkTeVuIs0+CNI12otrzsDLZuwpPoUT0+my8QK3HZSjKmiLViL5TGbGcfmXk5KRPTkN/Ub0LEwNKRMi7iqLcmyZuK/ore3QZxOH9vNqHlt3pPjTz9EUR1v6ionupDXkNchPS4/dzy+70GMZ362nWmkZ5DFnM5jlsej1Tshk+xwTRRmmU4G/RglLocxRBTiqU1pjWOIj6PMya52lfypmt6DmGt4ewcRalZZxemKllbgZpRxHqtaixMIX2NeJtrmHkClqXRN5atGf4UjoOZMdHdL458rv6OT6YxMgP4/fDRirKM4k7oBVmYYuY3+tEW4V/1eNVIaV0GgWdmD8ilBRcxF3LmDeYg78ydgNzB7NtPhf05nAFP26luorq6rqJ2YumaJKEfWLidY1gCnvAjqQDKsrJa0p+AdsWUNg1CNZM5/JRDJhBr3v4aRtHKusjhRbYh5FigCzIJE17/DEuA3cu4ucvKLqClWV8s4LRq5jZk0P7KLyY3LwosIbxphgif/6bQAqrar4W30e325l9HZ++w8FfoqB6j4vz8s3ZkBK5zcd00UbH3MuNJl9pD0YjctmkOYXF7Ezs2b2J7zdQeTgbQiK/O/GWuJ3+wCvJepTHW7CoZmf541wyhPxz4vtIFYcyXzXHRQtxM+XiQdFOe+oKpa/FImwWzU/TczlwjGy2qMTLeEw8DOpFzOqp1btwk3jC/BfSJWImj2mItJZ4Qg7xXOkrGeIniC3i/i1VZyY3TFyLFaLvXpL2vmHsxf3iXbYkWyupzXF9KMKtYpZ3RIEY8rvFpb9YVO6BEyFM4x9QNwNqMfLZngAAAABJRU5ErkJggg=="/></div>', '<div class="mini-rong-content">为自媒体免费生成内容小程序</div></div>', '<div class="mini-rong-item"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADwAAAAxCAYAAACGYsqsAAAHm0lEQVRogeWaW2xcVxWGv3OZOePxJb5NxnHiJI7bpk3Vqg9IqBEPEW6FBUgIeEwr0UrQKhVIIEPfSNInJEZIICitEIUH1DdUcYkYAeYpShUVFNEoF8ex4zhOJuOxx56b58ycs/fhYXtkx52rx9NOwi8dzTlLa69Z/5y1115r79HOnDnDw4hXvzVZuv0q8BvAA14Fou/9PlJxnN5yz1qP94AhYB/wTi3lR4FwQ3gUCL8C3AdiwOu1lM2Wu9N6nEOFc114aAlXS0zV0DTh06dPN2uiKZw9e7Yh/UdhDjeEpt7wxPhk5OL53A8AbZf82YkPHvB+dCryUj36zYb09z/3fFAzjM+ML1KifXQhdxKoi3CzIa1/lmQB9AYZVHzDb5yaLCv/1ds7y47tgv+7pFWL8NeAu0Ac+Hrr3Wk9aiWtd4Hwxv3PgQ9qGVxZdrm74GDnJYGAzv6DPgZC7VPf1PLEq3BfFskVl1szBQbDEN4Hti25dbOABwy2CelaIf0KcBu4BXynlrG7Cw6hMHR1g2FCZxeEhpS8XVDrZ48Ch+s1Zucle4celAU6lBzg4vlcQ859/gudDenXg4qEd7L8BDp0bFvS2bUps/NKDq0h0Ch2dVnaP+IjcR+yGRAu5LKQuK/k7YJdzST9gyYeas4uxSSBDp3RxzazdK2Q/jQiYNdT58CgycBgebOPXEg/DGiPxbEBpNYEqyuCTFpQKHgIV5UHE+OTl4ALqOLoX9GpiCw3/qEhvJYULMwX0XVVxITCFoEODcPQEMLDznvPpVPiuZWEeyqXlVcmxiffjE5Fzm230/aEPQ/mZwukU5KDh/0EO3UScYf52QL5dQ8hPHx+DcvS6Os3efzJAOs5+fTCfPGvE+OT7wLfi05FiiV7bT2HPQ9uXLVxHI9jzwZIpwSXL62TzTr09EoOHvEYOwoHDnn09ErS6SKXL62TTgmOPRugb8B4DfjTxPikv2SzrQnPzxbQNBgds5i+YpPNOIyMwuBe6AhuNv+6rp4H98LIKGQzDtNXbEbHLPr6jQnglyWbbUt4LSlIpyRHnrC4cc3Gb0nCw2AYn9SVEpyiujQgPAw+v+TGNZsjT1gEOvRvT4xPfgVaMId3q7hYmC9y6Iif2KIDSPoHy+vlshC/p+51HYSAzm4YGob4Pcm9RYdDR/xMX7F/OjE++Tez0lZOLVSqtXejuEitCXQdgkGd2WmbA4fL60kJSzEIdsHeIUU4k1ayYkGF+OJth6F9Pjq79KdyWfnFtgzp5LLLQMhkJeES7CofxqBISQkDoc353N0DlgVCbrSo3bCScEvl7TdKhAPAWeAm4KKa/dLlbsjPAh0tY7kF2YykZ4/BalI80Hlth7exJbF131RKcN3NH6CzC1aTgu4eA+B5EwgC/wCOV7BrAGPAj4EXgBeB9UpONNrzlv1CUyMQ0MjnK89d2CRVIp7LQHJFPfs3GjSfD/J5id/SAA6bwJtUJrsdxzf0Kx4o7cYcvng+h2FqSOGhbwlnz1NzVLjQN7BJWG4UkWurSjY8AlppyTJAuB4+nwbQqwMnG/Tn5ebo1IZhagjXw/RpSLEpjy2q/rpgq2dtG+H9B9VlBTbHSAE+vyo/gZQJjDToT1X9nYb01siwLA3b9rAsnWJRYPrUW82vw74DENxQ3f6Gy8EpgmXpFGwPYN4E7qDmaL24U6/jO0VXt04mJdjTa5BOC4Kdan31+TfJAmh1nPLksrCn1yCTEgAf6sD7Dfrzhwb1G0b/gMlywiUUNslmVNYFcB11lZDLqk+zwg6S60ImA6Gwsgd8oAM/QfWR9eDDDf2WYk+fgRQqu4aHfCzH1ds1TLhzG5bjEI+pAqOzS6275bAch/CQj/V1SS4rrwP/1FFLzAvAW8AsILaNExvyt4BxqixJu4mRUT/zs0X2j/jRdYNkAkYOQV8/OK5KRn0Dqm4uh5Ul0HWD/SN+bs8VAX4UnYrIUi2dRy01Lfv/QiLuMjdTaHjc3EyBx5+0mL5qsxSXhPZCb39lfeHC8hJ4ns7RYxazMzZ2Xv42OhX5C4D5aR1/3r/nEB6GYLD+MZ4H8ZjL3E2Po08HiC063Jl36O5R9bPfUmWnEKrMXM+qdTo05GP4gI+5mQJrSfF34FTJZrPdkijYnmEFqqfLtaTAdSVd3Y1/wb4DkIgLrn5sMzrmZ2jYx1LcIb0mKdgS1/UwTQ2rQ6e3z2D0MRM7L7n6sY2dl78DXt+649Es4e9evZx/+9gzHVQi7XkwP1eoWiJWg6apTiiXlczesPFbOgMhk8Njfnx+DZ9Pw3E8nKJHOiWYuW6TzcgZYDI6FfnzdnvGiRMnduYJcPPWhX+PHjyeSa64X+rrNzB9nyQ9N1NAiuo1cT3w+2FPH+i6IhaPudy747B42yEec1hNuqRWBQXb+zXw5ehU5Ho5O01vAIwd5Wez017x2mX7F089E9DQIJOSFIseyWUXISTDjdZyVRDsfLD4KCFxH9IpzgFvRKciFY92KxKeGC+7MfAi0LtVMDsNQLxY9N7573/yr7Fl28gwVa+63nwDVRUFG9IpLgMnq5GFxg7ENeCbQJVFgT9uuT8kXBaWYrUP0ncBAvhhdCqSqqX4P21J/cOxG5x+AAAAAElFTkSuQmCC"/></div>', "</div></li>", "</ul>", "</li>", "<li>", '           <i class="footer-pic"></i>       </a></li>', "</ul>", '<div class="new-header-user"></div>', "    </div>"];
                        if (e("#header").html(j.join("")).addClass("new-header"), e("div.mini-rong").unbind("click").bind("click",
                                function(a) {
                                    a.stopImmediatePropagation(),
                                        window.open("http://urlort.cn/hJ553b")
                                }), -999 == a) {
                            var k = ['<li style="margin-right:10px;"><a href="' + l.rootUrl.main + 'public/about/plugIn_introduce.html" target="_blank"><i class="footer-pic plugin-icon"></i>小助手·榜口令</a></li>', '<li class="login-reg-li">', '<a class="new-header-login unlogin" href="javascript:;"><i class="footer-pic new-header-login"></i>登录 / 注册</a>', "</li>"];
                            e(".new-header-user").append(k.join("")),
                                e(".new-header-login").click(function() {
                                    window.location.href = l.rootUrl.main + "public/login/login.html?back=" + escape(l.rootUrl.main)
                                }),
                                e(".bang-custom").click(function() {
                                    f.showLoginTip("定制榜单")
                                }),
                                e(".img-import").click(function() {
                                    i.shownewLogin()
                                }),
                                e(".nav-to-edit-qd").click(function(a) {
                                    a.stopImmediatePropagation(),
                                        window.open(l.rootUrl.edit + "?menu=4996fsd44-9f35-11e5-ad8a-382c4abc606b")
                                }),
                                e("a.zmb-nav-link").unbind("click").bind("click",
                                    function(a) {
                                        a.preventDefault(),
                                            window.open(l.rootUrl.ade + "center/#/index/")
                                    })
                        } else {
                            var m, o = t.admin,
                                p = t.roles || "";
                            parseInt(o) > 0 && (m = "true"),
                            t.name && -1 != t.name.lastIndexOf("@newrank.cn") && -1 == t.name.indexOf("search") && (m = "true");
                            var q = p && p.indexOf("1041") > -1 ? '<span class="seperate-line"></span><a href="' + l.rootUrl.data + 'order.html" target="_blank">微信粉丝画像</a>': "",
                                r = p ? p.indexOf("1041") > -1 ? " 256px": "202px": ""; (n >= 100 || -1 == n) && e(".ade-my a").attr("href", l.rootUrl.ade + "center/#/adv/");
                            var x = !p.contain("601"),
                                y = t.dd_rols ? '<span class="seperate-line"></span><a href="https://cd1.newrank.cn/v/home" target="_blank">dataN</a>': "",
                                k = ['<li style="margin-right:10px;"><a href="' + l.rootUrl.main + 'public/about/plugIn_introduce.html" target="_blank"><i class="footer-pic plugin-icon"></i>小助手·榜口令</a></li>', "true" == m ? '<li class="internal-channel-li"><a href="' + l.rootUrl.admin + '" target="_blank">内部通道</a><i class="drop-icon footer-pic"></i><span class="seperate-line nav-line"></span><ul style="width: ' + r + '"><div class="white-line"></div><li><a href="' + l.rootUrl.admin + '" target="_blank">后台管理</a><span class="seperate-line"></span><a href="' + l.rootUrl.main + 'public/super/account.html" target="_blank">优质账号</a><span class="seperate-line"></span><a href="' + l.rootUrl.zz + '" target="_blank">找找</a>' + (q ? y: "") + '</li><li style="margin-top: -5px;"><a href="' + l.rootUrl.main + (x ? "public/schedule/schedule-statistics.html": "public/schedule/schedule-project.html") + '"  target="_blank">广告代理</a><span class="seperate-line"></span><a href="http://mail.newrank.cn/" target="_blank">企业邮箱</a>' + q + (q ? "": y) + "</li></ul></li>": "", "<li>", '<div id="new-header-userbox" class="new-header-userbox">', '<a class="new-header-usertxt" href="javascript:;"><i class="footer-pic user-name"></i><span id="userName" style="display: inline-block;"></span><i class="footer-pic drop-icon"></i></a>', '<ul class="user-info-ul">', '<div class="white-line"></div>', '<div class="top-div"><i class="footer-pic user-head-icon"></i><div class="right-user-info"><p class="email"></p><p class="nav-user-phone"></p></div></div>', '<li class="zl-li"><a href="' + l.rootUrl.main + 'account/user/user.html" target="_blank"><i class="footer-pic zl-icon"></i>我的资料</a><span class="seperate-line"></span></li>', '<li class="bd-li"><a href="' + l.rootUrl.main + 'account/user/rankcoin.html" target="_blank"><i class="footer-pic bd-icon"></i>我的榜豆</a></li>', '<li class="qx-li"><a href="' + l.rootUrl.main + 'account/user/right.html" target="_blank"><i class="footer-pic qx-icon"></i>我的权限</a><span class="seperate-line"></span></li>', '<li class="js-li">' + (n >= 100 || -1 == n ? '<a href="' + l.rootUrl.ade + 'ade/invoice.html" target="_blank"><i class="footer-pic js-icon"></i>发票管理</a>': '<a href="' + l.rootUrl.ade + 'center/#/user/finance_cash" target="_blank"><i class="footer-pic js-icon"></i>结算管理</a></li>'), '<li class="gzh-li"><a href="' + l.rootUrl.main + 'account/user/box.html" target="_blank"><i class="footer-pic gzh-icon"></i>收藏公众号</a><span class="seperate-line"></span></li>', '<li class="zdy-bd-li"><a href="' + l.rootUrl.main + 'account/user/ranklist.html" target="_blank"><i class="footer-pic zdy-bd-icon"></i>自定义榜单</a></li>', '<li class="xgsh-li"><a href="' + l.rootUrl.main + 'account/user/typerecord.html" target="_blank"><i class="footer-pic xgsh-icon"></i>修改审核</a></li>', '<li class="ggzx-li">' + (n >= 100 || -1 == n ? '<a href="' + l.rootUrl.ade + 'center/#/adv/" target="_blank"><i class="footer-pic ggzx-icon"></i>我的有赚</a>': '<a href="' + l.rootUrl.ade + 'center/#/user/" target="_blank"><i class="footer-pic ggzx-icon"></i>我的有赚</a>') + "</li>", '<li class="new-header-quit">退出</li>', "</ul>", "</li>"];
                            e(".new-header-user").append(k.join(""));
                            var z = t.nick_name || t.nr_name;
                            if (e("#userName").html(z), e(".right-user-info .email").html(z), e(".user-info-ul .white-line").css("width", e(".new-header-userbox").width() + 2 + "px"), t.headimgurl && e(".user-head-icon").css({
                                    background: "url(" + t.headimgurl + ") no-repeat",
                                    "background-size": "cover"
                                }), t.phone_login) {
                                var A = t.phone_login.substring(0, 3) + "****" + t.phone_login.substring(7, 11);
                                e(".nav-user-phone").html('手机号：<span class="phone">' + A + "</span>")
                            } else e(".nav-user-phone").html('<span  class="bind-phone">绑定手机</span>'),
                                e(".nav-user-phone span.bind-phone").click(function(a) {
                                    a.stopPropagation(),
                                        window.open(l.rootUrl.main + "account/user/user.html?isBp=true")
                                });
                            u = c ? a.box.length: a.box,
                            u > 99 && (u = "N"),
                                e("#boxNum").html(u),
                                v = c ? a.rank.length: a.rank,
                                e("a.zmb-nav-link").unbind("click").bind("click",
                                    function(a) {
                                        a.preventDefault();
                                        var b = "",
                                            c = "";
                                        b = n >= 100 || -1 == n ? e("<a href='" + l.rootUrl.ade + "center/#/index/' target='_blank'></a>").get(0) : e("<a href='" + l.rootUrl.ade + "center/#/user/cpc_action/wx_aticle' target='_blank'></a>").get(0),
                                            c = document.createEvent("MouseEvents"),
                                            c.initEvent("click", !0, !0),
                                            b.dispatchEvent(c),
                                            b.remove()
                                    }),
                                e(".new-header-usertxt,.top-div").click(function(a) {
                                    a.stopPropagation(),
                                        window.location.href = s + "account/user/user.html"
                                }),
                                e(".new-header-quit").on("click",
                                    function() {
                                        g.removeCookie("token"),
                                            window.location.href = "/",
                                            window.location.href = s
                                    }),
                                e(".bang-custom").click(function() {
                                    window.open(l.rootUrl.main + "/account/user/ranklist.html")
                                }),
                                e(".img-import").click(function() {
                                    window.open(l.rootUrl.edit + "?menu=9ca47e67-a4a3-11e5-ad8a-382c4abc606b&type=0")
                                }),
                                e(".nav-to-edit-qd").click(function(a) {
                                    a.stopImmediatePropagation(),
                                        window.open(l.rootUrl.edit + "?menu=4996fsd44-9f35-11e5-ad8a-382c4abc606b")
                                })
                        }
                        e(".new-header-code").on("mouseover",
                            function() {
                                e(".new-header-cnav").show()
                            }),
                            e(".new-header-cnav").on("mouseout",
                                function() {
                                    e(".new-header-cnav").hide()
                                }),
                        e.isFunction(b) && b(a),
                            w = !0
                    })
                })(),
                    e(".new-header-navcon").on({
                        mouseenter: function() {
                            e(".new-header-about").addClass("new-hover"),
                                e("ul.new-header-dnav").show(),
                                e(this).find("i").css({
                                    "background-position": "-22px -249px"
                                })
                        },
                        mouseleave: function() {
                            e(".new-header-about").removeClass("new-hover"),
                                e("ul.new-header-dnav").hide(),
                                e(this).find("i").css({
                                    "background-position": "3px -249px"
                                })
                        }
                    })
                },
                E = ['<div class="new_footer_main">', '<div class="new_footer_left">', "   <span>上海看榜信息科技有限公司</span>", "   <span>复旦大学新闻学院提供全方位学术支持</span>", "</div>", '<div id="mainbox" class="new_footer_right">', "   <span>©" + (new Date).getFullYear() + " NEWRANK 沪ICP备14042332号-2</span>", "</div>", "</div>"];
            e("#footer").html(E.join("")).addClass("new_footer ft_fixed"),
                e("#mainbox").hover(function() {
                        e(".new_footer_mailbox").show()
                    },
                    function() {
                        e(".new_footer_mailbox").hide()
                    }),
                e("#links").hover(function() {
                        e(".new_footer_links").show()
                    },
                    function() {
                        e(".new_footer_links").hide()
                    });
            var F = {},
                G = {};
            return F.autoComplete = function(a, b) {
                var c = a.id;
                e("#" + c).autocomplete({
                    autoFocus: !1,
                    delay: 200,
                    minLength: 0,
                    source: [],
                    isChn: function(a) {
                        return /^[\u4e00-\u9fa5]+(\w|[\u4e00-\u9fa5])+$/i.test(a)
                    },
                    sortArr: function(a, b, c) {
                        return new Function("a", "b", "return a." + a + " > b." + a + " ? 1 : a." + a + "<b." + a + " ? -1: a." + b + ">b." + b + " ? 1 : a." + b + "<b." + b + " ? -1:  a." + c + ">b." + c + " ? -1 : a." + c + "<b." + c + " ? 1:0")
                    },
                    css: function(a) {
                        a.css({
                            margin: "-4px -2px",
                            "font-size": "12px",
                            "z-index": 101
                        })
                    },
                    select: function(a, c) {
                        a.stopPropagation();
                        var d = (e(this), c.item.value);
                        return d = d.substring(d.indexOf("(") + 1, d.indexOf(")")),
                            b(encodeURIComponent(d)),
                            !1
                    },
                    focus: function(a, b) {
                        var c = b.item.value;
                        try {
                            b.item.value = e(e(c)[0]).text()
                        } catch(d) {}
                    },
                    search: function(a, b) {
                        a.stopPropagation();
                        var c = e(this),
                            d = c.autocomplete("option"),
                            f = d.isChn,
                            g = d.sortArr,
                            h = e.trim(c.val());
                        f(h) && m.account.getAutocompleteAccount(h,
                            function(a) {
                                var b = [],
                                    d = [];
                                e.each(a,
                                    function() {
                                        var a = this,
                                            c = a.name,
                                            d = a.weekLog1pmark ? parseFloat(a.weekLog1pmark).toFixed(1) : "--/-",
                                            e = ['<div style="float: left">', c + "(" + a.account + ")", "</div>", '<div style="color: #fd8c25; float: right; ">', "<span>" + d + "</span>", "</div >"];
                                        b.push({
                                            name: e.join(""),
                                            count: c.indexOf(h),
                                            length: c.length,
                                            weekLog1pmark: a.weekLog1pmark ? a.weekLog1pmark: 0
                                        })
                                    }),
                                    b.sort(g("count", "length", "weekLog1pmark")),
                                    e.each(b,
                                        function() {
                                            d.push(this.name)
                                        }),
                                    c.autocomplete("option", "source", d)
                            })
                    }
                })
            },
                F.common4isDefault = function(a) {
                    var b = !1;
                    return "请输入微信公众号ID、名称（如：新榜）" != a && "输入投放关键词" != a && "请输入所搜资讯关键字" != a || (b = !0),
                        b
                },
                F.headerSimpleSearch = function(a, b) {
                    var c = a.defaultText,
                        d = (a.hasAuto, a.hasFocus),
                        f = e("#simple_search"),
                        g = e("#simple_del"),
                        h = e("#simple_btn_search");
                    f.focus(function() {
                        var a = f.val(),
                            b = "block";
                        "" == a ? f.css("color", "#333") : a == c && f.val("").css("color", "#333"),
                            g.css("display", b),
                            h.css("display", b)
                    }),
                        f.blur(function() {
                            "" == f.val() && (f.val(c).css("color", "#999999"), g.hide())
                        }),
                        h.click(function() {
                            var a = f.val(); ! F.common4isDefault(a) && String.HasText(a) ? b(a) : g.hide()
                        }),
                        f.keyup(function(a) {
                            var d = a.which,
                                h = e.trim(e(this).val()),
                                i = "none";
                            F.common4isDefault(h) && (h = "", f.val("")),
                            h.length > 0 && (i = "block"),
                                g.css("display", i),
                                z = e("div.header-search-chosen span.active").attr("bind-type"),
                            13 == d && (h != c && "" != h && b(h), e("ul.ui-autocomplete").css("display", "none"))
                        }),
                        g.on("click",
                            function() {
                                f.val(c).css("color", "#999999"),
                                    g.css("display", "none"),
                                    f.focus()
                            }),
                        String.HasText(d) && 0 == d ? f.focusout() : f.focus(),
                        e("#header_simple_search").find("div.simple-search-logo").on("click",
                            function() {
                                window.location.href = window.relativeUrl
                            })
                },
                F.header4Search = function(a, b) {
                    var c = a.defaultText,
                        d = a.hasAuto,
                        f = a.hasFocus,
                        g = e("#" + id4TxtAccount),
                        h = e("#" + id4SearchDel),
                        i = e("#btn_search"),
                        j = e("#" + id4huntBtn);
                    d && F.autoComplete({
                            id: id4TxtAccount
                        },
                        b),
                        g.focus(function() {
                            var a = g.val(),
                                b = "block";
                            i.hide(),
                                "" == a ? g.css("color", "#333") : a == c && g.val("").css("color", "#333"),
                                h.css("display", b),
                                j.css("display", b)
                        }),
                        g.blur(function() {
                            "" == g.val() && (g.val(c).css("color", "#999999"), h.hide())
                        }),
                        j.click(function() {
                            var a = g.val(); ! F.common4isDefault(a) && String.HasText(a) && b(a)
                        }),
                        g.keyup(function(a) {
                            var d = a.which,
                                f = e.trim(e(this).val()),
                                i = "none";
                            F.common4isDefault(f) && (f = "", g.val("")),
                            f.length > 0 && (i = "block"),
                                h.css("display", i),
                                z = e("div.header-search-chosen span.active").attr("bind-type"),
                            13 == d && (f != c && "" != f && b(f), e("ul.ui-autocomplete").css("display", "none"))
                        }),
                        h.on("click",
                            function() {
                                g.val(c).css("color", "#999999"),
                                    h.css("display", "none"),
                                    i.show(),
                                    g.focus()
                            }),
                        String.HasText(f) && 0 == f ? g.focusout() : g.focus(),
                        e("#header-search").find("div.header-search-logo").on("click",
                            function() {
                                window.location.href = window.relativeUrl
                            })
                },
                F.getNavIconHref = function(a) {
                    var b, c, d, e, f = function() {
                        return t ? t.user_type >= 100 || -1 === t.user_type ? 3 : 2 : 1
                    };
                    r.getIcon(function(g) {
                        if (g.length > 0) {
                            var h = g[0],
                                i = h.click_url || "javascript:void(0)";
                            c = h.img_url,
                                e = h.img_name,
                                b = 1 === f ? i: 2 == f ? h.media_click_url || i: h.ade_click_url || i,
                                d = "block"
                        } else d = "none";
                        a({
                            imgHref: c,
                            alt: e,
                            aHref: b,
                            display: d
                        })
                    })
                },
                F.checkDefaultText = function(a) {
                    var b = !1;
                    if ("" == a) b = !0;
                    else for (var c in B) a == B[c] && (b = !0);
                    return b
                },
                F.bindSpanClick = function() {
                    e("div.header-search-chosen").delegate("span:not(.active)", "click",
                        function() {
                            var a = e.trim(e("#" + id4TxtAccount).val()),
                                b = e(this);
                            for (var c in C) b.text() == C[c].title && C[c].clickFunction(a)
                        })
                },
                F.bindSearchFunction = function() {
                    var a;
                    if (void 0 == z || "undefined" == z) a = C.account.searchFunction;
                    else for (var b in C) z == C[b].bindType && (a = C[b].searchFunction);
                    return a
                },
                F.oldAddClass = function() {
                    e(".header-search-chosen span").each(function() {
                        void 0 == z || "undefined" == z ? "account" == e(this).attr("bind-type") && e(this).addClass("active") : e(this).attr("bind-type") == z && e(this).addClass("active")
                    }),
                        A && "" !== A ? e("#txt_account").val(A) : void 0 != z && "undefined" != z ? e("#txt_account").val(C[z].text) : e("#txt_account").val(B.normal)
                },
                F.bindLogoClick = function() {
                    e("#header-search").find("div.logo-click").bind("click",
                        function() {
                            z && C[z].logoFunction()
                        })
                },
                F.bindKeySearch = function() {
                    e("#txt_account").unbind("keyup").bind("keyup",
                        function(a) {
                            var b = e(this),
                                c = b.val();
                            13 == a.which && "" != c && (window.location.href = s + "public/info/search.html?value=" + encodeURIComponent(c) + "&isBind=" + y)
                        })
                },
                F.Global = function() {
                    id4TxtAccount = "txt_account",
                        id4SearchDel = "header-search-idel",
                        id4huntBtn = "header-search-btn"
                },
                F.Global(),
                G.header4Search = function(a) {
                    var b = (a.hasData, a.isHost),
                        c = a.title,
                        d = [{
                            type: "toAde",
                            name: "广告",
                            bind: !1
                        },
                            {
                                type: "xungou",
                                name: "可询购",
                                bind: !0
                            },
                            {
                                type: "report",
                                name: "报告",
                                bind: !1
                            },
                            {
                                type: "media",
                                name: "资讯",
                                bind: !1
                            },
                            {
                                type: "account",
                                name: "公众号",
                                bind: !1
                            },
                            {
                                type: "trend",
                                name: "趋势",
                                bind: !1
                            },
                            {
                                type: "article",
                                name: "文章",
                                bind: !1
                            },
                            {
                                type: "copyright",
                                name: "正版转载",
                                bind: !1
                            }],
                        e = new o;
                    e.append('<div class="header-search-inner clear">'),
                        e.append(' <div class="' + ("资讯" == c ? "header-search-logo-zixun": "header-search-logo") + ' logo-click"></div>'),
                        e.append(' <div class="header-search-content" style="border: 1px solid rgb(230, 230, 230); box-shadow: rgb(255, 255, 255) 0px 0px 0px;">'),
                        e.append('  <div class="header-search-chosen clear">');
                    for (var f = 0; f < d.length; f++) {
                        var g = d[f];
                        "可询购" == g.name ? e.append(b ? '<span bind-type="' + g.type + '" class="' + (g.name == c ? "active": "") + '">可询购</span>': "") : e.append('\t<span bind-type="' + g.type + '" class="' + (g.name == c ? "active": "") + '">' + g.name + "</span>")
                    }
                    return e.append("  </div>"),
                        e.append('   <div class="header-search-inputbox"> '),
                        e.append('    <input id="txt_account" type="text" class="header-search-itext" value="' + a.defaultText + '" style="color: #999999;" /> '),
                        e.append('    <input id="btn_search" type="submit" class="header-search-ibtn" value="" style="display:none" /> '),
                        e.append('    <input id="header-search-idel" type="submit" class="header-search-idel" value=""  style="display:none"/>'),
                        e.append("  </div>"),
                        e.append('   <div id="header-search-btn"  class="cursor-p" style="border:1px solid rgb(120, 120, 120); width: 60px; height: 33px; color: white; background-color: rgb(120, 120, 120); z-index: 3; position: absolute; margin-left:488px;"><div style="margin-top:6px;text-align: center;font-size: 14px;">搜索</div></div>'),
                        e.append(" </div>"),
                        e.append('<div class="header-icon"></div>'),
                        e.append("</div>"),
                        e.toString()
                },
                G.headerSimpleSearch = function(a) {
                    var b = new o;
                    return b.append('<div id="header_simple_search" class="header-simple-search common-nav-hide">'),
                        b.append('<div class="simple-search-box clear">'),
                        b.append('<div class="simple-search-logo"></div>'),
                        b.append('<div class="simple-search-txt"><span style="margin-right:16px;border-right:1px solid #aaa;"></span>搜公众号</div>'),
                        b.append('<div class="simple-search-inputbox clear">'),
                        b.append('<input id="simple_search" type="text" class="simple-search-itext ui-autocomplete-input" value="请输入微信公众号ID、名称（如：新榜）" style="color: rgb(153, 153, 153);" autocomplete="off">'),
                        b.append('<input id="simple_del" type="submit" class="simple-search-idel" value="" style="display: block;">'),
                        b.append('<div id="simple_btn_search" class="simple-btn-search cursor-p">搜索</div>'),
                        b.append("</div>"),
                    a.user && a.user.nr_id && (b.append('<div class="simple-search-user">'), b.append('<a href="' + l.rootUrl.main + 'account/user/user.html"><i class="footer-pic user-name-spe"></i><span style="display: inline-block;">' + (a.user.nick_name ? a.user.nick_name: a.user.nr_name) + "</span></a>"), b.append("</div>")),
                        b.append("</div>"),
                        b.append("</div>"),
                        b.toString()
                },
                G.headerAccountSearch = function(a) {
                    var b = new o;
                    return b.append('<div class="header-search-inner clear">'),
                        b.append(' <div class="header-search-new-logo logo-click"></div>'),
                        b.append(' <div class="header-search-box" style="border: 1px solid rgb(230, 230, 230); box-shadow: rgb(255, 255, 255) 0px 0px 0px;">'),
                        b.append('   <div class="header-search-inputbox">'),
                        b.append('    <input id="txt_account" type="text" class="header-search-itext" value="' + a.defaultText + '" style="color: #999999;" /> '),
                        b.append('    <input id="btn_search" type="submit" class="header-search-ibtn" value="" style="display:none" /> '),
                        b.append('    <input id="header-search-idel" type="submit" class="header-search-idel" value=""  style="display:none"/>'),
                        b.append("  </div>"),
                        b.append('   <div id="header-search-btn"  class="cursor-p" style="border:1px solid rgb(120, 120, 120); width: 60px; height: 32px; color: white; background-color: rgb(120, 120, 120); z-index: 3; position: absolute; margin-left:488px;"><div style="margin-top: 5px;text-align: center;font-size: 14px;">搜索</div></div>'),
                        b.append(" </div>"),
                        b.append("</div>"),
                        b.toString()
                },
                {
                    setNew: function() {
                        d = !1
                    },
                    setBindType: function(a) {
                        z = a
                    },
                    showLoginDialog: function() {
                        i.shownewLogin()
                    },
                    init: function(a) {
                        D(q.getUser, a, !1),
                            j.init()
                    },
                    getUser: function(a) {
                        q.getUser(a)
                    },
                    new_index_init: function(a) {
                        D(q.getUser, a, !1)
                    },
                    initAccount: function(a) {
                        D(q.info.getUser, a, !0),
                            j.init()
                    },
                    initCopyRight: function() {
                        var a = [d ? '<div class="header-search-old">': '<div class="header-search-inner clear">', '<div class="header-search-copyright"></div>', "</div>"];
                        e("#header-search").html(a.join("")),
                            e(".header-search-copyright").click(function() {
                                window.location.href = s + "public/copyright/content.html"
                            }),
                            j.init()
                    },
                    initZixun: function() {
                        var a = [d ? '<div class="header-search-old">': '<div class="header-search-inner clear">', '<div class="header-search-zixun"></div>', "</div>"];
                        e("#header-search").html(a.join("")),
                            e(".header-search-zixun").click(function() {
                                window.location.href = s + "public/news.html"
                            }),
                            j.init()
                    },
                    initOnlyLogo: function() {
                        var a = [d ? '<div class="header-search-old">': '<div class="header-search-inner clear">', '<div class="header-search-logo"></div>', "</div>"];
                        e("#header-search").html(a.join("")),
                            e(".header-search-logo").click(function() {
                                window.location.href = s
                            }),
                            j.init()
                    },
                    initAuth4Account: function() {
                        var a = [d ? '<div class="header-search-old">': '<div class="header-search-inner clear">', '<div class="header-search-auth4account"></div>', "</div>"];
                        e("#header-search").html(a.join("")),
                            e("div.header-search-auth4account").click(function() {
                                window.location.reload()
                            }),
                            j.init()
                    },
                    initPersonalCenter: function() {
                        var a = [d ? '<div class="header-search-old">': '<div class="header-search-inner clear">', '<div class="header-search-personalcenter"></div>', "</div>"];
                        e("#header-search").html(a.join("")),
                            e(".header-search-personalcenter").click(function() {
                                window.location.href = s + "account/user/user.html"
                            }),
                            j.init()
                    },
                    initCcUpdate: function() {
                        var a = [d ? '<div class="header-search-old">': '<div class="header-search-inner clear">', '<div class="header-search-ccUpdate"></div><div class="to-cc-reprint-side"></div>', "</div>"];
                        e("#header-search").html(a.join("")),
                            e(".header-search-ccUpdate").click(function() {
                                window.location.href = "https://cc.newrank.cn/"
                            }),
                            e(".to-cc-reprint-side").click(function() {
                                window.open(l.rootUrl.copyright + "reprint-side.html")
                            }),
                            j.init()
                    },
                    initNewrankUpdate: function() {
                        var a = [d ? '<div class="header-search-old">': '<div class="header-search-inner clear">', '<div class="header-search-nbUpdate"></div>', '<div class="header-search-right"></div>', "</div>"];
                        e("#header-search").html(a.join("")),
                            e(".header-search-nbUpdate").click(function() {
                                window.location.href = "https://data.newrank.cn/index.html"
                            }),
                            j.init()
                    },
                    initEnterclose: function() {
                        var a = [d ? '<div class="header-search-old">': '<div class="header-search-inner clear">', '<div class="header-search-enterclose"></div>', "</div>"];
                        e("#header-search").html(a.join(""))
                    },
                    initYouxxuan: function(a, b) {
                        this.initSearch(b, !0),
                            j.init()
                    },
                    initMatrix: function() {
                        var a = [d ? '<div class="header-search-old">': '<div class="header-search-inner clear">', '<div class="header-search-matrix"></div>', "</div>"];
                        e("#header-search").html(a.join("")),
                            e(".header-search-copyright").click(function() {
                                window.location.href = s + "public/copyright/content.html"
                            }),
                            j.init()
                    },
                    initSearch: function(a) {
                        this.initNewSearch(a),
                            j.init()
                    },
                    initSearch4pub: function(a, b) {
                        e("#header-search").html(G.header4Search({
                            isHost: a.isHost,
                            hasData: !0,
                            title: a.title,
                            defaultText: B.normal
                        })),
                            j.init(),
                            a.defaultText = B.normal,
                            setTimeout(function() {
                                    F.header4Search(a, b)
                                },
                                300),
                            F.bindSpanClick()
                    },
                    initSimpleSearch: function(a, b) {
                        e("body").append(G.headerSimpleSearch(a.user)),
                            a.defaultText = B.normal,
                            setTimeout(function() {
                                    F.headerSimpleSearch(a, b)
                                },
                                300);
                        var c = "";
                        e(window).scroll(function() {
                            e(this).scrollTop() >= 300 ? (clearTimeout(c), e("#header").addClass("common-nav-hide"), c = setTimeout(function() {
                                    e("#header_simple_search").removeClass("common-nav-hide")
                                },
                                300)) : (clearTimeout(c), e("#header_simple_search").addClass("common-nav-hide"), c = setTimeout(function() {
                                    e("#header").removeClass("common-nav-hide")
                                },
                                300))
                        })
                    },
                    initAccountSearch: function(a, b, c) {
                        a(function(a) {
                            e("#header-search").html(G.headerAccountSearch({
                                isHost: b.isHost,
                                hasData: !0,
                                title: b.title,
                                defaultText: B.normal
                            })),
                                j.init(),
                                b.defaultText = B.normal,
                                setTimeout(function() {
                                        F.header4Search(b, c)
                                    },
                                    300)
                        })
                    },
                    initNew: function(a, b) {
                        e("#header-search").html(G.header4Search({
                            isHost: a.isHost,
                            defaultText: B.ziXun,
                            title: "资讯"
                        })),
                            j.init();
                        var c = function(a) {
                            a == B.ziXun && (a = ""),
                                b(encodeURIComponent(a))
                        };
                        F.header4Search({
                                defaultText: B.ziXun,
                                hasFocus: !1
                            },
                            c),
                            F.bindSpanClick()
                    },
                    initNewSearch: function(a) {
                        void 0 == a && (a = !1);
                        var b = new o,
                            c = "";
                        for (var f in C) {
                            var g = C[f];
                            "可询购" == g.title ? b.append(n >= 100 || -1 == n ? '<span bind-type="' + g.bindType + '" class="' + (g.bindType == z ? "active": "") + '">可询购</span>': "") : b.append('<span bind-type="' + g.bindType + '" class="' + (g.bindType == z ? "active": "") + '">' + g.title + "</span>"),
                                void 0 == z || "undefined" == z ? c = "header-search-logo": z == g.bindType && (c = g.logoClass)
                        }
                        var h = [d ? '<div class="header-search-old">': '<div class="header-search-inner clear">', '<div class="' + c + ' logo-click"></div>', '<div class="header-search-content">', '<div class="header-search-chosen clear">' + b.toString() + "</div>", '<div class="header-search-inputbox">', '    <input id="txt_account" type="text" class="header-search-itext" value="' + ("toAde" == y ? "输入投放关键词": "请输入微信公众号ID、名称（如：新榜）") + '" style="color:#999999">', '   <input id="btn_search" type="submit" class="header-search-ibtn" value="">', '    <input id="header-search-idel" type="submit" class="header-search-idel" value="" style="display:none" />', "</div>", '<div id="header-search-btn"  class="cursor-p" style=" display: none; cursor: pointer;border:1px solid rgb(120, 120, 120);width: 60px; height: 32px; color: white; background-color: rgb(120, 120, 120); z-index: 3; position: absolute; margin-left:488px;"><div style="margin-top: 5px;text-align: center;font-size: 14px;">搜索</div></div>', "</div>", '<div class="nav-icon"></div>', a ? '<a class="nav-icon-5-1 zmb-link" href="###"><p><span>推广盈利 ▪ 自媒宝</span></p><i class="common-pic common-new"></i></a>': "", "</div>"];
                        e("#header-search").html(h.join("")),
                            e("a.zmb-link").unbind("click").bind("click",
                                function(a) {
                                    if (a.preventDefault(), t) {
                                        var b = "",
                                            c = "";
                                        b = n >= 100 || -1 == n ? e("<a href='" + l.rootUrl.ade + "cpm/zmindex.html' target='_blank'></a>").get(0) : e("<a href='" + l.rootUrl.ade + "center/#/user/cpc_action/wx_aticle' target='_blank'></a>").get(0),
                                            c = document.createEvent("MouseEvents"),
                                            c.initEvent("click", !0, !0),
                                            b.dispatchEvent(c),
                                            b.remove()
                                    } else i.shownewLogin()
                                }),
                            F.oldAddClass();
                        var j = F.bindSearchFunction();
                        setTimeout(function() {
                                var a = "";
                                a = "undefined" != z && void 0 != z ? C[z].text: C.account.text,
                                    F.header4Search({
                                            defaultText: a,
                                            hasFocus: !1
                                        },
                                        j)
                            },
                            500),
                            F.bindSpanClick(),
                            F.bindLogoClick(),
                            F.getNavIconHref(function(a) {
                                e("div.nav-icon").append("<a href=" + a.aHref + ' target="_blank" style="display:' + a.display + '" ><img src=' + a.imgHref + " alt=" + a.alt + "></a>")
                            })
                    },
                    initInsideSearch: function() {
                        var a = "请输入关键词",
                            b = [d ? '<div class="header-search-old">': '<div class="header-search-inner clear">', '<div class="header-search-logo-inside"></div>', '<div class="header-search-content-inside">', '<div class="header-search-inputbox">', '    <input id="txt_account" type="text" class="header-search-itext" value="输入关键词" style="color:#999999">', '    <input id="btn_search" type="submit" class="header-search-ibtn-spe header-search-ibtn" value="">', "    </div>", "</div>", '<div class="header-search-inside-highlevel">高级搜索</div>', "</div>"];
                        e("#header-search").html(b.join("")),
                            e(".header-search-logo").click(function() {
                                window.location.href = s
                            }),
                            e(".menu-s-liucheng").click(function() {
                                window.open(s + "account/ade/liucheng.html")
                            }),
                            e(".menu-s-main").on("click",
                                function() {
                                    window.location.href = l.rootUrl.ade + "ade/info.html"
                                }),
                            A && "" !== A && "输入关键词" !== A ? e("#txt_account").val(A).css("color", "#333") : e("#txt_account").val(a).css("color", "#d9d9d9"),
                            e("#txt_account").focus(function() {
                                e(this).parent().parent(".header-search-content-inside").css({
                                    border: "1px solid #ffdaad",
                                    "-webkit-box-shadow": "0 0 10px #fac88c"
                                }),
                                    "" == e(this).val() ? e(this).css("color", "#333333") : e(this).val() == a && e(this).val("").css("color", "#333333")
                            }),
                            e("#txt_account").blur(function() {
                                e(this).parent().parent(".header-search-content-inside").css({
                                    border: "1px solid #e6e6e6",
                                    "-webkit-box-shadow": "0 0 0 #fff"
                                }),
                                "" == e(this).val() && e(this).val(a).css("color", "#d9d9d9")
                            });
                        var c = function(a, b) {
                            window.location.href = s + "public/inside/inside_search.html?value=" + escape(a) + "&category=" + escape(b) + "&flag=true"
                        };
                        e("#txt_account").keyup(function(b) {
                            var d = b.which,
                                f = e.trim(e(this).val());
                            if (13 == d && "" !== f && f !== a) {
                                var g, h;
                                g = void 0 != f.split("&")[0] ? f.split("&")[0] : "",
                                    h = void 0 != f.split("&")[1] ? f.split("&")[1].split(":")[1] : "",
                                    c(g, h)
                            }
                        }),
                            e(window).keydown(function(a) {
                                if (116 == a.keyCode) return window.location.href = s + "public/inside/inside_search.html",
                                    !1
                            }),
                            e("#btn_search, .header-search-logo-inside").bind("click",
                                function() {
                                    var b = e.trim(e("#txt_account").val());
                                    if ("" !== b && b !== a) {
                                        var d, f;
                                        d = void 0 != b.split("&")[0] ? b.split("&")[0] : "",
                                            f = void 0 != b.split("&")[1] ? b.split("&")[1].split(":")[1] : "",
                                            c(d, f)
                                    }
                                }),
                            j.init()
                    },
                    hasInit: w,
                    boxCount: {
                        add: function(a) {
                            u += a,
                            u > 99 && (u = "N"),
                                e("#boxNum").html(u)
                        },
                        sub: function(a) {
                            u -= a,
                            u > 99 && (u = "N"),
                                e("#boxNum").html(u)
                        },
                        get: function() {
                            return u
                        },
                        update: function(a) {
                            u = a,
                            u > 99 && (u = "N"),
                                e("#boxNum").html(u)
                        },
                        canAdd: function() {
                            var a = parseInt(t.box_limit);
                            return ! (u > a - 1) || (showTip("最多只能添加" + a + "个账号！"), !1)
                        }
                    },
                    rankCount: {
                        add: function(a) {
                            v += a,
                                e("#listNum").html(v)
                        },
                        sub: function(a) {
                            v -= a,
                                e("#listNum").html(v)
                        },
                        get: function() {
                            return v
                        },
                        update: function(a) {
                            v = a,
                                e("#listNum").html(a)
                        }
                    },
                    updateNickName: function(a) {
                        e("#userName").html(a)
                    }
                }
        }),
    define("assets/common/js/md5", [],
        function(a) {
            function b(a) {
                function b(a) {
                    return d(c(e(a)))
                }
                function c(a) {
                    return g(h(f(a), 8 * a.length))
                }
                function d(a) {
                    for (var b, c = p ? "0123456789ABCDEF": "0123456789abcdef", d = "", e = 0; e < a.length; e++) b = a.charCodeAt(e),
                        d += c.charAt(b >>> 4 & 15) + c.charAt(15 & b);
                    return d
                }
                function e(a) {
                    for (var b, c, d = "",
                             e = -1; ++e < a.length;) b = a.charCodeAt(e),
                        c = e + 1 < a.length ? a.charCodeAt(e + 1) : 0,
                    55296 <= b && b <= 56319 && 56320 <= c && c <= 57343 && (b = 65536 + ((1023 & b) << 10) + (1023 & c), e++),
                        b <= 127 ? d += String.fromCharCode(b) : b <= 2047 ? d += String.fromCharCode(192 | b >>> 6 & 31, 128 | 63 & b) : b <= 65535 ? d += String.fromCharCode(224 | b >>> 12 & 15, 128 | b >>> 6 & 63, 128 | 63 & b) : b <= 2097151 && (d += String.fromCharCode(240 | b >>> 18 & 7, 128 | b >>> 12 & 63, 128 | b >>> 6 & 63, 128 | 63 & b));
                    return d
                }
                function f(a) {
                    for (var b = Array(a.length >> 2), c = 0; c < b.length; c++) b[c] = 0;
                    for (var c = 0; c < 8 * a.length; c += 8) b[c >> 5] |= (255 & a.charCodeAt(c / 8)) << c % 32;
                    return b
                }
                function g(a) {
                    for (var b = "",
                             c = 0; c < 32 * a.length; c += 8) b += String.fromCharCode(a[c >> 5] >>> c % 32 & 255);
                    return b
                }
                function h(a, b) {
                    a[b >> 5] |= 128 << b % 32,
                        a[14 + (b + 64 >>> 9 << 4)] = b;
                    for (var c = 1732584193,
                             d = -271733879,
                             e = -1732584194,
                             f = 271733878,
                             g = 0; g < a.length; g += 16) {
                        var h = c,
                            i = d,
                            o = e,
                            p = f;
                        c = j(c, d, e, f, a[g + 0], 7, -680876936),
                            f = j(f, c, d, e, a[g + 1], 12, -389564586),
                            e = j(e, f, c, d, a[g + 2], 17, 606105819),
                            d = j(d, e, f, c, a[g + 3], 22, -1044525330),
                            c = j(c, d, e, f, a[g + 4], 7, -176418897),
                            f = j(f, c, d, e, a[g + 5], 12, 1200080426),
                            e = j(e, f, c, d, a[g + 6], 17, -1473231341),
                            d = j(d, e, f, c, a[g + 7], 22, -45705983),
                            c = j(c, d, e, f, a[g + 8], 7, 1770035416),
                            f = j(f, c, d, e, a[g + 9], 12, -1958414417),
                            e = j(e, f, c, d, a[g + 10], 17, -42063),
                            d = j(d, e, f, c, a[g + 11], 22, -1990404162),
                            c = j(c, d, e, f, a[g + 12], 7, 1804603682),
                            f = j(f, c, d, e, a[g + 13], 12, -40341101),
                            e = j(e, f, c, d, a[g + 14], 17, -1502002290),
                            d = j(d, e, f, c, a[g + 15], 22, 1236535329),
                            c = k(c, d, e, f, a[g + 1], 5, -165796510),
                            f = k(f, c, d, e, a[g + 6], 9, -1069501632),
                            e = k(e, f, c, d, a[g + 11], 14, 643717713),
                            d = k(d, e, f, c, a[g + 0], 20, -373897302),
                            c = k(c, d, e, f, a[g + 5], 5, -701558691),
                            f = k(f, c, d, e, a[g + 10], 9, 38016083),
                            e = k(e, f, c, d, a[g + 15], 14, -660478335),
                            d = k(d, e, f, c, a[g + 4], 20, -405537848),
                            c = k(c, d, e, f, a[g + 9], 5, 568446438),
                            f = k(f, c, d, e, a[g + 14], 9, -1019803690),
                            e = k(e, f, c, d, a[g + 3], 14, -187363961),
                            d = k(d, e, f, c, a[g + 8], 20, 1163531501),
                            c = k(c, d, e, f, a[g + 13], 5, -1444681467),
                            f = k(f, c, d, e, a[g + 2], 9, -51403784),
                            e = k(e, f, c, d, a[g + 7], 14, 1735328473),
                            d = k(d, e, f, c, a[g + 12], 20, -1926607734),
                            c = l(c, d, e, f, a[g + 5], 4, -378558),
                            f = l(f, c, d, e, a[g + 8], 11, -2022574463),
                            e = l(e, f, c, d, a[g + 11], 16, 1839030562),
                            d = l(d, e, f, c, a[g + 14], 23, -35309556),
                            c = l(c, d, e, f, a[g + 1], 4, -1530992060),
                            f = l(f, c, d, e, a[g + 4], 11, 1272893353),
                            e = l(e, f, c, d, a[g + 7], 16, -155497632),
                            d = l(d, e, f, c, a[g + 10], 23, -1094730640),
                            c = l(c, d, e, f, a[g + 13], 4, 681279174),
                            f = l(f, c, d, e, a[g + 0], 11, -358537222),
                            e = l(e, f, c, d, a[g + 3], 16, -722521979),
                            d = l(d, e, f, c, a[g + 6], 23, 76029189),
                            c = l(c, d, e, f, a[g + 9], 4, -640364487),
                            f = l(f, c, d, e, a[g + 12], 11, -421815835),
                            e = l(e, f, c, d, a[g + 15], 16, 530742520),
                            d = l(d, e, f, c, a[g + 2], 23, -995338651),
                            c = m(c, d, e, f, a[g + 0], 6, -198630844),
                            f = m(f, c, d, e, a[g + 7], 10, 1126891415),
                            e = m(e, f, c, d, a[g + 14], 15, -1416354905),
                            d = m(d, e, f, c, a[g + 5], 21, -57434055),
                            c = m(c, d, e, f, a[g + 12], 6, 1700485571),
                            f = m(f, c, d, e, a[g + 3], 10, -1894986606),
                            e = m(e, f, c, d, a[g + 10], 15, -1051523),
                            d = m(d, e, f, c, a[g + 1], 21, -2054922799),
                            c = m(c, d, e, f, a[g + 8], 6, 1873313359),
                            f = m(f, c, d, e, a[g + 15], 10, -30611744),
                            e = m(e, f, c, d, a[g + 6], 15, -1560198380),
                            d = m(d, e, f, c, a[g + 13], 21, 1309151649),
                            c = m(c, d, e, f, a[g + 4], 6, -145523070),
                            f = m(f, c, d, e, a[g + 11], 10, -1120210379),
                            e = m(e, f, c, d, a[g + 2], 15, 718787259),
                            d = m(d, e, f, c, a[g + 9], 21, -343485551),
                            c = n(c, h),
                            d = n(d, i),
                            e = n(e, o),
                            f = n(f, p)
                    }
                    return Array(c, d, e, f)
                }
                function i(a, b, c, d, e, f) {
                    return n(o(n(n(b, a), n(d, f)), e), c)
                }
                function j(a, b, c, d, e, f, g) {
                    return i(b & c | ~b & d, a, b, e, f, g)
                }
                function k(a, b, c, d, e, f, g) {
                    return i(b & d | c & ~d, a, b, e, f, g)
                }
                function l(a, b, c, d, e, f, g) {
                    return i(b ^ c ^ d, a, b, e, f, g)
                }
                function m(a, b, c, d, e, f, g) {
                    return i(c ^ (b | ~d), a, b, e, f, g)
                }
                function n(a, b) {
                    var c = (65535 & a) + (65535 & b);
                    return (a >> 16) + (b >> 16) + (c >> 16) << 16 | 65535 & c
                }
                function o(a, b) {
                    return a << b | a >>> 32 - b
                }
                var p = 0;
                return b(a)
            }
            return b
        }),
    define("assets/common/js/services", ["jquery"],
        function(a) {
            var b = a("jquery"),
                c = a("memory");
            return function() {
                var a = !1,
                    d = !1;
                return b(window).scroll(function() {
                    var c = document.documentElement.clientHeight;
                    b(document).scrollTop() >= c / 2 - 100 ? a || "-60px" != b(".btn-top").css("right") || (a = !0, b(".btn-top").animate({
                            right: "2"
                        },
                        "fast",
                        function() {
                            a = !1
                        })) : a || "2px" != b(".btn-top").css("right") || (a = !0, b(".btn-top").animate({
                            right: "-60px"
                        },
                        "fast",
                        function() {
                            a = !1
                        }))
                }),
                    b(document).delegate(".btn-wx", "mouseenter",
                        function() {
                            b(this).find(".pic").fadeIn("fast")
                        }),
                    b(document).delegate(".btn-wx", "mouseleave",
                        function() {
                            b(this).find(".pic").fadeOut("fast")
                        }),
                    b(document).delegate(".btn-phone", "mouseenter",
                        function() {
                            b(this).find(".phone-part").css({
                                width: "145px"
                            })
                        }),
                    b(document).delegate(".btn-phone", "mouseleave",
                        function() {
                            b(this).find(".phone-part").css({
                                width: "0px"
                            })
                        }),
                    b(document).delegate(".btn-top", "click",
                        function() {
                            b(".mCSB_dragger").length > 0 ? b("body").mCustomScrollbar("scrollTo", "top") : b("html, body").animate({
                                    "scroll-top": 2
                                },
                                "fast",
                                function() {
                                    b(".btn-top").animate({
                                            right: "-60px"
                                        },
                                        "fast")
                                })
                        }),
                    {
                        init: function() {
                            if (!d) {
                                b(document.body).append('<script id="qd28521506101b466a066429dd6e3635ab64a3243126" src="https://wp.qiye.qq.com/qidian/2852150610/1b466a066429dd6e3635ab64a3243126" charset="utf-8" async defer><\/script>'),
                                    b(document.body).append('<script id="qd28521506105e189d00fa8e96817a0c47e79af8fc11" src="https://wp.qiye.qq.com/qidian/2852150610/5e189d00fa8e96817a0c47e79af8fc11" charset="utf-8" async defer><\/script>'),
                                    b(document.body).append('<div class="btn-help"><a href="' + c.rootUrl.help + 'help_detail.html?uuid=7DB1C684B0B3C368CF74C660F7B8DDA6" class="help-img" target="_blank"></a></div>'),
                                    b(document.body).append('<div class="btn-top"><span class="top-img"></span></div>'),
                                    d = !0;
                                var a = setInterval(function() {
                                        var b = document.getElementsByClassName("class_qidian_wpa");
                                        b.length > 0 && (b[0].style.cssText += "transform:scale(0.7,0.7);-webkit-transform:scale(0.7,0.7);-moz-transform:scale(0.7,0.7);transition:all 0.5s;-webkit-transition:all 0.5s;-moz-transition:all 0.5s;", b[0].style.right = "2px", clearInterval(a))
                                    },
                                    100)
                            }
                        }
                    }
            } ()
        }),
    define("assets/common/js/arale-cookie", [],
        function(a, b, c) {
            function d(a, b) {
                var c = {};
                if (e(a) && a.length > 0) for (var d, f, g, i = b ? j: h, k = a.split(/;\s/g), l = 0, m = k.length; l < m; l++) {
                    if ((g = k[l].match(/([^=]+)=/i)) instanceof Array) try {
                        d = j(g[1]),
                            f = i(k[l].substring(g[1].length + 1))
                    } catch(n) {} else d = j(k[l]),
                        f = "";
                    d && (c[d] = f)
                }
                return c
            }
            function e(a) {
                return "string" == typeof a
            }
            function f(a) {
                return e(a) && "" !== a
            }
            function g(a) {
                if (!f(a)) throw new TypeError("Cookie name must be a non-empty string")
            }
            function h(a) {
                return a
            }
            var i = b,
                j = decodeURIComponent,
                k = encodeURIComponent;
            i.get = function(a, b) {
                g(a),
                    b = "function" == typeof b ? {
                        converter: b
                    }: b || {};
                var c = d(document.cookie, !b.raw);
                return (b.converter || h)(c[a])
            },
                i.set = function(a, b, c) {
                    g(a),
                        c = c || {};
                    var d = c.expires,
                        e = c.domain,
                        h = c.path;
                    c.raw || (b = k(String(b)));
                    var i = a + "=" + b,
                        j = d;
                    return "number" == typeof j && (j = new Date, j.setDate(j.getDate() + d)),
                    j instanceof Date && (i += "; expires=" + j.toUTCString()),
                    f(e) && (i += "; domain=" + e),
                    f(h) && (i += "; path=" + h),
                    c.secure && (i += "; secure"),
                        document.cookie = i,
                        i
                },
                i.remove = function(a, b) {
                    return b = b || {},
                        b.expires = new Date(0),
                        this.set(a, "", b)
                }
        }),
    define("assets/common/js/login", ["api-common"],
        function(a) {
            var b = a("api-common"),
                c = a("jquery"),
                d = a("memory");
            c.ajaxSetup({
                type: "POST",
                dataType: "json"
            });
            var e = b.getCommonData,
                f = b.getNeedLoginData,
                g = b.getNeedLoginDataSync,
                h = b.download,
                i = d.urlBase,
                j = (d.appBase, d.appDomain, i),
                k = function() {
                    var a = j + "common/account/",
                        b = j + "login/",
                        c = j + "sys/account/ranklist/",
                        d = j + "sys/account/box/",
                        g = j + "sys/account/ranklist/box/",
                        i = j + "publish/",
                        k = j + "sys/account/weixin/",
                        l = j + "sys/account/tixian/",
                        m = j + "login/wxyz/";
                    return {
                        rank: {
                            searchList: function(a) {
                                f(c + "search", {},
                                    a)
                            },
                            postList: function(a, b) {
                                f(c + "post", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            putList: function(a, b) {
                                f(c + "put", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            delList: function(a, b) {
                                f(c + "delete", {
                                        id: a
                                    },
                                    b)
                            },
                            publish: function(a, b) {
                                f(c + "publish", {
                                        id: a
                                    },
                                    b)
                            },
                            get: function(a, b) {
                                f(c + "get", {
                                        id: a
                                    },
                                    b)
                            },
                            updateState: function(a, b, d) {
                                f(c + "updateState", {
                                        id: a,
                                        state: b
                                    },
                                    d)
                            },
                            getDetail: function(a, b, c, d) {
                                f(i + "getDetail", {
                                        flag: a,
                                        period: b,
                                        time: c
                                    },
                                    d)
                            },
                            download: function(a, b, c) {
                                h(i + "download", {
                                    flag: a,
                                    period: b,
                                    time: c
                                })
                            },
                            getPartsDetail: function(a, b, c, d, e, g, h) {
                                f(i + "getPartsDetail", {
                                        flag: a,
                                        period: b,
                                        time: c,
                                        count: d,
                                        sort_column: e,
                                        desc: g
                                    },
                                    h)
                            }
                        },
                        box: {
                            searchBox: function(a) {
                                f(d + "search", {},
                                    a)
                            },
                            postIds: function(a, b) {
                                f(d + "postIds", {
                                        ids: a
                                    },
                                    b)
                            },
                            delBox: function(a, b) {
                                f(d + "delete", {
                                        ids: a
                                    },
                                    b)
                            }
                        },
                        rankBox: {
                            searchRankBox: function(a, b) {
                                f(g + "search", {
                                        id: a
                                    },
                                    b)
                            },
                            postRankBox: function(a, b, c) {
                                f(g + "post", {
                                        ids: a,
                                        ranklist_id: b
                                    },
                                    c)
                            },
                            delRankBox: function(a, b, c) {
                                f(g + "delete", {
                                        ids: a,
                                        ranklist_id: b
                                    },
                                    c)
                            }
                        },
                        money: {
                            getMoneyDetail: function(a) {
                                e(l + "getMoneyDetail", {},
                                    a)
                            },
                            getCashRecord: function(a, b) {
                                e(l + "getCashRecord", {
                                        pageSize: a
                                    },
                                    b)
                            },
                            fetchMoney: function(a, b) {
                                e(l + "fetchMoney", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            }
                        },
                        info: {
                            getUser: function(b) {
                                e(a + "getFull", {},
                                    b)
                            },
                            putUser: function(b, c) {
                                f(a + "put", {
                                        item: JSON.stringify(b)
                                    },
                                    c)
                            },
                            changePass: function(b, c, d) {
                                f(a + "changePass", {
                                        oldPass: b,
                                        newPass: c
                                    },
                                    d)
                            },
                            searchRelevance: function(a) {
                                f(k + "search", {},
                                    a)
                            },
                            addRelevance: function(a, b) {
                                e(k + "add", {
                                        account: a
                                    },
                                    b)
                            },
                            validate: function(a, b) {
                                e(k + "validate", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            deleteRel: function(a, b) {
                                e(k + "delete", {
                                        account: a
                                    },
                                    b)
                            },
                            update: function(a, b) {
                                e(k + "update", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            getRelateCode: function(a) {
                                f(k + "getRelateCode", {},
                                    a)
                            },
                            searchNeedValidate: function(a) {
                                e(k + "searchNeedValidate", {},
                                    a)
                            },
                            ApproveCode: function(a, b) {
                                e(k + "ApproveCode", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            resend: function(a, b) {
                                e(k + "resend", {
                                        account: a
                                    },
                                    b)
                            },
                            getInviteCode: function(a) {
                                e(k + "getInviteCode", {},
                                    a)
                            }
                        },
                        getUser: function(b) {
                            e(a + "get", {},
                                b)
                        },
                        searchBox: function(a) {
                            e(d + "search", {},
                                a)
                        },
                        checkToken: function(b) {
                            e(a + "checkToken", {},
                                b)
                        },
                        register: function(a, c, d, f) {
                            e(b + "register", {
                                    identifyCode: a,
                                    email: c,
                                    flag: d
                                },
                                f)
                        },
                        registerUpdate: function(a, c, d, f) {
                            e(b + "registerUpdate", {
                                    identifyCode: a,
                                    email: c,
                                    flag: d
                                },
                                f)
                        },
                        registerCheck: function(a, c) {
                            e(b + "registerCheck", {
                                    flag: a
                                },
                                c)
                        },
                        registerBack: function(a, c) {
                            e(b + "registerBack", {
                                    item: JSON.stringify(a)
                                },
                                c)
                        },
                        login: function(a, c, d, f, g) {
                            e(b + "login", {
                                    username: a,
                                    password: c,
                                    flag: d,
                                    identifyCode: f
                                },
                                g)
                        },
                        loginandbdwx: function(a, c, d, f, g, h) {
                            e(b + "loginandbdwx", {
                                    username: a,
                                    password: c,
                                    flag: d,
                                    identifyCode: f,
                                    openid: g
                                },
                                h)
                        },
                        loginxz: function(a, b) {
                            e(m + "loginxz", {
                                    username: a
                                },
                                b)
                        },
                        logincreat: function(a, b) {
                            e(m + "logincreat", {
                                    item: JSON.stringify(a)
                                },
                                b)
                        },
                        loginemail: function(a, b) {
                            e(m + "loginemail", {
                                    name: a
                                },
                                b)
                        },
                        reset: function(a, c, d, f) {
                            e(b + "reset", {
                                    identifyCode: a,
                                    email: c,
                                    flag: d
                                },
                                f)
                        },
                        resetCheck: function(a, c) {
                            e(b + "resetCheck", {
                                    flag: a
                                },
                                c)
                        },
                        resetBack: function(a, c) {
                            e(b + "resetBack", {
                                    item: JSON.stringify(a)
                                },
                                c)
                        },
                        loginNew: function(a, c, d, f, g) {
                            e(b + "loginNew", {
                                    username: a,
                                    password: c,
                                    flag: d,
                                    identifyCode: f
                                },
                                g)
                        },
                        loginCount: function(a, c) {
                            e(b + "loginCount", {
                                    username: a
                                },
                                c)
                        }
                    }
                } (),
                l = function() {
                    var a = j + "login/new/";
                    return {
                        getEwmData: function(b, c) {
                            e(a + "getEwmData", {
                                    ticket: b
                                },
                                c)
                        },
                        getLoginBanner: function(b) {
                            e(a + "getLoginBanner", {},
                                b)
                        },
                        getEwmSubNotice: function(b, c) {
                            e(a + "getEwmSubNotice", {
                                    ticket: b
                                },
                                c)
                        },
                        loginChoose: function(b, c, d) {
                            e(a + "loginChoose", {
                                    username: b,
                                    ticket: c
                                },
                                d)
                        },
                        sendSMSNewUsername: function(b, c, d) {
                            e(a + "sendSMSNewUsername", {
                                    phone_login: b,
                                    type: c
                                },
                                d)
                        },
                        phoneLogin: function(b, c, d) {
                            e(a + "phoneLogin", {
                                    phone_login: b,
                                    phoneCode: c
                                },
                                d)
                        },
                        usernameLogin: function(b, c, d, f, g) {
                            e(a + "usernameLogin", {
                                    username: b,
                                    password: c,
                                    flag: d,
                                    identifyCode: f
                                },
                                g)
                        },
                        usernameLogins: function(b, c) {
                            e(a + "usernameLogins", {
                                    phone_login: b
                                },
                                c)
                        },
                        phoneLogins: function(b, c) {
                            e(a + "phoneLogins", {
                                    phone_login: b
                                },
                                c)
                        },
                        usernameExists: function(b, c) {
                            g(a + "usernameExists", {
                                    username: b
                                },
                                !1, c)
                        },
                        usernameExistsRegistered: function(b, c, d) {
                            g(a + "usernameExistsRegistered", {
                                    username: b,
                                    status: c
                                },
                                !1, d)
                        },
                        loginCount: function(b, c) {
                            e(a + "loginCount", {
                                    username: b
                                },
                                c)
                        },
                        openidCreateNewAccount: function(b, c, d) {
                            e(a + "openidCreateNewAccount", {
                                    openid: b,
                                    ticket: c
                                },
                                d)
                        },
                        phoneCreateNewAccount: function(b, c, d, f) {
                            e(a + "phoneCreateNewAccount", {
                                    phone_login: b,
                                    phoneCode: c,
                                    password: d
                                },
                                f)
                        },
                        checkRetrievePass: function(b, c, d) {
                            e(a + "checkRetrievePass", {
                                    phone_login: b,
                                    phoneCode: c
                                },
                                d)
                        },
                        RetrievePass: function(b, c, d, f) {
                            e(a + "RetrievePass", {
                                    phone_login: b,
                                    pass: c,
                                    confirmpass: d
                                },
                                f)
                        },
                        reset: function(b, c, d, f) {
                            e(a + "reset", {
                                    email: b,
                                    flag: c,
                                    identifyCode: d
                                },
                                f)
                        },
                        resetCheck: function(b, c) {
                            e(a + "resetCheck", {
                                    flag: b
                                },
                                c)
                        },
                        resetBack: function(b, c) {
                            e(a + "resetBack", {
                                    item: JSON.stringify(b)
                                },
                                c)
                        },
                        weixinLoginPhone: function(b, c) {
                            e(a + "weixinLoginPhone", {
                                    code: b
                                },
                                c)
                        }
                    }
                } ();
            return {
                baseUrl: j,
                user: k,
                newLogin: l
            }
        }),
    define("assets/common/js/login-dialog", ["jquery"],
        function(a, b, c) {
            var d = a("jquery"),
                e = (a("common"), a("memory")),
                f = a("dialog"),
                g = a("cookie"),
                h = a("login"),
                i = a("md5"),
                j = {},
                k = {},
                l = {
                    rememberPwd: !1,
                    errorTimeout: "",
                    errorHasShow: !1,
                    qrCode: "",
                    ticket: "",
                    backUrl: "",
                    userNameIsExists: !0,
                    pwdThreeTimesError: !1,
                    WechatTimeInterval: "",
                    codeFlag: "",
                    canLogin: !0,
                    canSendMs: !0,
                    reSendTime: 60,
                    reSendInterval: ""
                };
            k.setWx = function() {
                var a = ['<div class="login-action-code"></div>', '<div class="login-action-code-footer">打开微信扫一扫，经新榜服务验证后即可登录/注册</div>'];
                d("#login_zone").html(a.join("")),
                    j.getQrcode()
            },
                k.setPwd = function() {
                    var a = ['<div class="login-action-pwd">', '<div class="login-action-pwd-line clear"><div class="line-part user"><i class="login-pic"></i></div><input id="account_input" type="text" placeholder="手机/邮箱/新榜ID" value=""/></div>', '<div class="login-action-pwd-line clear"><div class="line-part psd"><i class="login-pic"></i></div><input id="password_input" type="password" placeholder="密码" value=""/></div>', '<div class="login-action-pwd-identify">', '<div class="login-action-pwd-identify-input"><input id="identify_input" type="text" placeholder="请输入验证码" value=""/></div>', '<img id="identifyCode" class="newmain-right-login-bottom-zhdl-yzm-identifycode" src="" alt="验证码加载失败"/>', "</div>", '<div class="login-action-set clear">', '<p><i class="login-pic"></i>保持登录状态</p>', '<a href="' + e.rootUrl.main + "public/login/reset.html?back=" + escape(l.backUrl) + '">忘记密码</a>', "</div>", '<div id="pwd_confirm" class="login-action-btn">登录</div>', "</div>", '<div class="login-action-footer">', '还没有账号？<a href="' + e.rootUrl.main + 'public/login/register_new.html" target="_blank">立即注册</a>', "</div>", '<div id="error" class="login-action-pwd-error" style="display:none;"></div>'];
                    d("#login_zone").html(a.join("")),
                        j.bindPwdFun(),
                        k.changeIdentifyCode()
                },
                k.setPhone = function() {
                    var a = ['<div class="login-action-phone">', '<p class="title">手机短信验证登录</p>', '<div class="login-action-pwd-line clear"><div class="line-part phone"><i class="login-pic"></i></div><input id="phone_input" type="text" placeholder="请输入手机号" maxlength="11" value=""/></div>', '<div class="login-action-identify clear"><input id="phone_code_input" type="text" placeholder="请输入手机验证码" maxlength="8" value=""/><div id="phone_code_btn" class="identify-btn phone-active">获取验证码</div></div>', '<div id="phone_confirm" class="login-action-btn">登录</div>', "</div>", '<div class="login-action-footer">', '还没有账号？<a href="' + e.rootUrl.main + 'public/login/register_new.html" target="_blank">立即注册</a>', "</div>", '<div id="error" class="login-action-phone-error" style="display:none;"></div>'];
                    d("#login_zone").html(a.join("")),
                        j.bindPhoneFun()
                },
                k.setUserRemind = function(a) {
                    var b = ['<div class="login-user">', '<div class="login-user-img"><img src="' + a.headimgurl + '" alt="" width="100%"/></div>', "<p>" + a.nickname + "</p>", "<p>欢迎回来</p>", "</div>"];
                    d("#login_zone").html(b.join(""))
                },
                k.setUserChoose = function(a) {
                    var b = ['<div class="login-account">', '<div class="clear">', '<div class="login-account-img"><img src="' + a.userinfo.headimgurl + '" alt="" width="100%"/></div>', '<div class="login-account-name">' + a.userinfo.nickname + "</div>", "</div>", '<p class="login-account-remind">请选择您想要登录的账号</p>', '<ul id="account_list" class="login-account-list"></ul>', "</div>"];
                    d("#login_zone").html(b.join(""));
                    for (var c = 0; c < a.userzh.length; c++) {
                        var e = d("#account_list"),
                            f = a.userzh[c].nick_name || a.userzh[c].nr_name;
                        e.append('<li data-login="' + a.userzh[c].nr_name + '">' + f + (a.userzh[c].user_type >= 100 || -1 == a.userzh[c].user_type ? '<div class="ade-type">广告主</div>': "") + "</li>")
                    }
                    j.bindChoseClick()
                },
                k.setPhoneUserChoose = function(a, b) {
                    var c = "",
                        e = b ? a.sysuser: a.users;
                    d.each(e,
                        function(a, b) {
                            var d = b.nick_name || b.nr_name;
                            c += '<li class="login-account-phone-item" data-phone="' + b.phone_login + '">' + d + (b.user_type >= 100 || -1 == b.user_type ? '<div class="ade-type">广告主</div>': "") + "</li>"
                        });
                    var f = ['<div class="login-account">', '<div class="login-account-wc"><span class="login-account-wc-strong">' + a.userName + "</span>用户，您好！</div>", '<p class="login-account-remind">请选择您想要登录的账号</p>', '<ul id="account_list" class="login-account-list">' + c + "</ul>", "</div>"];
                    if (d("#login_zone").html(f.join("")), b) return j.bindPhonesmsChoseClick();
                    j.bindPhoneChoseClick()
                },
                k.changeIdentifyCode = function() {
                    l.codeFlag = (new Date).getTime() + "" + Math.random(),
                    "none" == d("#identifyCode").css("display") && d("#identifyCode").show(),
                        d("#identifyCode").attr("src", e.urlBase + "login/getIdentifyCode.json?flag=" + l.codeFlag)
                },
                j.showIdentifyCode = function(a) {
                    d(".login-action-pwd-identify").css("visibility", a)
                },
                j.errorShow = function(a) {
                    if (!l.errorHasShow) {
                        clearTimeout(l.errorTimeout);
                        var b = d("#error");
                        b.html(a),
                            b.show(),
                            l.errorHasShow = !0
                    }
                },
                j.errorHide = function() {
                    l.errorHasShow && (l.errorHasShow = !1, l.errorTimeout = setTimeout(function() {
                            d("#error").hide()
                        },
                        3e3))
                },
                j.initLoginType = function() {
                    var a = d("div.login-type");
                    a.css("background-position", "0 0"),
                        a.attr("data-type", "wx"),
                        d("div.login-normal-tap").removeClass("selected"),
                        d("div.login-normal-tap[data-type=wx]").addClass("selected")
                },
                j.bindFun = function() {
                    d("div.login-type").unbind("click").bind("click",
                        function() {
                            clearInterval(l.WechatTimeInterval);
                            var a = d(this);
                            "wx" == a.attr("data-type") ? (a.css("background-position", "-44px -44px"), a.attr("data-type", "phone"), d("div.login-normal-tap").removeClass("selected"), k.setPhone()) : "phone" == a.attr("data-type") && (a.css("background-position", "0 0"), a.attr("data-type", "wx"), d("div.login-normal-tap").removeClass("selected"), d("div.login-normal-tap[data-type=wx]").addClass("selected"), k.setWx())
                        }),
                        d("div.login-normal-tap").unbind("click").bind("click",
                            function() {
                                clearInterval(l.WechatTimeInterval);
                                var a = d(this);
                                a.hasClass("selected") || (d("div.login-normal-tap").removeClass("selected"), a.addClass("selected"));
                                var b = d("div.login-type");
                                b.css("background-position", "0 0"),
                                    b.attr("data-type", "wx"),
                                    "wx" == a.attr("data-type") ? k.setWx() : "pwd" == a.attr("data-type") && k.setPwd()
                            }),
                        d("#identifyCode").unbind("click").bind("click",
                            function() {
                                k.changeIdentifyCode()
                            }),
                        setTimeout(function() {
                                clearInterval(l.WechatTimeInterval),
                                    d(".login-action-code").html('请<a href="">刷新</a>页面后重试')
                            },
                            3e5)
                },
                j.bindPwdFun = function() {
                    d("#login_zone input").unbind("focus").bind("focus",
                        function() {
                            j.errorHide()
                        }),
                        d("div.login-action-set p").unbind("click").bind("click",
                            function() {
                                var a = d(this);
                                l.rememberPwd ? (a.find("i").removeClass("checked"), l.rememberPwd = !1) : (a.find("i").addClass("checked"), l.rememberPwd = !0)
                            }),
                        d("#account_input").unbind("blur").bind("blur",
                            function() {
                                var a = d(this).val();
                                "" != a ? (l.userNameIsExists = !1, h.newLogin.usernameExists(a,
                                    function(b) {
                                        0 == b.code ? j.errorShow("该账号不存在") : 0 == b.pass ? j.errorShow("未设置密码，用其他方式登录") : (l.userNameIsExists = !0, j.checkLoginError(a))
                                    })) : j.errorShow("账号不能为空")
                            }),
                        d("#pwd_confirm").unbind("click").bind("click",
                            function() {
                                j.pwdLogin()
                            }),
                        d("#password_input").unbind("keyup").bind("keyup",
                            function(a) {
                                13 == a.which && j.pwdLogin()
                            })
                },
                j.pwdLogin = function() {
                    var a = d("#account_input").val(),
                        b = i(i(d("#password_input").val()) + e.mdValue),
                        c = d("#identify_input").val();
                    "" != a && "" != b && l.userNameIsExists && l.canLogin && (l.canLogin = !1, h.newLogin.usernameLogin(a, b, l.codeFlag, c,
                        function(b) {
                            1 == b.code ? (l.rememberPwd ? (g.setCookie("rmbuser", "true", 30), g.setCookie("name", a, 365), g.setCookie("token", b.token, 30), g.setCookie("useLoginAccount", "true", 30)) : (g.setCookie("useLoginAccount", null), g.setCookie("rmbuser", null), g.setCookie("name", null), g.setCookie("token", null), g.setCookie("token", b.token)), window.location.reload()) : (j.errorShow(b.msg), -10 == b.code ? j.checkLoginError(a) : -4 == b.code ? k.changeIdentifyCode() : "000" === b.code && (b.userName = a, k.setPhoneUserChoose(b)), l.canLogin = !0)
                        }))
                },
                j.checkLoginError = function(a) {
                    h.newLogin.loginCount(a,
                        function(a) {
                            3 == a ? (l.pwdThreeTimesError = !0, j.showIdentifyCode("visible")) : (l.pwdThreeTimesError = !1, j.showIdentifyCode("hidden"))
                        })
                },
                j.bindPhoneFun = function() {
                    d("#login_zone input").unbind("focus").bind("focus",
                        function() {
                            j.errorHide()
                        }),
                        d("#phone_input").unbind("blur").bind("blur",
                            function() {
                                "" == d(this).val() && j.errorShow("手机号不能为空")
                            }),
                        d("#phone_code_btn").unbind("click").bind("click",
                            function() {
                                if (g.getCookie("isSendMs")) j.errorShow("发送过于频繁，请稍后再试");
                                else {
                                    var a = d("#phone_input").val();
                                    j.sendSMSNewUsername(a)
                                }
                            }),
                        d("#phone_confirm").unbind("click").bind("click",
                            function() {
                                j.phoneLogin()
                            }),
                        d("#phone_code_input").unbind("keyup").bind("keyup",
                            function(a) {
                                13 == a.which && j.phoneLogin()
                            })
                },
                j.sendSMSNewUsername = function(a) {
                    return "" == a ? void j.errorShow("手机号不能为空") : /^(1+[0-9]{10})$/.test(a) ? (g.setCookie("isSendMs", !0, 1 / 1440), l.canSendMs && j.setMsCount(), void h.newLogin.sendSMSNewUsername(a, "login",
                        function(a) { - 1 == a ? j.errorShow("验证码发送失败") : -2 == a && j.errorShow("当天发送短信已达上限")
                        })) : void j.errorShow("手机号格式错误")
                },
                j.setMsCount = function() {
                    var a = d("#phone_code_btn");
                    a.removeClass("phone-active"),
                        a.addClass("phone-disable"),
                        a.html(l.reSendTime + "秒后重发"),
                        l.reSendTime--,
                        l.canSendMs = !1,
                        l.reSendInterval = setInterval(function() {
                                if (0 == l.reSendTime) return clearInterval(l.reSendInterval),
                                    a.removeClass("phone-disable"),
                                    a.addClass("phone-active"),
                                    a.html("获取验证码"),
                                    l.canSendMs = !0,
                                    void(l.reSendTime = 60);
                                a.html(l.reSendTime + "秒后重发"),
                                    l.reSendTime--
                            },
                            1e3)
                },
                j.phoneLogin = function() {
                    var a = d("#phone_input").val(),
                        b = d("#phone_code_input").val();
                    "" != a && "" != b && l.canLogin && (l.canLogin = !1, h.newLogin.phoneLogin(a, b,
                        function(b) {
                            if (l.canLogin = !0, 0 == b.code) return b.userName = a,
                                k.setPhoneUserChoose(b, "sms");
                            if (1 == b.code) b.msg ? (f.showTopTip("系统检测到您是初次登录，已为您创建账号。"), setTimeout(function() {
                                    g.setCookie("token", null),
                                        g.setCookie("token", b.token, 30),
                                        window.location.reload()
                                },
                                2e3)) : (g.setCookie("token", null), g.setCookie("token", b.token, 30), window.location.reload());
                            else {
                                switch (b.code) {
                                    case "-1":
                                        j.errorShow("手机验证码错误");
                                        break;
                                    case "-2":
                                        j.errorShow("手机验证码已过期");
                                        break;
                                    case "-7":
                                        j.errorShow("请先获取短信验证码")
                                }
                                l.canLogin = !0
                            }
                        }))
                },
                j.getQrcode = function() {
                    var a = d(".login-action-code"),
                        b = function() {
                            var b = new Image;
                            b.src = l.qrCode,
                                d(b).width(232),
                                d(b).height(232),
                                d(b).on("load",
                                    function() {
                                        a.html(""),
                                            d(b).appendTo(a)
                                    })
                        };
                    if (a.html('<img src="' + e.rootUrl.common + 'assets/common/img/public/loading.gif" width="80" height="80" style="margin-top:60px;"/>'), l.qrCode && l.ticket) return b(),
                        void j.checkWechatLogin();
                    g.getCookie("ticket") && (l.ticket = g.getCookie("ticket")),
                        h.newLogin.getEwmData(l.ticket,
                            function(c) {
                                1 == c.code ? (l.ticket = c.ticket, g.setCookie("ticket", c.ticket, 1 / 24), l.qrCode = c.url, b(), j.checkWechatLogin()) : a.html('请<a href="">刷新</a>页面后重试')
                            })
                },
                j.checkWechatLogin = function() {
                    var a = 0,
                        b = d(".login-action-code");
                    l.WechatTimeInterval = setInterval(function() {
                            a++>120 ? b.html('请<a href="">刷新</a>页面后重试') : h.newLogin.getEwmSubNotice(l.ticket,
                                function(a) {
                                    1 == a.smtype && (clearInterval(l.WechatTimeInterval), k.setUserRemind(a.userinfo), 0 == a.wxglzh ? g.getCookie("openid") != a.userinfo.openid && (g.setCookie("openid", a.userinfo.openid), j.registerNewUser(a.userinfo)) : 1 == a.wxglzh ? (g.setCookie("token", null), g.setCookie("token", a.token, 30), setTimeout(function() {
                                            window.location.reload()
                                        },
                                        1e3)) : j.setUser(a))
                                })
                        },
                        500)
                },
                j.registerNewUser = function(a) {
                    h.newLogin.openidCreateNewAccount(a.openid, l.ticket,
                        function(a) {
                            1 == a.code ? (f.showTopTip("系统检测到您是初次登录，已为您创建账号。"), setTimeout(function() {
                                    g.setCookie("token", null),
                                        g.setCookie("token", a.token, 30),
                                        window.location.reload()
                                },
                                2e3)) : -6 == a.code ? f.showTopTip("注册失败") : -7 == a.code && f.showTopTip("微信号为空")
                        })
                },
                j.directLogin = function(a) {
                    l.canLogin && (l.canLogin = !1, h.newLogin.loginChoose(a, l.ticket,
                        function(a) {
                            1 == a.code ? (g.setCookie("token", null), g.setCookie("token", a.token, 30), window.location.reload()) : -1 == a.code ? f.showTopTip("该账号不存在") : -2 == a.code ? f.showTopTip("未通过微信验证，请重新登录") : -1e4 == a.code && f.showTopTip("该账号已被冻结"),
                                l.canLogin = !0
                        }))
                },
                j.setUser = function(a) {
                    setTimeout(function() {
                            k.setUserChoose(a)
                        },
                        1e3)
                },
                j.bindChoseClick = function() {
                    d(".login-account-list li").unbind("click").bind("click",
                        function() {
                            var a = d(this).attr("data-login");
                            j.directLogin(a)
                        })
                },
                j.bindPhoneChoseClick = function() {
                    d(".login-account-phone-item").unbind("click").bind("click",
                        function() {
                            var a = d(this).data("phone");
                            l.canLogin && (l.canLogin = !1, h.newLogin.usernameLogins(a,
                                function(a) {
                                    return l.canLogin = !0,
                                        1 == a.code ? (g.setCookie("token", null), g.setCookie("token", a.token, 30), window.location.reload()) : -2 == a.code ? f.showTopTip("选择账号超时") : -3 == a.code ? f.showTopTip("账号不存在") : -1e4 == a.code ? f.showTopTip("账号被冻结") : void 0
                                }))
                        })
                },
                j.bindPhonesmsChoseClick = function() {
                    d(".login-account-phone-item").unbind("click").bind("click",
                        function() {
                            var a = d(this).data("phone");
                            l.canLogin && (l.canLogin = !1, h.newLogin.phoneLogins(a,
                                function(a) {
                                    return l.canLogin = !0,
                                        1 == a.code ? (g.setCookie("token", null), g.setCookie("token", a.token, 30), window.location.reload()) : -2 == a.code ? f.showTopTip("选择账号超时") : void 0
                                }))
                        })
                },
                j.initPosition = function() {
                    var a = (window.innerWidth - 350) / 2;
                    d(".login-pop").css("left", a)
                },
                j.popShow = function(a) {
                    var b = (window.innerHeight - 380) / 2;
                    "show" == a ? (d(".login-pop").animate({
                            top: b
                        },
                        500), d(".newloginmask").show()) : (d(".login-pop").animate({
                            top: "-1000px"
                        },
                        500), d(".newloginmask").hide())
                },
                c.exports.shownewLogin = function(a, b) {
                    l.backUrl = a || window.location.href;
                    var c = ['<div class="login-pop" style="top:-1000px;left:0">', '<div class="login-box-header clear">', '<div class="login-normal-tap selected" data-type="wx">微信扫一扫</div>', '<div class="login-normal-tap" data-type="pwd">密码登录</div>', '<div class="login-pic login-type chose" data-type="wx"></div>', '</div><div id="login_zone"></div>', b ? '<div class="ade-pass" style="top:384px">广告主注册<a href="' + e.rootUrl.ade + 'ade/register.html" target="_blank">专属通道>></a></div>': "", '<div class="login-pic close-login"></div>', '</div><div class="newloginmask"></div>'];
                    d(".newloginmask").length < 1 && d(".login-pop").length < 1 ? (d("body").append(c.join("")), j.initPosition(), j.popShow("show"), d(".close-login").unbind("click").bind("click",
                        function() {
                            j.popShow("hide"),
                                j.initLoginType(),
                                clearInterval(l.WechatTimeInterval)
                        })) : j.popShow("show"),
                        k.setWx(),
                        j.bindFun()
                }
        }),
    define("assets/common/js/exports", ["api-common"],
        function(a) {
            var b = a("api-common"),
                c = a("jquery"),
                d = a("memory");
            c.ajaxSetup({
                type: "POST",
                dataType: "json"
            });
            var e = b.getCommonData,
                f = b.getNeedLoginData,
                g = b.download,
                h = d.urlBase,
                i = (d.appBase, d.appDomain,
                    function(a, b, e, f) {
                        c.ajax({
                            url: a + d.accessSuffix,
                            data: b,
                            success: function(a) {
                                a.success && ( - 999 == a.value ? showTip("���˺��ѱ�����!") : e(a.value))
                            },
                            error: function(a) {
                                c.isFunction(f) && f(a),
                                    c.isFunction(networkFail)
                            }
                        })
                    }),
                j = h,
                k = function() {
                    var a = j + "account/",
                        b = j + "account/type/",
                        c = j + "account/field/",
                        d = j + "account/fieldWeixinUser/",
                        h = j + "weixinrank/item/",
                        k = j + "weixinrank/period/",
                        l = j + "account/ade/";
                    return {
                        addByFans: function(b, c) {
                            b.type && m[b.type] && (b.type = m[b.type]),
                                e(a + "addByFans", {
                                        account: JSON.stringify(b)
                                    },
                                    c)
                        },
                        checkExist: function(b, c) {
                            e(a + "checkExist", {
                                    account: b
                                },
                                c)
                        },
                        addByHand: function(b, c) {
                            e(a + "addByHand", {
                                    account: JSON.stringify(b)
                                },
                                c)
                        },
                        checkExistByHand: function(b, c) {
                            e(a + "checkExistByHand", {
                                    account: b
                                },
                                c)
                        },
                        addByField: function(b, c) {
                            e(a + "addByField", {
                                    account: JSON.stringify(b)
                                },
                                c)
                        },
                        checkExistByField: function(b, c, d) {
                            e(a + "checkExistByField", {
                                    account: b,
                                    field: c
                                },
                                d)
                        },
                        searchByAccounts: function(b, c) {
                            e(a + "searchByAccounts", {
                                    accounts: b
                                },
                                c)
                        },
                        field: {
                            addField: function(a, b) {
                                e(c + "add", {
                                        field: JSON.stringify(a)
                                    },
                                    b)
                            },
                            getField: function(a, b) {
                                e(c + "get", {
                                        name: a
                                    },
                                    b)
                            },
                            isExist: function(a, b) {
                                e(c + "isExist", {
                                        name: a
                                    },
                                    b)
                            },
                            searchField: function(a, b) {
                                e(c + "search", {
                                        page: a
                                    },
                                    b)
                            },
                            removeField: function(a, b) {
                                e(c + "remove", {
                                        id: a
                                    },
                                    b)
                            },
                            putField: function(a, b) {
                                e(c + "put", {
                                        field: JSON.stringify(a)
                                    },
                                    b)
                            },
                            downloadAll: function(a, b, c, e) {
                                g(d + "downloadAll", {
                                    field: a,
                                    name: b,
                                    account: c,
                                    type: e
                                })
                            },
                            downloadNew: function(a, b, c, e) {
                                g(d + "downloadNew", {
                                    field: a,
                                    name: b,
                                    account: c,
                                    type: e
                                })
                            },
                            downloadField: function(a) {
                                g(d + "download?field=" + a)
                            },
                            getWeixinUser: function(a, b, d, f, g, h, i) {
                                e(c + "getWeixinUser", {
                                        field: a,
                                        name: b,
                                        account: d,
                                        type: f,
                                        page: g,
                                        size: h
                                    },
                                    i)
                            },
                            replaceData: function(a, b, d) {
                                e(c + "replaceData", {
                                        field: a,
                                        path: b
                                    },
                                    d)
                            },
                            uploadData: function(a, b, d) {
                                e(c + "uploadData", {
                                        field: a,
                                        path: b
                                    },
                                    d)
                            },
                            checkExist: function(a, b, c, f, g) {
                                e(d + "checkExist", {
                                        field: a,
                                        account: b,
                                        name: c,
                                        type: f
                                    },
                                    g)
                            },
                            removeData: function(a, b) {
                                e(d + "remove", {
                                        id: a
                                    },
                                    b)
                            },
                            putData: function(a, b) {
                                e(d + "put", {
                                        account: JSON.stringify(a)
                                    },
                                    b)
                            }
                        },
                        item: {
                            add: function(a, b) {
                                e(h + "add", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            isExist: function(a, b) {
                                e(h + "isExist", {
                                        name: a
                                    },
                                    b)
                            },
                            search: function(a, b, c, d) {
                                e(h + "search", {
                                        type: a,
                                        page: b,
                                        size: c
                                    },
                                    d)
                            },
                            put: function(a, b) {
                                e(h + "put", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            remove: function(a, b) {
                                e(h + "remove", {
                                        id: a
                                    },
                                    b)
                            }
                        },
                        period: {
                            add: function(a, b) {
                                e(k + "add", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            isExist: function(a, b, c) {
                                e(k + "isExist", {
                                        id: a,
                                        pub_time: b
                                    },
                                    c)
                            },
                            search: function(a, b, c, d) {
                                e(k + "search", {
                                        id: a,
                                        page: b,
                                        size: c
                                    },
                                    d)
                            },
                            searchByName: function(a, b, c, d) {
                                e(k + "searchByName", {
                                        name: a,
                                        page: b,
                                        size: c
                                    },
                                    d)
                            },
                            put: function(a, b) {
                                e(k + "put", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            remove: function(a, b, c) {
                                e(k + "remove", {
                                        id: a,
                                        pub_time: b
                                    },
                                    c)
                            }
                        },
                        type: {
                            isLocked: function(a, c) {
                                e(b + "isLocked", {
                                        weixin_user_id: a
                                    },
                                    c)
                            },
                            post: function(a, c) {
                                i(b + "post", {
                                        item: JSON.stringify(a)
                                    },
                                    c)
                            },
                            getTypeLog: function(a, c) {
                                i(b + "getTypeLog", {
                                        weixin_user_id: a
                                    },
                                    c)
                            },
                            searchByUser: function(a, c, d, e) {
                                i(b + "searchByUser", {
                                        user_id: a,
                                        size: c,
                                        page: d
                                    },
                                    e)
                            },
                            getAccount: function(a) {
                                i(b + "getAccount", {},
                                    a)
                            },
                            searchByAccount: function(a, c) {
                                i(b + "searchByAccount", {
                                        weixin_user_id: a
                                    },
                                    c)
                            },
                            getCount: function(a) {
                                f(b + "getCount", {},
                                    a)
                            },
                            countNum: function(a) {
                                f(b + "countNum", {},
                                    a)
                            },
                            getAdminLog: function(a, c) {
                                i(b + "getAdminLog", {
                                        weixin_user_id: a
                                    },
                                    c)
                            },
                            put: function(a, c) {
                                i(b + "putYes", {
                                        item: JSON.stringify(a)
                                    },
                                    c)
                            },
                            accountLocked: function(a, c) {
                                i(b + "accountLocked", {
                                        item: JSON.stringify(a)
                                    },
                                    c)
                            },
                            accountUnlocked: function(a, c) {
                                i(b + "accountUnlocked", {
                                        weixin_user_id: a
                                    },
                                    c)
                            }
                        },
                        ad: {
                            search: function(a, b) {
                                e(l + "search", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            hotPush: function(a) {
                                e(l + "hotPush", {},
                                    a)
                            },
                            hotSell: function(a) {
                                e(l + "hotSell", {},
                                    a)
                            },
                            getAdDetail: function(a, b) {
                                f(l + "getAdDetail", {
                                        fake_id: a
                                    },
                                    b)
                            },
                            getTender: function(a, b) {
                                f(l + "getTender", {
                                        fake_id: a
                                    },
                                    b)
                            },
                            tendering: function(a, b) {
                                f(l + "tendering", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            getUseCount: function(a) {
                                f(l + "getUseCount", {},
                                    a)
                            },
                            tenderEdit: function(a, b) {
                                f(l + "tenderEdit", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            toTender: function(a, b) {
                                f(l + "toTender", {
                                        id: a
                                    },
                                    b)
                            },
                            searchByCharge: function(a) {
                                f(l + "searchByCharge", {},
                                    a)
                            },
                            searchByTender: function(a) {
                                f(l + "searchByTender", {},
                                    a)
                            },
                            getCase: function(a) {
                                f(l + "getCase", {},
                                    a)
                            },
                            getAllCase: function(a, b) {
                                f(l + "getAllCase", {
                                        status: a
                                    },
                                    b)
                            },
                            askForTender: function(a, b) {
                                f(l + "askForTender", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            addCase: function(a, b) {
                                f(l + "addCase", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            editCase: function(a, b) {
                                f(l + "editCase", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            editDocument: function(a, b) {
                                f(l + "editDocument", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            startTendering: function(a, b, c) {
                                f(l + "startTendering", {
                                        fake_id: a,
                                        tender_time: b
                                    },
                                    c)
                            },
                            endTendering: function(a, b) {
                                f(l + "endTendering", {
                                        fake_id: a
                                    },
                                    b)
                            },
                            chargeDone: function(a, b) {
                                f(l + "chargeDone", {
                                        uuid: a
                                    },
                                    b)
                            },
                            getCaseTendering: function(a, b, c) {
                                f(l + "getCaseTendering", {
                                        fake_id: a,
                                        category: b
                                    },
                                    c)
                            },
                            getDocument: function(a, b) {
                                e(l + "getDocument", {
                                        fake_id: a
                                    },
                                    b)
                            },
                            getTemporary: function(a, b) {
                                e(l + "getTemporary", {
                                        uuid: a
                                    },
                                    b)
                            },
                            editTemporary: function(a, b) {
                                f(l + "editTemporary", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            binded: function(a, b) {
                                f(l + "binded", {
                                        account_id: a
                                    },
                                    b)
                            },
                            createIndent: function(a, b) {
                                f(l + "createIndent", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            getAllIndent: function(a, b, c, d, e, g) {
                                f(l + "getAllIndent", {
                                        pageNum: a,
                                        title: b,
                                        size: c,
                                        payable: d,
                                        who: e
                                    },
                                    g)
                            },
                            getIndentByNum: function(a, b) {
                                f(l + "getIndentByNum", {
                                        indent_num: a
                                    },
                                    b)
                            },
                            getAllTitle: function(a) {
                                f(l + "getAllTitle", {},
                                    a)
                            },
                            deleteIndent: function(a, b, c) {
                                f(l + "deleteIndent", {
                                        uuid: a,
                                        code: b
                                    },
                                    c)
                            },
                            pay: function(a, b) {
                                f(l + "pay", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            addFodder: function(a, b) {
                                f(l + "addFodder", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            updateFodder: function(a, b) {
                                f(l + "updateFodder", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            deleteFodder: function(a, b) {
                                f(l + "deleteFodder", {
                                        document_id: a
                                    },
                                    b)
                            },
                            foundAllFodder: function(a, b) {
                                f(l + "foundAllFodder", {
                                        document_id: a
                                    },
                                    b)
                            },
                            searchUserInfo: function(a) {
                                e(l + "searchFodderAndPublicNum", {},
                                    a)
                            },
                            getReadNum: function(a, b) {
                                f(l + "getReadNum", {
                                        uuid: a
                                    },
                                    b)
                            },
                            removeTender: function(a, b) {
                                f(l + "removeTender", {
                                        uuid: a
                                    },
                                    b)
                            },
                            getCash: function(a) {
                                f(l + "getCash", {},
                                    a)
                            },
                            fetch: function(a, b, c, d) {
                                f(l + "removeTender", {
                                        money: a,
                                        name: b,
                                        buyer_email: c
                                    },
                                    d)
                            },
                            tqCashPost: function(a, b) {
                                f(l + "tqCashPost", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            getDetailByAccount: function(a) {
                                f(l + "getDetailByAccount", {},
                                    a)
                            },
                            getCash: function(a) {
                                f(l + "getCash", {},
                                    a)
                            }
                        }
                    }
                } (),
                l = function() {
                    var a = j + "data/";
                    return {
                        mail: function(b, c, d, f, g, h) {
                            e(a + "mail", {
                                    name: b,
                                    email: c,
                                    title: d,
                                    content: f,
                                    sendToMe: g
                                },
                                h)
                        },
                        searchUser: function(b, c) {
                            e(a + "search", {
                                    name: b
                                },
                                c)
                        },
                        getTopUser: function(b, c) {
                            e(a + "top", {
                                    date: b
                                },
                                c)
                        },
                        getUser: function(b, c) {
                            e(a + "get", {
                                    uid: b
                                },
                                c)
                        },
                        getUserDetail: function(b, c, d) {
                            e(a + "getDetail", {
                                    uid: b,
                                    date: c
                                },
                                d)
                        },
                        getTrend: function(b, c) {
                            e(a + "trend", {
                                    uid: b
                                },
                                c)
                        },
                        getUserByAccount: function(b, c) {
                            e(a + "getByAccount", {
                                    account: b
                                },
                                c)
                        },
                        getUserDetailByAccount: function(b, c, d) {
                            e(a + "getDetailByAccount", {
                                    account: b,
                                    date: c
                                },
                                d)
                        },
                        getTrendByAccount: function(b, c) {
                            e(a + "trendByAccount", {
                                    account: b
                                },
                                c)
                        },
                        getTopArticles: function(b, c) {
                            e(a + "topArticles", {
                                    uid: b
                                },
                                c)
                        },
                        getTimeArticles: function(b, c) {
                            e(a + "timeArticles", {
                                    uid: b
                                },
                                c)
                        },
                        getTypeRankArticles: function(b, c, d) {
                            e(a + "typeRankArticles", {
                                    type: b,
                                    date: c
                                },
                                d)
                        },
                        addReport: function(b, c) {
                            e(a + "addReport", {
                                    report: JSON.stringify(b)
                                },
                                c)
                        },
                        getAllReports: function(b, c) {
                            e(a + "getReports", {
                                    page: b,
                                    status: 0
                                },
                                c)
                        },
                        getPublishReports: function(b, c) {
                            e(a + "getReports", {
                                    page: b,
                                    status: 1
                                },
                                c)
                        },
                        publicReport: function(b, c) {
                            e(a + "publicReport", {
                                    id: b
                                },
                                c)
                        },
                        removeReport: function(b, c) {
                            e(a + "removeReport", {
                                    id: b
                                },
                                c)
                        }
                    }
                } (),
                m = {
                    "�ֻ�": "�ֻ�"
                },
                n = {
                    "�Ӿ�": "�ֻ�"
                },
                o = function() {
                    var a = j + "list/",
                        b = j + "stat/";
                    return {
                        getList: function(b, c, d, f, g, h, i) {
                            m[f] && (f = m[f]),
                                e(a + b + "/" + c, {
                                        rank_name_group: d,
                                        rank_name: f,
                                        start: g,
                                        end: h
                                    },
                                    i)
                        },
                        getDate: function(b) {
                            e(a + "getDate", {},
                                b)
                        },
                        getMax: function(b) {
                            e(a + "getMax", {},
                                b)
                        },
                        getSampleRecord: function(b, c) {
                            e(a + "getSampleRecord", {
                                    date: b
                                },
                                c)
                        },
                        getStatDetail: function(a, c, d, f) {
                            e(b + "detail", {
                                    rank_name: a,
                                    start_date: c,
                                    end_date: d
                                },
                                f)
                        },
                        getStatHour: function(a, c, d) {
                            e(b + "hour", {
                                    rank_name: a,
                                    rank_date: c
                                },
                                d)
                        }
                    }
                } (),
                p = function() {
                    var a = j + "sys/account/",
                        b = j + "sys/account/login/",
                        c = j + "sys/account/ranklist/",
                        d = j + "sys/account/box/",
                        h = j + "sys/account/ranklist/box/",
                        i = j + "publish/",
                        k = j + "sys/account/weixin/",
                        l = j + "sys/account/money/",
                        m = j + "login/syn/";
                    return {
                        rank: {
                            searchList: function(a) {
                                f(c + "search", {},
                                    a)
                            },
                            postList: function(a, b) {
                                f(c + "post", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            putList: function(a, b) {
                                f(c + "put", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            delList: function(a, b) {
                                f(c + "delete", {
                                        id: a
                                    },
                                    b)
                            },
                            publish: function(a, b) {
                                f(c + "publish", {
                                        id: a
                                    },
                                    b)
                            },
                            get: function(a, b) {
                                f(c + "get", {
                                        id: a
                                    },
                                    b)
                            },
                            updateState: function(a, b, d) {
                                f(c + "updateState", {
                                        id: a,
                                        state: b
                                    },
                                    d)
                            },
                            getDetail: function(a, b, c, d) {
                                f(i + "getDetail", {
                                        flag: a,
                                        period: b,
                                        time: c
                                    },
                                    d)
                            },
                            download: function(a, b, c) {
                                g(i + "download", {
                                    flag: a,
                                    period: b,
                                    time: c
                                })
                            },
                            getPartsDetail: function(a, b, c, d, e, g, h) {
                                f(i + "getPartsDetail", {
                                        flag: a,
                                        period: b,
                                        time: c,
                                        count: d,
                                        sort_column: e,
                                        desc: g
                                    },
                                    h)
                            }
                        },
                        box: {
                            searchBox: function(a) {
                                f(d + "search", {},
                                    a)
                            },
                            postIds: function(a, b) {
                                f(d + "postIds", {
                                        ids: a
                                    },
                                    b)
                            },
                            delBox: function(a, b) {
                                f(d + "delete", {
                                        ids: a
                                    },
                                    b)
                            }
                        },
                        rankBox: {
                            searchRankBox: function(a, b) {
                                f(h + "search", {
                                        id: a
                                    },
                                    b)
                            },
                            postRankBox: function(a, b, c) {
                                f(h + "post", {
                                        ids: a,
                                        ranklist_id: b
                                    },
                                    c)
                            },
                            delRankBox: function(a, b, c) {
                                f(h + "delete", {
                                        ids: a,
                                        ranklist_id: b
                                    },
                                    c)
                            }
                        },
                        money: {
                            getMoneyDetail: function(a) {
                                e(l + "getMoneyDetail", {},
                                    a)
                            },
                            getCashRecord: function(a, b) {
                                e(l + "getCashRecord", {
                                        pageSize: a
                                    },
                                    b)
                            },
                            fetchMoney: function(a, b) {
                                e(l + "fetchMoney", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            }
                        },
                        info: {
                            getUser: function(b) {
                                e(a + "getFull", {},
                                    b)
                            },
                            putUser: function(b, c) {
                                f(a + "put", {
                                        item: JSON.stringify(b)
                                    },
                                    c)
                            },
                            changePass: function(b, c, d) {
                                f(a + "changePass", {
                                        oldPass: b,
                                        newPass: c
                                    },
                                    d)
                            },
                            searchRelevance: function(a) {
                                f(k + "search", {},
                                    a)
                            },
                            addRelevance: function(a, b) {
                                e(k + "add", {
                                        account: a
                                    },
                                    b)
                            },
                            validate: function(a, b) {
                                e(k + "validate", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            deleteRel: function(a, b) {
                                e(k + "delete", {
                                        account: a
                                    },
                                    b)
                            },
                            update: function(a, b) {
                                e(k + "update", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            getRelateCode: function(a) {
                                f(k + "getRelateCode", {},
                                    a)
                            },
                            searchNeedValidate: function(a) {
                                e(k + "searchNeedValidate", {},
                                    a)
                            },
                            ApproveCode: function(a, b) {
                                e(k + "ApproveCode", {
                                        item: JSON.stringify(a)
                                    },
                                    b)
                            },
                            resend: function(a, b) {
                                e(k + "resend", {
                                        account: a
                                    },
                                    b)
                            },
                            getInviteCode: function(a) {
                                e(k + "getInviteCode", {},
                                    a)
                            }
                        },
                        getUser: function(b) {
                            e(a + "get", {},
                                b)
                        },
                        searchBox: function(a) {
                            e(d + "search", {},
                                a)
                        },
                        checkToken: function(b) {
                            e(a + "checkToken", {},
                                b)
                        },
                        register: function(a, c, d, f) {
                            e(b + "register", {
                                    identifyCode: a,
                                    email: c,
                                    flag: d
                                },
                                f)
                        },
                        registerUpdate: function(a, c, d, f) {
                            e(b + "registerUpdate", {
                                    identifyCode: a,
                                    email: c,
                                    flag: d
                                },
                                f)
                        },
                        registerCheck: function(a, c) {
                            e(b + "registerCheck", {
                                    flag: a
                                },
                                c)
                        },
                        registerBack: function(a, c) {
                            e(b + "registerBack", {
                                    item: JSON.stringify(a)
                                },
                                c)
                        },
                        registerBacklogin: function(a, c) {
                            e(b + "registerBacklogin", {
                                    item: JSON.stringify(a)
                                },
                                c)
                        },
                        login: function(a, c, d, f, g) {
                            e(b + "login", {
                                    username: a,
                                    password: c,
                                    flag: d,
                                    identifyCode: f
                                },
                                g)
                        },
                        reset: function(a, c, d, f) {
                            e(b + "reset", {
                                    identifyCode: a,
                                    email: c,
                                    flag: d
                                },
                                f)
                        },
                        resetCheck: function(a, c) {
                            e(b + "resetCheck", {
                                    flag: a
                                },
                                c)
                        },
                        resetBack: function(a, c) {
                            e(b + "resetBack", {
                                    item: JSON.stringify(a)
                                },
                                c)
                        },
                        ade: function(a, c, d, f) {
                            e(b + "ade", {
                                    identifyCode: a,
                                    email: c,
                                    flag: d
                                },
                                f)
                        },
                        adeUpdate: function(a, c, d, f) {
                            e(b + "adeUpdate", {
                                    identifyCode: a,
                                    email: c,
                                    flag: d
                                },
                                f)
                        },
                        synBbs: {
                            synregister: function(a, b) {
                                e(m + "synregister", {
                                        name: a
                                    },
                                    b)
                            },
                            synlogin: function(a, b, c) {
                                e(m + "synlogin", {
                                        name: a,
                                        cookietime: b
                                    },
                                    c)
                            },
                            synupdatepw: function(a, b) {
                                e(m + "synupdatepw", {
                                        name: a
                                    },
                                    b)
                            },
                            logout: function(a) {
                                e(m + "logout", {},
                                    a)
                            }
                        }
                    }
                } (),
                q = function() {
                    var a = j + "school/teacher/",
                        b = j + "school/course/";
                    return {
                        teacher: {
                            searchTeacher: function(b, c, d, f) {
                                e(a + "teacherAndCourse/search", {
                                        filters: JSON.stringify(b),
                                        pageNumber: c,
                                        pageSize: d
                                    },
                                    f)
                            },
                            count: function(b, c) {
                                e(a + "count", {
                                        filters: JSON.stringify(b)
                                    },
                                    c)
                            }
                        },
                        course: {
                            searchCourse: function(a) {
                                e(b + "courseAndTeacher/search", {},
                                    a)
                            },
                            searchAllCourse: function(a, c, d, f) {
                                e(b + "courseAndTeacher/searchCourseAndTeacher", {
                                        filters: JSON.stringify(a),
                                        pageNumber: c,
                                        pageSize: d
                                    },
                                    f)
                            },
                            countCourse: function(a, c) {
                                e(b + "teacherAndCourseSearchtCount/count", {
                                        filters: JSON.stringify(a)
                                    },
                                    c)
                            }
                        }
                    }
                } ();
            return {
                baseUrl: j,
                data: l,
                list: o,
                user: p,
                account: k,
                school: q,
                memory: {
                    rankNameMap: n
                }
            }
        });