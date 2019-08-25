from flask import abort
from werkzeug.urls import url_parse
from moe import moe, db
from moe.auth import gen_key

# -----------------------------------------------------------------------------


def add_link(l, kid):
    if len(l) > moe.config['SHORT']['max_link_len']:
        abort(413, description="link too long")

    purl = url_parse(l)
    # TODO: block subdomains of banned domain
    # ex: example.com blocks sub.example.com
    # but sub.example.com allows example.com
    # for banned_domain if in purl.host?
    if purl.host in moe.config['SHORT']['banned_domains']:
        abort(415, description="domain is banned")

    # add to db
    obj = gen_key(moe.config['API']['item_key_len'])
    del_key = gen_key(moe.config['API']['del_key_len'])

    db.execute('INSERT INTO shortlinks '
               '(obj, location, del_key, deleted, key_id) '
               'VALUES (?, ?, ?, 0, ?);', (obj, l, del_key, kid))
    db.commit()

    return (obj, del_key)

# -------------------------------------


def del_short(r_id):
    cur = db.execute('UPDATE shortlinks SET deleted=1 WHERE rowid=?;', (r_id,))
    db.commit()
    if cur.rowcount == 0:
        cur.close()
        abort(500, description="failed to mark link as deleted")

    cur.close()

# -------------------------------------


def get_link(obj):
    cur = db.execute('SELECT deleted, location FROM shortlinks WHERE obj=?;',
                     (obj,))
    row = cur.fetchone()
    cur.close()

    moe.logger.debug("get_file: object: %s", obj)
    if row is None:
        return (False, 404)

    if row[0] == 1:
        return (False, 410)

    return (True, row[1])
