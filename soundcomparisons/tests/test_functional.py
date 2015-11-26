# coding: utf8
from __future__ import unicode_literals
import unittest

import soundcomparisons


class FlaskrTestCase(unittest.TestCase):
    def setUp(self):
        self.app = soundcomparisons.app.test_client()

    def test_index(self):
        self.app.get('/')
