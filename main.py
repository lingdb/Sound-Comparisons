# -*- coding: utf-8 -*-
import flask

import db
import dataInfo
import redirectStatic
import templateInfo
import translationInfo

app = db.app

# Simple hello world at root:
@app.route("/")
def hello():
    return "Hello World!"

# Putting templateInfo into place.
templateInfo.addRoutes(app,'/query/templateInfo')

# Redirect currently expected static files from toplevel:
redirectStatic.addRoutes(app)

# query/data routes…
dataInfo.addRoute(app)

# query/translations
translationInfo.addRoute(app)

if __name__ == "__main__":
    app.debug = True
    app.run()
