OpenCart-Error-Helper
=====================

Helper to handle php errors gracefully in OpenCart

## Installation

- Edit your index.php and remove the existing set_error_handler()
- Edit system/startup.php and add the following line at the end

```php
require_once(DIR_SYSTEM . 'helper/error.php');
```

Be sure to edit the email that the errors will be sent to and the custom error page location.