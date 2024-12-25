<?php

namespace Scm\PluginBid\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class BidSaveRequest extends FormRequest
{

    protected function getRedirectUrl()
    {
        $called_route = Route::currentRouteName();
        if ($called_route === 'scm-bid.admin.bids.create') {
            $this->redirect = route('scm-bid.admin.bids.new');
        } elseif ($called_route === 'scm-bid.admin.bids.edit') {
            $url = $this->redirector->getUrlGenerator();
            return $url->route('scm-bid.admin.bids.update',['bid_id'=>$this->route('bid_id')]);
        }
        return parent::getRedirectUrl();
    }
    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    public function validationData()
    {
        return parent::validationData();
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'bid_contractor_id' => ['integer'],
            'bid_name' => ['string', 'max:255'],
            'address' => ['string', 'max:250'],
            'city' => ['string', 'max:100'],
            'state' => ['string', 'max:2'],
            'zip' => ['string', 'max:5'],
            'scratch_pad' => ['string', 'nullable','max:64000'],
            'latitude' => ['numeric','nullable'],
            'longitude' => ['numeric','nullable'],
            'budget' => ['numeric', 'min:1']
        ];
    }
}
