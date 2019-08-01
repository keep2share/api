API base url: http://keep2share.cc/api/v2


All request parameters must be encoded to JSON and sent using the POST method.
All methods return operation status (success, fail) and additional data in JSON.

# HTTP CODES
```
2xx - Request successful
400 - Bad request (not correct request params)
403 - Authorization required
406 - Not acceptable
429 - Too many requests, please wait 1 minute
```


# METHODS

### Login
```
Login (username, password, captcha_challenge = null, captcha_response = null, re_captcha_challenge = null, re_captcha_response = null) ->
    status_code: [success, fail]
    code: [200, 400, 403, 406]
    auth_token: string
```

```bash
For example:
    request:  curl -d '{"username":"yourbox@gmail.com","password":"yourpassword"}' http://keep2share.cc/api/v2/login
    response: {"status":"success","code":200,"auth_token":"mt2dr45tlnevrjemsq34gnu121"}
```
You must add the `auth_token` to all request methods.

If you have a partner account you can set `access_token` parameter while running any methods from this API.
You can find the `access_token` on the `Tools -> API` page.

### Test
```
Test() ->
    status: [success, fail]
    status_code: [200,406]
    message: string
```

```bash
For example:
    request:  curl -d '{"auth_token":"mt2dr45tlnevrjemsq34gnu121"}' http://keep2share.cc/api/v2/test
    response: {"status":"success","code":200,"message":"Test was successful!"}

    request:  curl -d '{"auth_token":"---wrong_token----"}' http://keep2share.cc/api/v2/test
    response: {"status":"error","code":403,"message":"Authorization session was expired"}
```

### RequestCaptcha
```
RequestCaptcha() ->
    status: [success, fail]
    status_code: [200,406]
    challenge: string
    captcha_url: string
```

### RequestReCaptcha
```
RequestReCaptcha() ->
    status: [success, fail]
    status_code: [200,406]
    challenge: string
    captcha_url: string
```

### GetUrl
The `GetUrl` method uses the required parameters `captcha_challenge` and `captcha_response`.
To obtain these parameters, use the `RequestCaptha` method.
```
GetUrl(
    file_id,
    free_download_key = null,
    captcha_challenge = null,
    captcha_response = null,
    url_referrer = null
) ->
    status: [success, fail]
    status_code: [200,400,406]
    url: string
    free_download_key: string
    time_wait: string
```

### AccountInfo
```
AccountInfo() ->
    status: [success]
    status_code: [200]
    available_traffic: int64 [in bytes]
    account_expires: int [timestamp]
```

### ResellerGetCode
```
ResellerGetCode(days, useExist = true, autoBuy = true) ->
    status: [success, fail]
    status_code: [200,400,406]
    code_id: int
    reseller_code: string
    balance: float
```

### GetFilesList
```
GetFilesList(
    parent = '/', limit = 100, offset = 0, sort = [id=>[-1,1],
    name=>[-1,1], date_created=>[-1,1]], type=>[any,file,folder],
    only_available = false, extended_info = false
) ->
    status: [success, fail]
    status_code: [200]
    files: [
        id: int
        name: string
        is_available: bool
        is_folder: bool
        date_created: string
        size: int @in bytes
        md5: string
        extended_info: [
            abuses: []
            storage_object: [available|deleted|corrupted]
            size: int
            date_download_last: string
            downloads: int
            access: string
            content_type: string
        ]
    ]
 sort by id=-1 - DESC
 sort by id=1 - ASC
```

### CreateFolder
```
CreateFolder(name, parent ['/' or parent_id], access [public, private, premium], is_public = false) ->
    status: [success, fail]
    status_code: [201,400,406]
    id: int @id new folder
```

### UpdateFile
```
UpdateFile(id, new_name = null, new_parent = null, new_access = null, new_is_public = null) ->
    status: [success, fail]
    status_code: [202,400,406]
```

### UpdateFiles
```
UpdateFiles(ids[], new_name = null, new_parent = null, new_access = null, new_is_public = null) ->
    status: [success]
    status_code: [200]
    files: [
        id: string,
        status: [success, error]
        errors: [] @if error
    ]
```

### GetBalance
```
GetBalance() ->
    status: [success]
    status_code: [200]
    balance: float
```

### GetFilesInfo
```
GetFilesInfo(ids[], extended_info = false) ->
    status: [success]
    status_code: [200,400]
    files: [
        id: int
        name: string
        size int
        is_available: bool
        access: ['public', 'private', 'premium']
        is_folder: bool
        md5: string
        extended_info: [
            abuses: []
            storage_object: [available|deleted|corrupted]
            size: int
            date_download_last: string
            downloads: int
            access: string
            content_type: string
        ]
    ]
```

### RemoteUploadAdd
```
RemoteUploadAdd(urls[]) ->
    status: [success]
    status_code: [200,400]
    acceptedUrls: []
    rejectedUrls: []
```

### RemoteUploadStatus
```
RemoteUploadStatus(ids[])
    status: [success]
    status_code: [200,400]
    uploads: [
        status: int @[1: new, 2: processing, 3: completed, 4:error, 5: accepted]
        progress: int @percents
        file_id: [string,null]
    ]
```

### FindFile
```
FindFile(md5)
    status: [success]
    status_code: [200,400]
    found: bool
```

### CreateFileByHash
```
CreateFileByHash(hash, name, parent = null, access = public) ->
    status: [success, error]
    status_code: [201,400,406]
    id: int @if created new file
    errors: [] @if error
```

### GetUploadFormData
```
GetUploadFormData(parent_id = null, preferred_node = null) ->
    status: [success]
    status_code: [200,400]
    form_action: string
    file_field: string
    form_data: [
        params: string
        signature: string
    ]
```

### DeleteFiles
```
DeleteFiles(ids[]) ->
    status: [success]
    status_code: [200]
    deleted: int @count of deleted files
```

### FindByFullMd5Hash
```
FindByFullMd5Hash(md5) ->
    status: [success,error]
    status_code: [200,400]
    exists: bool
    md5: string
```

### FindByFirst5MbSha1Hash
```
FindByFirst5MbSha1Hash(sha1) ->
    status: [success,error]
    status_code: [200,400]
    exists: bool
```

### GetFileStatus
```
GetFileStatus($id, $limit = 100, $offset = 0) ->
    status: [success,error]
    status_code: [200,406]
    errorCode: [not_found,deleted,abused,blocked] @if error
    files: [
        id: int
        name: string
        is_available: int
        is_folder: int
        date_created: string
        size: float
        md5: [string,null]
    ]
```

### GetDomainsList
```
GetDomainsList() ->
    status: [success, error]
    status_code: [200,400]
    domains: [string]
```

### GetFolderList
```
GetFoldersList() ->
    status: [success, error]
    code: [200,400]
    foldersList: [string]
```

# API ERRORS
```ini
    ERROR_INCORRECT_PARAM_VALUE = 3;

    ERROR_YOU_ARE_NEED_AUTHORIZED = 10;

    ERROR_FILE_NOT_FOUND = 20;
    ERROR_FILE_IS_NOT_AVAILABLE = 21;
    ERROR_FILE_IS_BLOCKED = 22;

    ERROR_CAPTCHA_REQUIRED = 30;
    ERROR_CAPTCHA_INVALID = 31;
    ERROR_RE_CAPTCHA_REQUIRED = 33;

    ERROR_WRONG_FREE_DOWNLOAD_KEY = 40;
    ERROR_NEED_WAIT_TO_FREE_DOWNLOAD = 41;
    ERROR_DOWNLOAD_NOT_AVAILABLE = 42;

    ERROR_NO_AVAILABLE_RESELLER_CODES = 50;
    ERROR_BUY_RESELLER_CODES = 51;

    ERROR_CREATE_FOLDER = 60;
    ERROR_UPDATE_FILE = 61;
    ERROR_COPY_FILE = 62;
    ERROR_NO_AVAILABLE_NODES = 63;

    ERROR_INCORRECT_USERNAME_OR_PASSWORD = 70;
    ERROR_LOGIN_ATTEMPTS_EXCEEDED = 71;
    ERROR_ACCOUNT_BANNED = 72;
    ERROR_NO_ALLOW_ACCESS_FROM_NETWORK = 73;
    ERROR_UNKNOWN_LOGIN_ERROR = 74;
    ERROR_ILLEGAL_SESSION_IP = 75;
    ERROR_ACCOUNT_STOLEN = 76;
    ERROR_NETWORK_BANNED = 77;

    //billings
    ERROR_BILLING_WRONG_ACCESS_API_KEY = 80;
    ERROR_BILLING_TRANSACTION_NOT_FOUND = 81;

    Additional download errors:
    DOWNLOAD_COUNT_EXCEEDED = 1;     //'Download count files exceed'
    DOWNLOAD_TRAFFIC_EXCEEDED = 2;   //'Traffic limit exceed'
    DOWNLOAD_FILE_SIZE_EXCEEDED = 3; //"Free user can't download large files. Upgrade to PREMIUM and forget about limits."
    DOWNLOAD_NO_ACCESS = 4;          //'You no can access to this file'
    DOWNLOAD_WAITING = 5;            //'Please wait to download this file'
    DOWNLOAD_FREE_THREAD_COUNT_TO_MANY = 6; //'Free account does not allow to download more than one file at the same time'
    PREMIUM_ONLY = 7;                //'This download available only for premium users',
    PRIVATE_ONLY = 8;                //'This is private file',
```
