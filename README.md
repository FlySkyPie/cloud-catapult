Cloud Catapult
===

Simply catapult file to Google drive .

Introdution
---

Google Drive prepared GUI that you can manage your file on browser, but it's tree structure. If you want to shared files to other people and do not cared files alive or dead, those file usually mess up your "private space". This package allow user upload a file, it'll upload to your Google Drive account, and be set up in read only public file.

### Required
* vlucas/phpdotenv
* google/apiclient

Usage
---

1. go to [Google API](https://developers.google.com/drive/api/v3/quickstart/php) get credentials, and download the json.
2. configure your Google OAuth json path to .env:

```
OAUTH_CREDENTIALS_PATH="/the/folder/you/put/json/"
OAUTH_TOKEN_PATH="/the/folder/you/put/json/"
```

2. execute authorization wizard in CLI to get toekn:

```shell
cd example
php AuthorizationWizard.php
```

3. copy the id of root folder to .env:

```
CLOUD_TARGET_ID="blabalbal"
```

4. now you are ready to upload file

```php
$Catapult = new Catapult();
$FileId = $Catapult->shoot($_FILES['fileToUpload']);
```



After finish the process above, you can check example to understand how it work,

execute `php -S localhost:8000` under `example`,

then  access the page by browser and tried upload something. : )