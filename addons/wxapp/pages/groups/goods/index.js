var a = getApp(), t = a.requirejs("core"), o = (a.requirejs("jquery"), a.requirejs("foxui"), 
a.requirejs("wxParse/wxParse")), e = 0;

Page({
    data: {
        goods_id: 0,
        advHeight: 1
    },
    imageLoad: function(a) {
        var t = a.detail.height, o = a.detail.width, e = Math.floor(750 * t / o);
        t == o ? this.setData({
            advHeight: 750
        }) : this.setData({
            advHeight: e
        });
    },
    onLoad: function(e) {
        var s = this;
        console.log(e), a.getCache("isIpx") ? s.setData({
            isIpx: !0,
            iphonexnavbar: "fui-iphonex-navbar"
        }) : s.setData({
            isIpx: !1,
            iphonexnavbar: ""
        });
        var i = e.id;
        this.setData({
            goods_id: i
        }), t.post("groups.goods", {
            id: i
        }, function(a) {
            s.setData({
                data: a.data
            }), o.wxParse("wxParseData", "html", a.data.content, s, "0");
        });
    },
    singlebuy: function(a) {
        var o = this;
        t.post("groups/goods/goodsCheck", {
            id: o.data.goods_id,
            type: "single"
        }, function(a) {
            if (1 != a.error) if (0 == o.data.data.more_spec) wx.navigateTo({
                url: "/pages/groups/confirm/index?id=" + o.data.goods_id + "&type=single"
            }); else {
                o.setData({
                    layershow: !0,
                    options: !0
                }), o.setData({
                    optionarr: [],
                    selectSpecsarr: []
                });
                var e = o.data.data.id;
                t.get("groups.goods.get_spec", {
                    id: e
                }, function(a) {
                    console.log(a), o.setData({
                        spec: a.data
                    });
                }), o.setData({
                    layershow: !0,
                    options: !0
                });
            } else t.alert(a.message);
        });
    },
    close: function() {
        this.setData({
            layershow: !1,
            options: !1
        });
    },
    specsTap: function(a) {
        e++;
        var o = this, s = o.data.spec, i = t.pdata(a).spedid, n = t.pdata(a).id, d = t.pdata(a).specindex;
        t.pdata(a).idx;
        s[d].item.forEach(function(a, t) {
            a.id == n ? s[d].item[t].status = "active" : s[d].item[t].status = "";
        }), o.setData({
            spec: s
        });
        var r = o.data.optionarr, p = o.data.selectSpecsarr;
        1 == e ? (r.push(n), p.push(i)) : p.indexOf(i) > -1 ? r.splice(d, 1, n) : (r.push(n), 
        p.push(i)), o.data.optionarr = r, o.data.selectSpecsarr = p, console.log(o.data.optionarr), 
        t.post("groups.goods.get_option", {
            spec_id: o.data.optionarr,
            groups_goods_id: o.data.goods_id
        }, function(a) {
            o.setData({
                optiondata: a.data
            });
        });
    },
    buy: function(a) {
        var o = this, e = (t.pdata(a).op, o.data.goods_id), s = o.data.optiondata;
        o.data.optiondata ? s.stock > 0 ? wx.navigateTo({
            url: "/pages/groups/confirm/index?id=" + e + "&option_id=" + s.id + " &type=single",
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
    onShareAppMessage: function() {
        var a = this.data.data;
        return console.log(a), {
            title: a.title
        };
    },
    check: function() {}
});