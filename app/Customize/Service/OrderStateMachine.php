<?php

namespace Customize\Service;


use Eccube\Entity\BaseInfo;
use Eccube\Entity\Order;
use Eccube\Repository\BaseInfoRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class OrderStateMachine implements EventSubscriberInterface
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    private $baseInfoRepository;

    public function __construct(\Swift_Mailer $mailer, BaseInfoRepository $baseInfoRepository)
    {
        $this->mailer = $mailer;
        $this->baseInfoRepository = $baseInfoRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.order.transition.to_naire' => ['toNaire'],
            'workflow.order.transition.to_naire_complete' => ['toNaireComplete'],
        ];
    }

    public function toNaire(Event $event)
    {
        $BaseInfo = $this->baseInfoRepository->get();
        $Order = $event->getSubject()->getOrder();
        $subject = '['.$BaseInfo->getShopName().'] 名入れ職人の手配をしました。';
        $body = '名入れを開始します。';

        $this->sendMeil($BaseInfo, $Order, $subject, $body);
    }

    public function toNaireComplete(Event $event)
    {
        $BaseInfo = $this->baseInfoRepository->get();
        $Order = $event->getSubject()->getOrder();
        $subject = '['.$BaseInfo->getShopName().'] 名入れが完了しました。';
        $body = '名入れが完了しました。';

        $this->sendMeil($BaseInfo, $Order, $subject, $body);
    }

    public function sendMeil(BaseInfo $BaseInfo, Order $Order, $subject, $body)
    {
        $message = (new \Swift_Message())
            ->setSubject($subject)
            ->setFrom([$BaseInfo->getEmail01() => $BaseInfo->getShopName()])
            ->setTo([$Order->getEmail()])
            ->setBcc($BaseInfo->getEmail01())
            ->setReplyTo($BaseInfo->getEmail03())
            ->setReturnPath($BaseInfo->getEmail04());

        $message->setBody($body);
        $this->mailer->send($message);
    }
}