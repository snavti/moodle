define ("mod_forum/subscription_toggle",["jquery","core/templates","core/notification","mod_forum/repository","mod_forum/selectors","core/pubsub","mod_forum/forum_events"],function(a,b,c,d,e,f,g){return{init:function registerEventListeners(b,h,i){b.on("click",e.subscription.toggle,function(b){var e=a(this),j=e.data("forumid"),k=e.data("discussionid"),l=e.data("targetstate");d.setDiscussionSubscriptionState(j,k,l).then(function(a){f.publish(g.SUBSCRIPTION_TOGGLED,{discussionId:k,subscriptionState:l});return i(e,a)}).catch(c.exception);if(h){b.preventDefault()}})}}});
//# sourceMappingURL=subscription_toggle.min.js.map
