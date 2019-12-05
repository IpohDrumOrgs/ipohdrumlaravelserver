<?php

namespace App\Traits;
use App\User;
use App\Store;
use App\ProductPromotion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\LogServices;
use App\Traits\StoreServices;
use App\Traits\ImageHostingServices;

trait ProductPromotionServices {

    use GlobalFunctions, LogServices, StoreServices;

    private function getProductPromotions($requester) {

        $data = collect();

        //Role Based Retrieve Done in Store Services
        $stores = $this->getStores($requester);
        foreach($stores as $store){
            $data = $data->merge($store->promotions()->where('status',true)->get());
        }

        $data = $data->merge(ProductPromotion::where('store_id',null)->get());

        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }

    private function filterProductPromotions($data , $params) {


        if($params->keyword){
            error_log('Filtering productpromotions with keyword....');
            $keyword = $params->keyword;
            $data = $data->filter(function($item)use($keyword){
                //check string exist inside or not
                if(stristr($item->name, $keyword) == TRUE || stristr($item->uid, $keyword) == TRUE ) {
                    return true;
                }else{
                    return false;
                }

            });
        }


        if($params->fromdate){
            error_log('Filtering productpromotions with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering productpromotions with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->status){
            error_log('Filtering productpromotions with status....');
            if($params->status == 'true'){
                $data = $data->where('status', true);
            }else if($params->status == 'false'){
                $data = $data->where('status', false);
            }else{
                $data = $data->where('status', '!=', null);
            }
        }



        $data = $data->unique('id');

        return $data;
    }

    private function getProductPromotion($uid) {

        $data = ProductPromotion::where('uid', $uid)->where('status', true)->first();
        return $data;

    }
    //Make Sure ProductPromotion is not empty when calling this function
    private function createProductPromotion($params) {

        $params = $this->checkUndefinedProperty($params , $this->productPromotionAllCols());
        $data = new ProductPromotion();

        $data->uid = Carbon::now()->timestamp . ProductPromotion::count();
        $data->name = $params->name;
        $data->desc = $params->desc;
        $data->disc = $this->toDouble($params->disc);
        $data->discpctg = $this->toDouble($params->discpctg / 100);
        $data->qty = $this->toInt($params->qty);
        $data->discbyprice = $params->discbyprice;
        $data->promostartdate = $this->toDate($params->promostartdate);
        $data->promoenddate = $this->toDate($params->promoenddate);

        $store = Store::find($params->store_id);
        if($this->isEmpty($store)){
            return null;
        }
        $data->store()->associate($store);
        
        $data->status = true;

        if(!$this->saveModel($data)){
            return null;
        }
        
      
        return $data->refresh();
    }

    //Make Sure ProductPromotion is not empty when calling this function
    private function updateProductPromotion($data,  $params) {

        $params = $this->checkUndefinedProperty($params , $this->productPromotionAllCols());

        $data->name = $params->name;
        $data->desc = $params->desc;
        $data->disc = $this->toDouble($params->disc);
        $data->discpctg = $this->toDouble($params->discpctg / 100);
        $data->qty = $this->toInt($params->qty);
        $data->discbyprice = $params->discbyprice;
        $data->promostartdate = $this->toDate($params->promostartdate);
        $data->promoenddate = $this->toDate($params->promoenddate);

        $store = Store::find($params->store_id);
        if($this->isEmpty($store)){
            return null;
        }
        $data->store()->associate($store);
        
        $data->status = true;

        if(!$this->saveModel($data)){
            return null;
        }
        
      
        return $data->refresh();

    }

    private function deleteProductPromotion($data) {

        //Remove the relationship
        $inventories = $data->inventories;
        foreach($inventories as $inventory){
            $inventory->promotion()->dissociate();
            if($this->saveModel($inventory)){
                return null;
            }
        }
        $tickets = $data->tickets;
        foreach($tickets as $ticket){
            $ticket->promotion()->dissociate();
            if($this->saveModel($ticket)){
                return null;
            }
        }
        //Cancel promotion
        $data->status = false;
        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }
    }

    //Modifying Display Data
    // -----------------------------------------------------------------------------------------------------------------------------------------
    public function productPromotionAllCols() {

        return ['id','uid', 'store_id', 'name' ,'desc', 'qty', 'disc' , 'discpctg' , 'discbyprice' , 'promostartdate', 'promoenddate' , 'status' ];

    }
    
    


}
