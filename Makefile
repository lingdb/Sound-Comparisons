# Start main.py with virtualenv python:
main:
	./bin/python main.py

# Setup virtualenv and install necessary packages:
install:
	virtualenv -p python2.7 .
	./bin/pip install -r requirements.txt

# Remove stuff created by install or python byte code:
clean:
	rm -rf bin include lib pip-selfcheck.json *.pyc

# List notes in the code:
fixme:
	find -type f -regex .*py | grep -v "./lib" | grep -v "./bin/" | xargs grep 'FIXME\|TODO'

pep8:
	find -type f -name "*.py" | grep -v "lib\|bin" | xargs ./bin/pep8
