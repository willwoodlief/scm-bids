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
                        <a href="{{route('scm-bid.admin.bids.new')}}">
                            New Bid
                        </a>
                    </li>
                </ol>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        New Bid
                    </h4>
                </div>
                <div class="card-body">
                    @include(\Scm\PluginBid\Facades\ScmPluginBid::getBladeRoot().'::bids/bid-form',[
                        'bid'=>$bid,
                        'contractors'=>$contractors
                    ])
                </div> <!-- /card-body -->
            </div> <!-- /card -->




        </div> <!-- container container-xl -->






    @endsection
@endcomponent

