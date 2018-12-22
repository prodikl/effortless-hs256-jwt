<?php
/**
 * Created by PhpStorm.
 * User: bchesney
 * Date: 11/7/18
 * Time: 3:33 PM
 */

namespace bradchesney79;

class Ehjwt
{
    /*
    iss: issuer, the website that issued the token
    sub: subject, the id of the entity being granted the token 
        (int has an unsigned, numeric limit of 4294967295)
        (bigint has an unsigned, numeric limit of 18446744073709551615)
        (unix epoch as of "now" 1544897945)
    aud: audience, the users of the token-- generally a url or string
    exp: expires, the UTC UNIX epoch time stamp of when the token is no longer valid
    nbf: not before, the UTC UNIX epoch time stamp of when the token becomes valid
    iat: issued at, the UTC UNIX epoch time stamp of when the token was issued
    jti: JSON web token ID, a unique identifier for the JWT that facilitates revocation 
    */

    /**
     * Issuer
     *
     * @var string
     */
    private $iss = null;
    /**
     * Subject
     *
     * @var string
     */
    private $sub = null;
    /**
     * Audience
     *
     * @var string
     */
    private $aud = null;
    /**
     * Expiration Time
     *
     * @var string
     */
    private $exp = null;
    /**
     * Not Before
     *
     * @var string
     */
    private $nbf = null;
    /**
     * Issued At
     *
     * @var string
     */
    private $iat = null;
    /**
     * JWT ID
     *
     * @var string
     */
    private $jti = null;

    /**
     * @var array
     */
    private $customClaims = null;

    /**
     * @var string
     */
    private $secretKey = null;

    /**
     * @var string
     */
    private $token = null;

    /**
     * The config file path.
     *
     * @var string
     */
    protected $file;

    /**
     * The config data.
     *
     * @var array
     */
    protected $config = [];

    public function __construct(string $secret = null, string $file = __DIR__.'/../config/ehjwt-conf.php') {

        $this->config = require $this->file;

        if (!is_null($secret)) {
            $this->secretKey = $secret;
        }
        else {
            $this->secretKey = $this->config
        }
    }

    public function Ehjwt(string $secret = null) {
        $this->__construct($secret);
    }



    /**
     * Load the configuration file.
     *
     * @return void
     */
    protected function load()
    {
        $this->config = require $this->file;
    }

    public function createToken() {
        // create header
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];
        // create body

        $standardClaims  = [
            'aud' => $this->aud,
            'exp' => $this->exp,
            'iat' => $this->iat,
            'iss' => $this->iss,
            'jti' => $this->jti,
            'nbf' => $this->nbf,
            'sub' => $this->sub
        ];

        ksort($this->customClaims);

        foreach ($this->customClaims as $key => $value) {
            if (null !== $value) {
                $tokenClaims[$key] = $value;
            }
        };

        foreach ($standardClaims as $key => $value) {
            if (null !== $value) {
                $tokenClaims[$key] = $value;
            }
        }

        // convert from arrays to JSON objects

        $jsonHeader = json_encode($header,JSON_FORCE_OBJECT);

        $jsonClaims = json_encode($tokenClaims,JSON_FORCE_OBJECT);

        // encode the header and claims to Base64Url String
        $base64UrlHeader = $this->base64UrlEncode($jsonHeader);

        $base64UrlClaims = $this->base64UrlEncode($jsonClaims);

        // create signature

        $jsonSignature = $this->makeHmacHash($base64UrlHeader, $base64UrlClaims);

        // Encode Signature to Base64Url String
        $base64UrlSignature = $this->base64UrlEncode($jsonSignature);

        $tokenParts = array($base64UrlHeader, $base64UrlClaims, $base64UrlSignature);

        $this->token = implode('.', $tokenParts);

        return $this->token;
    }

    public function getToken()
    {
        $this->createToken();
        return $this->token;
    }

    public static function validateToken(string $tokenString) {

        $tokenParts = explode('.', $tokenString);

        if (3 !== count($tokenParts)) {
            // 'Incorrect quantity of segments'
            return false;
        }

        try {
            $unpackedTokenHeader = json_decode($this->base64UrlDecode($tokenParts[0]), true);
        }
        catch {
            // 'Header does not decode'
            return false;
        }

        try {
            $unpackedTokenBody = json_decode($this->base64UrlDecode($tokenParts[1]), true);
        }
        catch {
            // 'Body does not decode'
            return false;
        }

        $unpackedTokenSignature = $tokenParts[2];

        if ($unpackedTokenHeader['alg'] !== 'HS256') {
            // 'Wrong algorithm'
            return false;
        }

        $date = new \DateTime('now', 'UTC');

        $utcTimeNow = $date->format("U");

        $expiryTime = $loadedTokenUnpackedBody['exp'];

        // a good JWT integration uses token expiration, I am forcing your hand
        if (($utcTimeNow - $this->exp) > 0 ) {
            // 'Expired (exp)'
            return false;
        }

        // if nbf is set
        if (null !== $this->nbf) {
            if ($this->nbf < $utcTimeNow) {
                // 'Too early for not before(nbf) value'
                return false;
            }
        }

        //ToDo: all the database token stuff
        if ('record for this token in revoked tokens exists') {
            // 'Revoked'

            // clean out revoked token records if the UTC unix time ends in "0"
            if ((0 + 0) === (substr($utcTimeNow, -1) + 0)) {

            }

            return false;
        }

        $this->createToken();

        // verify the signature
        $recreatedToken = $this->readToken();
        $recreatedTokenParts = explode($recreatedToken);
        $recreatedTokenSignature = $recreatedTokenParts[3];

        if ($recreatedTokenSignature !== $loadedTokenSignature) {
            // 'signature invalid, potential tampering
            return false;
        }

        // the token checks out!
        return true;
    }

    public function revokeToken() {
        //ToDo: add a record for the token jti claim
    }

    public function loadToken(string $tokenString) {
        $this->token = $tokenString;
        $this->unpackToken();
    }

    // From here out claims are equal, standard and custom have parity

    public function getClaims()
    {
        //$this->unpackToken($this->token);

        $standardClaims  = [
            'iss' => $this->iss,
            'sub' => $this->sub,
            'aud' => $this->aud,
            'exp' => $this->exp,
            'nbf' => $this->nbf,
            'iat' => $this->iat,
            'jti' => $this->jti
        ];

        $allClaims = array_merge($standardClaims, $this->customClaims);
        return $allClaims;
    }

    private function base64UrlEncode(string $unencodedString) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($unencodedString));
    }

    private function base64UrlDecode(string $base64UrlEncodedString) {
        return base64_decode(strtr($base64UrlEncodedString, '-_', '+/'));
    }

    private function makeHmacHash(string $base64UrlHeader, string $base64UrlClaims) {
        // sha256 is the only algorithm. sorry, not sorry.
        return hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlClaims, $this->secretKey, true);
    }

    public function clearClaims () {
        $this->iss = null;
        $this->sub = null;
        $this->aud = null;
        $this->exp = null;
        $this->nbf = null;
        $this->iat = null;
        $this->jti = null;

        $this->customClaims = [];
    }

    public function setStandardClaims(array $standardClaims) {
        foreach ($standardClaims as $claimKey => $value) {
            if ($value != null) {
                if (in_array($claimKey, array('iss', 'sub', 'aud', 'exp', 'nbf', 'iat', 'jti'), true )) {
                    $this->{$claimKey} = $value;
                }

                else {
                    $this->{$claimKey} = null;
                }
            }
        }
    }

    public function setCustomClaims(array $customClaims) {
        foreach ($customClaims as $claimKey => $value) {
            if ($value != null) {
                $this->customClaims[$claimKey] = $value;
            }
        }
    }

    public function deleteStandardClaims(string $standardClaimNamesCommaSeparated) {
        $standardClaims = explode(',', $standardClaimNamesCommaSeparated);
        foreach ($standardClaims as $claimKey) {
            $this->{$claimKey} = null;
        }
    }


    public function deleteCustomClaims(string $customClaimNamesCommaSeparated) {
        $customClaims = explode(',', $customClaimNamesCommaSeparated);
        foreach ($customClaims as $claimKey) {
            $this->customClaims[$claimKey] = null;
        }
    }

    private function unpackToken(bool $clearClaimsFirst = true) {

        if ($clearClaimsFirst === true) {
            $this->clearClaims();
        }

        $tokenParts = explode('.', $this->token);
        $tokenClaims = json_decode($this->base64UrlDecode($tokenParts[1]), true);
        foreach ($tokenClaims as $claimKey => $value) {
            // ToDo: in array this...
            if (in_array($claimKey, array('iss', 'sub', 'aud', 'exp', 'nbf', 'iat', 'jti'), true )) {
                $this->{$claimKey} = $value;
            }
            else {
                $this->customClaims[$claimKey] = $value;
            }
        }
    }
}