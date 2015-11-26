# coding: utf8
from __future__ import unicode_literals

import flask

from soundcomparisons import config
from soundcomparisons import dataInfo
from soundcomparisons.db import db
from soundcomparisons import downloads
from soundcomparisons import oauth
from soundcomparisons import projectPages
from soundcomparisons import shortLink
from soundcomparisons import templateInfo
from soundcomparisons import translationInfo


# Delivers the index page.
def getIndex():
    data = {
        'title': 'TEST ME!',
        'requirejs': 'static/js/App.js' if config.debug else 'static/js/App-minified.js'
    }
    return flask.render_template(
        'index.html',
        title='TEST ME!',
        requirejs='static/js/App.js' if config.debug else 'static/js/App-minified.js')

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
    '/export/singleSoundFile': downloads.singleSoundFile
}

app = flask.Flask(__name__)
app.config['SQLALCHEMY_DATABASE_URI'] = config.dbURI
db.init_app(app)

# Binding routes:
for r, f in routes.items():
    if isinstance(f, tuple):
        app.route(r, methods=f[1])(f[0])
    else:
        app.route(r)(f)

# Loading & applying configuration:
app.debug = config.debug
app.secret_key = config.getSecretKey()
app.passthrough_errors = True
