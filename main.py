# -*- coding: utf-8 -*-
import flask

import config
import db
import dataInfo
import projectPages
import templateInfo
import translationInfo
import shortLink

app = db.app

# Delivers the index page.
def getIndex():
    data = {
            'title': 'TEST ME!',
            'requirejs': 'static/js/App-minified.js'
        }
    if config.debug: data['requirejs'] = 'static/js/App.js'
    return flask.render_template('index.html', **data)

'''
    Routes is a dictionary used to set up all routing for soundcomparisons.
'''
routes = {
        '/': getIndex,
        '/query/shortLink': shortLink.addShortLink,
        '/projects/<path:url>': projectPages.checkUrl,
        '/query/translations': translationInfo.getTranslations,
        '/query/data': dataInfo.getData,
        '/query/templateInfo': templateInfo.returnTemplateInfo
    }

if __name__ == "__main__":
    # Binding routes:
    for r,f  in routes:
        app.route(r)(f)
    # Loading & applying configuration:
    import config
    app.debug = config.debug
    # Go:
    app.run()
