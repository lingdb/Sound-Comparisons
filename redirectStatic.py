'''
    With the current state of soundcomparisons,
    static files are spread at several routes.
    To make the transition happen more smoothly,
    this module provides a bunch of redirects that redirect
    requests for current static files to their new locations.
'''

import flask


'''
    @param app instance of Flask
    addRoutes puts some redirect in place to ease the transition towards flask.
    Redirects with status 308 (Permanent Redirect)
    Routes that are redirected:
    /css/* -> /static/css/*
    /js/*  -> /static/js/*
    /img/* -> /static/img/*
    favicon.ico -> static/img/favicon.ico
'''
def addRoutes(app):
    @app.route('/css/<path:path>')
    def redirectCss(path):
        return flask.redirect('/static/css/'+path, 308)

    @app.route('/js/<path:path>')
    def redirectJs(path):
        return flask.redirect('/static/js/'+path, 308)

    @app.route('/img/<path:path>')
    def redirectImg(path):
        return flask.redirect('/static/img/'+path, 308)

    @app.route('/favicon.ico')
    def redirectFavicon():
        return flask.redirect('/static/img/favicon.ico', 308)
