# -*- coding: utf-8 -*-
'''
    Provide the download endpoints given by code in php/export/*php files.
'''

import flask
import re
import os.path
import base64
import sqlalchemy

import db

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
    Expected GET parameters are:
    * word, only containing digits of a wordId
    * language, only containing digits of a languageId
    * study, containing the name of a study
    * n, number of Phonetic entry to use, starts at 0
    Example route:
    /export/singleTextFile?word=7660&language=11111230301&study=Germanic&n=0
'''
def singleTextFile():
    args = flask.request.args
    # Checking parameters:
    params = ['word','language','study','n']
    for p in params:
        if p not in args:
            msg = "Missing parameter: '%s'! Parameters should be: %s" % (p, params)
            return msg, 400
    # Getting data to respond with:
    where = sqlalchemy.func.concat(
                db.Transcriptions.LanguageIx,
                db.Transcriptions.IxElicitation,
                db.Transcriptions.IxMorphologicalInstance
                ).like(args['language'] + args['word'])
    transcriptions = db.getSession().query(db.Transcriptions).filter_by(StudyName = args['study']).filter(where).all()
    # Picking the element:
    n = int(args['n'])
    if n < 0:
        return "Parameter 'n' must be >= 0!", 400
    if n > len(transcriptions):
        return ("Parameter 'n' cannot be >= %s for this query." % len(transcriptions)), 400
    transcription = transcriptions[n].toDict()
    # Building and returning response:
    name = "transcription.txt"
    if len(transcription['soundPaths']) > 0:
        path = transcription['soundPaths'][0]
        name = os.path.basename(path).replace('.ogg','.txt').replace('.mp3','.txt')
    response = flask.make_response(transcription['Phonetic'])
    response.headers["Content-Type"] = 'text/plain; charset=utf-8'
    response.headers["Content-Disposition"] = "attachment; filename=%s" % name
    return response

'''
    Build after the preimage of php/export/csv.php
    Expected GET parameters are:
    * study, containing the name of a study
    * languages, containing ',' delimited languageIds
    * words, containing ',' delimited wordIds
    Example route:
    export/csv?study=Slavic&languages=13111210507&words=10,20,30
'''
def buildCSV():
    args = flask.request.args
    # Checking parameters:
    params = ['study','languages','words']
    for p in params:
        if p not in args:
            msg = "Missing parameter: '%s'! Parameters should be: %s" % (p, params)
            return msg, 400
    # Querying database:
    lIds = [int(id) for id in args['languages'].split(',')]
    wIds = [int(id) for id in args['words'].split(',')]
    languages = db.getSession().query(db.Languages).filter_by(StudyName = args['study']).all() # FIXME filter by lIds
    words = db.getSession().query(db.Words).filter_by(StudyName = args['study']).all() # FIXME filter by lIds
    # FIXME check where this is used!
    thing = {'lIds': lIds, 'wIds': wIds}
    return flask.jsonify(thing)
