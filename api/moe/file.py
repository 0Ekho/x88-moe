import logging
from os import path, remove
from flask import abort
from werkzeug import secure_filename
from moe import moe, db
from moe.auth import gen_key

log = logging.getLogger(__name__)


def valid_file_req(req):
    if 'file' not in req.files:
        abort(400, description="no file or incorrect parameter name")

    if req.files.get('file').filename == '':
        abort(400, description="no file provided")

# -------------------------------------


def save_file(reqf, kid):
    name = gen_key(moe.config['API']['item_key_len'])

    orig = secure_filename(reqf.filename)
    log.debug("original sec filename: %s", orig)
    if '.' in orig:
        ext = orig.rsplit('.', 1)[1]
        ext.translate({
            ord(i): None for i in moe.config['FILES']['banned_ext_chars']
        })
        ext = ext[:moe.config['FILES']['max_ext_len']]
        if ext.lower() in moe.config['FILES']['banned_files']:
            abort(415, description="Filetype is banned")

        name = secure_filename(name + '.' + ext)

    # add to db
    del_key = gen_key(moe.config['API']['del_key_len'])

    db.execute('INSERT INTO files (obj, del_key, deleted, key_id) '
               'VALUES (?, ?, 0, ?);', (name, del_key, kid))
    # call commit earlier here to prevent saving file incase of error
    # as if this failed the file would not be able to be deleted
    db.commit()

    reqf.save(path.join(moe.config['FILES']['upload_path'], name))
    return (name, del_key)

# -------------------------------------


def del_file(r_id, obj):
    cur = db.execute('UPDATE files SET deleted=1 WHERE rowid=?;', (r_id,))
    db.commit()
    if cur.rowcount == 0:
        cur.close()
        abort(500, description="failed to mark file as deleted")

    cur.close()
    remove(path.join(moe.config['FILES']['upload_path'], secure_filename(obj)))

# -------------------------------------


def get_file(obj):
    cur = db.execute('SELECT deleted FROM files WHERE obj=?;', (obj,))
    row = cur.fetchone()
    cur.close()

    log.debug("get_file: object: %s", obj)
    if row is None:
        return 404

    if row[0] == 1:
        return 410