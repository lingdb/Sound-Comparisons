# -*- coding: utf-8 -*-
from __future__ import unicode_literals
"""
run this file to start the app for development or to manage migrations.
See https://flask-migrate.readthedocs.org/en/latest/
"""
from flask.ext.script import Manager
from flask.ext.migrate import Migrate, MigrateCommand

from soundcomparisons import app
from db import db

app.host = '127.0.0.1'
app.port = 5000
app.debug = True

migrate = Migrate(app, db)
manager = Manager(app)
manager.add_command('db', MigrateCommand)

if __name__ == "__main__":
    manager.run()
