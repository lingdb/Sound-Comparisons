"use strict";
define(['views/render/SubView', 'models/Loader', 'markdown-it'],
       function(SubView, Loader, MarkdownIt){
  return SubView.extend({
    /***/
    initialize: function(){
      this.model = {
        pages: {}
      , currentPage: ''
      , mdProcessor: new MarkdownIt()
      };
      //Connecting to the router
      App.router.on('route:aboutView', this.route, this);
    }
    /**
      Method to make it possible to check what kind of PageView this SubView is.
    */
  , getKey: function(){return 'aboutView';}
    /**
      @param page string
    */
  , route: function(page){
      //Log what we're doing:
      console.log('AboutView.route('+page+')');
      //Set currentPage and do some stuff:
      this.model.currentPage = page;
      App.pageState.setPageView(this);
      App.views.renderer.render();
    }
    /***/
  , render: function(){
      var page = this.model.currentPage;
      //Skip if page is not a string:
      if(!_.isString(page)) return;
      //Test if we need to load the page:
      if(!(page in this.model.pages)){
        var view = this;
        Loader.github.about(page).done(function(data){
          view.model.pages[page] = data;
        }).fail(function(){
          view.model.pages[page] = [
            '# Error'
          , ''
          , 'Sorry, we failed to load the about page.'
          , 'Maybe you have more luck looking at '
          + '[GitHub](https://github.com/lingdb/Sound-Comparisons/wiki/'+page+').'
          ].join('\n');
        }).always(function(){
          //Rendering after load:
          view.render();
        });
      }else{
        var data = this.model.pages[page]
          , html = '<h3>'+page.replace(/-/g,' ')+'</h3>'
                 + this.model.mdProcessor.render(data);
        this.$el.html(html).removeClass('hide');
      }
    }
  });
});
