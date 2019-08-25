## v1 API

### File endpoints
* upload - POST
  * Required parameters
    * file - File to be uploaded
    * apikey (Only if site is not set to public) - API key given by admin

* get - GET
  * Required parameters
    * obj - object key (filename)
  * Optional parameters
    * browser - value meaningless, if parameter is present response will be plaintext / html
    
* delete - GET, DELETE
  * Required parameters
    * obj - object key (filename)
    * delkey - deletion key for object


### Shortlink endpoints
* makeshort - POST
  * Required parameters
    * link - URL to be shortened
    * apikey (Only if site is not set to public) - API key given by admin

* getshort - GET
  * Required parameters
    * obj - object key (filename)
  * Optional parameters
    * r - value meaningless, if parameter is present will respond with redirect instead of json
    * browser (r overrides this parameter) - value meaningless, if parameter is present response will be plaintext / html

* deleteshort - GET, DELETE
  * Required parameters
    * obj - object key
    * delkey - deletion key for object

### JSON Responses
success on upload or makeshort
```
{ok: {
  url: "<url for viewing>",
  deletion_link : "<link containing prefilled object and deletion key>"
}}
```
sucess on delete or deleteshort
```
{ok: {
  msg: "<message stating success>"
}}
```
success on get or getshort
```
{ok: {
  url: "<url for viewing file or url that the shortlink points to>"
}}
```
errors (will have apropriate http status code)
```
{error: "<error description>"}
```
