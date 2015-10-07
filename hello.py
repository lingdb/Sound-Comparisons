import flask

import templateInfo

app = flask.Flask(__name__)

@app.route("/")
def hello():
    return "Hello World!"

templateInfo.addRoutes(app,'/query/templateInfo','/templates/')

if __name__ == "__main__":
    app.debug = True
    app.run()
