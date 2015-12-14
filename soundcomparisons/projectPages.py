# -*- coding: utf-8 -*-
from __future__ import unicode_literals
'''
    This module was added to provide project pages.
    See #250 [1] for a details.
    Some code was inspired by a snippet [2] found online.
    [1]: https://github.com/sndcomp/website/issues/250
    [2]: http://flask.pocoo.org/snippets/118/
'''

import flask
import requests
import re

import db


def checkUrl(url):
    '''
        @param url String
        Provides the project page for the specified url, iff that url appears to be ok.
        Otherwise returns an error message.
    '''
    # Is url not absolute?
    match = re.match("^[^:]*:\/\/", url)
    if match != None:
        msg = 'Sorry, the requested url "{}" is not allowed.'.format(url)
        return msg, 403
    # Is url the name of a Family?
    match = re.match("^([^\/]*)/(.*)$", url)
    if match:
        try:
            family = getFamily(match.group(1))
            url = family.ProjectAboutUrl + match.group(2)
            return streamUrl(url)
        except:
            pass
    # Redirect if url is just a plain family name:
    try:
        family = getFamily(url)
        flask.redirect(url + '/')
    except:
        pass
    # Fail:
    msg = 'Sorry, the requested url "{}" could not be found.'.format(url)
    return msg, 404


def getFamily(name):  # May throw!
    family = db.getSession().query(db.Families).filter_by(FamilyNm=name).limit(1).one()
    if family.ProjectActive:
        return family
    raise RuntimeError("Family {} is not marked as active.".format(name))


def streamUrl(url):
    req = requests.get(url, stream=True)
    return flask.Response(
        flask.stream_with_context(req.iter_content()),
        content_type=req.headers['content-type'])
