define ("tool_lp/form-cohort-selector",["jquery","core/ajax","core/templates"],function(a,b,c){return{processResults:function processResults(b,c){var d=[];a.each(c,function(a,b){d.push({value:b.id,label:b._label})});return d},transport:function transport(d,e,f,g){var h,i=parseInt(a(d).data("contextid"),10),j=a(d).data("includes");h=b.call([{methodname:"tool_lp_search_cohorts",args:{query:e,context:{contextid:i},includes:j}}]);h[0].then(function(b){var d=[],e=0;a.each(b.cohorts,function(a,b){d.push(c.render("tool_lp/form-cohort-selector-suggestion",b))});return a.when.apply(a.when,d).then(function(){var c=arguments;a.each(b.cohorts,function(a,b){b._label=c[e];e++});f(b.cohorts)})}).catch(g)}}});
//# sourceMappingURL=form-cohort-selector.min.js.map
