<?php

require_once __DIR__ . '/../common/counselor.data.php';

class CounselorClientsModel
{
    public static function getAll(int $counselorId): array
    {
        return CounselorData::getClientsByCounselor($counselorId);
    }
    public static function searchClientsByName(string $query,int $counselorId){
        $clients= CounselorData::getClientsByCounselor($counselorId);
        $filteredClients=[];
        foreach($clients as $client){
            if(stripos($client['name'],$query)!==false){
                $filteredClients[]=$client;
            }
        }
        $clients=$filteredClients;
        return $clients;

    }
}
