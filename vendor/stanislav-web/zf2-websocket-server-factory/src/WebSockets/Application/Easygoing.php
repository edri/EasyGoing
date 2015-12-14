<?php
namespace WebSockets\Application;

use	WebSockets\Exception,
	WebSockets\Aware,
	WebSockets\Service\WebsocketServer;

/**
 * Simply web socket Chat. (Notice: do the favourite app's like this example ;-)
 * @package Zend Framework 2
 * @subpackage Websockets
 * @since PHP >=5.4
 * @version 1.0
 * @author Stanislav WEB | Lugansk <stanisov@gmail.com>
 * @copyright Stanislav WEB
 * @license Zend Framework GUI license
 * @filesource /vendor/Websockets/src/Websockets/Application/Chat.php
 */
class Easygoing implements Aware\ApplicationInterface
{
    /**
     * \WebSockets\Service\WebsocketServer $_server
     * @access protected
     * @var object
     */
    protected $_server = null;

    /**
     * __construct(WebsocketServer $server)
     * @param \WebSockets\Service\WebsocketServer $server
     * @return instance of WebsocketServer object
     */
    public function __construct(WebsocketServer $server)
    {
        // set ServiceManager throught constructor
        if(null === $this->_server)  $this->_server = $server;
    }

    /**
     * onOpen() opening a connection to the server
     * @param int $clientId connect identifier
     * @access public
     */
    public function onOpen($clientId)
    {
		$this->say("A new client opened a connection: #" . $clientId);
    }

    /**
     * onMessage($clientId, $message) get messages from server (request / response)
     * @param int $clientId connect identifier
     * @param varchar $message costom message throught socket
     * @access public
     */
    public function onMessage($clientId, $message)
    {
		$this->say("New message from client #" . $clientId . ": '" . $message . "'");
    }

    /**
     * onClose($clientId) closing a connection to the server
     * @param int $clientId connect identifier
     * @access public
     */
    public function onClose($clientId)
    {
		$this->say("Client #" . $clientId . " left the server");
    }

    /**
     * say($message) print console messanger. Will be able as Server callback function!
     * @param string $message
     * @access public
     */
    public function say($message)
    {
		$message = mb_convert_encoding($message, $this->_server->config['encoding']);
		echo date('[Y-m-d H:i:s] ').$message."\r\n";
    }

    /**
     * __call($name, $argument) need to do overloading! Its mostly setup event from server object
     * @param string $name function from webSocket Server class
     * @param array $argument
     * @return null
     */
    public function __call($name, $arguments)
	{
		if(!method_exists(get_class($this->_server), $name))
			throw new Exception\ExceptionStrategy("Error! Function {$name} does not exist in ".get_class($this->_server));

		else if(sizeof($arguments) != 2)
			throw new Exception\ExceptionStrategy("Error! Arguments setup incorrectly in ".__CLASS__);

		return $this->_server->$name($arguments[0], $arguments[1], $this);
	}

    /**
     * run() running application
     * @access public
     */
    public function run()
    {
		return $this->_server->run();
    }
}
