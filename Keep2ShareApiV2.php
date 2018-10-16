<?php

/**
 * Class Keep2ShareApiV2
 */
class Keep2ShareApiV2
{
    // protected $baseUrl = 'https://api.k2s.cc/v1';
    protected $baseUrl = 'http://localhost:3001/v1';
    protected $clientId = 'k2s_api_sdk';
    protected $clientSecret = 'FWCc1snnCuwizNDwn72PTAST';
    protected $accessToken;
    protected $refreshToken;

    const LEVEL_ERROR = 'Error';
    const LEVEL_WARNING = 'Warning';
    const LEVEL_INFO = 'Info';

    const CAPTCHA_CLASSIC = 'classic';
    const CAPTCHA_RECAPTCHA = 'recaptcha';

    const ERROR_INCORRECT_PARAMS = 2;
    const ERROR_INCORRECT_PARAM_VALUE = 3;
    const ERROR_INVALID_REQUEST = 4;

    const ERROR_YOU_ARE_NEED_AUTHORIZED = 10;
    const ERROR_AUTHORIZATION_EXPIRED = 11;

    const ERROR_FILE_NOT_FOUND = 20;
    const ERROR_FILE_IS_NOT_AVAILABLE = 21;
    const ERROR_FILE_IS_BLOCKED = 22;
    const ERROR_DOWNLOAD_FOLDER_NOT_SUPPORTED = 23;

    const ERROR_CAPTCHA_REQUIRED = 30;
    const ERROR_CAPTCHA_INVALID = 31;

    const ERROR_WRONG_FREE_DOWNLOAD_KEY = 40;
    const ERROR_NEED_WAIT_TO_FREE_DOWNLOAD = 41;
    const ERROR_DOWNLOAD_NOT_AVAILABLE = 42;
    const ERROR_DOWNLOAD_PREMIUM_ONLY = 43;

    const ERROR_NO_AVAILABLE_RESELLER_CODES = 50;
    const ERROR_BUY_RESELLER_CODES = 51;

    const ERROR_CREATE_FOLDER = 60;
    const ERROR_UPDATE_FILE = 61;
    const ERROR_COPY_FILE = 62;
    const ERROR_NO_AVAILABLE_NODES = 63;
    const ERROR_DISK_SPACE_EXCEED = 64;

    const ERROR_INCORRECT_USERNAME_OR_PASSWORD = 70;
    const ERROR_LOGIN_ATTEMPTS_EXCEEDED = 71;
    const ERROR_ACCOUNT_BANNED = 72;
    const ERROR_NO_ALLOW_ACCESS_FROM_NETWORK = 73;
    const ERROR_UNKNOWN_LOGIN_ERROR = 74;
    const ERROR_ILLEGAL_SESSION_IP = 75;
    const ERROR_ACCOUNT_STOLEN = 76;
    const ERROR_NETWORK_BANNED = 77;

    const FILE_ACCESS_PUBLIC = 'public';
    const FILE_ACCESS_PRIVATE = 'private';
    const FILE_ACCESS_PREMIUM = 'premium';

    const REMOTE_UPLOAD_STATUS_NEW = 1;
    const REMOTE_UPLOAD_STATUS_PROCESSING = 2;
    const REMOTE_UPLOAD_STATUS_COMPLETED = 3;
    const REMOTE_UPLOAD_STATUS_ERROR = 4;
    const REMOTE_UPLOAD_STATUS_ACCEPTED = 5;

    const CHUNK_HASH_SIZE = 5242880;

    /**
     * Keep2ShareApiV2 constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        if ($params['baseUrl']) {
            $this->setBaseUrl($params['baseUrl']);
        }

        if ($params['clientId']) {
            $this->setClientId($params['clientId']);
        }

        if ($params['clientSecret']) {
            $this->setClientSecret($params['clientSecret']);
        }
    }

    public function setBaseUrl($url)
    {
        $this->baseUrl = $url;
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    public function getClientId()
    {
        return $this->clientId;
    }

    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    public function log($source, $message, $level = self::LEVEL_INFO)
    {
        $time = date('Y-m-d h:i:s');
        echo "$time [{$level}]: {$source} - {$message}" . PHP_EOL;
    }

    /**
     * Process API request
     * @param string $method POST, PUT, GET, DELETE
     * @param string $endpoint API endpoint name
     * @param array $body request body
     * @param bool $withCredentials - pass authorization header if true
     * @throws Exception
     * @return stdClass
     */
    protected function request($method, $endpoint, $body = [], $withCredentials = true)
    {
        $url = $this->getBaseUrl() . '/' . $endpoint;
        $ch = curl_init();

        $headers = ['Content-Type: application/x-www-form-urlencoded'];
        if ($withCredentials && $this->getAccessToken()) {
            if ($this->isTokenExpired()) {
                $this->refreshAccessToken();
            }
            $headers[] = "Authorization: Bearer {$this->getAccessToken()}";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        switch ($method) {
            case 'POST': {
                curl_setopt($ch, CURLOPT_POST, true);
                if ($body) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body));
                }
                break;
            }
            case 'PUT': {
                curl_setopt($ch, CURLOPT_PUT, true);
                break;
            }
            default: {
                if ($body) {
                    $url = sprintf('%s?%s', $url, http_build_query($body));
                }
            }
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $result = curl_exec($ch);
        if (!$result && curl_error($ch)) {
            $this->log(__FUNCTION__, curl_error($ch), self::LEVEL_ERROR);
        }

        $response = new stdClass();
        $response->status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response->body = json_decode($result);
        curl_close($ch);

        return $response;
    }

    /**
     * Return true if access token will be expired soon
     * @return bool
     */
    protected function isTokenExpired()
    {
        $accessToken = $this->getAccessToken();
        $payload = $this->getJwtPayload($accessToken);
        return (int) $payload->exp < strtotime('+1 day');
    }

    /**
     * Getting new pair of accessToken and refreshToken by current refreshToken
     * @throws Exception
     * @return bool
     */
    protected function refreshAccessToken()
    {
        $response = $this->request('POST', 'auth/token', [
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->getRefreshToken(),
        ], false);

        $error = reset($this->getResponseErrors($response));
        if ($error) {
            $this->log(__FUNCTION__, $error, self::LEVEL_WARNING);
            return false;
        }

        $this->setAccessToken($response->body->access_token);
        $this->setRefreshToken($response->body->refresh_token);
        $this->log(__FUNCTION__, 'AccessToken is refreshed');
        $payload = $this->getJwtPayload($response->body->access_token);
        if ($payload->type === 'client') {
            $tokensStorage = $this->getTokensStorage('client');
            $this->saveTokens($tokensStorage, $response->body->access_token, $response->body->refresh_token);
            $this->log(__FUNCTION__, 'Client tokens saved at ' . $tokensStorage);
        } else {
            $userInfo = $this->getUserInfo();
            $tokensStorage = $this->getTokensStorage($userInfo->email);
            $this->saveTokens($tokensStorage, $response->body->access_token, $response->body->refresh_token);
            $this->log(__FUNCTION__, 'User tokens saved at ' . $tokensStorage);
        }
        return true;
    }

    /**
     * Returns JWT token payload (without checking signature)
     * @param string $jwt
     * @return object
     */
    protected function getJwtPayload($jwt)
    {
        $chunks = explode('.', $jwt);
        return json_decode(base64_decode($chunks[1]));
    }

    /**
     * Getting list of errors from response
     * @param object $response
     * @return array
     */
    protected function getResponseErrors($response)
    {
        if ($response->status === 200) {
            return [];
        }

        if ($response->body->error) {
            return ['error' => $response->body->error];
        }

        if ($response->body->errors) {
            $errors = [];
            foreach ($response->body->errors as $error) {
                if (is_array($error)) {
                    foreach ($error as $message) {
                        $errors[] = $message->message;
                    }
                } else {
                    $errors[] = $error;
                }
            }

            return $errors;
        }

        return ['error' => "Response status: {$response->status}"];
    }

    /**
     * Getting the first error from response
     * @param object $response
     * @return string
     */
    protected function getFirstResponseError($response)
    {
        return reset($this->getResponseErrors($response));
    }

    /**
     * Getting client's token
     * @throws Exception
     * @return object
     */
    public function getClientToken()
    {
        $response = $this->request('POST', 'auth/token', [
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'grant_type' => 'client_credentials',
        ], false);

        if ($this->getFirstResponseError($response)) {
            throw new Exception($this->getFirstResponseError($response), self::ERROR_UNKNOWN_LOGIN_ERROR);
        }

        return $response->body;
    }

    /**
     * Getting user's token
     * @param string $username
     * @param string $password
     * @param string|null $captchaType (classic, recaptcha)
     * @param string|null $captchaValue
     * @throws Exception
     * @return object
     */
    public function getUserToken($username, $password, $captchaType = null, $captchaValue = null)
    {
        $response = $this->request('POST', 'auth/token', [
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'username' => $username,
            'password' => $password,
            'grant_type' => 'password',
            'captchaType' => $captchaType,
            'captchaValue' => $captchaValue,
        ]);

        $firstError = $this->getFirstResponseError($response);
        if ($firstError) {
            if ($firstError === 'captcha_required') {
                throw new Exception('Please verify your request via captcha challenge', self::ERROR_CAPTCHA_REQUIRED);
            } else {
                throw new Exception($firstError, self::ERROR_UNKNOWN_LOGIN_ERROR);
            }
        }

        return $response->body;
    }

    /**
     * Returns true if access token is valid and can be used for API calls
     * @param string $accessToken
     * @return bool
     */
    public function isValidAccessToken($accessToken)
    {
        $response = $this->request('GET', "auth/token?accessToken={$accessToken}", [], false);
        return $response->status === 200;
    }

    /**
     * Returns filename for storing client's token
     * @return string
     */
    protected function getTokensStorage($key)
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . $key . '_k2s.api.key';
    }

    /**
     * Returns access_token and refresh_token which are stored in file
     * @param string $fileName
     * @return bool|object
     */
    protected function loadTokens($fileName)
    {
        $content = is_file($fileName) ? file_get_contents($fileName) : null;
        if (!$content) {
            return false;
        }
        return json_decode($content);
    }

    /**
     * Saving access_token and refresh_token to file
     * @param string $fileName
     * @param string $accessToken
     * @param string $refreshToken
     * @return bool
     */
    protected function saveTokens($fileName, $accessToken, $refreshToken)
    {
        return file_put_contents($fileName, json_encode([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ]));
    }

    /**
     * Authenticate by user's password
     * @param null $username
     * @param null $password
     * @param null $captchaType
     * @param null $captchaValue
     * @throws Exception
     * @return bool
     */
    public function authenticateByPassword($username, $password, $captchaType = null, $captchaValue = null)
    {
        $tokensStorage = $this->getTokensStorage($username);
        $storedTokens = $this->loadTokens($tokensStorage);
        if ($storedTokens && $this->isValidAccessToken($storedTokens->access_token)) {
            // using previously saved token
            $this->setAccessToken($storedTokens->access_token);
            $this->setRefreshToken($storedTokens->refresh_token);
            $this->log(__FUNCTION__, 'User tokens loaded from ' . $tokensStorage);
            return true;
        }

        // Getting user's token
        $token = $this->getUserToken($username, $password, $captchaType, $captchaValue);
        if ($token->access_token && $token->refresh_token) {
            $this->setAccessToken($token->access_token);
            $this->setRefreshToken($token->refresh_token);
            $this->saveTokens($tokensStorage, $token->access_token, $token->refresh_token);
            $this->log(__FUNCTION__, 'User tokens saved at ' . $tokensStorage);
            return true;
        }

        return false;
    }

    /**
     * Authenticate by client credentials
     * @return bool
     */
    public function authenticateByClient()
    {
        // Getting client's token (guests tokens)
        $tokensStorage = $this->getTokensStorage('client');
        $storedTokens = $this->loadTokens($tokensStorage);
        if ($storedTokens && $this->isValidAccessToken($storedTokens->access_token)) {
            // using previously saved token
            $this->setAccessToken($storedTokens->access_token);
            $this->setRefreshToken($storedTokens->refresh_token);
            $this->log(__FUNCTION__, 'Client tokens loaded from ' . $tokensStorage);
            return true;
        }

        // getting new token
        $token = $this->getClientToken();
        if ($token->access_token && $token->refresh_token) {
            $this->setAccessToken($token->access_token);
            $this->setRefreshToken($token->refresh_token);
            $this->saveTokens($tokensStorage, $token->access_token, $token->refresh_token);
            $this->log(__FUNCTION__, 'Client tokens saved at ' . $tokensStorage);
            return true;
        }

        return false;
    }

    /**
     * Getting captcha image URL
     * @return object
     */
    public function getCaptcha()
    {
        $response = new stdClass();
        $response->captchaUrl = $this->getBaseUrl() . '/users/me/captcha?accessToken=' . $this->getAccessToken();
        return $response;
    }

    /**
     * Getting user's info
     * @throws Exception
     * @return object
     */
    public function getUserInfo()
    {
        $response = $this->request('GET', 'users/me');
        if ($this->getFirstResponseError($response)) {
            throw new Exception($this->getFirstResponseError($response));
        }

        return $response->body;
    }

    /**
     * Getting user's statistic
     * @throws Exception
     * @return object
     */
    public function getUserStatistic()
    {
        $response = $this->request('GET', 'users/me/statistic');
        if ($this->getFirstResponseError($response)) {
            throw new Exception($this->getFirstResponseError($response));
        }

        return $response->body;
    }
}
