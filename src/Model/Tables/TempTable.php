<?php 

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisInstaller\Model\Tables;

class TempTable extends MelisGenericTable
{
    /**
     * Model table
     */
    const TABLE = 'changelog';

    /**
     * Table primary key
     */
    const PRIMARY_KEY = 'change_numberPrimary';

    public function __construct()
    {
        $this->idField = self::PRIMARY_KEY;
    }

    /*
     * Fetching Email details By LangID
     * */
    /*public function getEmailDetailsByEmailId($emailId, $langId = null){
        $select = $this->tableGateway->getSql()->select();
        
        $select->where('boed_email_id ='.$emailId);
        if($langId != null){
            $select->where('boed_lang_id ='.$langId);
        }
        
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet;
    }*/
}