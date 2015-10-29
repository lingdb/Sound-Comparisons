# -*- coding: utf-8 -*-
'''
Created on 25 Oct 2015

@author: AG
'''

import os
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker, relationship
from sqlalchemy import Column, Integer, String, ForeignKey, Date
from sqlalchemy.ext.declarative import declarative_base

import pandas as pd


Base = declarative_base()

class Users(Base):
    #Tell SQLAlchemy the table name
    __tablename__ = 'Users'
    __table_args__ = {'sqlite_autoincrement': True}
    #Tell SQLAlchemy the name of column and its attributes:
    id = Column(Integer, primary_key=True, nullable=False) 
    name = Column(String(128))
    email = Column(String(128))
    picture = Column(String(128))

class Lexemes(Base):
    #Tell SQLAlchemy the table name
    __tablename__ = 'Lexemes'
    __table_args__ = {'sqlite_autoincrement': True}
    #Tell SQLAlchemy the name of column and its attributes:
    id = Column(Integer, primary_key=True, nullable=False) 
    meaning = Column(String(16))
    cognate_id = Column(String(16))
    cognate_sources = Column(String(16))
    iso_lang_code = Column(String(16)) 
    lexeme = Column(String(convert_unicode=True))
    lexeme_sources = Column(String)
    user_id = Column(Integer, ForeignKey('Users.id'))
    user = relationship(Users)
    #date_created = Column(Date)

#class Wordlist(Base):
#    pass

class Sources(Base):
    #Tell SQLAlchemy the table name
    __tablename__ = 'Sources'
    __table_args__ = {'sqlite_autoincrement': True}
    #Tell SQLAlchemy the name of column and its attributes:
    id = Column(Integer, primary_key=True, nullable=False) 
    ref = Column(String(128))
    

class DataFileIO(object):
    def __init__(self, raw_file_path, rewritten_data_file_path, rewritten_source_file_path):
        self.FILE_RAW_PATH = open(raw_file_path).read()
        self.DATA_FILE_REWRITE_PATH = rewritten_data_file_path
        self.SOURCE_FILE_REWRITE_PATH = rewritten_source_file_path
        self.split_file()
        self.write_data_file()
        self.write_sources_file()
        
    def split_file(self):
        split_str = '#### SOURCES ####'
        self.DATA = self.FILE_RAW_PATH.split(split_str)[0]
        self.SOURCES = self.FILE_RAW_PATH.split(split_str)[1]
    
    def write_data_file(self):
        with open(self.DATA_FILE_REWRITE_PATH, 'wb') as f:
            header = self.DATA.split('\n')[0].split('\t')
            f.write(','.join(['"'+w+'"' for w in header[:-1]]))
            f.write(',"'+header[-1]+'"')
            f.write('\n')
            for line in self.DATA.split('\n')[1:]:
                out = ''
                if 'singleton' in line:
                    line = line.replace('singleton', '\t')
                if len(line.split('\t'))==len(header):
                    frstwrd = line.split('\t')[0]
                    lstwrd = line.split('\t')[-1]
                    if isinstance(frstwrd, str):
                        f.write(frstwrd+',')
                    else:
                        f.write(int(frstwrd))
                        f.write(',')
                    for w in line.split('\t')[1:-1]:
                        #if w:
                        #    if not('utf8' in chardet.detect(w)['encoding'].lower().strip('-')):
                        #        out += '"'+w.decode('latin-1').encode('utf-8')+'"'
                        #    else:
                        #        out += '"'+w+'",'
                        out += '"'+w+'",'
                    out += '"'+lstwrd+'"'
                    f.write(out)
                    f.write('\n')
                else:
                    'Problem with line: ',line
                    'This line is length: ',len(line)
        f.close()

    def write_sources_file(self):
        with open(self.SOURCE_FILE_REWRITE_PATH, 'wb') as f:
            header = ['id', 'ref']#self.SOURCES.split('\n')[0].split('\t')
            f.write(','.join(['"'+w+'"' for w in header[:-1]]))
            f.write(',"'+header[-1]+'"')
            f.write('\n')
            for line in self.SOURCES.split('\n')[1:]:
                out = ''
                if len(line.split('\t'))==len(header):
                    id_ = line.split('\t')[0]
                    ref = line.split('\t')[-1]
                    #Fix quotation issue
                    ref = ref.replace('"', "'")
                    if isinstance(id_, str):
                        f.write(id_+',')
                    else:
                        f.write(int(id_))
                        f.write(',')
                    out += '"'+ref+'"'
                    f.write(out)
                    f.write('\n')
                else:
                    'Problem with line: ',line
                    'This line is length: ',len(line)
        f.close()

    def load_data(self):
        df = pd.read_csv(self.DATA_FILE_REWRITE_PATH, sep=',', header=0, converters={0: lambda s: str(s)})
        return df.fillna('null')

    def load_sources(self):
        df = pd.read_csv(self.SOURCE_FILE_REWRITE_PATH, sep=',', header=0, converters={0: lambda s: str(s)})
        return df.fillna('null')


class SETUPDB(object):
    def __init__(self, raw_file_path):
        self.RAW_FILE_PATH = raw_file_path
        self.DATA_DIR = '\\'.join(self.RAW_FILE_PATH.split('\\')[:-1])
        self.create_db()
        
    def create_db(self):
        #Create the database
        REWRITTEN_DATA_FILE_PATH = os.path.join(self.DATA_DIR, self.RAW_FILE_PATH.split('\\')[-1].split('.')[0]+'.csv')
        REWRITTEN_SOURCES_FILE_PATH = os.path.join(self.DATA_DIR, self.RAW_FILE_PATH.split('\\')[-1].split('.')[0].replace('data', 'sources')+'.csv')
    
        engine = create_engine('sqlite:///'+self.DATA_DIR+'\\ielex2.db', encoding='utf8')
    
        Base.metadata.create_all(engine)
        
        #Create the session
        session = sessionmaker()
        session.configure(bind=engine)
        sessn = session()
    
        df = DataFileIO(RAW_FILE_PATH, REWRITTEN_DATA_FILE_PATH, REWRITTEN_SOURCES_FILE_PATH)
        df_data = df.load_data()
        df_data_iterrows = df_data.iterrows()
        next(df_data_iterrows)
        df_sources = df.load_sources()
        df_sources_iterrows = df_sources.iterrows()
        next(df_sources_iterrows)
        df_users = pd.DataFrame({'id': 1, 'name': 'fake', 'email': 'fake@fake.com', 'picture': 'none'}, index=[0])
        df_users_iterrows = df_users.iterrows()
        next(df_users_iterrows)
        try:
            for idx,row in df_users.iterrows():
                try:
                    usr_record = Users(**{
                        'id' : int(row['id']), 
                        'name' : row['name'],
                        'email' : row['email'],
                        'picture' : row['picture']
                    })
                    sessn.add(usr_record) #Add all the records
                except Exception, e:
                    print e,': ',idx,row
            for idx,row in df_data.iterrows():
                try:
                    data_record = Lexemes(**{
                        'id' : int(row[0]), 
                        'meaning' : row[1],
                        'cognate_id' : row[2],
                        'cognate_sources' : row[3],
                        'iso_lang_code' : row[4], 
                        'lexeme' : row[5].decode('utf-8'),
                        'lexeme_sources' : row[6],
                        'user_id' : 1
                    })
                    sessn.add(data_record) #Add all the records
                except Exception, e:
                    print e,': ',idx,row
            for idx,row in df_sources.iterrows():
                try:
                    src_record = Sources(**{
                        'id' : int(row[0]), 
                        'ref' : row[1].decode('utf-8')
                    })
                    sessn.add(src_record) #Add all the records
                except Exception, e:
                    print e,': ',idx,row
            sessn.commit() #Attempt to commit records
        except Exception, e:
            print e
            sessn.rollback() # In case of error, rollback changes
        finally:
            sessn.close() #Close the connection


if __name__ == '__main__':
    RAW_FILE_PATH = 'data\\IE2012_lexical_data.tsv'
    SETUPDB(RAW_FILE_PATH)

