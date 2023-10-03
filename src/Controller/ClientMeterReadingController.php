<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Service\AuthService;
use App\Service\TransactionService;


use App\Entity\BranchEntity;

use App\Entity\BranchVariableEntity;
use App\Entity\ClientMeterReadingEntity;
use App\Entity\ClientMeterEntity;

use App\Form\ClientMeterReadingForm;
use App\Entity\AuditTrailEntity;
use App\Entity\UserEntity;



/**
 * @Route("/client_meter_reading")
 */
class ClientMeterReadingController extends AbstractController
{

   /**
    * @Route("/details/{id}", name="client_meter_reading_details")
    */
    public function details($id,Request $request, AuthService $authService)
    {
       
       if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
       if(!$authService->isUserHasAccesses(array('Client Details Meter Details'))) return $authService->redirectToHome();
      

       $clientMeter  = $this->getDoctrine()->getManager()->getRepository(ClientMeterReadingEntity::class)->find(base64_decode($id)); 
       $page_title = ' Meter Details'; 
       return $this->render('ClientMeterReading/details.html.twig', [ 
          'page_title' => $page_title,
          'clientMeter' => $clientMeter, 
          'javascripts' => array('/plugins/datatables/jquery.dataTables.js','/js/client/details.js') 
         ]);
   }

 /**
     * @Route("/ajax_form", name="client_meter_reading_ajax_form")
     */
    public function ajaxForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];
       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $em = $this->getDoctrine()->getManager();
       $formData = $request->request->all();

       $clientMeter = $em->getRepository(ClientMeterEntity::class)->find(base64_decode($formData['clientMeterId']));
       $client = $em->getRepository(ClientMeterReadingEntity::class)->find(base64_decode($formData['id']));
       
       if(!$client) {
          $client = new ClientMeterReadingEntity();
       }

       $formOptions = array('action' => $formData['action'] , 'clientMeterId' => $formData['clientMeterId'], 'previousReading' => $formData['action'] == 'n' ?  $clientMeter->getPresentReading() :  $clientMeter->getPreviousReading());
       $form = $this->createForm(ClientMeterReadingForm::class, $client, $formOptions);
    
       $action = $formData['action'] == 'n' ? 'New' : 'Update';
       $result['html'] = $this->renderView('ClientMeterReading/ajax_form.html.twig', [
            'page_title' => $action .' Meter Reading',
            'clientMeter' => $clientMeter,
            'form' => $form->createView(),
            'action' => $formData['action']
        ]);

       return new JsonResponse($result);
    }

    /**
     * @Route("/ajax_form_process", name="client_meter_reading_ajax_form_process")
     */
    public function ajaxFormProcess(Request $request, AuthService $authService, TransactionService $transactionService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){
         $clientForm = $request->request->get('client_meter_reading_form');
         
         $em = $this->getDoctrine()->getManager();
         $clientMeter = $em->getRepository(ClientMeterEntity::class)->find(base64_decode($clientForm['clientMeter']));

         $errors = $em->getRepository(ClientMeterReadingEntity::class)->validate($clientForm);
         if(!count($errors)){
            
            $clientMeterReading = $em->getRepository(ClientMeterReadingEntity::class)->find($clientForm['id']);
            
            if(!$clientMeterReading) {
               $clientMeterReading = new ClientMeterReadingEntity();
            }

            $formOptions = array('action' => $clientForm['action'] , 'clientMeterId' => $clientForm['clientMeter'], 'previousReading' => $clientForm['action'] == 'n' ?  $clientMeter->getPresentReading() :  $clientMeter->getPreviousReading());
            $form = $this->createForm(ClientMeterReadingForm::class, $clientMeterReading, $formOptions);
          

            switch($clientForm['action']) {
               case 'n': // new

                     $form->handleRequest($request);
      
                     if ($form->isValid()) {
                        
                        $this->prepareDataForBilling($clientMeterReading, $em, 'n');
                        $clientMeterReading->setStatus('Pending Payment');
                        $em->persist($clientMeterReading);
                        $em->flush();

                  
                        $clientMeter->setPresentReading($clientMeterReading->getPresentReading());
                        $clientMeter->setPreviousReading($clientMeterReading->getPreviousReading());
                        $em->flush();


                        $transactionService->processTransaction($clientMeter);
   
                        $result['msg'] = 'Meter Reading successfully added to record.';
                     } else {
      
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'u': // update
                     
                     $form->handleRequest($request);

                     if ($form->isValid()) {

                        $clientMeterReading->setPreviousReading($clientMeter->getPreviousReading());
                        $this->prepareDataForBilling($clientMeterReading, $em, 'u');
                        $em->flush();

                        $clientMeter->setPresentReading($clientMeterReading->getPresentReading());
                        $em->flush();

                        $transactionService->processTransaction($clientMeter);
                        $result['msg'] = 'Meter Reading successfully updated.';
                     } else {
                       
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'd': // delete

                     $form->handleRequest($request);
                     if ($form->isValid()) {
                       
                        $clientMeterReading->setIsDeleted(true);
                        $em->flush();
                       
                        $lastReading = $em->getRepository(ClientMeterReadingEntity::class)->findOneBy([ 'isDeleted' => false, 'clientMeter' => $clientMeter->getId()],['id' => 'desc']);
                        if($lastReading){
                           $clientMeter->setPreviousReading($lastReading->getPreviousReading());
                           $clientMeter->setPresentReading($lastReading->getPresentReading());
                        } else {
                           $clientMeter->setPreviousReading(0);
                           $clientMeter->setPresentReading(0);
                        }
                        
                        $em->flush();   
                        
                        $transactionService->processTransaction($clientMeter);
                        $result['msg'] = 'Meter Reading successfully deleted.';
      
                     } else {
      
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
            }
        
         } else {

             $result['success'] = false;
             $result['msg'] = '';
             foreach ($errors as $error) {
                 
                 $result['msg'] .= $error;
             }
         }
     } else {

         $result['error'] = 'Ooops something went wrong please try again later.';
     }
    
       return new JsonResponse($result);
    }

   private function prepareDataForBilling($reading, $em, $action){

      $consume = $this->computeConsume($reading, $action);
      $pricePerCubic = $em->getRepository(BranchVariableEntity::class)->findOneBy(array('description' =>  $reading->getClientMeter()->getConnectionType(). ' - Price Per Cubic', 'branch' => $reading->getClientMeter()->getClient()->getBranch()->getId()));
      $maximumConsumeBeforeMinimum = $em->getRepository(BranchVariableEntity::class)->findOneBy(array('description' =>  'Maximum Consume Before Minimum', 'branch'=> $reading->getClientMeter()->getClient()->getBranch()->getId()));
      $minimumBilledAmount = $em->getRepository(BranchVariableEntity::class)->findOneBy(array('description' =>  'Minimum Billed Amount',  'branch' => $reading->getClientMeter()->getClient()->getBranch()->getId()));

      if($consume <=   $maximumConsumeBeforeMinimum->getBranchVariableValue()){
         $billedAmt = $minimumBilledAmount->getBranchVariableValue();
      } else {
         $billedAmt = $minimumBilledAmount->getBranchVariableValue() + (($consume - $maximumConsumeBeforeMinimum->getBranchVariableValue()) * $pricePerCubic->getBranchVariableValue());  
      }

      $reading->setAmountPerCubic($pricePerCubic->getBranchVariableValue());
      $reading->setConsume($consume);
      $reading->setBilledAmount($billedAmt);      
      $em->flush();
      
   }

   private function computeConsume($reading, $action){
      return $reading->getPresentReading() - ($action == 'n' ? $reading->getClientMeter()->getPresentReading() : $reading->getClientMeter()->getPreviousReading());
   }
   

   /**
    * @Route("/ajax_list", name="client_meter_reading_ajax_list")
    */
    public function ajax_listAction(Request $request, AuthService $authService) {

      $get = $request->query->all();

      $result = array(
          "draw" => intval($get['draw']),
          "recordsTotal" => 0,
          "recordsFiltered" => 0,
          "data" => array()
      );

      if($authService->isLoggedIn()) {
          $result = $this->getDoctrine()->getManager()->getRepository(ClientMeterReadingEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
      }

      return new JsonResponse($result);
   }
}