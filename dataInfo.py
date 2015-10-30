# -*- coding: utf-8 -*-
'''
    This module is concerned with delivering chunks of data from the database
    encoded as JSON so that the soundcomparisons client side JavaScript can deal with it.
'''

import flask
import sqlalchemy

import db

'''
    @param model (db.Model, SndCompModel)
    @return [{}] A list of Dicts
    Fetch all models and make them a list of ditcs.
'''
def dictAll(model):
    return [m.toDict() for m in db.getSession().query(model).all()]

'''
    Replies with a JSON encoded chunk of the database that is independant of the study.
'''
def getGlobal():
    # Structure to fill up:
    data = {
        'studies': [s.Name for s in db.getSession().query(db.Studies).all()]
    ,   'global': {
            'soundPath': 'static/sound'
        ,   'shortLinks': {l.Name: l.Target for l in db.getSession().query(db.ShortLinks).all()}
        ,   'contributors': dictAll(db.Contributors)
        ,   'contributorCategories': dictAll(db.ContributorCategories)
        ,   'flagTooltip': dictAll(db.FlagTooltip)
        ,   'languageStatusTypes': dictAll(db.LanguageStatusTypes)
        ,   'meaningGroups': dictAll(db.MeaningGroups)
        ,   'transcrSuperscriptInfo': dictAll(db.TranscrSuperscriptInfo)
        ,   'transcrSuperscriptLenderLgs': dictAll(db.TranscrSuperscriptLenderLgs)
        ,   'wikipediaLinks': dictAll(db.WikipediaLinks)
        }
    }
    # Return stuff encoded as JSON:
    return flask.jsonify(**data)

'''
    @param studyName String
    @throws sqlalchemy.orm.exc.NoResultFound if studyName not found.
    Replies with a JSON encoded, study dependant chunk of the database.
'''
def getStudy(studyName):
    # Study to fetch stuff for:
    study = db.getSession().query(db.Studies).filter_by(Name = studyName).limit(1).one()
    # Helper to remove StudyName from dicts:
    def filterDicts(xs):
        ys = []
        for x in xs:
            d = x.toDict()
            d.pop('StudyName',None)
            ys.append(d)
        return ys
    # Structure to fill up:
    data = {
        'study': study.toDict()
    ,   'families': dictAll(db.Families)
    ,   'regions': filterDicts(study.Regions)
    ,   'regionLanguages': filterDicts(study.RegionLanguages)
    ,   'languages': filterDicts(study.Languages)
    ,   'words': filterDicts(study.Words)
    ,   'meaningGroupMembers': [m.toDict() for m in study.MeaningGroupMembers]
    ,   'transcriptions': filterDicts(study.Transcriptions)
    ,   'defaults': {
            'language': None
        ,   'word': None
        ,   'languages': [{'LanguageIx': l.LanguageIx} for l in study.DefaultMultipleLanguages]
        ,   'words': [{'IxElicitation': w.IxElicitation, 'IxMorphologicalInstance': w.IxMorphologicalInstance} for w in study.DefaultMultipleWords]
        ,   'excludeMap': [{'LanguageIx': l.LanguageIx} for l in study.DefaultLanguagesExcludeMap]
        }
    }
    # Single defaults:
    if len(study.DefaultLanguages) > 0:
        l = study.DefaultLanguages[0]
        data['defaults']['language'] = {'LanguageIx': l.LanguageIx}
    if len(study.DefaultWords) > 0:
        w = study.DefaultWords[0]
        data['defaults']['word'] = {'IxElicitation': w.IxElicitation, 'IxMorphologicalInstance': w.IxMorphologicalInstance}
    # Handling dummies:
    dummies = db.getDummyTranscriptions(study.Name)
    if len(dummies):
        data['transcriptions'] += dummies
    # Return stuff encoded as JSON:
    return flask.jsonify(**data)

'''
    Serves the dataInfo module to a route, usually '/query/data'.
    It accepts GET parameters:
    * global
    * study=<studyName>
'''
def getData():
    # Checking if global portion of data is requested:
    if 'global' in flask.request.args:
        return getGlobal()
    # Checking if study depandant portion of data is requested:
    if 'study' in flask.request.args:
        studyName = flask.request.args['study']
        if studyName == '':
            return 'You need to supply a value for that study parameter!'
        else:
            studyCount = db.getSession().query(db.Studies).filter_by(Name = studyName).limit(1).count()
            if studyCount == 1:
                return getStudy(studyName)
            else:
                return ("Couldn't find study: "+studyName)
    # Normal response in case of no get parameters:
    try:
        latest = db.getSession().query(db.EditImports).order_by(db.EditImports.Time.desc()).limit(1).one()
        dict = {
            'lastUpdate': latest.getTimeStampString(),
            'Description': 'Add a global parameter to fetch global data, and add a study parameter to fetch a study.'
        }
        return flask.jsonify(**dict)
    except sqlalchemy.orm.exc.NoResultFound:
        return flask.jsonify(**{})
