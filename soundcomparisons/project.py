# -*- coding: utf-8 -*-
from __future__ import unicode_literals
'''
Created on 25 Oct 2015

@author: AG

'''

from flask import Flask, render_template, request, redirect, flash  # , jsonify, url_for

# from sqlalchemy import create_engine, asc
# from sqlalchemy.orm import sessionmaker
from db.db_access import load_session, Users  # , Lexemes, Sources

from flask import session as login_session
import random
import string

# Load libraries for oauth server exchanges
from oauth2client.client import flow_from_clientsecrets
from oauth2client.client import FlowExchangeError
# from oauth2client.client import AccessTokenCredentials
import httplib2
import json
from flask import make_response
import requests


app = Flask(__name__)

CLIENT_ID = json.loads(open('client_secrets.json', 'r').read())['web']['client_id']
APPLICATION_NAME = "IELex2"


session = load_session()


@app.route('/login')
def show_login():
    state = ''.join(random.choice(string.ascii_uppercase + string.digits) for _ in xrange(32))
    login_session['state'] = state
    # return 'The current session state is %s' % login_session['state']
    return render_template('login.html', STATE=state)


@app.route('/logout')
def show_logout():
    return redirect('google_logout')


@app.route('/google_login', methods=['POST'])
def google_login():

    # First, is the token valid?
    if request.args.get('state') != login_session['state']:
        response = make_response(json.dumps('State parameter is invalid.'), 401)
        response.headers['Content-Type'] = 'application/json'
        return response
    # Save code for authorisation
    code = request.data
    # For Python 3:
    # request.get_data()
    # code = request.data.decode('utf-8')

    try:
        # Next, try obtaining credentials from authorisation code
        oauth_flow = flow_from_clientsecrets('client_secrets.json', scope='')
        oauth_flow.redirect_uri = 'postmessage'
        creds = oauth_flow.step2_exchange(code)
    except FlowExchangeError:
        response = make_response(
            json.dumps('Failed to obtain credentials using the authorisation code.'), 401)
        response.headers['Content-Type'] = 'application/json'
        return response

    # Is the access token valid?
    access_token = creds.access_token
    url = ('https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=%s' % access_token)
    h = httplib2.Http()
    # For Python 3:
    # response = h.request(url, 'GET')[1]
    # str_response = response.decode('utf-8')
    result = json.loads(h.request(url, 'GET')[1])

    # Is there an error in the access token info? If so, then stop here.
    if result.get('error') is not None:
        response = make_response(json.dumps(result.get('error')), 500)
        response.headers['Content-Type'] = 'application/json'

    # Is the access token for the intended user?
    google_plus_id = creds.id_token['sub']
    if result['user_id'] != google_plus_id:
        text = "The user ID of the token doesn't match the given user ID."
        response = make_response(json.dumps(text), 401)
        response.headers['Content-Type'] = 'application/json'
        return response

    # Is the access token valid for this app?
    if result['issued_to'] != CLIENT_ID:
        text = 'The client ID of the token does not match that of the app.'
        response = make_response(json.dumps(text), 401)
        print(text)
        response.headers['Content-Type'] = 'application/json'
        return response

    stored_access_token = login_session.get('access_token')
    stored_gplus_id = login_session.get('gplus_id')
    if stored_access_token is not None and google_plus_id == stored_gplus_id:
        response = make_response(json.dumps('Current user is already connected.'), 200)
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

    output = '<h1>Welcome, %s!</h1>' \
        '<img src="%s" style="width: 300px; height: 300px; ' \
        'border-radius: 150px; -webkit-border-radius: 150px; -moz-border-radius: 150px;">'
    output %= login_session['username'], login_session['picture']
    flash('You have successfully logged in as %s' % login_session['username'])
    print('Login completed!')
    return output


# Method: revokes current user's token and resets login_session.
@app.route('/google_logout')
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

        resp = make_response(json.dumps('Session successfully shutdown. Goodbye %s' % usr), 200)
        resp.headers['Content-Type'] = 'application/json'

        return resp
    else:
        # Message about some kind of problem in shutdown procedure.
        resp = make_response(json.dumps("Failure to revoke current user's tokens."), 401)
        resp.headers['Content-Type'] = 'application/json'
        return resp


def create_user(login_session):
    new_usr = Users(
        name=login_session['username'],
        email=login_session['email'],
        picture=login_session['picture'])
    session.add(new_usr)
    session.commit()
    usr = session.query(Users).filter_by(email=login_session['email']).one()
    return usr.id


def get_user_info(user_id):
    usr = session.query(Users).filter_by(id=user_id).one()
    return usr


def get_user_id(email):
    try:
        usr = session.query(Users).filter_by(email=email).one()
        return usr.id
    except:
        return None


if __name__ == '__main__':
    app.secret_key = 'super_secret_key'  # NB. fix this...
    app.debug = True
    app.passthrough_errors = True
    app.run(host='0.0.0.0', port=5000)
