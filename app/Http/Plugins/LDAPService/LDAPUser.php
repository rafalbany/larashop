<?php

namespace App\Http\Plugins\LDAPService;
/**
 * LDAP User class.
 *
 * class LDAPUser
 * @package App\Http\Panel\LDAPService
 */
class LDAPUser {

    /**
     * @var string
     */
    private $_user = null;
    /**
     * @var string
     */
    private $_userPass = null;
    /**
     * @var string
     */
    private $_userDn = null;
    /**
     * @var string
     */
    private $_objectGuid = null;
    /**
     * @var string
     */
    private $_userGroups = null;

    public function __construct(string $user = null, string $password = null, $baseDn = null)
    {
        if($user && $password)
            $this->setUserCredentials($user, $password);
        if($baseDn)
            $this->setUserDn($baseDn);
    }

    /**
     * This function sets User Credentials.
     *
     * @param string $user
     * @param string $password
     *
     * @return $this
     */
    public function setUserCredentials(string $user, string $password) {
        $this->_user = $user;
        $this->_userPass = $password;
        return $this;
    }
    /**
     * Sets User DN
     *
     * @param string $userDn
     *
     * @return $this
     */
    public function setUserDn(string $userDn) {
        $this->_userDn = $userDn;
        return $this;
    }
    /**
     * Sets User Object GUID
     *
     * @param mixed $objectGuid
     *
     * @return $this
     */
    public function setObjectGuid($objectGuid) {
        $this->_objectGuid = $objectGuid;
        return $this;
    }
    /**
     * Get User Object GUID
     *
     * @return mixed
     */
    public function getObjectGuid() {
        return $this->_objectGuid;
    }
    /**
     * Sets User groups
     *
     * @param mixed $objectGuid
     *
     * @return $this
     */
    public function setUserGroups(array $groups) {
        $this->_userGroups = $groups;
        return $this;
    }
    /**
     * Get User groups
     *
     * @return mixed
     */
    public function getUserGroups() {
        return $this->_userGroups;
    }
    /**
     * Get User Login
     *
     * @return string
     */
    public function getUserLogin() {
        return $this->_user;
    }
    /**
     * Get User Password
     *
     * @return string
     */
    public function getUserPassword() {
        return $this->_userPass;
    }
    /**
     * Get User DN
     *
     * @return string
     */
    public function getUserDn() {
        return $this->_userDn;
    }
}




