define ("block_myoverview/repository",["core/ajax","core/notification"],function(a,b){return{getEnrolledCoursesByTimeline:function getEnrolledCoursesByTimeline(b){var c=a.call([{methodname:"core_course_get_enrolled_courses_by_timeline_classification",args:b}])[0];return c},setFavouriteCourses:function setFavouriteCourses(b){var c=a.call([{methodname:"core_course_set_favourite_courses",args:b}])[0];return c},updateUserPreferences:function updateUserPreferences(c){a.call([{methodname:"core_user_update_user_preferences",args:c}])[0].fail(b.exception)}}});
//# sourceMappingURL=repository.min.js.map
