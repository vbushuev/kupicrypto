<?php namespace Vsb\Crypto\Classes;

use \Coinbase\Wallet\Client;
use \Coinbase\Wallet\Configuration;

use \Coinbase\Wallet\Resource\Transaction;
use \Coinbase\Wallet\Resource\Account;
use \Coinbase\Wallet\Resource\Address;
use \Coinbase\Wallet\Value\Money;
use \Coinbase\Wallet\Enum\CurrencyCode;
/*
*/
class Coinbase{
    protected $_client;
    public function __construct($apiKey, $apiSecret){
        $configuration = Configuration::apiKey($apiKey, $apiSecret);
        $this->_client = Client::create($configuration);
    }
    public function accounts(){
        $accounts = $this->_client->getAccounts();
        $ret = [];
        foreach($accounts as $account){
            $ret[]=$this->account($account->getId());
        }
        return $ret;
    }
    public function account($id){
        $account = $this->_client->getAccount($id);
        return [
            "id"=>$account->getId(),
            "name"=>$account->getName(),
            'currency'=>$account->getCurrency(),
            'balance'=>$account->getBalance()->getAmount(),
            // 'amount'=>$account->getAmount()
        ];
    }
    public function transactions(){
        $account = $this->_client->getPrimaryAccount();
        $transactions = $this->_client->getAccountTransactions($account);
        $ret = [];
        foreach($transactions as $trx){
            $ret[]=[
                'id' => $trx->getId(),
                'type' => $trx->isSend()?'send':($trx->isRequest()?'request':'transfer'),
                'created' => $trx->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated' => $trx->getUpdatedAt()->format('Y-m-d H:i:s'),
                'status' => $trx->getStatus(),
                'currency' => $trx->getAmount()->getCurrency(),
                'amount' => $trx->getAmount()->getAmount(),
                'from' => $trx->getFrom(),
                'to' => $trx->getTo(),
                'buy' => $trx->getBuy(),
                'sell' => $trx->getSell(),
                'fee' => $trx->getFee(),
                'description' => $trx->getDescription(),
                'network' => $trx->getNetwork(),
                'address' => $trx->getAddress(),
                'application' => $trx->getApplication()
            ];
        }
        return $ret;
    }
    public function getBalance($wallet){
        $accs = $this->accounts();
        foreach ($accs as $key => $value) {
            if($value["currency"] == $wallet) return $value["balance"];
        }
        return 0;
    }
    public function checkBalance($amt,$wallet){
        $accs = $this->accounts();
        $acc = null;
        foreach ($accs as $key => $value) {
            if($value["currency"] == $wallet) $acc = $value;
        }
        if(is_null($acc) )return false;
        return ($acc["balance"]>$amt);
    }
    public function request($id,$amount){
        $account = $this->_client->getAccount($id);
        $address = new Address([
            'name' => 'New Address'
        ]);
        $this->_client->createAccountAddress($account, $address);
        $ret = $address->getRawData();
        return $ret;
        $transaction = Transaction::request([
            'toAddress' => $account->getAddress(),
            'amount'      => new Money($amount, CurrencyCode::BTC),
            'description' => 'Request'
        ]);
        $this->_client->createAccountTransaction($account,$transaction);
        return $transaction;
    }
    public function fund($address,$amount,$currency='BTC'){
        // $fromAccount = Account::reference($accountId);
        // $toAccount = Account::reference($toAccountId);

        // $transaction = Transaction::transfer([
        //     'to'            => $toAccount,
        //     'bitcoinAmount' => $amount,
        //     'description'   => ''
        // ]);
        $account = $this->_client->getPrimaryAccount();
        $transaction = Transaction::send([
            'toBitcoinAddress' => $address,
            'bitcoinAmount'    => $amount,
            // 'description'      => 'Your first bitcoin!',
            // 'fee'              => '0.0001' // only required for transactions under BTC0.0001
        ]);


        $this->_client->createAccountTransaction($account, $transaction);
        $data = $this->_client->decodeLastResponse();
        // $account->completeTransaction($transaction);
        return $data;
    }
};
?>
