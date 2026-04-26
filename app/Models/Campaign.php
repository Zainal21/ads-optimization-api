<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $table = 'campaigns';

    protected $fillable = [
        'user_id',
        'name',
        'platform',
        'impressions',
        'clicks',
        'conversions',
        'spend',
        'revenue',
        'start_date',
        'end_date',
        'notes',
    ];

    /**
     * user
     *
     * @return void
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * getCTR
     */
    public function getCTR(): float
    {
        return $this->impressions > 0 ? ($this->clicks / $this->impressions) * 100 : 0;
    }

    /**
     * getCPC
     */
    public function getCPC(): float
    {
        return $this->clicks > 0 ? $this->spend / $this->clicks : 0;
    }

    /**
     * getCPA
     */
    public function getCPA(): float
    {
        return $this->conversions > 0 ? $this->spend / $this->conversions : 0;
    }

    /**
     * getROAS
     */
    public function getROAS(): float
    {
        return $this->spend > 0 ? $this->revenue / $this->spend : 0;
    }

    /**
     * getMetrics
     */
    public function getMetrics(): array
    {
        return [
            'ctr' => round($this->getCTR(), 2),
            'cpc' => round($this->getCPC(), 2),
            'cpa' => round($this->getCPA(), 2),
            'roas' => round($this->getROAS(), 2),
            'conversion_rate' => round(($this->clicks > 0 ? ($this->conversions / $this->clicks) * 100 : 0), 2),
        ];
    }
}
