'''
    Playing with retrieving some stuff from the database
    For the toDict() methods I've decided to use an idea from StackOverflow:
    https://stackoverflow.com/a/11884806/448591
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
    Who = Column('Who', BIGINT(20, unsigned=True), nullable=False, primary_key=True)
    Time = Column('Time', TIMESTAMP, primary_key=True)

    def toDict(self):
        return {c.name: getattr(self, c.name) for c in self.__table__.columns}

    def getTimeStampString(self):
        return self.Time.strftime('%s')

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
    StudyIx = Column('StudyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    FamilyIx = Column('FamilyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    SubFamilyIx = Column('SubFamilyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    Name = Column('Name', String(255), nullable=False)
    DefaultTopLeftLat = Column('DefaultTopLeftLat', DOUBLE)
    DefaultTopLeftLon = Column('DefaultTopLeftLon', DOUBLE)
    DefaultBottomRightLat = Column('DefaultBottomRightLat', DOUBLE)
    DefaultBottomRightLon = Column('DefaultBottomRightLon', DOUBLE)
    ColorByFamily = Column('ColorByFamily', TINYINT(1, unsigned=True), nullable=False)
    SecondRfcLg  = Column('SecondRfcLg', String(255), nullable=False)

    def toDict(self):
        return {c.name: getattr(self, c.name) for c in self.__table__.columns}

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
    Hash = Column('Hash', String(32), nullable=False, primary_key=True)
    Name = Column('Name', String(32), nullable=False)
    Target = Column('Target', TEXT, nullable=False)

    def toDict(self):
        return {c.name: getattr(self, c.name) for c in self.__table__.columns}

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
    ContributorIx = Column('ContributorIx', BIGINT(20, unsigned=True), nullable=False, primary_key=True)
    SortGroup = Column('SortGroup', INTEGER(10, unsigned=True), nullable=False)
    SortIxForAboutPage = Column('SortIxForAboutPage', BIGINT(20, unsigned=True), nullable=False)
    Forenames = Column('Forenames', String(255), nullable=False)
    Surnames = Column('Surnames', String(255), nullable=False)
    Initials = Column('Initials', String(255), nullable=False)
    EmailUpToAt = Column('EmailUpToAt', String(255), nullable=False)
    EmailAfterAt = Column('EmailAfterAt', String(255), nullable=False)
    PersonalWebsite = Column('PersonalWebsite', String(255), nullable=False)
    FullRoleDescription = Column('FullRoleDescription', TEXT)

    def toDict(self):
        return {c.name: getattr(self, c.name) for c in self.__table__.columns}

'''
+-----------+------------------+------+-----+---------+
| Field     | Type             | Null | Key | Default |
+-----------+------------------+------+-----+---------+
| SortGroup | int(10) unsigned | NO   |     | NULL    |
| Headline  | varchar(255)     | NO   |     |         |
| Abbr      | varchar(255)     | NO   |     |         |
+-----------+------------------+------+-----+---------+
'''
# Model for v4.ContributorCategories table
class ContributorCategory(db.Model):
    __tablename__ = 'ContributorCategories';
    SortGroup = Column('SortGroup', INTEGER(10, unsigned=True), nullable=False, primary_key=True)
    Headline = Column('Headline', String(255), nullable=False, primary_key=True)
    Abbr = Column('Abbr', String(255), nullable=False, primary_key=True)

    def toDict(self):
        return {c.name: getattr(self, c.name) for c in self.__table__.columns}

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
