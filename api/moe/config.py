import os
import toml

BASEDIR = os.path.abspath(os.path.dirname(__file__))


class Config():
    cfgpath = os.environ.get('MOE_CONFIG') or\
            os.path.join(BASEDIR, '../../config.toml')
    # no try because don't know where to even log yet.
    cfg = toml.load(cfgpath)
    # have no idea the "correct" way to do this in python
    if 'core' not in cfg:
        raise Exception("core section missing from config")
    if 'domain' not in cfg['core'] or\
            not isinstance(cfg['core']['domain'], str):
        raise Exception("core.domain missing from config or not string")
    if 'https' not in cfg['core'] or\
            not isinstance(cfg['core']['https'], bool):
        raise Exception("core.https missing from config or not boolean")
    if 'log_location' not in cfg['core'] or\
            not isinstance(cfg['core']['log_location'], str):
        raise Exception("core.log_location missing from config or not string")
    if 'log_level' not in cfg['core'] or\
            not isinstance(cfg['core']['log_level'], int):
        raise Exception("core.log_level missing from config or not integer")
    if 'database' not in cfg['core'] or\
            not isinstance(cfg['core']['database'], str):
        raise Exception("core.database missing from config or not string")

    if 'api' not in cfg:
        raise Exception("api section missing from config")
    if 'public' not in cfg['api'] or\
            not isinstance(cfg['api']['public'], bool):
        raise Exception("api.public missing from config or not boolean")
    if 'item_key_len' not in cfg['api'] or\
            not isinstance(cfg['api']['item_key_len'], int):
        raise Exception("api.item_key_len missing from config or not integer")
    if 'del_key_len' not in cfg['api'] or\
            not isinstance(cfg['api']['del_key_len'], int):
        raise Exception("api.del_key_len missing from config or not integer")
    if 'key_charset' not in cfg['api'] or\
            not isinstance(cfg['api']['key_charset'], str):
        raise Exception("api.key_charset missing from config or not string")

    if 'files' not in cfg:
        raise Exception("files section missing from config")
    if 'upload_path' not in cfg['files'] or\
            not isinstance(cfg['files']['upload_path'], str):
        raise Exception("files.upload_path missing from config or not string")
    if 'max_filesize' not in cfg['files'] or\
            not isinstance(cfg['files']['max_filesize'], int):
        raise Exception(
                "files.max_filesize missing from config or not integer")
    if 'max_ext_len' not in cfg['files'] or\
            not isinstance(cfg['files']['max_ext_len'], int):
        raise Exception("files.max_ext_len missing from config or not integer")
    if 'banned_ext_chars' not in cfg['files'] or\
            not isinstance(cfg['files']['banned_ext_chars'], str):
        raise Exception(
                "files.banned_ext_chars missing from config or not string")
    if 'banned_files' not in cfg['files'] or\
            not isinstance(cfg['files']['banned_files'], list):
        raise Exception("files.upload_path missing from config or not array "
                        "of strings")
    if 'shorts' not in cfg:
        raise Exception("shorts section missing from config")
    if 'max_link_len' not in cfg['shorts'] or\
            not isinstance(cfg['shorts']['max_link_len'], int):
        raise Exception(
                "shorts.max_link_len missing from config or not integer")
    if 'use_reveal' not in cfg['shorts'] or\
            not isinstance(cfg['shorts']['use_reveal'], bool):
        raise Exception("shorts.use_reveal missing from config or not boolean")
    if 'banned_domains' not in cfg['shorts'] or\
            not isinstance(cfg['shorts']['banned_domains'], list):
        raise Exception("shorts.banned_domains missing from config or not "
                        "array of strings")

    print("log location is at ", cfg['core']['log_location'])

    CORE = cfg['core']
    API = cfg['api']
    FILES = cfg['files']
    SHORT = cfg['shorts']
