# -*- coding: utf-8 -*-
'''
    Provide the download endpoints given by code in php/export/*php files.
'''

import flask
import re
import os.path
import base64

'''
    Build after the preimage of php/export/singleSoundFile.php
'''
def singleSoundFile():
    # Making sure file parameter exists:
    args = flask.request.args
    if 'file' in args:
        file = args['file']
        # Preventing path traversal:
        test1 = re.match('\.\.', file)
        test2 = re.match('static/.*', file)
        test3 = re.match('\/\/', file)
        if test2 != None and test1 == None and test3 == None:
            # Modeled after https://stackoverflow.com/a/23354496/448591
            # this is not as fast as redirecting, but does the job.
            # Code to redirect:
            # return flask.redirect(file, code=302)
            filename = os.path.basename(file)
            headers = {"Content-Disposition": "attachment; filename=%s" % filename}
            with open(file, 'r') as f:
                body = f.read()
                # Encode base64 iff requested:
                if 'base64' in args:
                    body = base64.standard_b64encode(body)
            # Build and return response:
            response = flask.make_response(body)
            response.headers["Content-Disposition"] = "attachment; filename=%s" % filename
            return response
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
