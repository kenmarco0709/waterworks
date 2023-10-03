<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Response;

use App\Service\AuthService;

use App\Entity\BranchEntity;
use App\Entity\BranchVariableEntity;
use App\Entity\ClientMeterEntity;
use App\Entity\ClientMeterReadingEntity;
use App\Entity\PurokEntity;
use App\Form\ClientMeterForm;
use App\Entity\AuditTrailEntity;
use App\Entity\UserEntity;



/**
 * @Route("/client_meter")
 */
class ClientMeterController extends AbstractController
{

   /**
    * @Route("/details/{id}", name="client_meter_details")
    */
    public function details($id,Request $request, AuthService $authService)
    {
       
       if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
       if(!$authService->isUserHasAccesses(array('Client Details Meter Details'))) return $authService->redirectToHome();
      

       $clientMeter  = $this->getDoctrine()->getManager()->getRepository(ClientMeterEntity::class)->find(base64_decode($id)); 
       $page_title = ' Meter Details'; 
       return $this->render('ClientMeter/details.html.twig', [ 
          'page_title' => $page_title,
          'clientMeter' => $clientMeter, 
          'javascripts' => array('/plugins/datatables/jquery.dataTables.js','/js/client_meter/details.js') 
         ]);
   }

 /**
     * @Route("/ajax_form", name="client_meter_ajax_form")
     */
    public function ajaxForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];
       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $em = $this->getDoctrine()->getManager();
       $formData = $request->request->all();
       $client = $em->getRepository(ClientMeterEntity::class)->find(base64_decode($formData['id']));
       
       if(!$client) {
          $client = new ClientMeterEntity();
       }

       $formOptions = array('action' => $formData['action'] , 'clientId' => $formData['clientId']);
       $form = $this->createForm(ClientMeterForm::class, $client, $formOptions);
    
       $result['html'] = $this->renderView('ClientMeter/ajax_form.html.twig', [
            'page_title' => 'New Meter',
            'form' => $form->createView(),
            'action' => $formData['action']
        ]);

       return new JsonResponse($result);
    }

    /**
     * @Route("/ajax_form_process", name="client_meter_ajax_form_process")
     */
    public function ajaxFormProcess(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){
         $clientForm = $request->request->get('client_meter_form');
         
         $em = $this->getDoctrine()->getManager();

         $errors = $em->getRepository(ClientMeterEntity::class)->validate($clientForm);
         if(!count($errors)){
            
            $clientMeter = $em->getRepository(ClientMeterEntity::class)->find($clientForm['id']);
            
            if(!$clientMeter) {
               $clientMeter = new ClientMeterEntity();
            }
     
            $formOptions = array('action' => $clientForm['action'] , 'clientId' => $clientForm['client']);
            $form = $this->createForm(ClientMeterForm::class, $clientMeter, $formOptions);


            switch($clientForm['action']) {
               case 'n': // new

                     $form->handleRequest($request);
      
                     if ($form->isValid()) {
                        
                        $em->persist($clientMeter);
                        $em->flush();

                        $clientMeter->setfinalBalance($clientMeter->getOldBalance());

                        $em->flush();
   
                        $result['msg'] = 'Meter successfully added to record.';
                     } else {
      
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'u': // update

                     $form->handleRequest($request);
                     if ($form->isValid()) {
      
                        $em->persist($clientMeter);
                        $em->flush();
                        $result['msg'] = 'Meter successfully updated.';
                     } else {
                       
                        $result['error'] = 'Ooops2 something went wrong please try again later.';
                     }
      
                     break;
      
               case 'd': // delete
                     $form->handleRequest($request);
                     if ($form->isValid()) {
                          
                        $clientMeter->setIsDeleted(true);
                        $em->flush();
      
                        $result['msg'] = 'Meter successfully deleted.';
      
                     } else {
      
                        $result['error'] = 'Ooops 3something went wrong please try again later.';
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

   /**
    * @Route("/ajax_list", name="client_meter_ajax_list")
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
          $result = $this->getDoctrine()->getManager()->getRepository(ClientMeterEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
      }

      return new JsonResponse($result);
   }

   /**
     * @Route("/ajax_details", name="client_meter_ajax_details")
     */
    public function ajaxDetails(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];
       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $em = $this->getDoctrine()->getManager();
       $r = $request->query->get('clientMeterId');

       $clientMeter = $em->getRepository(ClientMeterEntity::class)->find(base64_decode($r));
       $lastReading = $em->getRepository(ClientMeterReadingEntity::class)->findOneBy([ 'isDeleted' => false, 'clientMeter' => $clientMeter->getId()],['id' => 'desc']);

    
       $result['html'] = $this->renderView('ClientMeter/ajax_detail.html.twig', [
            'clientMeter'=> $clientMeter,
            'lastReading' => $lastReading
        ]);

       return new JsonResponse($result);
   }

      /**
     * @Route("/print/temporary_receipt/{id}", name = "client_meter_print_temporary_receipt")
     */
    public function printTemporaryReceipt(Request $request, AuthService $authService, Pdf $pdf, $id){

      ini_set('memory_limit', '2048M');

      $meterReading  = $this->getDoctrine()->getManager()->getRepository(ClientMeterReadingEntity::class)->find(base64_decode($id));

      $options = [
          'orientation' => 'portrait',
          'print-media-type' =>  True,
          'zoom' => .7,
          'margin-top'    => 5,
          'margin-right'  => 5,
          'margin-bottom' => 5,
          'margin-left'   => 5,
      ];
      $em = $this->getDoctrine()->getManager();

      $pricePerCubic = $em->getRepository(BranchVariableEntity::class)->findOneBy(array('description' =>  $meterReading->getClientMeter()->getConnectionType(). ' - Price Per Cubic', 'branch' => $meterReading->getClientMeter()->getClient()->getBranch()->getId()));
      $maximumConsumeBeforeMinimum = $em->getRepository(BranchVariableEntity::class)->findOneBy(array('description' =>  'Maximum Consume Before Minimum', 'branch'=> $meterReading->getClientMeter()->getClient()->getBranch()->getId()));
      $minimumBilledAmount = $em->getRepository(BranchVariableEntity::class)->findOneBy(array('description' =>  'Minimum Billed Amount',  'branch' => $meterReading->getClientMeter()->getClient()->getBranch()->getId()));
      $firstReadingWithRemainingBalance = $em->getRepository(ClientMeterReadingEntity::class)->findOneBy([ 'isDeleted' => false, 'clientMeter' => $meterReading->getClientMeter()->getId(), 'status' => 'Pending Payment'],['id' => 'asc']);

      $newContent = $this->renderView('ClientMeter/print_temporary_receipt.wkpdf.twig', array(
          'meterReading' => $meterReading,
          'pricePerCubic' => $pricePerCubic,
          'maximumConsumeBeforeMinimum' =>  $maximumConsumeBeforeMinimum,
          'minimumBilledAmount' =>  $minimumBilledAmount,
          'firstReadingWithRemainingBalance' => $firstReadingWithRemainingBalance

      ));

      $xml = $pdf->getOutputFromHtml($newContent,$options);
      $pdfResponse = array(
          'success' => true,
          'msg' => 'PDF was successfully generated.', 
          'pdfBase64' => base64_encode($xml)
      );
     
      $pdfContent = $pdfResponse['pdfBase64'];
       return new Response(base64_decode($pdfContent), 200, array(
          'Content-Type' => 'application/pdf',
          'Content-Disposition'   => 'attachment; filename="'.  'receipt' .'-' . date('m/d/Y') . '.pdf"'
      ));
  }

   /**
    * @Route("/ajax_for_reading_list", name="client_meter_ajax_for_reading_list")
    */
    public function ajax_for_reading_listAction(Request $request, AuthService $authService) {

      $get = $request->query->all();

      $result = array(
          "draw" => intval($get['draw']),
          "recordsTotal" => 0,
          "recordsFiltered" => 0,
          "data" => array()
      );

      if($authService->isLoggedIn()) {
         $result = $this->getDoctrine()->getManager()->getRepository(ClientMeterEntity::class)->ajax_for_reading_list($get, $this->get('session')->get('userData'));
      }

      return new JsonResponse($result);
   }

   
    /**
    * @Route("/ajax_pending_payment_list", name="client_meter_ajax_pending_payment_list")
    */
    public function ajax_pending_listAction(Request $request, AuthService $authService) {

      $get = $request->query->all();

      $result = array(
          "draw" => intval($get['draw']),
          "recordsTotal" => 0,
          "recordsFiltered" => 0,
          "data" => array()
      );

      if($authService->isLoggedIn()) {
          $result = $this->getDoctrine()->getManager()->getRepository(ClientMeterEntity::class)->ajax_pending_payment_list($get, $this->get('session')->get('userData'));
      }

      return new JsonResponse($result);
   }

      /**
     * @Route("/print/master_list/{purok}", name = "client_meter_print_master_list")
     */
    public function printMasterList(Request $request, AuthService $authService, Pdf $pdf, $purok){

      ini_set('memory_limit', '2048M');

      $options = [
          'orientation' => 'portrait',
          'print-media-type' =>  True,
          'zoom' => .7,
          'margin-top'    => 5,
          'margin-right'  => 5,
          'margin-bottom' => 5,
          'margin-left'   => 5,
      ];
      $em = $this->getDoctrine()->getManager();
      $masterLists = $em->getRepository(ClientMeterEntity::class)->master_list($purok, $this->get('session')->get('userData'));
      $purok = $em->getRepository(PurokEntity::class)->find(base64_decode($purok));
     
      $newContent = $this->renderView('ClientMeter/print_master_list.wkpdf.twig', array(
         'masterLists' => $masterLists,
         'user' => $authService->getUser(),
         'purok' => $purok
      ));

      $xml = $pdf->getOutputFromHtml($newContent,$options);
      $pdfResponse = array(
          'success' => true,
          'msg' => 'PDF was successfully generated.', 
          'pdfBase64' => base64_encode($xml)
      );
     
      $pdfContent = $pdfResponse['pdfBase64'];
       return new Response(base64_decode($pdfContent), 200, array(
          'Content-Type' => 'application/pdf',
          'Content-Disposition'   => 'attachment; filename="'.  'master_list' .'-' . date('m/d/Y') . '.pdf"'
      ));
  }
}