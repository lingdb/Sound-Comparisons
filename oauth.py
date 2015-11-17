# -*- coding: utf-8 -*-
'''
    Module to provide oauth authentication.
    Heavily inspired by https://github.com/lingdb/flask-oauth
'''
import flask
import httplib2
import json
import random
import requests
import string

from flask import session as login_session
from oauth2client.client import flow_from_clientsecrets
from oauth2client.client import FlowExchangeError
from oauth2client.client import AccessTokenCredentials

import config

CLIENT_ID = config.getOAuth()['web']['client_id']


def show_login():
    data = {
        'STATE': ''.join(random.choice(string.ascii_uppercase + string.digits)
                         for _ in xrange(32)),
        'CLIENT_ID': config.getOAuth()['web']['client_id']
    }
    login_session['state'] = data['STATE']
    return flask.render_template('login.html', **data)


def show_logout():
    return flask.redirect(flask.url_for('google_logout'))


def google_login():
    # Check if we've got a POST request:
    if flask.request.method != 'POST':
        return 'Method Not Allowed, use POST!', 405
    # First, is the token valid?
    if flask.request.args.get('state') != login_session['state']:
        response = flask.make_response(
                     json.dumps('State parameter is invalid.'), 401)
        response.headers['Content-Type'] = 'application/json'
        return response
    # Save code for authorisation
    code = flask.request.data

    try:
        # Next, try obtaining credentials from authorisation code
        oauth_flow = flow_from_clientsecrets('client_secrets.json', scope='')
        oauth_flow.redirect_uri = 'postmessage'
        creds = oauth_flow.step2_exchange(code)
    except FlowExchangeError:
        answer = 'Failed to obtain credentials using the authorisation code.'
        response = flask.make_response(
            json.dumps(answer), 401)
        response.headers['Content-Type'] = 'application/json'
        return response

    # Is the access token valid?
    access_token = creds.access_token
    url = ('https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=%s' %
           access_token)
    h = httplib2.Http()
    result = json.loads(h.request(url, 'GET')[1])

    # Is there an error in the access token info? If so, then stop here.
    if result.get('error') is not None:
        response = flask.make_response(json.dumps(result.get('error')), 500)
        response.headers['Content-Type'] = 'application/json'

    # Is the access token for the intended user?
    google_plus_id = creds.id_token['sub']
    if result['user_id'] != google_plus_id:
        answer = "The user ID of the token doesn't match the given user ID."
        response = flask.make_response(json.dumps(answer), 401)
        response.headers['Content-Type'] = 'application/json'
        return response

    # Is the access token valid for this app?
    if result['issued_to'] != CLIENT_ID:
        answer = 'The client ID of the token does not match that of the app.'
        response = flask.make_response(json.dumps(answer), 401)
        print 'The client ID of the token does not match that of the app.'
        response.headers['Content-Type'] = 'application/json'
        return response

    stored_access_token = login_session.get('access_token')
    stored_gplus_id = login_session.get('gplus_id')
    if stored_access_token is not None and google_plus_id == stored_gplus_id:
        answer = 'Current user is already connected.'
        response = make_response(json.dumps(answer), 200)
        response.headers['Content-Type'] = 'application/json'
        return response

    # In this session, store the access token for later use.
    login_session['access_token'] = access_token
    login_session['google_plus_id'] = google_plus_id

    # Get user info.
    userinfo_url = 'https://www.googleapis.com/oauth2/v1/userinfo'
    params = {'access_token': access_token, 'alt': 'json'}
    answer = requests.get(userinfo_url, params=params)

    res = answer.json()

    login_session['username'] = res['name']
    login_session['picture'] = res['picture']
    login_session['email'] = res['email']

    # Check if user exists, if not create user
    usrid = get_user_id(login_session['email'])
    if not usrid:
        usrid = create_user(login_session)
    login_session['user_id'] = usrid

    output = """
<h1>Welcome, %s!</h1>
<img src="%s" style="
width: 300px;
height: 300px;
border-radius: 150px;
-webkit-border-radius: 150px;
-moz-border-radius: 150px;">
""" % (login_session['username'], login_session['picture'])
    msg = 'You have successfully logged in as %s'
    flask.flash(msg % login_session['username'])
    print 'Login completed!'
    return output


# Method: revokes current user's token and resets login_session.
def google_logout():
    # Logout a logged in user.
    access_token = login_session.get('access_token')
    # access_token = login_session.get('access_token')
    if access_token is None:
        resp = make_response(json.dumps('Current user not logged in.'), 401)
        resp.headers['Content-Type'] = 'application/json'
        return resp

    usr = login_session.get('username')

    url = 'https://accounts.google.com/o/oauth2/revoke?token=%s' % access_token
    http_obj = httplib2.Http()
    google_resp = http_obj.request(url, 'GET')[0]

    # Now check Google's response.
    if google_resp['status'] == '200':
        # Shutdown user's session.
        del login_session['access_token']
        del login_session['google_plus_id']
        del login_session['username']
        del login_session['email']
        del login_session['picture']

        answer = 'Session successfully shutdown. Goodbye %s' % usr
        resp = flask.make_response(json.dumps(answer), 200)
        resp.headers['Content-Type'] = 'application/json'

        return resp
    else:
        # Message about some kind of problem in shutdown procedure.
        answer = "Failure to revoke current user's tokens."
        resp = flask.make_response(json.dumps(answer), 401)
        resp.headers['Content-Type'] = 'application/json'
        return resp


# TODO HELPER METHODS BELOW:
# FIXME to make this module useful it will be necessary to introduce some concept of storage for a users id + email and such.
def get_user_id(email):
    return 'MAGICALMYSTERYNUMBER'
