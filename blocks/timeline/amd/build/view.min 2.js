define ("block_timeline/view",["jquery","block_timeline/view_dates","block_timeline/view_courses"],function(a,b,c){var d={TIMELINE_DATES_VIEW:"[data-region=\"view-dates\"]",TIMELINE_COURSES_VIEW:"[data-region=\"view-courses\"]"};return{init:function init(e){e=a(e);var f=e.find(d.TIMELINE_DATES_VIEW),g=e.find(d.TIMELINE_COURSES_VIEW);b.init(f);c.init(g)},reset:function reset(a){var e=a.find(d.TIMELINE_DATES_VIEW),f=a.find(d.TIMELINE_COURSES_VIEW);b.reset(e);c.reset(f)},shown:function shown(a){var e=a.find(d.TIMELINE_DATES_VIEW),f=a.find(d.TIMELINE_COURSES_VIEW);if(e.hasClass("active")){b.shown(e)}else{c.shown(f)}}}});
//# sourceMappingURL=view.min.js.map
