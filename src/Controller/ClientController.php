<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

use App\Service\AuthService;

use App\Entity\BranchEntity;

use App\Entity\ClientEntity;
use App\Form\ClientForm;
use App\Entity\AuditTrailEntity;
use App\Entity\UserEntity;
use App\Entity\PurokEntity;
use App\Entity\ClientMeterEntity;


/**
 * @Route("/client")
 */
class ClientController extends AbstractController
{
    /**
     * @Route("/ajax_form", name="client_ajax_form")
     */
    public function ajaxForm(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];
       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}
       
       $em = $this->getDoctrine()->getManager();
       $formData = $request->request->all();
       $client = $em->getRepository(ClientEntity::class)->find(base64_decode($formData['id']));
       
       if(!$client) {
          $client = new ClientEntity();
       }

       $formOptions = array('action' => $formData['action'] , 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
       $form = $this->createForm(ClientForm::class, $client, $formOptions);
    
       $result['html'] = $this->renderView('Client/ajax_form.html.twig', [
            'page_title' => 'New Client',
            'form' => $form->createView(),
            'action' => $formData['action']
        ]);

       return new JsonResponse($result);
    }

    /**
     * @Route("/ajax_form_process", name="client_ajax_form_process")
     */
    public function ajaxFormProcess(Request $request, AuthService $authService): JsonResponse
    {
       
       $result = [ 'success' =>  true, 'msg' => ''];

       if(!$authService->isLoggedIn()) { $result['success'] = false; $result['msg'] = 'Unauthorized access please call a system administrator.';}

       if($request->getMethod() == "POST"){
         $clientForm = $request->request->get('client_form');
         
         $em = $this->getDoctrine()->getManager();
         $errors = $em->getRepository(ClientEntity::class)->validate($clientForm);

         if(!count($errors)){
            
            $client = $em->getRepository(ClientEntity::class)->find($clientForm['id']);
            
            if(!$client) {
               $client = new ClientEntity();
            }
     
            $formOptions = array('action' => $clientForm['action'] , 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
            $form = $this->createForm(ClientForm::class, $client, $formOptions);


            switch($clientForm['action']) {
               case 'n': // new

                     $form->handleRequest($request);
      
                     if ($form->isValid()) {
                        
                        $em->persist($client);
                        $em->flush();
   
                        $result['msg'] = 'Client successfully added to record.';
                     } else {
      
                        $result['error'] = 'Ooops1 something went wrong please try again later.';
                     }
      
                     break;
      
               case 'u': // update

                     $form->handleRequest($request);
                     if ($form->isValid()) {
      
                        $em->persist($client);
                        $em->flush();
                        $result['msg'] = 'Client successfully updated.';
                     } else {
                       
                        $result['error'] = 'Ooops2 something went wrong please try again later.';
                     }
      
                     break;
      
               case 'd': // delete
                     $form->handleRequest($request);
                     if ($form->isValid()) {
                          
                        $client->setIsDeleted(true);
                        $em->flush();
      
                        $result['msg'] = 'Client successfully deleted.';
      
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
    * @Route("", name="client_index")
    */
   public function index(Request $request, AuthService $authService)
   {
      
      if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
      if(!$authService->isUserHasAccesses(array('Client'))) return $authService->redirectToHome();
      
      $page_title = ' Client'; 
      return $this->render('Client/index.html.twig', [ 
         'page_title' => $page_title, 
         'javascripts' => array('plugins/datatables/jquery.dataTables.js','/js/client/index.js') ]
      );
   }

   /**
    * @Route("/details/{id}", name="client_details")
    */
    public function details($id,Request $request, AuthService $authService)
    {
       
       if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
       if(!$authService->isUserHasAccesses(array('Client Details'))) return $authService->redirectToHome();
      

       $client  = $this->getDoctrine()->getManager()->getRepository(ClientEntity::class)->find(base64_decode($id)); 
       $page_title = ' Client Details'; 
       return $this->render('Client/details.html.twig', [ 
          'page_title' => $page_title,
          'client' => $client, 
          'javascripts' => array('/plugins/datatables/jquery.dataTables.js','/js/client/details.js') 
         ]);
   }


   /**
    * @Route("/autocomplete", name="client_autocomplete")
    */
   public function autocompleteAction(Request $request) {

      return new JsonResponse(array(
         'query' => 'clients',
         'suggestions' => $this->getDoctrine()->getManager()->getRepository(ClientEntity::class)->autocompleteSuggestions($request->query->all(), $this->get('session')->get('userData'))
      ));
   }

   /**
    * @Route("/ajax_list", name="client_ajax_list")
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
          $result = $this->getDoctrine()->getManager()->getRepository(ClientEntity::class)->ajax_list($get, $this->get('session')->get('userData'));
      }

      return new JsonResponse($result);
   }

   /**
   * @Route(
   *      "/form/{action}/{id}",
   *      defaults = {
   *          "action":  "n",
   *          "id": 0
   *      },
   *      requirements = {
   *          "action": "n|u"
   *      },
   *      name = "client_form"
   * )
   */
   public function formAction($action, $id, Request $request, AuthService $authService) {

   if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
   if(!$authService->getUser()->getType() == 'Super Admin') return $authService->redirectToHome();

   $em = $this->getDoctrine()->getManager();

   $client = $em->getRepository(ClientEntity::class)->find(base64_decode($id));
   if(!$client) {
      $client = new ClientEntity();
   }

   $formOptions = array('action' => 'n' , 'branchId' => $authService->getUser()->getBranch()->getIdEncoded());
   $form = $this->createForm(ClientForm::class, $client, $formOptions);

   if($request->getMethod() === 'POST') {

      $client_form = $request->get($form->getName());
      $result = $this->processForm($client_form, $client, $form, $request);

      if($result['success']) {
         if($result['redirect'] === 'index') {
               return $this->redirect($this->generateUrl('client_index'), 302);
         } else if($result['redirect'] === 'form') {
               return $this->redirect($this->generateUrl('client_form', array(
                  'action' => 'u',
                  'id' => base64_encode($result['id'])
               )), 302);
         } else if($result['redirect'] === 'form with error') {
               return $this->redirect($this->generateUrl('client_form'), 302);
         }
      } else {
         $form->submit($client_form, false);
      }
   }

   $title = ($action === 'n' ? 'New' : 'Update') . ' Client';

   return $this->render('Client/form.html.twig', array(
      'title' => $title,
      'page_title' => $title,
      'form' => $form->createView(),
      'action' => $action,
      'id' => $id
   ));
   }

   private function processForm($client_form, $client ,$form, Request $request) {

      $em = $this->getDoctrine()->getManager();

       $errors = $em->getRepository(ClientEntity::class)->validate($client_form);

      if(!count($errors)) {

         switch($client_form['action']) {
            case 'n': // new

                  $form->handleRequest($request);

                  if ($form->isValid()) {
                     
                     $em->persist($client);
                     $em->flush();

                     $newAT = new AuditTrailEntity();
                     $newAT->setUser($em->getReference(UserEntity::class, base64_decode($_COOKIE['userId'])));
                     $newAT->entity = $client;
                     $newAT->setRefTable('client');
                     $newAT->parseInformation('New');
                     $em->persist($newAT);
                     $em->flush();

                     $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Saved.');

                     $result['redirect'] = 'index';

                  } else {

                     $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                     $result['redirect'] = 'form with error';
                  }

                  break;

            case 'u': // update

                  $newAT = new AuditTrailEntity();
                  $newAT->setUser($em->getReference(UserEntity::class, base64_decode($_COOKIE['userId'])));
                  $newAT->entity = $client;
                  $newAT->setRefTable('client');
                  $newAT->parseOriginalDetails();

                  $form->handleRequest($request);

                  if ($form->isValid()) {

                     $em->persist($client);

                     

                     $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Updated.');

                     $result['redirect'] = 'index';

                  } else {

                     $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                     $result['redirect'] = 'form with error';
                  }

                  break;

            case 'd': // delete
                  $form->handleRequest($request);


                  if ($form->isValid()) {
                     
                     $newAT = new AuditTrailEntity();
                     $newAT->setUser($em->getReference(UserEntity::class, base64_decode($_COOKIE['userId'])));
                     $newAT->entity = $client;
                     $newAT->setRefTable('client');
                     $newAT->parseInformation('Delete');
                     $em->persist($newAT);

                     $client->setIsDeleted(true);
                     $em->flush();

                     $this->get('session')->getFlashBag()->set('success_messages', 'Record Successfully Deleted.');

                     $result['redirect'] = 'index';

                  } else {

                     $this->get('session')->getFlashBag()->set('error_messages', 'Something went wrong. Please try again.');
                     $result['redirect'] = 'form with error';
                  }

                  break;
         }

         $result['success'] = true;

      } else {

         foreach($errors as $error) {
            $this->get('session')->getFlashBag()->add('error_messages', $error);
         }

         $result['redirect'] = 'index';
         $result['success'] = false;
      }

         return $result;
   }

   
     /**
     * @Route("/import", name="client_import")
     */
    public function import(Request $request, AuthService $authService)
    {
        if(!$authService->isLoggedIn()) return $authService->redirectToLogin();
        if(!$authService->isUserHasAccesses(array('Client Import'))) return $authService->redirectToHome();

        if($request->getMethod() == 'POST'){

            ini_set('memory_limit', '2048M');
            set_time_limit(0);

            $file_mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            
            if(isset($_FILES['items']['name']) && in_array($_FILES['items']['type'], $file_mimes)) {

                $reader = new Csv();
                $spreadsheet = $reader->load($_FILES['items']['tmp_name']);
 
                $sheetData = $spreadsheet->getActiveSheet()->toArray();
                array_shift($sheetData);

                $em = $this->getDoctrine()->getManager();

                foreach($sheetData as $data){

                     $firstName = $data[0];
                     $lastName = $data[1];
                     $address = $data[2];
                     $contactNo = $data[3];
                     $email = $data[4];
                     $connectionType = $data[5];
                     $status = !empty($data[6]) ? $data[6] : 'Active';
                     $purok = $data[7];
                     $houseNo = $data[8] ? $data[8] : '';
                     $meterModel = $data[9] ? $data[9] : '';
                     $serialNo = $data[10];
                     $presentReading = $data[11];
                     $remainingBalance = $data[12];
                     $userData = $this->get('session')->get('userData');
                     
                     $client = $em->getRepository(ClientEntity::class)->getClientCtr($firstName, $lastName, $userData);
                     
                     if(!count($client)){
                        //Create Client
                        $client = new ClientEntity();
                        $client->setBranch($em->getReference(BranchEntity::class, base64_decode($userData['branchId'])));
                        $client->setFirstName($firstName);
                        $client->setLastName($lastName);
                        $client->setAddress($address);
                        $client->setContactNo($contactNo);
                        $em->persist($client);
                        $em->flush();
                        
                        //Create Client Account
                        $purok = $em->getRepository(PurokEntity::class)->findOneBy(array('isDeleted' => 0, 'description' => $purok, 'branch'=> base64_decode($userData['branchId'])));
                        $clientAccount = new ClientMeterEntity();
                        $clientAccount->setClient($client);
                        $clientAccount->setConnectionType($connectionType); 
                        $clientAccount->setHouseNo($houseNo); 
                        $clientAccount->setPurok($purok);
                        $clientAccount->setOldBalance($remainingBalance);
                        $clientAccount->setFinalBalance($remainingBalance);
                        $clientAccount->setMeterSerialNo(empty($serialNo) ? ($firstName . $lastName) : $serialNo);
                        $clientAccount->setMeterModel($meterModel);
                        $clientAccount->setStatus($status);
                        $clientAccount->setPresentReading(empty($presentReading) ? 0 : $presentReading);
                        $em->persist($clientAccount);
                        $em->flush();
                     }
                }
                $this->get('session')->getFlashBag()->set('success_messages', 'Client  successfully import.');

            } else {

                $this->get('session')->getFlashBag()->set('error_messages', 'Please put a valid CSV file.');

            }

        } else {

            $this->get('session')->getFlashBag()->set('error_messages', 'Unauthorized request please call a system administrator.');

        }


       return $this->redirect($this->generateUrl('client_index'),302);
   }

}
