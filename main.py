import flask

import templateInfo
import redirectStatic
import db

app = db.app

# Simple hello world at root:
@app.route("/")
def hello():
    return "Hello World!"

# Putting templateInfo into place.
templateInfo.addRoutes(app,'/query/templateInfo')

# Redirect currently expected static files from toplevel:
redirectStatic.addRoutes(app)

#FIXME some test:
import dataInfo
dataInfo.addRoute(app)

if __name__ == "__main__":
    app.debug = True
    app.run()
