from clldutils.dsv import reader
from clldutils.path import Path


if __name__ == '__main__':
    for path in Path('.').glob('*.txt'):
        items = list(reader(path, dicts=True))
        print path.name
        print 'rows:', len(items)
        if items:
            print 'columns:'
            for k in items[0].keys():
                print k.encode('utf8')
        print
