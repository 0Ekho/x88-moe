from flask import jsonify
from moe import moe
import werkzeug

# -----------------------------------------------------------------------------
# error handlers


@moe.errorhandler(400)
def err_bad_req(err):
    moe.logger.info("served 400: %s", str(err))
    return jsonify(error=str(err)), 400


@moe.errorhandler(403)
def err_auth_fail(err):
    moe.logger.info("served 403: %s", str(err))
    return jsonify(error=str(err)), 403


@moe.errorhandler(404)
def err_not_found(err):
    moe.logger.debug("served 404: %s", str(err))
    return jsonify(error=str(err)), 404


@moe.errorhandler(410)
def err_file_gone(err):
    moe.logger.info("served 410: %s", str(err))
    return jsonify(error=str(err)), 410


@moe.errorhandler(413)
def err_req_to_large(err):
    moe.logger.info("served 413: %s", str(err))
    return jsonify(error=str(err)), 413


@moe.errorhandler(415)
def err_bad_filetype(err):
    moe.logger.info("served 415: %s", str(err))
    return jsonify(error=str(err)), 415


@moe.errorhandler(418)
def err_teapot(err):
    moe.logger.critical("served 418: %s", str(err))
    return jsonify(error=str(err)), 418

# -------------------------------------


@moe.errorhandler(500)
def err_internal(err):
    moe.logger.warning("served 500: %s", str(err))
    return jsonify(error=str(err)), 500


def err_no_space(err):
    moe.logger.warning("served 507: %s", str(err))
    return jsonify(error=str(err)), 507


class InsufficientStorage(werkzeug.exceptions.HTTPException):
    code = 507
    description = 'Not enough storage space.'


moe.register_error_handler(InsufficientStorage, err_no_space)
