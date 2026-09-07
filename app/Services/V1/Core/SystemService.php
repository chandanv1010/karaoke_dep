<?php

namespace App\Services\V1\Core;

use App\Repositories\Core\SystemRepository;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\FrontendController;

/**
 * Class SystemService
 * @package App\Services
 */
class SystemService
{
    protected $systemRepository;
    

    public function __construct(
        SystemRepository $systemRepository
    ){
        $this->systemRepository = $systemRepository;
    }

    
    public function save($request, $languageId){
        DB::beginTransaction();
        try{

            $config = $request->input('config');
            $payload = [];
            if(count($config)){
                foreach($config as $key => $val){
                    $payload = [
                        'keyword' => $key,
                        'content' => $val,
                        'language_id' => $languageId,
                        'user_id' => Auth::id(),
                    ];
                    $condition = ['keyword' => $key, 'language_id' => $languageId];
                    $this->systemRepository->updateOrInsert($payload, $condition);
                }
            }
            
           
            DB::commit();

            // Frontend cache bang systems (xem FrontendController::setSystem).
            // Khong xoa thi admin luu xong ma trang ngoai van hien cau hinh cu.
            // Boc try/catch: neu cache loi thi da luu DB thanh cong roi, khong
            // duoc de viec xoa cache lam that bai ca thao tac luu.
            try {
                Cache::forget(FrontendController::systemCacheKey((int) $languageId));
            } catch (\Throwable $e) {
                report($e);
            }

            return true;
        }catch(\Exception $e ){
            DB::rollBack();
            // Log::error($e->getMessage());
            echo $e->getMessage();die();
            return false;
        }
    }

    

}
