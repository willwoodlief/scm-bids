
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

            <section class="scm-page-header mt-1">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{route('dashboard')}}">
                                Dashboard
                            </a>
                        </li>

                        <li class="breadcrumb-item" >
                            <a href="{{route('admin')}}">
                                Admin
                            </a>
                        </li>

                        <li class="breadcrumb-item active" aria-current="page">
                            Bids
                        </li>


                        <li class="breadcrumb-item">
                            <a  href="{{route('scm-bid.list')}}">
                                Active Bids
                            </a>
                        </li>

                        <li class="breadcrumb-item">
                            <a   href="{{route('scm-bid.show_processed')}}">
                                Resolved Bids
                            </a>
                        </li>
                    </ol>



                </nav>
            </section>

            <section>
                @include(\Scm\PluginBid\Facades\ScmPluginBid::getBladeRoot().'::bids/index/stats-overview')

                @include(\Scm\PluginBid\Facades\ScmPluginBid::getBladeRoot().'::bids/index/small-bid-list',[
                          'bids'=>$bids
                       ])

                @include(\Scm\PluginBid\Facades\ScmPluginBid::getBladeRoot().'::bids/stats/charts')
            </section>


        </div> <!-- container container-xl -->






    @endsection
@endcomponent

