# coding: utf8
from __future__ import unicode_literals
import unittest

import soundcomparisons
import soundcomparisons.db as db


class ValidateTestCase(unittest.TestCase):
    '''
        This test fetches all models from the database and runs their validate method.
        This is to make sure that the validate method isn't overly specific
        and works for the current set of data.
    '''

    def setUp(self):
        self.app = soundcomparisons.app.test_client()

    def test_validate(self):
        models = [db.EditImports,
                  db.Studies,
                  db.ShortLinks,
                  db.Contributors,
                  db.ContributorCategories,
                  db.FlagTooltip,
                  db.LanguageStatusTypes,
                  db.MeaningGroups,
                  db.TranscrSuperscriptInfo,
                  db.TranscrSuperscriptLenderLgs,
                  db.WikipediaLinks,
                  db.Families,
                  db.MeaningGroupMembers,
                  db.DefaultLanguages,
                  db.DefaultLanguagesExcludeMap,
                  db.DefaultMultipleLanguages,
                  db.DefaultMultipleWords,
                  db.DefaultWords,
                  db.Regions,
                  db.RegionLanguages,
                  db.Languages,
                  db.Words,
                  db.Transcriptions,
                  db.Page_Translations,
                  db.Page_StaticDescription,
                  db.Page_StaticTranslation,
                  db.Page_DynamicTranslation]
        for model in models:
            assert False, db.getSession()
            for entry in db.getSession().query(model).all():
                pass

if __name__ == "__main__":
    app = soundcomparisons.app.test_client()
    session = db.getSession()
    print soundcomparisons.db
    print 'db: ', db
    print db.db.init_app(soundcomparisons.app)
    langs = session.query(db.Languages).all()
    print 'lang count: ', len(langs)
    # unittest.main()
    # test = ValidateTestCase()
    # test.test_validate()
    # TODO use db.getSession() in above test!
