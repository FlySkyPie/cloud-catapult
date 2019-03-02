Cloud Catapult
===

Simply catapult file to Google drive .

Introdution
---

Google Drive prepared GUI that you can manage your file on browser, but it's tree structure.

If you want to shared files to other people and do not cared files alive or dead, those file usually mess up your "private space".

This package allow user upload a file, it'll upload to your Google Drive account, and be set up in read only public file.

Usage
---

1. configure your Google OAuth json path to .env:

```
OAUTH_CREDENTIALS_PATH="/the/folder/you/put/json/"
OAUTH_TOKEN_PATH="/the/folder/you/put/json/"
CLOUD_TARGET_ID=""
```

2. execute authorization wizard in CLI to get toekn:

```php
require __DIR__ . '/../vendor/autoload.php';

use FlySkyPie\CloudCatapult\AuthorizationWizard;

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

AuthorizationWizard::start();
```

3. copy the id of root folder to .env:

```
CLOUD_TARGET_ID="<copy id to here>"
```

4. now you are ready to upload file

```php
$Catapult = new Catapult();
$FileId = $Catapult->shoot($_FILES['fileToUpload']);
```

