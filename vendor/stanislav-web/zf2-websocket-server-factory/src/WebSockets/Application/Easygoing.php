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
    }

    /**
     * onMessage($clientId, $message) get messages from server (request / response)
     * @param int $clientId connect identifier
     * @param varchar $message costom message throught socket
     * @access public
     */
    public function onMessage($clientId, $message)
    {
    }

    /**
     * onClose($clientId) closing a connection to the server
     * @param int $clientId connect identifier
     * @access public
     */
    public function onClose($clientId)
    {
    }

    /**
     * say($message) print console messanger. Will be able as Server callback function!
     * @param string $message
     * @access public
     */
    public function say($message)
    {
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
