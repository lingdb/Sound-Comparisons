(function(){
  "use strict";
  /* global module */
  module.exports = function(grunt) {
    grunt.initConfig({
      eslint: {
        options: {
          configFile: '.eslintrc.js'
        },
        target: [
          "collections/*.js",
          "models/*.js",
          "views/*.js",
          "worker/*.js",
          "Linker.js",
          "Projects.js",
          "Router.js",
          "Sanitizer.js",
          "App.js",
          "Configurator.js",
          "build.js",
          "Gruntfile.js"]
      },
      requirejs: {
        compile: {
          options: {
            baseUrl: ".",
            mainConfigFile: "App.js",
            name: "App",
            include: ['bower_components/almond/almond.js'],
            out: "App-minified.js"
          }
        }
      },
      watch: {
        files: ['<%= eslint.files %>'],
        tasks: ['eslint', 'requirejs']
      }
    });

    grunt.loadNpmTasks('grunt-eslint');
    grunt.loadNpmTasks('grunt-contrib-requirejs');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('default', ['eslint', 'requirejs']);
  };
})();
