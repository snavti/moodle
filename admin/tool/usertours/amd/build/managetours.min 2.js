define ("tool_usertours/managetours",["jquery","core/ajax","core/str","core/notification"],function(a,b,c,d){var e={removeTour:function removeTour(b){b.preventDefault();var e=a(b.currentTarget).attr("href");c.get_strings([{key:"confirmtourremovaltitle",component:"tool_usertours"},{key:"confirmtourremovalquestion",component:"tool_usertours"},{key:"yes",component:"moodle"},{key:"no",component:"moodle"}]).then(function(a){d.confirm(a[0],a[1],a[2],a[3],function(){window.location=e})}).catch()},setup:function setup(){a("body").delegate("[data-action=\"delete\"]","click",e.removeTour)}};return{setup:e.setup}});
//# sourceMappingURL=managetours.min.js.map
