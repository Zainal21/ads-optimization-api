<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Analys extends Model
{
    protected $table = 'analyses';

    protected $fillable = [
        'user_id',
        'campaign_ids',
        'summary',
        'performance_analysis',
        'underperforming_campaigns',
        'optimization_suggestions',
        'action_items',
        'metrics',
    ];

    protected $casts = [
        'campaign_ids' => 'array',
        'action_items' => 'array',
        'metrics' => 'array',
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
     * campaigns
     *
     * @return void
     */
    public function campaigns()
    {
        return Campaign::whereIn('id', $this->campaign_ids)->get();
    }
}
