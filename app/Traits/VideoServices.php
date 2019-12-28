<?php

namespace App\Traits;
use App\User;
use App\Store;
use App\ProductPromotion;
use App\Video;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\AllServices;

trait VideoServices {

    use AllServices;

    private function getVideos($requester) {

        $data = collect();
        //Role Based Retrieve Done in Store
        $channels = $this->getChannels($requester);
        foreach($channels as $channel){
            $data = $data->merge($channel->videos()->where('status',true)->get());
        }

        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }


    private function filterVideos($data , $params) {

        $params = $this->checkUndefinedProperty($params , $this->videoFilterCols());
        error_log('Filtering videos....');

        if($params->keyword){
            error_log('Filtering videos with keyword....');
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
            error_log('Filtering videos with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering videos with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->status){
            error_log('Filtering videos with status....');
            if($params->status == 'true'){
                $data = $data->where('status', true);
            }else if($params->status == 'false'){
                $data = $data->where('status', false);
            }else{
                $data = $data->where('status', '!=', null);
            }
        }

        if($params->scope){
            error_log('Filtering videos with scope....');
            $data = $data->where('scope', $params->scope);
        }


        $data = $data->unique('id');

        return $data;
    }

    private function getVideo($uid) {
        $data = Video::where('uid', $uid)->with('channel', 'comments.secondcomments')->where('status', 1)->first();
        return $data;
    }

    private function getVideoById($id) {
        $data = Video::where('id', $id)->with('channel', 'comments.secondcomments')->where('status', 1)->first();
        return $data;
    }

    private function createVideo($params) {

        $params = $this->checkUndefinedProperty($params , $this->videoAllCols());

        $data = new Video();
        $data->uid = Carbon::now()->timestamp . Video::count();
        $data->name = $params->name;
        $data->code = $params->code;
        $data->sku = $params->sku;
        $data->desc = $params->desc;
        $data->price = $this->toDouble($params->price);
        $data->enddate = $this->toDate($params->enddate);
        $data->qty = $this->toInt($params->qty);
        $data->salesqty = 0;
        $data->stockthreshold = $this->toInt($params->stockthreshold);
        $data->onsale = $params->onsale;

        $store = $this->getStoreById($params->store_id);
        if($this->isEmpty($store)){
            return null;
        }
        $data->store()->associate($store);
           
        $promotion = $this->getProductPromotionById($params->product_promotion_id);
        if($this->isEmpty($promotion)){
            return null;
        }else{
            if($promotion->qty > 0){
                $data->promoendqty = $data->salesqty + $promotion->qty;
            }
        }

        $data->promotion()->associate($promotion);

        $data->status = true;
        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }

        return $data->refresh();
    }

    //Make Sure Video is not empty when calling this function
    private function updateVideo($data,  $params) {
        
        $params = $this->checkUndefinedProperty($params , $this->videoAllCols());

        $data->name = $params->name;
        $data->code = $params->code;
        $data->sku = $params->sku;
        $data->desc = $params->desc;
        $data->price = $this->toDouble($params->price);
        $data->enddate = $this->toDate($params->enddate);
        $data->qty = $this->toInt($params->qty);
        $data->salesqty = 0;
        $data->stockthreshold = $this->toInt($params->stockthreshold);
        $data->onsale = $params->onsale;

        $store = $this->getStoreById($params->store_id);
        if($this->isEmpty($store)){
            return null;
        }
        $data->store()->associate($store);
           
        $promotion = $this->getProductPromotionById($params->product_promotion_id);
        if($this->isEmpty($promotion)){
            return null;
        }else{
            if($promotion->qty > 0){
                $data->promoendqty = $data->salesqty + $promotion->qty;
            }
        }

        $data->promotion()->associate($promotion);

        $data->status = true;
        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }
        return $data->refresh();
    }

    private function deleteVideo($data) {
        $data->status = false;
        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }

        return $data->refresh();
    }

    private function getAllVideos() {
        
        $data = Video::where('status', true)->get();

        return $data;
    }

    // Modifying Display Data
    // -----------------------------------------------------------------------------------------------------------------------------------------
    public function videoAllCols() {

        return ['id','store_id', 'product_promotion_id', 'uid', 
        'code' , 'sku' , 'name'  , 'imgpublicid', 'imgpath' , 'desc' , 'rating' , 
        'price' , 'qty','promoendqty','salesqty','stockthreshold','status','onsale'];

    }

    public function videoDefaultCols() {

        return ['id','uid' ,'onsale', 'onpromo', 'name' , 'desc' , 'price' , 'disc' , 
        'discpctg' , 'promoprice' , 'promostartdate' , 'promoenddate', 'enddate' , 
        'stock', 'salesqty' ];

    }
    public function videoFilterCols() {

        return ['keyword','fromdate' ,'todate', 'status', 'scope'];

    }

}
