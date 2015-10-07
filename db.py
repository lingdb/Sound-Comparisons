'''
    Playing with retrieving some stuff from the database
'''

import sqlalchemy
import flask
from flask.ext.sqlalchemy import SQLAlchemy

app = flask.Flask('Soundcomparisons')
app.config['SQLALCHEMY_DATABASE_URI'] = 'mysql://root:1234@localhost/v4'
db = SQLAlchemy(app)

# Model for v4.Edit_Imports table
class EditImport(db.Model):
    __tablename__ = 'Edit_Imports'
    who = sqlalchemy.Column('Who', sqlalchemy.Integer, nullable=False, primary_key=True)
    time = sqlalchemy.Column('Time', sqlalchemy.TIMESTAMP, primary_key=True)

    def toDict(self):
        return {'who': self.who, 'time': self.time}

    def getTimeStampString(self):
        return self.time.strftime('%s')

if __name__ == '__main__':
    session = db.session
    print session
    xs = session.query(EditImport).all()
    for x in xs:
        print x.toDict()
