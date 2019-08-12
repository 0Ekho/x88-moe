import secrets
import logging
from flask import abort
from moe import moe, db

log = logging.getLogger(__name__)

# -----------------------------------------------------------------------------


def check_param(req, prm):
    if prm not in req:
        abort(400, description="no " + prm + " parameter")

    if req.get(prm) == '':
        abort(400, description="no " + prm + " provided")


def gen_key(length):
    return ''.join(secrets.choice(moe.config['API']['key_charset'])
                   for _ in range(length))

# -------------------------------------


def check_api(req):
    if moe.config['API']['public']:
        return (True, 0)

    check_param(req.form, 'apikey')

    res = check_api_key(req.form.get('apikey'))
    if not res[0]:
        abort(403, description="Invalid API key")

    return res


def check_api_key(key):
    cur = db.execute('SELECT valid, id FROM apikeys WHERE key=?;', (key,))
    row = cur.fetchone()
    if row is None:
        log.debug("no API keys found")
        return (False, 0)

    cur.close()
    log.debug("found API key at index %s, with validity %s", row[1], row[0])
    return (bool(row[0]), row[1])

# -------------------------------------


def check_del(req, table):
    check_param(req.args, 'delkey')
    check_param(req.args, 'obj')

    res = check_del_key(req.args.get('delkey'), req.args.get('obj'), table)
    if not res[0]:
        abort(403, description="Invalid delkey or obj")

    return res


def check_del_key(key, obj, table):
    # table is not user provided and is hardcoded up the stack so no SQLi
    cur = db.execute('SELECT rowid, deleted FROM ' + table +
                     ' WHERE obj=? AND del_key=?;', (obj, key))
    row = cur.fetchone()
    if row is None:
        log.debug("incorrect object %s or deletion key %s", obj, key)
        return (False, 0)

    cur.close()

    if row[1] == 1:
        abort(410, "object already deleted")

    log.debug("found valid object at index %s,", row[0])
    return (True, row[0])
