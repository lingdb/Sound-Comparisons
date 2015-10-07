import flask

import templateInfo
import redirectStatic

app = flask.Flask(__name__)

# Simple hello world at root:
@app.route("/")
def hello():
    return "Hello World!"

# Putting templateInfo into place.
templateInfo.addRoutes(app,'/query/templateInfo','/templates/')

# Redirect currently expected static files from toplevel:
redirectStatic.addRoutes(app)

if __name__ == "__main__":
    app.debug = True
    app.run()
