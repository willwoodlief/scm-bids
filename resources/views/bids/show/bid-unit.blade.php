@php
    /**
     * @var \Scm\PluginBid\Models\ScmPluginBidSingle $bid
     */
@endphp



<div class="card">
    <div class="card-body">
        <div class="card-use-box">
            <ul class="post-pos">
                <li>
                    <span class="card__info__stats">Budget: </span>
                    <span>{{\App\Helpers\Utilities::formatMoney($bid->budget)}}</span>
                </li>
                <li>
                    <span class="card__info__stats">Address: </span>
                    <span>{{$bid->address}}</span>
                </li>
                <li>
                    <span class="card__info__stats">City, State: </span>
                    <span>
                        {{$bid->city}},
                        {{$bid->state}}
                        {{$bid->zip}}
                    </span>
                </li>
            </ul>
        </div> <!-- /card-use-box -->
    </div> <!-- /card-body -->
</div> <!-- /card -->

