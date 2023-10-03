<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Response;

use App\Entity\BranchSmsEntity;
use App\Entity\BranchEntity;
use App\Entity\ClientMeterEntity;
use App\Entity\ClientMeterReadingEntity;
use App\Entity\SmsEntity;


use App\Service\AuthService;

/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/generate_pending_payment_sms", name="api_generate_pending_payment_sms")
     */
    public function generate_pending_payment_sms(Request $request, AuthService $authService)
    {

       $result = [ 'success' => true, 'msg' => ''];
       
       $em = $this->getDoctrine()->getManager();
       $clientMeters = $em->getRepository(ClientMeterReadingEntity::class)->get_pending_payment();
      
        foreach($clientMeters as $clientMeter){

            $sms = $em->getRepository(SmsEntity::class)->findOneBy(array('smsType' => 'Pending - Remaining Balance', 'company' => $clientMeter['companyId']));

            if($sms){
                $smsMesg  = $sms->getMessage();
                $msg = str_replace("[month]",$clientMeter['readingDate'], str_replace("[amount]",$clientMeter['totalBalance'], str_replace("[client]",$clientMeter['fullName'],$smsMesg)));
                
                $a = $em->getRepository(BranchSmsEntity::class)->getBranchSmsByClientMeter($clientMeter);
                
                if($a == 0){
    
                    $branchSmsEntity = new BranchSmsEntity;
                    $branchSmsEntity->setBranch($em->getReference(BranchEntity::class, $clientMeter['branchId']));
                    $branchSmsEntity->setClientMeter($em->getReference(ClientMeterEntity::class, $clientMeter['id']));
                    $branchSmsEntity->setSms($sms);
                    $branchSmsEntity->setMessage($msg);
                    $branchSmsEntity->setStatus($this->validatePhoneNo($clientMeter['contactNo']) ? 'New' : 'Invalid Contact No');
                    $em->persist($branchSmsEntity);
                    $em->flush();
                }
            }
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/get_for_sent_sms", name="api_get_for_sent_sms")
     */
    public function get_for_sent_sms(Request $request, AuthService $authService)
    {

       $result = [ ];
       $em = $this->getDoctrine()->getManager();
       $bs = $em->getRepository(BranchSmsEntity::class)->findOneBy([
            'status' => [
                'New',
                'Sending'
            ]
       ]);
      
       if($bs){
            $result = [ 
                'id' => $bs->getId(),
                'contact_no' => $bs->getClientMeter()->getClient()->getContactNo(),
                'message' => $bs->getMessage(),
                'sent_ctr' => $bs->getSentCtr() ? $bs->getSentCtr() : 0 
            ];
            

        }
       
       return new JsonResponse($result);
    }

    /**
     * @Route("/update_sms/{id}/{status}/{sent_ctr}", name="api_update_sms")
     */
    public function update_sms(Request $request, AuthService $authService, $id, $status, $sent_ctr)
    {

       $result = [ 'success' => true, 'msg' => ''];
       $em = $this->getDoctrine()->getManager();
       $bs = $em->getRepository(BranchSmsEntity::class)->find($id);
      
       if($bs){


            if($status == 'Sending' && $sent_ctr >= 5){
                $status = 'Invalid Contact No';
            }

            $bs->setSentCtr($sent_ctr);
            $bs->setStatus($status);
            $bs->setSendAt(new \DateTime( date('Y-m-d H:i:s') ));
            $em->flush();
       }
       
       return new JsonResponse($result);
    }

    private function validatePhoneNo($phoneNo){
        
        $isVAlid = false;
        if(preg_match("/^(09|\+639)\d{9}$/", $phoneNo)) {
            $isVAlid = true;
        }
        
        return $isVAlid;
        
    }
    
}
