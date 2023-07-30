<?php

namespace Ext\Centro\System\Security;

/*****
 * TODO:
 *  [ ] Refresh Token to new Token + new Refresh Token
 *  [ ] 
 */

use Ext\Centro\System\Security\JWT;

class JWTAuth {

    protected $redis = NULL;
    protected $CI = NULL;

    public function __construct() {
        //$this->CI =& Controller::$instance;

        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379);

        //$this->CI->load_library('JWT');
    }

    // public function __destruct() {
    //     if ($this->redis !== NULL) {
    //         $this->redis->disconnect();
    //     }
    // }
#region ' RSA Handling '
    private function generateRSA() {
        // Create RSAKey
        $config = [
            'digest_alg' => 'sha256',
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $privKey = NULL;

        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $privKey);

        // Extract the public key from $res to $pubKey
        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey["key"];

        $keys = ['priv' => $privKey, 'pub' => $pubKey];

        return $keys;
    }
#endregion

    // generateApiAccessToken()   -- creates both AuthToken and RefreshToken
    public function generateApiAccessToken($app_id, $seconds_to_live = 0) {
        

        // default to live for 1 week
        $seconds_to_live = (int)$seconds_to_live;
        if ($seconds_to_live < 1) {
        	$seconds_to_live = 60*60*24*7; // 7 days
        }

        // Refresh is at least 28 days
        $seconds_to_live_refresh = max(60*60*24*7*4, $seconds_to_live * 4);

        $payload = [
            'app_id' => $app_id,
            'expr' => time() + $seconds_to_live,
        ];

        $refresh_payload = [
            'app_id' => $app_id,
            'expr' => time() + $seconds_to_live_refresh
        ];

        $keys = $this->generateRSA();

        // Create the JWT
        $jwt = JWT::encode($payload, $keys['priv'], 'RS256');
        $rjwt = JWT::encode($refresh_payload, $keys['priv'], 'RS256');

        
        // Store RSAKey in Redis using Hash as Key
        $redis_pub_key = 'jwt:pub:' . sha1($jwt);
        $redis_priv_key = 'jwt:priv:' . sha1($jwt);
        $this->redis->set($redis_pub_key, $keys['pub'], $seconds_to_live);
        $this->redis->set($redis_priv_key, $keys['priv'], $seconds_to_live);


        $redis_pub_key_refresh = 'jwt:pub:' . sha1($rjwt);
        $redis_priv_key_refresh = 'jwt:priv:' . sha1($rjwt);
        $this->redis->set($redis_pub_key_refresh, $keys['pub'], $seconds_to_live_refresh);
        $this->redis->set($redis_priv_key_refresh, $keys['priv'], $seconds_to_live_refresh);


        return ['token' => $jwt, 'refresh' => $rjwt];

    }

    // decodeApiAccessToken()     -- decodes both AuthToken and RefreshToken
    public function decodeApiAccessToken($jwt) {
        // Get the public key from redis

        $redis_key = sha1($jwt);
        $pub_key = $this->redis->get('jwt:pub:' . $redis_key);

        // Check if keys was found in redis
        if ($pub_key === FALSE) {
            return FALSE;
        }

        $payload_obj = JWT::decode($jwt, $pub_key, ['RS256']);
        $payload = json_decode(json_encode($payload_obj), TRUE);

        return $payload;

    }

    // generateUserAuthToken()  -- creates both AuthToken and RefreshToken
    public function generateUserAuthToken($user_id, $app_id, $account_id, $permissions=[], $seconds_to_live=0) {
        // default to live for 1 week
        $seconds_to_live = (int)$seconds_to_live;
        if ($seconds_to_live < 1) {
        	$seconds_to_live = 60*60*24*7; // 7 days
            
        }
        
        // Get the refresh TTL in seconds (min 28 Days)
        $refresh_seconds_to_live = max($seconds_to_live * 4, 60*60*24*28); // Make sure we refresh at at least 28 days
        

        $payload = [
            'user_id' => $user_id,
            'app_id' => $app_id,        // The 3rdParty App this token was generated for (In Cluster if AAT:app_id and UAT:app_id don't match we have a problem)
            'account_id' => $account_id,
            'permissions' => $permissions,
            'expr' => time() + $seconds_to_live,
        ];

        $refresh_payload = [
            'user_id' => $user_id,
            'app_id' => $app_id,
            'expr' => time() + $refresh_seconds_to_live,
        ];

        $keys = $this->generateRSA();
        
        //var_dump($seconds_to_live);
        //var_dump($refresh_seconds_to_live);
        //var_dump($keys);

        // Create the JWT
        $jwt = JWT::encode($payload, $keys['priv'], 'RS256');
        $rjwt = JWT::encode($refresh_payload, $keys['priv'], 'RS256');

        //var_dump($jwt);
        //var_dump($rjwt);
        //return FALSE;


        // Store RSA Key in Redis using Hash as Key
        $redis_pub_key = 'jwt:pub:' . sha1($jwt);
        $redis_priv_key = 'jwt:priv:' . sha1($jwt); //TODO: This key should not need to be stored....
        $this->redis->set($redis_pub_key, $keys['pub'], $seconds_to_live);
        $this->redis->set($redis_priv_key, $keys['priv'], $seconds_to_live);

        $redis_pub_key_refresh = 'jwt:pub:' . sha1($rjwt);
        $redis_priv_key_refresh = 'jwt:priv:' . sha1($rjwt);
        $this->redis->set($redis_pub_key_refresh, $keys['pub'], $refresh_seconds_to_live);
        $this->redis->set($redis_priv_key_refresh, $keys['priv'], $refresh_seconds_to_live);


        return ['token' => $jwt, 'refresh' => $rjwt];

    }

    // decodeUserAuthToken()    -- decodes both AuthToken and RefreshToken
    public function decodeUserAuthToken($jwt) {
        // Get the public key from redis

        $redis_key = sha1($jwt);

        //print($redis_key);

        $pub_key = $this->redis->get('jwt:pub:' . $redis_key);

        // Check if keys was found in redis
        if ($pub_key == FALSE) {
            return FALSE;
        }

        $payload_obj = JWT::decode($jwt, $pub_key, ['RS256']);
        $payload = json_decode(json_encode($payload_obj), TRUE);

        return $payload;

    }

    //public function generateJWT($app_id, $app_user_id, $data=[], $seconds_to_live=NULL) { // WE CENTRALIZED USER AUTH. WE NO LONGER NEED $app_id
    // generateLoginJWT, is used to allow a user who logged into one app, to subsequenty log into many others
    // without proving identity again.
	public function generateLoginJWT($user_id, $data=[], $seconds_to_live=0) {
        $this->CI->load_library('JWT');
        
        // default to live for 1 week
        $seconds_to_live = (int)$seconds_to_live;
        if ($seconds_to_live < 1) {
        	$seconds_to_live = 60*60*24*7; // 7 days
        }

        $payload = [
            //'app_id' => $app_id,
            'user_id' => $user_id, // The UUID of the user
            'data' => $data,       // the data to store (must be json serializable)
            'expr' => time() + $seconds_to_live,
        ];


        // Create a redis key, for the pubKey
        $keys = $this->generateRSA();


        // Use RSA to create JWT
        $jwt = JWT::encode($payload, $keys['priv'], 'RS256');
        
        //print($jwt);
        //$sanity_check = JWT::decode($jwt, $keys['pub'], ['RS256']);
        //print('<br><b>' . 'Sanity Check' . "</b><br>\n");
        //print_r($sanity_check);

        
        // Get Hash of JWT
        
        // Store RSAKey in Redis using Hash as Key
        $redis_key = 'jwt:pub:' . sha1($jwt);
        $this->redis->set($redis_key, $keys['pub'], $seconds_to_live);

        return $jwt;
    }

    public function decodeLoginJWT($jwt) {
        $this->CI->load_library('JWT');

        $redis_key = 'jwt:pub:' . sha1($jwt);
        //print('<br><b>' . $redis_key . '</b><br>');
        $pub_key = $this->redis->get($redis_key);

        //print('<br><b>' . $jwt . '</b><br>');
        //print($pub_key);

        // Check if keys was found in redis
        if ($pub_key === FALSE) {
            return FALSE;
        }

        $payload_obj = JWT::decode($jwt, $pub_key, ['RS256']);
        $payload = json_decode(json_encode($payload_obj), TRUE);

        return $payload;
    }

    public function randomAuth() {

        return 'dhjakfhfjals';

    }

}