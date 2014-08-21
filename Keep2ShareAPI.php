<?php


class Keep2ShareAPI {

    protected $_ch;
    protected $_auth_token;
    protected $_allowAuth = true;
    public $baseUrl = 'http://keep2share.cc/api/v1/';
    public $username;
    public $password;

    public function __construct()
    {
        $this->_ch = curl_init();
        curl_setopt($this->_ch, CURLOPT_POST, true);
        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, 2);

        $this->_auth_token = $this->getAuthToken();
    }

    public function login()
    {
        curl_setopt($this->_ch, CURLOPT_URL, $this->baseUrl.'login');
        curl_setopt($this->_ch, CURLOPT_POSTFIELDS, json_encode(array(
            'username'=>$this->username,
            'password'=>$this->password,
        )));

        $data = json_decode(curl_exec($this->_ch), true);

        if(!$data || !isset($data['status'])) {
            self::log('Authentication failed', 'warning');
            return false;
        }

        if($data['status'] == 'success') {
            $this->setAuthToken($data['auth_token']);
            $this->_auth_token = $data['auth_token'];
            return true;
        } else {
            self::log('Authentication failed: ' . $data['message'], 'warning');
            return false;
        }
    }

    public function request($action, $params = array())
    {
        if($this->username && !$this->_auth_token) {
            if($this->_allowAuth) {
                $this->login();
                $this->_allowAuth = false;
                if(!$this->_auth_token) {
                    return false;
                }
            } else
                return false;
        }

        $params['auth_token'] = $this->_auth_token;
        curl_setopt($this->_ch, CURLOPT_URL, $this->baseUrl.$action);
        curl_setopt($this->_ch, CURLOPT_POSTFIELDS, json_encode($params));
        $response = curl_exec($this->_ch);
        $data = json_decode($response, true);
        if($data['status'] == 'error' && isset($data['code']) && $data['code'] == 403) {
            if($this->_allowAuth) {
                $this->login();
                $this->_allowAuth = false;
                return $this->request($action, $params);
            } else {
                return false;
            }
        }
        return $data;
    }


    public function getCode($days, $autoBuy = true, $useExist = true)
    {
        $data = $this->request('resellerGetCode', array(
            'days'=>$days,
            'autoBuy'=>$autoBuy,
            'useExist'=>$useExist,
        ));
        if(!isset($data['status']) || $data['status'] != 'success') {
            $err = 'Error get code';
            if(isset($data['message'])) {
                $err .= ' :' . $data['message'];
            }
            self::log($err, 'warning');
            return false;
        } else {
            return $data;
        }
    }

    public function getFilesList($parent = '/', $limit = 100, $offset = 0, array $sort = [], $type = 'any')
    {
        return $this->request('getFilesList', array(
            'parent'=>$parent,
            'limit'=>$limit,
            'offset'=>$offset,
            'sort'=>$sort,
            'type'=>$type,
        ));
    }

    const FILE_ACCESS_PUBLIC = 'public';
    const FILE_ACCESS_PRIVATE = 'private';
    const FILE_ACCESS_PREMIUM = 'premium';

    public function createFolder($name, $parent = '/', $access = Keep2ShareAPI::FILE_ACCESS_PUBLIC, $is_public = false)
    {
        return $this->request('createFolder', array(
            'name'=>$name,
            'parent'=>$parent,
            'access'=>$access,
            'is_public'=>$is_public,
        ));
    }

    public function updateFiles($ids = [], $new_name = null, $new_parent = null, $new_access = null, $new_is_public = null)
    {
        return $this->request('updateFiles', array(
            'ids'=>$ids,
            'new_name'=>$new_name,
            'new_parent'=>$new_parent,
            'new_access'=>$new_access,
            'new_is_public'=>$new_is_public,
        ));
    }


    public function updateFile($id, $new_name = null, $new_parent = null, $new_access = null, $new_is_public = null)
    {
        return $this->request('updateFile', array(
            'id'=>$id,
            'new_name'=>$new_name,
            'new_parent'=>$new_parent,
            'new_access'=>$new_access,
            'new_is_public'=>$new_is_public,
        ));
    }

    public function getBalance()
    {
        return $this->request('getBalance');
    }

    public function getFilesInfo(array $ids)
    {
        return $this->request('getFilesInfo', array(
            'ids'=>$ids,
        ));
    }


    public function remoteUploadAdd(array $urls)
    {
        return $this->request('remoteUploadAdd', array(
            'urls'=>$urls,
        ));
    }

    const REMOTE_UPLOAD_STATUS_NEW = 1;
    const REMOTE_UPLOAD_STATUS_PROCESSING = 2;
    const REMOTE_UPLOAD_STATUS_COMPLETED = 3;
    const REMOTE_UPLOAD_STATUS_ERROR = 4;
    const REMOTE_UPLOAD_STATUS_ACCEPTED = 5;

    public function remoteUploadStatus(array $ids)
    {
        return $this->request('remoteUploadStatus', array(
            'ids'=>$ids,
        ));
    }

    public function findFile($md5)
    {
        return $this->request('findFile', array(
            'md5'=>$md5,
        ));
    }

    public function createFileByHash($md5, $name, $parent = '/', $access = Keep2ShareAPI::FILE_ACCESS_PUBLIC)
    {
        return $this->request('createFileByHash', array(
            'hash'=>$md5,
            'name'=>$name,
            'parent'=>$parent,
            'access'=>$access,
        ));
    }

    public function getUploadFormData()
    {
        return $this->request('getUploadFormData');
    }

    public function test()
    {
        $response = $this->request('test');
        return $response;
    }

    /**
     * @param $file
     * @param null $parent_id ID of existing folder
     * @param null $parent_name Name of existing destination folder (has lower priority than parent_id)
     * @return bool|mixed
     * @throws Exception
     *
     * You can use parent_id OR parent_name for specify file folder
     */
    public function uploadFile($file, $parent_id = null, $parent_name = null)
    {
        if(!is_file($file))
            throw new Exception("File '{$file}' is not found");

        $data = $this->getUploadFormData();
        if($data['status'] == 'success') {
            $curl = curl_init();

            $postFields = $data['form_data'];
            $postFields['parent_id'] = $parent_id;
            $postFields['parent_name'] = $parent_name;
            $postFields[$data['file_field']] = '@'.$file;

            curl_setopt_array($curl, array(
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL => $data['form_action'],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS =>$postFields,
            ));

            return json_decode(curl_exec($curl));
        } else {
            self::log('Error uploading file : ' . print_r($data, true), 'error');
            return false;
        }
    }

    public function getAccountInfo()
    {
        return $this->request('accountInfo');
    }

    public function requestCaptcha()
    {
        return $this->request('requestCaptcha');
    }

    public function getUrl($id, $free_download_key = null, $captcha_challenge = null, $captcha_response = null)
    {
        return $this->request('getUrl', [
            'file_id'=>$id,
            'free_download_key'=>$free_download_key,
            'captcha_challenge'=>$captcha_challenge,
            'captcha_response'=>$captcha_response,
        ]);
    }


    public static function log($msg, $level)
    {
        echo $msg."<br>";
    }

    public function setAuthToken($key)
    {
//        $cache = new Memcache();
//        $cache->addserver('127.0.0.1');
//        $cache->set('Keep2ShareApiAuthToken', $key, 0, 1800);

        $temp_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5($this->username) . '_k2s.api.key';
        file_put_contents($temp_file, $key);
    }

    public function getAuthToken()
    {
//        $cache = new Memcache();
//        $cache->addserver('127.0.0.1');
//        return $cache->get('Keep2ShareApiAuthToken');

        $temp_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5($this->username) . '_k2s.api.key';
        return is_file($temp_file)? file_get_contents($temp_file) : false;

    }

}
