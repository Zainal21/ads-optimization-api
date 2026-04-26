<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        $emailToCampaigns = [
            'admin@example.com' => [
                [
                    'name' => 'Brand Awareness - Q1',
                    'platform' => 'Facebook',
                    'impressions' => 250000,
                    'clicks' => 5200,
                    'conversions' => 140,
                    'spend' => 4200.75,
                    'revenue' => 9800.25,
                    'start_date' => '2026-01-01',
                    'end_date' => '2026-01-31',
                    'notes' => 'Broad targeting; focus on reach and CTR improvements.',
                ],
            ],
            'marketing@example.com' => [
                [
                    'name' => 'Demo Campaign Retargeting',
                    'platform' => 'Facebook',
                    'impressions' => 90000,
                    'clicks' => 4100,
                    'conversions' => 520,
                    'spend' => 3600.00,
                    'revenue' => 17250.00,
                    'start_date' => '2026-03-01',
                    'end_date' => '2026-03-31',
                    'notes' => 'Warm audiences; maintain frequency and ROAS.',
                ],
            ],
            'analyst@example.com' => [
                [
                    'name' => 'Demo Campaign Video Test',
                    'platform' => 'TikTok',
                    'impressions' => 210000,
                    'clicks' => 3800,
                    'conversions' => 90,
                    'spend' => 5200.00,
                    'revenue' => 6100.00,
                    'start_date' => '2026-01-15',
                    'end_date' => '2026-02-14',
                    'notes' => 'New creatives; underperforming on conversions.',
                ],
            ],
            'test@example.com' => [
                [
                    'name' => 'Demo Campaign',
                    'platform' => 'Google',
                    'impressions' => 25000,
                    'clicks' => 700,
                    'conversions' => 35,
                    'spend' => 900.00,
                    'revenue' => 1900.00,
                    'start_date' => '2026-04-01',
                    'end_date' => '2026-04-15',
                    'notes' => 'Small sample campaign for API testing.',
                ],
            ],
        ];

        $usersByEmail = User::whereIn('email', array_keys($emailToCampaigns))
            ->get()
            ->keyBy('email');

        foreach ($emailToCampaigns as $email => $campaigns) {
            $user = $usersByEmail->get($email);
            if (! $user) {
                continue;
            }

            foreach ($campaigns as $campaign) {
                Campaign::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'name' => $campaign['name'],
                        'start_date' => Carbon::parse($campaign['start_date'])->toDateString(),
                        'end_date' => Carbon::parse($campaign['end_date'])->toDateString(),
                    ],
                    [
                        'platform' => $campaign['platform'],
                        'impressions' => $campaign['impressions'],
                        'clicks' => $campaign['clicks'],
                        'conversions' => $campaign['conversions'],
                        'spend' => $campaign['spend'],
                        'revenue' => $campaign['revenue'],
                        'notes' => $campaign['notes'],
                    ]
                );
            }
        }
    }
}
