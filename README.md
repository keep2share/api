API base url: http://keep2share.cc/api/v1


All request parameters must be encoded to JSON and sent using the POST method
All methods return operation status (success, fail) and additional data in JSON

HTTP CODES
```
2xx - Request successful
400 - Bad request (not correct request params)
403 - Authorization required
406 - Not acceptable
429 - Too many requests, please wait 1 minute
```


METHODS

```
Login (username, password) ->
    status_code: [success, fail]
    code: [200, 400, 403]
    auth_token: string

    For example:
        request:  curl -d '{"username":"yourbox@gmail.com","password":"yourpassword"}' http://keep2share.cc/api/v1/login
        response: {"status":"success","code":200,"auth_token":"mt2dr45tlnevrjemsq34gnu121"}

    auth_token must be added as param for all request methods

Test() ->
    status: [success, fail]
    status_code: [200,406]
    message: string

    For example:
        request:  curl -d '{"auth_token":"mt2dr45tlnevrjemsq34gnu121"}' http://keep2share.cc/api/v1/test
        response: {"status":"success","code":200,"message":"Test was successful!"}
    
        request:  curl -d '{"auth_token":"---wrong_token----"}' http://keep2share.cc/api/v1/test
        response: {"status":"error","code":403,"message":"Authorization session was expired"}


RequestCaptcha() ->
    status: [success, fail]
    status_code: [200,406]
    challenge: string
    captcha_url: string

GetUrl(file_id, free_download_key = null, captcha_challenge = null, captcha_response = null) ->
    status: [success, fail]
    status_code: [200,400,406]
    url: string
    free_download_key: string
    time_wait: string

AccountInfo() ->
    status: [success]
    status_code: [200]
    available_traffic: int64 [in bytes]
    account_expires: int [timestamp]

ResellerGetCode(days, useExist = true, autoBuy = true) ->
    status: [success, fail]
    status_code: [200,400,406]
    code_id: int
    reseller_code: string
    balance: float


GetFilesList(parent = '/', limit = 100, offset = 0, sort = [id=>[-1,1], name=>[-1,1], date_created=>[-1,1]], type=>[any,file,folder]) ->
    status: [success, fail]
    status_code: [200]
    files: [
        id: int
        name: string
        is_available: bool
        is_folder: bool
        date_created: string
        size: int @in bytes
    ]
 sort by id=-1 - DESC
 sort by id=1 - ASC


CreateFolder(name, parent ['/' or parent_id], access [public, private, premium], is_public = false) ->
    status: [success, fail]
    status_code: [201,400,406]
    id: int @id new folder


UpdateFile(id, new_name = null, new_parent = null, new_access = null, new_is_public = null) ->
    status: [success, fail]
    status_code: [202,400,406]

UpdateFiles(ids[], new_name = null, new_parent = null, new_access = null, new_is_public = null) ->
    status: [success]
    status_code: [200]
    files: [
        id: string,
        status: [success, error]
        errors: [] @if error
    ]

GetBalance() ->
    status: [success]
    status_code: [200]
    balance: float


GetFilesInfo(ids[]) ->
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
    ]


RemoteUploadAdd(urls[]) ->
    status: [success]
    status_code: [200,400]
    acceptedUrls: []
    rejectedUrls: []


RemoteUploadStatus(ids[])
    status: [success]
    status_code: [200,400]
    uploads: [
        status: int @[1: new, 2: processing, 3: completed, 4:error, 5: accepted]
        progress: int @percents
    ]


FindFile(md5)
    status: [success]
    status_code: [200,400]
    found: bool


CreateFileByHash(hash, name, parent = '/', access = public) ->
    status: [success, error]
    status_code: [201,400,406]
    id: int @if created new file
    errors: [] @if error


GetUploadFormData() ->
    status: [success]
    status_code: [200]
    form_action: string
    file_field: string
    form_data: [
        nodeName: string
        userId: int
        expires: int
        hmac: string
    ]

```

API ERRORS
```
    ERROR_INCORRECT_PARAM_VALUE = 3;

    ERROR_YOU_ARE_NEED_AUTHORIZED = 10;

    ERROR_FILE_NOT_FOUND = 20;
    ERROR_FILE_IS_NOT_AVAILABLE = 21;
    ERROR_FILE_IS_BLOCKED = 22;

    ERROR_CAPTCHA_REQUIRED = 30;
    ERROR_CAPTCHA_INVALID = 31;

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

    //billings
    ERROR_BILLING_WRONG_ACCESS_API_KEY = 80;
    ERROR_BILLING_TRANSACTION_NOT_FOUND = 81;

    Additional download errors:
    DOWNLOAD_COUNT_EXCEEDED = 1;     //'Download count files exceed'
    DOWNLOAD_TRAFFIC_EXCEEDED = 2;   //'Traffic limit exceed'
    DOWNLOAD_FILE_SIZE_EXCEEDED = 3; //"Free user can't download large files. Upgrate to PREMIUM and forget about limits."
    DOWNLOAD_NO_ACCESS = 4;          //'You no can access to this file'
    DOWNLOAD_WAITING = 5;            //'Please wait to download this file'
    DOWNLOAD_FREE_THREAD_COUNT_TO_MANY = 6; //'Free account does not allow to download more than one file at the same time'
    PREMIUM_ONLY = 7;                //'This download available only for premium users',
    PRIVATE_ONLY = 8;                //'This is private file',
```
