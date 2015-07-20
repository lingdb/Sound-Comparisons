# See https://github.com/jrburke/r.js/blob/master/build/example.build.js
JS_FILES = $(filter-out App-minified.js, $(wildcard *js))

App-minified.js: $(JS_FILES)
	node extern/r.js -o build.js

main: App-minified.js
