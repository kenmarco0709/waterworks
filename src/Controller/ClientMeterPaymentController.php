<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\ImagickEscposImage;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;


use App\Service\AuthService;
use App\Service\TransactionService;

use Knp\Snappy\Pdf;
use App\Entity\BranchEntity;

use App\Entity\BranchVariableEntity;
use App\Entity\ClientMeterPaymentEntity;
use App\Entity\ClientMeterEntity;
use App\Entity\ClientMeterReadingEntity;
use App\Form\ClientMeterPaymentForm;
use App\Entity\AuditTrailEntity;
use App\Entity\UserEntity;



/**
 * @Route("/client_meter_payment")
 */
class ClientMeterPaymentController extends AbstractController
{

 
 /**
     * @Route("/ajax_form", name="client_meter_payment_ajax_form")
     */
    public function ajaxForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];
       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $em = $this->getDoctrine()->getManager();
       $formData = $request->request->all();

       $clientMeter = $em->getRepository(ClientMeterEntity::class)->find(base64_decode($formData['clientMeterId']));
       $client = $em->getRepository(ClientMeterPaymentEntity::class)->find(base64_decode($formData['id']));

       $transactionNo = $em->getRepository(ClientMeterPaymentEntity::class)->getNextTransactionNo($this->get('session')->get('userData'));

       if(!$client) {
          $client = new ClientMeterPaymentEntity();
       }

       $formOptions = array('action' => $formData['action'] , 'clientMeterId' => $formData['clientMeterId'], 'transactionNo' => $transactionNo);
       $form = $this->createForm(ClientMeterPaymentForm::class, $client, $formOptions);
       $lastReading = $em->getRepository(ClientMeterReadingEntity::class)->findOneBy([ 'isDeleted' => false, 'clientMeter' => $clientMeter->getId()],['id' => 'desc']);

       $result['html'] = $this->renderView('ClientMeterPayment/ajax_form.html.twig', [
            'page_title' => 'New Meter Payment',
            'lastReading' => $lastReading,
            'form' => $form->createView(),
            'action' => $formData['action']
        ]);

       return new JsonResponse($result);
    }

    /**
     * @Route("/ajax_form_process", name="client_meter_payment_ajax_form_process")
     */
    public function ajaxFormProcess(Request $request, AuthService $authService, TransactionService $transactionService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){
         $clientForm = $request->request->get('client_meter_payment_form');
         
         $em = $this->getDoctrine()->getManager();
         $clientMeter = $em->getRepository(ClientMeterEntity::class)->find(base64_decode($clientForm['clientMeter']));

         $errors = $em->getRepository(ClientMeterPaymentEntity::class)->validate($clientForm);
         if(!count($errors)){
            
            $clientMeterPayment = $em->getRepository(ClientMeterPaymentEntity::class)->find($clientForm['id']);
            
            if(!$clientMeterPayment) {
               $clientMeterPayment = new ClientMeterPaymentEntity();
            }

            $formOptions = array('action' => $clientForm['action'] , 'clientMeterId' => $clientForm['clientMeter']);
            $form = $this->createForm(ClientMeterPaymentForm::class, $clientMeterPayment, $formOptions);
          

            switch($clientForm['action']) {
               case 'n': // new

                     $form->handleRequest($request);
      
                     if ($form->isValid()) {
                        
                        
                        $em->persist($clientMeterPayment);
                        $em->flush();

                        $transactionService->processTransaction($clientMeter);
                        

                        // try{
                          
                        //    $connector = new WindowsPrintConnector("XP-58C");

                        //   // $connector = new WindowsPrintConnector("XP-58C");
                        //    $printer = new Printer($connector);
           
                        //    $printer->feed();
                        //    $printer->feed();
                        //    $printer->feed();
                        //    $printer->setJustification(Printer::JUSTIFY_CENTER);
                        //    $printer->text($clientMeterPayment->getClientMeter()->getClient()->getBranch()->getDescription());
                        //    $printer->feed();
                        //    $printer->text('Waterworks');
                        //    $printer->feed();
                        //    $printer->feed();
                        //    $printer->feed();
                        //    $printer->setJustification(Printer::JUSTIFY_LEFT);
                        //    $printer->text('Name: '. $clientMeterPayment->getClientMeter()->getClient()->getFullName());
                        //    $printer->feed();
                        //    $printer->text('Meter: '. $clientMeterPayment->getClientMeter()->getMeterSerialNo());
                        //    $printer->feed();
                        //    $printer->text('Date: '. date( 'm/d/Y', strtotime($clientMeterPayment->getPaymentDate()->format('Y-m-d H:i:s'))));
                        //    $printer->feed();
                        //    $printer->feed();
                        //    $printer->feed();
                        //    $printer->text('Amount: '. number_format($clientMeterPayment->getAmount(),2, '.', ','));
                        //    $printer->feed();
                        //    $printer->text('Remaining Balance: '. number_format($clientMeterPayment->getClientMeter()->getRemainingBalance(),2, '.', ','));
                        //    $printer->feed();
                        //    $printer->feed();
                        //    $printer->feed();
                        //    $printer->feed();
                        //    $printer->feed();
                        //    $printer->feed();
                        //    $printer -> close();

                        // } catch(Exception $e) {
                        //    echo 'Message: ' .$e->getMessage();
                        //  }
                        $em->refresh($clientMeter);
                        $payment = $em->getRepository(ClientMeterPaymentEntity::class)->find($clientMeterPayment->getId());
                        $cashier = $em->getRepository(UserEntity::class)->findOneBy(['username' => $payment->getCreatedBy()]);
                  
                        $result['html'] = $this->renderView('ClientMeterPayment/direct_print_receipt.wkpdf.twig', array(
                            'payment' => $payment,
                            'cashier' => $cashier->getFullName()
                  
                        ));
   
                        $result['msg'] = 'Meter Payment successfully added to record.';
                     } else {
      
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'u': // update
                     
                     $form->handleRequest($request);

                     if ($form->isValid()) {

                        $em->flush();

                        $transactionService->processTransaction($clientMeter);
                        
                        $result['msg'] = 'Meter Payment successfully updated.';
                     } else {
                       
                        $result['error'] = 'Ooops something went wrong please try again later.';
                     }
      
                     break;
      
               case 'd': // delete

                     $form->handleRequest($request);
                     if ($form->isValid()) {
                       
                        $clientMeterPayment->setIsDeleted(true);
                        $em->flush();
                                               
                        $transactionService->processTransaction($clientMeter);
                        $result['msg'] = 'Meter Payment successfully deleted.';
      
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

   
   

   /**
    * @Route("/ajax_list", name="client_meter_payment_ajax_list")
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
          $result = $this->getDoctrine()->getManager()->getRepository(ClientMeterPaymentEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
      }

      return new JsonResponse($result);
   }

      /**
     * @Route("/print/receipt/{id}", name = "client_meter_payment_print_receipt")
     */
    public function printReceipt(Request $request, AuthService $authService, Pdf $pdf, $id){

      ini_set('memory_limit', '2048M');



      $meterReading  = $this->getDoctrine()->getManager()->getRepository(ClientMeterReadingEntity::class)->find(base64_decode($id));

      $options = [
         'orientation' => 'portrait',
         'enable-javascript' => true,
         'javascript-delay' => 1000,
         'no-stop-slow-scripts' => true,
         'no-background' => false,
         'lowquality' => false,
         'page-width' => '80mm',
         'page-height' => '10cm',
         'margin-left'=>0,
         'margin-right'=>0,
         'margin-top'=>0,
         'margin-bottom'=>0,
         'encoding' => 'utf-8',
         'images' => true,
         'cookie' => array(),
         'dpi' => 300,
         'enable-external-links' => true,
         'enable-internal-links' => true,
          'margin-top'    => 5,
          'margin-bottom' => 5,
      ];
      $em = $this->getDoctrine()->getManager();
      $payment = $em->getRepository(ClientMeterPaymentEntity::class)->find(base64_decode($id));
      $cashier = $em->getRepository(UserEntity::class)->findOneBy(['username' => $payment->getCreatedBy()]);

      $newContent = $this->renderView('ClientMeterPayment/print_receipt.wkpdf.twig', array(
          'payment' => $payment,
          'cashier' => $cashier->getFullName()

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
          'Content-Disposition'   => 'attachment; filename="'.  $payment->getTransactionNo() .'-' . date('m/d/Y') . '.pdf"'
      ));
  }

}