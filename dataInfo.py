'''
    FIXME DESCRIBE
'''

import flask

import db

def addRoute(app):
    @app.route('/data')
    def giveData():
        xs = db.db.session.query(db.EditImport).all()
        dicts = []
        for x in xs:
            dicts.append(x.toDict())
        return flask.jsonify(*dicts)
