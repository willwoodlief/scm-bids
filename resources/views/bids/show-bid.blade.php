@php
    /**
     * @var \Scm\PluginBid\Models\ScmPluginBidSingle $bid
     * @var bool $b_edit
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

                        <li class="breadcrumb-item ">
                            <a href="{{route('scm-bid.list')}}">
                                Bid List
                            </a>
                        </li>


                        <li class="breadcrumb-item active" aria-current="page">
                             Bid {{$bid->getName()}}
                        </li>


                    </ol>
                </nav>
            </section> <!-- /scm-page-header -->

            <section>
                @include(\Scm\PluginBid\Facades\ScmPluginBid::getBladeRoot().'::bids/show/bid-card',[
                             'bid'=>$bid, 'b_edit' =>$b_edit
                         ])

            </section>






        </div> <!-- container container-xl -->


        @include(\Scm\PluginBid\Facades\ScmPluginBid::getBladeRoot().'::bids/shared/file-dialog')


    @endsection
@endcomponent

