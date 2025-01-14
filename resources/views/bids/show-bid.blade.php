@php
    /**
     * @var \Scm\PluginBid\Models\ScmPluginBidSingle $bid
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

                    <li class="breadcrumb-item active">
                        <a href="{{route('scm-bid.bid.show',['single_bid'=>$bid->id])}}">
                            Show Bid {{$bid->getName()}}
                        </a>
                    </li>
                </ol>

                <a href="{{route('scm-bid.bid.edit',['single_bid'=>$bid->id])}}" class="btn btn-secondary float-end">
                    Edit {{$bid->getName()}}
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        {{$bid->getName()}}
                    </h4>

                    <button type="button" class="btn btn-outline-success scm-plugin-bid-success-action "
                            data-bid_name="{{str_replace('"','&quot;',$bid->getName())}}"
                            data-url="{{route('scm-bid.bid.success',['single_bid'=>$bid->id])}}"
                            data-method="post"
                            title="Make into a project {{str_replace('"','&quot;',$bid->getName())}}"
                    >
                        Success!
                        <i class="bi bi-play-fill"></i>
                    </button>

                    <button type="button" class="btn  btn-outline-danger scm-plugin-bid-fail-action "
                            data-bid_name="{{str_replace('"','&quot;',$bid->getName())}}"
                            data-url="{{route('scm-bid.bid.fail',['single_bid'=>$bid->id])}}"
                            data-method="delete"
                            title="Remove bid as unsuccessful: {{str_replace('"','&quot;',$bid->getName())}}"
                    >
                        Failed
                        <i class="bi bi-x-octagon-fill"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            @include(\Scm\PluginBid\Facades\ScmPluginBid::getBladeRoot().'::bids/show/contractor-unit',[
                               'contractor'=>$bid->bid_contractor
                            ])
                        </div>

                        <div class="col-12 col-md-6">
                            @include(\Scm\PluginBid\Facades\ScmPluginBid::getBladeRoot().'::bids/show/bid-unit',[
                               'bid'=>$bid
                            ])
                        </div>

                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div style="white-space: pre">{{$bid->scratch_pad}}</div>
                                </div>
                            </div>

                        </div>
                    </div>



                    @include(\Scm\PluginBid\Facades\ScmPluginBid::getBladeRoot().'::bids/shared/file-list',[
                       'bid'=>$bid,'b_edit'=>false
                    ])

                    <div class="row mt-4">
                        @include(\Scm\PluginBid\Facades\ScmPluginBid::getBladeRoot().'::bids/show/file-images',[
                              'bid'=>$bid
                           ])
                    </div>

                </div> <!-- /card-body -->
            </div> <!-- /card -->




        </div> <!-- container container-xl -->






    @endsection
@endcomponent

