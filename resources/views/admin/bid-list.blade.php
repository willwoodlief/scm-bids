@php
    /**
     * @var \Scm\PluginBid\Models\ScmPluginBidSingle[] $bids
     */
@endphp

@component('layouts.app')

    @section('page_title')
        Bids
    @endsection

    @section('main_content')

        <div class="container container-xl mt-0">

            <!-- row -->
            <div class="page-titles mb-2">
                <ol class="breadcrumb">
                    <li>
                        <h5 class="bc-title">Bids</h5>
                    </li>

                    <li class="breadcrumb-item">
                        <a href="{{route('dashboard')}}">
                            @include('layouts.common.home-svg')
                            Home
                        </a>
                    </li>
                    <li class="breadcrumb-item "><a href="{{route('admin')}}">Admin</a></li>

                    <li class="breadcrumb-item">
                        <a href="{{route('scm-bid.admin.index')}}">
                            Bid Administration
                        </a>
                    </li>

                    <li class="breadcrumb-item active">
                        <a href="{{route('scm-bid.admin.bids.list')}}">
                            Bid List
                        </a>
                    </li>
                </ol>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        List of pending bids
                    </h4>
                </div>
                <div class="card-body">
                    <table  class="table" data-order='[[ 3, "desc" ]]' id="scm-plugin-bid-list">
                        <thead>
                        <tr>
                            <th>Bid Name</th>
                            <th>Contractor</th>
                            <th>Added By</th>
                            <th>When</th>
                            <th></th>

                        </tr>
                        </thead>
                        <tbody>
                        @foreach($bids as $bid)
                            <tr >
                                <td data-order="{{$bid->bid_name}}" data-sort="{{$bid->bid_name}}" >
                                    <a href="{{route('scm-bid.admin.bids.show',['bid_id'=>$bid->id])}}">
                                        {{$bid->getName()}}
                                    </a>

                                </td>

                                <td data-order="{{$bid->bid_contractor->getName()}}" data-sort="{{$bid->bid_contractor->getName()}}">
                                    <a href="{{route('contractor.view',['contractor_id'=>$bid->bid_contractor_id])}}">
                                        {{$bid->bid_contractor->getName()}}
                                    </a>
                                </td>

                                <td>
                                    <span>
                                        {{$bid->bid_created_by_user->getName()}}
                                    </span>

                                </td>

                                @php
                                    $tz_ts = \Carbon\Carbon::createFromTimestamp($bid->created_at_ts,config('app.timezone'))->getTimestamp();
                                @endphp

                                <td data-order="{{$tz_ts}}" data-sort="{{$tz_ts}}" >
                                    <span class="will-show-long-date-time" data-ts="{{$tz_ts}}"></span>
                                </td>

                                <td>
                                    <div class="d-flex justify-content-between flex-wrap">
                                        <a class="btn btn-sm btn-outline-primary "
                                           href="{{route('scm-bid.admin.bids.edit',['bid_id'=>$bid->id])}}"
                                           title="Edit {{str_replace('"','&quot;',$bid->getName())}}"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <button type="button" class="btn btn-sm btn-outline-success scm-plugin-bid-success-action "
                                                data-bid_name="{{str_replace('"','&quot;',$bid->getName())}}"
                                                data-url="{{route('scm-bid.admin.bids.successful',['bid_id'=>$bid->id])}}"
                                                data-method="post"
                                                title="Make into a project {{str_replace('"','&quot;',$bid->getName())}}"
                                        >
                                            <i class="bi bi-play-fill"></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-outline-danger scm-plugin-bid-fail-action "
                                                data-bid_name="{{str_replace('"','&quot;',$bid->getName())}}"
                                                data-url="{{route('scm-bid.admin.bids.failed',['bid_id'=>$bid->id])}}"
                                                data-method="delete"
                                                title="Remove bid as unsuccessful: {{str_replace('"','&quot;',$bid->getName())}}"
                                        >
                                            <i class="bi bi-x-octagon-fill"></i>
                                        </button>
                                    </div>

                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div> <!-- /card-body -->
            </div> <!-- /card -->




        </div> <!-- container container-xl -->

        <script>
            jQuery(function($) {
                function make_datatable() {
                    let table = $('#scm-plugin-bid-list').DataTable({

                        'dom': 'ZBfrltip',
                        buttons: [],
                        searching: true,
                        select: false,
                        responsive: true,
                        pageLength:12,
                        lengthChange:false ,
                        language: {
                            paginate: {
                                next: '<i class="fa-solid fa-angle-right"></i>',
                                previous: '<i class="fa-solid fa-angle-left"></i>'
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




    @endsection
@endcomponent

