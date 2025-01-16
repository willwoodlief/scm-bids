@php
    /**
     * @var \Scm\PluginBid\Models\ScmPluginBidSingle $bid
     * @var \App\Models\Contractor[] $contractors
     */
@endphp


<div class="container-fluid">
    <form method="POST" enctype="multipart/form-data"
          action="{{$bid->id? route('scm-bid.bid.update',['single_bid'=>$bid->id]) : route('scm-bid.create')}}"
    >
        @csrf
        @if($bid->id)
            @method('put')
        @else
            @method('post')
        @endif
        <div class="row">
            <div class="col-sm-6 mb-3">
                <label class="form-label" for="bid_name">Bid Name</label>
                <input type="text" name="bid_name" id="bid_name"  class="form-control" placeholder="" value="{{old('bid_name',$bid->bid_name)}}">
                <x-input-error :messages="$errors->get('bid_name')" />
            </div>
            <div class="col-sm-6 mb-3">
                <label class="form-label" for="address">Address</label>
                <input id="address" type="text" class="form-control addformcntrl" placeholder="address" name="address"  value="{{old('address',$bid->address)}}" autocomplete="off">
                <x-input-error :messages="$errors->get('address')" />
            </div>
            <div class="col-sm-6 mb-3">
                <label class="form-label" for="city">City</label>
                <input type="text" name="city" id="city" class="form-control"  placeholder="" value="{{old('city',$bid->city)}}">
                <x-input-error :messages="$errors->get('city')" />
            </div>
            <div class="col-sm-6 mb-3">
                <label class="form-label" for="state">State</label>
                <input type="text" name="state" id="state" class="form-control"  maxlength="2" placeholder="" value="{{old('state',$bid->state)}}">
                <x-input-error :messages="$errors->get('state')" />
            </div>
            <div class="col-sm-6 mb-3">
                <label class="form-label" for="zip">Zip</label>
                <input type="number" name="zip" id="zip" class="form-control" placeholder="" value="{{old('zip',$bid->zip)}}">
                <x-input-error :messages="$errors->get('zip')" />
            </div>
            <div class="col-sm-6 mb-3">
                <label class="form-label" for="budget">Budget</label>
                <input type="number" name="budget" id="budget" class="form-control" placeholder="Budget"
                       step="0.01" min="0.01"
                       value="{{old('budget',$bid->budget)}}">
                <x-input-error :messages="$errors->get('budget')" />
            </div>

            <div class="col-sm-6 mb-3">
                <label class="form-label" for="contractor-list">General Contractor</label>
                <select name="bid_contractor_id" id="contractor-list" class="form-control" autocomplete="off">
                    <option selected disabled>Select Contractor</option>
                    @foreach ($contractors as $contractor)
                        <option value="{{$contractor->id}}" @if(intval(old('bid_contractor_id',$bid->bid_contractor_id)) === $contractor->id) selected @endif>
                            {{$contractor->getName()}}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('bid_contractor_id')" />
            </div>

            <div class="col-sm-3 mb-3">
                <div class="mt-1">
                    <button type="button" class="btn btn-outline-primary scm-bid-plugin-new-contractor-action  mt-4">
                        New Contractor
                    </button>
                </div>

            </div>



            <div class="col-12 mb-3">
                <label class="form-label" for="scratch_pad">Scratch Pad</label>
                <textarea name="scratch_pad" id="scratch_pad" class="form-control" autocomplete="off"
                          rows="10"
                >{{old('scratch_pad',$bid->scratch_pad)}}</textarea>
                <x-input-error :messages="$errors->get('scratch_pad')" />
            </div>
            <div>
                <input type="hidden" class="form-control" id="lat" name="latitude" value="{{old('latitude',$bid->latitude)}}">
                <x-input-error :messages="$errors->get('latitude')" />
                <input type="hidden" class="form-control" id="long" name="longitude" value="{{old('longitude',$bid->longitude)}}">
                <x-input-error :messages="$errors->get('longitude')" />
            </div>
        </div> <!-- /row -->

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                @if($bid->id) Update @else Create @endif
            </button>
        </div>
    </form>
</div>

@include(\Scm\PluginBid\Facades\ScmPluginBid::getBladeRoot().'::bids/shared/contractor-dialog')
