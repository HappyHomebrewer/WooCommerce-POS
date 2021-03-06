var Route = require('lib/config/route');
var POS = require('lib/utilities/global');
var View = require('./view');

var General = Route.extend({

  initialize: function( options ) {
    options = options || {};
    this.container = options.container;
    this.model = options.model;
  },

  fetch: function() {
    if(this.model.isNew()){
      return this.model.fetch();
    }
  },

  render: function() {
    var view = new View({
      model: this.model
    });
    this.listenTo(view, 'action:save', this.saveModel);
    this.container.show(view);
  },

  saveModel: function(){
    this.model.save();
  }

});

module.exports = General;
POS.attach('SettingsApp.General.Route', General);