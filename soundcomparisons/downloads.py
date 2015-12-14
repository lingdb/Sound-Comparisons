# -*- coding: utf-8 -*-
from __future__ import unicode_literals
'''
    Provide the download endpoints given by code in php/export/*php files.
'''

import flask
import re
import os.path
import base64
import sqlalchemy
import datetime

import db


def singleSoundFile():
    '''
        Build after the preimage of php/export/singleSoundFile.php
    '''
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


def singleTextFile():
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
    args = flask.request.args
    # Checking parameters:
    params = ['word', 'language', 'study', 'n']
    for p in params:
        if p not in args:
            msg = "Missing parameter: '%s'! Parameters should be: %s" % (p, params)
            return msg, 400
    # Getting data to respond with:
    where = sqlalchemy.func.concat(
        db.Transcriptions.LanguageIx,
        db.Transcriptions.IxElicitation,
        db.Transcriptions.IxMorphologicalInstance).like(args['language'] + args['word'])
    query = db.getSession().query(db.Transcriptions)
    transcriptions = query.filter_by(StudyName=args['study']).filter(where).all()
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
        name = os.path.basename(path).replace('.ogg', '.txt').replace('.mp3', '.txt')
    response = flask.make_response(transcription['Phonetic'])
    response.headers["Content-Type"] = 'text/plain; charset=utf-8'
    response.headers["Content-Disposition"] = "attachment; filename=%s" % name
    return response


def buildCSV():
    '''
        Build after the preimage of php/export/csv.php
        Expected GET parameters are:
        * study, containing the name of a study
        * languages, containing ',' delimited languageIds
        * words, containing ',' delimited wordIds
        Example route:
        export/csv?study=Slavic&languages=13111210507&words=10,20,30
    '''
    args = flask.request.args
    # Checking parameters:
    params = ['study', 'languages', 'words']
    for p in params:
        if p not in args:
            msg = "Missing parameter: '%s'! Parameters should be: %s" % (p, params)
            return msg, 400
    # Querying languages:
    lIds = {int(id) for id in args['languages'].split(',')}
    languages = db.getSession().query(db.Languages).filter_by(
        StudyName=args['study']).filter(db.Languages.LanguageIx.in_(lIds)).all()
    # Querying words:
    wIds = {int(id) for id in args['words'].split(',')}
    words = db.getSession().query(db.Words).filter_by(StudyName=args['study']).all()
    words = [w for w in words if int(str(w.IxElicitation) + str(w.IxMorphologicalInstance)) in wIds]

    def quote(x):  # Compossing csv:
        return '"' + x + '"'
    head = ['LanguageId', 'LanguageName', 'Latitude', 'Longitude',
            'WordId', 'WordModernName1', 'WordModernName2', 'WordProtoName1', 'WordProtoName2',
            'Phonetic', 'SpellingAltv1', 'SpellingAltv2', 'NotCognateWithMainWordInThisFamily']
    csv = [[quote(h) for h in head]]
    for l in languages:
        lPart = [str(l.LanguageIx), quote(l.ShortName),
                 str(l.Latitude or ''), str(l.Longtitude or '')]
        for w in words:
            wPart = [str(w.IxElicitation) + str(w.IxMorphologicalInstance),
                     quote(w.FullRfcModernLg01), quote(w.FullRfcModernLg02),
                     quote(w.FullRfcProtoLg01), quote(w.FullRfcProtoLg02)]
            transcriptions = [t for t in w.Transcriptions if t.LanguageIx in lIds]
            for t in transcriptions:
                tPart = [quote(t.Phonetic), quote(t.SpellingAltv1),
                         quote(t.SpellingAltv2), str(t.NotCognateWithMainWordInThisFamily)]
                csv.append(lPart + wPart + tPart)
    # Transform csv to string:
    csv = "\n".join([','.join(line) for line in csv])
    # filename to use:
    filename = 'Customexport_%s.csv' % datetime.datetime.utcnow().isoformat()
    # Build and return response:
    response = flask.make_response(csv)
    response.headers["Pragma"] = "public"
    response.headers["Expires"] = "0"
    response.headers["Cache-Control"] = "must-revalidate, post-check=0, pre-check=0"
    response.headers["Content-Type"] = "text/csv; charset=utf-8"
    response.headers["Content-Disposition"] = ('attachment;filename="%s"' % filename)
    response.headers["Content-Transfer-Encoding"] = "binary"
    return response
