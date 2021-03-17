<?php
// define("TOOL_HOST", "https://sith.knowfear.net/lti/src/web");
namespace App\Library;
use Packback\Lti1p3\Interfaces\Database;
use Packback\Lti1p3\LtiRegistration;
use Packback\Lti1p3\LtiDeployment;
use Packback\Lti1p3\OidcException;


class LTI13Database implements Database {
    
      public static function findIssuer($issuer_url, $client_id = null)
    {
        $query =  \App\LTI13Issuer::where("host", $issuer_url);

        if($client_id) {
            $query->where("client_id", $client_id);
        }
        if ($query->count() > 1) {
            throw new OidcException('Found multiple registrations for the given issuer, ensure a client_id is specified on login (contact your LMS administrator)', 1);
        }
        
        return $query->first();
    }

    public function findRegistrationByIssuer($issuer_id, $client_id=null) {

        $issuer = self::findIssuer($issuer_id, $client_id);
        if (!$issuer) {
            return false;
        }
        return LtiRegistration::new()
            ->setAuthLoginUrl($issuer->auth_login_url)
            ->setAuthTokenUrl($issuer->auth_token_url)
            // ->set_auth_server($_SESSION['iss'][$iss]['auth_server'])
            ->setClientId($issuer->client_id)
            ->setKeySetUrl($issuer->key_set_url)
            ->setKid($issuer->kid)
            ->setIssuer($issuer_id)
            ->setToolPrivateKey($issuer->private_key);
    }

    public function findDeployment($iss, $deployment_id, $client_id = null) {
        
        $deployment = \App\LTI13Deployment::where("deployment_id", $deployment_id)->firstOr(function () use($iss, $deployment_id, $client_id) {
            $deployment = new \App\LTI13Deployment;
            $deployment->deployment_id = $deployment_id;
            $issuer = self::findIssuer($iss, $client_id);
            $deployment->issuer()->associate($issuer);
            $deployment->save();
            return $deployment;
        });
        
        return LtiDeployment::new()
            ->setDeploymentId($deployment->deployment_id);
    }

  
}
?>