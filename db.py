'''
    Playing with retrieving some stuff from the database
'''

import sqlalchemy
from sqlalchemy import Column, String, Integer
from sqlalchemy.dialects.mysql import TINYINT, DOUBLE, TIMESTAMP, TEXT, BIGINT, INTEGER
import flask
from flask.ext.sqlalchemy import SQLAlchemy

app = flask.Flask('Soundcomparisons')
app.config['SQLALCHEMY_DATABASE_URI'] = 'mysql://root:1234@localhost/v4'
db = SQLAlchemy(app)

'''
+-------+---------------------+------+-----+-------------------+
| Field | Type                | Null | Key | Default           |
+-------+---------------------+------+-----+-------------------+
| Who   | bigint(20) unsigned | NO   |     | NULL              |
| Time  | timestamp           | NO   |     | CURRENT_TIMESTAMP |
+-------+---------------------+------+-----+-------------------+
'''
# Model for v4.Edit_Imports table
class EditImport(db.Model):
    __tablename__ = 'Edit_Imports'
    who = Column('Who', BIGINT(20, unsigned=True), nullable=False, primary_key=True)
    time = Column('Time', TIMESTAMP, primary_key=True)

    def toDict(self):
        return {'who': self.who, 'time': self.time}

    def getTimeStampString(self):
        return self.time.strftime('%s')

'''
+-----------------------+---------------------+------+-----+---------+
| Field                 | Type                | Null | Key | Default |
+-----------------------+---------------------+------+-----+---------+
| StudyIx               | tinyint(3) unsigned | NO   | PRI | NULL    |
| FamilyIx              | tinyint(3) unsigned | NO   | PRI | NULL    |
| SubFamilyIx           | tinyint(3) unsigned | NO   | PRI | 0       |
| Name                  | varchar(255)        | NO   |     | NULL    |
| DefaultTopLeftLat     | double              | YES  |     | NULL    |
| DefaultTopLeftLon     | double              | YES  |     | NULL    |
| DefaultBottomRightLat | double              | YES  |     | NULL    |
| DefaultBottomRightLon | double              | YES  |     | NULL    |
| ColorByFamily         | tinyint(1) unsigned | NO   |     | 0       |
| SecondRfcLg           | varchar(255)        | NO   |     | NULL    |
+-----------------------+---------------------+------+-----+---------+
'''
# Model for v4.Studies table
class Study(db.Model):
    __tablename__ = 'Studies'
    studyIx = Column('StudyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    familyIx = Column('FamilyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    subFamilyIx = Column('SubFamilyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    name = Column('Name', String(255), nullable=False)
    defaultTopLeftLat = Column('DefaultTopLeftLat', DOUBLE)
    defaultTopLeftLon = Column('DefaultTopLeftLon', DOUBLE)
    defaultBottomRightLat = Column('DefaultBottomRightLat', DOUBLE)
    defaultBottomRightLon = Column('DefaultBottomRightLon', DOUBLE)
    colorByFamily = Column('ColorByFamily', TINYINT(1, unsigned=True), nullable=False)
    secondRfcLg  = Column('SecondRfcLg', String(255), nullable=False)

'''
+--------+-------------+------+-----+---------+
| Field  | Type        | Null | Key | Default |
+--------+-------------+------+-----+---------+
| Hash   | varchar(32) | NO   | PRI | NULL    |
| Name   | varchar(32) | NO   |     | NULL    |
| Target | text        | NO   |     | NULL    |
+--------+-------------+------+-----+---------+
'''
# Model for v4.Page_ShortLinks table
class ShortLink(db.Model):
    __tablename__ = 'Page_ShortLinks'
    hash = Column('Hash', String(32), nullable=False, primary_key=True)
    name = Column('Name', String(32), nullable=False)
    target = Column('Target', TEXT, nullable=False)

'''
+---------------------+---------------------+------+-----+---------+
| Field               | Type                | Null | Key | Default |
+---------------------+---------------------+------+-----+---------+
| ContributorIx       | bigint(20) unsigned | NO   | PRI | NULL    |
| SortGroup           | int(10) unsigned    | NO   |     | 0       |
| SortIxForAboutPage  | bigint(20) unsigned | NO   |     | NULL    |
| Forenames           | varchar(255)        | NO   |     |         |
| Surnames            | varchar(255)        | NO   |     |         |
| Initials            | varchar(255)        | NO   |     |         |
| EmailUpToAt         | varchar(255)        | NO   |     |         |
| EmailAfterAt        | varchar(255)        | NO   |     |         |
| PersonalWebsite     | varchar(255)        | NO   |     |         |
| FullRoleDescription | text                | YES  |     | NULL    |
+---------------------+---------------------+------+-----+---------+
'''
# Model for v4.Contributors table
class Contributor(db.Model):
    __tablename__ = 'Contributors'
    contributorIx = Column('ContributorIx', BIGINT(20, unsigned=True), nullable=False, primary_key=True)
    sortGroup = Column('SortGroup', INTEGER(10, unsigned=True), nullable=False)
    sortIxForAboutPage = Column('SortIxForAboutPage', BIGINT(20, unsigned=True), nullable=False)
    forenames = Column('Forenames', String(255), nullable=False)
    surnames = Column('Surnames', String(255), nullable=False)
    initials = Column('Initials', String(255), nullable=False)
    emailUpToAt = Column('EmailUpToAt', String(255), nullable=False)
    emailAfterAt = Column('EmailAfterAt', String(255), nullable=False)
    personalWebsite = Column('PersonalWebsite', String(255), nullable=False)
    fullRoleDescription = Column('FullRoleDescription', TEXT)

'''
    A short method to access the database session from outside of this module.
'''
def getSession():
    return db.session

if __name__ == '__main__':
    xs = getSession().query(EditImport).all()
    print 'Entries in Edit_Imports:'
    for x in xs:
        print x.toDict()
