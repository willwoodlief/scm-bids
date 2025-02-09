@php
    /**
     * @var \Scm\PluginBid\Models\ScmPluginBidSingle[] $bids
     */
@endphp



<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            Active bids
        </h4>
        <a class="btn btn-outline-primary float-end" href="{{route('scm-bid.new')}}">
            New Bid
        </a>
    </div>
    <div class="card-body">
        <table  class="table" data-order='[[ 3, "desc" ]]' id="scm-plugin-small-bid-list">
            <thead>
            <tr>
                <th>Bid Name</th>
                <th>Contractor</th>
                <th>When</th>
                <th>Budget</th>
            </tr>
            </thead>
            <tbody>
            @foreach($bids as $bid)
                <tr >
                    <td data-order="{{$bid->bid_name}}" data-sort="{{$bid->bid_name}}" >
                        <a href="{{route('scm-bid.bid.show',['single_bid'=>$bid->id])}}">
                            {{$bid->getName()}}
                        </a>
                        <a class="btn btn-rounded btn-outline-dark border-0  p-2 ms-2"
                           href="{{route('scm-bid.bid.edit',['single_bid'=>$bid->id])}}"
                           title="Edit {{str_replace('"','&quot;',$bid->getName())}}"
                        >
                            <i class="bi bi-pencil"></i>
                        </a>
                    </td>

                    <td data-order="{{$bid->bid_contractor->getName()}}" data-sort="{{$bid->bid_contractor->getName()}}">
                        <a href="{{route('contractor.view',['contractor_id'=>$bid->bid_contractor_id])}}">
                            {{$bid->bid_contractor->getName()}}
                            <img src="{{$bid->bid_contractor->get_image_asset_path()}}" alt="" style="height: 2rem; width: auto;" class="ms-1">
                        </a>
                    </td>



                    @php
                        $tz_ts = \Carbon\Carbon::createFromTimestamp($bid->created_at_ts,config('app.timezone'))->getTimestamp();
                    @endphp

                    <td data-order="{{$tz_ts}}" data-sort="{{$tz_ts}}" >
                        <span class="will-show-long-date-time" style="white-space: normal" data-ts="{{$tz_ts}}"></span>
                    </td>

                    <td data-order="{{$bid->budget}}" data-sort="{{$bid->budget}}">
                        {{\App\Helpers\Utilities::formatMoney($bid->budget)}}
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
            let table = $('#scm-plugin-small-bid-list').DataTable({

                'dom': 'ZBfrltip',
                buttons: [],
                searching: true,
                select: false,
                responsive: true,
                pageLength:{{count($bids) + 2}},
                lengthChange:true ,
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

