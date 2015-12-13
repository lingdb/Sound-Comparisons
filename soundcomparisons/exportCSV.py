# -*- coding: utf-8 -*-
'''
    This module replicates the php/export/csv.php functionality.
    Given GET parameters {study,languages,words} it shall provide
    a .csv file for download.
'''

import flask
import sqlalchemy

import db


def withParams(func):
    '''
        @param func function(
            'study'= db.Studies,
            'languages'= [db.Languages],
            'words'= [db.Words])
            -> flask response
        @return flask response
    '''
    params = ['study', 'languages', 'words']
    for p in params:
        if p not in flask.request.args:
            return 'GET parameters should be: ' + str(params)
    fetched = {}
    for p in params:
        try:
            val = flask.request.args[p]
            if p == 'study':
                fetched[p] = db.getSession().query(db.Studies).filter_by(Name=val).limit(1).one()
            else:
                val = val.split(',')
                if p == 'languages':
                    langs = []
                    for lIx in val:
                        query = db.getSession().query(db.Languages)
                        langs.append(query.filter_by(LanguageIx=lIx).limit(1).one())
                    fetched[p] = langs
                elif p == 'words':
                    words = []
                    for wId in val:
                        where = sqlalchemy.func.concat(
                            db.Words.IxElicitation, db.Words.IxMorphologicalInstance).like(wId)
                        words.append(db.getSession().query(db.Words).filter(where).limit(1).one())
                    fetched[p] = words
        except:
            return 'Could not fetch data for parameter: ' + p
    return func(**fetched)


def getCSV(study=None, languages=None, words=None):
    '''
        @param study db.Studies
        @param languages db.Languages
        @param words db.Words
    '''
    print('Here')
    pass
    return 'there!'

# Testing:
if __name__ == "__main__":
    app = db.app
    app.debug = True

    @app.route('/')  # Route to getCSV:
    def routeMe():
        return withParams(getCSV)
    # Run app:
    app.run()
