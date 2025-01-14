
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


                    <li class="breadcrumb-item active">
                        <a href="{{route('scm-bid.index')}}">
                            Bids
                        </a>
                    </li>
                </ol>
                <a class="btn btn-outline-secondary float-end" href="{{route('scm-bid.list')}}">
                    Bid List
                </a>
            </div>

            @include(\Scm\PluginBid\Facades\ScmPluginBid::getBladeRoot().'::bids/index/small-bid-list',[
                      'bids'=>$bids
                   ])

            @include(\Scm\PluginBid\Facades\ScmPluginBid::getBladeRoot().'::bids/index/charts',[
                      'stats'=>$stats
                   ])



        </div> <!-- container container-xl -->






    @endsection
@endcomponent

