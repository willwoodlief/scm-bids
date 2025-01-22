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
                            Processed Bids
                        </li>

                    </ol>
                </nav>
            </section> <!-- /scm-page-header -->

           <section>
               @include(\Scm\PluginBid\Facades\ScmPluginBid::getBladeRoot().'::bids/stats/processed_table',['resolved'=>$resolved])
           </section>






        </div> <!-- container container-xl -->


    @endsection
@endcomponent

