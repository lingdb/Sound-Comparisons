# -*- coding: utf-8 -*-
'''
    Tries to reproduce the thing the site currently does when fetching
    /query/templateInfo
    which is delivering a JSON object mapping all mustache templates to their md5 hashes.
    @author JR
'''

import os
import hashlib
import flask

import soundcomparisons


def getMustacheDir():
    '''
        Relative location of mustache templates
    '''
    return os.path.join(os.path.dirname(soundcomparisons.__file__), 'static', 'mustache')


def md5(fname):
    '''
        @param fname String
        @return sum String
        Compute the md5sum of a file
        by reading it into memory in chunks of 4096 bytes.
    '''
    hash = hashlib.md5()
    with open(fname, "rb") as f:
        for chunk in iter(lambda: f.read(4096), b""):
            hash.update(chunk)
    return hash.hexdigest()


def getTemplateInfo():
    '''
          @param route String
          @return dict(route+fileName => md5(filename))
          Iterates all files in mustacheDir and creates a dict
          that maps route+fileName to the md5sum of the file at getMustacheDir()+fileName
    '''
    dir = getMustacheDir()
    templateMap = {}
    for fn in os.listdir(dir):
        fname = 'static/mustache/%s' % fn
        templateMap[fname] = md5(os.path.join(dir, fn))
    return templateMap


def returnTemplateInfo():
    '''
        @param app instance of Flask
        @param queryRoute String
        @param templateRoute String
        Attaches templateInfo logic to queryRoute providing templates at templateRoute.
    '''
    return flask.jsonify(**getTemplateInfo())


# Produce test output:
if __name__ == "__main__":
    print(getTemplateInfo(getMustacheDir()))
