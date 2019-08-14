import logging
import sqlite3
from flask import Flask
from moe.config import Config

moe = Flask(__name__)
moe.config.from_object(Config)

logging.basicConfig(filename=moe.config['CORE']['log_location'],
                    level=moe.config['CORE']['log_level'])

moe.config['MAX_CONTENT_LENGTH'] = moe.config['FILES']['max_filesize']
moe.logger.info("MAX_CONTENT_LENGTH set to %s",
                moe.config['FILES']['max_filesize'])

try:
    # does this need to be closed? at flask server shutdown?
    # also does it work with multiple concurent requests, or are multiple
    # connections needed?
    db = sqlite3.connect(moe.config['CORE']['database'])
    moe.logger.info("opened database at '%s'", moe.config['CORE']['database'])
except sqlite3.Error:
    moe.logger.critical("unable to open databade at '%s'",
                        moe.config['CORE']['database'])
    raise

# @moe.teardown_appcontext
# def teardown_cleanup(c):

from moe import routes  # noqa: E402,F401
from moe import errors  # noqa: E402,F401
