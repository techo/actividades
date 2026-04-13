<?php

namespace App\Search\filters\usuario;

use App\Search\filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class CampaignId implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        return $builder->where('campaign_id', $value);
    }
}
