# -*- coding: utf-8 -*-
from __future__ import unicode_literals
'''
    This module aims to provide translation data in a JSON encoded format.
'''

import flask
import sqlalchemy
from collections import defaultdict

import db


def getSummary():
    '''
        @return flask reply
        Provides a JSON encoded summary of available translations to the client.
    '''
    tMap = {}  # TranslationId -> Page_Translations.toDict()
    ts = db.getSession().query(db.Page_Translations).filter(sqlalchemy.or_(
        db.Page_Translations.Active == 1, db.Page_Translations.TranslationId == 1)).all()
    for t in ts:
        tMap[str(t.TranslationId)] = t.toDict()
    return flask.jsonify(**tMap)


def chkTranslationId(func):
    '''
        @param func Page_Translation -> flask reply
        @return flask reply
        Check the existence of a translationId parameter.
        If the parameter exists and the corresponding instance of Page_Translations
        can be fetched, the given func is called and it's result returned.
        Otherwise a JSON encoded error message is returned.
    '''
    return func(db.getSession()
                .query(db.Page_Translations)
                .filter_by(TranslationId=flask.request.args['translationId'])
                .one())


def getStatic(translation):
    '''
        @param translation Page_Translations
        @return flask reply
        @deprecated since i18n this most likely isn't used any more.
        Returns JSON encoded data for dynamic translation.
    '''
    # FIXME remove due to deprecation at once.
    static = {}
    for s in translation.Page_StaticTranslation:
        static[s.Req] = s.Trans
    return flask.jsonify(**static)


def getDynamic(translation):
    '''
        @param translation Page_Translations
        @return flask reply
        @deprecated since i18n this most likely isn't used any more.
        Returns JSON encoded data for dynamic translation.
        This implementation varies from the website PHP one.
    '''
    # FIXME remove due to deprecation at once.
    dynamic = defaultdict(lambda: defaultdict(dict))
    for d in translation.Page_DynamicTranslation:
        dynamic[d.Category][d.Field] = d.Trans
    return flask.jsonify(**dynamic)


def getI18n(lngs):
    '''
        @param lngs [String]
        @return flask reply
        Returns the JSON encoded data for translation that can be consumed by i18n client side.
    '''
    i18n = defaultdict(dict)
    for l in lngs:
        translation = db.getSession()\
            .query(db.Page_Translations)\
            .filter_by(BrowserMatch=l)\
            .one_or_none()
        if translation:
            tDict = {s.Req: s.Trans for s in translation.Page_StaticTranslation}
            for d in translation.Page_DynamicTranslation:
                tDict[d.Category + d.Field] = d.Trans
            i18n[l]['translation'] = tDict
    return flask.jsonify(**i18n)


def getTranslations():
    '''
        Handles the translationInfo requests,
        and is usually installed at /query/translations.
        Serves GET requests, and accepts parameters:
        * action in {summary,static,dynamic}
        * lng = ' '.join([String])
    '''
    jumpMap = {
        'summary': getSummary,
        'static': lambda: chkTranslationId(getStatic),
        'dynamic': lambda: chkTranslationId(getDynamic)}
    # Executing specified action, iff possible:
    if 'action' in flask.request.args:
        action = flask.request.args['action']
        if action in jumpMap:
            return jumpMap[action]()
    if 'lng' in flask.request.args:
        lngs = flask.request.args['lng'].split(' ')
        return getI18n(lngs)
    # Fallback in case of error:
    return flask.jsonify(**{
        'msg': '"action" parameter must be specified, '
               'carrying one of the action values, '
               'or a lng parameter must be given.',
        'action': jumpMap.keys()})
