import logging
import sqlite3
from flask import Flask, g
from moe.config import Config

moe = Flask(__name__)
moe.config.from_object(Config)

moe.version = "0.0.1-alpha"

logging.basicConfig(filename=moe.config['CORE']['log_location'],
                    level=moe.config['CORE']['log_level'])

moe.logger.info("starting version %s", moe.version)

moe.config['MAX_CONTENT_LENGTH'] = moe.config['FILES']['max_filesize']
moe.logger.info("MAX_CONTENT_LENGTH set to %s",
                moe.config['FILES']['max_filesize'])


def get_db():
    db = getattr(g, '_database', None)
    if db is None:
        try:
            db = g._database = sqlite3.connect(moe.config['CORE']['database'])
            moe.logger.info("opened database at '%s'",
                            moe.config['CORE']['database'])
        except sqlite3.Error:
            moe.logger.critical("unable to open databade at '%s'",
                                moe.config['CORE']['database'])
            raise
    return db


@moe.teardown_appcontext
def teardown_appcontext(exception):
    db = getattr(g, '_database', None)
    if db is not None:
        db.close()


from moe import routes  # noqa: E402,F401
from moe import errors  # noqa: E402,F401
