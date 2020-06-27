define ("core_calendar/modal_delete",["jquery","core/notification","core/custom_interaction_events","core/modal","core/modal_events","core/modal_registry","core_calendar/events"],function(a,b,c,d,f,g,h){var i=!1,j={DELETE_ONE_BUTTON:"[data-action=\"deleteone\"]",DELETE_ALL_BUTTON:"[data-action=\"deleteall\"]",CANCEL_BUTTON:"[data-action=\"cancel\"]"},k=function(a){d.call(this,a)};k.TYPE="core_calendar-modal_delete";k.prototype=Object.create(d.prototype);k.prototype.constructor=k;k.prototype.registerEventListeners=function(){d.prototype.registerEventListeners.call(this);this.getModal().on(c.events.activate,j.DELETE_ONE_BUTTON,function(b,c){var d=a.Event(f.save);this.getRoot().trigger(d,this);if(!d.isDefaultPrevented()){this.hide();c.originalEvent.preventDefault()}}.bind(this));this.getModal().on(c.events.activate,j.DELETE_ALL_BUTTON,function(b,c){var d=a.Event(h.deleteAll);this.getRoot().trigger(d,this);if(!d.isDefaultPrevented()){this.hide();c.originalEvent.preventDefault()}}.bind(this));this.getModal().on(c.events.activate,j.CANCEL_BUTTON,function(b,c){var d=a.Event(f.cancel);this.getRoot().trigger(d,this);if(!d.isDefaultPrevented()){this.hide();c.originalEvent.preventDefault()}}.bind(this))};if(!i){g.register(k.TYPE,k,"calendar/event_delete_modal");i=!0}return k});
//# sourceMappingURL=modal_delete.min.js.map
