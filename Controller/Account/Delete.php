<?php
/**
 * Copyright © Bluethinkinc All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Bluethinkinc\DeleteCustomer\Controller\Account;

use Exception;
use Psr\Log\LoggerInterface;
use Magento\Framework\Registry;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Bluethinkinc\DeleteCustomer\Helper\Mail;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\Result\RedirectFactory;

class Delete extends Action
{
    /**
     * Constructor
     *
     * @param Mail $mail
     * @param Context $context
     * @param Registry $registry
     * @param LoggerInterface $logger
     * @param Session $customerSession
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $resultRedirectFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Mail $mail,
        Context $context,
        Registry $registry,
        LoggerInterface $logger,
        Session $customerSession,
        ManagerInterface $messageManager,
        RedirectFactory $resultRedirectFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->mail = $mail;
        $this->logger = $logger;
        $this->registry = $registry;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }

    /**
     * This will delete customer account and redirected to the home page
     *
     * @return void
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!($this->customerSession->isLoggedIn())) {
            $this->messageManager->addNotice(__("Please Login here to delete your account"));
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        } else {
            $checkboxValue = $this->getRequest()->getParam('isConfirmToDelete');

            if (!$checkboxValue) {
                $resultRedirect->setPath('customer/account/login');
                return $resultRedirect;
            } else {
                try {
                    $this->registry->register('isSecureArea', true);
                    // Deleting the customer
                    $customerId = $this->customerSession->getCustomerId();
                    $customerEmail = $this->customerSession->getCustomer()->getEmail();
                    $customerName = $this->customerSession->getCustomer()->getName();
                    $customer = $this->customerRepository->getById($customerId);
                    $this->customerRepository->delete($customer);
                    // Sending a confirmation email to the customer
                    $this->mail->sendEmail($customerEmail, $customerName);
                    // Redirecting to Home page with a notice
                    $this->messageManager->addNotice(__("Your account has been deleted and a confirmation
                    email has been sent to your registered email id."));
                    $resultRedirect->setPath('/');
                    return $resultRedirect;
                } catch (Exception $e) {
                    $this->logger->warning("Delete Customer Module : " . $e->getMessage());
                }
            }
        }
    }
}
