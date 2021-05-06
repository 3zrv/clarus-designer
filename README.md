# clarus-designer
The online web designer for the print-on-demand-service

# System Status
PHP must allows functions:
* stream_context_create
* libxml_set_streams_context
* simplexml_load_file
* ZipArchive
* Imagick
* file_put_contents
* file_get_contents

### System resource – PHP settings
* allow_url_fopen
* memory_limit – recommend 256MB
* post_max_size – recommend 800MB

### Why Memory & Post-Data?

While working with tools, it doesn’t require a little resource from the server. The progress base on your client's performance.
Your customer may add many resources; total capacity data is big sometimes.

So when pressing the checkout, the server has to use the number of resources to process them.
The server needs to process two essential things:
1. Amount data can put to the server based on users data. PHP has setting post_max_size to control that.
2. Another thing is the memory for PHP process. It may use 512MB of RAM in short time.

We need to increase the PHP setting to increase available memory. PHP has memory_limit to control it.
How to set that value
Often hosting supports the method for edit the .ini file
You can find the setting PHP Options with the value above.
Or
In case, your hosting support custom php.ini / .user.ini file for any folder

Kindly create that file php.ini / .user.ini to the root folder of website with values below:

```
memory_limit = 256M
post_max_size = 800M
allow_url_fopen = -1
extension=imagick.so
```
