define ("tool_lp/evidence_delete",["jquery","core/notification","core/ajax","core/str","core/log"],function(a,b,c,d,e){var f={};return{register:function register(g,h){if("undefined"!=typeof f[g]){return}f[g]=a("body").delegate(g,"click",function(f){var g=a(f.currentTarget).parents(h);if(!g.length||1<g.length){e.error("None or too many evidence container were found.");return}var i=g.data("id");if(!i){e.error("Evidence ID was not found.");return}f.preventDefault();f.stopPropagation();d.get_strings([{key:"confirm",component:"moodle"},{key:"areyousure",component:"moodle"},{key:"delete",component:"moodle"},{key:"cancel",component:"moodle"}]).done(function(a){b.confirm(a[0],a[1],a[2],a[3],function(){var a=c.call([{methodname:"core_competency_delete_evidence",args:{id:i}}]);a[0].then(function(){g.remove()}).fail(b.exception)})}).fail(b.exception)})}}});
//# sourceMappingURL=evidence_delete.min.js.map
