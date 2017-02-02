<?php

namespace Bkademy\Webpos\Model;

class Staff extends \Magento\Framework\Model\AbstractModel
{
    /**
     *
     */
    const PASSWORD = 'password';

    /**
     *
     */
    const USER_NAME = 'username';
    /**
     *
     */
    const MIN_PASSWORD_LENGTH = 7;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * Staff constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\User\Helper\Data $userData
     * @param \Magento\Backend\App\ConfigInterface $config
     * @param \Magento\Framework\Validator\DataObjectFactory $validatorObjectFactory
     * @param \Magento\Authorization\Model\RoleFactory $roleFactory
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Authorization\Model\RoleFactory $roleFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->encryptor = $encryptor;
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('Bkademy\Webpos\Model\ResourceModel\Staff');
    }

    /**
     * @return bool
     */
    public function userExists() {
        $username = $this->getUsername();
        $check = $this->getCollection()->addFieldToFilter('username',$username);
        if ($check->getFirstItem()->getId() && $this->getId() != $check->getFirstItem()->getId()) {
            return true;
        }
        return false;
    }

    /**
     * @return array|bool
     */
    public function validate() {
        $errors = array();
        if ($this->hasNewPassword()) {
            if (strlen($this->getNewPassword()) < self::MIN_PASSWORD_LENGTH) {
                $errors[] = __('Password must be at least of %1 characters.', self::MIN_PASSWORD_LENGTH);
            }

            if (!preg_match('/[a-z]/iu', $this->getNewPassword()) || !preg_match('/[0-9]/u', $this->getNewPassword())
            ) {
                $errors[] = __('Password must include both numeric and alphabetic characters.');
            }

            if ($this->hasPasswordConfirmation() && $this->getNewPassword() != $this->getPasswordConfirmation()) {
                $errors[] = __('Password confirmation must be same as password.');
            }
        }
        if ($this->userExists()) {
            $errors[] = __('A user with the same user name already exists.');
        }
        if (empty($errors)) {
            return true;
        }
        return $errors;
    }


    /**
     * @param $username
     * @return $this
     */
    public function loadByUsername($username) {
        $staffs = $this->getCollection()->addFieldToFilter('username', $username);
        if ($id = $staffs->getFirstItem()->getId())
            $this->load($id);
        return $this;
    }


    /**
     * @param $login
     * @param $password
     * @return bool
     */
    public function authenticate($login, $password) {
        $this->loadByUsername($login);
        if (!$this->validatePassword($password)) 
            return false;
        return true;
    }


    /**
     * @param $password
     * @return bool
     */
    public function validatePassword($password) {
        return $this->getPassword() == $password ? true : false;
    }
    
}