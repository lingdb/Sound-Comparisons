# Start main.py with virtualenv python:
main:
	./bin/python main.py

# Setup virtualenv and install necessary packages:
install:
	virtualenv -p python2.7 .
	./bin/pip install flask Flask-SQLAlchemy MySQL-python

# Remove stuff created by install or python byte code:
clean:
	rm -rf bin include lib pip-selfcheck.json *.pyc

# List notes in the code:
fixme:
	find -type f -regex .*py | grep -v "./lib" | grep -v "./bin/" | xargs grep 'FIXME\|TODO'
