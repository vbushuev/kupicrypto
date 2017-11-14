<?php namespace Vsb\Crypto\Classes;
use BlockCypher\Api\TX;
use BlockCypher\Auth\SimpleTokenCredential;
use BlockCypher\Client\TXClient;
use BlockCypher\Rest\ApiContext;

class Transfer{
    protected $_tokens=[];
    protected $_address = '1srgRQWTa4QaAg8vZCmHSwBCHTr3ucRgP';
    protected $_apiContext=false;
    public function __construct(){
        $this->_tokens[]='9521d647d6c44f8c94b2edbc8d205086';
        $this->_apiContext = ApiContext::create(
            'test', 'bcy', 'v1',
            new SimpleTokenCredential($this->_tokens[0]),
            array('mode' => 'sandbox', 'log.LogEnabled' => true, 'log.FileName' => 'BlockCypher.log', 'log.LogLevel' => 'DEBUG')
        );
    }
    public function send($address,$amount){
        // Create a new instance of TX object
        $tx = new TX();

        // Tx inputs
        $input = new \BlockCypher\Api\TXInput();
        $input->addAddress($this->_address);//"C5vqMGme4FThKnCY44gx1PLgWr86uxRbDm"
        $tx->addInput($input);
        // Tx outputs
        $output = new \BlockCypher\Api\TXOutput();
        $output->addAddress($address);
        $tx->addOutput($output);
        // Tx amount
        $output->setValue($amount); // Satoshis

        // For Sample Purposes Only.
        $request = clone $tx;

        $txClient = new TXClient($this->_apiContext);
        $txSkeleton = $txClient->create($tx);

        // ResultPrinter::printResult("New TX Endpoint", "TXSkeleton", $txSkeleton->getTx()->getHash(), $request, $txSkeleton);
    }
};
?>
