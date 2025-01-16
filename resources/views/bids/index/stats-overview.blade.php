@php
    if (!\App\Helpers\Utilities::get_logged_user()->has_permission(permission_names: \Scm\PluginBid\Helpers\PluginPermissions::PERMISSION_BID_VIEW_STATS)) {
            return;
    }
@endphp
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-4">
                <div class="alert alert-light text-center">
                    All Bids
                    <span class="badge badge-light regular-bootstap">
                        {{\Scm\PluginBid\Helpers\OverallStats::getTotalBids()}}
                    </span>

                </div>
            </div>

            <div class="col-4">

                <div class="alert alert-success text-center">
                    Accepted
                    <span class="badge badge-success">
                        {{\Scm\PluginBid\Helpers\OverallStats::getTotalSuccess()}}
                    </span>

                    <span class="badge badge-success float-end">
                        {{\App\Helpers\Utilities::formatMoney( \Scm\PluginBid\Helpers\OverallStats::getTotalSuccessBudget() )}}
                    </span>
                </div>

            </div>

            <div class="col-4">
                <div class="alert alert-danger text-center">
                    Not accepted
                    <span class="badge badge-danger">
                        {{\Scm\PluginBid\Helpers\OverallStats::getTotalFailed()}}
                    </span>

                </div>
            </div>
        </div>
    </div>
</div>
