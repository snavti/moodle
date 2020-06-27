define ("tool_dataprivacy/categoriesactions",["jquery","core/ajax","core/notification","core/str","core/modal_factory","core/modal_events"],function(a,b,c,d,e,f){var g={DELETE:"[data-action=\"deletecategory\"]"},h=function(){this.registerEvents()};h.prototype.registerEvents=function(){a(g.DELETE).click(function(g){g.preventDefault();var h=a(this).data("id"),i=a(this).data("name");d.get_strings([{key:"deletecategory",component:"tool_dataprivacy"},{key:"deletecategorytext",component:"tool_dataprivacy",param:i},{key:"delete"}]).then(function(d){var g=d[0],i=d[1],j=d[2];return e.create({title:g,body:i,type:e.types.SAVE_CANCEL}).then(function(d){d.setSaveButtonText(j);d.getRoot().on(f.save,function(){b.call([{methodname:"tool_dataprivacy_delete_category",args:{id:h}}])[0].done(function(b){if(b.result){a("tr[data-categoryid=\""+h+"\"]").remove()}else{c.addNotification({message:b.warnings[0].message,type:"error"})}}).fail(c.exception)});d.getRoot().on(f.hidden,function(){d.destroy()});return d})}).done(function(a){a.show()}).fail(c.exception)})};return{init:function init(){return new h}}});
//# sourceMappingURL=categoriesactions.min.js.map
