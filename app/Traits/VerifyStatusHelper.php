<?php

namespace App\Traits;
use App\Enums\VerifyStatus;
use App\Enums\VerifyFileType;
trait VerifyStatusHelper
{
    public function getLaosStringStatus($statusName){
        if($statusName==VerifyStatus::APPROVED){
            return "ຖຶກຢືນຢັນແລ້ວ";
        }else if($statusName==VerifyStatus::REJECTED){
            return "ຖຶກປະຕິເສດ";
        }else if($statusName==VerifyStatus::PENDING){
            return "ຖຶກປ່ຽນສະຖານະເປັນກຳລັງລໍຖ້າ";
        }else{
            return "";
        }
    }
    public function getLaosStringFilesStatus($fileStatusName){
        if($fileStatusName==VerifyFileType::CENCUS){
            return "ສຳມະໂນ";
        }else if($fileStatusName==VerifyFileType::IDENTITY_CARD){
            return "ບັດປະຈຳໂຕ";
        }else if($fileStatusName==VerifyFileType::PASSPORT){
            return "ພາດສະປອດ";
        }else{
            return "";
        }
    }
}
