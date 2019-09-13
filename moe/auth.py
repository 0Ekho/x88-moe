import secrets
from flask import abort
from moe import moe, get_db

# -----------------------------------------------------------------------------


def check_param(req, prm):
    """Check if request contains parameter and is non empty, else abort"""
    if prm not in req:
        abort(400, description="no " + prm + " parameter")

    if req.get(prm) == '':
        abort(400, description="no " + prm + " provided")


def gen_key(length):
    return ''.join(secrets.choice(moe.config['API']['key_charset'])
                   for _ in range(length))

# -------------------------------------


def check_api(req):
    """Check if request satisfies API key requirements

    check if API key is even required
    if it is, check if the key is present in the request, and if it passes auth
    return tuple with if auth passed and the API key ID (0 if no API key)
    abort if the API key is invalid
    """
    if moe.config['API']['public']:
        return (True, 0)

    check_param(req.form, 'apikey')

    res = check_api_key(req.form.get('apikey'))
    if not res[0]:
        abort(403, description="Invalid API key")

    return res


def check_api_key(key):
    """Check is api key is valid and get its row ID"""
    cur = get_db().execute(
        'SELECT valid, id FROM apikeys WHERE key=?;', (key,)
    )
    row = cur.fetchone()
    if row is None:
        moe.logger.debug("no API keys found")
        return (False, 0)

    cur.close()
    moe.logger.debug("found API key at index %s, with validity %s",
                     row[1], row[0])
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
    """Checks if delete key is valid for object in table and gets its row ID

    Aborts if already deleted
    Returns a tuple containing if the deletion key was valid and if it was, the
    row ID of the object
    """
    # table is not user provided and is hardcoded up the stack so no SQLi
    cur = get_db().execute('SELECT rowid, deleted FROM ' + table
                           + ' WHERE obj=? AND del_key=?;', (obj, key))
    row = cur.fetchone()
    if row is None:
        moe.logger.debug("incorrect object %s or deletion key %s", obj, key)
        return (False, 0)

    cur.close()

    if row[1] == 1:
        abort(410, "object already deleted")

    moe.logger.debug("found valid object at index %s,", row[0])
    return (True, row[0])
