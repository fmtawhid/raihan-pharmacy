<?php

namespace App\Notify;

use App\Notify\NotifyProcess;
use App\Notify\Notifiable;
use App\Services\DeelkoSmsService;


class Sms extends NotifyProcess implements Notifiable
{

    /**
     * Mobile number of receiver
     *
     * @var string
     */
    public $mobile;

    /**
     * Assign value to properties
     *
     * @return void
     */
    public function __construct()
    {
        $this->statusField = 'sms_status';
        $this->body = 'sms_body';
        $this->globalTemplate = 'sms_template';
        $this->notifyConfig = 'sms_config';
    }


    /**
     * Send notification
     *
     * @return void|bool
     */
    public function send()
    {
        if (!gs('sn')) {
            return false;
        }

        //get message from parent
        $message = $this->getMessage();
        if ($message) {
            if ($this->mobile) {
                $this->getSmsFrom();
                $result = app(DeelkoSmsService::class)->sendMasking($this->mobile, strip_tags($message));

                if ($result['success']) {
                    $this->createLog('sms');
                } else {
                    $this->createErrorLog('SMS Error: ' . $result['message']);
                    session()->flash('sms_error', $result['message']);
                }
            }
        }
    }

    /**
     * Configure some properties
     *
     * @return void
     */
    public function prevConfiguration()
    {
        //Check If User
        if ($this->user) {
            $this->mobile = $this->user->mobileNumber;
            $this->receiverName = $this->user->fullname;
        }
        $this->toAddress = $this->mobile;
    }

    private function getSmsFrom()
    {
        $this->sentFrom = $this->replaceTemplateShortCode($this->template->sms_sent_from ?? gs('sms_from'));
        return $this->sentFrom;
    }
}
