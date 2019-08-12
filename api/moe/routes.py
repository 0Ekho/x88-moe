import logging
from os.path import basename
from flask import request, jsonify, redirect, abort
from werkzeug.urls import url_parse
from moe import moe
from moe.auth import check_param, check_api, check_del
from moe.file import valid_file_req, save_file, del_file, get_file
from moe.short import add_link, del_short, get_link

log = logging.getLogger(__name__)

BASEURL = "https://" if moe.config['CORE']['https'] else "http://"\
    + moe.config['CORE']['domain']

# -----------------------------------------------------------------------------
# file endpoints


@moe.route('/api/v1/upload', methods=['POST'])
def file_upload():
    log.debug("recived api request for upload")

    valid_file_req(request)  # aborts if invalid

    res = check_api(request)  # aborts if auth fails
    log.info("upload: auth successful with key ID %s", res[1])

    res = save_file(request.files['file'], res[1])  # aborts if fails
    log.info("upload: file successfully saved as %s from IP %s", res[0],
             request.remote_addr)

    return jsonify(ok={
        'url': BASEURL + '/f/' + res[0],
        'del_key':
            BASEURL + '/api/v1/delete?obj=' + res[0] + '&delkey=' + res[1]
    }), 201

# -------------------------------------


@moe.route('/api/v1/get', methods=['GET'])
def file_get():
    log.debug("recived api request for get file")

    check_param(request.args, 'obj')

    res = get_file(basename(request.args['obj']))
    if res == 404:
        if 'browser' in request.args:
            return '404 Not Found: The requested URL was not found on the '\
                'server. If you entered the URL manually please check your '\
                'spelling and try again.', 404

        abort(404, "object not found")
    if res == 410:
        if 'browser' in request.args:
            return "410 Gone: the requested URL has been deleted", 410

        abort(410, "Gone, object deleted")

    return jsonify(ok={
        'url': BASEURL + '/f/' + basename(request.args['obj'])}), 200

# -------------------------------------


@moe.route('/api/v1/delete', methods=['GET'])
def file_delete():
    log.debug("recived api request for delete")

    res = check_del(request, "files")

    del_file(res[1], request.args.get('obj'))

    return jsonify(ok={'msg': "file deleted successfully"}), 200

# -----------------------------------------------------------------------------
# shortlink endpoints


@moe.route('/api/v1/makeshort', methods=['POST'])
def short_make():
    log.debug("recived api request for makeshort")

    check_param(request.form, 'link')

    res = check_api(request)  # aborts if auth fails
    log.info("makeshort: auth successful with key ID %s", res[1])

    res = add_link(request.form['link'], res[1])  # aborts if fails
    log.info("makeshort: link successfully saved as %s from IP %s", res[0],
             request.remote_addr)

    return jsonify(ok={
        'url': BASEURL + (
            '/r/' if moe.config['SHORT']['use_reveal'] else '/s/') + res[0],
        'del_key':
            BASEURL + '/api/v1/deleteshort?obj=' + res[0] + '&delkey=' + res[1]
        }), 201

# -------------------------------------


@moe.route('/api/v1/getshort', methods=['GET'])
def short_get():
    log.debug("recived api request for getshort")

    check_param(request.args, 'obj')

    res = get_link(basename(request.args['obj']))
    if not res[0]:
        if res[1] == 404:
            if 'browser' in request.args:
                return "404 Not Found: The requested URL was not found on "\
                    "the server. If you entered the URL manually please "\
                    "check your spelling and try again.", 404

            abort(404, "object not found")
        if res[1] == 410:
            if 'browser' in request.args:
                return "410 Gone: the requested URL has been deleted", 410

            abort(410, "Gone, object deleted")

    if 'r' in request.args:
        log.debug('redirecting to %s', res[1])
        purl = url_parse(res[1])
        if purl.scheme == '':
            log.debug('adding http scheme to url before redirect')
            return redirect('http://' + res[1], code=303)

        return redirect(res[1], code=303)

    if 'browser' in request.args:
        # TODO: add some minimal HTML to make this a link, but disable clicking
        return "Link redirects to: " + res[1], 200

    return jsonify(ok={'url': res[1]}), 200

# -------------------------------------


@moe.route('/api/v1/deleteshort', methods=['GET'])
def short_delete():
    log.debug("recived api request for deleteshort")

    res = check_del(request, "shortlinks")

    del_short(res[1])

    return jsonify(ok={'msg': "shortlink deleted successfully"}), 200
