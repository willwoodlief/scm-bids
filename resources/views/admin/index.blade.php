

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

                    <li class="breadcrumb-item active">
                        <a href="{{route('scm-bid.admin.index')}}">
                            Bid Administration
                        </a>
                    </li>
                </ol>
            </div>

            <ul class="list-group">
                <li class="list-group-item">
                    <a href="{{route('scm-bid.admin.bids.list')}}">
                        Bid List
                    </a>
                </li>

                <li class="list-group-item">
                    <a href="{{route('scm-bid.admin.bids.new')}}">
                        New Bid
                    </a>
                </li>
            </ul>


        </div> <!-- container container-xl -->






    @endsection
@endcomponent

