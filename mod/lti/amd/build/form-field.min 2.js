define ("mod_lti/form-field",["jquery"],function(a){var b=function(a,b,c,d){this.name=a;this.id="id_"+this.name;this.selector="#"+this.id;this.type=b;this.resetIfUndefined=c;this.defaultValue=d};b.TYPES={TEXT:1,SELECT:2,CHECKBOX:3,EDITOR:4};b.prototype.setFieldValue=function(c){if(null===c){if(this.resetIfUndefined){c=this.defaultValue}else{return}}switch(this.type){case b.TYPES.CHECKBOX:if(c){a(this.selector).prop("checked",!0)}else{a(this.selector).prop("checked",!1)}break;case b.TYPES.EDITOR:if("undefined"!=typeof c.text){var d=a(this.selector+"editable");if(d.length){d.html(c.text)}else if("undefined"!=typeof tinyMCE){tinyMCE.execInstanceCommand(this.id,"mceInsertContent",!1,c.text)}a(this.selector).val(c.text)}break;default:a(this.selector).val(c);break;}};return b});
//# sourceMappingURL=form-field.min.js.map
