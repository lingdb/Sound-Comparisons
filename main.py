# -*- coding: utf-8 -*-
import flask

import config
import dataInfo
import db
import downloads
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
    if config.debug:
        data['requirejs'] = 'static/js/App.js'
    return flask.render_template('index.html', **data)

'''
    Routes is a dictionary used to set up all routing for soundcomparisons.
    Keys are the described routes.
    Values may be:
    - A function to call for the route
    - A tuple of a function and a list of strings,
      where the function will be called for the route,
      and the strings describe acceptable http methods.
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
        '/google_login': (oauth.google_login, ['POST']),
        '/google_logout': oauth.google_logout,
        '/export/singleSoundFile': downloads.singleSoundFile,
        '/export/singleTextFile': downloads.singleTextFile
    }

if __name__ == "__main__":
    # Binding routes:
    for r, f in routes.iteritems():
        if isinstance(f, tuple):
            app.route(r, methods=f[1])(f[0])
        else:
            app.route(r)(f)
    # Loading & applying configuration:
    import config
    app.debug = config.debug
    app.secret_key = config.getSecretKey()
    app.passthrough_errors = True
    # Go:
    app.run(host=config.host, port=config.port)
