@php
/** @var \Scm\PluginBid\Models\ScmPluginBidStat[] $resolved */
@endphp

<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            Bids that were marked as complete
        </h4>
    </div>
    <div class="card-body">
        <table  class="table" data-order='[[ 3, "desc" ]]' id="scm-plugin-bid-resolved-list">
            <thead>
            <tr>
                <th>Bid Name</th>
                <th>Status</th>
                <th>Contractor</th>
                <th>Added by</th>
                <th>Resolved at</th>
                <th>Budget</th>
            </tr>
            </thead>
            <tbody>
            @foreach($resolved as $stat)
                <tr >
                    <td data-order="{{$stat->getName()}}" data-sort="{{$stat->getName()}}" >
                        {{$stat->getName()}}
                    </td>

                    @php
                        $status_int = $stat->bid_failed_at ? -1 : ($stat->bid_success_at? 1 : 0);
                    @endphp
                    <td data-order="{{$status_int}}" data-sort="{{$status_int}}">
                        @if($stat->bid_success_at)
                            <span class="alert alert-success">
                                <a href="{{route('project.view', ['project_id'=>$stat->stats_project_id])}}">
                                    {{$stat->stat_project->getName()}}
                                </a>
                            </span>
                        @elseif ($stat->bid_failed_at)
                            <span class="alert alert-danger">
                                Not Accepted
                            </span>
                        @else
                            <span class="alert alert-light">
                                Pending
                            </span>
                        @endif
                    </td>

                    <td data-order="{{$stat->stat_contractor->getName()}}" data-sort="{{$stat->stat_contractor->getName()}}">
                        <a href="{{route('contractor.view',['contractor_id'=>$stat->stats_contractor_id])}}">
                            {{$stat->stat_contractor->getName()}}
                            <img src="{{$stat->stat_contractor->get_image_asset_path()}}" alt="" style="height: 2rem; width: auto;" class="ms-1">
                        </a>
                    </td>

                    <td>
                        <span>
                            {{$stat->stat_user->getName()}}
                        </span>
                    </td>

                    @php
                        $status_ts = $stat->bid_failed_at ? $stat->bid_failed_at_ts : ($stat->bid_success_at? $stat->bid_success_at_ts : $stat->bid_created_at_ts);
                        $tz_ts = \Carbon\Carbon::createFromTimestamp($status_ts,config('app.timezone'))->getTimestamp();
                    @endphp

                    <td data-order="{{$tz_ts}}" data-sort="{{$tz_ts}}" >
                        <span class="will-show-long-date-time" style="white-space: normal" data-ts="{{$tz_ts}}"></span>
                    </td>

                    <td data-order="{{$stat->budget}}" data-sort="{{$stat->budget}}">
                        {{\App\Helpers\Utilities::formatMoney($stat->budget)}}
                    </td>

                </tr>
            @endforeach
            </tbody>
        </table>
    </div> <!-- /card-body -->
</div> <!-- /card -->

<script>
    jQuery(function($) {
        function make_datatable() {
            let table = $('#scm-plugin-bid-resolved-list').DataTable({

                'dom': 'ZBfrltip',
                buttons: [],
                searching: true,
                select: false,
                responsive: true,
                pageLength:12,
                lengthChange:false ,
                language: {
                    paginate: {
                        next: `<x-icons.chevron-right />`,
                        previous: `<x-icons.chevron-left />`
                    }

                },

            });
            table.on('draw', function () {
                will_refresh_times();
            });
        }
        make_datatable();

    });

</script>
