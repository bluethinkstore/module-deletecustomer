<?php
/**
 * Copyright © Bluethinkinc All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Bluethinkinc\DeleteCustomer\Model\Config;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
#[\AllowDynamicProperties]

/**
 * DeleteCustomer configuration model
 */
class Provider
{
    private const EMAIL_SENDER = 'deletecustomer/generaloptions/emailsender';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Email Sender's name and email config value
     *
     * @return array
     */
    public function senderNameEmail(): array
    {
        $storeScope = ScopeInterface::SCOPE_STORE;
        $sender = $this->scopeConfig->getValue(self::EMAIL_SENDER, $storeScope);
        return [
            'name' => $this->scopeConfig->getValue('trans_email/ident_'.$sender.'/name', $storeScope),
            'email' => $this->scopeConfig->getValue('trans_email/ident_'.$sender.'/email', $storeScope)
        ];
    }
}
