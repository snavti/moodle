define ("tool_dataprivacy/request_filter",["jquery","core/form-autocomplete","core/str","core/notification"],function(a,b,c,d){var e={REQUEST_FILTERS:"#request-filters"},f=function init(){c.get_strings([{key:"filter",component:"moodle"},{key:"nofiltersapplied",component:"moodle"}]).then(function(a){var c=a[0],d=a[1];return b.enhance(e.REQUEST_FILTERS,!1,"",c,!1,!0,d,!0)}).fail(d.exception);var f=a(e.REQUEST_FILTERS).val();a(e.REQUEST_FILTERS).on("change",function(){var b=a(this).val();if(f.join(",")!==b.join(",")){if(0===b.length){a("#filters-cleared").val(1)}a(this.form).submit()}})};return{init:function init(){f()}}});
//# sourceMappingURL=request_filter.min.js.map
