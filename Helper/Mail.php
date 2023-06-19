<?php
/**
 * Copyright © Bluethinkinc All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Bluethinkinc\DeleteCustomer\Helper;

use Magento\Framework\Escaper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Bluethinkinc\DeleteCustomer\Model\Config\Provider;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Mail extends AbstractHelper
{
    /** @var $logger */
    protected $logger;

    /** @var Escaper $escaper */
    protected $escaper;

    /** @var ScopeConfigInterface $scopeConfig */
    protected $scopeConfig;

    /** @var TransportBuilder $transportBuilder */
    protected $transportBuilder;

    /** @var StateInterface $inlineTranslation */
    protected $inlineTranslation;

    /**
     * Constructor
     *
     * @param Escaper $escaper
     * @param Context $context
     * @param Provider $provider
     * @param ScopeConfigInterface $scopeConfig
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     */
    public function __construct(
        Escaper $escaper,
        Context $context,
        Provider $provider,
        ScopeConfigInterface $scopeConfig,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder
    ) {
        parent::__construct($context);
        $this->escaper = $escaper;
        $this->provider = $provider;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $context->getLogger();
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
    }

    /**
     * This function is trying to send the email to customer
     *
     * @param String $customerEmail
     * @param String $customerName
     * @return void
     */
    public function sendEmail($customerEmail, $customerName)
    {
        $sender = $this->provider->senderNameEmail();
        $senderName = $sender['name'];
        $senderEmail = $sender['email'];

        try {
            $this->inlineTranslation->suspend();
            $templateVars = [
                'customer_name' => $customerName
            ];
            $sender = [
                'name' => $this->escaper->escapeHtml($senderName),
                'email' => $this->escaper->escapeHtml($senderEmail)
            ];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('deletecustomer_generaloptions_emailtemplate')
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                    ]
                )
                ->setTemplateVars($templateVars)
                ->setFrom($sender)
                ->addTo($customerEmail)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }
}
