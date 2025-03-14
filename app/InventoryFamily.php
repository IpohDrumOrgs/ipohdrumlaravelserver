<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/** @OA\Schema(
 *     title="InventoryFamily"
 * )
 */
class InventoryFamily extends Model
{
    /** @OA\Property(property="id", type="integer"),
     * @OA\Property(property="inventory_id", type="integer"),
     * @OA\Property(property="uid", type="string"),
     * @OA\Property(property="code", type="string"),
     * @OA\Property(property="sku", type="string"),
     * @OA\Property(property="name", type="string"),
     * @OA\Property(property="desc", type="string"),
     * @OA\Property(property="imgpublicid", type="string"),
     * @OA\Property(property="imgpath", type="string"),
     * @OA\Property(property="cost", type="number"),
     * @OA\Property(property="price", type="number"),
     * @OA\Property(property="qty", type="integer"),
     * @OA\Property(property="salesqty", type="integer"),
     * @OA\Property(property="onsale", type="integer"),
     * @OA\Property(property="status", type="integer"),
     * @OA\Property(property="created_at", type="string"),
     * @OA\Property(property="updated_at", type="string"),
     * @OA\Property(
     *     property="patterns",
     *      type="array",
     *      @OA\Items(
     *          ref="#/components/schemas/Pattern"
     *      )
     * )
     */
    /**
     *
     */
    public function inventory()
    {
        return $this->belongsTo('App\Inventory');
    }

    /**
     *
     */
    public function saleitems()
    {
        return $this->hasMany('App\SaleItem');
    }

    /**
     *
     */
    public function patterns()
    {
        return $this->hasMany('App\Pattern');
    }
}
