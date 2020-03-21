<?php

namespace App\Console\Commands;

use App\Services\Lists\SubSet\SubSetListener;
use Illuminate\Console\Command;
use MQ\Model\TopicMessage;
use MQ\MQClient;


class AliyunMqProductCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AliyunMqProductCommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '阿里云mq消费';

    protected $client = null;

    protected $_instanceId = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $endPoint = getenv('ALIYUN_MQ_END_POINT');
        $accessId = getenv('ALIYUN_MQ_ACCESS_ID');
        $accessKey = getenv('ALIYUN_MQ_ACCESS_KEY');
        $this->_instanceId = getenv('ALIYUN_MQ_INS_ID');

        //var_dump($endPoint, $accessId, $accessKey);exit;

        $this->client = new MQClient($endPoint, $accessId, $accessKey);
    }

    public function getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        //$this->producerTest();exit;

        // get message consumer
        $consumer = $this->client->getConsumer($this->_instanceId,"product", "GID_product");

        while (true) {
            try {
                $messages = $consumer->consumeMessage(4, 3);
            } catch (\Exception $e) {
                if ($e instanceof MQ\Exception\MessageNotExistException) {
                    // no new message;
                    // long polling again.
                    logInfo('AliyunMqCommand no new message');
                    continue;
                }
                logInfo('AliyunMqCommand', $e->getMessage());
                sleep(3);
                continue;
            }
            logInfo('AliyunMqCommand consume finish, messages');
            $receiptHandles = array();
            foreach ($messages as $message) {
                $receiptHandles[] = $message->getReceiptHandle();
                $latency = $this->getMillisecond() - $message->getPublishTime();
                $body = $message->getMessageBody();
                $msg = [
                    'ID', $message->getMessageId(), 'LAT' => $latency, 'TAG' => $message->getMessageTag(), 'BODY' => $body,
                    'PublishTime' => $message->getPublishTime(), 'FirstConsumeTime' => $message->getFirstConsumeTime(),
                    'ConsumedTimes' => $message->getConsumedTimes(), 'NextConsumeTime' => $message->getNextConsumeTime(),
                ];
                logInfo('AliyunMqCommand info', $msg);
                $body = @json_decode($body, true);
                $body = empty($body) ? [] : $body;
                $this->dispatch($message->getMessageTag(), $body);
            }
            $ackMessage = $consumer->ackMessage($receiptHandles);
            logInfo('AliyunMqCommand ack finish', ['receiptHandles' => $receiptHandles, 'ackMessage' => $ackMessage]);
            sleep(3);
        }
    }

    public function producerTest()
    {
        // get message producer
        // 使用实例则带上实例ID，否则为:NULL，注意：默认实例不需要实例ID
        $producer = $this->client->getProducer($this->_instanceId,"product");
        // publish one message to topic abc
        $msg = new TopicMessage(json_encode(['a' => rand(1, 9), 'b' => rand(1, 9)]));
        $msg->messageTag = 'product_update';
        $topicMessage = $producer->publishMessage(
            $msg
        );
        print "\npublish finish -> " . $topicMessage->getMessageId() . " " . $topicMessage->getMessageBodyMD5() . "\n";
    }

    public function dispatch($messageTag, $body)
    {
        switch ($messageTag) {
            case 'product_update':
                $this->productUpdate($body);
                break;


        }
    }

    public function productUpdate($body)
    {
        if (empty($body['productId'])) {
            return false;
        }

        $productIds = $body['productId'];

        $lister = new SubSetListener();
        $lister->fire(function ($obj) use ($productIds) {
            $obj->productUpdateListener($productIds);
        });
    }
}
