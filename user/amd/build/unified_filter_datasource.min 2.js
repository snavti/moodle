define ("core_user/unified_filter_datasource",["jquery","core/ajax","core/notification"],function(a,b,c){return{list:function list(b,c){var d=[],e=a(b),f=a(b).data("originaloptionsjson"),g=e.val();a.each(f,function(b,e){if(""!==a.trim(c)&&-1===e.label.toLocaleLowerCase().indexOf(c.toLocaleLowerCase())){return!0}if(-1<a.inArray(e.value,g)){return!0}d.push(e);return!0});var h=new a.Deferred;h.resolve(d);return h.promise()},processResults:function processResults(b,c){var d=[];a.each(c,function(a,b){d.push({value:b.value,label:b.label})});return d},transport:function transport(a,b,d){this.list(a,b).then(d).catch(c.exception)}}});
//# sourceMappingURL=unified_filter_datasource.min.js.map
