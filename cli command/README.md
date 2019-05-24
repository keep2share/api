# CLI command v.1.3.7

Latest version.

* We added `-n` parameter. You can specify the project and get the appropriate domain for links.
* We improved an uploading files speed.

## USAGE
    $ partnercli upload

## OPTIONS
    -a, --access-token=access-token              (required) access token from your profile
    -c, --path-to-csv=path-to-csv                [default: upload_log.csv] local path to generated result file
    -d, --destination-folder=destination-folder  folder Id on server
    -j, --stdout-json=stdout-json                stdout in json format
    -n, --domain-name=k2s|fb|p2m|tz              [default: k2s] domain name for generated URLs
    -s, --source-folder=source-folder            (required) path to local folder

## ALIASES
    $ partnercli u
    $ partnercli upl

# CLI command v.1.1.5

Fixed some bugs.

# CLI command v.1.1.4

## USAGE
    $ partnercli upload

## OPTIONS
      -a, --access-token=access-token              (required) access token from your profile
      -c, --path-to-csv=path-to-csv                [default: upload_log.csv] local path to generated result file
      -d, --destination-folder=destination-folder  folder Id on server
      -j, --stdout-json=stdout-json                stdout in json format
      -s, --source-folder=source-folder            (required) path to local folder

## ALIASES
    $ partnercli u
    $ partnercli upl

# CLI command v.1.1.3

## Examples
    $ ./partnercli-macos help upload

Upload files on server

### USAGE
    $ partnercli upload

### OPTIONS
    -a, --access-token=access-token              (required) access token from your profile
    -c, --path-to-csv=path-to-csv                [default: upload_log.csv] local path to generated result file
    -d, --destination-folder=destination-folder  folder Id on server
    -j, --stdout-json=stdout-json                stdout in json format
    -s, --source-folder=source-folder            (required) path to local folder

### ALIASES
  $ partnercli u
  $ partnercli upl

# CLI command v.0.0.0

There are things it can do for you:
- Upload a specific file from your computer
- Upload files from specific folder from your computer
- Create the CSV file with operetion's result.

CLI command checks the hash of file before uploading.


## Examples:

    $ ./partnercli-macos help upload

Upload files on server

### USAGE
    $ partnercli upload

### OPTIONS

    -a, --access-token=access-token              (required) access token from your profile
    -c, --path-to-csv=path-to-csv                [default: upload_log.csv] local path to generated result file
    -d, --destination-folder=destination-folder  folder Id on server
    -s, --source-folder=source-folder            (required) path to local folder
