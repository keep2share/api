<?php


class Keep2ShareAPI {

    protected $_ch;
    protected $_auth_token;
    public $baseUrl = 'http://keep2share.cc/api/v1/';
    public $username;
    public $password;

    public function __construct()
    {
        $this->_ch = curl_init();
        curl_setopt($this->_ch, CURLOPT_POST, true);
        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, 2);

        $this->_auth_token = self::getAuthToken();
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
            self::setAuthToken($data['auth_token']);
            $this->_auth_token = $data['auth_token'];
            return true;
        } else {
            self::log('Authentication failed: ' . $data['message'], 'warning');
            return false;
        }
    }

    public function request($action, $params = array(), $allowAuth = true)
    {
        if(!$this->_auth_token) {
            $this->login();
        }
        $params['auth_token'] = $this->_auth_token;
        curl_setopt($this->_ch, CURLOPT_URL, $this->baseUrl.$action);
        curl_setopt($this->_ch, CURLOPT_POSTFIELDS, json_encode($params));
        $response = curl_exec($this->_ch);
        $data = json_decode($response, true);
        if($data['status'] == 'error' && isset($data['code']) && $data['code'] == 403) {
            if($allowAuth) {
                $this->login();
                return $this->request($action, $params, false);
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

    public function getFilesList($parent = '/', $limit = 100, $offset = 0)
    {
        return $this->request('getFilesList', array(
            'parent'=>$parent,
            'limit'=>$limit,
            'offset'=>$offset,
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

    public function getFilesInfo(array $id)
    {
        return $this->request('getFilesInfo', array(
            'ids'=>json_encode($id),
        ));
    }


    public function remoteUploadAdd(array $urls)
    {
        return $this->request('remoteUploadAdd', array(
            'urls'=>json_encode($urls),
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
            'ids'=>json_encode($ids),
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


    public static function log($msg, $level)
    {
        echo $msg."<br>";
    }

    public static function setAuthToken($key)
    {
        $cache = new Memcache();
        $cache->addserver('127.0.0.1');
        $cache->set('Keep2ShareApiAuthToken', $key, 0, 1800);
    }

    public static function getAuthToken()
    {
        $cache = new Memcache();
        $cache->addserver('127.0.0.1');
        return $cache->get('Keep2ShareApiAuthToken');
    }

}