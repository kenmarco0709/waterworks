<?php

namespace App\Repository;

use App\Entity\ClientEntity;

/**
 * ClientRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ClientRepository extends \Doctrine\ORM\EntityRepository
{

    public function validate($client_form) {

        $errors = array();

        $action = $client_form['action'];

        // d = delete
        if($action !== 'd') {
            $clientExist = $this->getEntityManager()->getRepository(ClientEntity::class)
                ->createQueryBuilder('u')
                ->where('u.id != :id')
                ->andWhere('u.firstName = :firstName')
                ->andWhere('u.lastName = :lastName')
                ->andWhere('u.branch = :branch')
                ->setParameters(array(
                    'id' => $client_form['id'],
                    'firstName' => $client_form['first_name'],
                    'lastName' => $client_form['last_name'],
                    'branch' => base64_decode($client_form['branch'])


                ))
                ->getQuery()->getResult();

            if($clientExist) {
                $errors[] = 'Client already exist.';
            }

            if(empty($client_form['first_name'])) {
                $errors[] = 'First name should not be blank.';
            }

            if(empty($client_form['last_name'])) {
                $errors[] = 'Last name should not be blank.';
            }
            
        }

        return $errors;
    }

    public function ajax_list(array $get, $userData){

        $columns = array(
            array("CONCAT(c.`first_name`, ' ', IFNULL(c.`last_name`, ' '))", "CONCAT(c.`first_name`, ' ', IFNULL(c.`last_name`, ' '))", 'fullName'),
            array('c.`address`', "c.`address`"),
            array('c.`contact_no`', "c.`contact_no`", " contactNo"),
            array('c.`email`', "c.`email`", " email"),
            array('c.`id`', "c.`id`")
        );
        $asColumns = array();

        $select = "SELECT";
        $from = "FROM `client` c";
        $sqlWhere = " WHERE c.`is_deleted` = 0";
        $joins = "";
        $groupBy = "";
        $orderBy = "";
        $limit = "";
        $stmtParams = array();

        foreach($columns as $key => $column) {
            $select .= ($key > 0 ? ', ' : ' ') . $column[1] . (isset($column[2]) ? ' AS ' . $column[2] : '');
        }


        if($userData['type'] != 'Super Admin' || $userData['branchId']){

            $sqlWhere .= " AND c.`branch_id` = :branchId";
            $stmtParams['branchId'] = base64_decode($userData['branchId']);
        }

        /*
         * Ordering
         */
        foreach($get['columns'] as $key => $column) {
            if($column['orderable']=='true') {
                if(isSet($get['order'])) {
                    foreach($get['order'] as $order) {
                        if($order['column']==$key) {
                            $orderBy .= (!empty($orderBy) ? ', ' : 'ORDER BY ') . $columns[$key][0] . (!empty($order['dir']) ? ' ' . $order['dir'] : '');
                        }
                    }
                }
            }
        }

        /*
         * Filtering
         */
        if(isset($get['search']) && $get['search']['value'] != ''){
            $aLikes = array();
            foreach($get['columns'] as $key => $column) {
                if($column['searchable']=='true') {
                    $aLikes[] = $columns[$key][0] . ' LIKE :searchValue';
                }
            }
            foreach($asColumns as $asColumn) {
                $aLikes[] = $asColumn . ' LIKE :searchValue';
            }
            if(count($aLikes)) {
                $sqlWhere .= (!empty($sqlWhere) ? ' AND ' : 'WHERE ') . '(' . implode(' OR ', $aLikes) . ')';
                $stmtParams['searchValue'] = "%" . $get['search']['value'] . "%";
            }
        }

        /* Set Limit and Length */
        if(isset( $get['start'] ) && $get['length'] != '-1'){
            $limit = 'LIMIT ' . (int)$get['start'] . ',' . (int)$get['length'];
        }

        $sql = "$select $from $joins $sqlWhere $groupBy $orderBy";
        $query = $this->getEntityManager()->getConnection()->prepare($sql);
     

        foreach($stmtParams as $k => $v){
            $query->bindValue($k, $v);

        }
        $res = $query->executeQuery();
        $result_count = $res->fetchAllAssociative();
        $sql = "$select $from $joins $sqlWhere $groupBy $orderBy $limit";
        $query = $this->getEntityManager()->getConnection()->prepare($sql);
        foreach($stmtParams as $k => $v){
            $query->bindValue($k, $v);

        }
        $res = $query->executeQuery();
        $result = $res->fetchAllAssociative();

        /* Data Count */
        $recordsTotal = count($result_count);

        /*
         * Output
         */
        $output = array(
            "draw" => intval($get['draw']),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsTotal,
            "data" => array()
        );

        $url = $get['url'];
        $formUrl = '';
        $hasUpdate = false;
        $hasDetails = false;
        if($userData['type'] == 'Super Admin'  || in_array('Client Update', $userData['accesses'])){
            $hasUpdate = true;
        }

        if($userData['type'] == 'Super Admin'  || in_array('Client Details', $userData['accesses'])){

            $detailsUrl = $url . 'client/details';  
            $hasDetails = true;
        }


        foreach($result as $row) {

            $id = base64_encode($row['id']);

            $action = $hasUpdate ? "<a class='action-button-style btn btn-primary  href-modal' href='javascript:void(0)' data-id='".$id."' data-action='u'>Update</a>" : "";
            $details = $hasDetails ? "<a class='action-button-style ' href='$detailsUrl/$id'>". $row['fullName']."</a>" : $row['fullName'];

            $values = array(
                $details,
                $row['address'],
                $row['contactNo'],
                $row['email'],
                $action
            );

            $output['data'][] = $values;
        }

        unset($result);

        return $output;
    }

    public function autocompleteSuggestions($q, $userData) {

        $stmtParams = array(
            'q' => "%" . $q['query'] . "%"
        );
        $andWhere = '';

        if($userData['type'] != 'Super Admin'){

             $andWhere.= ' WHERE u.branch_id = :branchId'; 
             $stmtParams['branchId'] = $userData['branchId'];   
        }

        
        $query = $this->getEntityManager()->getConnection()->prepare("
            SELECT
                u.`id`,
                CONCAT(u.`first_name`, ' ', u.`last_name`) AS data,
                CONCAT(u.`first_name`, ' ', u.`last_name`) AS value
            FROM `client` u
            $andWhere
            AND u.`is_deleted` != 1
            AND CONCAT(u.`first_name`, ' ', u.`last_name`) LIKE :q
            ORDER BY u.`first_name`
            LIMIT 0,20
        ");

        foreach($stmtParams as $k => $v){
            $query->bindValue($k, $v);

        }
       
        $res = $query->executeQuery();
        $result = $res->fetchAllAssociative();
        return $result;
    }


    public function getClientCtr($firstName, $lastName, array $userData){

        $result =[];

        $stmtParams =[];
        $andWhere = ' WHERE c.`first_name` = "' . $firstName . '" AND c.`last_name` = "' .$lastName. '"';

        if($userData['type'] != 'Super Admin'){

             $andWhere.= ' AND c.branch_id = :branchId'; 
             $stmtParams['branchId'] = base64_decode($userData['branchId']);   
        }
       
        $query = $this->getEntityManager()->getConnection()->prepare("
                SELECT
                    c.`id`
                from `client` c
                $andWhere
                AND c.`is_deleted` != 1 

        ");

       
        foreach($stmtParams as $k => $v){
            $query->bindValue($k, $v);

        }

        $res = $query->executeQuery();
        $result = $res->fetchAllAssociative();

        return $result;
    }

   
}
