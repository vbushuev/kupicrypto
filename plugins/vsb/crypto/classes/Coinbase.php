<?php namespace Vsb\Crypto\Classes;

use \Coinbase\Wallet\Client;
use \Coinbase\Wallet\Configuration;

use \Coinbase\Wallet\Resource\Transaction;
use \Coinbase\Wallet\Resource\Account;
use \Coinbase\Wallet\Resource\Address;
use \Coinbase\Wallet\Value\Money;
use \Coinbase\Wallet\Enum\CurrencyCode;
/*
API Key: gmWkAaXVi1ImmBDu
API Secret: 2boLOndVO6ccmjleAozDaIZrYZXOu8V3
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
    public function checkBalance($amt){
        $account = $this->_client->getPrimaryAccount();

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