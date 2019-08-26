from os.path import basename
from flask import request, jsonify, redirect, abort, escape, render_template
from werkzeug.urls import url_parse
from moe import moe
from moe.auth import check_param, check_api, check_del
from moe.file import valid_file_req, save_file, del_file, get_file
from moe.short import add_link, del_short, get_link

BASEURL = ("https://" if moe.config['CORE']['https'] else "http://")\
    + moe.config['CORE']['domain']

# -----------------------------------------------------------------------------
# file endpoints


@moe.route('/api/v1/upload', methods=['POST'])
def file_upload():
    moe.logger.debug("recived api request for upload")

    valid_file_req(request)  # aborts if invalid

    res = check_api(request)  # aborts if auth fails
    moe.logger.info("upload: auth successful with key ID %s", res[1])

    res = save_file(request.files['file'], res[1])  # aborts if fails
    moe.logger.info("upload: file successfully saved as %s from IP %s", res[0],
                    request.remote_addr)

    return jsonify(ok={
        'url': BASEURL + '/f/' + res[0],
        'del_link':
            BASEURL + '/api/v1/delete?obj=' + res[0] + '&delkey=' + res[1]
    }), 201

# -------------------------------------


@moe.route('/api/v1/get', methods=['GET'])
def file_get():
    moe.logger.debug("recived api request for get file")

    check_param(request.args, 'obj')

    res = get_file(basename(request.args['obj']))
    if res == 404:
        if 'browser' in request.args:
            return render_template(
                'error.html', errmsg='404 Not Found: '
                'The requested URL was not found on the server. '
                'If you entered the URL manually please check your '
                'spelling and try again.'), 404

        abort(404, "object not found")
    if res == 410:
        if 'browser' in request.args:
            return render_template(
                'error.html',
                errmsg="410 Gone: the requested URL has been deleted"), 410

        abort(410, "Gone, object deleted")

    return jsonify(ok={
        'url': BASEURL + '/f/' + basename(request.args['obj'])}), 200

# -------------------------------------


@moe.route('/api/v1/delete', methods=['GET', 'DELETE'])
def file_delete():
    moe.logger.debug("recived api request for delete")

    res = check_del(request, "files")

    del_file(res[1], request.args.get('obj'))

    return jsonify(ok={'msg': "file deleted successfully"}), 200

# -----------------------------------------------------------------------------
# shortlink endpoints

# NOTE: this is known to be risky, does not validate URL and can pretty much
# end up with arbitrary text, possibly allowing for attacks on users opening
# links, however when it already redirects to arbitrary locations there is
# already a risk present (but should still be fixed eventually)


@moe.route('/api/v1/makeshort', methods=['POST'])
def short_make():
    moe.logger.debug("recived api request for makeshort")

    check_param(request.form, 'link')

    res = check_api(request)  # aborts if auth fails
    moe.logger.info("makeshort: auth successful with key ID %s", res[1])

    res = add_link(request.form['link'], res[1])  # aborts if fails
    moe.logger.info("makeshort: link successfully saved as %s from IP %s",
                    res[0], request.remote_addr)

    return jsonify(ok={
        'url': BASEURL + (
            '/r/' if moe.config['SHORT']['use_reveal'] else '/s/') + res[0],
        'del_link':
            BASEURL + '/api/v1/deleteshort?obj=' + res[0] + '&delkey=' + res[1]
    }), 201

# -------------------------------------


@moe.route('/api/v1/getshort', methods=['GET'])
def short_get():
    moe.logger.debug("recived api request for getshort")

    check_param(request.args, 'obj')

    res = get_link(basename(request.args['obj']))
    if not res[0]:
        if res[1] == 404:
            if 'browser' in request.args:
                return render_template(
                    'error.html', errmsg='404 Not Found: '
                    'The requested URL was not found on the server. '
                    'If you entered the URL manually please check your '
                    'spelling and try again.'), 404

            abort(404, "object not found")
        if res[1] == 410:
            if 'browser' in request.args:
                return render_template(
                    'error.html',
                    errmsg="410 Gone: the requested URL has been deleted"
                ), 410

            abort(410, "Gone, object deleted")

    if 'r' in request.args:
        moe.logger.debug('redirecting to %s', res[1])
        purl = url_parse(res[1])
        if purl.scheme == '':
            moe.logger.debug('adding http scheme to url before redirect')
            return redirect('http://' + res[1], code=303)

        return redirect(res[1], code=303)

    if 'browser' in request.args:
        return render_template('reveal.html', link=escape(res[1])), 200

    return jsonify(ok={'url': res[1]}), 200

# -------------------------------------


@moe.route('/api/v1/deleteshort', methods=['GET', 'DELETE'])
def short_delete():
    moe.logger.debug("recived api request for deleteshort")

    res = check_del(request, "shortlinks")

    del_short(res[1])

    return jsonify(ok={'msg': "shortlink deleted successfully"}), 200


# -----------------------------------------------------------------------------
# frontend templating


@moe.route('/', methods=['GET'])
def show_index():
    return render_template('index.html', title=moe.config['CORE']['domain'])


@moe.route('/makeshort', methods=['GET'])
def show_makeshort():
    return render_template('makeshort.html', title="Create shortlink",
                           public=moe.config['API']['public'])


@moe.route('/upload', methods=['GET'])
def show_upload():
    return render_template('upload.html', title="Upload file",
                           public=moe.config['API']['public'])
