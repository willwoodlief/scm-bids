@php
    /** @var \Scm\PluginBid\Models\ScmPluginBidStat[] $stats_success */
    /** @var \Scm\PluginBid\Models\ScmPluginBidStat[] $stats_fail */
    /** @var \Scm\PluginBid\Models\ScmPluginBidStat[] $stats_active */
    /** @var string $unit_type */
    if (!\App\Helpers\Utilities::get_logged_user()->has_permission(permission_names: \Scm\PluginBid\Helpers\PluginPermissions::PERMISSION_BID_VIEW_STATS)) {
        return;
    }
@endphp
<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            Charts and Graphs
        </h4>
    </div>
    <div class="card-body">
        <canvas id="line-chart"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

<script>
    jQuery(function() {
        /** @var {object[]} stats_success*/
        const stats_success = @json($stats_success);
        const stats_fail = @json($stats_fail);
        const stats_active = @json($stats_active);

        let data_success = [];
        for(let i = 0;i < stats_success.length; i++) {
            const node = stats_success[i];
            const plot = {x: node.date,y:node.count };
            data_success.push(plot);
        }

        let data_fail = [];
        for(let i = 0;i < stats_fail.length; i++) {
            const node = stats_fail[i];
            const plot = {x: node.date,y:node.count };
            data_fail.push(plot);
        }

        let data_active = [];
        for(let i = 0;i < stats_active.length; i++) {
            const node = stats_active[i];
            const plot = {x: node.date,y:node.count };
            data_active.push(plot);
        }
        const data = {
            datasets: [{
                    label: 'Not Accepted Bids',
                    borderColor: 'rgb(255, 99, 132)',
                    lineTension: 0.4,
                    data: data_fail
                },
                {
                    label: 'Accepted Bids',
                    borderColor: 'rgb(99, 255, 132)',
                    lineTension: 0.4,
                    data: data_success
                },
                {
                    label: 'Active Bids',
                    borderColor: 'rgb(0, 16, 132)',
                    lineTension: 0.4,
                    data: data_active
                }
            ]
        };

        const config = {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: '{{$unit_type}}'
                        }
                    },
                    y: {
                        min: 0
                    }
                }
            },
            title: {
                display: true,
                text: "Bids"
            }
        };

        const myChart = new Chart(
            document.getElementById('line-chart'),
            config
        );
    });
</script>


{{--
'millisecond'
'second'
'minute'
'hour'
'day'
'week'
'month'
'quarter'
'year'
--}}
