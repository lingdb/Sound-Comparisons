'''
    Playing with retrieving some stuff from the database
    For the toDict() methods I've decided to use an idea from StackOverflow:
    https://stackoverflow.com/a/11884806/448591
'''

import sqlalchemy
from sqlalchemy import Column, String, Integer, ForeignKeyConstraint
from sqlalchemy.orm import relationship
'''
    Don't use/import DOUBLE if it can be avoided, because DOUBLE for some reason doesn't (precision?) work with flask.jsonify
'''
from sqlalchemy.dialects.mysql import TINYINT, TIMESTAMP, TEXT, BIGINT, INTEGER, FLOAT
import flask
from flask.ext.sqlalchemy import SQLAlchemy

app = flask.Flask('Soundcomparisons')
app.config['SQLALCHEMY_DATABASE_URI'] = 'mysql://root:1234@localhost/v4'
db = SQLAlchemy(app)

'''
    Child of db.Model to add useful method
'''
class SndCompModel():
    '''
        @return dict {}
        Serialize a Model to a dict that maps its column names to column values.
    '''
    def toDict(self):
        return {c.name: getattr(self, c.name) for c in self.__table__.columns}

'''
+-------+---------------------+------+-----+-------------------+
| Field | Type                | Null | Key | Default           |
+-------+---------------------+------+-----+-------------------+
| Who   | bigint(20) unsigned | NO   |     | NULL              |
| Time  | timestamp           | NO   |     | CURRENT_TIMESTAMP |
+-------+---------------------+------+-----+-------------------+
'''
# Model for v4.Edit_Imports table
class EditImports(db.Model, SndCompModel):
    __tablename__ = 'Edit_Imports'
    Who = Column('Who', BIGINT(20, unsigned=True), nullable=False, primary_key=True)
    Time = Column('Time', TIMESTAMP, primary_key=True)

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
class Studies(db.Model, SndCompModel):
    __tablename__ = 'Studies'
    StudyIx = Column('StudyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    FamilyIx = Column('FamilyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    SubFamilyIx = Column('SubFamilyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    Name = Column('Name', String(255), nullable=False)
    DefaultTopLeftLat = Column('DefaultTopLeftLat', FLOAT)
    DefaultTopLeftLon = Column('DefaultTopLeftLon', FLOAT)
    DefaultBottomRightLat = Column('DefaultBottomRightLat', FLOAT)
    DefaultBottomRightLon = Column('DefaultBottomRightLon', FLOAT)
    ColorByFamily = Column('ColorByFamily', TINYINT(1, unsigned=True), nullable=False)
    SecondRfcLg  = Column('SecondRfcLg', String(255), nullable=False)
    # Relationships with other models:
    MeaningGroupMembers = relationship('MeaningGroupMembers')
    DefaultLanguages = relationship('DefaultLanguages')
    DefaultLanguagesExcludeMap = relationship('DefaultLanguagesExcludeMap')
    DefaultMultipleLanguages = relationship('DefaultMultipleLanguages')
    DefaultMultipleWords = relationship('DefaultMultipleWords')
    DefaultWords = relationship('DefaultWords')

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
class ShortLinks(db.Model, SndCompModel):
    __tablename__ = 'Page_ShortLinks'
    Hash = Column('Hash', String(32), nullable=False, primary_key=True)
    Name = Column('Name', String(32), nullable=False)
    Target = Column('Target', TEXT, nullable=False)

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
class Contributors(db.Model, SndCompModel):
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
class ContributorCategories(db.Model, SndCompModel):
    __tablename__ = 'ContributorCategories';
    SortGroup = Column('SortGroup', INTEGER(10, unsigned=True), nullable=False, primary_key=True)
    Headline = Column('Headline', String(255), nullable=False, primary_key=True)
    Abbr = Column('Abbr', String(255), nullable=False, primary_key=True)

'''
+---------+--------------+------+-----+---------+
| Field   | Type         | Null | Key | Default |
+---------+--------------+------+-----+---------+
| Flag    | varchar(255) | NO   | PRI | NULL    |
| Tooltip | varchar(255) | NO   |     | NULL    |
+---------+--------------+------+-----+---------+
'''
# Model for v4.FlagTooltip
class FlagTooltip(db.Model, SndCompModel):
    __tablename__ = 'FlagTooltip'
    Flag = Column('Flag', String(255), nullable=False, primary_key=True)
    Tooltip = Column('Tooltip', String(255), nullable=False)

'''
+--------------------+---------------------+------+-----+---------+
| Field              | Type                | Null | Key | Default |
+--------------------+---------------------+------+-----+---------+
| LanguageStatusType | tinyint(3) unsigned | NO   | PRI | NULL    |
| Description        | text                | YES  |     | NULL    |
| Status             | varchar(50)         | YES  |     | NULL    |
| StatusTooltip      | varchar(255)        | YES  |     | NULL    |
| Color              | varchar(6)          | NO   |     | 00FFFF  |
| Opacity            | double              | NO   |     | 1       |
| ColorDepth         | double              | NO   |     | 0.5     |
+--------------------+---------------------+------+-----+---------+
'''
# Model for v4.LanguageStatusTypes
class LanguageStatusTypes(db.Model, SndCompModel):
    __tablename__ = 'LanguageStatusTypes'
    LanguageStatusType = Column('LanguageStatusType', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    Description = Column('Description', TEXT)
    Status = Column('Status', String(50))
    StatusTooltip = Column('StatusTooltip', String(255))
    Color = Column('Color', String(6), nullable=False)
    Opacity = Column('Opacity', FLOAT, nullable=False)
    ColorDepth = Column('ColorDepth', FLOAT, nullable=False)

'''
+----------------+------------------+------+-----+---------+
| Field          | Type             | Null | Key | Default |
+----------------+------------------+------+-----+---------+
| MeaningGroupIx | int(10) unsigned | NO   | PRI | NULL    |
| Name           | varchar(255)     | YES  |     | NULL    |
+----------------+------------------+------+-----+---------+
'''
# Model for v4.MeaningGroups
class MeaningGroups(db.Model, SndCompModel):
    __tablename__ = 'MeaningGroups'
    MeaningGroupIx = Column('MeaningGroupIx', INTEGER(10, unsigned=True), nullable=False, primary_key=True)
    Name = Column('Name', String(255))

'''
+--------------+------------------+------+-----+---------+
| Field        | Type             | Null | Key | Default |
+--------------+------------------+------+-----+---------+
| Ix           | int(10) unsigned | NO   | PRI | NULL    |
| Abbreviation | varchar(10)      | NO   |     | NULL    |
| HoverText    | text             | NO   |     | NULL    |
+--------------+------------------+------+-----+---------+
'''
# Model for v4.TranscrSuperscriptInfo
class TranscrSuperscriptInfo(db.Model, SndCompModel):
    __tablename__ = 'TranscrSuperscriptInfo'
    Ix = Column('Ix', INTEGER(10, unsigned=True), nullable=False, primary_key=True)
    Abbreviation = Column('Abbreviation', String(10), nullable=False)
    HoverText = Column('HoverText', TEXT, nullable=False)

'''
+----------------------+--------------+------+-----+---------+
| Field                | Type         | Null | Key | Default |
+----------------------+--------------+------+-----+---------+
| IsoCode              | varchar(3)   | NO   | PRI | NULL    |
| Abbreviation         | varchar(10)  | NO   |     | NULL    |
| FullNameForHoverText | varchar(255) | NO   |     | NULL    |
+----------------------+--------------+------+-----+---------+
'''
# Model for v4.TranscrSuperscriptLenderLgs
class TranscrSuperscriptLenderLgs(db.Model, SndCompModel):
    __tablename__ = 'TranscrSuperscriptLenderLgs'
    IsoCode = Column('IsoCode', String(3), nullable=False, primary_key=True)
    Abbreviation = Column('Abbreviation', String(10), nullable=False)
    FullNameForHoverText = Column('FullNameForHoverText', String(255), nullable=False)

'''
+-------------------+--------------+------+-----+---------+
| Field             | Type         | Null | Key | Default |
+-------------------+--------------+------+-----+---------+
| BrowserMatch      | varchar(255) | NO   | PRI | NULL    |
| ISOCode           | varchar(3)   | NO   | PRI | NULL    |
| WikipediaLinkPart | varchar(255) | NO   | PRI | NULL    |
| Href              | text         | NO   |     | NULL    |
+-------------------+--------------+------+-----+---------+
'''
# Model for v4.WikipediaLinks
class WikipediaLinks(db.Model, SndCompModel):
    __tablename__ = 'WikipediaLinks'
    BrowserMatch = Column('BrowserMatch', String(255), nullable=False, primary_key=True)
    ISOCode = Column('ISOCode', String(3), nullable=False, primary_key=True)
    WikipediaLinkPart = Column('WikipediaLinkPart', String(255), nullable=False, primary_key=True)
    Href = Column('Href', TEXT, nullable=False)

'''
+------------------------+---------------------+------+-----+---------+
| Field                  | Type                | Null | Key | Default |
+------------------------+---------------------+------+-----+---------+
| StudyIx                | tinyint(3) unsigned | NO   | PRI | NULL    |
| FamilyIx               | tinyint(3) unsigned | NO   | PRI | NULL    |
| FamilyNm               | varchar(255)        | NO   | PRI | NULL    |
| FamilyAbbrAllFileNames | varchar(255)        | NO   |     |         |
| ProjectAboutUrl        | varchar(2000)       | NO   |     |         |
| ProjectActive          | tinyint(1)          | NO   |     | 1       |
+------------------------+---------------------+------+-----+---------+
'''
# Model for v4.Families
class Families(db.Model, SndCompModel):
    __tablename__ = 'Families'
    StudyIx = Column('StudyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    FamilyIx = Column('FamilyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    FamilyNm = Column('FamilyNm', String(255), nullable=False)
    FamilyAbbrAllFileNames = Column('FamilyAbbrAllFileNames', String(255), nullable=False)
    ProjectAboutUrl = Column('ProjectAboutUrl', String(2000), nullable=False)
    ProjectActive = Column('ProjectActive', TINYINT(1), nullable=False)

'''
+-------------------------+---------------------+------+-----+---------+
| Field                   | Type                | Null | Key | Default |
+-------------------------+---------------------+------+-----+---------+
| StudyIx                 | tinyint(3) unsigned | NO   | PRI | NULL    |
| FamilyIx                | tinyint(3) unsigned | NO   | PRI | NULL    |
| MeaningGroupIx          | int(10) unsigned    | NO   | PRI | NULL    |
| MeaningGroupMemberIx    | int(10) unsigned    | NO   |     | NULL    |
| IxElicitation           | int(10) unsigned    | NO   | PRI | NULL    |
| IxMorphologicalInstance | tinyint(3) unsigned | NO   | PRI | NULL    |
+-------------------------+---------------------+------+-----+---------+
'''
# Model for v4.MeaningGroupMembers
class MeaningGroupMembers(db.Model, SndCompModel):
    __tablename__ = 'MeaningGroupMembers'
    StudyIx = Column('StudyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    FamilyIx = Column('FamilyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    MeaningGroupIx = Column('MeaningGroupIx', INTEGER(10, unsigned=True), nullable=False, primary_key=True)
    MeaningGroupMemberIx = Column('MeaningGroupMemberIx', INTEGER(10, unsigned=True), nullable=False)
    IxElicitation = Column('IxElicitation', INTEGER(10, unsigned=True), nullable=False, primary_key=True)
    IxMorphologicalInstance = Column('IxMorphologicalInstance', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    __table_args__ = (ForeignKeyConstraint([StudyIx, FamilyIx],[Studies.StudyIx, Studies.FamilyIx]), {})
    # FIXME ADD FOREIGN KEY ON MeaningGroup

'''
+------------+---------------------+------+-----+---------+
| Field      | Type                | Null | Key | Default |
+------------+---------------------+------+-----+---------+
| LanguageIx | bigint(20) unsigned | NO   | PRI | NULL    |
| StudyIx    | tinyint(3) unsigned | NO   | PRI | NULL    |
| FamilyIx   | tinyint(3) unsigned | NO   | PRI | NULL    |
+------------+---------------------+------+-----+---------+
'''
# Model for v4.Default_Languages
class DefaultLanguages(db.Model, SndCompModel):
    __tablename__ = 'Default_Languages'
    LanguageIx = Column('LanguageIx', BIGINT(20, unsigned=True), nullable=False, primary_key=True)
    StudyIx = Column('StudyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    FamilyIx = Column('FamilyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    __table_args__ = (ForeignKeyConstraint([StudyIx, FamilyIx],[Studies.StudyIx, Studies.FamilyIx]), {})
    # FIXME FOREIGN KEYS

'''
+------------+---------------------+------+-----+---------+
| Field      | Type                | Null | Key | Default |
+------------+---------------------+------+-----+---------+
| LanguageIx | bigint(20) unsigned | NO   | PRI | NULL    |
| StudyIx    | tinyint(3) unsigned | NO   | PRI | NULL    |
| FamilyIx   | tinyint(3) unsigned | NO   | PRI | NULL    |
+------------+---------------------+------+-----+---------+
'''
# Model for v4.Default_Languages_Exclude_Map
class DefaultLanguagesExcludeMap(db.Model, SndCompModel):
    __tablename__ = 'Default_Languages_Exclude_Map'
    LanguageIx = Column('LanguageIx', BIGINT(20, unsigned=True), nullable=False, primary_key=True)
    StudyIx = Column('StudyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    FamilyIx = Column('FamilyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    __table_args__ = (ForeignKeyConstraint([StudyIx, FamilyIx],[Studies.StudyIx, Studies.FamilyIx]), {})
    # FIXME FOREIGN KEYS

'''
+------------+---------------------+------+-----+---------+
| Field      | Type                | Null | Key | Default |
+------------+---------------------+------+-----+---------+
| LanguageIx | bigint(20) unsigned | NO   | PRI | NULL    |
| StudyIx    | tinyint(3) unsigned | NO   | PRI | NULL    |
| FamilyIx   | tinyint(3) unsigned | NO   | PRI | NULL    |
+------------+---------------------+------+-----+---------+
'''
# Model for v4.Default_Multiple_Languages
class DefaultMultipleLanguages(db.Model, SndCompModel):
    __tablename__ = 'Default_Multiple_Languages'
    LanguageIx = Column('LanguageIx', BIGINT(20, unsigned=True), nullable=False, primary_key=True)
    StudyIx = Column('StudyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    FamilyIx = Column('FamilyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    __table_args__ = (ForeignKeyConstraint([StudyIx, FamilyIx],[Studies.StudyIx, Studies.FamilyIx]), {})
    # FIXME FOREIGN KEYS

'''
+-------------------------+---------------------+------+-----+---------+
| Field                   | Type                | Null | Key | Default |
+-------------------------+---------------------+------+-----+---------+
| IxElicitation           | int(10) unsigned    | NO   |     | NULL    |
| IxMorphologicalInstance | tinyint(3) unsigned | NO   |     | NULL    |
| StudyIx                 | tinyint(3) unsigned | NO   |     | NULL    |
| FamilyIx                | tinyint(3) unsigned | NO   |     | NULL    |
+-------------------------+---------------------+------+-----+---------+
'''
# Model for v4.Default_Multiple_Words
class DefaultMultipleWords(db.Model, SndCompModel):
    __tablename__ = 'Default_Multiple_Words'
    IxElicitation = Column('IxElicitation', INTEGER(10, unsigned=True), nullable=False, primary_key=True)
    IxMorphologicalInstance = Column('IxMorphologicalInstance', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    StudyIx = Column('StudyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    FamilyIx = Column('FamilyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    __table_args__ = (ForeignKeyConstraint([StudyIx, FamilyIx],[Studies.StudyIx, Studies.FamilyIx]), {})
    # FIXME FOREIGN KEYS

'''
+-------------------------+---------------------+------+-----+---------+
| Field                   | Type                | Null | Key | Default |
+-------------------------+---------------------+------+-----+---------+
| IxElicitation           | int(10) unsigned    | NO   |     | NULL    |
| IxMorphologicalInstance | tinyint(3) unsigned | NO   |     | NULL    |
| StudyIx                 | tinyint(3) unsigned | NO   |     | NULL    |
| FamilyIx                | tinyint(3) unsigned | NO   |     | NULL    |
+-------------------------+---------------------+------+-----+---------+
'''
# Model for v4.Default_Words
class DefaultWords(db.Model, SndCompModel):
    __tablename__ = 'Default_Words'
    IxElicitation = Column('IxElicitation', INTEGER(10, unsigned=True), nullable=False, primary_key=True)
    IxMorphologicalInstance = Column('IxMorphologicalInstance', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    StudyIx = Column('StudyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    FamilyIx = Column('FamilyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    __table_args__ = (ForeignKeyConstraint([StudyIx, FamilyIx],[Studies.StudyIx, Studies.FamilyIx]), {})
    # FIXME FOREIGN KEYS

'''
    A short method to access the database session from outside of this module.
'''
def getSession():
    return db.session

if __name__ == '__main__':
    s = getSession().query(Studies).limit(1).one()
    print s.toDict()
