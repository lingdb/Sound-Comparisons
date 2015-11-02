# -*- coding: utf-8 -*-
'''
    This file will be copied to 'config.py' by Vagrant setup.
    All basic configuration should be contained in here,
    so that it's easy to adjust on a per deployment basis.
'''
import json

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

'''
    For some stuff a secret key is necessary.
    The below should therefore be changed in config.py.
'''
_secret_key = 'Zooj8eegie4sheequ2ohfoh6pu0goKae'

def getSecretKey():
    import config_example as example
    if _secret_key == example._secret_key:
        print 'CHANGE THE SECRET KEY IN config.py!'
    return _secret_key

# Used for memoization of getOAuth:
_getOAuth_memo = {'loaded': False, 'data': None}

'''
    @return dict {'web': {'client_id':…, 'auth_uri':…, 'token_uri':…,
                          'auth_provider_x509_cert_url':…, 'client_secret':…,
                          'redirect_uris':…, 'javascript_origins':…}}
'''
def getOAuth():
    if not _getOAuth_memo['loaded']:
        _getOAuth_memo['data'] = json.loads(open('client_secrets.json', 'r').read())
        _getOAuth_memo['loaded'] = True
    return _getOAuth_memo['data']


# Test self:
if __name__ == "__main__":
    print getOAuth()
