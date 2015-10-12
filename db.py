# -*- coding: utf-8 -*-
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
# To check file existence:
import os.path

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
    MeaningGroupMembers = relationship('MeaningGroupMembers', viewonly=True)
    DefaultLanguages = relationship('DefaultLanguages', viewonly=True)
    DefaultLanguagesExcludeMap = relationship('DefaultLanguagesExcludeMap', viewonly=True)
    DefaultMultipleLanguages = relationship('DefaultMultipleLanguages', viewonly=True)
    DefaultMultipleWords = relationship('DefaultMultipleWords', viewonly=True)
    DefaultWords = relationship('DefaultWords', viewonly=True)
    Regions = relationship('Regions', viewonly=True)
    RegionLanguages = relationship('RegionLanguages', viewonly=True)
    Languages = relationship('Languages', viewonly=True)
    Words = relationship('Words', viewonly=True)
    Transcriptions = relationship('Transcriptions', viewonly=True)

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
    # Foreign keys:
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
    # Foreign keys:
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
    # Foreign keys:
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
    # Foreign keys:
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
    # Foreign keys:
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
    # Foreign keys:
    __table_args__ = (ForeignKeyConstraint([StudyIx, FamilyIx],[Studies.StudyIx, Studies.FamilyIx]), {})
    # FIXME FOREIGN KEYS

'''
+----------------------+---------------------+------+-----+---------+
| Field                | Type                | Null | Key | Default |
+----------------------+---------------------+------+-----+---------+
| StudyIx              | tinyint(3) unsigned | NO   | PRI | NULL    |
| FamilyIx             | tinyint(3) unsigned | NO   | PRI | NULL    |
| SubFamilyIx          | tinyint(3) unsigned | NO   | PRI | NULL    |
| RegionGpIx           | tinyint(3) unsigned | NO   | PRI | NULL    |
| DefaultExpandedState | tinyint(3) unsigned | NO   |     | 0       |
| RegionGpTypeIx       | tinyint(3) unsigned | NO   |     | 1       |
| RegionGpNameLong     | varchar(255)        | YES  |     | NULL    |
| RegionGpNameShort    | varchar(255)        | YES  |     | NULL    |
| StudyName            | varchar(10)         | NO   | PRI |         |
+----------------------+---------------------+------+-----+---------+
'''
# Model for v4.Regions
class Regions(db.Model, SndCompModel):
    __tablename__ = 'Regions'
    StudyIx = Column('StudyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    FamilyIx = Column('FamilyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    SubFamilyIx = Column('SubFamilyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    RegionGpIx = Column('RegionGpIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    DefaultExpandedState = Column('DefaultExpandedState', TINYINT(3, unsigned=True), nullable=False)
    RegionGpTypeIx = Column('RegionGpTypeIx', TINYINT(3, unsigned=True), nullable=False)
    RegionGpNameLong = Column('RegionGpNameLong', String(255))
    RegionGpNameShort = Column('RegionGpNameShort', String(255))
    StudyName = Column('StudyName', String(10), nullable=False, primary_key=True)
    # Foreign keys:
    __table_args__ = (ForeignKeyConstraint([StudyIx, FamilyIx, StudyName],[Studies.StudyIx, Studies.FamilyIx, Studies.Name]), {})
    # FIXME FOREIGN KEYS

'''
+-------------------------------------------------+---------------------+------+-----+---------+
| Field                                           | Type                | Null | Key | Default |
+-------------------------------------------------+---------------------+------+-----+---------+
| StudyIx                                         | tinyint(3) unsigned | NO   | PRI | NULL    |
| FamilyIx                                        | tinyint(3) unsigned | NO   | PRI | NULL    |
| SubFamilyIx                                     | tinyint(3) unsigned | NO   | PRI | NULL    |
| RegionGpIx                                      | tinyint(3) unsigned | NO   | PRI | NULL    |
| RegionMemberLgIx                                | tinyint(3) unsigned | NO   |     | NULL    |
| LanguageIx                                      | bigint(20) unsigned | NO   | PRI | NULL    |
| RegionMemberWebsiteSubGroupIx                   | tinyint(3) unsigned | YES  |     | NULL    |
| RegionGpMemberLgNameShortInThisSubFamilyWebsite | text                | YES  |     | NULL    |
| RegionGpMemberLgNameLongInThisSubFamilyWebsite  | text                | YES  |     | NULL    |
| Include                                         | tinyint(1)          | NO   |     | 0       |
| StudyName                                       | varchar(10)         | NO   | PRI |         |
+-------------------------------------------------+---------------------+------+-----+---------+
'''
# Model for v4.RegionLanguages
class RegionLanguages(db.Model, SndCompModel):
    __tablename__ = 'RegionLanguages'
    StudyIx = Column('StudyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    FamilyIx = Column('FamilyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    SubFamilyIx = Column('SubFamilyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    RegionGpIx = Column('RegionGpIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    RegionMemberLgIx = Column('RegionMemberLgIx', TINYINT(3, unsigned=True), nullable=False)
    LanguageIx = Column('LanguageIx', BIGINT(20, unsigned=True), nullable=False, primary_key=True)
    RegionMemberWebsiteSubGroupIx = Column('RegionMemberWebsiteSubGroupIx', TINYINT(3, unsigned=True))
    RegionGpMemberLgNameShortInThisSubFamilyWebsite = Column('RegionGpMemberLgNameShortInThisSubFamilyWebsite', TEXT)
    RegionGpMemberLgNameLongInThisSubFamilyWebsite = Column('RegionGpMemberLgNameLongInThisSubFamilyWebsite', TEXT)
    Include = Column('Include', TINYINT(1), nullable=False)
    StudyName = Column('StudyName', String(10), nullable=False, primary_key=True)
    # Foreign keys:
    __table_args__ = (ForeignKeyConstraint([StudyIx, FamilyIx, StudyName],[Studies.StudyIx, Studies.FamilyIx, Studies.Name]), {})
    # FIXME FOREIGN KEYS

'''
+----------------------------------------+---------------------+------+-----+---------+
| Field                                  | Type                | Null | Key | Default |
+----------------------------------------+---------------------+------+-----+---------+
| StudyIx                                | tinyint(3) unsigned | NO   |     | NULL    |
| FamilyIx                               | tinyint(3) unsigned | NO   |     | NULL    |
| LanguageIx                             | bigint(20) unsigned | NO   | PRI | NULL    |
| ShortName                              | varchar(255)        | NO   |     | NULL    |
| ToolTip                                | text                | YES  |     | NULL    |
| SpecificLanguageVarietyName            | text                | YES  |     | NULL    |
| LanguageStatusType                     | tinyint(3) unsigned | YES  |     | NULL    |
| WebsiteSubgroupName                    | text                | YES  |     | NULL    |
| WebsiteSubgroupWikipediaString         | text                | YES  |     | NULL    |
| HistoricalPeriod                       | text                | YES  |     | NULL    |
| HistoricalPeriodWikipediaString        | text                | YES  |     | NULL    |
| EthnicGroup                            | text                | YES  |     | NULL    |
| StateRegion                            | text                | YES  |     | NULL    |
| NearestCity                            | text                | YES  |     | NULL    |
| PreciseLocality                        | text                | YES  |     | NULL    |
| PreciseLocalityNationalSpelling        | text                | YES  |     | NULL    |
| ExternalWeblink                        | text                | YES  |     | NULL    |
| FilePathPart                           | varchar(255)        | NO   |     | NULL    |
| Flag                                   | varchar(255)        | YES  |     | NULL    |
| RfcLanguage                            | bigint(20)          | YES  |     | NULL    |
| Latitude                               | double              | YES  |     | NULL    |
| Longtitude                             | double              | YES  |     | NULL    |
| ISOCode                                | varchar(3)          | YES  |     | NULL    |
| GlottoCode                             | varchar(8)          | YES  |     | NULL    |
| WikipediaLinkPart                      | text                | YES  |     | NULL    |
| IsSpellingRfcLang                      | tinyint(3) unsigned | YES  |     | 0       |
| SpellingRfcLangName                    | varchar(255)        | YES  |     | NULL    |
| ContributorSpokenBy                    | bigint(20) unsigned | YES  |     | NULL    |
| ContributorRecordedBy1                 | bigint(20) unsigned | YES  |     | NULL    |
| ContributorRecordedBy2                 | bigint(20) unsigned | YES  |     | NULL    |
| ContributorSoundEditingBy              | bigint(20) unsigned | YES  |     | NULL    |
| ContributorPhoneticTranscriptionBy     | bigint(20) unsigned | YES  |     | NULL    |
| ContributorReconstructionBy            | bigint(20) unsigned | YES  |     | NULL    |
| ContributorCitationAuthor1             | bigint(20) unsigned | YES  |     | NULL    |
| Citation1Year                          | int(10) unsigned    | YES  |     | NULL    |
| Citation1Pages                         | varchar(255)        | YES  |     | NULL    |
| ContributorCitationAuthor2             | bigint(20) unsigned | YES  |     | NULL    |
| Citation2Year                          | int(10) unsigned    | YES  |     | NULL    |
| Citation2Pages                         | varchar(255)        | YES  |     | NULL    |
| AssociatedPhoneticsLgForThisSpellingLg | bigint(20)          | YES  |     | NULL    |
| IsOrthographyHasNoTranscriptions       | tinyint(3) unsigned | YES  |     | NULL    |
| StudyName                              | varchar(10)         | NO   | PRI |         |
+----------------------------------------+---------------------+------+-----+---------+
'''
# Model for v4.Languages
class Languages(db.Model, SndCompModel):
    __tablename__ = 'Languages'
    StudyIx = Column('StudyIx', TINYINT(3, unsigned=True), nullable=False)
    FamilyIx = Column('FamilyIx', TINYINT(3, unsigned=True), nullable=False)
    LanguageIx = Column('LanguageIx', BIGINT(20, unsigned=True), nullable=False, primary_key=True)
    ShortName = Column('ShortName', String(255), nullable=False)
    ToolTip = Column('ToolTip', TEXT)
    SpecificLanguageVarietyName = Column('SpecificLanguageVarietyName', TEXT)
    LanguageStatusType = Column('LanguageStatusType', TINYINT(3, unsigned=True))
    WebsiteSubgroupName = Column('WebsiteSubgroupName', TEXT)
    WebsiteSubgroupWikipediaString = Column('WebsiteSubgroupWikipediaString', TEXT)
    HistoricalPeriod = Column('HistoricalPeriod', TEXT)
    HistoricalPeriodWikipediaString = Column('HistoricalPeriodWikipediaString', TEXT)
    EthnicGroup = Column('EthnicGroup', TEXT)
    StateRegion = Column('StateRegion', TEXT)
    NearestCity = Column('NearestCity', TEXT)
    PreciseLocality = Column('PreciseLocality', TEXT)
    PreciseLocalityNationalSpelling = Column('PreciseLocalityNationalSpelling', TEXT)
    ExternalWeblink = Column('ExternalWeblink', TEXT)
    FilePathPart = Column('FilePathPart', String(255), nullable=False)
    Flag = Column('Flag', String(255))
    RfcLanguage = Column('RfcLanguage', BIGINT(20, unsigned=True))
    Latitude = Column('Latitude', FLOAT)
    Longtitude = Column('Longtitude', FLOAT)
    ISOCode = Column('ISOCode', String(3))
    GlottoCode = Column('GlottoCode', String(8))
    WikipediaLinkPart = Column('WikipediaLinkPart', TEXT)
    IsSpellingRfcLang = Column('IsSpellingRfcLang', TINYINT(3, unsigned=True))
    SpellingRfcLangName = Column('SpellingRfcLangName', String(255))
    ContributorSpokenBy = Column('ContributorSpokenBy', BIGINT(20, unsigned=True))
    ContributorRecordedBy1 = Column('ContributorRecordedBy1', BIGINT(20, unsigned=True))
    ContributorRecordedBy2 = Column('ContributorRecordedBy2', BIGINT(20, unsigned=True))
    ContributorSoundEditingBy = Column('ContributorSoundEditingBy', BIGINT(20, unsigned=True))
    ContributorPhoneticTranscriptionBy = Column('ContributorPhoneticTranscriptionBy', BIGINT(20, unsigned=True))
    ContributorReconstructionBy = Column('ContributorReconstructionBy', BIGINT(20, unsigned=True))
    ContributorCitationAuthor1 = Column('ContributorCitationAuthor1', BIGINT(20, unsigned=True))
    Citation1Year = Column('Citation1Year', INTEGER(10, unsigned=True))
    Citation1Pages = Column('Citation1Pages', String(255))
    ContributorCitationAuthor2 = Column('ContributorCitationAuthor2', BIGINT(20, unsigned=True))
    Citation2Year = Column('Citation2Year', INTEGER(10, unsigned=True))
    Citation2Pages = Column('Citation2Pages', String(255))
    AssociatedPhoneticsLgForThisSpellingLg = Column('AssociatedPhoneticsLgForThisSpellingLg', BIGINT(20, unsigned=True))
    IsOrthographyHasNoTranscriptions = Column('IsOrthographyHasNoTranscriptions', TINYINT(3, unsigned=True))
    StudyName = Column('StudyName', String(10), nullable=False, primary_key=True)
    # Foreign keys:
    __table_args__ = (ForeignKeyConstraint([StudyIx, FamilyIx, StudyName],[Studies.StudyIx, Studies.FamilyIx, Studies.Name]), {})
    # Relationships with other models:
    Transcriptions = relationship('Transcriptions', viewonly=True)
    # FIXME FOREIGN KEYS

'''
+-----------------------------------------------+---------------------+------+-----+---------+
| Field                                         | Type                | Null | Key | Default |
+-----------------------------------------------+---------------------+------+-----+---------+
| IxElicitation                                 | int(10) unsigned    | NO   | PRI | NULL    |
| IxMorphologicalInstance                       | tinyint(3) unsigned | NO   | PRI | NULL    |
| MeaningGroupIx                                | int(10) unsigned    | NO   |     | NULL    |
| MeaningGroupMemberIx                          | int(10) unsigned    | NO   |     | NULL    |
| ThisFySortOrderByAlphabeticalOfFamilyAncestor | int(10) unsigned    | YES  |     | NULL    |
| SoundFileWordIdentifierText                   | varchar(255)        | NO   |     | NULL    |
| FileNameRfcModernLg01                         | varchar(255)        | YES  |     | NULL    |
| FileNameRfcModernLg02                         | varchar(255)        | YES  |     | NULL    |
| FileNameRfcProtoLg01                          | varchar(255)        | YES  |     | NULL    |
| FileNameRfcProtoLg02                          | varchar(255)        | YES  |     | NULL    |
| FullRfcModernLg01                             | varchar(255)        | YES  |     | NULL    |
| LongerRfcModernLg01                           | varchar(255)        | YES  |     | NULL    |
| FullRfcModernLg02                             | varchar(255)        | YES  |     | NULL    |
| LongerRfcModernLg02                           | varchar(255)        | YES  |     | NULL    |
| FullRfcProtoLg01                              | varchar(255)        | YES  |     | NULL    |
| FullRfcProtoLg02                              | varchar(255)        | YES  |     | NULL    |
| FullRfcProtoLg01AltvRoot                      | varchar(255)        | YES  |     | NULL    |
| FullRfcProtoLg02AltvRoot                      | varchar(255)        | YES  |     | NULL    |
| StudyName                                     | varchar(10)         | NO   | PRI |         |
+-----------------------------------------------+---------------------+------+-----+---------+
'''
# Model for v4.Words
class Words(db.Model, SndCompModel):
    __tablename__ = 'Words'
    IxElicitation = Column('IxElicitation', INTEGER(10, unsigned=True), nullable=False, primary_key=True)
    IxMorphologicalInstance = Column('IxMorphologicalInstance', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    MeaningGroupIx = Column('MeaningGroupIx', INTEGER(10, unsigned=True), nullable=False)
    MeaningGroupMemberIx = Column('MeaningGroupMemberIx', INTEGER(10, unsigned=True), nullable=False)
    ThisFySortOrderByAlphabeticalOfFamilyAncestor = Column('ThisFySortOrderByAlphabeticalOfFamilyAncestor', INTEGER(10, unsigned=True))
    SoundFileWordIdentifierText = Column('SoundFileWordIdentifierText', String(255), nullable=False)
    FileNameRfcModernLg01 = Column('FileNameRfcModernLg01', String(255))
    FileNameRfcModernLg02 = Column('FileNameRfcModernLg02', String(255))
    FileNameRfcProtoLg01 = Column('FileNameRfcProtoLg01', String(255))
    FileNameRfcProtoLg02 = Column('FileNameRfcProtoLg02', String(255))
    FullRfcModernLg01 = Column('FullRfcModernLg01', String(255))
    LongerRfcModernLg01 = Column('LongerRfcModernLg01', String(255))
    FullRfcModernLg02 = Column('FullRfcModernLg02', String(255))
    LongerRfcModernLg02 = Column('LongerRfcModernLg02', String(255))
    FullRfcProtoLg01 = Column('FullRfcProtoLg01', String(255))
    FullRfcProtoLg02 = Column('FullRfcProtoLg02', String(255))
    FullRfcProtoLg01AltvRoot = Column('FullRfcProtoLg01AltvRoot', String(255))
    FullRfcProtoLg02AltvRoot = Column('FullRfcProtoLg02AltvRoot', String(255))
    StudyName = Column('StudyName', String(10), nullable=False, primary_key=True)
    # Foreign keys:
    __table_args__ = (ForeignKeyConstraint([StudyName],[Studies.Name]), {})
    # FIXME FOREIGN KEYS
    # Relationships with other models:
    Transcriptions = relationship('Transcriptions', viewonly=True)

'''
+-------------------------------------+---------------------+------+-----+---------+
| Field                               | Type                | Null | Key | Default |
+-------------------------------------+---------------------+------+-----+---------+
| StudyIx                             | tinyint(3) unsigned | NO   | PRI | NULL    |
| FamilyIx                            | tinyint(3) unsigned | NO   | PRI | NULL    |
| IxElicitation                       | int(10) unsigned    | NO   | PRI | NULL    |
| IxMorphologicalInstance             | tinyint(3) unsigned | NO   | PRI | NULL    |
| AlternativePhoneticRealisationIx    | tinyint(3) unsigned | NO   | PRI | 1       |
| AlternativeLexemIx                  | tinyint(3) unsigned | NO   | PRI | 1       |
| LanguageIx                          | bigint(20) unsigned | NO   | PRI | NULL    |
| Phonetic                            | varchar(255)        | YES  |     | NULL    |
| SpellingAltv1                       | varchar(255)        | YES  |     | NULL    |
| SpellingAltv2                       | varchar(255)        | YES  |     | NULL    |
| NotCognateWithMainWordInThisFamily  | tinyint(1)          | YES  |     | NULL    |
| CommonRootMorphemeStructDifferent   | tinyint(1)          | YES  |     | NULL    |
| DifferentMeaningToUsualForCognate   | tinyint(1)          | YES  |     | NULL    |
| ActualMeaningInThisLanguage         | varchar(255)        | YES  |     | NULL    |
| OtherLexemeInLanguageForMeaning     | varchar(255)        | YES  |     | NULL    |
| RootIsLoanWordFromKnownDonor        | tinyint(1)          | YES  |     | NULL    |
| RootSharedInAnotherFamily           | tinyint(1)          | YES  |     | NULL    |
| IsoCodeKnownDonor                   | varchar(3)          | YES  |     | NULL    |
| DifferentMorphemeStructureNote      | varchar(255)        | YES  |     | NULL    |
| OddPhonology                        | tinyint(1)          | YES  |     | NULL    |
| OddPhonologyNote                    | varchar(255)        | YES  |     | NULL    |
| UsageNote                           | varchar(255)        | YES  |     | NULL    |
| SoundProblem                        | tinyint(1)          | YES  |     | NULL    |
| ReconstructedOrHistQuestionable     | tinyint(1)          | YES  |     | NULL    |
| ReconstructedOrHistQuestionableNote | varchar(255)        | YES  |     | NULL    |
| StudyName                           | varchar(10)         | NO   | PRI |         |
+-------------------------------------+---------------------+------+-----+---------+
'''
# Model for v4.Transcriptions
class Transcriptions(db.Model, SndCompModel):
    __tablename__ = 'Transcriptions'
    StudyIx = Column('StudyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    FamilyIx = Column('FamilyIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    IxElicitation = Column('IxElicitation', INTEGER(10, unsigned=True), nullable=False, primary_key=True)
    IxMorphologicalInstance = Column('IxMorphologicalInstance', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    AlternativePhoneticRealisationIx = Column('AlternativePhoneticRealisationIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    AlternativeLexemIx = Column('AlternativeLexemIx', TINYINT(3, unsigned=True), nullable=False, primary_key=True)
    LanguageIx = Column('LanguageIx', BIGINT(20, unsigned=True), nullable=False, primary_key=True)
    Phonetic = Column('Phonetic', String(255))
    SpellingAltv1 = Column('SpellingAltv1', String(255))
    SpellingAltv2 = Column('SpellingAltv2', String(255))
    NotCognateWithMainWordInThisFamily = Column('NotCognateWithMainWordInThisFamily', TINYINT(1))
    CommonRootMorphemeStructDifferent = Column('CommonRootMorphemeStructDifferent', TINYINT(1))
    DifferentMeaningToUsualForCognate = Column('DifferentMeaningToUsualForCognate', TINYINT(1))
    ActualMeaningInThisLanguage = Column('ActualMeaningInThisLanguage', String(255))
    OtherLexemeInLanguageForMeaning = Column('OtherLexemeInLanguageForMeaning', String(255))
    RootIsLoanWordFromKnownDonor = Column('RootIsLoanWordFromKnownDonor', TINYINT(1))
    RootSharedInAnotherFamily = Column('RootSharedInAnotherFamily', TINYINT(1))
    IsoCodeKnownDonor = Column('IsoCodeKnownDonor', String(3))
    DifferentMorphemeStructureNote = Column('DifferentMorphemeStructureNote', String(255))
    OddPhonology = Column('OddPhonology', TINYINT(1))
    OddPhonologyNote = Column('OddPhonologyNote', String(255))
    UsageNote = Column('UsageNote', String(255))
    SoundProblem = Column('SoundProblem', TINYINT(1))
    ReconstructedOrHistQuestionable = Column('ReconstructedOrHistQuestionable', TINYINT(1))
    ReconstructedOrHistQuestionableNote = Column('ReconstructedOrHistQuestionableNote', String(255))
    StudyName = Column('StudyName', String(10), nullable=False, primary_key=True)
    # Foreign keys:
    __table_args__ = (
        # Relation to Studies:
        ForeignKeyConstraint([StudyName],[Studies.Name]),
        # Relation to Languages:
        ForeignKeyConstraint([LanguageIx, StudyName], [Languages.LanguageIx, Languages.StudyName]),
        # Relation to Words:
        ForeignKeyConstraint([IxElicitation, IxMorphologicalInstance, StudyName], [Words.IxElicitation, Words.IxMorphologicalInstance, Words.StudyName]),
        {})
    # FIXME FOREIGN KEYS
    # Relationships with other models:
    Study = relationship('Studies', viewonly=True)
    Language = relationship('Languages', viewonly=True)
    Word = relationship('Words', viewonly=True)

    '''
        @return {'found': [String], 'missing': [String]}
        Combines the work of these query/dataProvider.php methods:
        * soundPathParts(…)
        * findSoundFiles(…)
        Returns a dictionary containing both,
        lists of the found and of the missing sound files in the static/sound directory.
        @throws AssertionError
        Throws AssertionError iff self.{Language,Word} are not as expected.
    '''
    def getSoundFiles(self):
        # Sanity checks:
        assert isinstance(self.Language, Languages)
        assert isinstance(self.Word, Words)
        # Work from soundPathParts:
        parts = {
            'word': self.Word.SoundFileWordIdentifierText,
            'language': self.Language.FilePathPart
            }
        def helper(field, thing):
            if thing > 1:
                parts[field] = '_'+field+str(thing)
        helper('pron', self.AlternativePhoneticRealisationIx)
        helper('lex', self.AlternativeLexemIx)
        # Work from findSoundFiles:
        return findSoundFiles(parts)

    '''
        @return dict {}
        Extending SndCompModel.toDict(…) to include sound files
    '''
    def toDict(self):
        dict = super(Transcriptions, self).toDict()
        try:
            dict['soundPaths'] = self.getSoundFiles()['found']
        except:
            dict['soundPaths'] = []
        return dict


'''
    @param parts {
            'word': SoundFileWordIdentifierText String,
            'language': FilePathPart String,
            'pron': String, #optional
            'lex': String,  #optional
        }
    @return {'found': [String], 'missing': [String]}
    Tests if sound files are at their expected locations.
    used by Transcriptions and getDummyTranscriptions.
'''
def findSoundFiles(parts):
    #Sanitiziny parts:
    if 'pron' not in parts:
        parts['pron'] = ''
    if 'lex' not in parts:
        parts['lex'] = ''
    #Structure to return:
    ret = {
        'found': [],
        'missing': []
        }
    #Extensions and base dir:
    extensions = ['.mp3','.ogg']
    base = 'static/sound/'
    #Testing files:
    for ext in extensions:
        path = base+parts['language']+'/'+parts['language']+parts['word']+parts['lex']+parts['pron']+ext
        if os.path.isfile(path):
            ret['found'].append(path)
        else:
            ret['missing'].append(path)
    # Done:
    return ret

'''
    @param studyName String
    @return [{
            'isDummy': True,
            'IxElicitation': …,
            'IxMorphologicalInstance': …,
            'LanguageIx': …,
            'soundPaths': [String]
        }]
    Produces dicts for the expected Transcriptions entries that may follow in the future,
    but don't currently exist in the database.
    Returns only the cases where soundfiles are found.
'''
def getDummyTranscriptions(studyName):
    ret = [] # Results produced
    # Find Languages and Words that don't have Transcriptions:
    langs = getSession().query(Languages).filter_by(StudyName = studyName).filter(~Languages.Transcriptions.any()).all()
    words = getSession().query(Words).filter_by(StudyName = studyName).all()
    # Cross product:
    for l in langs:
        for w in words:
            files = findSoundFiles({
                'word': w.SoundFileWordIdentifierText,
                'language': l.FilePathPart
                })['found']
            if len(files):
                ret.append({
                    'isDummy': True,
                    'IxElicitation': w.IxElicitation,
                    'IxMorphologicalInstance': w.IxMorphologicalInstance,
                    'LanguageIx': l.LanguageIx,
                    'soundPaths': files
                    })
    return ret

'''
+-------------------+---------------------+------+-----+---------------------+----------------+
| Field             | Type                | Null | Key | Default             | Extra          |
+-------------------+---------------------+------+-----+---------------------+----------------+
| TranslationId     | bigint(20) unsigned | NO   | PRI | NULL                | auto_increment |
| TranslationName   | varchar(255)        | NO   |     | NULL                |                |
| BrowserMatch      | varchar(255)        | YES  |     | NULL                |                |
| ImagePath         | varchar(255)        | YES  |     | NULL                |                |
| Active            | tinyint(1)          | NO   |     | 0                   |                |
| RfcLanguage       | bigint(20) unsigned | YES  |     | NULL                |                |
| lastChangeStatic  | timestamp           | NO   |     | 0000-00-00 00:00:00 |                |
| lastChangeDynamic | timestamp           | NO   |     | 0000-00-00 00:00:00 |                |
+-------------------+---------------------+------+-----+---------------------+----------------+
'''
# Model for v4.Page_Translations
class Page_Translations(db.Model, SndCompModel):
    __tablename__ = 'Page_Translations'
    TranslationId = Column('TranslationId', BIGINT(20, unsigned=True), nullable=False, primary_key=True)
    TranslationName = Column('TranslationName', String(255), nullable=False)
    BrowserMatch = Column('BrowserMatch', String(255))
    ImagePath = Column('ImagePath', String(255))
    Active = Column('Active', TINYINT(1), nullable=False)
    RfcLanguage = Column('RfcLanguage', BIGINT(20, unsigned=True))
    lastChangeStatic = Column('lastChangeStatic', TIMESTAMP, nullable=False)
    lastChangeDynamic = Column('lastChangeDynamic', TIMESTAMP, nullable=False)
    # Foreign keys:
    __table_args__ = (
        # Relation to Languages:
        ForeignKeyConstraint([RfcLanguage],[Languages.LanguageIx]),
        {})
    # Relationships with other models:
    Language = relationship('Languages', viewonly=True)

    '''
        @return dict {}
        Extending SndCompModel.toDict(…) to convert timestamps.
        Hides 'Active' from dict.
    '''
    def toDict(self):
        dict = super(Page_Translations, self).toDict()
        for k in ['lastChangeStatic','lastChangeDynamic']:
            dict[k] = dict[k].strftime('%s')
        dict.pop('Active', None)
        return dict

'''
+-------------+--------------+------+-----+---------+
| Field       | Type         | Null | Key | Default |
+-------------+--------------+------+-----+---------+
| Req         | varchar(255) | NO   | PRI | NULL    |
| Description | text         | NO   |     | NULL    |
+-------------+--------------+------+-----+---------+
'''
# Model for v4.Page_StaticDescription
class Page_StaticDescription(db.Model, SndCompModel):
    __tablename__ = 'Page_StaticDescription'
    Req = Column('Req', String(255), nullable=False, primary_key=True)
    Description = Column('Description', TEXT, nullable=False)

'''
+---------------+---------------------+------+-----+-------------------+
| Field         | Type                | Null | Key | Default           |
+---------------+---------------------+------+-----+-------------------+
| TranslationId | bigint(20) unsigned | NO   |     | NULL              |
| Req           | varchar(255)        | NO   |     | NULL              |
| Trans         | text                | NO   |     | NULL              |
| IsHtml        | tinyint(1)          | NO   |     | 0                 |
| Time          | timestamp           | NO   |     | CURRENT_TIMESTAMP |
+---------------+---------------------+------+-----+-------------------+
'''
# Model for v4.Page_StaticTranslation
class Page_StaticTranslation(db.Model, SndCompModel):
    __tablename__ = 'Page_StaticTranslation'
    TranslationId = Column('TranslationId', BIGINT(20, unsigned=True), nullable=False, primary_key=True)
    Req = Column('Req', String(255), nullable=False, primary_key=True)
    Trans = Column('Trans', TEXT, nullable=False,)
    IsHtml = Column('IsHtml', TINYINT(1), nullable=False,)
    Time = Column('Time', TIMESTAMP, nullable=False,)
    # Foreign keys:
    __table_args__ = (
        # Relation to Page_Translations
        ForeignKeyConstraint([TranslationId],[Page_Translations.TranslationId]),
        # Relation to Page_StaticDescription:
        ForeignKeyConstraint([Req],[Page_StaticDescription.Req]),
        {})
    # Relationships with other models:
    Page_Translations = relationship('Page_Translations', viewonly=True)
    Page_StaticDescription = relationship('Page_StaticDescription', viewonly=True)

'''
+---------------+---------------------+------+-----+-------------------+
| Field         | Type                | Null | Key | Default           |
+---------------+---------------------+------+-----+-------------------+
| TranslationId | bigint(20) unsigned | NO   | PRI | NULL              |
| Category      | varchar(255)        | NO   | PRI | NULL              |
| Field         | varchar(255)        | NO   | PRI | NULL              |
| Trans         | varchar(255)        | NO   |     | NULL              |
| Time          | timestamp           | NO   |     | CURRENT_TIMESTAMP |
+---------------+---------------------+------+-----+-------------------+
'''
# Model for v4.Page_DynamicTranslation
class Page_DynamicTranslation(db.Model, SndCompModel):
    __tablename__ = 'Page_DynamicTranslation'
    TranslationId = Column('TranslationId', BIGINT(20, unsigned=True), nullable=False, primary_key=True)
    Category = Column('Category', String(255), nullable=False, primary_key=True)
    Field = Column('Field', String(255), nullable=False, primary_key=True)
    Trans = Column('Trans', String(255), nullable=False)
    Time = Column('Time', TIMESTAMP, nullable=False)
    # Foreign keys:
    __table_args__ = (
        # Relation to Page_Translations
        ForeignKeyConstraint([TranslationId],[Page_Translations.TranslationId]),
        {})
    # Relationships with other models:
    Page_Translations = relationship('Page_Translations', viewonly=True)

'''
    A short method to access the database session from outside of this module.
'''
def getSession():
    return db.session

if __name__ == '__main__':
    print getDummyTranscriptions('Germanic')
#   # TODO wield this into a test:
#   broken = 0
#   for t in getSession().query(Transcriptions).limit(2000).all():
#       try:
#           print t.getSoundFiles()
#       except:
#           broken += 1
#   print 'Broken Transcriptions:'
#   print broken
