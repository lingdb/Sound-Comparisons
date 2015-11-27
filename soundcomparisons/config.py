# -*- coding: utf-8 -*-
'''
Default configuration settings
'''
import os
import json

import soundcomparisons


SQLALCHEMY_DATABASE_URI = 'mysql://root:@localhost/sndcmp'
SQLALCHEMY_TRACK_MODIFICATIONS = False
SECRET_KEY = 'Zooj8eegie4sheequ2ohfoh6pu0goKae'

# Used for memoization of getOAuth:
_OAuth = None
CLIENT_SECRETS = os.path.join(
    os.path.dirname(soundcomparisons.__file__), '..', 'client_secrets.json')


def getOAuth():
    '''
        @return dict {'web': {'client_id':…, 'auth_uri':…, 'token_uri':…,
                              'auth_provider_x509_cert_url':…, 'client_secret':…,
                              'redirect_uris':…, 'javascript_origins':…}}
    '''
    global _OAuth
    if not _OAuth:
        with open(CLIENT_SECRETS) as fp:
            _OAuth = json.load(fp)
    return _OAuth
