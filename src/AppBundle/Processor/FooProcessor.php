<?php

namespace AppBundle\Processor;

use Interop\Queue\PsrMessage;
use Interop\Queue\PsrContext;
use Interop\Queue\PsrProcessor;
use Enqueue\Client\TopicSubscriberInterface;

class FooProcessor implements PsrProcessor, TopicSubscriberInterface
{
    public function process(PsrMessage $message, PsrContext $session)
    {
        if ($message->isRedelivered()) {
            return self::REQUEUE;
        }


        echo $message->getBody();
//        return self::ACK;
// return self::REJECT; // when the message is broken
            return self::REQUEUE; // the message is fine but you want to postpone processing
    }

    public static function getSubscribedTopics()
    {
        return [
            'aFooTopic'
        ];
    }
}