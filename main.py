# -*- coding: utf-8 -*-
import flask

import config
import db
import dataInfo
import projectPages
import templateInfo
import translationInfo

app = db.app

# Putting templateInfo into place.
templateInfo.addRoutes(app,'/query/templateInfo')

# query/data routes…
dataInfo.addRoute(app)

# query/translations
translationInfo.addRoute(app)

# projects/<magic> routes…
projectPages.addRoute(app)

# index route:
@app.route('/')
def getIndex():
    data = {
            'title': 'TEST ME!',
            'requirejs': 'static/js/App-minified.js'
        }
    if config.debug: data['requirejs'] = 'static/js/App.js'
    return flask.render_template('index.html', **data)

if __name__ == "__main__":
    import config
    app.debug = config.debug
    app.run()
