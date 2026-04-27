<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>{{ 'Campaign Analysis #'.$analysis->id }}</title>
    </head>
    <body style="font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827;">
        <div style="margin-bottom: 14px;">
            <div style="font-size: 18px; font-weight: 700;">{{ 'Campaign Analysis #'.$analysis->id }}</div>
            <div style="color: #6b7280;">Generated: {{ (string) $analysis->created_at }}</div>
        </div>

        <div style="margin: 12px 0 14px 0;">
            <div style="font-size: 14px; font-weight: 700; margin-bottom: 6px;">Summary</div>
            <div style="line-height: 1.4;">{!! nl2br(e((string) $analysis->summary)) !!}</div>
        </div>

        <div style="margin: 12px 0 14px 0;">
            <div style="font-size: 14px; font-weight: 700; margin-bottom: 6px;">Performance Analysis</div>
            <div style="line-height: 1.4;">{!! nl2br(e((string) $analysis->performance_analysis)) !!}</div>
        </div>

        @if(!empty($analysis->underperforming_campaigns))
            <div style="margin: 12px 0 14px 0;">
                <div style="font-size: 14px; font-weight: 700; margin-bottom: 6px;">Underperforming Campaigns</div>
                <div style="line-height: 1.4;">{!! nl2br(e((string) $analysis->underperforming_campaigns)) !!}</div>
            </div>
        @endif

        <div style="margin: 12px 0 14px 0;">
            <div style="font-size: 14px; font-weight: 700; margin-bottom: 6px;">Optimization Suggestions</div>
            <div style="line-height: 1.4;">{!! nl2br(e((string) $analysis->optimization_suggestions)) !!}</div>
        </div>

        @if(!empty($analysis->metrics))
            <div style="margin: 12px 0 14px 0;">
                <div style="font-size: 14px; font-weight: 700; margin-bottom: 6px;">Aggregated Metrics</div>
                <table style="border-collapse: collapse; width: 100%;">
                    @foreach(($analysis->metrics ?? []) as $key => $value)
                        <tr>
                            <td style="padding: 6px 8px; border: 1px solid #e5e7eb; width: 40%;">{{ ucwords(str_replace('_', ' ', (string) $key)) }}</td>
                            <td style="padding: 6px 8px; border: 1px solid #e5e7eb;">{{ is_scalar($value) || $value === null ? $value : json_encode($value) }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        @php($actionItems = $analysis->action_items ?? [])
        @if(!empty($actionItems))
            <div style="margin: 12px 0 14px 0;">
                <div style="font-size: 14px; font-weight: 700; margin-bottom: 6px;">Action Items</div>
                <ol style="padding-left: 18px; margin: 0;">
                    @foreach($actionItems as $item)
                        @php($priority = data_get($item, 'priority'))
                        @php($action = (string) data_get($item, 'action', ''))
                        @php($expectedImpact = (string) data_get($item, 'expectedImpact', ''))
                        <li style="margin: 0 0 8px 0;">
                            <div style="font-weight: 600;">
                                @if($priority !== null)
                                    {{ 'Priority '.$priority.': ' }}
                                @endif
                                {{ $action }}
                            </div>
                            @if($expectedImpact !== '')
                                <div style="color: #374151;">{{ 'Expected impact: '.$expectedImpact }}</div>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        @endif

        @if(!empty($campaigns) && count($campaigns) > 0)
            <div style="margin: 12px 0 14px 0; page-break-inside: avoid;">
                <div style="font-size: 14px; font-weight: 700; margin-bottom: 6px;">Campaigns</div>
                <table style="border-collapse: collapse; width: 100%;">
                    <thead>
                        <tr>
                            <th style="padding: 6px 8px; border: 1px solid #e5e7eb; text-align: left;">Name</th>
                            <th style="padding: 6px 8px; border: 1px solid #e5e7eb; text-align: left;">Platform</th>
                            <th style="padding: 6px 8px; border: 1px solid #e5e7eb; text-align: right;">Impr.</th>
                            <th style="padding: 6px 8px; border: 1px solid #e5e7eb; text-align: right;">Clicks</th>
                            <th style="padding: 6px 8px; border: 1px solid #e5e7eb; text-align: right;">Conv.</th>
                            <th style="padding: 6px 8px; border: 1px solid #e5e7eb; text-align: right;">Spend</th>
                            <th style="padding: 6px 8px; border: 1px solid #e5e7eb; text-align: right;">Revenue</th>
                            <th style="padding: 6px 8px; border: 1px solid #e5e7eb; text-align: left;">Period</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($campaigns as $campaign)
                            <tr>
                                <td style="padding: 6px 8px; border: 1px solid #e5e7eb;">{{ $campaign->name }}</td>
                                <td style="padding: 6px 8px; border: 1px solid #e5e7eb;">{{ $campaign->platform }}</td>
                                <td style="padding: 6px 8px; border: 1px solid #e5e7eb; text-align: right;">{{ $campaign->impressions }}</td>
                                <td style="padding: 6px 8px; border: 1px solid #e5e7eb; text-align: right;">{{ $campaign->clicks }}</td>
                                <td style="padding: 6px 8px; border: 1px solid #e5e7eb; text-align: right;">{{ $campaign->conversions }}</td>
                                <td style="padding: 6px 8px; border: 1px solid #e5e7eb; text-align: right;">{{ number_format((float) $campaign->spend, 2, '.', '') }}</td>
                                <td style="padding: 6px 8px; border: 1px solid #e5e7eb; text-align: right;">{{ number_format((float) $campaign->revenue, 2, '.', '') }}</td>
                                <td style="padding: 6px 8px; border:  1px solid #e5e7eb;">{{ $campaign->start_date.' - '.$campaign->end_date }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </body>
</html>
