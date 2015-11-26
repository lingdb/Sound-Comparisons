# -*- coding: utf-8 -*-
from soundcomparisons import app
from soundcomparisons import config


if __name__ == "__main__":
    app.run(host=config.host, port=config.port)
