/**
  The Configurator provides means to build configuration such as used by the Linker.
*/
Configurator = Sanitizer.extend({
  /**
    This method shall modify different page settings that can be conveyed via the config routes.
  */
  configure: function(config){
    console.log('Router.configure()');
    //Sanitizing config:
    if(_.isString(config)){
      config = $.parseJSON(config);
    }
    //Configuring the wordOrder:
    if('wordOrder' in config){
      var callMap = { alphabetical: 'wordOrderSetAlphabetical'
                    , logical:      'wordOrderSetLogical'};
      if(config.wordOrder in callMap){
        App.pageState[callMap[config.wordOrder]]();
      }else{
        console.log('Could not configure wordOrder: '+config.wordOrder);
      }
    }
    if('spLang' in config){
      var spLang = App.languageCollection.find(function(l){
        return l.getKey() === config.spLang;
      }, this);
      App.pageState.setSpLang(spLang);
    }
    if('phLang' in config){
      var phLang = App.languageCollection.find(function(l){
        return l.getKey() === config.phLang;
      }, this);
      App.pageState.setPhLang(phLang);
    }
    if('meaningGroups' in config){
      var lookup = {};
      _.each(config.meaningGroups, function(m){lookup[m] = true;});
      var mgs = App.meaningGroupCollection.filter(function(m){
        return m.getKey() in lookup;
      }, this);
      App.meaningGroupCollection.setSelected(mgs);
    }
    //FIXME Add other configuration cases.
  }
  /**
    Takes a calls Object that maps Suffixes to args,
    where Suffix is the match of /configSet(.+)/ for methods of Router,
    and args will be applied to the Router method,
    with the config as first argument.
    If args is not an array, but a single value,
    that value will be wrapped in an array,
    and, after prepending the config, become the second argument.
    Router:sanitize works in a similar fashion.
  */
, configSet: function(calls, config){
    config = config || {};
    _.each(calls, function(args, suffix){
      var method = 'configSet'+suffix;
      if(method in this){
        if(!_.isArray(args)){
          args = [args];
        }
        args.unshift(config);
        config = this[method].apply(this, args);
      }else{
        console.log('Router:configSet() cannot call method: '+method);
      }
    }, this);
    return config;
  }
  /**
    Builds configuration to set the WordOrder managed by PageState to alphabetical.
  */
, configSetWordOrderAlphabetical: function(config){
    config = config || {};
    config.wordOrder = 'alphabetical';
    return config;
  }
  /**
    Builds configuration to set the WordOrder managed by PageState to logical.
  */
, configSetWordOrderLogical: function(config){
    config = config || {};
    config.wordOrder = 'logical';
    return config;
  }
  /**
    Builds configuration to set the spLang to the given spLang.
  */
, configSetSpLang: function(config, spLang){
    config = config || {};
    if(spLang instanceof Language){
      config.spLang = spLang.getKey();
    }
    return config;
  }
  /**
    Builds configuration to set the phLang to the given phLang.
  */
, configSetPhLang: function(config, phLang){
    config = config || {};
    if(phLang instanceof Language){
      config.phLang = phLang.getKey();
    }
    return config;
  }
  /***/
, configSetMeaningGroups: function(config, mgs){
    config = config || {};
    if(mgs instanceof MeaningGroupCollection){
      mgs = mgs.models;
    }
    if(_.isArray(mgs)){
      var ms = _.map(mgs, function(mg){
        if(_.isString(mg)) return mg;
        return mg.getKey();
      }, this);
      config.meaningGroups = JSON.stringify(ms);
    }
    return config;
  }
});
