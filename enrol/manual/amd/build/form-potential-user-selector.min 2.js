define ("enrol_manual/form-potential-user-selector",["jquery","core/ajax","core/templates","core/str"],function(a,b,c,d){return{processResults:function processResults(b,c){var d=[];if(a.isArray(c)){a.each(c,function(a,b){d.push({value:b.id,label:b._label})});return d}else{return c}},transport:function transport(e,f,g,h){var i,j=a(e).attr("courseid"),k=a(e).attr("userfields").split(",");if("undefined"==typeof j){j="1"}var l=a(e).attr("enrolid");if("undefined"==typeof l){l=""}var m=parseInt(a(e).attr("perpage"));if(isNaN(m)){m=100}i=b.call([{methodname:"core_enrol_get_potential_users",args:{courseid:j,enrolid:l,search:f,searchanywhere:!0,page:0,perpage:m+1}}]);i[0].then(function(b){var e=[],f=0;if(b.length<=m){a.each(b,function(b,d){var f=d,g=[];a.each(k,function(a,b){if("undefined"!=typeof d[b]&&""!==d[b]){f.hasidentity=!0;g.push(d[b])}});f.identity=g.join(", ");e.push(c.render("enrol_manual/form-user-selector-suggestion",f))});return a.when.apply(a.when,e).then(function(){var c=arguments;a.each(b,function(a,b){b._label=c[f];f++});g(b)})}else{return d.get_string("toomanyuserstoshow","core",">"+m).then(function(a){g(a)})}}).fail(h)}}});
//# sourceMappingURL=form-potential-user-selector.min.js.map
