#!/usr/bin/env python3

import os
import secrets
import sqlite3
import sys
import toml

if len(sys.argv) < 2:
    print("Please specify name for key")
    sys.exit()

name = sys.argv[1]

BASEDIR = os.path.abspath(os.path.dirname(__file__))
cfgpath = os.environ.get('MOE_CONFIG') or\
    os.path.join(BASEDIR, '../config.toml')
cfg = toml.load(cfgpath)
if 'core' not in cfg:
    raise Exception("core section missing from config")
if 'database' not in cfg['core'] or\
        not isinstance(cfg['core']['database'], str):
    raise Exception("core.database missing from config or not string")

if 'api' not in cfg:
    raise Exception("api section missing from config")
if 'key_charset' not in cfg['api'] or\
        not isinstance(cfg['api']['key_charset'], str):
    raise Exception("api.key_charset missing from config or not string")


key = ''.join(secrets.choice(cfg['api']['key_charset'])
              for _ in range(48))

db = sqlite3.connect(cfg['core']['database'])
db.execute('INSERT INTO apikeys (key, valid, name) '
           'VALUES (?, 1, ?);', (key, name))
db.commit()
db.close()

print("key:", key, "name:", name, "added")
