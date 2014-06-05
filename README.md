API base url: http://keep2share.cc/api/v1


All request parameters must be encoded to JSON and sent using the POST method
All methods return operation status (success, fail) and additional data in JSON

HTTP CODES
```
2xx - Request successful
400 - Bad request (not correct request params)
403 - Authorization required
406 - Not acceptable
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
