

@component('layouts.app')

    @section('page_title')
        Bids
    @endsection

    @section('main_content')

        <div class="inner-content-body">

            <!-- row -->
            <div class="page-titles">
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
                            Bids
                        </a>
                    </li>
                </ol>
            </div>




        </div> <!-- inner-content-body -->






    @endsection
@endcomponent

