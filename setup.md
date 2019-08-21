## basic setup

make data directories
```
$ mkdir -p data/public
```

link file directory, optionally just make a normal directory if you have enough space
```
$ ln -s /bulk/storage/location data/public/f
```

install venv and dependencies
```
$ virtualenv -p python3 api/venv
$ source api/venv/bin/activate
$ pip install toml flask uwsgi
```

create the database, and set permissions
```
$ sqlite3 data/x88.sqlite < sys/create_db.sql
# chown webmin:www-data data/x88.sqlite
# chmod 660 data/x88.sqlite
```

configure API
```
$ cp config.example.toml config.toml
```
change options as needed, domain and path locations are almost certain to need changes, and
preferably enable https

"api/moe/templates/index.html" should be edited however for your index page

configure nginx
```
# cp sys/nginx_x88-moe /etc/nginx/sites-available/x88-moe
```
and edit the 3 root paths, 4 domains, and (optionally) setup TLS appropriately
you may also need to configure your socket appropriately

you may have to create the log file and give correct permissions
```
# touch /var/log/x88-moe.log
# chown webmin:www-data /var/log/x88-moe.log
# chmod 660 /var/log/x88-moe.log
```

setup uwsgi (systemd, bleh)
// TODO:


finally, create your API key, if needed. make one for each user you want to have access
```
$ sys/add_api.py 'user name'
```


// TODO: better permissions for security, do not want to be able to write to api or public,
only data
