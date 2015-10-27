<?php
  namespace MyApp;
  use Ratchet\MessageComponentInterface; //Ratchet class where Server sends messages to client.
  use Ratchet\ConnectionInterface; //Ratchet connectioninterface to keep connection open with server and client

  class Chat implements MessageComponentInterface {
    protected $clients;

    // Initializing construct function
    public function __construct(){
        $this->clients = new \SplObjectStorage; //Initialize clients as stdclasses
    }

    // On open event
    public function onOpen(ConnectionInterface $conn){
      // Store the new connection to send messages to later
      $this->clients->attach($conn);

      echo "New connection! ({$conn->resourceId})";
    }

    public function onMessage(ConnectionInterface $from, $msg){
      $numRecv = count($this->clients) - 1;
      echo  sprintf('Connection %d sending message "%s" to %d other connection%s'."\n",
      $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

      foreach($this->clients AS $client){
        // If the sender is no tthe reciever
        if($from !== $client)
          $client->send($msg); //Send to every other reciever
      }
    }

    public function onClose(ConnectionInterface $conn) {
      // The connection is closed remove it as we no longer send it messages.
      $this->clients->detach($conn);
      echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
      echo "An error has occured: {$e->getMessage()}";
      $conn->close();
    }
  }
?>
