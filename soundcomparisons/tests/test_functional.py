# coding: utf8
from __future__ import unicode_literals, print_function, division
import unittest
from json import loads
from urllib import urlencode

import soundcomparisons


class FlaskrTestCase(unittest.TestCase):
    def setUp(self):
        self.app = soundcomparisons.app.test_client()

    def get_json(self, path, **params):
        if params:
            path = '%s?%s' % (path, urlencode(params))
        return loads(self.app.get(path).data)

    def test_index(self):
        self.app.get('/')

    def test_query_data(self):
        res = self.get_json('/query/data')
        self.assertIn('Description', res)
        res = self.get_json('/query/data?global=1')
        self.assertIn('global', res)
        res = self.get_json('/query/data?study=Germanic')
        self.assertIn('defaults', res)

    def test_query_templateInfo(self):
        res = self.get_json('/query/templateInfo')
        self.assertIsInstance(res, dict)

    def test_query_translations(self):
        res = self.get_json('/query/translations')
        self.assertIsInstance(res, dict)
        res = self.get_json('/query/translations', action='summary')
        self.assertIsInstance(res, dict)
        res = self.get_json('/query/translations', action='static', translationId='1')
        self.assertIsInstance(res, dict)
        res = self.get_json('/query/translations', action='dynamic', translationId='1')
        self.assertIsInstance(res, dict)
        res = self.get_json('/query/translations', lng='en', ns='translation')
        self.assertIn('en', res)
