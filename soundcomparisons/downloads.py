# -*- coding: utf-8 -*-
'''
    Provide the download endpoints given by code in php/export/*php files.
'''

import flask
import re

'''
    Build after the preimage of php/export/singleSoundFile.php
'''
def singleSoundFile():
    # Making sure file parameter exists:
    if 'file' in flask.request.args:
        file = flask.request.args['file']
        # Preventing path traversal:
        test1 = re.match('\.\.', file)
        test2 = re.match('static/.*', file)
        if test2 != None and test1 == None:
            # Instead of the old download we should be able to simply redirect to the static file.
            return flask.redirect(file, code=302)
        else:
            return 'Sorry, I cannot serve this file.', 403
    else:
        return 'GET parameter "file" missing.', 400

'''
    Build after the preimage of php/export/singleTextFile.php
'''
def singleTextFile():
    pass

'''
    Build after the preimage of php/export/csv.php
'''
def buildCSV():
    # FIXME check where this is used!
    pass
