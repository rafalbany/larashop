<?php

namespace App\Http\Plugins\LDAPService;

/**
 * Service class to operating on LDAP protocol.
 *
 * class LDAPService
 * @package App\Http\Panel\LDAPService
 */
class LDAPService {

    /**
     * @var array
     */
    private $_config = [];
    /**
     * @var string
     */
    private $_errorCode = null;
    /**
     * @var string
     */
    private $_errorMessage = null;

    /**
     * @var mixed
     */
    private $_ldap = null;
    /**
     * @var LDAPUser
     */
    private $_user = null;
    /**
     * @var LDAPUser
     */
    private $_authUser = null;

    /**
     * Sets Service config.
     *
     * @param mixed $anchor_or_array
     * @param mixed $value
     *
     * @return void
     */
    public function setConfig($anchor_or_array, $value = null) {
        if(is_string($anchor_or_array))
            $this->_config[$anchor_or_array] = $value;
        else {
            if(is_array($anchor_or_array)&&count($anchor_or_array)>0) {
                foreach($anchor_or_array as $a => $v)
                    $this->_config[$a] = $v;
            }
        }
    }

    /**
     * Get Service config.
     *
     * @param string $anchor
     *
     * @return mixed
     */
    public function getConfig(string $anchor) {
        if(isseT($this->_config[$anchor]))
            return $this->_config[$anchor];
    }
    /**
     * Sets error.
     *
     * @param string $errorCode
     * @param string $errorMessage
     *
     * @return void
     */
    public function setError(string $errorCode, string $errorMessage) {
        $this->_errorCode = $errorCode;
        $this->_errorMessage = $errorMessage;
    }
    /**
     * Get error code.
     *
     * @return string
     */
    public function getErrorCode() {
        return $this->_errorCode;
    }
    /**
     * Get error message.
     *
     * @return string
     */
    public function getErrorMessage() {
        return $this->_errorMessage;
    }

    /**
     * Get objects unique GUID
     *
     * @param string $ad
     * @param string $samaccountname
     * @param string $basedn
     *
     * @return string
     */
    public static function getObjectGuid($ad, string $samaccountname, string $basedn) {
        //First try - Windows
        $result = ldap_search($ad, $basedn,
            "(sAMAccountName={$samaccountname})");
        if ($result === FALSE) { return ''; }
        $entries = ldap_get_entries($ad, $result);

        if(!empty($entries['count']))
            return isset($entries[0]['objectguid'][0]) ? bin2hex($entries[0]['objectguid'][0]) : NULL;

        // Second try - overall and rest
        if(empty($entries['count'])) {
            $filter="(|(uid=$samaccountname*))";
            $sr=ldap_search($ad, $basedn, $filter, array('*','entryUUID'));
            $entries = ldap_get_entries($ad, $sr);

            if(isset($entries[0]['entryuuid'][0]))
                return bin2hex($entries[0]['entryuuid'][0]);
            if(isset($entries[0]['uid'][0]))
                return bin2hex($entries[0]['uid'][0]);

            return null;
        }
        return '';
    }

    /**
     * This function searchs in LDAP tree ($ad -LDAP link identifier)
     * entry specified by samaccountname and returns its DN or epmty
     * string on failure.
     *
     * @param string $ad
     * @param string $samaccountname
     * @param string $basedn
     *
     * @return string
     */
    public static function getDN($ad, string $samaccountname, string $basedn) {
        //First try - Windows
        $attributes = array('dn');
        $result = ldap_search($ad, $basedn,
            "(sAMAccountName={$samaccountname})", $attributes);
        if ($result === FALSE) { return ''; }
        $entries = ldap_get_entries($ad, $result);

        if ($entries['count']>0) { return $entries[0]['dn']; }

        // Second try - overall and rest
        if(empty($entries['count'])) {
            $filter = '(&(objectClass=inetOrgPerson) (uid='.$samaccountname.'))';
            $res = ldap_search($ad, $basedn, $filter);
            $first = ldap_first_entry($ad, $res);
            if($first) {
                return ldap_get_dn($ad, $first);
            }
        }
        return '';
    }

    /**
     * This function retrieves and returns CN from given DN
     *
     * @param string $dn
     *
     * @return bool
     */
    public static function getCN(string $dn) {
        preg_match('/[^,]*/', $dn, $matchs, PREG_OFFSET_CAPTURE, 3);
        return $matchs[0][0];
    }

    /**
     * This function checks group membership of the user, searching only
     * in specified group (not recursively).
     *
     * @param string $ad
     * @param string $userdn
     * @param string $groupdn
     *
     * @return bool
     */
    public static function checkGroup($ad, string $userdn, string $groupdn) {
        $attributes = array('members');
        $result = ldap_read($ad, $userdn, "(memberof={$groupdn})", $attributes);
        if ($result === FALSE) { return FALSE; };
        $entries = ldap_get_entries($ad, $result);
        return ($entries['count'] > 0);
    }
    /**
     * This function checks group membership of the user, searching
     * in specified group and groups which is its members (recursively).
     *
     * @param string $ad
     * @param string $userdn
     * @param string $groupdn
     *
     * @return bool
     */
    public static function checkGroupEx($ad, string $userdn, string $groupdn) {
        $attributes = array('memberof');
        $result = ldap_read($ad, $userdn, '(objectclass=*)', $attributes);
        if ($result === FALSE) { return FALSE; };
        $entries = ldap_get_entries($ad, $result);
        if ($entries['count'] <= 0) { return FALSE; };
        if (empty($entries[0]['memberof'])) { return FALSE; } else {
            for ($i = 0; $i < $entries[0]['memberof']['count']; $i++) {
                if ($entries[0]['memberof'][$i] == $groupdn) { return TRUE; }
                elseif (self::checkGroupEx($ad, $entries[0]['memberof'][$i], $groupdn)) { return TRUE; };
            };
        };
        return FALSE;
    }
    /**
     * This function sets LDAP Host.
     *
     * @param string $host
     *
     * @return $this
     */
    public function setHost(string $host) {
        $this->setConfig('host',$host);
        return $this;
    }
    /**
     * This function gets LDAP Host.
     *
     * @return string
     */
    public function getHost() {
        return $this->getConfig('host');
    }
    /**
     * This function sets LDAP port.
     *
     * @param string $port
     *
     * @return $this
     */
    public function setPort(string $port) {
        $this->setConfig('port',$port);
        return $this;
    }
    /**
     * This function gets LDAP port.
     *
     * @return string
     */
    public function getPort() {
        return $this->getConfig('port');
    }
    /**
     * This LDAP Conn.
     *
     * @return string
     */
    public function getLDAP() {
        return $this->_ldap;
    }
    /**
     * Close current LDAP Conn.
     *
     * @return void
     */
    public function closeLDAP() {
        ldap_close($this->getLDAP());
    }
    /**
     * This function sets LDAP Base DN.
     *
     * @param string $baseDn
     *
     * @return $this
     */
    public function setBaseDn(string $baseDn) {
        $this->setConfig('base_dn',$baseDn);
        return $this;
    }
    /**
     * This function gets LDAP Base DN.
     *
     * @return string
     */
    public function getBaseDn() {
        return $this->getConfig('base_dn');
    }
    /**
     * Sets LDAP Auth User
     *
     * @param LDAPUser $user
     *
     * @return $this
     */
    public function setAuthUser(LDAPUser $user) {
        $this->_authUser = $user;
        return $this;
    }
    /**
     * Get LDAP Auth User
     *
     * @return LDAPUser
     */
    public function getAuthUser() {
        return $this->_authUser;
    }
    /**
     * Sets LDAP User
     *
     * @param LDAPUser $user
     *
     * @return $this
     */
    public function setUser(LDAPUser $user) {
        $this->_user = $user;
        return $this;
    }
    /**
     * Get LDAP User
     *
     * @return LDAPUser
     */
    public function getUser() {
        return $this->_user;
    }

    /**
     * Get LDAP User Groups - alternative way
     *
     * @return mixed
     */
    public function getADGroups($ad, $baseDn, $login) {
        // Search AD
        $results = ldap_search($ad,$baseDn,"(samaccountname={$login})",array("memberof","primarygroupid"));
        $entries = ldap_get_entries($ad, $results);

        if(empty($entries[0]))
            return null;

        // Get groups and primary group token
        $output = $entries[0]['memberof'];
        $token = $entries[0]['primarygroupid'][0];

        // Remove extraneous first entry
        array_shift($output);

        // We need to look up the primary group, get list of all groups
        $results2 = ldap_search($ad,$baseDn,"(objectcategory=group)",array("distinguishedname","primarygrouptoken"));
        $entries2 = ldap_get_entries($ad, $results2);

        // Remove extraneous first entry
        array_shift($entries2);

        // Loop through and find group with a matching primary group token
        foreach($entries2 as $e) {
            if($e['primarygrouptoken'][0] == $token) {
                // Primary group found, add it to output array
                $output[] = $e['distinguishedname'][0];
                // Break loop
                break;
            }
        }

        return $output;
    }
    /**
     * Get LDAP User Groups
     *
     * @return array
     */
    public function getUserGroups(LDAPUser $user) {
        if($this->getLDAP()) {
            try {

                $allGroupsArr = [];

                // First try - Windows
                $filter = "(&(objectClass=group)(member:1.2.840.113556.1.4.1941:={$user->getUserDn()}))";
                $search = ldap_search($this->getLDAP(), $this->getBaseDn(), $filter, array("cn"));

                $allGroups = ldap_get_entries($this->getLDAP(), $search);

                if(empty($allGroups["count"])) {
                    // Second try - overall and rest
                    $query = "(&(objectClass=groupOfUniqueNames)(uniqueMember=" . $user->getUserDn() . "))";
                    $results = ldap_search($this->getLDAP(),$this->getBaseDn(),$query);
                    $allGroups = ldap_get_entries($this->getLDAP(), $results);
                }

                if(!empty($allGroups["count"])) {
                    for ($i=0; $i < $allGroups["count"]; $i++) {
                        $allGroupsArr[] = $allGroups[$i]["cn"][0];
                    }
                }
                $user->setUserGroups($allGroupsArr);

                return $allGroupsArr;
            } catch(\Exception $exception)  {
                $this->setError($exception->getCode(), $exception->getMessage());
            }
        }
    }
    /**
     * Authenticate LDAP User and optionally, if authenticated, find DN other given LDAP User
     * @param LDAPUser $user
     * @param LDAPUser $userToFind
     * @param bool $closeConnection
     *
     * @return mixed
     */
    public function authenticate(LDAPUser $user, LDAPUser $userToFind = null, bool $closeConnection = true) {

        try {
            $ldap = ldap_connect($this->getHost(), $this->getPort());
            $this->_ldap = $ldap;

            ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
            $bind = ldap_bind($ldap, $user->getUserDn(), $user->getUserPassword());

            if($userToFind && $bind) {
                $userToFindDn = self::getDN($ldap, $userToFind->getUserLogin(), $this->getBaseDn());

                if(!empty($userToFindDn)) {
                    $objectGuid = self::getObjectGuid($ldap, $userToFind->getUserLogin(), $this->getBaseDn());

                    if($objectGuid)
                        $userToFind->setObjectGuid($objectGuid);
                }
                return $userToFindDn;
            }

            if($closeConnection)
                ldap_close($ldap);
            return $bind;
        } catch(\Exception $exception)  {
            $this->setError($exception->getCode(), $exception->getMessage());
        }
    }

}




