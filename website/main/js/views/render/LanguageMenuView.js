/**
  The LanguageMenuView will be used by the Renderer.
  It will set it's own model and handle it similar to TopMenuView.
*/
LanguageMenuView = Backbone.View.extend({
  initialize: function(){
    //Setting callbacks to update model:
    App.translationStorage.on('change:translationId', function(){
      this.updateStatic();
      this.updateTree();
    }, this);
    App.study.on('change', this.updateTree, this);
    App.familyCollection.on('reset', this.updateTree, this);
    App.regionCollection.on('reset', this.updateTree, this);
    App.languageCollection.on('reset', this.updateTree, this);
    //Initial model:
    this.model = {
      collapseHref: 'href="#FIXME/implement links for adding regions"'
    , expandHref:   'href="#FIXME/implement links for removing regions"'
    };
  }
  /***/
, updateStatic: function(){
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
      var families = [];
      App.familyCollection.each(function(f){
        //Checking if we got regions:
        var regions = f.getRegions();
        if(regions.length === 0) return;
        var selected = App.familyCollection.isSelected(f)
          , data = { // Basic information for a family
              name:  f.getName()
            , color: f.getColor()
            , link:  selected ? 'href="#FIXME/implement removing families"'
                              : 'href="#FIXME/implement adding families"'
            , checkbox: {
                icon: 'icon-chkbox-custom'
              }
            };
        //Checkbox info:
        var languages = f.getLanguages();
        switch(App.languageCollection.areSelected(languages)){
          case 'all':
            data.checkbox.icon = 'icon-check';
            data.checkbox.href = 'href="#FIXME/implement removing languages"';
            data.checkbox.ttip = App.translationStorage.translateStatic('multimenu_tooltip_del_family');
          break;
          case 'some':
            data.checkbox.icon = 'icon-chkbox-half-custom';
          case 'none':
            data.checkbox.href = 'href="#FIXME/implement adding languages"';
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
      if(languages.length === 0)
        return;
      var isMultiView = false // FIXME implement
        , isMapView   = false // FIXME implement
        , region      = {
            selected: App.regionCollection.isSelected(r)
          , name: r.getShortName()
          , ttip: r.getLongName()
          , languages: []
          };
      //Filling the checkbox:
      if(isMultiView||isMapView){
        var box = {icon: 'icon-chkbox-custom'};
        switch(App.languageCollection.areSelected(languages)){
          case 'all':
            box.icon = 'icon-check';
            box.href = 'href="#FIXME/implement removing languages"';
            box.ttip = App.translationStorage.translateStatic('multimenu_tooltip_minus');
          break;
          case 'some':
            box.icon = 'icon-chkbox-half-custom';
          case 'none':
            box.href = 'href="#FIXME/implement adding languages"';
            box.ttip = App.translationStorage.translateStatic('multimenu_tooltip_plus');
        }
        region.checkbox = box;
      }
      //The color:
      if(regionList.isDl){
        region.color = r.getColor();
      }
      //The link:
      region.link = region.selected
                  ? 'href="#FIXME/implement removing regions"'
                  : 'href="#FIXME/implement adding regions"';
      //The triangle:
      region.triangle = region.selected
                      ? 'icon-chevron-down'
                      : 'icon-chevron-up rotate90';
      //Languages for selected Regions:
      if(region.selected){
        languages.each(function(l){
          var language = {
            shortName: l.getShortName()
          , longName:  l.getLongName()
          , selected:  App.languageCollection.isSelected(l)
          , link:      'href="#FIXME/implement switching to a language"'
          };
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
              icon.href = 'href="#FIXME/implement removing a language"';
            }else{
              if(isMapView){
                icon.ttip += App.translationStorage.translateStatic('multimenu_tooltip_add_map');
              }else{
                icon.ttip += App.translationStorage.translateStatic('multimenu_tooltip_add');
              }
              icon.href = 'href="#FIXME/implement adding a language"';
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
    console.log('LanguageMenuView.render()');
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
