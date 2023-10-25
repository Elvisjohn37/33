<?php

namespace Backend\models;

class Mpromotion extends Basemodel {
    protected $table      = 'promotion';
    public    $timestamps = false;

    /**
     * This will count enabled promotionID
     * @param  int $promotionID 
     * @return int
     */
    public function count_promotion_enabled($promotionID)
    {
        return $this->where('promotionID','=',$promotionID)->where('isEnabled','=',1)->count();
    }
}
