# See https://github.com/jrburke/r.js/blob/master/build/example.build.js
JS_FILES = $(shell find -type f -name "*.js" | grep -v App-minified.js)

App-minified.js: $(JS_FILES)
	node extern/r.js -o build.js

main: App-minified.js

chkSyntax:
	find -type f -regex .*js | grep -v extern | xargs jshint
