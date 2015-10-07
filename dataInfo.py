'''
    FIXME DESCRIBE
'''

import flask
import sqlalchemy

import db
from db import EditImport

def addRoute(app):
    @app.route('/query/data')
    def getData():
        # Checking if global portion of data is requested:
        if 'global' in flask.request.args:
            #FIXME supply global chunk!
            return 'A wild global was seen!'
        if 'study' in flask.request.args:
            study = flask.request.args['study']
            if study == '':
                return 'You need to supply a value for that study parameter!'
            else:
                #FIXME supply study chunk!
                return ('Requested study: '+study)
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
