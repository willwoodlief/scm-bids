@php
    /**
     * @var \Scm\PluginBid\Models\ScmPluginBidStat[] $stats
     * @var \Scm\PluginBid\Models\ScmPluginBidStat[] $resolved
     * @var string $total_budget
     */
@endphp

@component('layouts.app')

    @section('page_title')
        Processed Bids
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


                    <li class="breadcrumb-item">
                        <a href="{{route('scm-bid.index')}}">
                            Bids
                        </a>
                    </li>

                    <li class="breadcrumb-item active">
                        <a href="{{route('scm-bid.list')}}">
                           Processed Bids
                        </a>
                    </li>
                </ol>
            </div>

            @include(\Scm\PluginBid\Facades\ScmPluginBid::getBladeRoot().'::bids/stats/processed_table',['resolved'=>$resolved])




        </div> <!-- container container-xl -->


    @endsection
@endcomponent

