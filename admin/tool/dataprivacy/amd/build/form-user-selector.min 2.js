define ("tool_dataprivacy/form-user-selector",["jquery","core/ajax","core/templates"],function(a,b,c){return{processResults:function processResults(b,c){var d=[];a.each(c,function(a,b){d.push({value:b.id,label:b._label})});return d},transport:function transport(d,e,f,g){var h=b.call([{methodname:"tool_dataprivacy_get_users",args:{query:e}}]);h[0].then(function(b){var d=[],e=0;a.each(b,function(a,b){d.push(c.render("tool_dataprivacy/form-user-selector-suggestion",b))});return a.when.apply(a.when,d).then(function(){var c=arguments;a.each(b,function(a,b){b._label=c[e];e++});f(b)})}).fail(g)}}});
//# sourceMappingURL=form-user-selector.min.js.map
