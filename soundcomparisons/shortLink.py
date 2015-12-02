# -*- coding: utf-8 -*-
'''
    Tries to reproduce the behaviour of php/query/shortLink.php
    which is to shorten URLs and store them in the Page_ShortLinks table.
'''

import flask
import hashlib

import db


def shorten(url):
    '''
        @param url String
        @return {url: String, hex: String, str: String}
        Takes a url and shortens it.
        The returned dict contains the original url, the hex computed from it,
        and the shortened str of the hex.
    '''
    # Alphabet to use for link shortening:
    alphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-+'
    # Hash to translate into alphabet:
    hash = hashlib.md5(url).hexdigest()
    # nibbles from hash as ints:
    nibbles = [int(x, 16) for x in hash]
    # Producing str, which is the translation of hash into the alphabet:
    str = ''
    for i in xrange(len(hash)):
        mod = i % 3
        bits = 0
        if mod == 0:
            bits = (nibbles[i] << 2) + (nibbles[i + 1] >> 2)
        elif mod == 2:
            bits = ((nibbles[i - 1] & 3) << 4) + nibbles[i]
        else:
            continue
        str += alphabet[bits]
    # Returning computed data:
    return {
        'url': url,
        'hex': hash,
        'str': str
    }


def insert(url):
    '''
        @param url String
        @return shortLink db.ShortLinks
        Inserts a url into the database to store it as a shortLink.
        Returns the produced shortLink or one with the same hash.
    '''
    # Transform a ShortLinks instance to a dict like it's returned by shorten:
    def toShortUrlDict(shortLink):
        return {
            'url': shortLink.Target,
            'hex': shortLink.Hash,
            'str': shortLink.Name
        }
    # Shortened data:
    s = shorten(url)

    def _insert():
        # Helper to find prefix…
        for i in xrange(1, len(s['str']) + 1):
            prefix = s['str'][:i]
            # Find first non inserted prefix:
            try:
                db.getSession().query(db.ShortLinks).filter_by(Name=prefix).limit(1).one()
                continue
            except:
                entry = db.ShortLinks(Hash=s['hex'], Name=prefix, Target=s['url'])
                db.getSession().add(entry)
                db.getSession().commit()
                print 'Inserted!'
                return toShortUrlDict(entry)
    # Return existing entry rather than creating a new one:
    try:
        exists = db.getSession().query(db.ShortLinks).filter_by(Hash=s['hex']).limit(1).one()
        return toShortUrlDict(exists)
    except:
        return _insert()

'''
  public static function handlePost(){
    if(!array_key_exists('createShortLink', $_POST)) return;
    //Creating ShortLink:
    $arr = self::insert($_POST['createShortLink']);
    //Making sure $arr is an array:
    if($arr instanceof Exception){
      $arr = array('error' => $arr->getMessage());
    }
    //Producing output:
    Config::setResponseJSON();
    echo Config::toJSON($arr);
'''


def addShortLink():
    '''
        Provides the shortlink addition funcitonality.
        Expected to handle POST requests carrying a createShortLink parameter.
        Usual route is /query/shortLink
    '''
    if flask.request.method == 'POST':
        if 'createShortLink' in flask.request.args:
            # FIXME IMPLEMEMT
            # i = insert(flask.request.args['createShortLink'])
            pass
        else:
            return 'Please specify a createShortLink parameter.', 400
    else:
        return 'Please do a POST request with a createShortLink parameter.', 400

if __name__ == "__main__":
    urls = ['foobar', '#/config/?families=11&language=11111230301&languages=11002000000,11111110102,11111230301,11131000008,11141230509,11151120109,11161010008,11161960109,11161180509&mapViewIgnoreSelection=false&meaningGroups= &pageView=language&phLang=11002000000&regions= &siteLanguage=1&study=Germanic&translation=1&word=4410&wordByWord=false&wordOrder=logical&words=10,20,30,40,50']
    for u in urls:
        x = insert(u)
        print x.toDict()
