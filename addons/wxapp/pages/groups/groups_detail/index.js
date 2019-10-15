var a = getApp(), t = a.requirejs("core"), e = (a.requirejs("jquery"), a.requirejs("foxui"), 
0), o = a.requirejs("wxParse/wxParse");

Page({
    data: {
        showtab: "groups",
        count_down: !0,
        time: "",
        share: 1,
        options: "",
        show: !1
    },
    onLoad: function(t) {
        var e = this;
        a.getCache("isIpx") ? e.setData({
            isIpx: !0,
            iphonexnavbar: "fui-iphonex-navbar"
        }) : e.setData({
            isIpx: !1,
            iphonexnavbar: ""
        }), this.setData({
            teamid: t.teamid
        }), this.get_details(t.teamid);
    },
    get_details: function(a) {
        var e = this;
        t.get("groups/team/details", {
            teamid: a
        }, function(a) {
            if (0 == a.error && (a.data.goods.content = a.data.goods.content.replace(/data-lazy/g, "src"), 
            e.setData({
                data: a.data
            }), o.wxParse("wxParseData", "html", a.data.goods.content, e, "0")), 0 == a.data.tuan_first_order.success) {
                if (a.data.lasttime2 <= 0) return void e.setData({
                    count_down: !1
                });
                if (clearInterval(e.data.timer), 0 == a.data.tuan_first_order.success) var t = setInterval(function() {
                    e.countDown(a.data.tuan_first_order.createtime, a.data.tuan_first_order.endtime);
                }, 1e3);
                e.setData({
                    timer: t
                });
            }
        });
    },
    countDown: function(a, t, e) {
        var o = parseInt(Date.now() / 1e3), s = (a > o ? a : t) - o, i = parseInt(s), d = Math.floor(i / 86400), r = Math.floor((i - 24 * d * 60 * 60) / 3600), n = Math.floor((i - 24 * d * 60 * 60 - 3600 * r) / 60), u = Math.floor(i - 24 * d * 60 * 60 - 3600 * r - 60 * n);
        0 == d && 0 == r && 0 == n && 0 == u && this.get_details(this.data.teamid);
        var c = [ d, r, n, u ];
        this.setData({
            time: c
        });
    },
    tuxedobuy: function(e) {
        a.checkAuth();
        var o = this, s = o.data.data.goods.id;
        0 == o.data.data.goods.more_spec ? o.data.data.goods.stock > 0 ? t.get("groups/order/create_order", {
            id: s,
            ladder_id: o.data.data.tuan_first_order.ladder_id,
            type: "groups",
            heads: 0,
            teamid: o.data.teamid
        }, function(a) {
            1 != a.error ? -1 != a.error ? wx.navigateTo({
                url: "/pages/groups/confirm/index?id=" + s + "&heads=0&type=groups&teamid=" + o.data.teamid + "&ladder_id=" + o.data.data.tuan_first_order.ladder_id,
                success: function() {
                    o.setData({
                        layershow: !1,
                        chosenum: !1,
                        options: !1
                    });
                }
            }) : wx.redirectTo({
                url: "/pages/message/auth/index"
            }) : t.alert(a.message);
        }) : wx.showToast({
            title: "库存不足",
            icon: "none",
            duration: 2e3
        }) : (t.get("groups.goods.get_spec", {
            id: s
        }, function(a) {
            o.setData({
                spec: a.data
            });
        }), o.setData({
            layershow: !0,
            options: !0
        }), o.setData({
            optionarr: [],
            selectSpecsarr: []
        }), o.data.data.goods.stock > 0 ? wx.navigateTo({
            url: "/pages/groups/confirm/index?id=" + goods_id + "&type=groups&teamid=" + o.data.teamid,
            success: function() {
                o.setData({
                    layershow: !1,
                    chosenum: !1,
                    options: !1
                });
            }
        }) : wx.showToast({
            title: "库存不足",
            icon: "none",
            duration: 2e3
        }), o.setData({
            layershow: !0,
            options: !0
        }));
    },
    close: function() {
        this.setData({
            layershow: !1,
            options: !1
        });
    },
    specsTap: function(a) {
        e++;
        var o = this, s = o.data.spec, i = t.pdata(a).spedid, d = t.pdata(a).id, r = t.pdata(a).specindex;
        t.pdata(a).idx;
        s[r].item.forEach(function(a, t) {
            a.id == d ? s[r].item[t].status = "active" : s[r].item[t].status = "";
        }), o.setData({
            spec: s
        });
        var n = o.data.optionarr, u = o.data.selectSpecsarr;
        1 == e ? (n.push(d), u.push(i)) : u.indexOf(i) > -1 ? n.splice(r, 1, d) : (n.push(d), 
        u.push(i)), o.data.optionarr = n, o.data.selectSpecsarr = u, console.log(o.data.optionarr), 
        t.post("groups.goods.get_option", {
            spec_id: o.data.optionarr,
            groups_goods_id: o.data.data.goods.id
        }, function(a) {
            o.setData({
                optiondata: a.data
            });
        });
    },
    buy: function(a) {
        var e = this, o = (t.pdata(a).op, e.data.data.goods.id), s = e.data.optiondata;
        e.data.optiondata ? s.stock > 0 ? wx.navigateTo({
            url: "/pages/groups/confirm/index?id=" + o + "&type=groups&option_id=" + s.id + " &teamid=" + e.data.teamid,
            success: function() {
                e.setData({
                    layershow: !1,
                    chosenum: !1,
                    options: !1
                });
            }
        }) : wx.showToast({
            title: "库存不足",
            icon: "none",
            duration: 2e3
        }) : wx.showToast({
            title: "请选择规格",
            icon: "none",
            duration: 2e3
        });
    },
    onReady: function() {},
    onShow: function() {},
    onHide: function() {},
    onUnload: function() {},
    onPullDownRefresh: function() {},
    onReachBottom: function() {},
    onShareAppMessage: function(a) {
        var t = this;
        return {
            title: t.data.data.shopshare.title,
            path: "/pages/groups/groups_detail/index?teamid=" + t.data.data.tuan_first_order.teamid,
            imageUrl: t.data.data.shopshare.imgUrl
        };
    },
    goodsTab: function(a) {
        this.setData({
            showtab: a.target.dataset.tap
        });
    }
});