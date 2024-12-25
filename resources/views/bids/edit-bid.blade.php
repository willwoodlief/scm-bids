@php
    /**
     * @var \Scm\PluginBid\Models\ScmPluginBidSingle $bid
     * @var \App\Models\Contractor[] $contractors
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
                        <a href="{{route('scm-bid.admin.bids.edit',['bid_id'=>$bid->id])}}">
                            Edit Bid
                        </a>
                    </li>

                </ol>
                <a href="{{route('scm-bid.admin.bids.show',['bid_id'=>$bid->id])}}" class="btn btn-secondary float-end">
                    Show {{$bid->getName()}}
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        Edit {{$bid->getName()}}
                    </h4>
                </div>
                <div class="card-body">
                    @include(\Scm\PluginBid\Facades\ScmPluginBid::getBladeRoot().'::bids/shared/bid-form',[
                        'bid'=>$bid,
                        'contractors'=>$contractors
                    ])

                    @include(\Scm\PluginBid\Facades\ScmPluginBid::getBladeRoot().'::bids/shared/file-list',[
                       'bid'=>$bid,'b_edit'=>true
                    ])

                    <div class="mt-5">
                        @include(\Scm\PluginBid\Facades\ScmPluginBid::getBladeRoot().'::bids/edit/bid-add-files',[
                       'bid'=>$bid
                   ])
                    </div>

                </div> <!-- /card-body -->
            </div> <!-- /card -->




        </div> <!-- container container-xl -->






    @endsection
@endcomponent

