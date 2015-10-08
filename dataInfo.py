'''
    This module is concerned with delivering chunks of data from the database
    encoded as JSON so that the soundcomparisons client side JavaScript can deal with it.
'''

import flask
import sqlalchemy

import db
from db import EditImport, Study, ShortLink

'''
    Gathers global information about the database and hands that to the client encoded as JSON.
    JSON Structure will be like this:
    {
        "studies": [<studyNames>]
    ,   "global": {
            "shortLinks": {Name: Target}
        ,   "soundPath": String
        ,   "contributors"                => "SELECT * FROM Contributors"
        ,   "contributorCategories"       => "SELECT * FROM ContributorCategories"
        ,   "flagTooltip"                 => "SELECT * FROM FlagTooltip WHERE FLAG != ''"
        ,   "languageStatusTypes"         => "SELECT * FROM LanguageStatusTypes"
        ,   "meaningGroups"               => "SELECT * FROM MeaningGroups"
        ,   "transcrSuperscriptInfo"      => "SELECT * FROM TranscrSuperscriptInfo"
        ,   "transcrSuperscriptLenderLgs" => "SELECT * FROM TranscrSuperscriptLenderLgs"
        ,   "wikipediaLinks"              => "SELECT * FROM WikipediaLinks"
        }
    }
'''
def getGlobal():
    # Fetch all models and make them a list of ditcs:
    def dictAll(model):
        ms = db.getSession().query(model).all()
        return [m.toDict() for m in ms]
    # Structure to fill up:
    data = {
        'studies': [s.Name for s in db.getSession().query(Study).all()]
    ,   'global': {
            'soundPath': 'static/sound'
        ,   'shortLinks': {l.Name: l.Target for l in db.getSession().query(ShortLink).all()}
        ,   'contributors': dictAll(db.Contributor)
        ,   'contributorCategories': dictAll(db.ContributorCategory)
        ,   'flagTooltip': dictAll(db.FlagTooltip)
        ,   'languageStatusTypes': dictAll(db.LanguageStatusType)
        ,   'meaningGroups': dictAll(db.MeaningGroup)
        ,   'transcrSuperscriptInfo': dictAll(db.TranscrSuperscriptInfo)
        ,   'transcrSuperscriptLenderLgs': dictAll(db.TranscrSuperscriptLenderLg)
        ,   'wikipediaLinks': []
        }
    }
    #FIXME IMPLEMENT
    # Return stuff encoded as JSON:
    return flask.jsonify(**data)

'''
    @param studyName String
    Replies with a JSON encoded, study dependant chunk of the database.
'''
def getStudy(studyName):
    # FIXME IMPLEMENT!
    return ('Found study: '+studyName)

'''
    @param app instance of Flask
    Installs the dataInfo module to the /query/data route,
    from where it serves GET requests.
    It accepts parameters:
    * global
    * study=<studyName>
'''
def addRoute(app):
    @app.route('/query/data')
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
                studyCount = db.getSession().query(Study).filter_by(Name = studyName).limit(1).count()
                if studyCount == 1:
                    return getStudy(studyName)
                else:
                    return ("Couldn't find study: "+studyName)
        # Normal response in case of no get parameters:
        try:
            latest = db.getSession().query(EditImport).order_by(EditImport.time.desc()).limit(1).one()
            dict = {
                'lastUpdate': latest.getTimeStampString(),
                'Description': 'Add a global parameter to fetch global data, and add a study parameter to fetch a study.'
            }
            return flask.jsonify(**dict)
        except sqlalchemy.orm.exc.NoResultFound:
            return flask.jsonify(**{})
