define ("block_recentlyaccesseditems/main",["jquery","block_recentlyaccesseditems/repository","core/templates","core/notification"],function(a,b,c,d){var e={CARDDECK_CONTAINER:"[data-region=\"recentlyaccesseditems-view\"]",CARDDECK:"[data-region=\"recentlyaccesseditems-view-content\"]"},f=function(a){return b.getRecentItems(a)},g=function(a,b){if(0<b.length){return c.render("block_recentlyaccesseditems/view-cards",{items:b})}else{var d=a.attr("data-noitemsimgurl");return c.render("block_recentlyaccesseditems/no-items",{noitemsimgurl:d})}};return{init:function init(b){b=a(b);var h=b.find(e.CARDDECK_CONTAINER),i=b.find(e.CARDDECK),j=f(9);j.then(function(a){var b=g(h,a);b.then(function(a,b){return c.replaceNodeContents(i,a,b)}).catch(d.exception);return j}).catch(d.exception)}}});
//# sourceMappingURL=main.min.js.map
