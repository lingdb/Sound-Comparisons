/**
  The LanguageMenuView will be used by the Renderer.
  It will set it's own model and handle it similar to TopMenuView.
*/
LanguageMenuView = Backbone.View.extend({
  initialize: function(){
    //Initial model:
    this.model = {
      collapseHref: function(){return 'href="'+App.router.linkConfig({Regions: []})+'"';}
    , expandHref:   function(){return 'href="'+App.router.linkConfig({Regions: App.regionCollection})+'"';}
    };
  }
  /**
    Activates callbacks to build parts of the model, which shall not be produced by /^update.+/ methods.
  */
, activate: function(){
    //Setting callbacks to update model:
    App.translationStorage.on('change:translationId', this.buildStatic, this);
    //Building statics:
    this.buildStatic();
  }
  /**
    Builds the static translations, and is, in contrast to /^update.+/ methods,
    only called on activate and change of translationId.
  */
, buildStatic: function(){
    var staticT = App.translationStorage.translateStatic({
      headline:      'menu_regions_headline'
    , languageSets:  'menu_regions_languageSets_title'
    , collapseTitle: 'menu_regions_languageSets_collapse'
    , expandTitle:   'menu_regions_languageSets_expand'
    });
    staticT.languageSets += ':';
    this.setModel(staticT);
  }
  /**
    Builds the complete tree of [families ->] regions -> languages
  */
, updateTree: function(){
    if(App.study.getColorByFamily()){
      var families = [], fCol = App.familyCollection;
      App.familyCollection.each(function(f){
        //Checking if we got regions:
        var regions = f.getRegions();
        if(regions.length === 0) return;
        var selected = fCol.isSelected(f)
          , data = { // Basic information for a family
              name:  f.getName()
            , color: f.getColor()
            , checkbox: {
                icon: 'icon-chkbox-custom'
              }
            };
        //Link building:
        var fCol = App.familyCollection
          , fams = (selected)
                 ? fCol.getDifference(fCol.getSelected(), [f])
                 : fCol.getUnion(fCol.getSelected(), [f]);
        data.link = 'href="'+App.router.linkConfig({Families: fams})+'"'
        //Checkbox info:
        var languages = f.getLanguages(), lCol = App.languageCollection;
        switch(lCol.areSelected(languages)){
          case 'all':
            var removed = lCol.getDifference(lCol.getSelected(), languages);
            data.checkbox.icon = 'icon-check';
            data.checkbox.href = 'href="'+App.router.linkCurrent({languages: removed})+'"';
            data.checkbox.ttip = App.translationStorage.translateStatic('multimenu_tooltip_del_family');
          break;
          case 'some':
            data.checkbox.icon = 'icon-chkbox-half-custom';
          case 'none':
            var additional = lCol.getUnion(lCol.getSelected(), languages);
            data.checkbox.href = 'href="'+App.router.linkCurrent({languages: additional})+'"';
            data.checkbox.ttip = App.translationStorage.translateStatic('multimenu_tooltip_add_family');
        }
        //The RegionList:
        if(selected){
          data.RegionList = this.buildRegionTree(regions);
        }
        //Finish:
        families.push(data);
      }, this);
      this.setModel({families: families});
    }else{
      this.setModel({RegionList: this.buildRegionTree(App.regionCollection)});
    }
  }
  /**
    Helperfunction for updateTree that builds a RegionList for a given collection of regions.
  */
, buildRegionTree: function(regions){
    var regionList = {
      isDl: !App.study.getColorByFamily()
    , regions: []
    };
    regions.each(function(r){
      var languages = r.getLanguages();
      if(languages.length === 0){
        console.log('Found region with no languages.');
        return;
      }
      var isMultiView = App.pageState.isMultiView()
        , isMapView   = App.pageState.isMapView()
        , region      = {
            selected: App.regionCollection.isSelected(r)
          , name: r.getShortName()
          , ttip: r.getLongName()
          , languages: []
          };
      //Filling the checkbox:
      if(isMultiView||isMapView){
        var box = {icon: 'icon-chkbox-custom'}, lCol = App.languageCollection;
        switch(lCol.areSelected(languages)){
          case 'all':
            var removed = lCol.getDifference(lCol.getSelected(), languages);
            box.icon = 'icon-check';
            box.href = 'href="'+App.router.linkCurrent({languages: removed})+'"';
            box.ttip = App.translationStorage.translateStatic('multimenu_tooltip_minus');
          break;
          case 'some':
            box.icon = 'icon-chkbox-half-custom';
          case 'none':
            var additional = lCol.getUnion(lCol.getSelected(), languages);
            box.href = 'href="'+App.router.linkCurrent({languages: additional})+'"';
            box.ttip = App.translationStorage.translateStatic('multimenu_tooltip_plus');
        }
        region.checkbox = box;
      }
      //The color:
      if(regionList.isDl){
        region.color = r.getColor();
      }
      //The link:
      var rCol = App.regionCollection
        , rgs  = region.selected
               ? rCol.getDifference(rCol.getSelected(), [r])
               : rCol.getUnion(rCol.getSelected(), [r]);
      region.link = 'href="'+App.router.linkConfig({Regions: rgs})+'"';
      //The triangle:
      region.triangle = region.selected
                      ? 'icon-chevron-down'
                      : 'icon-chevron-up rotate90';
      //Languages for selected Regions:
      if(region.selected){
        var lCol = App.languageCollection;
        languages.each(function(l){
          var language = {
            shortName: l.getShortName()
          , longName:  l.getLongName()
          , link:      'href="'+App.router.linkLanguageView({language: l})+'"'
          };
          //Deciding if the language is selected:
          if(isMultiView||isMapView){
            language.selected = lCol.isSelected(l);
          }else if(App.pageState.isPageView('l')){
            language.selected = lCol.isChoice(l);
          }else{
            language.selected = false;
          }
          //TODO implement flags if wanted!
          //language.flag = l.getFlag();
          //Building the icon for a language:
          if(isMultiView||isMapView){
            var icon = {
              checked: language.selected ? 'icon-check' : 'icon-chkbox-custom'
            , ttip: language.longName+"\n"
            };
            if(language.selected){
              if(isMapView){
                icon.ttip += App.translationStorage.translateStatic('multimenu_tooltip_del_map');
              }else{
                icon.ttip += App.translationStorage.translateStatic('multimenu_tooltip_del');
              }
              var removed = lCol.getDifference(lCol.getSelected(), [l]);
              icon.href = 'href="'+App.router.linkCurrent({languages: removed})+'"';
            }else{
              if(isMapView){
                icon.ttip += App.translationStorage.translateStatic('multimenu_tooltip_add_map');
              }else{
                icon.ttip += App.translationStorage.translateStatic('multimenu_tooltip_add');
              }
              var additional = lCol.getUnion(lCol.getSelected(), [l]);
              icon.href = 'href="'+App.router.linkCurrent({languages: additional})+'"';
            }
            language.icon = icon;
          }
          //Finish:
          region.languages.push(language);
        }, this);
      }
      //Finish:
      regionList.regions.push(region);
    }, this);
    return regionList;
  }
, render: function(){
    this.$el.html(App.templateStorage.render('LanguageMenu', {LanguageMenu: this.model}));
  }
  /**
    Basically the same as TopMenuView:setModel,
    this overwrites the current model with the given one performing a deep merge.
  */
, setModel: function(m){
    this.model = $.extend(true, this.model, m);
  }
});
