# -*- coding: utf-8 -*-
'''
Created on 25 Oct 2015

@author: AG

'''

from collections import OrderedDict

from sqlalchemy import Table
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker


ENGINE = create_engine('sqlite:///db\\data\\ielex2.db', encoding='utf8')


class CommonBase(object):

    def __repr__(self):
        return '\n'.join('%s:%s' % i for i in self.serialize.items())

    @property
    def serialize(self):
        #Returns object in easily serializable format, by 
        #returning model and its fields as ordered dictionary. 
        result = OrderedDict()
        for key in self.__mapper__.c.keys():
            result[key] = getattr(self, key)
        return result


Base = declarative_base(ENGINE, cls=CommonBase)


#Now autoload all the tables.
class Lexemes(Base):
    __table__ = Table('Lexemes', Base.metadata, autoload=True, autoload_with=ENGINE)
class Sources(Base):
    __table__ = Table('Sources', Base.metadata, autoload=True, autoload_with=ENGINE)
class Users(Base):
    __table__ = Table('Users', Base.metadata, autoload=True, autoload_with=ENGINE)


def load_session():
    metadata = Base.metadata
    
    metadata.create_all(ENGINE)
    Session = sessionmaker(bind=ENGINE)
    session = Session()
    
    #metadata_details = {
    #                    k: metadata.tables[k].columns.keys() 
    #                    for k in metadata.tables.keys()
    #                    }
    
    #return metadata_details, session
    return session


if __name__ == '__main__':
    #meta,session = load_session()
    session = load_session()
    for x in session.query(Lexemes):
        print x
