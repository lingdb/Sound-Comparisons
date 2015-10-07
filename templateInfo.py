'''
    Tries to reproduce the thing the site currently does when fetching
    /query/templateInfo
    which is delivering a JSON object mapping all mustache templates to their md5 hashes.
    @author JR
'''

import os
import hashlib
import flask

'''
    Relative location of mustache templates
'''
def getMustacheDir():
    return 'static/mustache/'

'''
    @param fname String
    @return sum String
    Compute the md5sum of a file
    by reading it into memory in chunks of 4096 bytes.
'''
def md5(fname):
    hash = hashlib.md5()
    with open(fname, "rb") as f:
        for chunk in iter(lambda: f.read(4096), b""):
            hash.update(chunk)
    return hash.hexdigest()

'''
      @param route String
      @return dict(route+fileName => md5(filename))
      Iterates all files in mustacheDir and creates a dict
      that maps route+fileName to the md5sum of the file at getMustacheDir()+fileName
'''
def getTemplateInfo(route):
    templateMap = {}
    for fn in os.listdir(getMustacheDir()):
        file = getMustacheDir()+fn
        templateMap[route+fn] = md5(file)
    return templateMap

'''
    @param app instance of Flask
    @param queryRoute String
    @param templateRoute String
    Attaches templateInfo logic to queryRoute providing templates at templateRoute.
'''
def addRoutes(app, queryRoute, templateRoute):
    @app.route(queryRoute)
    def returnTemplateInfo():
        tInfo = getTemplateInfo(templateRoute)
        return flask.jsonify(**tInfo)

    @app.route(templateRoute+'<path:path>')
    def returnTemplate(path):
        return flask.send_from_directory(getMustacheDir(), path)

# Produce test output:
if __name__ == "__main__":
    print(getTemplateInfo(getMustacheDir()))
