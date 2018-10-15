<?php

/**
 * Class Keep2ShareApiV2
 */
class Keep2ShareApiV2
{
    protected $baseUrl = 'https://api.k2s.cc/v1';
    protected $clientId = 'k2s_api_sdk';
    protected $clientSecret = 'FWCc1snnCuwizNDwn72PTAST';
    protected $accessToken;
    protected $refreshToken;

    const LEVEL_ERROR = 'Error';
    const LEVEL_WARNING = 'Warning';
    const LEVEL_INFO = 'Info';

    const CAPTCHA_CLASSIC = 'classic';
    const CAPTCHA_RECAPTCHA = 'recaptcha';

    /**
     * Keep2ShareApiV2 constructor.
     * @param array $params
     * @throws Exception
     */
    public function __construct($params = [])
    {
        if (!is_array($params)) {
            throw new Exception('Constructor params must be an array');
        }

        if ($params['baseUrl']) {
            $this->setBaseUrl($params['baseUrl']);
        }

        if ($params['clientId']) {
            $this->setClientId($params['clientId']);
        }

        if ($params['clientSecret']) {
            $this->setClientSecret($params['clientSecret']);
        }

        $this->authenticate();
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
            $this->log(__FUNCTION__, $this->getFirstResponseError($response), self::LEVEL_WARNING);
        }

        return $response->body;
    }

    /**
     * Getting user's token
     * @param string $username
     * @param string $password
     * @param string|null $captchaType (classic, recaptcha)
     * @param string|null $captchaValue
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

        if ($this->getFirstResponseError($response)) {
            $this->log(__FUNCTION__, $this->getFirstResponseError($response), self::LEVEL_WARNING);
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
    protected function getClientTokensStorage()
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'client_k2s.api.key';
    }

    /**
     * Returns access_token and refresh_token which are stored in file
     * @return bool|object
     */
    protected function loadClientTokens()
    {
        $content = is_file($this->getClientTokensStorage())
            ? file_get_contents($this->getClientTokensStorage())
            : false;

        if (!$content) {
            return false;
        }

        return json_decode($content);
    }

    /**
     * Saving access_token and refresh_token to file
     * @param string $accessToken
     * @param string $refreshToken
     * @return bool
     */
    protected function saveClientTokens($accessToken, $refreshToken)
    {
        return file_put_contents($this->getClientTokensStorage(), json_encode([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ]));
    }

    /**
     * Authenticate on API server - getting client's or user's access token
     * and saving it in current instance
     * @param null $username
     * @param null $password
     * @param null $captchaType
     * @param null $captchaValue
     * @return bool
     */
    public function authenticate($username = null, $password = null, $captchaType = null, $captchaValue = null)
    {
        if ($username && $password) {
            // Getting user's token
            $token = $this->getUserToken($username, $password, $captchaType, $captchaValue);
            $this->setAccessToken($token->access_token);
            $this->setRefreshToken($token->refresh_token);
            return true;
        }

        // Getting client's token (for guests)
        $storedTokens = $this->loadClientTokens();
        if ($storedTokens && $this->isValidAccessToken($storedTokens->access_token)) {
            // using previously saved token
            $this->setAccessToken($storedTokens->access_token);
            $this->setRefreshToken($storedTokens->refresh_token);
            return true;
        }

        // getting new token
        $token = $this->getClientToken();
        $this->saveClientTokens($token->access_token, $token->refresh_token);
        return true;
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
     * @return object
     */
    public function getUserInfo()
    {
        $response = $this->request('GET', 'users/me');
        if ($this->getFirstResponseError($response)) {
            $this->log(__FUNCTION__, $this->getFirstResponseError($response));
        }

        return $response->body;
    }

    /**
     * Getting user's statistic
     * @return object
     */
    public function getUserStatistic()
    {
        $response = $this->request('GET', 'users/me/statistic');
        if ($this->getFirstResponseError($response)) {
            $this->log(__FUNCTION__, $this->getFirstResponseError($response));
        }

        return $response->body;
    }
}
