# -*- coding: utf-8 -*-
'''
    This file will be copied to 'config.py' by Vagrant setup.
    All basic configuration should be contained in here,
    so that it's easy to adjust on a per deployment basis.
'''

'''
    The SQLALCHEMY_DATABASE_URI string
    that will be used to connect to the database by SqlAlchemy.
    This setting is configured to fit the default setting
    put during Vagrant setup.
'''
dbURI = 'mysql://root:1234@localhost/v5'

'''
    Decide wether flask shall run in debugging mode.
    Make sure this is False when running production.
    It's usually helpful for this to be True when developing.
    When debug = True, the following conditions hold:
    * Flask debugging and thereby werkzeug is enabled.
    * Instead of App-minified.js, App.js is delivered.
'''
debug = False
