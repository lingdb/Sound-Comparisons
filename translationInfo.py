# -*- coding: utf-8 -*-
'''
    This module aims to provide translation data in a JSON encoded format.
'''

import flask
import sqlalchemy

import db

#FIXME DESCRIBE
def getSummary():
    tMap = {}#TranslationId -> Page_Translations.toDict()
    ts = db.getSession().query(db.Page_Translations).filter(
            sqlalchemy.or_(
            db.Page_Translations.Active == 1,
            db.Page_Translations.TranslationId == 1)
        ).all()
    for t in ts:
        tMap[str(t.TranslationId)] = t.toDict()
    return flask.jsonify(**tMap)

#FIXME DESCRIBE
def getStatic():
    #FIXME IMPLEMENT
    return 'getStatic'

#FIXME DESCRIBE
def getDynamic():
    #FIXME IMPLEMENT
    return 'getDynamic'

#FIXME DESCRIBE
def getI18n():
    #FIXME IMPLEMENT
    return 'getI18n'

'''
    @param app instance of Flask
    Installs the translationInfo module to the /query/translations route,
    from where it serves GET requests.
    It accepts parameters:
    * summary
    * static
    * dynamic
    * i18n
'''
def addRoute(app):
    @app.route('/query/translations')
    def getTranslations():
        jumpMap = {
            'summary': getSummary,
            'static': getStatic,
            'dynamic': getDynamic,
            'i18n': getI18n
            }
        #Executing specified action, iff possible:
        if 'action' in flask.request.args:
            action = flask.request.args['action']
            if action in jumpMap:
                return jumpMap[action]()
        #Fallback in case of error:
        return flask.jsonify(**{
            'msg': '"action" parameter must be specified, carrying one of the action values.',
            'action': jumpMap.keys()
            })
