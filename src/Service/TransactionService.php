<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\DBAL\Connection;

use App\Entity\ClientMeterReading;

Class TransactionService {

    private $em;
    private $container;
    private $conn; 
    public function __construct(EntityManagerInterface $em, ContainerInterface $container, Connection $connection) {

        $this->em = $em;
        $this->container = $container;
        $this->conn  = $connection;
    }

    public function processTransaction($clientMeter){


        $query = 'SELECT id, billed_amount, client_meter_id  FROM client_meter_reading WHERE client_meter_id =' .$clientMeter->getId() . ' AND is_deleted = 0';
        $query = $this->conn->prepare($query);
        $r= $query->executeQuery();
        $readings = $r->fetchAllAssociative();

        $totalPayment = floatval($this->clientMeterPaymentTotal($clientMeter));
        $remainingFromLastBilled = $clientMeter->getOldBalance() ? floatval($clientMeter->getOldBalance()) : 0;
        $finalBalance = $clientMeter->getFinalBalance() ? floatval($clientMeter->getFinalBalance()) : 0;
        
        $totalPayment-= $remainingFromLastBilled;
        if($totalPayment >= $remainingFromLastBilled){

            $remainingFromLastBilled = 0;
            $finalBalance = 0;
        } else {

            $remainingFromLastBilled = abs($totalPayment);
            $finalBalance = abs($totalPayment);
        }

        if(count($readings)){

            foreach ($readings as $k =>  $reading) {
                
                $totalPayment -= $reading['billed_amount'];  
                if($totalPayment >= 0 ){
                    $status = 'Paid';
                        $remainingFromLastBilled = 0;
                        $finalBalance = 0;

                } else {
                    $status = 'Pending Payment';
                    if($k !== array_key_last($readings)){
                        $remainingFromLastBilled = abs($totalPayment);
                    }

                    $finalBalance = abs($totalPayment);

                } 
                $sql = 'Update client_meter_reading SET status = "'.$status.'" WHERE id=' . $reading['id'];
                $sql = $this->conn->prepare($sql);
                $sql->executeQuery();
            }

            $sql = 'Update client_meter SET remaining_balance = '.$remainingFromLastBilled.', final_balance = '.$finalBalance.' WHERE id=' .  $clientMeter->getId();
            $sql = $this->conn->prepare($sql);
            $sql->executeQuery();          
        }
    }

    private function clientMeterPaymentTotal($clientMeter)
    {

        $query = 'SELECT SUM(amount) AS totalAmt FROM client_meter_payment WHERE client_meter_id =' .$clientMeter->getId() . ' AND is_deleted != "1" GROUP BY client_meter_id'  ;
        $query = $this->conn->prepare($query);
        $r= $query->executeQuery();
        $readings = $r->fetchAllAssociative();

        return $readings ? $readings[0]['totalAmt'] : 0;
    }
}