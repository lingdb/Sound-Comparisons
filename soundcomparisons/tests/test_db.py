# coding: utf8
from __future__ import unicode_literals
import unittest

import soundcomparisons
import soundcomparisons.db as db


class ValidateTestCase(unittest.TestCase):
    def test_foo(self):
        assert True
        return False

if __name__ == "__main__":
    print soundcomparisons
    print 'HURZ:', db, db.getSession()
    print soundcomparisons.dataInfo
    # TODO use db.getSession() in above test!
