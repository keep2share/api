#!/bin/bash

################################################################################
# Script demonstrates how to upload file to keep2share
#
# Dependencies:
#   curl - transfer a URL
#   jq   - Command-line JSON processor (http://stedolan.github.io/jq/download/)
#
# Script was tested on Ubuntu 13.10 (64-bit, kernel 3.11.0-13-generic)
################################################################################

user="your_email"
password="your_password"
url="http://keep2share.cc/api/v1/"
# Temporary file for error curl execution
tempError="outputCurl.txt"

# Check status from server response
# If status not "success" program will terminate
function checkStatus(){
    local json=$1
    local action=$2
    status=$(echo "$json" | jq -r ".status")

    if [ "$status" == "success" ]; then
        echo -e "===> $action is \e[32mOK\e[0m"
    else
        echo -e "===> $action is \e[31mFAILD\e[0m"
        echo "[message] :" $(echo "$json" | jq ".message")
        echo "[code] :" $(echo "$json" | jq ".code")
        echo "Curl execution output:"
        cat "$tempError"
        rm "$tempError"
        exit 1
    fi
}

################################################################################
# Authorization
#

# Run command for authorization
cmd="curl -d '{\"username\":\"$user\",\"password\":\"$password\"}' "$url"login 2>$tempError"
# Get response from server
resLogin=`eval $cmd`

# Look for an authorization status
checkStatus "$resLogin" "Authentacation"

# Get authorization token
auth_token=$(echo "$resLogin" | jq -r ".auth_token")


################################################################################
# Upload file
#

cmd="curl -d '{\"auth_token\":\"$auth_token\"}' "$url"GetUploadFormData 2>$tempError"
resForm=`eval $cmd`

# Look for an form data status
checkStatus "$resForm" "Getting form data"

# File which we are going to upload on server
fileName="exampleAPI.php"

# Url for upload
form_action=$(echo "$resForm" | jq -r ".form_action")
# Required params for uploading file
file_field=$(echo "$resForm" | jq -r ".file_field")
nodeName=$(echo "$resForm" | jq -r ".form_data.nodeName")
userId=$(echo "$resForm" | jq -r ".form_data.userId")
expires=$(echo "$resForm" | jq -r ".form_data.expires")
hmac=$(echo "$resForm" | jq -r ".form_data.hmac")
api_request=$(echo "$resForm" | jq -r ".form_data.api_request")

# Run command for upload file
cmd="curl -F '$file_field=@$fileName' -F 'hmac=$hmac' -F 'expires=$expires' -F 'userId=$userId' -F 'nodeName=$nodeName' -F 'api_request=$api_request' "$form_action" 2>$tempError"
resUpload=`eval $cmd`

# Response server after uploading file
checkStatus "$resUpload" "Uploading file to server"

echo "Response: $resUpload"

# Delete temporary file
rm "$tempError"
