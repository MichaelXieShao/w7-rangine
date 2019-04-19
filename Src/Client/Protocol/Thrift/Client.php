<?php

namespace W7\Client\Protocol\Thrift;

use Thrift\Protocol\TBinaryProtocol;
use Thrift\Protocol\TMultiplexedProtocol;
use Thrift\Transport\TFramedTransport;
use Thrift\Transport\TSocket;
use W7\Client\Protocol\ClientAbstract;
use W7\Client\Protocol\Thrift\Core\DispatcherClient;

class Client extends ClientAbstract
{
	private $host;
	private $port;

	public function __construct($host) {
		$pos = strrpos($host, ':');
		if ($pos !== false) {
			$this->host = substr($host, 0, $pos);
			$this->port = substr($host, $pos + 1);
		} else {
			$this->host = $host;
		}
	}

	public function call($url, $data = null)
    {
	    $socket = new TSocket($this->host, $this->port);
	    $transport = new TFramedTransport($socket);
	    $protocol = new TBinaryProtocol($transport);
	    $service = new TMultiplexedProtocol($protocol, 'Dispatcher');
	    $transport->open();

	    $client = new DispatcherClient($service);
	    $body = [
		    'url' => $url
	    ];
	    if ($data) {
	    	$body['data'] = $data;
	    }
	    $ret = $client->run(json_encode($body));
	    $transport->close();

	    return $ret;
    }
}