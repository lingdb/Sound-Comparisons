Setup instructions:
===

* Dependencies for `pip` are given in `requirements.txt`.
* Sound files must be placed in [soundcomparisons/static/sound](https://github.com/lingdb/soundcomparisons/tree/master/soundcomparisons/static/sound).
* The expected database location is `mysql://root:@localhost/sndcmp`,
  but can be configured with environment variables or as done in [soundcomparisons/main.py](https://github.com/lingdb/soundcomparisons/blob/master/soundcomparisons/main.py) for testing.
* A `soundcomparisons/client_secrets.json` file is necessary for oauth.
