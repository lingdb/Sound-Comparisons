# -*- coding: utf-8 -*-
import flask

import config
import dataInfo
import db
import oauth
import projectPages
import shortLink
import templateInfo
import translationInfo

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
        '/query/templateInfo': templateInfo.returnTemplateInfo,
        '/login': oauth.show_login,
        '/logout': oauth.show_logout,
        '/google_login': oauth.google_login,
        '/google_logout': oauth.google_logout
    }

if __name__ == "__main__":
    # Binding routes:
    for r, f in routes.iteritems():
        app.route(r)(f)
    # Loading & applying configuration:
    import config
    app.debug = config.debug
    app.secret_key = config.getSecretKey()
    app.passthrough_errors=True
    # Go:
    app.run()
