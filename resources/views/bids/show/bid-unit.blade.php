@php
    /**
     * @var \Scm\PluginBid\Models\ScmPluginBidSingle $bid
     */
@endphp



<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            {{$bid->getName()}}
        </h4>
    </div>
    <div class="card-body">
        <div class="card-text">
            <h5>Budget</h5>
            <span>{{\App\Helpers\Utilities::formatMoney($bid->budget)}}</span>
        </div>

        <div class="card-text mt-4">
            <h5>Address</h5>
            <span>{{$bid->address}}</span>
            <span>
                {{$bid->city}},
                {{$bid->state}}
                {{$bid->zip}}
            </span>
        </div>

    </div> <!-- /card-body -->
</div> <!-- /card -->

