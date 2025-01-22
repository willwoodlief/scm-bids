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

                        <li class="breadcrumb-item">
                            <a href="{{route('scm-bid.index')}}">
                                Bids
                            </a>
                        </li>


                        <li class="breadcrumb-item active" aria-current="page">
                            New Bid
                        </li>

                    </ol>
                </nav>
            </section> <!-- /scm-page-header -->

            <section>
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            New Bid
                        </h4>
                    </div>
                    <div class="card-body">
                        @include(\Scm\PluginBid\Facades\ScmPluginBid::getBladeRoot().'::bids/shared/bid-form',[
                            'bid'=>$bid,
                            'contractors'=>$contractors
                        ])
                    </div> <!-- /card-body -->
                </div> <!-- /card -->
            </section>

        </div> <!-- container container-xl -->

    @endsection
@endcomponent

